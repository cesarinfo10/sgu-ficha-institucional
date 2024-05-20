<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$id_escuela = $_REQUEST['id_escuela'];
if (!is_numeric($id_escuela)) {
	echo("<script language='JavaScript1.2'>location.href='principal.php?modulo=gestion_escuelas';</script>");
	exit;
};

$bdcon = pg_connect("dbname=regacad" . $authbd);
            
$SQLtxt = "SELECT id as \"identificador interno\",nombre,director FROM vista_escuelas WHERE id=$id_escuela;";
$resultado = pg_query($bdcon, $SQLtxt);
$filas = pg_numrows($resultado);
if ($filas > 0) {
	$escuela = pg_fetch_all($resultado);
	$nombre_escuela = $escuela[0]['nombre'];
	$SQLtxt2 = "SELECT id,nombre,alias,coordinador,escuela,activa 
	            FROM vista_carreras WHERE id_escuela=$id_escuela;";
	$resultado2 = pg_query($bdcon, $SQLtxt2);
	$filas2 = pg_numrows($resultado2);
	if ($filas2 > 0) {
		$carreras = pg_fetch_all($resultado2);
	};
	$cant_campos2 = pg_num_fields($resultado2);
};

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>: <?php echo($escuela[0]['nombre']); ?>
</div><br>
<table class="tabla">
<form name="formulario" action="principal.php" method="post">
<input type="hidden" name="modulo" value="editar_escuela">
<input type="hidden" name="id_escuela" value="<?php echo($id_escuela); ?>">
  <tr>
<?php
	if ($_SESSION['tipo'] == 0) {
?>
    <td class="tituloTabla"><input type="submit" name="editar" value="Editar"></td>
<?php
	};
?>
    <td class="tituloTabla"><input type="button" name="volver" value="Volver" onClick="history.back()"></td>
  </tr>
</form>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
<?php
	for($x=0;$x<pg_num_fields($resultado);$x++) {
		$nombre_campo = ucfirst(pg_field_name($resultado,$x));
		$valor_campo = $escuela[0][pg_field_name($resultado,$x)];
		echo("  <tr>");
		echo("    <td class='celdaNombreAttr'>$nombre_campo:</td>");
		echo("    <td class='celdaValorAttr'>&nbsp;$valor_campo</td>");
		echo("  </tr>");
	};
?>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
	<?php
		for ($y=1;$y<$cant_campos2;$y++) {
			$nombre_campo = ucfirst(pg_field_name($resultado2,$y));
			echo("<td class='tituloTabla'>$nombre_campo</td>\n");
		};
	?>
  </tr>
<?php
	for ($x=0; $x<$filas2; $x++) {
		$enl = "$enlbase=ver_carrera&id_carrera=" . $carreras[$x]['id'];
		$enlace = "<a class='enlitem' href='$enl'>";
		echo("  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n");
		for ($z=1;$z<$cant_campos2;$z++) {
			$alinear="";
			if (strncmp(pg_field_type($resultado2,$z),"int",3) == 0 || pg_field_type($resultado2,$z) == "date") {
				$alinear=" align='right'";
			};			 
			echo("    <td class='textoTabla'$alinear>&nbsp;$enlace" . $carreras[$x][pg_field_name($resultado2,$z)] . "</a></td>\n");			
		};
	};
?>
</table>
<!-- Fin: <?php echo($modulo); ?> -->

