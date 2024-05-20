<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$id_alumno = $_REQUEST['id_alumno'];
$anotacion = $_REQUEST['anotacion'];

if ($_REQUEST['guardar'] == "Guardar Anotación") {
	$fecha_hora = strftime("%x %X");
	$anotacion = "El $fecha_hora, $nombre_real_usuario anotó:\n"
	           . wordwrap($anotacion)."\n"
	           . "*****\n";
	$SQLupdate = "UPDATE alumnos SET anotaciones = coalesce(anotaciones,'')||'$anotacion' WHERE id=$id_alumno;";
	consulta_dml($SQLupdate);
	echo(js("window.location='$enlbase=ver_alumno&id_alumno=$id_alumno&vista=anotaciones';"));  
}

$SQL_alumno = "SELECT id,nombre,rut,carrera,malla_actual,id_malla_actual,id_carrera
               FROM vista_alumnos
               WHERE id=$id_alumno;";
$alumno = consulta_sql($SQL_alumno);

if (count($alumno) > 0) {
	$anotaciones = consulta_sql("SELECT anotaciones FROM alumnos WHERE id=$id_alumno;");
	if ($anotaciones[0]['anotaciones'] == "") {
		$anotaciones = "** Sin anotaciones aún **";
	} else {
		$anotaciones = nl2br($anotaciones[0]['anotaciones']);
	}
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="get" onSubmit="return enblanco2('anotacion');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_alumno" value="<?php echo($id_alumno); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type="submit" name="guardar" value="Guardar Anotación"></td>
    <td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar" onClick="history.back();"></td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="celdaNombreAttr">ID alumno:</td>
    <td class="celdaValorAttr"><?php echo($alumno[0]['id']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nombre:</td>
    <td class="celdaValorAttr"><?php echo($alumno[0]['nombre']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">RUT:</td>
    <td class="celdaValorAttr"><?php echo($alumno[0]['rut']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Carrrera:</td>
    <td class="celdaValorAttr"><?php echo($alumno[0]['carrera']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Anotaciones actuales:</td>
    <td class="celdaValorAttr"><?php echo($anotaciones); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nueva Anotación:</td>
    <td class="celdaValorAttr"><textarea class="grande" name="anotacion"></textarea></td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

