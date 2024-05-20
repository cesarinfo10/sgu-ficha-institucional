<?php
/*
 * Submodulo de cuadro de resumen de Reincorporados.
 * Modulo principal: indicadores_gestion_academica.php
 */

function cuadro_Reincorporados() {
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
	
	$SQL_mat_ant = "SELECT id_alumno FROM matriculas WHERE ano=$ANO_ant AND semestre=$SEMESTRE_ant";	
	$SQL_mat_act = "SELECT id_alumno FROM matriculas WHERE ano=$_ano AND semestre=$_semestre";
	
	$SQL_MAT_act = "SELECT car.nombre AS carrera,car.alias,a.jornada,count(va.id) AS cant_alumnos
	                FROM vista_alumnos AS va
	                LEFT JOIN alumnos AS a USING (id)
	                LEFT JOIN carreras AS car ON car.id=va.id_carrera
	                WHERE va.id IN ($SQL_mat_act) $condiciones
	                GROUP BY car.nombre,car.alias,a.jornada
	                ORDER BY car.nombre,a.jornada";
	$mat_act     =  consulta_sql($SQL_MAT_act);

	$SQL_MAT_Reincorp = "SELECT car.nombre AS carrera,car.alias,a.jornada,count(va.id) AS cant_alumnos
	                     FROM vista_alumnos AS va
	                     LEFT JOIN alumnos AS a USING (id)
	                     LEFT JOIN carreras AS car ON car.id=va.id_carrera
	                     WHERE va.cohorte<$_ano AND va.id NOT IN ($SQL_mat_ant) AND va.id IN ($SQL_mat_act) $condiciones
	                     GROUP BY car.nombre,car.alias,a.jornada 
	                     ORDER BY car.nombre,a.jornada";
	$mat_act_reincorp =  consulta_sql($SQL_MAT_Reincorp);
	//echo($SQL_MAT_Reincorp);

	$HTML = "<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla'>"
	      . "  <tr class='filaTituloTabla'>"
	      . "    <td class='tituloTabla'>Carrera</td>"
	      . "    <td class='tituloTabla'>Jornada</td>"
	      . "    <td class='tituloTabla'>Matrícula<br>Total</td>"
	      . "    <td class='tituloTabla'>Reincorp.</td>"
	      . "    <td class='tituloTabla'>Porc.<br>Reincorp.</td>"
	      . "  </tr>";
	
	$tot_act = $tot_act_reincorp = 0;
	
	$grafico = new VerticalBarChart(700,350);
	$grafico->getPlot()->getPalette()->setBarColor(array(new Color(76,98,128),new Color(0, 0, 255)));
	$grafico_mat_reincorp = new XYDataSet();
	
	$cr_reincorp = array();
	$y = 0;
	for ($x=0;$x<count($mat_act);$x++) {
		$cr_reincorp[$x]['carrera'] = $mat_act[$x]['carrera'];
		$cr_reincorp[$x]['jornada'] = $mat_act[$x]['jornada'];
		$cr_reincorp[$x]['alias']   = $mat_act[$x]['alias'];
		$cr_reincorp[$x]['mat_act'] = $mat_act[$x]['cant_alumnos'];
		$cr_reincorp[$x]['mat_act_reincorp'] = 0;
		if ($mat_act[$x]['carrera'] == $mat_act_reincorp[$y]['carrera']  && $mat_act[$x]['jornada'] == $mat_act_reincorp[$y]['jornada']) {
			$cr_reincorp[$x]['mat_act_reincorp'] = $mat_act_reincorp[$y]['cant_alumnos'];
			$y++;
		}
	}
		
	for ($x=0;$x<count($cr_reincorp);$x++) {
		extract($cr_reincorp[$x]);
		$carrera_jornada = trim($alias)."-".$jornada;
		if ($jornada == "D") { $jornada = "Diurna"; } elseif ($jornada == "V") { $jornada = "Vespertina"; }

		$porc_reincorp = number_format(($mat_act_reincorp/$mat_act)*100,1,',','.');
			
		$HTML .= "  <tr class='filaTabla'>"
			  .  "    <td class='textoTabla'>$carrera</td>"
			  .  "    <td class='textoTabla'>$jornada</td>"
		      .  "    <td class='textoTabla' align='right'>$mat_act</td>"
		      .  "    <td class='textoTabla' align='right'>$mat_act_reincorp</td>"
		      .  "    <td class='textoTabla' align='right'>$porc_reincorp%</td>"
		      .  "  </tr>";
		
		$tot_act          += $mat_act;
		$tot_act_reincorp += $mat_act_reincorp;
		
		$grafico_mat_reincorp->addPoint(new Point($carrera_jornada, $porc_reincorp));
	}
	
	$porc_reincorp = number_format(($tot_act_reincorp/$tot_act)*100,1,',','.');

	$HTML .= "  <tr>"
		  .  "    <td class='celdaNombreAttr' colspan='2'>Total:</td>"
		  .  "    <td class='celdaNombreAttr' align='right'>$tot_act</td>"
		  .  "    <td class='celdaNombreAttr' align='right'>$tot_act_reincorp</td>"
		  .  "    <td class='celdaNombreAttr' align='right'>$porc_reincorp%</td>"
		  .  "  </tr>"
		  .  "</table>";

	$grafico->setDataSet($grafico_mat_reincorp);
	$grafico->getPlot()->setCaptionPadding(new Padding(50));
	$grafico->getPlot()->setGraphCaptionRatio(0.9);
	$grafico->setUpper(15);
	$grafico->setTitle("Reincorporaciones");
	$grafico->render("graficos/reincorporados_$ano_$semestre.png");
	
	$HTML = "<table bgcolor='#ffffff' cellspacing='0' cellpadding='0' class='tabla' style='margin-top: 5px; box-shadow: 1px 1px 4px #999'>"
	      . "  <tr class='filaTituloTabla'>"
	      . "    <td class='tituloTabla' colspan='2'><big>Reincorporados</big></td>"
	      . "  </tr>"
	      . "  <tr>"
	      . "    <td class='textoTabla' valign='top'>$HTML</td>"
	      . "    <td class='textoTabla' align='center'><img src='graficos/reincorporados_$ano_$semestre.png'></td>"
	      . "  </tr>"
	      . "</table>";
	return $HTML;
}

?>
