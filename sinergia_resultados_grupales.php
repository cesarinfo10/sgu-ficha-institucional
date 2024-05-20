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

if (empty($_REQUEST['ano_periodo'])) { $ano_periodo = $ANO; }
if (empty($_REQUEST['semestre_periodo'])) { $semestre_periodo = $SEMESTRE; }
if (empty($_REQUEST['cohorte'])) { $cohorte = 0; }
if (empty($_REQUEST['semestre_cohorte'])) { $semestre_cohorte = 0; }
if (empty($_REQUEST['estado'])) { $estado = -1; }
if (empty($_REQUEST['moroso_financiero'])) { $moroso_financiero = -1; }
if (empty($_REQUEST['regimen'])) { $regimen = "PRE"; }

$condicion = "WHERE true AND p.alias <> 'OTIS_sencillo' ";

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

$HTML_pruebas_realizadas = "";
$SQL_pruebas_realizadas = "SELECT p.nombre AS nombre_prueba,count(sr.id) AS cant_respondidas
                           FROM sinergia.respuestas AS sr 
                           LEFT JOIN sinergia.pruebas AS p ON p.id=sr.id_prueba
                           LEFT JOIN alumnos AS a ON a.rut=sr.rut_alumno
                           LEFT JOIN carreras AS c ON c.id=a.carrera_actual
                           $condicion
                           GROUP BY p.nombre
                           ORDER BY p.nombre";
$pruebas_realizadas     = consulta_sql($SQL_pruebas_realizadas);
if (count($pruebas_realizadas) == 0) {
	$HTML_pruebas_realizadas = "  <tr>"
	                         . "    <td class='textoTabla' colspan='2' align='center'>"
	                         . "      <br><b> <blink>*** No hay pruebas contestadas para el periodo $semestre_periodo-$ano_periodo ***</blink> </b><br><br>"
	                         . "    </td>"
	                         . "  </tr>";
} else {
	for ($x=0;$x<count($pruebas_realizadas);$x++) {
		$HTML_pruebas_realizadas .= "  <tr class='filaTabla'>"
		                         .  "    <td class='textoTabla'>{$pruebas_realizadas[$x]['nombre_prueba']}</td>"
		                         .  "    <td class='textoTabla' align='center'>{$pruebas_realizadas[$x]['cant_respondidas']}</td>"
		                         .  "  </tr>";
	}
}

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

$AF5_respuestas   = sinergia_AF5(sinergia_respuestas("AF5",$ano_periodo,$semestre_periodo,$condicion));
$AF5_resumen      = sinergia_AF5_resumen($AF5_respuestas);
$AF5_resumen_json = sinergia_AF5_resumen_json($AF5_respuestas);
$AF5_graphs_json  = sinergia_amchart_graphs_json($AF5_resumen_json,"dimension");
$HTML_AF5         = sinergia_HTML_cuadro_resumen("AF5",$AF5_resumen,"Dimensiones","Cuadro N°1: Prueba AF5 - Valores Absolutos",$AF5_tot_dim);
$HTML_AF5_porc    = sinergia_HTML_cuadro_resumen_porc("AF5",$AF5_resumen,"Dimensiones","Cuadro N°2: Prueba AF5 - Valores Porcentuales",$AF5_tot_dim);
$HTML_AF5_informe = sinergia_HTML_informe_analitico(sinergia_resumen_porc($AF5_resumen),"AF5");
$JS_AF5_grafico   = "<div id='AF5_grafico' style='width: 600px; height: 420px; background-color: #FFFFFF;' ></div>";

$ACRA_respuestas   = sinergia_ACRA(sinergia_respuestas("ACRA",$ano_periodo,$semestre_periodo,$condicion));
$ACRA_resumen      = sinergia_ACRA_resumen($ACRA_respuestas);
$ACRA_resumen_json = sinergia_ACRA_resumen_json($ACRA_respuestas);
$ACRA_graphs_json  = sinergia_amchart_graphs_json($ACRA_resumen_json,"escala");
$HTML_ACRA         = sinergia_HTML_cuadro_resumen("ACRA",$ACRA_resumen,"Escalas","Cuadro N°3: Prueba ACRA - Valores Absolutos",$ACRA_tot_esc);
$HTML_ACRA_porc    = sinergia_HTML_cuadro_resumen_porc("ACRA",$ACRA_resumen,"Escalas","Cuadro N°4: Prueba ACRA - Valores Procentuales",$ACRA_tot_esc);
$HTML_ACRA_informe = sinergia_HTML_informe_analitico(sinergia_resumen_porc($ACRA_resumen),"ACRA");
$JS_ACRA_grafico   = "<div id='ACRA_grafico' style='width: 590px; height: 420px; background-color: #FFFFFF;' ></div>";


$OTIS_respuestas   = sinergia_OTIS(sinergia_respuestas("OTIS_sencillo",$ano_periodo,$semestre_periodo,$condicion));
$OTIS_resumen      = sinergia_OTIS_resumen($OTIS_respuestas);
$OTIS_resumen_json = sinergia_OTIS_resumen_json($OTIS_respuestas);
$OTIS_graphs_json  = sinergia_amchart_graphs_json($OTIS_resumen_json,"area");
$HTML_OTIS         = sinergia_HTML_cuadro_resumen("OTIS_sencillo",$OTIS_resumen,"Áreas","Cuadro N°5: Prueba Inteligencia y Orientación Vocacional - Valores Absolutos",$OTIS_tot_areas);
//$HTML_OTIS_porc    = sinergia_HTML_cuadro_resumen_porc("OTIS_sencillo",$OTIS_resumen,"Áreas","Cuadro N°6: Prueba Inteligencia y Orientación Vocacional - Valores Procentuales",$OTIS_tot_areas);
//$HTML_OTIS_informe = sinergia_HTML_informe_analitico(sinergia_resumen_porc($OTIS_resumen),"OTIS_sencillo");
//$JS_OTIS_grafico   = "<div id='OTIS_grafico' style='width: 600px; height: 470px; background-color: #FFFFFF;' ></div>";

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<script src="js/amcharts/amcharts.js" type="text/javascript"></script>
<script src="js/amcharts/serial.js" type="text/javascript"></script>
<script src="js/amcharts/exporting/amexport.js" type="text/javascript"></script>
<script src="js/amcharts/exporting/canvg.js" type="text/javascript"></script>
<script src="js/amcharts/exporting/rgbcolor.js" type="text/javascript"></script>
<script src="js/amcharts/exporting/filesaver.js" type="text/javascript"></script>
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<div class="texto">
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
        <td class="celdaFiltro" style="vertical-align: bottom;">
          <input type='button' value='Imprimir' onClick="window.open('<?php echo("$enlbase_sm=$modulo&$enl_nav&imprimir=si"); ?>');">
        </td>
      </tr>
    </table>
  <table bgcolor='#ffffff' cellspacing='0' cellpadding='2' class='tabla' style='margin-top: 5px; box-shadow: 1px 1px 4px #999' width='33%'>
    <tr class='filaTituloTabla'><td class='tituloTabla' colspan='2'>Pruebas Psicométricas realizadas en el periodo <?php echo("$semestre_periodo-$ano_periodo"); ?></td></tr>
    <tr class='filaTituloTabla'>
      <td class='tituloTabla'>Nombre</td>
      <td class='tituloTabla'>Casos</td>
    </tr>
    <?php echo($HTML_pruebas_realizadas); ?>
  </table>
  <br>
  <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="5" style='magins: 10px; box-shadow: -1px 1px 4px #999'>
    <tr><td class="texto" colspan="2" style='text-align: center;'><b>Resultados de la prueba Autoconcepto Forma 5 (AF5)</b></td></tr>
    <tr>
      <td>
        <?php echo($HTML_AF5); ?>
        <?php echo($HTML_AF5_porc); ?>
      </td>
      <td><?php echo($JS_AF5_grafico); ?></td>
    </tr>
    <tr><td class='texto' colspan='2' width='1160' align='justify'><?php echo($HTML_AF5_informe); ?></td></tr>
  </table>
  <br style="PAGE-BREAK-AFTER: always">
  <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="5" style='magins: 10px; box-shadow: -1px 1px 4px #999'>
    <tr><td class="texto" colspan="2" style='text-align: center;'><b>Resultados de  la prueba Escalas de Estrategias de Aprendizaje (ACRA)</b></td></tr>
    <tr>
      <td>
        <?php echo($HTML_ACRA); ?>
        <?php echo($HTML_ACRA_porc); ?>
      </td>
      <td><?php echo($JS_ACRA_grafico); ?></td>
    </tr>
    <tr><td class='texto' colspan='2' width='1160' align='justify'><?php echo($HTML_ACRA_informe); ?></td></tr>
  </table>
  <br style="PAGE-BREAK-AFTER: always">
<!--  <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="5" style='magins: 10px; box-shadow: -1px 1px 4px #999'>
    <tr><td class="texto" colspan="2" style='text-align: center;'><b>Resultados de la prueba de Inteligencia y Orientación Vocacional</b></td></tr>
    <tr>
      <td>
        <?php echo($HTML_OTIS); ?>
        <?php echo($HTML_OTIS_porc); ?>
      </td>
      <td><?php echo($JS_OTIS_grafico); ?></td>
    </tr>
    <tr><td class='texto' colspan='2' width='1160' align='justify'><?php echo($HTML_OTIS_informe); ?></td></tr>
  </table>-->
  
</div>
<?php if ($_REQUEST['imprimir'] == "si") { echo(js("window.print(); window.close();")); } ?>
<!-- Fin: <?php echo($modulo); ?> -->

<!-- amCharts javascript code -->
<script type="text/javascript">
	AmCharts.makeChart("AF5_grafico",
		{
			"type": "serial",
			"pathToImages": "http://cdn.amcharts.com/lib/3/images/",
			"categoryField": "dimension",
			"autoMarginOffset": 5,
			"plotAreaBorderAlpha": 0.59,
	        "sequencedAnimation": false,
	        "startDuration": 0,
	        "startEffect": "easeOutSine",
	        "urlTarget": "_blank",
	        "creditsPosition": "bottom-right",
			"fontSize": 10,
			"percentPrecision": 1,
			"categoryAxis": {
				"gridPosition": "start",
				"title": ""
			},
			"trendLines": [],
			"graphs": <?php echo($AF5_graphs_json); ?>,
			"guides": [],
			"valueAxes": [
				{
					"axisTitleOffset": 0,
					"id": "ValueAxis-1",
					"integersOnly": true,
					"maximum": 100,
					"minimum": 0,
					"precision": 0,
					"stackType": "100%",
					"synchronizeWith": "Not set",
					"totalText": "",
					"unit": "%",
					"autoGridCount": false,
					"title": "Cantidad Alumnos"
				}
			],
			"allLabels": [],
			"amExport": {},
			"balloon": {
				"showBullet": true
			},
			"legend": {
				"equalWidths": false,
				"reversedOrder": false,
				"switchable": false,
				"textClickEnabled": true,
				"useGraphSettings": true,
				"valueWidth": 0
			},
			"titles": [
				{
					"id": "AF5",
					"size": 15,
					"text": "Autoconcepto Forma 5"
				}
			],
			"dataProvider": <?php echo($AF5_resumen_json); ?>
		}
	);

	AmCharts.makeChart("ACRA_grafico",
		{
			"type": "serial",
			"pathToImages": "http://cdn.amcharts.com/lib/3/images/",
			"categoryField": "escala",
			"autoMarginOffset": 5,
			"plotAreaBorderAlpha": 0.59,
	        "sequencedAnimation": false,
	        "startDuration": 0,
	        "startEffect": "easeOutSine",
	        "urlTarget": "_blank",
	        "creditsPosition": "bottom-right",
			"fontSize": 10,
			"percentPrecision": 1,
			"categoryAxis": {
				"gridPosition": "start",
				"title": ""
			},
			"trendLines": [],
			"graphs": <?php echo($ACRA_graphs_json); ?>,
			"guides": [],
			"valueAxes": [
				{
					"axisTitleOffset": 0,
					"id": "ValueAxis-1",
					"integersOnly": true,
					"maximum": 100,
					"minimum": 0,
					"precision": 0,
					"stackType": "100%",
					"synchronizeWith": "Not set",
					"totalText": "",
					"unit": "%",
					"autoGridCount": false,
					"title": "Cantidad Alumnos"
				}
			],
			"allLabels": [],
			"amExport": {},
			"balloon": {
				"showBullet": true
			},
			"legend": {
				"equalWidths": false,
				"reversedOrder": false,
				"switchable": false,
				"textClickEnabled": true,
				"useGraphSettings": true,
				"valueWidth": 0
			},
			"titles": [
				{
					"id": "ACRA",
					"size": 15,
					"text": "Escalas de Estrategias de Aprendizaje"
				}
			],
			"dataProvider": <?php echo($ACRA_resumen_json); ?>
		}
	);

	AmCharts.makeChart("OTIS_grafico",
		{
			"type": "serial",
			"pathToImages": "http://cdn.amcharts.com/lib/3/images/",
			"categoryField": "area",
			"autoMarginOffset": 5,
			"plotAreaBorderAlpha": 0.59,
	        "sequencedAnimation": false,
	        "startDuration": 0,
	        "startEffect": "easeOutSine",
	        "urlTarget": "_blank",
	        "creditsPosition": "bottom-right",
			"fontSize": 10,
			"percentPrecision": 1,
			"categoryAxis": {
				"gridPosition": "start",
				"title": ""
			},
			"trendLines": [],
			"graphs": <?php echo($OTIS_graphs_json); ?>,
			"guides": [],
			"valueAxes": [
				{
					"axisTitleOffset": 0,
					"id": "ValueAxis-1",
					"integersOnly": true,
					"maximum": 100,
					"minimum": 0,
					"precision": 0,
					"stackType": "100%",
					"synchronizeWith": "Not set",
					"totalText": "",
					"unit": "%",
					"autoGridCount": false,
					"title": "Cantidad Alumnos"
				}
			],
			"allLabels": [],
			"amExport": {},
			"balloon": {
				"showBullet": true
			},
			"legend": {
				"maxColumns": 1,
				"position": "right",
				"reversedOrder": true,
				"switchable": false,
				"switchType": "v",
				"textClickEnabled": true,
				"useGraphSettings": true,
				"valueWidth": 0
			},
			"titles": [
				{
					"id": "OTIS",
					"size": 15,
					"text": "Inteligencia y Orientación Vocacional"
				}
			],
			"dataProvider": <?php echo($OTIS_resumen_json); ?>
		}
	);
</script>

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 1000,
		'height'			: 400,
		'afterClose'		: function () { true; },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_medium").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 800,
		'maxHeight'			: 700,
		'afterClose'		: function () { true; },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 500,
		'height'			: 450,
		'maxHeight'			: 450,
		'afterClose'		: function () { true; },
		'type'				: 'iframe'
	});
});
</script>
