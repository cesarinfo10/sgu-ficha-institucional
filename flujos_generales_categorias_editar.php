<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_categoria    = $_REQUEST['id_categoria'];
$nombre          = $_REQUEST['nombre'];
$id_cat_grupo    = $_REQUEST['id_cat_grupo'];
$id_cta_contable = $_REQUEST['id_cta_contable'];
$ano_flujo       = $_REQUEST['ano_flujo'];

if ($_REQUEST['eliminar'] == "Si" && $id_categoria > 0 && $id_cta_contable > 0 && $ano_flujo > 0) {
	$SQL_del_cat_cta_contable = "DELETE FROM finanzas.flujos_categorias_ctas_contables 
	                             WHERE id_cta_contable=$id_cta_contable AND id_categoria=$id_categoria AND ano_flujo=$ano_flujo";
	consulta_dml($SQL_del_cat_cta_contable);
	$id_cta_contable = null;
}

if ($id_cta_contable > 0) {
	$SQL_ins_cat_cta_contable = "INSERT INTO finanzas.flujos_categorias_ctas_contables (id_categoria,id_cta_contable,ano_flujo)"
	                          . "                                              VALUES ($id_categoria,$id_cta_contable,$ano_flujo)";
	if (consulta_dml($SQL_ins_cat_cta_contable) == 0) {
		echo(msje_js("ERROR: No fue posible añadir la Cuenta Contable seleccionada, debido a que está "
		            ."presente en esta u otra asignación para el Flujo $ano_flujo"));
	}
}
	
if ($_REQUEST['guardar'] == "Guardar") {
	if ($nombre <> "" && $id_categoria > 0 && $id_cat_grupo <> "") {
		$SQL_upd_categoria = "UPDATE finanzas.flujos_categorias SET nombre='$nombre',id_cat_grupo=$id_cat_grupo WHERE id=$id_categoria";
		if (consulta_dml($SQL_upd_categoria) > 0) {
			echo(msje_js("Se ha modificado con éxito la asignación $nombre"));
			echo(js("window.location='$enlbase_sm=flujos_generales_categorias&ano_flujo=$ano_flujo';"));
			exit;
		}
	}
}

$SQL_categoria = "SELECT * FROM finanzas.flujos_categorias WHERE id=$id_categoria";
$categoria = consulta_sql($SQL_categoria);
if (count($categoria) > 0) {
	extract($categoria[0]);
	$SQL_cat_ctas_contables = "SELECT id_cta_contable,fcc.nombre AS nombre_cat_ctas_contables 
	                           FROM finanzas.flujos_categorias_ctas_contables aS ccc
	                           LEFT JOIN finanzas.flujos_ctas_contables AS fcc ON fcc.id=ccc.id_cta_contable
	                           WHERE id_categoria=$id_categoria AND ano_flujo=$ano_flujo
	                           ORDER BY fcc.nombre";
	$cat_ctas_contables = consulta_sql($SQL_cat_ctas_contables);
	if (count($cat_ctas_contables) == 0) {
		$cat_ctas_contables = "** Sin cuenta contable asignada **<br>";
	} else {
		$HTML = "";
		for($x=0;$x<count($cat_ctas_contables);$x++) {
			$enl_elim = "$enlbase_sm=flujos_generales_categorias_editar&eliminar=Si&id_cta_contable={$cat_ctas_contables[$x]['id_cta_contable']}&id_categoria=$id_categoria&ano_flujo=$ano_flujo";
			$pregunta_js = "confirm('¿Está seguro de eliminar la asociación entre la cuenta contable "
			             . "«{$cat_ctas_contables[$x]['nombre_cat_ctas_contables']}» y esta asignación ($nombre)?');";
			$HTML .= "<div onMouseOver=\"document.getElementById('bo$x').style.visibility='visible';\" onMouseOut=\"document.getElementById('bo$x').style.visibility='hidden';\">"
			      .  "  {$cat_ctas_contables[$x]['nombre_cat_ctas_contables']}"
			      .  "  <a id='bo$x' href='$enl_elim' onClick=\"return $pregunta_js\" title='Eliminar Cta. Contable asociada' class='boton' style='visibility: hidden'><small>✕</small></a>"
			      .  "</div>";
		}
		$cat_ctas_contables = $HTML;
	}
	
	$SQL_cat_flujo = "SELECT 1 FROM finanzas.flujos_detalle WHERE id_cat_flujo=$id_categoria";	
	$cat_flujo = consulta_sql($SQL_cat_flujo);
	if (count($cat_flujo) > 0) {
		echo(msje_js("ATENCIÓN: Esta asignación ya se está usando en uno o más flujos.\\n\\n"
		            ."La modificación que realice afectará a todos los flujos en donde esta asignación esté presente.\\n\\n"
		            ."Tenga en cuenta que modificar el tipo de la asignación puede descuadrar alguno de estos flujos"));
	 }		
} else {
	echo(msje_js("ERROR: No existe la asignación que intenta editar. No se puede continuar"));
	echo(js("window.location='$enlbase_sm=flujos_generales_categorias';"));
	exit;
}
	
$CATEGORIAS_TIPO = array(array('id'=>"I",'nombre'=>"Ingresos"),
                         array('id'=>"G",'nombre'=>"Gastos"));
                         
$cat_grupos = consulta_sql("SELECT id,acumulador||'/'||nombre AS nombre FROM finanzas.flujos_cat_grupos ORDER BY nombre");

$SQL_ctas_contables = "SELECT id,nombre FROM finanzas.flujos_ctas_contables "
                    . "WHERE id NOT IN (SELECT id_cta_contable FROM finanzas.flujos_categorias_ctas_contables WHERE ano_flujo=$ano_flujo) AND ano=$ano_flujo"
                    . "ORDER BY nombre";
$ctas_contables = consulta_sql($SQL_ctas_contables);

?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal_sm.php" method="get" onSubmit="if (formulario.nombre.value=='' || formulario.id_cat_grupo.value=='') { alert('Debe ingresar el Nombre y el Sub-Título/Ítem de la asignación'); return false; } else { return true; }">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_categoria" value="<?php echo($id_categoria); ?>">
<input type="hidden" name="cat_flujo" value="<?php echo(count($cat_flujo)); ?>">
<input type="hidden" name="ano_flujo" value="<?php echo($ano_flujo); ?>">
<div style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Guardar">
  <input type="button" onClick="location='<?php echo("$enlbase_sm=flujos_generales_categorias&ano_flujo=$ano_flujo"); ?>';" value="Cancelar">
</div>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' style="text-align: center" colspan="2">Datos de la Asignación</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr'><input type="text" name="nombre" size="40" value="<?php echo($nombre); ?>" class='boton'></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Sub-Título/Ítem:</td>
    <td class='celdaValorAttr'>
      <select name='id_cat_grupo' class='filtro' style="max-width: none">
        <?php echo(select($cat_grupos,$id_cat_grupo)); ?>
      </select>
    </td>
  </tr>
  <tr><td class='celdaNombreAttr' style="text-align: center" colspan="2">Datos del Plan de Cuentas (Balance)</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Año Flujo/Balance:</td>
    <td class='celdaValorAttr'><?php echo($ano_flujo); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cuenta(s) Contable(s):</td>
    <td class='celdaValorAttr'>
      <?php echo($cat_ctas_contables); ?><br>
      <select name='id_cta_contable' class='filtro' style="max-width: none" onChange="submitform();">
        <option>-- Añadir Cta. Cobtable --</option>
        <?php echo(select($ctas_contables,$id_ctas_contables)); ?>
      </select>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->
