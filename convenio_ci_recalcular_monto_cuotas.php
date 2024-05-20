<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_convenio_ci  = $_REQUEST['id_convenio_ci'];
$fecha_recalculo = $_REQUEST['fecha_recalculo'];

//if (empty($fecha_recalculo)) { $fecha_recalculo = date("d-m-Y"); }

if (!is_numeric($id_convenio_ci)) {
	echo(js("location.href='$enlbase=gestion_liquidaciones_cred_interno';"));
	exit;
}

$SQL_convenio_ci = "SELECT c.id,to_char(c.fecha,'DD-tmMon-YYYY') AS fecha,c.estado,
                            date_part('year',c.fecha) AS periodo,trim(a.rut) AS rut,c.id_alumno,
                            upper(a.apellidos)||' '||initcap(a.nombres) AS al_nombre,c.monto_liqci,
                            c.monto_liqci::float/uf.valor::float AS monto_liqci_uf,
                            c.descuento_inicial,c.monto_adicional,c.monto_adicional::float/uf.valor::float AS monto_adicional_uf,
                            c.descuento_inicial::float/uf.valor::float AS descuento_inicial_uf,
                            c.descuento_inicial::float*100/(c.monto_liqci+coalesce(c.monto_adicional,0))::float AS descuento_inicial_porc,
                            trim(car.nombre) AS carrera,CASE a.jornada WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada,
                            c.liqci_efectivo,c.liqci_cheque,coalesce(c.liqci_cant_cheques,0) AS liqci_cant_cheques,
                            c.liqci_pagare,coalesce(c.liqci_cuotas_pagare,0) AS liqci_cuotas_pagare,
                            c.liqci_diap_pagare,c.liqci_mes_ini_pagare,c.liqci_ano_ini_pagare,
                            c.liqci_tarj_credito,coalesce(c.liqci_cant_tarj_credito,0) AS liqci_cant_tarj_credito,
                            CASE WHEN c.monto_condonacion>0 THEN '(C)' END AS condonacion,c.monto_condonacion,
                            to_char(c.fecha_condonacion,'DD-MM-YYYY') AS fecha_condonacion,
                            vc.total_pagado,vc.saldo_total,vc.monto_moroso,vc.cant_cuotas_morosas,
                            u.nombre_usuario AS emisor,c.comentarios,pc.id AS id_pagare_liqci,
                            a.direccion,va.comuna,va.region,a.telefono,a.tel_movil,a.email,a.genero,a.fec_nac,
                            a.cohorte,a.mes_cohorte,uf.valor AS valor_uf
                     FROM finanzas.convenios_ci AS c
                     LEFT JOIN finanzas.vista_convenios_ci AS vc USING (id)
                     LEFT JOIN alumnos         AS a   ON a.id=c.id_alumno
                     LEFT JOIN carreras        AS car ON car.id=a.carrera_actual
                     LEFT JOIN usuarios        AS u   ON u.id=c.id_emisor                             
                     LEFT JOIN finanzas.pagares_liqci AS pc ON pc.id_convenio_ci=c.id
                     LEFT JOIN vista_alumnos   AS va   ON va.id=c.id_alumno
                     LEFT JOIN finanzas.valor_uf AS uf ON uf.fecha=c.fecha
                     WHERE c.id=$id_convenio_ci";
$convenio_ci     = consulta_sql($SQL_convenio_ci);
if (count($convenio_ci) == 0) {
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$SQL_cobros = "SELECT 1 FROM finanzas.cobros WHERE id_convenio_ci=$id_convenio_ci AND id_glosa IN (300,301,302,303) AND NOT pagado";
if (count(consulta_sql($SQL_cobros)) == 0) {
	echo(msje_js("No es posible recalcular los montos de las cuotas, debido a que en este convenio se encuentran todas pagadas."));
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$SQL_pagos = "SELECT to_char(max(p.fecha),'DD-MM-YYYY') AS fecha 
              FROM finanzas.cobros AS c 
              LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_cobro=c.id 
              LEFT JOIN finanzas.pagos AS p ON p.id=pd.id_pago 
              WHERE c.id_convenio_ci=$id_convenio_ci";
$pagos     = consulta_sql($SQL_pagos);

if (empty($fecha_recalculo)) { $fecha_recalculo = $pagos[0]['fecha']; }
if (empty($fecha_recalculo)) { $fecha_recalculo = date("d-m-Y"); }

$estado_cci = $color = "";
switch ($convenio_ci[0]['estado']) {
	case "En cobranza":
	case "Cob. Judicial":
		$color = "#FFA500";
		break;
	case "Nulo":
		$color = "#FF0000";
		break;
	case "Emitido":
		$color = "#009900";
		break;
}
$estado_cci = "<b><span style='color: $color'>{$convenio_ci[0]['estado']}</span></b>";

$estado_legal_cci = $color = "";
switch ($convenio_ci[0]['estado_legal']) {
	case "Notariado":
		$color = "#009900";
		break;
	case "Sin firma":
		$color = "#FF0000";
		break;
	case "Firmado":
	case "En Notaria":
		$color = "#FFA500";
		break;
}
$estado_legal_cci = "<b><span style='color: $color'>{$convenio_ci[0]['estado_legal']}</span></b>";

if ($_REQUEST['guardar'] == "Guardar" && strtotime($fecha_recalculo) <> -1) {
	$SQL_valor_uf = "SELECT valor FROM finanzas.valor_uf WHERE fecha='$fecha_recalculo'::date";
	$valor_uf     = consulta_sql($SQL_valor_uf);
	if (count($valor_uf) > 0) {
		$SQL_cobros_upd = "UPDATE finanzas.cobros
		                   SET monto=round(monto_uf*({$valor_uf[0]['valor']}),0)
		                   WHERE id_convenio_ci=$id_convenio_ci AND id_glosa IN (300,301,302,303) AND NOT pagado";
		if (consulta_dml($SQL_cobros_upd) > 0) {
			echo(msje_js("Se ha realizado el recalculo exitosamente"));
			echo(js("parent.jQuery.fancybox.close();"));
			exit;
		}
	} else {
		echo(msje_js("ERROR: No se ha realizado el recalculo. No existe un valor de UF registrado para la fecha indicada."));
		$fecha_recalculo = date("d-m-Y");
	}
}

?>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>
<script src="js/Kalendae/kalendae.standalone.js" type="text/javascript" charset="utf-8"></script>
<form name="formulario" action="principal_sm.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_convenio_ci" value="<?php echo($id_convenio_ci); ?>">

<div style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Guardar">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Generales del Convenio</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Número:</td>
    <td class='celdaValorAttr'><?php echo($convenio_ci[0]['id']); ?></td>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><?php echo($convenio_ci[0]['fecha']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Estado Interno:</td>
    <td class='celdaValorAttr'><?php echo($estado_cci); ?></td>
    <td class='celdaNombreAttr'>Estado Legal:</td>
    <td class='celdaValorAttr'><?php echo($estado_legal_cci); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Alumno</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($convenio_ci[0]['rut']); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($convenio_ci[0]['id_alumno']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($convenio_ci[0]['al_nombre']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Conversión de UF a Pesos</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr' colspan="3">
      <input type="text" name="fecha_recalculo" value="<?php echo($fecha_recalculo); ?>" size="10" id="fecha_recalculo" class="boton">
      <script type="text/javascript" charset="utf-8">
		var k4 = new Kalendae.Input('fecha_recalculo', { format: 'DD-MM-YYYY', weekStart: 1 } );
      </script>
    </td>
  </tr>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
