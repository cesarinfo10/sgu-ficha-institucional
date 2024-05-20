<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$ano    = $_REQUEST['ano'];

if ($_REQUEST['guardar'] == "Guardar") {
	$id_cat_flujo      = $_REQUEST['id_cat_flujo'];	
	$aComentarios      = $_REQUEST['comentarios'];
	$monto_presupuesto = str_replace(".","",$_REQUEST['monto_presupuesto']);
	$aMontos = array();
	$i = 0;
	foreach ($_REQUEST AS $var => $valor) { if (substr($var,0,7) == "montos_") { $aMontos[$i] = str_replace(".","",$valor); $i++; } }
	for ($x=0;$x<12;$x++) { $aComentarios[$x] = "'$aComentarios[$x]'"; }
	$montos      = implode(",",$aMontos);
	$comentarios = implode(",",$aComentarios);
	$SQL_cat_flujo_insert = "INSERT INTO finanzas.flujos_detalle (ano_flujo,id_cat_flujo,montos,comentarios,monto_presupuesto) "
	                      . "VALUES ($ano,$id_cat_flujo,ARRAY[$montos],ARRAY[$comentarios],$monto_presupuesto)";
	if (consulta_dml($SQL_cat_flujo_insert) > 0) {
		consulta_dml("UPDATE finanzas.flujos SET fecha_modificacion=now() WHERE ano=$ano");
		echo(msje_js("Se ha agragado con éxito el item al flujo $ano"));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}
}

$HTML = "";
for ($x=0;$x<12;$x++) {
	$HTML .= "<tr class='filaTabla'>"
	      .  "  <td class='textoTabla' style='vertical-align: middle; text-align: right'>{$meses_palabra[$x]['nombre']}</td>"
	      .  "  <td class='textoTabla' style='text-align: right'>$<input type='text' name='montos_$x' value='0' size='10' class='montos' onBlur=\"if (this.value=='') { this.value=0; }\" onKeyUp=\"puntitos(this,this.value.charAt(this.value.length-1),this.name);\"></td>"
	      .  "  <td class='textoTabla'><input type='text' name='comentarios[$x]' value='$comentarios[$x]' size='30' class='boton'></td>"
	      .  "</tr>";
}
$HTML .= "";

$SQL_cat_flujo = "SELECT id_cat_flujo FROM finanzas.flujos_detalle WHERE ano_flujo=$ano";
$cat_flujo = consulta_sql($SQL_cat_flujo);
$cond_cat = "AND id NOT IN ($SQL_cat_flujo)";
if (count($cat_flujo) == 0) { $cond_cat = ""; }

$SQL_categorias = "SELECT c.id,CASE tipo WHEN 'E' THEN 'Egresos' WHEN 'I' THEN 'Ingresos' END||' \ '||acumulador||' \ '||fcg.nombre||' \ '||c.nombre AS nombre 
                   FROM finanzas.flujos_categorias AS c 
                   LEFT JOIN finanzas.flujos_cat_grupos AS fcg ON fcg.id=c.id_cat_grupo
                   WHERE c.id NOT IN (SELECT id_cat_flujo FROM finanzas.flujos_detalle WHERE ano_flujo=$ano)
                   ORDER BY tipo,acumulador,fcg.nombre,c.nombre";
$categorias = consulta_sql($SQL_categorias);

if (count($categorias) == 0) {
	echo(msje_js("AVISO: No hay Asignaciones que se puedan añadir a este flujo.\\n\\n"
	            ."Si necesita crear una nueva asignación, pinche en el botón Gestionar Asignaciones"));
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}
?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo("$nombre_modulo $ano"); ?>
</div><br>
<form name="formulario" action="principal_sm.php" method="get" onSubmit="if (formulario.id_cat_flujo.value=='') { alert('Debe seleccionar la categoría para este item'); return false; } else { return true; }">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="ano" value="<?php echo($ano); ?>">

<input type="submit" name="guardar" value="Guardar">
<br><br>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr'>Tipo \ Sub-Título \ Ítem \ Asignación:</td>
    <td class='celdaValorAttr' colspan='2'>
      <select name='id_cat_flujo' class='filtro' style='max-width: none'>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($categorias,$id_cat_flujo)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Monto Presupuesto:</td>
    <td class='celdaValorAttr' colspan='2'>$<input type='text' name='monto_presupuesto' value='0' size='15' class='montos' onBlur=\"if (this.value=='') { this.value=0; }\" onKeyUp=\"puntitos(this,this.value.charAt(this.value.length-1),this.name);\"></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='text-align: center'>Mes</td>
    <td class='celdaNombreAttr' style='text-align: center'>Monto</td>
    <td class='celdaNombreAttr' style='text-align: center'>Comentarios</td>
  </tr>
  <?php echo($HTML); ?>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->
