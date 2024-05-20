<?php
/*
 * Submodulo de cuadro de resumen de Calendarizaciones de cursos.
 * Modulo principal: indicadores_gestion_academica.php
 */

function cuadro_GradoAcadDocentes() {
	global $_ano,$_semestre,$_id_escuela,$_jornada,$_regimen;
	
	$condiciones = "";

	if (!empty($_id_escuela)) { $condiciones .= " AND vp.id_escuela=$_id_escuela "; }

	if ($_ano > 0) {	$condiciones .= " AND vc.ano=$_ano "; }

	if ($_semestre > -1) { $condiciones .= " AND vc.semestre=$_semestre "; }

	if ($_jornada == 'D') { $condiciones .= " AND vc.seccion BETWEEN 1 AND 4 "; }
	elseif ($_jornada == 'V') { $condiciones .= " AND vc.seccion BETWEEN 5 AND 8 "; }

	if ($_regimen <> "" && $_regimen <> "t") { $condiciones .= "AND (car.regimen = '$_regimen') "; }

	if ($ids_carreras <> "") { $condiciones .= " AND car.id IN ($ids_carreras) "; }
	
	$SQL_profes_cursos = "SELECT DISTINCT ON (vc.id_profesor) vc.id_profesor,escuela,grado_academico,id_grado_academico
	                      FROM vista_cursos          AS vc
	                      LEFT JOIN carreras         AS car ON car.id=vc.id_carrera
	                      LEFT JOIN vista_profesores AS vp  ON vp.id=vc.id_profesor
	                      WHERE vc.id_profesor IS NOT NULL $condiciones AND vc.seccion<9";
	                      	
	$SQL = "SELECT escuela,grado_academico,count(id_profesor) AS cant_profes 
	        FROM ($SQL_profes_cursos) AS foo 
	        GROUP BY escuela,grado_academico,id_grado_academico
	        ORDER BY escuela,id_grado_academico";
	$cr_profes = consulta_sql($SQL);
	
	$grados_acad = consulta_sql("SELECT nombre FROM grado_acad ORDER BY id");
	
	$HTML_grados_acad = "";
	for ($x=0;$x<count($grados_acad);$x++) { $HTML_grados_acad .= "    <td class='tituloTabla' colspan='2'>{$grados_acad[$x]['nombre']}</td>"; }
	
	$HTML = "<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla'>"
	      . "  <tr class='filaTituloTabla'>"
	      . "    <td class='tituloTabla'>Escuela</td>"
	      . $HTML_grados_acad
	      . "    <td class='tituloTabla'>Total</td>"
	      . "  </tr>";
	
	
	//crear tabla de doble entrada Escuelas/grados Academicos
	$profes = array();
	$y = $x = 0;
	$escuela = $cr_profes[0]['escuela'];
	while ($x < count($cr_profes)) {
		if ($escuela <> $cr_profes[$x]['escuela']) { $y++; $escuela = $cr_profes[$x]['escuela']; }
		$profes[$y]['escuela']    = $cr_profes[$x]['escuela'];
		$profes[$y]['tot_profes'] = 0;
		for ($z=0;$z<count($grados_acad);$z++) {
			$grado_acad = $grados_acad[$z]['nombre'];
			$profes[$y][$grado_acad] = 0;
			if ($cr_profes[$x]['grado_academico'] == $grado_acad && $escuela == $cr_profes[$x]['escuela']) { 
				$profes[$y][$grado_acad]   = $cr_profes[$x]['cant_profes'];
				$profes[$y]['tot_profes'] += $cr_profes[$x]['cant_profes'];
				$x++;
			}
		}
	}
	
	$grafico = new PieChart(400,300);
	//$grafico->getPlot()->getPalette()->setBarColor(array(new Color(76,98,128),new Color(255, 0,   0)));
	$grafico_profes = new XYDataSet();
	
	$tot_profes = array();
	for ($x=0;$x<count($profes);$x++) {
		$porc_cant_cursos = number_format(($cr_cal[$x]['cant_cursos']/$cr_cursos[$x]['cant_cursos'])*100,1,',','.');
		
		$HTML_grados_acad = "";
		for ($y=0;$y<count($grados_acad);$y++) { 
			$grado_acad = $grados_acad[$y]['nombre'];
			$cant_profes = $profes[$x][$grado_acad];
			$porc_profes = number_format(($cant_profes/$profes[$x]['tot_profes'])*100,1,',','.');
			$HTML_grados_acad .= "    <td class='textoTabla' align='right'>$cant_profes</td><td class='textoTabla' align='right'>$porc_profes%</td>";
			$tot_profes[$grado_acad] += $cant_profes;
		}
			
		$HTML .= "  <tr class='filaTabla'>"
			  .  "    <td class='textoTabla'>{$profes[$x]['escuela']}</td>"
			  .  $HTML_grados_acad
		      .  "    <td class='textoTabla' align='right'>{$profes[$x]['tot_profes']}</td>"
		      .  "  </tr>";
		      
	}

	$HTML_grados_acad = "";
	$total_profes = array_sum($tot_profes);	
	foreach ($tot_profes AS $grado_acad => $cant_profes) { 
		$porc_profes = number_format(($cant_profes/$total_profes)*100,1,',','.');
		$HTML_grados_acad .= "    <td class='celdaNombreAttr'>$cant_profes</td><td class='celdaNombreAttr'>$porc_profes%</td>";
		$grafico_profes->addPoint(new Point($grado_acad, $cant_profes));
	}
	
	$HTML .= "  <tr>"
		  .  "    <td class='celdaNombreAttr'>Total:</td>"
		  .  $HTML_grados_acad
		  .  "    <td class='celdaNombreAttr'>$total_profes</td>"
		  .  "  </tr>"
		  .  "</table>";

	$grafico->setDataSet($grafico_profes);
	$grafico->setTitle("Composición del Cuerpo Docente\npor Grado Académico");
	$grafico->render("graficos/comp_docentes_$ano_$semestre.png");
	
	$HTML = "<table bgcolor='#ffffff' cellspacing='0' cellpadding='0' class='tabla' style='margin-top: 8px; box-shadow: 1px 1px 4px #999'>"
	      . "  <tr class='filaTituloTabla'>"
	      . "    <td class='tituloTabla' colspan='2'><big>Composición del Cuerpo Docente por Grado Académico</big></td>"
	      . "  </tr>"
	      . "  <tr>"
	      . "    <td class='textoTabla' valign='top'>$HTML</td>"
	      . "    <td class='textoTabla'><img src='graficos/comp_docentes_$ano_$semestre.png'></td>"
	      . "  </tr>"
	      . "</table>";
	return $HTML;
}

?>
