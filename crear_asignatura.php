<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");
$bdcon = pg_connect("dbname=regacad" . $authbd);

if ($_REQUEST['crear'] <> "") {
	$codigo = $_REQUEST['codigo'];
	$SQLtxt = "SELECT nombre FROM asignaturas WHERE codigo='$codigo';";
	$resultado = pg_query($bdcon, $SQLtxt);
	$filas = pg_numrows($resultado);
	if ($filas > 0) {
		$asig = pg_fetch_all($resultado);
		$nombre_asig = $asig[0]['nombre'];
		echo(msje("Intenta crear una Asignatura con un c&oacute;digo ya existente en la
		           base de datos ($nombre_asig).<br><br>A continuaci&oacute;n puede
		           editar nuevamente los datos del ingreso."));
	} else {
		$aCampos = array("codigo","nombre","id_profesor","id_carrera");	
		$SQLinsert = "INSERT INTO asignaturas " . arr2sqlinsert($_REQUEST,$aCampos);
		$resultado = pg_query($bdcon, $SQLinsert);
		if (!$resultado) {
			echo(msje(pg_last_error()));
		} else {
			$filas = pg_affected_rows($resultado);
		};
		if ($filas > 0) {
			echo(msje("Se ha creado una nueva asignatura con los datos ingresados<br>
			           Pinche <a class='enlaces' href='principal.php?modulo=gestion_asignaturas'>aqu&iacute;</a>
			           para voler al Gestor de Asignaturas"));
			exit;
		};
	};
};
	
$asignatura = utf2html(pg_fetch_all($resultado));
$SQLtxt2 = "SELECT id,nombre FROM carreras ORDER BY nombre;";
$resultado2 = pg_query($bdcon, $SQLtxt2);
$filas2 = pg_numrows($resultado2);
if ($filas2 > 0) {
	$carreras = pg_fetch_all($resultado2);
	$SQLtxt3 = "SELECT * FROM profesores ORDER BY nombre;";
	$resultado3 = pg_query($bdcon, $SQLtxt3);
	$filas3 = pg_numrows($resultado3);
	if ($filas3 > 0) {
		$profesores = pg_fetch_all($resultado3);
	};
};

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="post" onSubmit="return enblanco2('codigo','nombre','id_carrera');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type="submit" name="crear" value="Crear"></td>
    <td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar"  onClick="cancelar_guardar();"></td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="celdaNombreAttr">C&oacute;digo:</td>
    <td class="celdaValorAttr">
      <input type="text" name="codigo" value="<?php echo($_REQUEST['codigo']); ?>" maxlength="10" size="10" onChange="cambiado();" onKeyUp="var valor=this.value; this.value=valor.toUpperCase();">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nombre:</td>
    <td class="celdaValorAttr">
      <input type="text" name="nombre" value="<?php echo($_REQUEST['nombre']); ?>" size="40" onChange="cambiado();">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Profesor:</td>
    <td class="celdaValorAttr">
      <select name="id_profesor" onChange="cambiado();">
        <option value="">-- Seleccione --</option>
        <?php echo(select($profesores,$_REQUEST['id_profesor'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Carrera:</td>
    <td class="celdaValorAttr">
      <select name="id_carrera" onChange="cambiado();">
        <option value="">-- Seleccione --</option>
        <?php echo(select($carreras,$_REQUEST['id_carrera'])); ?>
      </select>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

