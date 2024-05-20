<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$directores = consulta_sql("SELECT u.id,u.nombre||' '||apellido||' ('||gu.alias||')' as nombre FROM usuarios AS u LEFT JOIN gestion.unidades AS gu ON gu.id=u.id_unidad WHERE activo AND tipo in (1,2) ORDER BY u.nombre");

if ($_REQUEST['crear'] <> "") {
	$nombre = $_REQUEST['nombre'];
	$escuelas = consulta_sql("SELECT id FROM escuelas WHERE nombre='$nombre';");
	if (count($escuelas) > 0) {
		echo(msje("ERROR: Esta intentando crear una Escuela con un nombre de una ya existente.<br>
		           Esto no es posible realizarlo"));
	} else {
		$aCampos = array("nombre","id_director");
		$SQLinsert = "INSERT INTO escuelas " . arr2sqlinsert($_REQUEST,$aCampos);		
		if (consulta_dml($SQLinsert) == 1) {
			echo(msje_js("Se ha creado una nueva Escuela con los datos ingresados.<br><br>
			              Pinche <a class='enlaces' href='principal.php?modulo=gestion_escuelas'>aqu&iacute;</a>
			              para voler al Gestor de Escuelas"));
		} else {
			echo(msje_js("ERROR: No ha sido posible crear la nueva Escuela"));
		}
		exit;
	}
}
?>

<!-- Inicio: crear escuela -->
<form name="formulario" action="principal.php" method="post" onSubmit="return enblanco2('nombre','id_director');">
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
    <td class="celdaValorAttr">
      <input type="text" name="nombre" value="" size="20" onChange="cambiado();">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Director</td>
    <td class="celdaValorAttr">
      <select name="id_director" onChange="cambiado();">
        <option value="">-- Seleccione --</option>
        <?php echo(select($directores,$_REQUEST['id_director'])); ?>
      </select>
    </td>
  </tr>
</table>
</form>
<!-- Fin: crear escuela -->

