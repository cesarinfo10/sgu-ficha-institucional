<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$condicion = "WHERE true AND vm.id<>52 ";
if ($_SESSION['ids_carreras'] <> "") {
	$ids_carreras = $_SESSION['ids_carreras'];
	$condicion .= "AND vm.id_carrera IN ($ids_carreras)";
}

$regimen        = $_REQUEST['regimen'];
$id_escuela     = $_REQUEST['id_escuela'];
$carrera_activa = $_REQUEST['carrera_activa'];
$malla_activa   = $_REQUEST['malla_activa'];

if (empty($_REQUEST['regimen']))        { $regimen = "PRE"; }
if (empty($_REQUEST['carrera_activa'])) { $carrera_activa = "t"; }
if (empty($_REQUEST['malla_activa']))   { $malla_activa = "t"; }

if (!empty($id_escuela)) { $condicion .= " AND c.id_escuela=$id_escuela "; }

if ($regimen <> "" && $regimen <> "t") { $condicion .= " AND c.regimen='$regimen' "; }

if ($carrera_activa == "t") { $condicion .= " AND c.activa "; }  elseif ($carrera_activa == "f") { $condicion .= " AND NOT c.activa "; } 
if ($malla_activa == "t")   { $condicion .= " AND c.id_malla_actual=m.id "; } elseif ($malla_activa == "f")   { $condicion .= " AND c.id_malla_actual<>m.id "; } 

$SQL_mallas = "SELECT vm.id,carrera,alias_carrera,m.ano,CASE WHEN c.id_malla_actual=m.id THEN true ELSE false END AS malla_activa
               FROM vista_mallas AS vm
               LEFT JOIN mallas AS m USING (id)
               LEFT JOIN carreras AS c ON c.id=m.id_carrera
               $condicion  
               ORDER BY carrera,ano";
$mallas = consulta_sql($SQL_mallas);
//var_dump($mallas[0]);
//exit;
$REGIMENES = consulta_sql("SELECT * FROM regimenes");
$ESCUELAS = consulta_sql("SELECT id,nombre FROM escuelas ORDER BY nombre;");
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo"><?php echo($nombre_modulo); ?></div>
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<table cellpadding="1" border="0" cellspacing="2" width="auto" style="margin-top: 5px">
  <tr>
	<td class="celdaFiltro">
	  Escuela:<br>
	  <select class='filtro' name="id_escuela" onChange="submitform();">
		<option value="">Todas</option>
		<?php echo(select($ESCUELAS,$id_escuela)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
	  Régimen:<br>
	  <select class='filtro' name="regimen" onChange="submitform();">
		<option value="t">Todos</option>
		<?php echo(select($REGIMENES,$regimen)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
	  Carreas Activas:<br>
	  <select class='filtro' name="carrera_activa" onChange="submitform();">
		<option value="-1">Todos</option>
		<?php echo(select($sino,$carrera_activa)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
	  Mallas Vigente:<br>
	  <select class='filtro' name="malla_activa" onChange="submitform();">
		<option value="-1">Todos</option>
		<?php echo(select($sino,$malla_activa)); ?>
	  </select>
	</td>
  </tr>
</table>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class="tituloTabla">Carrera</td>
    <td class="tituloTabla">Año malla</td>
  </tr>
<?php

	$carrera_malla = $mallas[0]['carrera'];

	for ($x=0;$x<count($mallas);$x++) {

		echo("  <tr class='filaTabla'>\n");
		echo("    <td class='textoTabla'>".$mallas[$x]['carrera']."</td>\n");
		echo("    <td class='textoTabla' align='right'>");
		
		while(true) {
		
			$enl = "$enlbase=ver_malla&enl_volver=history.back();&id_malla=".$mallas[$x]['id'];			
			echo("<a href='$enl' class='enlaces'>".$mallas[$x]['activa']." ".$mallas[$x]['ano']."</a><br>");
			$x++;

			if ($carrera_malla <> $mallas[$x]['carrera']) {
				$carrera_malla = $mallas[$x]['carrera'];
				break;
			}				

		}
		$x--;
		echo("</td>\n");
	}
	
?>
</table>
</form>
<div class="texto">
  Los años de mallas marcados con * corresponde a la
  malla actualmente usada en la carrera para nuevos alumnos.
</div>
<!-- Fin: <?php echo($modulo); ?> -->

<?php
/*
$mallas = consulta_sql("SELECT * FROM vista_mallas $condicion;");

$carreras = consulta_sql("SELECT id,id_malla_actual FROM carreras WHERE id IN (SELECT DISTINCT id_carrera FROM mallas);");

	$x = 0;
	while ($x < count($mallas)) {
		echo("  <tr class='filaTabla'>\n");
		echo("    <td class='textoTabla'>" . $mallas[$x]['carrera'] . "</td>\n");
		echo("    <td class='textoTabla' align='right'>\n");		
		$id_carrera = $mallas[$x]['id_carrera'];
		$anos = "";
		while ($id_carrera == $mallas[$x]['id_carrera']) {
			$malla_actual = "";
			for ($z=0;$z<count($carreras);$z++) {
				if ($mallas[$x]['id_carrera'] == $carreras[$z]['id'] &&
					 $mallas[$x]['id'] == $carreras[$z]['id_malla_actual']) {
					$malla_actual = "*";
				};
			};
			$enl = "$enlbase=ver_malla&enl_volver=history.back();&id_malla=" . $mallas[$x]['id'];
			$anos .= "      <a class='enlaces' href='$enl'>$malla_actual " . $mallas[$x]['ano'] . "</a><br>\n";
			$x++;
		};
		echo($anos);
		echo("    </td>\n");
		echo("  </tr>\n");
	};
*/

?>
