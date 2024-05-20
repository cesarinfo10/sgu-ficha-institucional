<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$cod_asignatura = $_REQUEST['cod_asignatura'];
if ($cod_asignatura == "") {
	echo("<script language='JavaScript1.2'>location.href='principal.php?modulo=gestion_carreras';</script>");
	exit;
};

if ($_REQUEST['guardar'] <> "") {
	$aCampos = array("nombre","id_profesor","id_carrera");	
	$SQLupdate = "UPDATE asignaturas SET " . arr2sqlupdate($_REQUEST,$aCampos) . " WHERE codigo='$cod_asignatura';";
	$bdcon = pg_connect("dbname=regacad" . $authbd);
	$resultado = pg_query($bdcon, $SQLupdate);
	if (!$resultado) {
		echo(msje(pg_last_error()));
	} else {
		$filas = pg_affected_rows($resultado);
	};
	if ($filas > 0) {
		echo(msje("Se han guardado los cambios<br>Pinche 
		           <a class='enlaces' href='principal.php?modulo=ver_asignatura&cod_asignatura=$cod_asignatura'>aqu&iacute;</a>
		           para voler a ver la Asignatura"));
		exit;
	};
};
	
$bdcon = pg_connect("dbname=regacad" . $authbd);
$SQLtxt = "SELECT * FROM asignaturas WHERE codigo='$cod_asignatura';";
$resultado = pg_query($bdcon, $SQLtxt);
$filas = pg_numrows($resultado);
if ($filas > 0) {
	$asignatura = pg_fetch_all($resultado);
	$SQLtxt2 = "SELECT id,nombre FROM carreras ORDER BY nombre;";
	$resultado2 = pg_query($bdcon, $SQLtxt2);
	$filas2 = pg_numrows($resultado2);
	if ($filas2 > 0) {
		$carreras = pg_fetch_all($resultado2);
		$SQLtxt3 = "SELECT * FROM profesores ORDER BY nombre;";
		$resultado3 = pg_query($bdcon, $SQLtxt3);
		$filas3 = pg_numrows($resultado3);
		if ($filas3 > 0) {
			$profesores = utf2html(pg_fetch_all($resultado3));
		};
	};
};

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="post" onSubmit="return enblanco2('nombre','id_carrera');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="cod_asignatura" value="<?php echo($cod_asignatura); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>: <?php echo($asignatura[0]['nombre']); ?>
</div><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type="submit" name="guardar" value="Guardar" onClick="return confirmar_guardar();"></td>
    <td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar"  onClick="cancelar_guardar();"></td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="celdaNombreAttr">C&oacute;digo:</td>
    <td class="celdaValorAttr"><?php echo($asignatura[0]['codigo']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nombre:</td>
    <td class="celdaValorAttr">
      <input type="text" name="nombre" value="<?php echo($asignatura[0]['nombre']); ?>" size="40" onChange="cambiado();">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Profesor titular:</td>
    <td class="celdaValorAttr">
      <select name="id_profesor" onChange="cambiado();">
        <option value="">No tiene asignado</option>
        <?php echo(select($profesores,$asignatura[0]['id_profesor'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Carrera:</td>
    <td class="celdaValorAttr">
      <select name="id_carrera" onChange="cambiado();">
        <?php echo(select($carreras,$asignatura[0]['id_carrera'])); ?>
      </select>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

