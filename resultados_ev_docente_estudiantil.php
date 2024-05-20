<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

$id_profesor = $_REQUEST['id_profesor'];
$semestre    = $_REQUEST['semestre'];
$ano         = $_REQUEST['ano'];

if (empty($id_profesor) || empty($ano) || empty($semestre)) { 
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$SQL_cursos_profe = "SELECT id,profesor FROM vista_cursos WHERE id_profesor=$id_profesor and ano=$ano and semestre=$semestre";
$cursos_profe     = consulta_sql($SQL_cursos_profe);
$profesor = $cursos_profe[0]['profesor'];

$SQL_ev_est = "SELECT c.id_profesor,ee.*,ee.semestre||'-'||ee.ano AS periodo
			   FROM encuestas.estudiantil_historica AS ee
			   LEFT JOIN vista_cursos AS c ON c.id=ee.id_curso
			   WHERE c.id_profesor=$id_profesor AND ee.ano=$ano AND ee.semestre=$semestre 
			   ORDER BY ee.ano,ee.semestre,id_curso";
$stat_ev_est = resultados_enc_est(consulta_sql($SQL_ev_est));
$casos = count(consulta_sql($SQL_ev_est));

$minimo = min($stat_ev_est);
if ($minimo >= 90) { $minimo = 0; }
$maximo = max($stat_ev_est);
if ($maximo < 90) { $maximo = 0; }

$encuesta = file("encuestas/estudiantil.txt");
$HTML_encuesta = "";
for ($x=0;$x<count($encuesta);$x++) {
	$linea_pregunta = explode("#",$encuesta[$x]);
	if (count($linea_pregunta) == 1) {
		$titulo = $linea_pregunta[0];
		$HTML_encuesta .= "  <tr class='filaTituloTabla'>".$LF
		                . "    <td colspan='2' align='center' class='tituloTabla'><b>$titulo</b></td>".$LF
		                . "  </tr>".$LF;
	} 
	if (count($linea_pregunta) > 1) {
	
		$nombre_pregunta=$linea_pregunta[0];
		$aCampos = array_merge($aCampos, array($nombre_pregunta));
		if ($nombre_pregunta == "p22") { break; }

		$opciones = array();
		for($y=2;$y<count($linea_pregunta);$y++) {
			$opciones = array_merge($opciones, array(array("id"=>$y-1,"nombre"=>trim($linea_pregunta[$y]))));
		}
		$pregunta = $linea_pregunta[1];
		
		$res = $stat_ev_est[$nombre_pregunta]."%";
		
		if ($stat_ev_est[$nombre_pregunta] < 60) { $res = "<span  style='color: #ff0000'>$res</span>"; }
		else { $res = "<span  style='color: #0000ff'>$res</span>"; }
		
		if ($minimo == $stat_ev_est[$nombre_pregunta] || $maximo == $stat_ev_est[$nombre_pregunta]) { $res = "<b>$res</b>"; }

		$HTML_encuesta .= "  <tr class='filaTabla'>".$LF
						. "    <td class='textoTabla'><u>$pregunta</u></td>".$LF
						. "    <td class='textoTabla'>$res</td>".$LF
						. "  </tr>".$LF;
		 $aCampos_Req = array_merge($aCampos_Req,array($nombre_pregunta));		
	}
}

?>
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div><br>
<div class="texto">
  A continuación se muestran los resultados de la Encuesta de Evaluación Estudiantil,
  realizada por los (<?php echo($casos); ?>) alumnos del profesor <b><?php echo($profesor); ?></b> en el periodo <b><?php echo("$semestre-$ano"); ?></b>
</div><br>
<table cellpadding="2" cellspacing="1" class="tabla" bgcolor="#FFFFFF">
  <?php echo($HTML_encuesta); ?>
</table>

<!-- Fin: <?php echo($modulo); ?> -->

<?php

function resultados_enc_est($matriz) {
	$resultados_enc_est = array();
	$casos = count($matriz);
	for ($x=0;$x<$casos;$x++) {
		for ($np=1;$np<=21;$np++) {
			
			$resultados_enc_est["p$np"] += 4- $matriz[$x]["p$np"];
		}
	}
	for ($np=1;$np<=21;$np++) {
		$resultados_enc_est["p$np"] = round(($resultados_enc_est["p$np"] / $casos) * (100/3),1);
	}
	
	return $resultados_enc_est;
}

?>
