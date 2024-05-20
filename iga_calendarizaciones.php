<?php
/*
 * Submodulo de cuadro de resumen de Calendarizaciones de cursos.
 * Modulo principal: indicadores_gestion_academica.php
 */

function cuadro_Calendarizaciones() {
	global $_ano,$_semestre,$_id_escuela,$_jornada,$_regimen;
	
	$condiciones = "";

	if (!empty($_id_escuela)) { $condiciones .= " AND car.id_escuela=$_id_escuela "; }

	if ($_ano > 0) {	$condiciones .= " AND vc.ano=$_ano "; }

	if ($_semestre > -1) { $condiciones .= " AND vc.semestre=$_semestre "; }

	if ($_jornada == 'D') { $condiciones .= " AND vc.seccion BETWEEN 1 AND 4 "; }
	elseif ($_jornada == 'V') { $condiciones .= " AND vc.seccion BETWEEN 5 AND 8 "; }

	if ($_regimen <> "" && $_regimen <> "t") { $condiciones .= "AND (car.regimen = '$_regimen') "; }

	if ($ids_carreras <> "") { $condiciones .= " AND car.id IN ($ids_carreras) "; }
	
	$SQL_cal = "SELECT id_curso,CASE WHEN count(id)=count(materia) AND count(id)>0 THEN 'SI' ELSE null END AS hecha
	            FROM calendarizaciones 
	            GROUP BY id_curso";
	
	$SQL_cursos = "SELECT id,CASE WHEN seccion BETWEEN 1 AND 4 THEN 'D' WHEN seccion BETWEEN 5 AND 8 THEN 'V' END AS jornada FROM cursos";

	$SQL = "SELECT carrera,car.alias,c.jornada,count(cal.hecha) AS cant_cursos
	        FROM vista_cursos       AS vc
	        LEFT JOIN ($SQL_cursos) AS c   USING (id)
	        LEFT JOIN cursos        AS cc   USING (id)
	        LEFT JOIN carreras      AS car ON car.id=vc.id_carrera
	        LEFT JOIN ($SQL_cal)    AS cal ON cal.id_curso=vc.id
	        WHERE true $condiciones AND vc.seccion<9 AND cc.id_fusion IS NULL
	        GROUP BY carrera,car.alias,c.jornada
	        ORDER BY carrera,c.jornada";
	$cr_cal = consulta_sql($SQL);
	
	$SQL_cursos = "SELECT carrera,car.alias,c.jornada,count(vc.id) AS cant_cursos
	               FROM vista_cursos       AS vc
	               LEFT JOIN ($SQL_cursos) AS c   USING (id)
	               LEFT JOIN cursos        AS cc  USING (id)
	               LEFT JOIN carreras      AS car ON car.id=vc.id_carrera
	               WHERE true $condiciones AND vc.seccion<9 AND cc.id_fusion IS NULL
	               GROUP BY carrera,car.alias,c.jornada
	               ORDER BY carrera,c.jornada";
	$cr_cursos = consulta_sql($SQL_cursos);
	
	$HTML = "<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla'>"
	      . "  <tr class='filaTituloTabla'>"
	      . "    <td class='tituloTabla'>Carrera</td>"
	      . "    <td class='tituloTabla'>Jornada</td>"
	      . "    <td class='tituloTabla'>Cursos<br>Carrera</td>"
	      . "    <td class='tituloTabla'>Cursos<br>Calend.</td>"
	      . "    <td class='tituloTabla'>Porc.</td>"
	      . "  </tr>";
	
	$tot_cursos = $tot_cursos_cal = 0;
	for ($x=0;$x<count($cr_cal);$x++) { $tot_cursos += $cr_cursos[$x]['cant_cursos']; $tot_cursos_cal += $cr_cal[$x]['cant_cursos']; }

	if (!empty($_id_escuela) || !empty($ids_carreras)) {
		$grafico = new VerticalBarChart(300,350);
	} else {
		$grafico = new VerticalBarChart(600,350);
	}
	
	$grafico->getPlot()->getPalette()->setBarColor(array(new Color(76,98,128),new Color(255, 0,   0)));
	$grafico_cal = new XYDataSet();
	
	for ($x=0;$x<count($cr_cal);$x++) {
		$porc_cant_cursos = number_format(($cr_cal[$x]['cant_cursos']/$cr_cursos[$x]['cant_cursos'])*100,1,',','.');
		if ($cr_cal[$x]['jornada'] == "D") { $jornada = "Diurna"; } elseif ($cr_cal[$x]['jornada'] == "V") { $jornada = "Vespertina"; }
			
		$HTML .= "  <tr class='filaTabla'>"
			  .  "    <td class='textoTabla'>{$cr_cal[$x]['carrera']}</td>"
			  .  "    <td class='textoTabla'>$jornada</td>"
		      .  "    <td class='textoTabla' align='right'>{$cr_cursos[$x]['cant_cursos']}</td>"
		      .  "    <td class='textoTabla' align='right'>{$cr_cal[$x]['cant_cursos']}</td>"
		      .  "    <td class='textoTabla' align='right'>$porc_cant_cursos%</td>"
		      .  "  </tr>";
		      
		$grafico_cal->addPoint(new Point(trim($cr_cal[$x]['alias'])."-".$cr_cal[$x]['jornada'], $porc_cant_cursos));
	}
	
	$porc_cant_cursos = number_format(($tot_cursos_cal/$tot_cursos)*100,1,',','.');

	$HTML .= "  <tr>"
		  .  "    <td class='celdaNombreAttr' colspan='2'>Total:</td>"
		  .  "    <td class='celdaNombreAttr'>$tot_cursos</td>"
		  .  "    <td class='celdaNombreAttr'>$tot_cursos_cal</td>"
		  .  "    <td class='celdaNombreAttr'>$porc_cant_cursos%</td>"
		  .  "  </tr>"
		  .  "</table>";

	$grafico->setDataSet($grafico_cal);
	$grafico->setUpper(90);
	$grafico->setTitle("Calendarizaciones");
	$grafico->render("graficos/calendarizacion_$ano_$semestre.png");
	
	$HTML = "<table bgcolor='#ffffff' cellspacing='0' cellpadding='0' class='tabla' style='margin-top: 5px; box-shadow: 1px 1px 4px #999'>"
	      . "  <tr class='filaTituloTabla'>"
	      . "    <td class='tituloTabla' colspan='2'><big>Calendarizaciones</big></td>"
	      . "  </tr>"
	      . "  <tr>"
	      . "    <td class='textoTabla' valign='top'>$HTML</td>"
	      . "    <td class='textoTabla'><img src='graficos/calendarizacion_$ano_$semestre.png'></td>"
	      . "  </tr>"
	      . "</table>";
	return $HTML;
}

?>
