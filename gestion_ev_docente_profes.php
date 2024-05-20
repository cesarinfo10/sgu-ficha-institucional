<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

$id_profesor  = $_SESSION['id_usuario'];
$tipo_usuario = $_SESSION['tipo'];

if ($tipo_usuario <> 3 && empty($_REQUEST['id_profesor'])) { 
	echo(msje_js("Usted no es un(a) profesor(a). No puede acceder a este módulo"));
	header("Location: index.php");
	exit;
} elseif ($tipo_usuario <> 3 && !empty($_REQUEST['id_profesor'])) {
	$id_profesor = $_REQUEST['id_profesor'];
}

include("validar_modulo.php");

$condicion = "";

//$SQL_cursos_profe = "SELECT id,profesor FROM vista_cursos WHERE id_profesor=$id_profesor AND ano>=2007 AND NOT (semestre=$SEMESTRE AND ano=$ANO)";
$SQL_cursos_profe = "SELECT id,profesor FROM vista_cursos WHERE id_profesor=$id_profesor AND ano BETWEEN 2007 AND $ANO";
$cursos_profe     = consulta_sql($SQL_cursos_profe);
if (count($cursos_profe) > 0) {
	$SQL_periodos_profe = "SELECT semestre||'-'||ano AS periodo,semestre,ano,count(id)
	                       FROM cursos
	                       WHERE id_profesor=$id_profesor AND ano BETWEEN 2007 AND $ANO AND semestre IN (1,2)
	                       GROUP BY semestre,ano ORDER BY ano DESC,semestre DESC";
	$periodos_profe     = consulta_sql($SQL_periodos_profe);
	
	$stat_autoev_doc = $stat_ev_doc = $stat_ev_est = array();
	
	for ($x=0;$x<count($periodos_profe);$x++) {
		
		$SQL_autoev_doc = "SELECT ead.*,ead.semestre||'-'||ead.ano AS periodo
						   FROM encuestas.autoevaluacion_docente_historica AS ead
						   WHERE ead.id_profesor=$id_profesor AND ead.ano={$periodos_profe[$x]['ano']} AND ead.semestre={$periodos_profe[$x]['semestre']} 
						   ORDER BY ead.ano,ead.semestre";
		if (($ANO==2012 && $SEMESTRE==2) || $ANO>2012) {
			$stat_autoev_doc = enc_autoev_conteo_ranking2(consulta_sql($SQL_autoev_doc));
		} else {
			$stat_autoev_doc = enc_autoev_conteo_ranking(consulta_sql($SQL_autoev_doc));
		}
		//$stat_autoev_doc = enc_autoev_conteo_ranking(consulta_sql($SQL_autoev_doc));
		//var_dump($stat_autoev_doc);

		$SQL_ev_doc = "SELECT eed.*,eed.semestre||'-'||eed.ano AS periodo
					   FROM encuestas.evaluacion_docente_historica AS eed
					   WHERE eed.id_profesor=$id_profesor AND eed.ano={$periodos_profe[$x]['ano']} AND eed.semestre={$periodos_profe[$x]['semestre']} 
					   ORDER BY eed.ano,eed.semestre";
		$stat_ev_doc = enc_evdoc_conteo_ranking(consulta_sql($SQL_ev_doc));
		//var_dump($stat_ev_doc);

		
		$SQL_ev_est = "SELECT c.id_profesor,ee.*,ee.semestre||'-'||ee.ano AS periodo
					   FROM encuestas.estudiantil_historica AS ee
					   LEFT JOIN vista_cursos AS c ON c.id=ee.id_curso
					   WHERE c.id_profesor=$id_profesor  AND ee.ano={$periodos_profe[$x]['ano']} AND ee.semestre={$periodos_profe[$x]['semestre']} 
					   ORDER BY ee.ano,ee.semestre,id_curso";
		$stat_ev_est = enc_est_conteo_ranking(consulta_sql($SQL_ev_est));
		//var_dump($stat_ev_est);

		$periodos_profe[$x]['p_autoev_doc']   = $stat_autoev_doc[0]['total'];
		$periodos_profe[$x]['p_ev_doc']       = $stat_ev_doc[0]['total'];
		$periodos_profe[$x]['p_ev_est']       = $stat_ev_est[0]['total'];
		$periodos_profe[$x]['p_ev_est_casos'] = $stat_ev_est[0]['casos'];
		
	}
}

$tot_autoev_doc = (count($stat_autoev_doc)/$tot_reg)*100;
$tot_ev_doc     = (count($stat_ev_doc)/$tot_reg)*100;

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div><br>
<div class="texto">
  Mostrando todos los procesos de Evaluación Docente del profesor <b><?php echo($cursos_profe[0]['profesor']); ?></b>:
</div>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Periodo</td>
    <td class='tituloTabla'>Auto Ev.<br>30%</td>
    <td class='tituloTabla'>Ev. Doc. Dir.<br>35%</td>
    <td class='tituloTabla' colspan="2">Ev. Doc. Est.<br>35%</td>
    <td class='tituloTabla'>Puntaje<br>Final</td>
  </tr>
<?php
	if (count($periodos_profe) > 0) {
		//$_verde = "color: #009900;";
		$_rojo  = "color: #ff0000;";
		
		for ($x=0; $x<count($periodos_profe); $x++) {
			$periodos_profe[$x]['total_ev'] = ($periodos_profe[$x]['p_autoev_doc']*0.30)
			                                + ($periodos_profe[$x]['p_ev_doc']*0.35)
			                                + ($periodos_profe[$x]['p_ev_est']*0.35);
		}

		$repr = 0;
		for ($x=0; $x<count($periodos_profe); $x++) {
			extract($periodos_profe[$x]);
			
			$estilo = "";
			if ($periodos_profe[$x]['total_ev'] < 60) {
				$estilo = $_rojo;
				$repr++;
			}
			
			$p_autoev_doc = number_format($p_autoev_doc,1);
			$p_ev_doc     = number_format($p_ev_doc,1);
			$p_ev_est     = number_format($p_ev_est,1);
			$total_ev     = number_format($total_ev,1);
			
			$enl      = "$enlbase=resultados_ev_docente_estudiantil&id_profesor=$id_profesor&ano=$ano&semestre=$semestre";
			$p_ev_est = "<a href='$enl' style='$estilo'>$p_ev_est%</a>";
			//$enl = "$enlbase=ver_profesor&id_profesor=$id_profesor&ano=$ano&semestre=$semestre&id_carrera=$id_carrera";
			//$enlace = "<a class='enlitem' href='$enl'>";
			echo("  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n"
			    ."    <td class='textoTabla' align='right' style='$estilo'>$periodo</td>"
			    ."    <td class='textoTabla' align='right' style='$estilo'>$p_autoev_doc%</td>"
			    ."    <td class='textoTabla' align='right' style='$estilo'>$p_ev_doc%</td>"
			    ."    <td class='textoTabla' align='right' style='$estilo'>$p_ev_est</td>"
			    ."    <td class='textoTabla' align='right' style='$estilo'>($p_ev_est_casos)</td>"
			    ."    <td class='textoTabla' align='right' style='$estilo'>$total_ev%</td>"
			    ."  </tr>");			
		}
	$stat_autoev = number_format($tot_autoev_doc,1);
	$stat_ev     = number_format($tot_ev_doc,1);

	} else {
		echo("<td class='textoTabla' colspan='5'>"
		    ."  No hay registros para los criterios de búsqueda/selección"
		    ."</td>\n");
	}
?>
</table><br>
<div class="texto">
  <b>Auto Ev.:</b> Porcentaje ponderado de la Autoevaluación del Docente<br>
  <b>Ev. Doc. Dir:</b> Porcentaje ponderado de la Evaluación Docente del Director<br>
  <b>Ev. Doc. Est:</b> Porcentaje ponderado de la Evaluación Docente de los estudiantes. 
                       Entre parentesis, casos (alumnos) que contestan. Puede ver el
                       desglose de la Encuesta Estudiantil, pinchando el porcentaje
</div><br>
<!-- Fin: <?php echo($modulo); ?> -->

