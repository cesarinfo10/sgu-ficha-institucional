<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$codigo_barras = $_REQUEST["codigo_barras"];

if ($nuevo_estado == "") { $nuevo_estado = 'Firmado'; }

$mensaje = "";
if (!empty($codigo_barras)) {
	$SQL_contrato = "SELECT to_char(firmado_fecha,'DD-tmMon-YYYY HH24:MI') AS firmado_fecha,u.nombre||' '||u.apellido AS firmado_usuario 
	                 FROM finanzas.contratos AS c
					 LEFT JOIN usuarios AS u ON u.id=firmado_id_usuario
					 WHERE c.id=$codigo_barras AND firmado";
	$contrato = consulta_sql($SQL_contrato);
	if (count($contrato) > 0) {
		echo(msje_js("ATENCIÓN: Este contrato ya está firmado. El registro lo realizó {$contrato[0]['firmado_usuario']} el {$contrato[0]['firmado_fecha']}"));		
	} else {
		$SQL_upd_contrato = "UPDATE finanzas.contratos SET firmado=true,firmado_fecha=now(),firmado_id_usuario={$_SESSION['id_usuario']} WHERE id=$codigo_barras";
		if (consulta_dml($SQL_upd_contrato) > 0) {
			$mensaje = "Se ha marcado como firmado el contrato N° $codigo_barras";
		}

	}
}
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="nuevo_estado" value="<?php echo($nuevo_estado); ?>">
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px;'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='text-align: right'>Código de Barras:</td>
    <td class='celdaValorAttr' bgcolor='#FFFFFF'><input type="text" name="codigo_barras" id="codigo_barras" size="20" value="" class='boton'></td>
  </tr>
</table>
<div class='texto'>
  <?php echo("$mensaje"); ?>
</div>

<script>document.getElementById("codigo_barras").focus();document.getElementById("codigo_barras").select();</script>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
