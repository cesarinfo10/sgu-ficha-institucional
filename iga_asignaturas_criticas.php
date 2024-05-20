<?php
/*
 * Submodulo de cuadro de resumen de Asignaturas Críticas.
 * Modulo principal: indicadores_gestion_academica.php
 */

function cuadro_AsignaturasCriticas() {
	global $_ano,$_semestre,$_id_escuela,$_jornada,$_regimen;
	
	$condiciones = "";

	if (!empty($_id_escuela)) { $condiciones .= " AND car.id_escuela=$_id_escuela "; }

	if ($_ano > 0) {	$condiciones .= " AND vc.ano=$_ano "; }

	if ($_semestre > -1) { $condiciones .= " AND vc.semestre=$_semestre "; }

	if ($_jornada == 'D') { $condiciones .= " AND vc.seccion BETWEEN 1 AND 4 "; }
	elseif ($_jornada == 'V') { $condiciones .= " AND vc.seccion BETWEEN 5 AND 8 "; }

	if ($_regimen <> "" && $_regimen <> "t") { $condiciones .= "AND (car.regimen = '$_regimen') "; }

	if ($ids_carreras <> "") { $condiciones .= " AND car.id IN ($ids_carreras) "; }
	
	$SQL_s1_aprob = "SELECT count(solemne1) FROM cargas_academicas WHERE solemne1>=4 AND id_curso IN (vc.id,c.id_fusion) ";
	$SQL_s1_tot   = "SELECT CASE cant_notas WHEN 0 THEN 1 ELSE cant_notas END FROM (SELECT count(solemne1) AS cant_notas FROM cargas_academicas WHERE solemne1 BETWEEN 1 AND 7 AND id_curso IN (vc.id,c.id_fusion)) AS foo";

	$SQL_c1_aprob = "SELECT count(c1) FROM cargas_academicas ca LEFT JOIN calificaciones_parciales cp ON ca.id=cp.id_ca WHERE c1>=4 AND id_curso IN (vc.id,c.id_fusion) ";
	$SQL_c1_tot   = "SELECT CASE cant_notas WHEN 0 THEN 1 ELSE cant_notas END FROM (SELECT count(c1) AS cant_notas FROM cargas_academicas ca LEFT JOIN calificaciones_parciales cp ON ca.id=cp.id_ca WHERE c1 BETWEEN 1 AND 7 AND id_curso IN (vc.id,c.id_fusion)) AS foo ";

	$SQL_c2_aprob = "SELECT count(c2) FROM cargas_academicas ca LEFT JOIN calificaciones_parciales cp ON ca.id=cp.id_ca WHERE c2>=4 AND id_curso IN (vc.id,c.id_fusion) ";
	$SQL_c2_tot   = "SELECT CASE cant_notas WHEN 0 THEN 1 ELSE cant_notas END FROM (SELECT count(c2)  AS cant_notas FROM cargas_academicas ca LEFT JOIN calificaciones_parciales cp ON ca.id=cp.id_ca WHERE c2 BETWEEN 1 AND 7 AND id_curso IN (vc.id,c.id_fusion)) AS foo ";

	$SQL_s2_aprob = "SELECT count(solemne2) FROM cargas_academicas WHERE solemne2>=4 AND id_curso IN (vc.id,c.id_fusion) ";
	$SQL_s2_tot   = "SELECT CASE cant_notas WHEN 0 THEN 1 ELSE cant_notas END FROM (SELECT count(solemne2) AS cant_notas FROM cargas_academicas WHERE solemne2 BETWEEN 1 AND 7 AND id_curso IN (vc.id,c.id_fusion)) AS foo ";
	
	
	$SQL_cursos = "SELECT vc.id,vc.carrera,car.alias,
	                      CASE WHEN c.seccion BETWEEN 1 AND 4 THEN 'D' WHEN c.seccion BETWEEN 5 AND 8 THEN 'V' END AS jornada,
	                      CASE WHEN ($SQL_s1_aprob)/($SQL_s1_tot) < 0.67 THEN 1 ELSE null END AS s1_critica,
	                      CASE WHEN ($SQL_c1_aprob)/($SQL_c1_tot) < 0.67 THEN 1 ELSE null END AS c1_critica,
	                      CASE WHEN ($SQL_c2_aprob)/($SQL_c2_tot) < 0.67 THEN 1 ELSE null END AS c2_critica,
	                      CASE WHEN ($SQL_s2_aprob)/($SQL_s2_tot) < 0.67 THEN 1 ELSE null END AS s2_critica
	               FROM vista_cursos  AS vc
	               LEFT JOIN cursos   AS c USING(id)
	               LEFT JOIN carreras AS car ON car.id=vc.id_carrera
	               WHERE true $condiciones AND vc.seccion<9 ";

	$SQL = "SELECT carrera,alias,jornada,count(id) AS cant_cursos,
	               count(s1_critica) AS cant_s1_critica,
	               count(c1_critica) AS cant_c1_critica,
	               count(c2_critica) AS cant_c2_critica,
	               count(s2_critica) AS cant_s2_critica
	        FROM ($SQL_cursos) AS foo
	        GROUP BY carrera,alias,jornada
	        ORDER BY carrera,jornada";
	$cr_asig_critica = consulta_sql($SQL);
	
	$HTML = "<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla'>"
	      . "  <tr class='filaTituloTabla'>"
	      . "    <td class='tituloTabla'>Carrera</td>"
	      . "    <td class='tituloTabla'>1ᵃ Solemne</td>"
	      //. "    <td class='tituloTabla'>1ᵃ Parcial</td>"
	      //. "    <td class='tituloTabla'>2ᵃ Parcial</td>"
	      . "    <td class='tituloTabla'>2ᵃ Solemne</td>"
	      . "    <td class='tituloTabla'>Total<br>Cursos</td>"
	      . "  </tr>";
	
	$tot_s1_critica = $tot_c1_critica = $tot_c2_critica = $tot_s2_critica = $tot_cursos = 0;
	
	if (!empty($_id_escuela) || !empty($ids_carreras)) {
		$grafico = new VerticalBarChart(500,350);
	} else {
		$grafico = new VerticalBarChart(900,350);
	}
	$grafico->getPlot()->getPalette()->setBarColor(array(new Color(255, 0, 0),new Color(150, 0, 0)));
	$grafico_s1_critica = new XYDataSet();
	//$grafico_c1_critica = new XYDataSet();
	//$grafico_c2_critica = new XYDataSet(); 
	$grafico_s2_critica = new XYDataSet();
	
	for ($x=0;$x<count($cr_asig_critica);$x++) {
		extract($cr_asig_critica[$x]);
		$carrera_jornada = trim($alias)."-".$jornada;
		if ($jornada == "D") { $jornada = "Diurna"; } elseif ($jornada == "V") { $jornada = "Vespertina"; }

		$porc_s1_critica = number_format(($cant_s1_critica/$cant_cursos)*100,1,',','.');
		//$porc_c1_critica = number_format(($cant_c1_critica/$cant_cursos)*100,1,',','.');
		//$porc_c2_critica = number_format(($cant_c2_critica/$cant_cursos)*100,1,',','.');
		$porc_s2_critica = number_format(($cant_s2_critica/$cant_cursos)*100,1,',','.');
			
		$HTML .= "  <tr class='filaTabla'>"
			  .  "    <td class='textoTabla'>$carrera_jornada</td>"
		      .  "    <td class='textoTabla' align='right'>$porc_s1_critica%</td>"
		      //.  "    <td class='textoTabla' align='right'>$porc_c1_critica%</td>"
		      //.  "    <td class='textoTabla' align='right'>$porc_c2_critica%</td>"
		      .  "    <td class='textoTabla' align='right'>$porc_s2_critica%</td>"
		      .  "    <td class='textoTabla' align='right'>$cant_cursos</td>"
		      .  "  </tr>";
		
		$tot_s1_critica += $cant_s1_critica;
		//$tot_c1_critica += $cant_c1_critica;
		//$tot_c2_critica += $cant_c2_critica;
		$tot_s2_critica += $cant_s2_critica;
		$tot_cursos     += $cant_cursos;
		
		$grafico_s1_critica->addPoint(new Point("$carrera_jornada", $porc_s1_critica));
		//$grafico_c1_critica->addPoint(new Point("$carrera_jornada", $porc_c1_critica));
		//$grafico_c2_critica->addPoint(new Point("$carrera_jornada", $porc_c2_critica));
		$grafico_s2_critica->addPoint(new Point("$carrera_jornada", $porc_s2_critica));
	}
	
	$porc_s1_critica = number_format(($tot_s1_critica/$tot_cursos)*100,1,',','.');
	//$porc_c1_critica = number_format(($tot_c1_critica/$tot_cursos)*100,1,',','.');
	//$porc_c2_critica = number_format(($tot_c2_critica/$tot_cursos)*100,1,',','.');
	$porc_s2_critica = number_format(($tot_s2_critica/$tot_cursos)*100,1,',','.');
	
	$HTML .= "  <tr>"
		  .  "    <td class='celdaNombreAttr'>Total:</td>"
		  .  "    <td class='celdaNombreAttr' align='right'>$porc_s1_critica%</td>"
		  //.  "    <td class='celdaNombreAttr' align='right'>$porc_c1_critica%</td>"
		  //.  "    <td class='celdaNombreAttr' align='right'>$porc_c2_critica%</td>"
		  .  "    <td class='celdaNombreAttr' align='right'>$porc_s2_critica%</td>"
		  .  "    <td class='celdaNombreAttr'>$tot_cursos</td>"
		  .  "  </tr>"
		  .  "</table>";

	$grafico_asig_criticas = new XYSeriesDataSet();
	$grafico_asig_criticas->addSerie("1ra Solemne",$grafico_s1_critica);
	//$grafico_asig_criticas->addSerie("1ra Parcial",$grafico_c1_critica);
	//$grafico_asig_criticas->addSerie("2da Parcial",$grafico_c2_critica);
	$grafico_asig_criticas->addSerie("2da Solemne",$grafico_s2_critica);
	$grafico->setDataSet($grafico_asig_criticas);
	$grafico->getPlot()->setCaptionPadding(new Padding(50));
	$grafico->getPlot()->setGraphCaptionRatio(0.9);
	$grafico->setUpper(99);
	$grafico->setTitle("Asignaturas Críticas");
	$grafico->render("graficos/asig_criticas_$ano_$semestre.png");
	
	$HTML = "<table bgcolor='#ffffff' cellspacing='0' cellpadding='0' class='tabla' style='margin-top: 5px; box-shadow: 1px 1px 4px #999'>"
	      . "  <tr class='filaTituloTabla'>"
	      . "    <td class='tituloTabla' colspan='2'><big>Asignaturas Críticas</big></td>"
	      . "  </tr>"
	      . "  <tr>"
	      . "    <td class='textoTabla' valign='top'>$HTML</td>"
	      . "    <td class='textoTabla' align='center'>"
	      . "      <img src='graficos/asig_criticas_$ano_$semestre.png'><br>"
	      . "      <span style='background: rgb(255,0,0); border: 1px solid #000000'>&nbsp;&nbsp;&nbsp;</span> Primera Solemne  &nbsp;&nbsp;&nbsp;"
	      . "      <span style='background: rgb(150,0,0); border: 1px solid #000000'>&nbsp;&nbsp;&nbsp;</span> Segunda Solemne"
	      . "      <br><br>"
	      . "    </td>"
	      . "  </tr>"
	      . "</table>";
	return $HTML;
}

?>
