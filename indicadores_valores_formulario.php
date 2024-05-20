<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$aCampos = array('id_cat_indicador','ano','valor');

if ($_REQUEST['guardar'] == "Guardar") {

	$SQL_ins = "INSERT INTO gestion.indicadores ".arr2sqlinsert($_REQUEST,$aCampos);
	if (consulta_dml($SQL_ins) > 0) {
		echo(msje_js("Se han guardado exitosamente los datos."));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	} else {
		echo(msje_js("ERROR: Ha ocurrido un error y NO se han guardado los datos."));
	}
}

$INDICADORES_CAT = consulta_sql("SELECT id,agrupador||'\'||nombre AS nombre FROM gestion.indicadores_categorias WHERE activo AND abierto ORDER BY agrupador,nombre");

$indicador_cat = consulta_sql("SELECT valor_porcentaje FROM gestion.indicadores_categorias WHERE id={$_REQUEST['id_cat_indicador']}");
if ($indicador_cat[0]['valor_porcentaje'] == "t") { $valor_porcentaje = "%"; }
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_indicador" value="<?php echo($id_indicador); ?>">
<div style='margin-top: 5px'>
  <input type="submit" name='guardar' value="Guardar">
  <input type="button" name='cancelar' value="Cancelar">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Valores</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Indicador:</td>
    <td class='celdaValorAttr' colspan="3">
	  <select name="id_cat_indicador" class='filtro' style="max-width: none" required>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($INDICADORES_CAT,$_REQUEST['id_cat_indicador'])); ?>    
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Año:</td>
    <td class='celdaValorAttr' colspan="3"><input type="number" size='4' name="ano" value="<?php echo($_REQUEST['ano']); ?>" <?php echo($readonly); ?> class='boton' required></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Valor:</td>
    <td class='celdaValorAttr' colspan="3">
      <input type="number" size="4" step="any" name="valor" value="<?php echo($_REQUEST['valor']); ?>" <?php echo($readonly); ?> class='boton' style='text-align: right' required>
      <?php echo($valor_porcentaje); ?>
    </td>
  </tr> 

</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
