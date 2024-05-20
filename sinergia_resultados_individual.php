<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");
include("sinergia/func_sinergia.php");

$ids_carreras = $_SESSION['ids_carreras'];

$semestre_periodo  = $_REQUEST['semestre_periodo'];
$ano_periodo       = $_REQUEST['ano_periodo'];
$prueba            = $_REQUEST['prueba'];
$nivel             = $_REQUEST['nivel'];
$categoria         = $_REQUEST['categoria'];
$id_carrera        = $_REQUEST['id_carrera'];
$jornada           = $_REQUEST['jornada'];
$semestre_cohorte  = $_REQUEST['semestre_cohorte'];
$cohorte           = $_REQUEST['cohorte'];
$estado            = $_REQUEST['estado'];
$moroso_financiero = $_REQUEST['moroso_financiero'];
$admision          = $_REQUEST['admision'];
$regimen           = $_REQUEST['regimen'];
$genero            = $_REQUEST['genero'];
$rango_etario      = $_REQUEST['rango_etario'];
$mod_ant           = $_REQUEST['mod_ant'];


if (empty($_REQUEST['ano_periodo'])) { $ano_periodo = $ANO; }
if (empty($_REQUEST['semestre_periodo'])) { $semestre_periodo = $SEMESTRE; }
if (empty($_REQUEST['cohorte'])) { $cohorte = 0; }
if (empty($_REQUEST['semestre_cohorte'])) { $semestre_cohorte = 0; }
if (empty($_REQUEST['estado'])) { $estado = -1; }
if (empty($_REQUEST['moroso_financiero'])) { $moroso_financiero = -1; }
if (empty($_REQUEST['regimen'])) { $regimen = "PRE"; }

$condicion = "WHERE true ";

if ($ano_periodo > 0) {	$condicion .= "AND (sr.ano = '$ano_periodo') "; }

if ($semestre_periodo > 0) { $condicion .= "AND (sr.semestre = '$semestre_periodo') "; }

if ($cohorte > 0) {	$condicion .= "AND (a.cohorte = '$cohorte') "; }

if ($semestre_cohorte > 0) { $condicion .= "AND (a.semestre_cohorte = $semestre_cohorte) "; }

if ($estado <> "-1") { $condicion .= "AND (a.estado = '$estado') "; }

if ($moroso_financiero <> "-1") { $condicion .= "AND (a.moroso_financiero = '$moroso_financiero') "; }

if ($id_carrera <> "") { $condicion .= "AND (a.carrera_actual = '$id_carrera') "; }

if ($jornada <> "") { $condicion .= "AND (a.jornada = '$jornada') "; }

if ($admision <> "") { $condicion .= "AND (a.admision = '$admision') "; }

if ($regimen <> "" && $regimen <> "t") { $condicion .= "AND (c.regimen = '$regimen') "; }

if ($genero <> "") { $condicion .= "AND (a.genero='$genero') "; }

switch ($rango_etario) {
	case 1:
		$condicion .= "AND (date_part('year',age(a.fec_nac)) >= 15 AND date_part('year',age(a.fec_nac)) <= 29) ";
		break;
	case 2:
		$condicion .= "AND (date_part('year',age(a.fec_nac)) >= 30 AND date_part('year',age(a.fec_nac)) <= 44) ";
		break;
	case 3:
		$condicion .= "AND (date_part('year',age(a.fec_nac)) >= 45 AND date_part('year',age(a.fec_nac)) <= 59) ";
		break;
	case 4:
		$condicion .= "AND (date_part('year',age(a.fec_nac)) >= 60 AND date_part('year',age(a.fec_nac)) <= 74) ";
		break;
}

if (!empty($ids_carreras) && empty($id_carrera)) {
	$condicion .= " AND carrera_actual IN ($ids_carreras) ";
}

$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "")      { $cond_carreras .= "AND regimen='$regimen' "; }

$SQL_carreras = "SELECT id,nombre FROM carreras $cond_carreras ORDER BY nombre;";
$carreras = consulta_sql($SQL_carreras);

$SQL_al_estados = "SELECT id,nombre FROM al_estados WHERE nombre<>'Moroso' ORDER BY id;";
$al_estados = consulta_sql($SQL_al_estados);

$cohortes = $anos;

$anos = array();
for($ano_x=date("Y");$ano_x>=2014;$ano_x--) {
	$anos = array_merge($anos,array($ano_x => array("id" => $ano_x,"nombre" => $ano_x)));
}

$RANGOS_ETARIOS = array(array('id'=>"1", 'nombre'=>"1ra Edad (15 - 29)"),
                        array('id'=>"2", 'nombre'=>"2da Edad (30 - 44)"),
                        array('id'=>"3", 'nombre'=>"3ra Edad (45 - 59)"),
                        array('id'=>"4", 'nombre'=>"4ta Edad (60 - 74)"));

$SEMESTRES_COHORTES = array(array("id"=>1,"nombre"=>1),
                            array("id"=>2,"nombre"=>2));
                            
$pruebas = consulta_sql("SELECT alias AS id,nombre FROM sinergia.pruebas ORDER BY id");

$enl_nav = "semestre_periodo=$semestre_periodo"
         . "&ano_periodo=$ano_periodo"
         . "&id_carrera=$id_carrera"
         . "&jornada=$jornada"
         . "&semestre_cohorte=$semestre_cohorte"
         . "&cohorte=$cohorte"
         . "&estado=$estado"
         . "&moroso_financiero=$moroso_financiero"
         . "&admision=$admision"
         . "&regimen=$regimen"
         . "&genero=$genero"
         . "&rango_etario=$rango_etario";

$prueba_respuestas = sinergia_respuestas($prueba,$ano_periodo,$semestre_periodo,$condicion);
switch ($prueba) {
	case "AF5":
		$prueba_respuestas = sinergia_AF5($prueba_respuestas);
		$nombre_categorias = "Dimensiones";
		$categorias        = $AF5_Dimensiones;
		break;
	case "ACRA":
		$prueba_respuestas = sinergia_ACRA($prueba_respuestas);
		$nombre_categorias = "Escalas";
		$categorias = $ACRA_Escalas;
		break;
	case "OTIS_sencillo":
		$prueba_respuestas = sinergia_OTIS($prueba_respuestas);
		$nombre_categorias = "Áreas";
		$categorias = $OTIS_Areas;
		break;
}

$HTML_cat = "";
$cant_cat = count($categorias);
for ($x=0;$x<$cant_cat;$x++) {
	$HTML_cat .= "    <td class='tituloTabla' style='min-width: 130px'>{$categorias[$x]['alias']}</td>";
}

$HTML = "<table bgcolor='#ffffff' cellspacing='0' cellpadding='2' class='tabla' style='margin-top: 5px;'>\n"
      . "  <tr class='filaTituloTabla'>\n"
      . "    <td class='tituloTabla' colspan='3'>Alumno</td>\n"
      . "    <td class='tituloTabla' colspan='$cant_cat'>$nombre_categorias</td>\n"
      . "  </tr>\n"
      . "  <tr class='filaTituloTabla'>\n"
      . "    <td class='tituloTabla'>RUT</td>\n"
      . "    <td class='tituloTabla'>Nombre</td>\n"
      . "    <td class='tituloTabla'>Carrera</td>\n"
      . $HTML_cat
      . "  </tr>\n";

if (count($prueba_respuestas) == 0) {
	$HTML .= "<tr><td  class='celdaRamoMalla' style='font-size: 9pt; text-align: center' colspan='3'><br>*** Debe seleccionar una prueba ***<br><br></td></tr>";
} else {
	$HTML_al = "";
	for ($x=0;$x<count($prueba_respuestas);$x++) {
		$HTML_al .= "  <tr class='filaTabla'>\n"
				 .  "    <td class='celdaRamoMalla' style='font-size: 9pt; text-align: left'>{$prueba_respuestas[$x]['rut_alumno']}</td>\n"
				 .  "    <td class='celdaRamoMalla' style='font-size: 9pt; text-align: left'>{$prueba_respuestas[$x]['nombre']}</td>\n"
				 .  "    <td class='celdaRamoMalla' style='font-size: 9pt; text-align: left'>{$prueba_respuestas[$x]['carrera']}-{$prueba_respuestas[$x]['jornada']}</td>\n";
		for ($y=0;$y<$cant_cat;$y++) {
			$alias = $categorias[$y]['alias'];
			$nombre_campo = "nivel_".$alias;
			$color = "#ffffff";
			if ($nombre_campo == "nivel_".$categoria && $prueba_respuestas[$x][$nombre_campo] == $nivel) { $color = "#FFFF82"; }
			$HTML_al .= "    <td class='celdaRamoMalla' style='font-size: 9pt; text-align: left; background: $color'>{$prueba_respuestas[$x][$nombre_campo]}</td>\n";
		}
		$HTML_al .=  "  </tr>\n";

		if ($categoria == "" && $nivel == "") {
			$HTML .= $HTML_al;
			$HTML_al = "";
		} elseif ($prueba_respuestas[$x]["nivel_$categoria"] == $nivel) {
			$HTML .= $HTML_al;
		} else{ 
			$HTML_al = "";
		}
	}
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<div class="texto">
<?php if ($mod_ant == "") { ?>
  <form name="formulario" action="principal.php" method="get">
  <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Periodo: <br>
          <select class="filtro" name="semestre_periodo" onChange="submitform();">
            <?php echo(select($SEMESTRES_COHORTES,$semestre_periodo)); ?>
          </select>
          -
          <select class="filtro" name="ano_periodo" onChange="submitform();">
            <?php echo(select($anos,$ano_periodo)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Prueba: <br>
          <select class="filtro" name="prueba" onChange="submitform();">
			<option value=""> -- Todas -- </option>
            <?php echo(select($pruebas,$prueba)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Cohorte: <br>
          <select class="filtro" name="semestre_cohorte" onChange="submitform();">
            <option value="0"></option>
            <?php echo(select($SEMESTRES_COHORTES,$semestre_cohorte)); ?>
          </select>
          -
          <select class="filtro" name="cohorte" onChange="submitform();">
            <option value="0">Todas</option>
            <?php echo(select($cohortes,$cohorte)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Estado: <br>
          <select class="filtro" name="estado" onChange="submitform();">
            <option value="-1">Todos</option>
            <?php echo(select($al_estados,$estado)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Moroso: <br>
          <select class="filtro" name="moroso_financiero" onChange="submitform();">
            <option value="-1">Todos</option>
            <?php echo(select($sino,$moroso_financiero)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Admisión: <br>
          <select class="filtro" name="admision" onChange="submitform();">
            <option value="">Todos</option>
            <?php echo(select($ADMISION,$admision)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Rango Etario: <br>
          <select class="filtro" name="rango_etario" onChange="submitform();">
            <option value="t">Todos</option>
            <?php echo(select($RANGOS_ETARIOS,$rango_etario)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Género: <br>
          <select class="filtro" name="genero" onChange="submitform();">
            <option value="">Todos</option>
            <?php echo(select($generos,$genero)); ?>
          </select>
        </td>
      </tr>
    </table>
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Carrera/Programa:<br>
          <select class="filtro" name="id_carrera" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($carreras,$id_carrera)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Jornada:<br>
          <select class="filtro" name="jornada" onChange="submitform();">
            <option value="">Ambas</option>
            <?php echo(select($JORNADAS,$jornada)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Régimen: <br>
          <select class="filtro" name="regimen" onChange="submitform();">
            <option value="t">Todos</option>
            <?php echo(select($REGIMENES,$regimen)); ?>
          </select>
        </td>
      </tr>
    </table>
  </form>
  <?php } ?>
  <?php echo($HTML); ?>  
</div>
<!-- Fin: <?php echo($modulo); ?> -->
