<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

if ($_REQUEST['id_escuela'] <> "") {
	$condicion = "WHERE id_escuela=" . $_REQUEST['id_escuela'];
};

$bdcon = pg_connect("dbname=regacad" . $authbd);

$SQLtxt = "SELECT id,nombre,escuela FROM vista_lineas_tematicas $condicion;";
$resultado = pg_query($bdcon, $SQLtxt);
$filas = pg_numrows($resultado);
if ($filas > 0) {
	$lineas_tematicas = pg_fetch_all($resultado);
};

$SQLtxt2 = "SELECT id,nombre FROM escuelas;";
$resultado2 = pg_query($bdcon, $SQLtxt2);
$filas2 = pg_numrows($resultado2);
if ($filas2 > 0) {
	$escuelas = pg_fetch_all($resultado2);
};
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div><br>
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<div class="texto">
  Mostrando de la escuela: 
  <select name="id_escuela" onChange="submitform();">
    <option value="">Todas</option>
    <?php echo(select($escuelas,$_REQUEST['id_escuela'])); ?>
  </select>
</div>
</form><br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
	<?php
		for ($y=1;$y<pg_num_fields($resultado);$y++) {
			$nombre_campo = ucfirst(pg_field_name($resultado,$y));
			echo("<td class='tituloTabla'>$nombre_campo</td>\n");
		};
	?>
  </tr>
<?php
	for ($x=0; $x<$filas; $x++) {
		$enl = "$enlbase=editar_lt&enl_volver=history.back();&id_lt=" . $lineas_tematicas[$x]['id'];
		$enlace = "<a class='enlitem' href='$enl'>";
		echo("  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n");
		for($z=1;$z<pg_num_fields($resultado);$z++) {
			echo("    <td class='textoTabla'>&nbsp;$enlace" . $lineas_tematicas[$x][pg_field_name($resultado,$z)] . "</a></td>\n");
		};
		echo("  </tr>\n");
	};
	if ($filas == 0) {
		echo("  <tr><td colspan='2' class='textoTabla'>No hay líneas temáticas para el criterio de selección</td></tr>\n");
	};
?>
</table>
<!-- Fin: <?php echo($modulo); ?> -->

