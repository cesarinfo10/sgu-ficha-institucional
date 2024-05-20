<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

if ($_REQUEST['crear'] == "Crear") {
	$aCampos = array("nombre","alias","jornada","id_coordinador","id_escuela","activa",'regimen');
	$SQLinsert = "INSERT INTO carreras " . arr2sqlinsert($_REQUEST,$aCampos);
	if (consulta_dml($SQLinsert) == 1) {
		echo(msje_js("Se ha creado una nueva carrera con los datos ingresados exitósamente"));
		echo(js("window.location='$enlbase=gestion_carreras';"));
		exit;
	}
}
	
$escuelas      = consulta_sql("SELECT * FROM escuelas ORDER BY nombre;");
$coordinadores = consulta_sql("SELECT id,nombre||' '||apellido as nombre FROM usuarios WHERE tipo=2 AND activo ORDER BY apellido,nombre;");

$JORNADAS = array_merge($JORNADAS,array(array('id'=>'a','nombre'=>"Ambas")));

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

?>

<!-- Inicio: crear carrera -->
<form name="formulario" action="principal.php" method="post" onSubmit="return enblanco2('nombre','alias','jornada','id_coordinador','id_escuela');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type="submit" name="crear" value="Crear"></td>
    <td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar" onClick="history.back();"></td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="celdaNombreAttr">Nombre:</td>
    <td class="celdaValorAttr" colspan='3'>
      <input type="text" name="nombre" size="80" value="<?php echo($_REQUEST['nombre']); ?>" class='boton'>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Alias:</td>
    <td class="celdaValorAttr">
      <input type="text" name="alias" size="6" maxlength="6" value="<?php echo($_REQUEST['alias']); ?>" class='boton'>
    </td>
    <td class="celdaNombreAttr">Activa:</td>
    <td class="celdaValorAttr">
      <select name="activa">
        <?php echo(select($sino,$_REQUEST['activa'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Jornada(s):</td>
    <td class="celdaValorAttr">
      <select name="jornada" onChange="cambiado();">
        <option value=''>-- Seleccione --</option>
        <?php echo(select($JORNADAS,$_REQUEST['jornada'])); ?>
      </select>
    </td>
    <td class="celdaNombreAttr">Régimen:</td>
    <td class="celdaValorAttr">
      <select name="regimen" required>
		    <option value="">-- Seleccione --</option>
		    <?php echo(select($REGIMENES,$_REQUEST['regimen'])); ?>
	    </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Coordinador:</td>
    <td class="celdaValorAttr">
      <select name="id_coordinador">
        <option value="">-- Seleccione --</option>
        <?php echo(select($coordinadores,$_REQUEST['id_coordinador'])); ?>
      </select>
    </td>
    <td class="celdaNombreAttr">Escuela:</td>
    <td class="celdaValorAttr">
      <select name="id_escuela">
        <option value="">-- Seleccione --</option>
        <?php echo(select($escuelas,$_REQUEST['id_escuela'])); ?>
      </select>
    </td>
  </tr>
</table>
</form>
<!-- Fin: Crear carrera -->

