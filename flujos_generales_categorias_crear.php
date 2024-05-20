<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$nombre       = $_REQUEST['nombre'];
$id_cat_grupo = $_REQUEST['id_cat_grupo'];

if ($_REQUEST['guardar'] == "Guardar") {
	if ($nombre <> "" && $id_cat_grupo > 0) {
		$SQL_categoria = "SELECT 1 FROM finanzas.flujos_categorias WHERE nombre='$nombre' AND id_cat_grupo=$id_cat_grupo";
		$categoria = consulta_sql($SQL_categoria);
		if (count($categoria) > 0) {
			echo(msje_js("ERROR: Ya existe una Asignación con el nombre y Sub-Título que ha ingresado. No se puede continuar"));
			$nombre = $id_cat_grupo = "";
		} else {
			$SQL_categoria_insert = "INSERT INTO finanzas.flujos_categorias (nombre,id_cat_grupo) VALUES ('$nombre',$id_cat_grupo)";
			if (consulta_dml($SQL_categoria_insert) > 0) {
				echo(msje_js("Se ha creado con éxito la categoría $nombre"));
				echo(js("window.location='$enlbase_sm=flujos_generales_categorias';"));
				exit;
			}
		}
	}	
				
}

$cat_grupos = consulta_sql("SELECT id,CASE tipo WHEN 'E' THEN 'Egresos' WHEN 'I' THEN 'Ingresos' END||'/'||acumulador||'/'||nombre AS nombre FROM finanzas.flujos_cat_grupos ORDER BY nombre");

?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal_sm.php" method="get" onSubmit="if (formulario.nombre.value=='' || formulario.id_cat_grupo.value=='') { alert('Debe ingresar el Nombre y Partida de la nueva categoría'); return false; } else { return true; }">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<div style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Guardar">
  <input type="button" onClick="history.back();" value="Cancelar">
</div>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr'><input type="text" name="nombre" size="40" value="<?php echo($nombre); ?>" class='boton'></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Sub-Título/Ítem:</td>
    <td class='celdaValorAttr'>
      <select name='id_cat_grupo' class='filtro' style="max-width: none">
        <option value=''>-- Seleccione --</option>
        <?php echo(select($cat_grupos,$id_cat_grupo)); ?>
      </select>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->
