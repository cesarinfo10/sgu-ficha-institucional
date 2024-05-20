<?php
/*
 * Submodulo de cuadro de resumen de Inscripciones de Asignaturas.
 * Modulo principal: indicadores_gestion_academica.php
 */

function cuadro_Inscripcion_Asignaturas() {
	global $_ano,$_semestre,$_id_escuela,$_jornada,$_regimen;
	
	$condiciones = "";

	if (!empty($_id_escuela)) { $condiciones .= " AND car.id_escuela=$_id_escuela "; }

	//if ($ano > 0) {	$condiciones .= " AND vc.ano=$ano "; }

	//if ($semestre > -1) { $condiciones .= " AND vc.semestre=$semestre "; }

	if ($_jornada <> "") { $condiciones .= " AND a.jornada='$_jornada' "; }

	if ($_regimen <> "" && $_regimen <> "t") { $condiciones .= "AND (car.regimen = '$_regimen') "; }

	if ($ids_carreras <> "") { $condiciones .= " AND car.id IN ($ids_carreras) "; }
	
	$ANO_ant      = $_ano - 1;
	$SEMESTRE_ant = $_semestre - 1;
	
	if ($_semestre == 1) { $SEMESTRE_ant = 2; }
	if ($_semestre == 2) { $ANO_ant = $_ano; }
	
	$SQL_mat_ant = "SELECT id_alumno FROM cargas_academicas WHERE ano=$ANO_ant AND semestre=$SEMESTRE_ant";	
	$SQL_mat_act = "SELECT id_alumno FROM cargas_academicas WHERE ano=$_ano AND semestre=$_semestre";
	
	$SQL_MAT_ant = "SELECT va.cohorte,count(va.id) AS cant_alumnos
	                FROM vista_alumnos AS va
	                LEFT JOIN alumnos AS a USING (id)
	                LEFT JOIN carreras AS car ON car.id=va.id_carrera
	                WHERE true AND va.id IN ($SQL_mat_ant) $condiciones
	                GROUP BY va.cohorte 
	                ORDER BY va.cohorte DESC";
	$mat_ant     =  consulta_sql($SQL_MAT_ant);

	$SQL_MAT_act = "SELECT va.cohorte,count(va.id) AS cant_alumnos
	                FROM vista_alumnos AS va
	                LEFT JOIN alumnos AS a USING (id)
	                LEFT JOIN carreras AS car ON car.id=va.id_carrera
	                WHERE true AND va.id IN ($SQL_mat_ant) AND va.id IN ($SQL_mat_act) $condiciones
	                GROUP BY va.cohorte 
	                ORDER BY va.cohorte DESC";
	$mat_act     =  consulta_sql($SQL_MAT_act);
	
	$HTML = "<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla'>"
	      . "  <tr class='filaTituloTabla'>"
	      . "    <td class='tituloTabla'>Cohorte</td>"
	      . "    <td class='tituloTabla'>Periodo<br>Anterior<br>$SEMESTRE_ant-$ANO_ant</td>"
	      . "    <td class='tituloTabla'>Periodo<br>Actual<br>$_semestre-$_ano</td>"
	      . "    <td class='tituloTabla'>Rematricula</td>"
	      . "  </tr>";
	
	$tot_ant = $tot_act = 0;
	
	$grafico = new VerticalBarChart(800,300);
	$grafico->getPlot()->getPalette()->setBarColor(array(new Color(76,98,128),new Color(0, 0, 255)));
	$grafico_mat_ant = new XYDataSet();
	$grafico_mat_act = new XYDataSet();
	
	$cr_remat = array();
	$y = 0;
	for ($x=0;$x<count($mat_ant);$x++) {
		$cr_remat[$x]['cohorte'] = $mat_ant[$x]['cohorte'];
		$cr_remat[$x]['mat_ant'] = $mat_ant[$x]['cant_alumnos'];
		$cr_remat[$x]['mat_act'] = 0;
		if ($mat_ant[$x]['cohorte'] == $mat_act[$y]['cohorte']) {
			$cr_remat[$x]['mat_act'] = $mat_act[$y]['cant_alumnos'];
			$y++;
		}
	}
		
	for ($x=0;$x<count($cr_remat);$x++) {
		extract($cr_remat[$x]);
		$carrera_jornada = trim($alias)."-".$jornada;
		if ($jornada == "D") { $jornada = "Diurna"; } elseif ($jornada == "V") { $jornada = "Vespertina"; }

		$porc_remat = number_format(($mat_act/$mat_ant)*100,1,',','.');
			
		$HTML .= "  <tr class='filaTabla'>"
			  .  "    <td class='textoTabla' align='center'>$cohorte</td>"
		      .  "    <td class='textoTabla' align='right'>$mat_ant</td>"
		      .  "    <td class='textoTabla' align='right'>$mat_act</td>"
		      .  "    <td class='textoTabla' align='right'>$porc_remat%</td>"
		      .  "  </tr>";
		
		$tot_ant += $mat_ant;
		$tot_act += $mat_act;
		
		$grafico_mat_ant->addPoint(new Point("$cohorte", $mat_ant));
		$grafico_mat_act->addPoint(new Point("$cohorte", $mat_act));
	}
	
	$porc_remat = number_format(($tot_act/$tot_ant)*100,1,',','.');

	$HTML .= "  <tr>"
		  .  "    <td class='celdaNombreAttr'>Total:</td>"
		  .  "    <td class='celdaNombreAttr' align='right'>$tot_ant</td>"
		  .  "    <td class='celdaNombreAttr' align='right'>$tot_act</td>"
		  .  "    <td class='celdaNombreAttr' align='right'>$porc_remat%</td>"
		  .  "  </tr>"
		  .  "</table>";

	$grafico_remat = new XYSeriesDataSet();
	$grafico_remat->addSerie("Periodo Anterior ($SEMESTRE_ant-$ANO_ant)",$grafico_mat_ant);
	$grafico_remat->addSerie("Periodo Actual ($_semestre-$_ano)",$grafico_mat_act);
	$grafico->setDataSet($grafico_remat);
	$grafico->getPlot()->setCaptionPadding(new Padding(50));
	$grafico->getPlot()->setGraphCaptionRatio(0.9);
	//$grafico->setUpper(90);
	$grafico->setTitle("Rematrícula");
	$grafico->render("graficos/rematricula_$ano_$semestre.png");
	
	$HTML = "<table bgcolor='#ffffff' cellspacing='0' cellpadding='0' class='tabla' style='margin-top: 5px; box-shadow: 1px 1px 4px #999'>"
	      . "  <tr class='filaTituloTabla'>"
	      . "    <td class='tituloTabla' colspan='2'><big>Rematrícula por Cohortes</big></td>"
	      . "  </tr>"
	      . "  <tr>"
	      . "    <td class='textoTabla' valign='top'>$HTML</td>"
	      . "    <td class='textoTabla' align='center'>"
	      . "      <img src='graficos/rematricula_$ano_$semestre.png'><br>"
	      . "      <span style='background: rgb(76,98,128); border: 1px solid #000000'>&nbsp;&nbsp;&nbsp;</span> Periodo Anterior ($SEMESTRE_ant-$ANO_ant)  &nbsp;&nbsp;&nbsp;"
	      . "      <span style='background: rgb(0, 0, 255); border: 1px solid #000000'>&nbsp;&nbsp;&nbsp;</span> Periodo Actual ($_semestre-$_ano)"
	      . "      <br><br>"
	      . "    </td>"
	      . "  </tr>"
	      . "</table>";
	return $HTML;
}

?>
