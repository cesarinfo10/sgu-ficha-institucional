<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_pago     = $_REQUEST['id_pago'];
$impresion   = $_REQUEST['impresion'];
$enviar_api  = $_REQUEST['enviar_api'];
$reenvia_api = $_REQUEST['reenvia_api'];
$script_name = $_SERVER['SCRIPT_NAME'];
$enlbase     = $script_name."?modulo";

include("integracion/boletas.php");

if ($enviar_api == "si") {
	$respuesta_api = api_manager_crear_boleta($id_pago);
	//var_dump($respuesta_api);
	$bol_e_cod_erp = 'null';	
	if (is_numeric($respuesta_api)) {
		echo(msje_js("Boleta aceptada por la API Manager"));
		$bol_e_cod_erp = intval($respuesta_api);
	} else {
		echo(msje_js("ERROR: La boleta NO ha sido aceptada por la API Manager:\\n\\n"
					.$respuesta_api));
	}
	if ($reenvia_api == "si" && !is_numeric($respuesta_api)) {
		echo(msje_js("AVISO: Debido a que se solicitó un reenvio a la API de esta boleta y este ha fallado, no se registrará este resultado."));
	} else {
		consulta_dml("UPDATE finanzas.pagos SET bol_e_respuesta_api = '$respuesta_api',bol_e_cod_erp = $bol_e_cod_erp WHERE id=$id_pago");
	}
}

$SQL_pago = "SELECT p.id,coalesce(p.nro_boleta,p.nro_boleta_e,nro_factura) AS nro_boleta,
                    coalesce('B/'||p.nro_boleta,'BE/'||p.nro_boleta_e,'F/'||nro_factura) AS nro_docto,
                    to_char(p.fecha,'DD \"de\" tmMonth \"de\" YYYY') AS fecha,u.nombre_usuario AS cajero,cod_operacion,
                    efectivo,deposito,cheque,transferencia,tarj_credito,tarj_debito,cheque_afecha,p.nro_boleta_e,
                    CASE WHEN p.nro_boleta IS NOT NULL THEN 'boleta'
                         WHEN p.nro_boleta_e IS NOT NULL THEN 'boleta_e'
                         WHEN p.nro_factura IS NOT NULL THEN 'factura'
                    END AS tipo_docto,
                    CASE WHEN p.nro_boleta_e IS NOT NULL THEN 1 ELSE 0 END AS es_electronica,
                    CASE WHEN p.nro_boleta_e IS NOT NULL THEN 'Bol-E'
                         WHEN p.nro_boleta IS NOT NULL THEN 'Bol'
                         WHEN p.nro_factura IS NOT NULL THEN 'Fac'
                         ELSE '' 
                    END AS tipo_boleta,
                    CASE WHEN id_arqueo IS NULL THEN 'No' ELSE 'Si' END AS rendida,p.nulo,p.nulo_motivo,to_char(p.nulo_fecha,'DD tmMonth YYYY') AS nulo_fecha,
                    CASE
                      WHEN cob.id_contrato    IS NOT NULL THEN trim(coalesce(a.rut,pap.rut))
                      WHEN cob.id_convenio_ci IS NOT NULL THEN trim(a3.rut)
                      WHEN cob.id_alumno      IS NOT NULL THEN trim(a2.rut)
                    END AS rut_alumno,
                    CASE
                      WHEN cob.id_contrato    IS NOT NULL THEN coalesce(a.apellidos||' '||a.nombres,pap.apellidos||' '||pap.nombres) 
                      WHEN cob.id_convenio_ci IS NOT NULL THEN a3.apellidos||' '||a3.nombres
                      WHEN cob.id_alumno      IS NOT NULL THEN a2.apellidos||' '||a2.nombres  
                    END AS nombre_alumno,
                    CASE
                      WHEN cob.id_contrato    IS NOT NULL THEN car.nombre 
                      WHEN cob.id_convenio_ci IS NOT NULL THEN car3.nombre 
                      WHEN cob.id_alumno      IS NOT NULL THEN car2.nombre  
                    END AS carrera_alumno,
                    CASE
                      WHEN cob.id_contrato    IS NOT NULL THEN c.jornada 
                      WHEN cob.id_convenio_ci IS NOT NULL THEN a3.jornada 
                      WHEN cob.id_alumno      IS NOT NULL THEN a2.jornada  
                    END AS jornada_alumno,to_char(p.fecha_reg,'DD-tmMon-YYYY HH24:MI') as fecha_reg,
                    p.bol_e_respuesta_api,p.bol_e_cod_erp
             FROM finanzas.pagos AS p
             LEFT JOIN vista_usuarios AS u          ON u.id=id_cajero
             LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_pago=p.id 
             LEFT JOIN finanzas.cobros AS cob       ON cob.id=id_cobro 
             LEFT JOIN finanzas.contratos AS c      ON c.id=cob.id_contrato
             LEFT JOIN finanzas.convenios_ci AS cci ON cci.id=cob.id_convenio_ci 
             LEFT JOIN alumnos AS a                 ON a.id=c.id_alumno
             LEFT JOIN alumnos AS a2                ON a2.id=cob.id_alumno
             LEFT JOIN alumnos AS a3                ON a3.id=cci.id_alumno
             LEFT JOIN pap                          ON pap.id=c.id_pap
             LEFT JOIN carreras AS car			    ON car.id=c.id_carrera
             LEFT JOIN carreras AS car2			    ON car2.id=a2.carrera_actual
             LEFT JOIN carreras AS car3			    ON car3.id=a3.carrera_actual
             WHERE p.id=$id_pago";
$pago     = consulta_sql($SQL_pago);

//api_manager_agregar_alumno($pago[0]['rut_alumno']);
//api_manager_mod_alumno($pago[0]['rut_alumno']);


// Intentar crear estudiante mediante API: Desconectado por servicio caido en Manager 24-2-23
api_manager_agrmod_alumno($pago[0]['rut_alumno']);

if (count($pago) > 0) {
  $nc = consulta_sql("SELECT nro_docto,to_char(fecha,'DD-tmMon-YYYY') AS fecha,monto,observacion FROM finanzas.notas_credito WHERE id_pago=$id_pago");

  $nota_credito = "";
  if (count($nc) == 1) {
    $monto_nc = number_format($nc[0]['monto'],0,',','.');
    $nota_credito = "<a title='Observación: {$nc[0]['observacion']}'>NCE/{$nc[0]['nro_docto']} {$nc[0]['fecha']} $$monto_nc</a>";
  }
	//var_dump($pago);
	//if (api_manager_consulta_alumno($pago[0]['rut_alumno']) == false) {
	//	echo(msje_js("No existe en el ERP el cliente. Se procede a crearlo."));
	//	echo(msje_js(api_manager_agregar_alumno($pago[0]['rut_alumno'])));
	//}
//	echo(msje_js(api_manager_agregar_alumno($pago[0]['rut_alumno'])));
	
	$msje = "¿Está seguro de reenviar esta boleta a la API?\\n\\n"
	      . "Para reenviarla debe verificar que no exista esta boleta en el ERP, de otro modo generará un error el reenvio.";
	$url_si = "$enlbase=ver_pago&id_pago=$id_pago&enviar_api=si&reenvia_api=si";
	$boton_api = "<a href='#' class='boton' style='color: red' onClick=\"if (confirm('$msje')) { location.href='$url_si'; } \">Reenviar a la API</a>";
	
	if ($pago[0]['nro_boleta_e'] <> "" && $pago[0]['bol_e_cod_erp'] == "" && $impresion <> "si") {
		echo(msje_js("ATENCIÓN: Esta boleta electrónica NO está informada al ERP y por consiguiente "
		            ."no está emitida electrónicamente.\\n\\n"
		            ."Corrija los errores que indica el campo Mensaje API y presione el botón Enviar a la API"));
		$boton_api = "<a href='$enlbase=ver_pago&id_pago=$id_pago&enviar_api=si' class='boton';\">Enviar a la API</a>";
	}
	$efectivo      = number_format($pago[0]['efectivo'],0,",",".");
	$deposito      = number_format($pago[0]['deposito'],0,",",".");
	$cheque        = number_format($pago[0]['cheque'],0,",",".");
	$cheque_afecha = number_format($pago[0]['cheque_afecha'],0,",",".");
	$transferencia = number_format($pago[0]['transferencia'],0,",",".");
	$tarj_credito  = number_format($pago[0]['tarj_credito'],0,",",".");
	$tarj_debito   = number_format($pago[0]['tarj_debito'],0,",",".");
	
	$SQL_pago_detalle = "SELECT c.id AS id_cobro,g.nombre AS glosa,pd.monto_pagado,c.monto,
	                            to_char(c.fecha_venc,'DD-tmMon-YYYY') AS fecha_venc,nro_cuota,
	                            coalesce(id_contrato,id_convenio_ci) AS nro_docto,
	                            coalesce(con.ano,date_part('year',cci.fecha)) AS ano_docto
	                     FROM finanzas.pagos_detalle     AS pd
	                     LEFT JOIN finanzas.cobros       AS c   ON c.id=pd.id_cobro
	                     LEFT JOIN finanzas.glosas       AS g   ON g.id=c.id_glosa
	                     LEFT JOIN finanzas.contratos    AS con ON con.id=c.id_contrato
	                     LEFT JOIN finanzas.convenios_ci AS cci ON cci.id=c.id_convenio_ci
	                     WHERE pd.id_pago=$id_pago
	                     ORDER BY c.fecha_venc";
	$pago_detalle     = consulta_sql($SQL_pago_detalle);
	$monto_total = 0;
	$HTML = "";
	for ($x=0;$x<count($pago_detalle);$x++) {
		extract($pago_detalle[$x]);
		$accion = "Pagó";
		if ($monto_pagado < $monto) { $accion = "Abonó"; } 
		$monto_total += $monto_pagado;
		$monto_pagado = number_format($monto_pagado,0,",",".");
		$id_contrato =  number_format($id_contrato,0,",",".");
		$HTML .= "<tr>"
		      .  "  <td class='textoTabla' style='vertical-align: middle; color: #7F7F7F'>$id_cobro</td>"
		      .  "  <td class='textoTabla' style='vertical-align: middle'>$glosa</td>"
		      .  "  <td class='textoTabla' style='vertical-align: middle'>$fecha_venc</td>"
		      .  "  <td class='textoTabla' style='vertical-align: middle; text-align: center'>$nro_cuota</td>"
		      .  "  <td class='textoTabla' style='vertical-align: middle; text-align: center'>$nro_docto</td>"
		      .  "  <td class='textoTabla' style='vertical-align: middle; text-align: center'>$ano_docto</td>"
		      .  "  <td class='textoTabla' style='vertical-align: middle; text-align: center'>$accion</td>"
		      .  "  <td class='textoTabla' style='vertical-align: middle; text-align: right'>$$monto_pagado</td>"
		      .  "</tr>";
	}
	$monto_total = number_format($monto_total,0,",",".");
	
	$boton_registro_cheques = "";
	if ($cheque <> "" || $cheque_afecha <> "") {
		$boton_registro_cheques = "<a href='$enlbase_sm=pago_registrar_cheques&id_pago={$pago[0]['id']}' class='boton'>Registrar Cheque(s)</a>";
	}
} else {
	exit;
}

if ($pago[0]['nulo'] == "t") { $nulo = " <b style='color: red'>NULO</b>"; }
$jornada_alumno = "";
if ($pago[0]['jornada_alumno'] == "D") { $jornada_alumno = "Diurna"; } else { $jornada_alumno = "Vespertina"; }
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<?php if ($impresion <> "si") { ?>
<table cellpadding="1" border="0" cellspacing="2" width="auto" style="margin-top: 5px">
  <tr>
    <td class="celdaFiltro">
      Acciones:<br>
      <a href='<?php echo("$enlbase_sm=ver_pago_imprimir&id_pago=$id_pago"); ?>' target='_blank' class="boton">Imprimir</a>
      <?php echo($boton_registro_cheques); ?>
      <?php if ($pago[0]['es_electronica'] == 0) { ?>
        <a href='<?php echo("$enlbase_sm=anular_pago&nro_docto={$pago[0]['nro_boleta']}&tipo_docto={$pago[0]['tipo_docto']}"); ?>' class="boton">Anular</a>
      <?php } elseif ($pago[0]['es_electronica'] == 1) { ?>
        <a href='<?php echo("$enlbase_sm=eliminar_boleta_electronica&nro_docto={$pago[0]['nro_boleta']}&tipo_docto={$pago[0]['tipo_docto']}"); ?>' class="boton">Eliminar</a>
      <?php } ?>
      <a href='#' onClick="history.back();" class="boton">Volver</a>
	<?php if (count($nc) == 0) { ?>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <a href='<?php echo("$enlbase_sm=nota_credito_registrar&id_pago={$pago[0]['id']}"); ?>' class="boton">Nota de Crédito</a>
	<?php } ?>
    </td>
  </tr>
</table>
<?php } ?>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes del Documento de Pago</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='color: #7F7F7F'>ID:</td>
    <td class='celdaValorAttr' style='color: #7F7F7F'><?php echo($pago[0]['id']); ?></td>
    <td class='celdaNombreAttr'>Nº <?php echo($pago[0]['tipo_boleta']); ?>:</td>
    <td class='celdaValorAttr'><b><?php echo(number_format($pago[0]['nro_boleta'],0,",",".").$nulo); ?></b><br><?php echo($nota_credito); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cajero:</td>
    <td class='celdaValorAttr'><?php echo($pago[0]['cajero']); ?></td>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><a title="Registrada: <?php echo($pago[0]['fecha_reg']); ?>"><?php echo($pago[0]['fecha']); ?></a></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes del Alumno</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr'><?php echo($pago[0]['nombre_alumno']); ?></td>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($pago[0]['rut_alumno']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr'><?php echo($pago[0]['carrera_alumno']); ?></td>
    <td class='celdaNombreAttr'>Jornada:</td>
    <td class='celdaValorAttr'><?php echo($jornada_alumno); ?></td>
  </tr>
<?php if ($pago[0]['nro_boleta_e'] <> "") { ?>  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Mensaje de la API</td></tr>
  <tr><td class='celdaValorAttr' colspan="4"><?php echo($pago[0]['bol_e_respuesta_api']." $boton_api"); ?></td></tr>
<?php } ?>
<?php if ($pago[0]['nulo'] == "t") {?>  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Anulación</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($pago[0]['nulo_fecha']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Motivo:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($pago[0]['nulo_motivo']); ?></td>
  </tr>
<?php } ?>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr class='filaTituloTabla'><td colspan="8" class='tituloTabla'>Detalle</td></tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' rowspan="2" style='color: #7F7F7F'>ID<br>Cobro</td>
    <td class='tituloTabla' rowspan="2">Glosa</td>
    <td class='tituloTabla' rowspan="2">Vencimiento</td>
    <td class='tituloTabla' rowspan="2">Nº<br> Cuota</td>
    <td class='tituloTabla' colspan="2">Documento</td>
    <td class='tituloTabla' rowspan="2">Acción</td>
    <td class='tituloTabla' rowspan="2">Monto</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>N°</td>
    <td class='tituloTabla'>Año</td>
  </tr>
  <?php echo($HTML); ?>
  <tr><td colspan="8" class='celdaValorAttr'>&nbsp;</td></tr>
  <tr>
    <td colspan="5">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="2" style="text-align: right; ">Monto Boleta</td>
    <td class='celdaValorAttr' style='text-align: right;'><b>$<?php echo($monto_total); ?></b></td>
  </tr>
  <tr>
    <td colspan="5">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="2" style="text-align: right; ">Código Operación</td>
    <td class='celdaValorAttr' style='text-align: right;'><?php echo($pago[0]['cod_operacion']); ?></td>
  </tr>
  <tr>
    <td colspan="5">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="3" style="text-align: center;">Forma de Pago</td>
  </tr>
  <tr>
    <td colspan="5">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="2">Efectivo</td>
    <td class='celdaValorAttr' style='text-align: right;'>$<?php echo($efectivo); ?></td>
  </tr>
  <tr>
    <td colspan="5">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="2">Depósito</td>
    <td class='celdaValorAttr' style='text-align: right;'>$<?php echo($deposito); ?></td>
  </tr>
  <tr>
    <td  colspan="5">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="2">Cheque al día</td>
    <td class='celdaValorAttr' style='text-align: right;'>$<?php echo($cheque); ?></td>
  </tr>
  <tr>
    <td  colspan="5">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="2">Cheque a fecha</td>
    <td class='celdaValorAttr' style='text-align: right;'>$<?php echo($cheque_afecha); ?></td>
  </tr>
  <tr>
    <td  colspan="5">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="2">Transferencia</td>
    <td class='celdaValorAttr' style='text-align: right;'>$<?php echo($transferencia); ?></td>
  </tr>
  <tr>
    <td  colspan="5">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="2">Tarjeta de Crédito</td>
    <td class='celdaValorAttr' style='text-align: right;'>$<?php echo($tarj_credito); ?></td>
  </tr>
  <tr>
    <td colspan="5">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="2">Tarjeta de Débito</td>
    <td class='celdaValorAttr' style='text-align: right;'>$<?php echo($tarj_debito); ?></td>
  </tr>  
</table>

<!-- Fin: <?php echo($modulo); ?> -->
