<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_carrera = $_REQUEST['id_carrera'];
if (!is_numeric($id_carrera)) {
	echo(js("location.href='principal.php?modulo=gestion_carreras';"));
	exit;
}

if ($_REQUEST['guardar'] <> "") {
	$aCampos = array("nombre","alias","jornada","id_coordinador","id_escuela","activa","id_malla_actual","admision","regimen","nombre_grado","nombre_titulo");
	$SQLupdate = "UPDATE carreras SET " . arr2sqlupdate($_REQUEST,$aCampos) . " WHERE id=$id_carrera;";
	if (consulta_dml($SQLupdate) == 1) {
		echo(msje_js("Se han guardado los cambios exitósamente"));
		echo(js("window.location='$enlbase=gestion_carreras';"));
		exit;
	}
}
	
$carrera = consulta_sql("SELECT * FROM carreras WHERE id=$id_carrera;");
if (count($carrera) == 0) {
	echo(js("location.href='$enlbase=gestion_carreras';"));
	exit;
}
extract($carrera[0]);

$escuelas      = consulta_sql("SELECT * FROM escuelas ORDER BY nombre;");
$coordinadores = consulta_sql("SELECT u.id,u.nombre||' '||apellido||' ('||gu.alias||')' as nombre FROM usuarios AS u LEFT JOIN gestion.unidades AS gu ON gu.id=u.id_unidad WHERE activo AND tipo in (1,2) ORDER BY u.nombre");
$mallas        = consulta_sql("SELECT id,ano AS nombre FROM mallas WHERE id_carrera=$id_carrera;");

$JORNADAS = array_merge($JORNADAS,array(array('id'=>'a','nombre'=>"Ambas")));

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="post" onSubmit="return enblanco2('nombre','alias');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_carrera" value="<?php echo($id_carrera); ?>">
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Guardar" onClick="return confirmar_guardar();">
  <input type="button" name="cancelar" value="Cancelar"  onClick="cancelar_guardar();">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class="celdaNombreAttr">ID:</td>
    <td class="celdaValorAttr"><?php echo($id); ?></td>
    <td class="celdaNombreAttr">Alias:</td>
    <td class="celdaValorAttr"><input type='text' size='10' name='alias' value='<?php echo($alias); ?>' class='boton'></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nombre:</td>
    <td class="celdaValorAttr" colspan='3'><input type='text' size='50' name='nombre' value='<?php echo($nombre); ?>' class='boton'></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Jornada:</td>
    <td class="celdaValorAttr">
      <select name="jornada" onChange="cambiado();" class='filtro'>
        <?php echo(select($JORNADAS,$jornada)); ?>
      </select>		
    </td>
    <td class="celdaNombreAttr">Regimen:</td>
    <td class="celdaValorAttr">
      <select name="regimen" onChange="cambiado();" class='filtro'>
        <?php echo(select($REGIMENES,$regimen)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Escuela:</td>
    <td class="celdaValorAttr" colspan='3'>
      <select name="id_escuela" onChange="cambiado();" class='filtro' style='max-width: 300px'>
        <?php echo(select($escuelas,$id_escuela)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Coordinador(a):</td>
    <td class="celdaValorAttr" colspan='3'>
      <select name="id_coordinador" onChange="cambiado();" class='filtro' style='max-width: 300px'>
        <?php echo(select($coordinadores,$id_coordinador)); ?>
      </select>		
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nombre Título:</td>
    <td class="celdaValorAttr" colspan='3'><input type='text' size='50' name='nombre_titulo' value='<?php echo($nombre_titulo); ?>' class='boton'></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nombre Grado:</td>
    <td class="celdaValorAttr" colspan='3'><input type='text' size='50' name='nombre_grado' value='<?php echo($nombre_grado); ?>' class='boton'></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Admisión:</td>
    <td class="celdaValorAttr">
      <select name="admision" onChange="cambiado();" class='filtro'>
        <?php echo(select($sino,$admision)); ?>
      </select>
    </td>
    <td class="celdaNombreAttr">Activa:</td>
    <td class="celdaValorAttr">
      <select name="activa" onChange="cambiado();" class='filtro'>
        <?php echo(select($sino,$activa)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Malla Actual:</td>
    <td class="celdaValorAttr" colspan='3'>
      <select name="id_malla_actual" onChange="cambiado();" class='filtro'>
        <option value="">-- Ninguna --</option>
        <?php echo(select($mallas,$id_malla_actual)); ?>
      </select>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

