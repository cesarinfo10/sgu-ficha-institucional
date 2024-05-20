<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$ano_flujo      = $_REQUEST['ano_flujo'];

$HTML = "";

if ($_REQUEST['eliminar'] == "Si" && $_REQUEST['id_cta_contable'] > 0) {
	$SQL_del_cta_contable = "DELETE FROM finanzas.flujos_ctas_contables WHERE id={$_REQUEST['id_cta_contable']}";
	if (consulta_dml($SQL_del_cta_contable) > 0) {
		echo(msje_js("Se ha eliminado la cuenta contable"));
	} else {
		echo(msje_js("ERROR: No es posible eliminar esta cuenta contable, debido a que se encuentra asociada a una asignación"));
	}
}

$SQL_ctas_contables = "SELECT fcc.id,fcc.nombre,to_char(fcc.fecha_reg,'DD-tmMon-YYYY') AS fecha_reg,u.nombre_usuario AS creador,
                              fcg.acumulador||' \ '||fcg.nombre||' \ '||fc.nombre AS categoria
                       FROM finanzas.flujos_ctas_contables AS fcc
                       LEFT JOIN usuarios AS u on u.id=fcc.id_usuario
                       LEFT JOIN finanzas.flujos_categorias_ctas_contables AS fccc ON (fccc.id_cta_contable=fcc.id AND fccc.ano_flujo=$ano_flujo)
                       LEFT JOIN finanzas.flujos_categorias AS fc ON fc.id=fccc.id_categoria
                       LEFT JOIN finanzas.flujos_cat_grupos AS fcg ON fcg.id=fc.id_cat_grupo
                       WHERE ano=$ano_flujo
                       ORDER BY nombre";
$ctas_contables = consulta_sql($SQL_ctas_contables);
//echo($SQL_ctas_contables);
if (count($ctas_contables) == 0) {
	$HTML .= "<tr><td class='textoTabla' colspan='4'><br> *** No hay cuentas contables para el año $ano_flujo creadas ***<br><br></td></tr>";
}

for ($x=0;$x<count($ctas_contables);$x++) {
	extract($ctas_contables[$x]);
	
	$href_editar = "$enlbase_sm=flujos_generales_ctas_contables_editar&id_cta_contable=$id&ano_flujo=$ano_flujo";
	$href_elim = "$enlbase_sm=flujos_generales_ctas_contables&eliminar=Si&id_cta_contable=$id&ano_flujo=$ano_flujo";
	
	$onclick_confirm_elim = "confirm('Está seguro de eliminar esta cuenta contable ($nombre)')";
	
	$nombre = "<span id='bo$x' style='visibility: hidden'>\n"
		    . "  <a href='$href_editar' title='Editar Cta. Contable' class='boton'>✍</a>\n"
		    . "  <a href='$href_elim' onClick=\"return $onclick_confirm_elim\" title='Eliminar Cta. Contable' class='boton'>✕</a>\n"
		    . "</span> $nombre\n";
		    
	if ($categoria == "") { $categoria = "<span style='color: red'>** Sin asignación **</span>"; }
		   
	$HTML .= "<tr class='filaTabla' onMouseOver=\"document.getElementById('bo$x').style.visibility='visible';\" onMouseOut=\"document.getElementById('bo$x').style.visibility='hidden';\">\n"
	      .  "  <td class='textoTabla' style='vertical-align: middle'>$nombre</td>\n"
	      .  "  <td class='textoTabla' style='vertical-align: middle'>$fecha_reg</td>\n"
	      .  "  <td class='textoTabla' style='vertical-align: middle'>$creador</td>\n"
	      .  "  <td class='textoTabla' style='vertical-align: middle'>$categoria</td>\n"
	      .  "</tr>\n";
}

$ANOS_flujos = consulta_sql("SELECT ano AS id,ano||CASE WHEN activo THEN ' *' ELSE '' END AS nombre FROM finanzas.flujos ORDER BY ano DESC");

$ctas_contables_ano = consulta_sql("SELECT * FROM finanzas.flujos_ctas_contables WHERE ano=$ano_flujo");
if (count($ctas_contables_ano) > 0) {
	$SQL_ctas_contables_sin_asig = "SELECT id,nombre FROM finanzas.flujos_ctas_contables 
									WHERE ano=$ano_flujo AND id NOT IN (SELECT id_cta_contable FROM finanzas.flujos_categorias_ctas_contables WHERE ano_flujo=$ano_flujo)
									ORDER BY nombre";
	$ctas_contables_sin_asig = consulta_sql($SQL_ctas_contables_sin_asig);
	$HTML_ccsa = "";
	if (count($ctas_contables_sin_asig) > 0) {
		$HTML_ccsa = "<ul>";
		for ($x=0;$x<count($ctas_contables_sin_asig);$x++) { $HTML_ccsa .= "<li>{$ctas_contables_sin_asig[$x]['nombre']}</li>"; }
		$HTML_ccsa .= "</ul>";
		$HTML_ccsa = "<div class='texto' style='border: 2px red solid; background: #FFE4E9; margin: 5px; padding: 5px;'>"
				   . "  ERROR: Actualmente se encuentran las siguientes Cuentas Contables sin Asignación: "
				   .    $HTML_ccsa
				   . "  Debe realizar las asignaciones correspondientes (no se permiten Cuentas Contables sin Asignación)."
				   . "</div>";
		$HTML_ctas_contables_asig = $HTML_ccsa;
	}
} else {
	$HTML_ccsa = "<div class='texto' style='border: 2px red solid; background: #FFE4E9; margin: 5px; padding: 5px;'>"
			   . "  ERROR: Actualmente NO HAY cuentas contables para el Flujo $ano_flujo. Debe definir el conjunto de cuentas contables antes de proseguir "
			   . "  y luego realizar las asignaciones correspondientes."
			   . "</div>";
	$HTML_ctas_contables_asig = $HTML_ccsa;	
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
<form name="formulario" action="principal_sm.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="ano_flujo" value="<?php echo($ano_flujo); ?>">
  <tr>
	<td class="celdaFiltro">
      Acciones:<br>
      <a href="<?php echo("$enlbase_sm=flujos_generales_cta_contable_crear&ano_flujo=$ano_flujo"); ?>" class="boton">Crear Cta Contable</a>
      <a href="<?php echo("$enlbase_sm=flujos_generales_ctas_contables_copiar&ano_flujo=$ano_flujo"); ?>" class="boton">Copiar Ctas. Contables</a>
      <a href="<?php echo("$enlbase_sm=flujos_generales_categorias&ano_flujo=$ano_flujo"); ?>" id='sgu_fancybox' class='boton'>Gestionar Asignaciones</a>
    </td>
    <td class="celdaFiltro">
      Año Flujo/Balance:<br>
      <select name='ano_flujo' onChange='submitform()' class='filtro'>
        <?php echo(select($ANOS_flujos,$ano_flujo)); ?>
      </select>
    </td>   	
  </tr>
</form>
</table>
<?php echo($HTML_ctas_contables_asig); ?>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla_acordeon' style='margin-top: 5px' id="tabla_categorias">
  <tr class='filaTituloTabla'>
    <th class='tituloTabla'><input type="text" size="40" id="nombre_categoria" onkeyup="buscar_categorias()" placeholder="Buscar ctas contables por nombre..." class="boton"></th>
    <th colspan="3" class='tituloTabla'></th>
  </tr>
  <tr class='filaTituloTabla'>
    <th class='tituloTabla'>&nbsp;&nbsp;Nombre Cta. Contable</th>
    <th class='tituloTabla'>Fecha Registro</th>
    <th class='tituloTabla'>Creador</th>
    <th class='tituloTabla'>Sub-Título \ Ítem \ Asignación</th>
  </tr>
  <?php echo($HTML); ?>
</table>
<!-- Fin: <?php echo($modulo); ?> -->

<script>
function buscar_categorias() {
  // Declare variables 
  var input, filter, table, tr, td, i;
  input = document.getElementById("nombre_categoria");
  filter = input.value.toUpperCase();
  table = document.getElementById("tabla_categorias");
  tr = table.getElementsByTagName("tr");

  // Loop through all table rows, and hide those who don't match the search query
  for (i = 0; i < tr.length; i++) {
    td = tr[i].getElementsByTagName("td")[0];
    if (td) {
      if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
        tr[i].style.display = "";
      } else {
        tr[i].style.display = "none";
      }
    } 
  }
}
</script>
