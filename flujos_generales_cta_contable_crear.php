<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$nombre       = $_REQUEST['nombre'];
$ano_flujo    = $_REQUEST['ano_flujo'];
$id_categoria = $_REQUEST['id_categoria'];
$id_usuario   = $_SESSION['id_usuario'];

if ($_REQUEST['guardar'] == "Guardar") {
	if ($nombre <> "" && $ano_flujo > 0 && $id_categoria > 0) {
		$SQL_ins_cta_contable = "INSERT INTO finanzas.flujos_ctas_contables (nombre,ano,id_usuario) VALUES ('$nombre',$ano_flujo,$id_usuario)";
		if (consulta_dml($SQL_ins_cta_contable) > 0) {
			$cta_contable = consulta_sql("SELECT id FROM finanzas.flujos_ctas_contables WHERE nombre='$nombre' AND ano=$ano_flujo");
			$id_cta_contable = $cta_contable[0]['id'];
			$SQL_ins_cat_cta_contable = "INSERT INTO finanzas.flujos_categorias_ctas_contables (id_categoria,id_cta_contable,ano_flujo)"
			                          . "                                              VALUES ($id_categoria,$id_cta_contable,$ano_flujo)";
			if (consulta_dml($SQL_ins_cat_cta_contable) > 0) {
				echo(msje_js("Se ha creado con éxito la cuenta contable « $nombre » y se ha pareado con la asignación indicada."));				
			} else {
				echo(msje_js("ERROR: No fue posible añadir la Cuenta Contable seleccionada, debido a que está "
				            ."presente en esta u otra categoria para el Flujo $ano_flujo"));
			}
		} else {
			echo(msje_js("ERROR: No fue posible añadir la Cuenta Contable debido a que existe una con el mismo nombre para el año $ano_flujo."));
		}
		echo(js("window.location='$enlbase_sm=flujos_generales_ctas_contables&ano_flujo=$ano_flujo';"));
		exit;
	}
}

$ANOS_flujos = consulta_sql("SELECT ano AS id,ano||CASE WHEN activo THEN ' *' ELSE '' END AS nombre FROM finanzas.flujos ORDER BY ano DESC");

$SQL_categorias = "SELECT c.id,CASE tipo WHEN 'E' THEN 'Egresos' WHEN 'I' THEN 'Ingresos' END||' \ '||acumulador||' \ '||fcg.nombre||' \ '||c.nombre AS nombre 
                   FROM finanzas.flujos_categorias AS c 
                   LEFT JOIN finanzas.flujos_cat_grupos AS fcg ON fcg.id=c.id_cat_grupo
                   ORDER BY tipo,acumulador,fcg.nombre,c.nombre";
$categorias = consulta_sql($SQL_categorias);

?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal_sm.php" method="get" onSubmit="if (formulario.nombre.value=='' || formulario.id_categoria.value=='') { alert('Debe ingresar el Nombre de la Cuenta Contable y la Asignación.'); return false; } else { return true; }">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="ano_flujo" value="<?php echo($ano_flujo); ?>">
<div style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Guardar">
  <input type="button" onClick="history.back();" value="Cancelar">
</div>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' style="text-align: center" colspan="2">Datos de la Cuenta Contable</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr'><input type="text" name="nombre" size="40" value="<?php echo($nombre); ?>" class='boton'></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Año Flujo/Balance:</td>
    <td class='celdaValorAttr'><?php echo($ano_flujo); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' style="text-align: center" colspan="2">Datos del Flujo</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Sub-Título \ Ítem \ Asignación:</td>
    <td class='celdaValorAttr'>
      <select name='id_categoria' class='filtro' style="max-width: none">
        <option value=''>-- Seleccione --</option>
        <?php echo(select($categorias,$id_categoria)); ?>
      </select>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->
