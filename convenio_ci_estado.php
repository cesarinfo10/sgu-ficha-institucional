<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_convenio_ci = $_REQUEST['id_convenio_ci'];
$estado         = $_REQUEST['estado'];
$fecha_estado   = $_REQUEST['fecha_estado'];

if ($fecha_estado == "") { $fecha_estado = date("Y-m-d"); }
	
if (!is_numeric($id_convenio_ci)) {
	echo(js("location.href='$enlbase=gestion_liquidaciones_cred_interno';"));
	exit;
}

if ($_REQUEST['guardar'] == "Guardar" && $estado <> "" && $fecha_estado <> "") {
	$SQL_upd_cci = "UPDATE finanzas.convenios_ci SET estado='$estado',fecha_cambio_estado='$fecha_estado'::date+now()::time WHERE id=$id_convenio_ci";
	if (consulta_dml($SQL_upd_cci) > 0) {
		echo(msje("Se ha guardado el cambio de estado"));
	} else {
		echo(msje("ERROR: NO se ha guardado el cambio de estado. Informe al Departamento de Informática."));
	}
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$SQL_convenio_ci = "SELECT c.id,to_char(c.fecha,'DD-tmMon-YYYY') AS fecha,c.estado,
                           to_char(c.fecha_cambio_estado,'DD \"de\" tmMonth \"de\" YYYY \"a las\" HH24:MI \"horas\"') AS fecha_cambio_estado,
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
                            u.nombre_usuario AS emisor,c.comentarios,
                            a.direccion,va.comuna,va.region,a.telefono,a.tel_movil,a.email,a.genero,a.fec_nac,
                            a.cohorte,a.mes_cohorte,uf.valor AS valor_uf,c.nulo
                     FROM finanzas.convenios_ci AS c
                     LEFT JOIN finanzas.vista_convenios_ci AS vc USING (id)
                     LEFT JOIN alumnos         AS a   ON a.id=c.id_alumno
                     LEFT JOIN carreras        AS car ON car.id=a.carrera_actual
                     LEFT JOIN usuarios        AS u   ON u.id=c.id_emisor                             
                     LEFT JOIN vista_alumnos   AS va   ON va.id=c.id_alumno
                     LEFT JOIN finanzas.valor_uf AS uf ON uf.fecha=c.fecha
                     WHERE c.id=$id_convenio_ci";
$convenio_ci     = consulta_sql($SQL_convenio_ci);
if (count($convenio_ci) == 0) {
	echo(js("location.href='$enlbase=gestion_liquidaciones_cred_interno';"));
	exit;
}

$estados_cci = consulta_sql("SELECT * FROM vista_cci_estados");

$estado_cci = "<span class='".str_replace(" ","",$convenio_ci[0]['estado'])."'><b>{$convenio_ci[0]['estado']}</b></span> "
            . "<i>desde el {$convenio_ci[0]['fecha_cambio_estado']}</i>";

?>
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div style="margin-top: 5px">

<form name='formulario' action='<?php echo($_SERVER['SCRIPT_NAME']); ?>' method="get">
<input type='hidden' name='modulo' value='<?php echo($modulo); ?>'>
<input type='hidden' name='id_convenio_ci' value='<?php echo($id_convenio_ci); ?>'>

<div>
  <input type='submit' name='guardar' value='Guardar'>
  <input type='button' name='cancelar' value='Cancelar' onClick="parent.jQuery.fancybox.close();">
</div>

<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style="margin-top: 5px">
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
    <td class='celdaNombreAttr'>Estado Actual:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($estado_cci); ?></td>
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
    <td class='celdaNombreAttr'>Nuevo Estado:</td>
    <td class='celdaValorAttr'>
      <select name="estado" class="filtro">
        <?php echo(select($estados_cci,$convenio_ci[0]['estado'])); ?>
      </select>
    </td>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><input type="date" class="boton" name="fecha_estado" value="<?php echo($fecha_estado); ?>"></td>
  </tr>
</table>
</form>

