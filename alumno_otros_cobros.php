<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_alumno = $_REQUEST['id_alumno'];

$alumno = consulta_sql("SELECT id,rut,nombre,estado FROM vista_alumnos WHERE id=$id_alumno");
extract($alumno[0]);

if ($_REQUEST['eliminar'] === "Eliminar Cobros Seleccionados") {
	$aIds_cobros = array();
	$x = 0;
	foreach ($_REQUEST['id_cobro'] AS $id_cobro => $valor) {
		if ($valor == "on") { 
			$aIds_cobros[$x] = $id_cobro;
			$x++;
		 }
	 }
	 $ids_cobros = implode(",",$aIds_cobros);
	 
	 consulta_dml("DELETE FROM finanzas.cobros WHERE id_alumno=$id_alumno AND id IN ($ids_cobros)");	
}

$SQL_cobros = "SELECT c.id,to_char(c.fecha_venc,'DD-tmMon-YYYY') as fec_venc,c.fecha_venc,g.nombre AS glosa,monto,nro_cuota,
                      CASE WHEN pagado THEN 'Si' ELSE 'No' END AS pagado,
                      CASE WHEN abonado THEN 'Si' ELSE 'No' END AS abonado,
                      monto_abonado,id_glosa,u.nombre_usuario AS emisor,c.id_usuario AS id_emisor,
                      (SELECT char_comma_sum(coalesce(nro_boleta::text,'')) FROM finanzas.pagos WHERE id IN (SELECT id_pago FROM finanzas.pagos_detalle WHERE id_cobro=c.id)) AS nro_boleta,
                      (SELECT char_comma_sum(id_pago::text) FROM finanzas.pagos_detalle WHERE id_cobro=c.id) AS id_pago,
                      (SELECT char_comma_sum(to_char(fecha,'DD-tmMon-YYYY')) FROM finanzas.pagos WHERE id IN (SELECT id_pago FROM finanzas.pagos_detalle WHERE id_cobro=c.id)) AS fecha_pago
               FROM finanzas.cobros AS c
               LEFT JOIN finanzas.glosas AS g ON g.id=c.id_glosa
               LEFT JOIN usuarios AS u ON u.id=c.id_usuario
               WHERE c.id_alumno=$id_alumno
               ORDER BY c.fecha_venc,c.id";
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
	
	if (!$primer_nopag && $pagado == "No" && $abonado == "No" && ($id_glosa==2 || $id_glosa==21)) {
		$enl_fec_venc = "$enlbase_sm=form_matricula_cambiar_fec_venc&id_contrato=$id_contrato&id_cobro=$id";
		$fec_venc     = "<a id='sgu_fancybox_small' href='$enl_fec_venc' class='boton' title='Prorrogar vencimiento' style='font-size: 8pt'>$fec_venc</a>";
		$primer_nopag = true;
	}

	if ($pagado == "Si") {
		$total_pagado += $monto;
		$abonado = "";
	} elseif ($abonado == "Si") {
		$total_pagado += $monto_abonado;
		$deuda_total += $monto - $monto_abonado;
	} else {
		$deuda_total += $monto - $monto_abonado;
	}

	//$nro_boleta = str_replace(",","<br>",$nro_boleta);
	//$id_pago    = str_replace(",","<br>",$id_pago);
	$fecha_pago = str_replace(",","<br>",$fecha_pago);
	
	$nro_boleta = explode(",",$nro_boleta);
	$id_pago    = explode(",",$id_pago);
	

	$nro_bol = "";
	for($i=0;$i<count($nro_boleta);$i++) {
		$nro_bol .= "<a href='$enlbase_sm=ver_pago&id_pago={$id_pago[$i]}' id='sgu_fancybox_medium' class='enlaces'>{$nro_boleta[$i]}</a><br>";
	}
	$nro_boleta = $nro_bol;
	$id_pago    = str_replace(",","<br>",implode(",",$id_pago));

	$elim = "";
	if ($pagado == "No" && $abonado == "No") {
		$elim = "<input type='checkbox' id='marcar' name='id_cobro[$id]'>";
	}
	
	$HTML = "<tr class='filaTabla' style='vertical-align: middle'>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: center;'>$elim</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: center; color: #7F7F7F'>$id</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle'>$emisor</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='right'>$fec_venc</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle'>$glosa</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='right'>$nro_cuota</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='right'>$$monto_f</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='center'><span class='$pagado'>$pagado</span></td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='center'><span class='$abonado'>$abonado <small>$saldo_f</small></span></td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='right'>$nro_boleta</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: right; color: #7F7F7F'>$id_pago</td>\n"
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
$HTML_cobros_vencidos .= "<tr class='filaTabla'><td class='textoTabla' align='right' colspan='7'>"
					  .  "  <b><span class='Si'>Total Pagado: $$total_pagado</span></b></td><td class='textoTabla' colspan='5'> </td></tr>\n"
                      .  "<tr class='filaTabla'><td class='textoTabla' align='right' colspan='7'>"
					  .  "  <b><span class='No'>Deuda Vencida: $$deuda_vencida</span></b></td><td class='textoTabla' colspan='5'> </td></tr>\n";

$deuda_total = number_format($deuda_total,0,',','.');                      
$HTML_cobros .= "<tr class='filaTabla'><td class='textoTabla' align='right' colspan='7'>"
			 .  "  <b>Deuda Total <small>(incluye Deuda Vencida)</small>: $$deuda_total</b></td><td class='textoTabla' colspan='5'> </td></tr>\n";

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name='formulario' action='principal_sm.php' method='get'>
<input type='hidden' name='modulo' value='<?php echo($modulo); ?>'>
<input type='hidden' name='id_alumno' value='<?php echo($id_alumno); ?>'>

<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="6" style="text-align: center; ">Antecedentes Personales del Alumno(a)</td></tr>
  <tr>
    <td class='celdaNombreAttr'><u>RUT:</u></td>
    <td class='celdaValorAttr'><?php echo($rut); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($id_alumno); ?></td>
    <td class='celdaNombreAttr'><u>Nombre:</u></td>
    <td class='celdaValorAttr' colspan="3"><?php echo($nombre); ?></td>
  </tr>
</table>
<table class="tabla" style="margin-top: 5px">
  <tr>
	<td class='celdaFiltro'>
      <input type='button' onClick="window.location='<?php echo("$enlbase_sm=alumno_otros_cobros_agregar&id_alumno=$id_alumno"); ?>';" value="Agregar">
    </td>
    <td class='celdaFiltro'>
      <input type='submit' name='eliminar' value='Eliminar Cobros Seleccionados'
          onClick="return confirm('¿Está seguro de eliminar los cobros seleccionados?');">
      <a href='<?php echo("$enlbase_sm=emitir_boleta_electronica&rut=$rut"); ?>' class='boton' id='sgu_fancybox_medium'>Pagar (BOL-E)</a>
      <input type='button' onClick="window.location='<?php echo("$enlbase_sm=gestion_caja2&rut=$rut&validar=Validar"); ?>';" value="Pagar">
    </td>
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr class='filaTituloTabla'><td class='tituloTabla' colspan="12">Otros Cobros (Otros Ingresos)</td></tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'><!-- <input type='checkbox' name='marcar_todos' onChange="marcar_todos(this.checked);"> -->&nbsp;</td>
    <td class='tituloTabla' style='color: #7F7F7F'>ID</td>
    <td class='tituloTabla'>Emisor</td>
    <td class='tituloTabla'>Fecha<br>Venc.</td>
    <td class='tituloTabla'>Glosa</td>
    <td class='tituloTabla'>Nº<br>Cuota</td>
    <td class='tituloTabla'>Monto</td>
    <td class='tituloTabla'>Pagado?</td>
    <td class='tituloTabla'>Abono? <small>(saldo)</small></td>
    <td class='tituloTabla'>Nº<br>Boleta</td>
    <td class='tituloTabla' style='color: #7F7F7F'>ID<br>Pago</td>
    <td class='tituloTabla'>Fecha<br>Pago</td>
  </tr>
  <tr class='filaTabla'><td colspan="12" class='textoTabla'><i>Cobros Vencidos</i></td></tr>
  <?php echo($HTML_cobros_vencidos); ?>
  <tr class='filaTabla'><td colspan="12" class='textoTabla'><i>Cobros por Vencer</i></td></tr>
  <?php echo($HTML_cobros); ?>
</table>
</form>

<script>
function marcar_todos(estado) {
	document.getElementById('marcar').checked=estado;
}
</script>
<!-- Fin: <?php echo($modulo); ?> -->
