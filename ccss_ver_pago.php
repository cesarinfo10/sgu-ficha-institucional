<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_pago = $_REQUEST['id_pago'];

$script_name  = $_SERVER['SCRIPT_NAME'];
$enlbase = $script_name."?modulo";

$SQL_socios = "SELECT char_comma_sum(rut||' '||nombres||' '||apellidos) 
               FROM finanzas.ccss_socios 
               WHERE id IN (SELECT id_socio FROM finanzas.ccss_sociedades_socios WHERE id_sociedad=s.id)";


$SQL_pago = "SELECT p.id,to_char(p.fecha,'DD tmMonth YYYY') AS fecha,u.nombre_usuario AS cajero,
                    s.razon_social,s.rut,($SQL_socios) AS socios,
                    efectivo,cheque,transferencia
             FROM finanzas.ccss_pagos              AS p
             LEFT JOIN vista_usuarios              AS u   ON u.id=id_cajero
             LEFT JOIN finanzas.ccss_pagos_detalle AS pd  ON pd.id_pago=p.id 
             LEFT JOIN finanzas.ccss_cobros        AS cob ON cob.id=id_cobro 
             LEFT JOIN finanzas.ccss_compromisos   AS c   ON c.id=cob.id_compromiso
             LEFT JOIN finanzas.ccss_sociedades    AS s   ON s.id=c.id_sociedad
             WHERE p.id=$id_pago";
$pago     = consulta_sql($SQL_pago);

if (count($pago) > 0) {
	
	$socios = str_replace(",","<br>",$pago[0]['socios']);
	
	$efectivo      = number_format($pago[0]['efectivo'],0,",",".");
	$cheque        = number_format($pago[0]['cheque'],0,",",".");
	$cheque_afecha = number_format($pago[0]['cheque_afecha'],0,",",".");
	$transferencia = number_format($pago[0]['transferencia'],0,",",".");
	$tarj_credito  = number_format($pago[0]['tarj_credito'],0,",",".");
	$tarj_debito   = number_format($pago[0]['tarj_debito'],0,",",".");
	
	$SQL_pago_detalle = "SELECT c.id AS id_cobro,pd.monto_pagado,c.monto,
	                            to_char(c.fecha_venc,'DD-tmMon-YYYY') AS fecha_venc,nro_cuota,id_compromiso,com.ano
	                     FROM finanzas.ccss_pagos_detalle    AS pd
	                     LEFT JOIN finanzas.ccss_cobros      AS c   ON c.id=pd.id_cobro
	                     LEFT JOIN finanzas.ccss_compromisos AS com ON com.id=c.id_compromiso
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
		      .  "  <td class='textoTabla' style='vertical-align: middle'>$fecha_venc</td>"
		      .  "  <td class='textoTabla' style='vertical-align: middle; text-align: center'>$nro_cuota</td>"
		      .  "  <td class='textoTabla' style='vertical-align: middle; text-align: center'>$id_compromiso</td>"
		      .  "  <td class='textoTabla' style='vertical-align: middle; text-align: center'>$ano</td>"
		      .  "  <td class='textoTabla' style='vertical-align: middle; text-align: center'>$accion</td>"
		      .  "  <td class='textoTabla' style='vertical-align: middle; text-align: right'>$$monto_pagado</td>"
		      .  "</tr>";
	}
	$monto_total = number_format($monto_total,0,",",".");
	
	$SQL_cheques = "SELECT if.nombre AS inst_finan,nro_cuenta,numero,monto,to_char(fecha_venc,'DD-tmMon-YYYY') AS fecha_venc,rut_emisor,nombre_emisor,telefono_emisor
	                FROM finanzas.ccss_cheques AS ch
	                LEFT JOIN finanzas.inst_financieras AS if ON if.codigo=ch.cod_inst_finan
	                WHERE ch.id_pago = $id_pago";
	$cheques     = consulta_sql($SQL_cheques);	
	$HTML_cheques = "";
	for($x=0;$x<count($cheques);$x++) {
		$monto = number_format($cheques[$x]['monto'],0,",",".");
		$nro_cuota = $x + 1;
		$HTML_cheques .=  "<tr class='filaTabla'>\n"
			          .   "  <td class='textoTabla' align='center'>{$cheques[$x]['fecha_venc']}</td>\n"
			          .   "  <td class='textoTabla' align='right'>$nro_cuota</td>\n"
			          .   "  <td class='textoTabla' align='right'>$$monto</td>\n"
			          .   "  <td class='textoTabla' align='center'>{$cheques[$x]['inst_finan']}</td>\n"
			          .   "  <td class='textoTabla' align='center'>{$cheques[$x]['nro_cuenta']}</td>\n"
			          .   "  <td class='textoTabla' align='center'>{$cheques[$x]['numero']}</td>\n"
			          .   "  <td class='textoTabla'><small>{$cheques[$x]['rut_emisor']}<br>{$cheques[$x]['nombre_emisor']}<br>{$cheques[$x]['telefono_emisor']}</small></td>\n"
			          .   "</tr>\n";
	}
} else {
	exit;
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<table cellpadding="1" border="0" cellspacing="2" width="auto" style="margin-top: 5px">
  <tr>
    <td class="celdaFiltro">
      Acciones:<br>
      <a href='<?php echo("ccss_ver_pago_imprimir.php?id=$id_pago"); ?>' target='_blank' class="boton">Imprimir Recibo</a>
      <a href='#' onClick="location.href='<?php echo($_SERVER['HTTP_REFERER']); ?>';" class="boton">Volver</a>
    </td>
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes del Comprobante de Ingreso CCSS</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='color: #7F7F7F'>ID:</td>
    <td class='celdaValorAttr' style='color: #7F7F7F'><?php echo($pago[0]['id']); ?></td>
    <td class='celdaNombreAttr'>Nº:</td>
    <td class='celdaValorAttr'><b><?php echo(number_format($pago[0]['id'],0,",",".")); ?></b></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Receptor:</td>
    <td class='celdaValorAttr'><?php echo($pago[0]['cajero']); ?></td>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><?php echo($pago[0]['fecha']); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Sociedad Responsable</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Razón Social:</td>
    <td class='celdaValorAttr'><?php echo($pago[0]['razon_social']); ?></td>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($pago[0]['rut']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Socios:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($socios); ?></td>
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr class='filaTituloTabla'><td colspan="7" class='tituloTabla'>Detalle</td></tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' rowspan="2" style='color: #7F7F7F'>ID<br>Cobro</td>
    <td class='tituloTabla' rowspan="2">Vencimiento</td>
    <td class='tituloTabla' rowspan="2">Nº<br>Cuota</td>
    <td class='tituloTabla' colspan="2">Compromiso</td>
    <td class='tituloTabla' rowspan="2">Acción</td>
    <td class='tituloTabla' rowspan="2">Monto</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>N°</td>
    <td class='tituloTabla'>Año</td>
  </tr>
  <?php echo($HTML); ?>
  <tr><td colspan="7" class='celdaValorAttr'>&nbsp;</td></tr>
  <tr>
    <td colspan="4">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="2" style="text-align: right; ">Monto Comprobante</td>
    <td class='celdaValorAttr' style='text-align: right;'><b>$<?php echo($monto_total); ?></b></td>
  </tr>
  <tr>
    <td colspan="4">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="3" style="text-align: center;">Forma de Pago</td>
  </tr>
  <tr>
    <td colspan="4">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="2">Efectivo</td>
    <td class='celdaValorAttr' style='text-align: right;'>$<?php echo($efectivo); ?></td>
  </tr>
  <tr>
    <td  colspan="4">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="2">Cheque(s)</td>
    <td class='celdaValorAttr' style='text-align: right;'>$<?php echo($cheque); ?></td>
  </tr>
  <tr>
    <td  colspan="4">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="2">Transferencia</td>
    <td class='celdaValorAttr' style='text-align: right;'>$<?php echo($transferencia); ?></td>
  </tr>
</table>
<?php if (count($cheques) > 0) { ?>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr class='filaTituloTabla'><td colspan="7" class='tituloTabla'>Cheques asociados</td></tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Fecha<br>Venc.</td>    
    <td class='tituloTabla'>Nº<br>Cuota</td>
    <td class='tituloTabla'>Monto</td>
    <td class='tituloTabla'>Inst.<br>Financiera</td>
    <td class='tituloTabla'>Nº Cuenta</td>
    <td class='tituloTabla'>Nº Docto.</td>
    <td class='tituloTabla'>Emisor</td>
  </tr>
  <?php echo($HTML_cheques); ?>
</table>
<?php } ?>


<!-- Fin: <?php echo($modulo); ?> -->
