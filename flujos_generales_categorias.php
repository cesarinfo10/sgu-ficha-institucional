<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$tipo_cat_grupo = $_REQUEST['tipo_cat_grupo'];
$id_categoria   = $_REQUEST['id_categoria'];
$id_cat_grupo   = $_REQUEST['id_cat_grupo'];
$id_acumulador  = $_REQUEST['id_acumulador'];
$ano_flujo      = $_REQUEST['ano_flujo'];

$condicion = "WHERE true ";
if ($tipo_cat_grupo <> "") { $condicion .= " AND tipo='$tipo_cat_grupo' "; }
if ($id_acumulador <> "") { $condicion .= " AND fcg.acumulador='$id_acumulador' "; }
if ($id_cat_grupo <> "") { $condicion .= " AND id_cat_grupo='$id_cat_grupo' "; }
$HTML = "";

if ($_REQUEST['eliminar'] == "Si") {
	$SQL_cat_flujo = "SELECT 1 FROM finanzas.flujos_detalle WHERE id_cat_flujo=$id_categoria";
	$cat_flujo = consulta_sql($SQL_cat_flujo);
	if (count($cat_flujo) > 0) {
		echo(msje_js("ERROR: No es posible eliminar esta asignación, debido a que está usada en uno o más flujos"));
	} else {
		$SQL_categoria_delete = "DELETE FROM finanzas.flujos_categorias WHERE id=$id_categoria";
		if (consulta_dml($SQL_categoria_delete) > 0) {
			echo(msje_js("Se ha eliminado la asignación"));
		}
	}
}

$SQL_cat_ctas_contables = "SELECT char_comma_sum(fcc.nombre||'<br>') AS cat_ctas_contables
                           FROM finanzas.flujos_categorias_ctas_contables AS ccc 
                           LEFT JOIN finanzas.flujos_ctas_contables AS fcc ON fcc.id=ccc.id_cta_contable
                           WHERE ccc.id_categoria=fc.id AND ccc.ano_flujo=$ano_flujo";
$SQL_categorias = "SELECT fc.id,fc.nombre,CASE tipo WHEN 'I' THEN 'Ingreso' WHEN 'E' THEN 'Egreso' END AS tipo,
                          fcg.nombre AS sub_partida,fcg.acumulador AS partida,($SQL_cat_ctas_contables) AS cat_ctas_contables
                   FROM finanzas.flujos_categorias AS fc
                   LEFT JOIN finanzas.flujos_cat_grupos AS fcg ON fcg.id=fc.id_cat_grupo
                   $condicion
                   ORDER BY nombre";
$categorias = consulta_sql($SQL_categorias);
//echo($SQL_categorias);
if (count($categorias) == 0) {
	$HTML .= "<tr><td class='textoTabla' colspan='5'><br> *** No hay Asginaciones creadas ***<br><br></td></tr>";
}

for ($x=0;$x<count($categorias);$x++) {
	extract($categorias[$x]);
	
	$nombre = "<span id='bo_$x' style='visibility: hidden'>"
		    . "  <a href='$enlbase_sm=flujos_generales_categorias_editar&id_categoria=$id&ano_flujo=$ano_flujo' title='Editar asignación' class='boton'>✍</a>"
		    . "  <a href='$enlbase_sm=flujos_generales_categorias&eliminar=Si&id_categoria=$id' title='Eliminar asignación' class='boton'>✕</a>"
		    . "</span> $nombre";
		    
	$sub_partida = "$sub_partida "
	             . "<span id='bo2_$x' style='visibility: hidden'>"
		         . "  <a href='$enlbase_sm=flujos_generales_cat_grupo_editar&id_cat_grupo=$id_cat_grupo' title='Editar Sub-Título/Ítem' class='boton'>✍</a>"
		         . "</span>";
	
	if ($cat_ctas_contables <> "") {
		$cat_ctas_contables = implode("<br>",explode("<br>,",$cat_ctas_contables));
	} else {
		$cat_ctas_contables = "<span style='color: red'>** No asociada(s) **</span>";
	}
		   
	$HTML .= "<tr class='filaTabla' onMouseOver=\"document.getElementById('bo_$x').style.visibility='visible';document.getElementById('bo2_$x').style.visibility='visible';\" onMouseOut=\"document.getElementById('bo_$x').style.visibility='hidden';document.getElementById('bo2_$x').style.visibility='hidden';\">\n"
	      .  "  <td class='textoTabla' style='vertical-align: middle'>$nombre</td>\n"
	      .  "  <td class='textoTabla' style='vertical-align: middle'>$tipo</td>\n"
	      .  "  <td class='textoTabla' style='vertical-align: middle'>$partida</td>\n"
	      .  "  <td class='textoTabla' style='vertical-align: middle'>$sub_partida</td>\n"
	      .  "  <td class='textoTabla' style='vertical-align: middle'><small>$cat_ctas_contables</small></td>\n"
	      .  "</tr>\n";
}

$TIPO_CAT_GRUPO = array(array('id'=>"I",'nombre'=>"Ingresos"),
                        array('id'=>"E",'nombre'=>"Egresos"));

$cond_cat_grupo = "WHERE true";
if ($id_acumulador <> "") { 
	$cond_cat_grupo .= " AND acumulador='$id_acumulador' ";
}
$cat_grupos = consulta_sql("SELECT id,nombre FROM finanzas.flujos_cat_grupos $cond_cat_grupo ORDER BY nombre");

$acumuladores = consulta_sql("SELECT id,nombre FROM vista_acumuladores_flujo $cond_acum ORDER BY nombre");

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
<table cellpadding="1" border="0" cellspacing="2" width="auto">
  <tr>
	<td class="celdaFiltro">
      Acciones:<br>
      <a href="<?php echo("$enlbase_sm=flujos_generales_categorias_crear"); ?>" class="boton">Crear Asignación</a>
      <!--<a href="<?php echo("$enlbase_sm=flujos_generales_cat_grupos"); ?>" class="boton">Crear Ítem</a>-->
      <a href="<?php echo("$enlbase_sm=flujos_generales_ctas_contables&ano_flujo=$ano_flujo"); ?>" id='sgu_fancybox' class='boton'>Gestionar Ctas. Contables</small></a>
    </td>
  </tr>
</table>
<table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
<form name="formulario" action="principal_sm.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="ano_flujo" value="<?php echo($ano_flujo); ?>">
  <tr>
	<td class="celdaFiltro">
      Tipo:<br>
      <select name="tipo_cat_grupo" onChange="submitform();" class="filtro">
        <option value=''>Todas</option>
        <?php echo(select($TIPO_CAT_GRUPO,$tipo_cat_grupo)); ?>
      </select>
    </td>   	
	<td class="celdaFiltro">
      Sub-Título:<br>
      <select name="id_acumulador" onChange="submitform();" class="filtro">
        <option value=''>Todas</option>
        <?php echo(select($acumuladores,$id_acumulador)); ?>
      </select>
    </td>
	<td class="celdaFiltro">
      Ítem:<br>
      <select name="id_cat_grupo" onChange="submitform();" class="filtro">
        <option value=''>Todas</option>
        <?php echo(select($cat_grupos,$id_cat_grupo)); ?>
      </select>
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
    <th class='tituloTabla'><input type="text" size="40" id="nombre_categoria" onkeyup="buscar_categorias()" placeholder="Buscar asignaciones por nombre..." class="boton"></th>
    <th colspan="3" class='tituloTabla'></th>
    <th rowspan="2" class='tituloTabla'>Cuenta(s) Contable(s)<br><small>(definidas para el año <?php echo($ano_flujo); ?>)</small></th>
  </tr>
  <tr class='filaTituloTabla'>
    <th class='tituloTabla'>&nbsp;&nbsp;Asignación</th>
    <th class='tituloTabla'>Tipo</th>
    <th class='tituloTabla'>Sub-Título</th>
    <th class='tituloTabla'>Ítem</th>
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
