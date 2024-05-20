<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");
$bdcon = pg_connect("dbname=regacad" . $authbd);

$cod_asignatura = $_REQUEST['cod_asignatura'];
$id_profesor = $_REQUEST['id_profesor'];
if ($cod_asignatura == "") {
	echo("<script language='JavaScript1.2'>location.href='principal.php?modulo=gestion_asignaturas';</script>");
	exit;
};

if ($_REQUEST['agregar'] <> "") {
	$SQLtxt = "SELECT cod_asignatura FROM asig_profes WHERE cod_asignatura='$cod_asignatura' AND id_profesor=$id_profesor;";
	$resultado = pg_query($bdcon, $SQLtxt);
	$filas = pg_numrows($resultado);
	if ($filas > 0) {
		$mensaje_error = "El profesor que intenta agregar, ya está asociado a esta asignatura";
		echo("<script language='Javascript1.2'>alert('$mensaje_error');</script>");
	} else {
		$aCampos = array("cod_asignatura","id_profesor");
		$SQLinsert = "INSERT INTO asig_profes " . arr2sqlinsert($_REQUEST,$aCampos);
		$resultado = pg_query($bdcon, $SQLinsert);
		if (!$resultado) {
			echo(msje(pg_last_error()));
		} else {
			$filas = pg_affected_rows($resultado);
		};
	};	
};

if ($_REQUEST['borrar'] == "Borrar") {
	$SQLdelete = "DELETE FROM asig_profes WHERE id_profesor=$id_profesor AND cod_asignatura='$cod_asignatura';";
	$resultado = pg_query($bdcon, $SQLdelete);
	if (!$resultado) {
		echo(msje(pg_last_error()));
	};
};
	
$SQLtxt = "SELECT codigo,nombre FROM asignaturas WHERE codigo='$cod_asignatura';";
$resultado = pg_query($bdcon, $SQLtxt);
$filas = pg_numrows($resultado);
if ($filas > 0) {
	$asignatura = utf2html(pg_fetch_all($resultado));
};

$SQLtxt2 = "SELECT id_profesor,profesor FROM vista_asig_profes WHERE cod_asignatura='$cod_asignatura' ORDER BY profesor;";
$resultado2 = pg_query($bdcon, $SQLtxt2);
$filas2 = pg_numrows($resultado2);
if ($filas2 > 0) {
	$asig_profes = utf2html(pg_fetch_all($resultado2));
};

$SQLtxt3 = "SELECT * FROM profesores ORDER BY nombre;";
$resultado3 = pg_query($bdcon, $SQLtxt3);
$filas3 = pg_numrows($resultado3);
if ($filas3 > 0) {
	$profesores = utf2html(pg_fetch_all($resultado3));
};

$enlVolver = "$enlbase=ver_asignatura&cod_asignatura=$cod_asignatura";
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>: <?php echo($asignatura[0]['nombre']); ?>
</div><br>

<table class="tabla">
  <tr>
    <td class="tituloTabla">
      <input type="button" name="volver" value="Volver" onClick="window.location='<?php echo($enlVolver); ?>';">
    </td>
  </tr>
</table><br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
<?php
	for($x=0;$x<pg_num_fields($resultado);$x++) {
		$nombre_campo = ucfirst(pg_field_name($resultado,$x));
		$valor_campo = $asignatura[0][pg_field_name($resultado,$x)];
		echo("  <tr>\n");
		echo("    <td class='celdaNombreAttr'>$nombre_campo:</td>\n");
		echo("    <td class='celdaValorAttr'>&nbsp;$valor_campo</td>\n");
		echo("  </tr>\n");
	};
?>
</table><br>
<form name="formulario" action="principal.php" method="post" onSubmit="return enblanco2('id_profesor');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="cod_asignatura" value="<?php echo($cod_asignatura); ?>">
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Profesores asociados</td>
  </tr>
<?php
	if ($filas2 > 0) {
		for ($x=0; $x<$filas2; $x++) {
			$enl = "$enlbase=$modulo&cod_asignatura=$cod_asignatura&borrar=Borrar&id_profesor=" . $asig_profes[$x]['id_profesor'];
			echo("  <tr class='filaTabla' onClick=\"return confirmar_borrar('$enl','" . $asig_profes[$x]['profesor'] . "');\">\n");
			echo("    <td class='textoTabla'>&nbsp;" . $asig_profes[$x]['profesor'] . "</td>\n");
			echo("  </tr>\n");
		};
	} else {
		echo("  <tr>\n");
		echo("    <td class='textoTabla'>No hay profesores asignados para esta asignatura</td>\n");
		echo("  </tr>\n");
	};
?>
  <tr>
    <td class='textoTabla'>
      <select name="id_profesor">
        <option value=''>-- Seleccione --</option>
        <?php echo(select($profesores,null)); ?>
      </select>
      <input type="submit" name="agregar" value="Agregar">
    </td>
  </tr>
</table>
<div class="texto">Pinche sobre el nombre del profesor para borrarlo de la lista</div>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

