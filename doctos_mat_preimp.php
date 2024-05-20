<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");


$regimen  = $_REQUEST["regimen"];
$cantidad = $_REQUEST["cantidad"];

if ($_REQUEST['guardar'] == "Obtener documentos" && !empty($regimen) && is_numeric($cantidad)) {

		$fmt_contrato = "fmt/contrato_preimp_$regimen.php";
		$fmt_pagare   = "fmt/pagare_colegiatura_preimp_$regimen.php";

		if (file_exists($fmt_contrato) && file_exists($fmt_pagare)) {
				$sesion = md5(microtime());
				$SQL_ins = "";
				for ($x=1;$x<=$cantidad;$x++) {
						$SQL_ins .= "INSERT INTO finanzas.contratos_preimp (regimen,id_usuario,sesion) VALUES ('$regimen',{$_SESSION['id_usuario']},'$sesion');";
				}
				if (consulta_dml($SQL_ins) > 0) {

						echo(js("window.open('contratos_preimp.php?sesion=$sesion&regimen=$regimen');"));
						
				} else {
						echo(msje_js("ERROR: No fue posible generar los documentos solicitados."));
						exit;
				}
		} else {
				echo(msje_js("ERROR: No existe plantilla de Contrato o Pagaré para el regimen seleccionado ($regimen)."));
				exit;
		}
}

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<div style='margin-top: 5px;'>
	<input type="submit" name="guardar" value="Obtener documentos">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px;'>
	<tr class='filaTituloTabla'>
		<td class='tituloTabla' style='text-align: right'>Régimen:</td>
		<td class='celdaValorAttr' bgcolor='#FFFFFF'>
			<select class="filtro" name="regimen" required>
				<option value="">-- Seleccione --</option>
				<?php echo(select($REGIMENES,$regimen)); ?>
			</select>
		</td>
	</tr>
	<tr class='filaTituloTabla'>
		<td class='tituloTabla' style='text-align: right'>Cantidad:</td>
		<td class='celdaValorAttr' bgcolor='#FFFFFF'><input type="number" name="cantidad" min="1" max="300" value="<?php echo($cantidad); ?>" class="boton" required></td>
	</tr>

</table> 
</form>

<!-- Fin: <?php echo($modulo); ?> -->
