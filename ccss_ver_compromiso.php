<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

if (!is_numeric($_REQUEST['id_compromiso'])) {
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$id_compromiso = $_REQUEST['id_compromiso'];

$SQL_socios = "SELECT char_comma_sum(rut||' '||nombres||' '||apellidos) 
               FROM finanzas.ccss_socios 
               WHERE id IN (SELECT id_socio FROM finanzas.ccss_sociedades_socios WHERE id_sociedad=s.id)";

$SQL_compromiso = "SELECT c.id,c.ano,c.monto,c.monto_adicional,c.monto_descuento,
                          to_char(fecha,'DD-tmMon-YYYY') AS fecha,s.razon_social,s.rut,comentarios,
                          ($SQL_socios) AS socios
                    FROM finanzas.ccss_compromisos AS c
                    LEFT JOIN finanzas.ccss_sociedades AS s ON s.id=c.id_sociedad
                    WHERE c.id=$id_compromiso";
$compromiso     = consulta_sql($SQL_compromiso);

if (count($compromiso) > 0) {
	extract($compromiso[0]);
	$socios = str_replace(",","<br>",$socios);
} else {
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$SQL_cobros = "SELECT id,fecha_venc,to_char(fecha_venc,'DD-tmMon-YYYY') AS fec_venc,nro_cuota,monto,
                      CASE WHEN pagado  THEN 'Si' ELSE 'No' END AS pagado,
                      CASE WHEN abonado THEN 'Si' ELSE 'No' END AS abonado,
                      monto_abonado,
                      (SELECT char_comma_sum(coalesce(id_pago::text,'')) FROM finanzas.ccss_pagos_detalle WHERE id_cobro=cc.id) AS nro_comprobante,
                      (SELECT char_comma_sum(to_char(p.fecha,'DD-tmMon-YYYY')) FROM finanzas.ccss_pagos_detalle AS pd LEFT JOIN finanzas.ccss_pagos AS p ON p.id=pd.id_pago WHERE pd.id_cobro=cc.id) AS fecha_pago
               FROM finanzas.ccss_cobros AS cc
               WHERE id_compromiso=$id_compromiso
               ORDER BY fecha_venc";
$cobros     = consulta_sql($SQL_cobros);

$HTML_cobros = $HTML_cobros_vencidos = "";
$deuda_total = $deuda_vencida = $total_pagado = 0;
$primer_nopag = false;
for ($x=0;$x<count($cobros);$x++) {
	extract($cobros[$x]);

	$monto_f = number_format($monto,0,',','.');

	$saldo_f = "";
	$saldo   = 0;
	if ($abonado == "Si") { 
		$saldo   = $monto - $monto_abonado;
		$saldo_f = "($".number_format($saldo,0,',','.').")";
	}
	
	/*
	if (!$primer_nopag && $pagado == "No" && $abonado == "No") {
		$enl_fec_venc = "$enlbase_sm=ccss_compromiso_cambiar_fecvenc&id_compromiso=$id_comprimso&id_cobro=$id";
		$fec_venc     = "<a id='sgu_fancybox_small' href='$enl_fec_venc' class='boton' title='Prorrogar vencimiento' style='font-size: 8pt'>$fec_venc</a>";
		$primer_nopag = true;
	}
	*/

	if ($pagado == "Si") {
		$total_pagado += $monto;
		$abonado = "";
	} elseif ($abonado == "Si") {
		$total_pagado += $monto_abonado;
		$deuda_total += $monto - $monto_abonado;
	} else {
		$deuda_total += $monto - $monto_abonado;
	}

	$nro_comprobante = explode(",",$nro_comprobante);
	$fecha_pago = str_replace(",","<br>",$fecha_pago);
	
	$nro_comp = "";
	for($i=0;$i<count($nro_comprobante);$i++) {
		$nro_comprobante[$i] = trim($nro_comprobante[$i]);
		$nro_comp .= "<a href='$enlbase_sm=ccss_ver_pago&id_pago={$nro_comprobante[$i]}' id='sgu_fancybox_medium' class='enlaces'>{$nro_comprobante[$i]}</a><br>";
	}
	$nro_comprobante = $nro_comp;
	
	$id_pago = str_replace(",","<br>",implode(",",$id_pago));
	
	$HTML = "<tr class='filaTabla' style='vertical-align: middle'>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: center; color: #7F7F7F'>$id</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='right'>$fec_venc</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='right'>$nro_cuota</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='right'>$$monto_f</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='center'><span class='$pagado'>$pagado</span></td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='center'><span class='$abonado'>$abonado <small>$saldo_f</small></span></td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='right'>$nro_comprobante</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='right'>$fecha_pago</td>\n"
		  . "</tr>\n";

	if (strtotime($fecha_venc) < time()) { 
		$HTML_cobros_vencidos .= $HTML;
		if ($pagado == "No") { $deuda_vencida += $monto - $monto_abonado; }
	} else { 
		$HTML_cobros .= $HTML;
	}
}
$deuda_vencida = number_format($deuda_vencida,0,',','.');
$total_pagado  = number_format($total_pagado,0,',','.');
$HTML_cobros_vencidos .= "<tr><td class='textoTabla' align='right' colspan='4'>"
					  .  "  <b><span class='Si'>Compromiso Pagado: $$total_pagado</span></b></td><td class='textoTabla' colspan='4'> </td></tr>\n"
                      .  "<tr><td class='textoTabla' align='right' colspan='4'>"
					  .  "  <b><span class='No'>Compromiso Adeudado: $$deuda_vencida</span></b></td><td class='textoTabla' colspan='4'> </td></tr>\n";

$deuda_total = number_format($deuda_total,0,',','.');                      
$HTML_cobros .= "<tr><td class='textoTabla' align='right' colspan='4'>"
			 .  "  <b>Compromiso Pendiente: $$deuda_total</b><br> <small>(incluye Compromiso Adeudado)</small></td><td class='textoTabla' colspan='4'> </td></tr>\n";

$monto_total = $compromiso[0]['monto'] - $monto_descuento + $monto_adicional;

$monto_adicional        = number_format($monto_adicional,0,',','.');
$monto_descuento        = number_format($monto_descuento,0,',','.');
$compromiso[0]['monto'] = number_format($compromiso[0]['monto'],0,',','.');
$monto_cobrable         = number_format($monto_total,0,',','.');

?>

<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<!--
<div style='margin-top: 5px'>
  <a href='<?php echo("$enlbase_sm=ccss_editar_sociedad&id_sociedad=$id_sociedad"); ?>' class='boton'>Editar</a>
  <a href='#' onClick='parent.jQuery.fancybox.close();' class='boton'>Cerrar</a>
</div>
-->
<table bgcolor='#ffffff' cellspacing='1' cellpadding='1' class='tabla' style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style='text-align: center'>Antecedentes del Compromiso</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Id:</td>
    <td class='celdaValorAttr'><?php echo($compromiso[0]['id']); ?></td>
    <td class='celdaNombreAttr'>Año:</td>
    <td class='celdaValorAttr'><?php echo($compromiso[0]['ano']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Monto:</td>
    <td class='celdaValorAttr' align='right'>$<?php echo($compromiso[0]['monto']); ?></td>
    <td class='celdaNombreAttr'>Fecha Registro:</td>
    <td class='celdaValorAttr'><?php echo($compromiso[0]['fecha']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Monto Adicional:</td>
    <td class='celdaValorAttr' align='right'>$<?php echo($monto_adicional); ?></td>
    <td class='celdaNombreAttr'>Monto Descuento:</td>
    <td class='celdaValorAttr' align='right'>$<?php echo($monto_descuento); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Total Compromiso:</td>
    <td class='celdaValorAttr' align='right'>$<?php echo($monto_cobrable); ?></td>
    <td class='celdaValorAttr' colspan='2'>&nbsp;</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style='text-align: center'>Antecedentes de la Sociedad Responsable</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Razón Social:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($compromiso[0]['razon_social']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Socio(s):</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($socios); ?>
  </tr>
<?php if ($comentarios <> "") { ?>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style='text-align: center'>Comentarios</td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="4" style='text-align: justify'><?php echo(nl2br($comentarios)); ?></td>
  </tr>
<?php } ?>
</table>
<div class="celdaFiltro" style="float: left ; margin-top: 5px">
  <b>Acciones:</b><br>
<?php
	echo("  <a href='$enlbase_sm=ccss_pagar&rut=$rut&id_sociedad=$id_sociedad&ano=$ano' class='boton' id='sgu_fancybox_small'>Registrar pago</a> ");
	echo("  <a href='$enlbase_sm=ccss_condonar&id_compromiso=$id' class='boton' id='sgu_fancybox_small'>Descuento</a> ");

?>
</div><br><br><br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td colspan="10" class='tituloTabla'>
      Cuotas Sociales<!-- <br><small>Compromiso Nº <?php echo("{$compromiso[0]['id']} $rut $razon_social"); ?></small> -->
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='color: #7F7F7F'>ID</td>
    <td class='tituloTabla'>F. Vencimiento</td>
    <td class='tituloTabla'>Nº Cuota</td>
    <td class='tituloTabla'>Monto</td>
    <td class='tituloTabla'>Pagado?</td>
    <td class='tituloTabla'>Abono? <small>(saldo)</small></td>
    <td class='tituloTabla'>Nº Comp.</td>
    <td class='tituloTabla'>F. Pago</td>
  </tr>
  <tr><td colspan="9" class='textoTabla'><i>Vencidas</i></td></tr>
  <?php echo($HTML_cobros_vencidos); ?>
  <tr><td colspan="9" class='textoTabla'><i>Por Vencer</i></td></tr>
  <?php echo($HTML_cobros); ?>
</table>

<!-- Fin: <?php echo($modulo); ?> -->
