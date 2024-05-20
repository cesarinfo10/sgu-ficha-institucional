<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$id_lt = $_REQUEST['id_lt'];
if (!is_numeric($id_lt)) {
	echo(js("location.href='principal.php?modulo=gestion_carreras';"));
	exit;
};

$bdcon = pg_connect("dbname=regacad" . $authbd);

$SQLtxt = "SELECT id,nombre,escuela FROM vista_lineas_tematicas WHERE id=$id_lt;";
$resultado = pg_query($bdcon, $SQLtxt);
$filas = pg_numrows($resultado);
if ($filas > 0) {
	$lt = utf2html(pg_fetch_all($resultado));
};
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>: <?php echo($lt[0]['nombre']); ?>  
</div><br>
<table class="tabla">
<form name="formulario" action="principal.php" method="post">
<input type="hidden" name="modulo" value="editar_lt">
<input type="hidden" name="id_lt" value="<?php echo($id_lt); ?>">
  <tr>
<?php
	if ($_SESSION['tipo'] == 0) {
?>
    <td class="tituloTabla"><input type="submit" name="editar" value="Editar"></td>
<?php
	};
?>
    <td class="tituloTabla">
      <input type="button" name="volver" value="Volver" onClick="<?php echo($_REQUEST['enl_volver']); ?>">
    </td>
  </tr>
</form>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
<?php
	for($x=0;$x<pg_num_fields($resultado);$x++) {
		$nombre_campo = ucfirst(pg_field_name($resultado,$x));
		$valor_campo = $lt[0][pg_field_name($resultado,$x)];
		echo("  <tr>\n");
		echo("    <td class='celdaNombreAttr'>$nombre_campo:</td>\n");
		echo("    <td class='celdaValorAttr'>&nbsp;$valor_campo</td>\n");
		echo("  </tr>\n");
	};
?>
</table>
<!-- Fin: <?php echo($modulo); ?> -->

