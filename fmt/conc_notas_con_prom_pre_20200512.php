<?php

//include("conversor_num2palabras.php");

$ID_MALLA_ELECTIVOS = 19;

$SQL_alumno = "SELECT va.rut,upper(a.nombres||' '||a.apellidos) AS nombre,a.cohorte,a.semestre_cohorte,a.mes_cohorte,c.nombre AS carrera,
                      to_char(a.fecha_titulacion,'DD \"de\" tmMonth \"de\" YYYY') AS fecha_titulacion,a.malla_actual,va.malla_actual AS ano_malla_actual,
                      CASE a.genero WHEN 'f' THEN 'doña' WHEN 'm' THEN 'don' END AS vocativo,va.estado,
                      CASE a.jornada WHEN 'D' THEN 'diurna' WHEN 'V' THEN 'vespertina' END AS jornada
               FROM alumnos AS a
               LEFT JOIN vista_alumnos AS va USING (id)
               LEFT JOIN carreras AS c ON c.id=a.carrera_actual
               WHERE a.id=$id_alumno";
$alumno = consulta_sql($SQL_alumno);
if (count($alumno) == 0) {
}
extract($alumno[0],EXTR_PREFIX_ALL,'al');

$SQL_detalle_malla = "SELECT vdm.id_prog_asig,vdm.cod_asignatura,vdm.asignatura,vdm.nivel,vdm.caracter,dm1.pond_tns,dm1.pond_ga,dm1.pond_tp,dm1.pond_otros
                      FROM vista_detalle_malla AS vdm
                      LEFT JOIN detalle_mallas AS dm1 USING (id)
                      WHERE vdm.id_malla=$al_malla_actual";
	
$SQL_alumno_ca = "SELECT CASE WHEN ca.id_curso IS NOT NULL THEN c.id_prog_asig
                              WHEN ca.id_pa IS NOT NULL AND ca.convalidado THEN ca.id_pa
                              WHEN ca.id_pa_homo IS NOT NULL AND ca.homologada THEN ca.id_pa_homo
                              WHEN ca.id_pa IS NOT NULL AND ca.examen_con_rel THEN ca.id_pa
                         END AS id_prog_asig,
                         CASE WHEN ca.id_curso IS NOT NULL THEN greatest(1,c.semestre)||'° / '||c.ano
                              WHEN ca.convalidado THEN '$al_semestre_cohorte'||'° / $al_cohorte'
                              WHEN ca.homologada OR ca.examen_con_rel THEN CASE WHEN date_part('month',ca.fecha_mod) <= 7 THEN 1 ELSE 2 END ||'° / '|| date_part('year',ca.fecha_mod)
                         END AS periodo,
                         CASE WHEN ca.id_curso IS NOT NULL THEN ca.nota_final::numeric(2,1)::text
                              WHEN ca.convalidado THEN 'APC'
                              WHEN ca.homologada THEN 'APH'
                              WHEN ca.examen_con_rel THEN 'APECR'
                         END AS nf,ca.id_estado,ca.nota_final
                  FROM cargas_academicas AS ca
                  LEFT JOIN cursos AS c ON c.id=ca.id_curso
                  LEFT JOIN ca_estados AS cae ON cae.id=ca.id_estado
                  WHERE ca.id_alumno=$id_alumno AND ca.id_estado IN (1,3,4,5)
                  ORDER BY periodo DESC";

$SQL_avance_malla = "SELECT dm.cod_asignatura||' '||dm.asignatura AS asignatura,dm.nivel,dm.caracter,
                            coalesce(aca.nf,'** No cursada **') AS nf,
                            aca.periodo AS periodo,dm.pond_tns,dm.pond_ga,dm.pond_tp,dm.pond_otros
                     FROM ($SQL_detalle_malla) AS dm
                     LEFT JOIN ($SQL_alumno_ca) AS aca ON aca.id_prog_asig=dm.id_prog_asig
                     WHERE aca.nf IS NOT NULL OR dm.caracter='Electiva'
                     ORDER BY dm.nivel,cod_asignatura;";

$asig_alumno = consulta_sql($SQL_avance_malla);
//var_dump($asig_alumno);

$al_cohorte  = "$al_semestre_cohorte-$al_cohorte ";
$al_cohorte .= ($al_mes_cohorte > 0) ? "({$meses_palabra[$al_mes_cohorte-1]['nombre']})" : "";

$SQL_electivos = "SELECT id_prog_asig FROM vista_detalle_malla WHERE id_malla=$ID_MALLA_ELECTIVOS 
                  EXCEPT ALL 
                  SELECT id_prog_asig FROM vista_detalle_malla WHERE id_malla=$al_malla_actual";

$SQL_alumno_ca_efg = "SELECT asignatura,nf,semestre||'° / '||ano as periodo 
                      FROM vista_alumnos_cursos 
                      WHERE id_alumno=$id_alumno AND id_prog_asig IN ($SQL_electivos) AND id_estado IN (1,3,4,5)";
$alumno_ca_efg = consulta_sql($SQL_alumno_ca_efg);
if (count($alumno_ca_efg) > 0) {
	$j=0;
	for ($x=0;$x<count($asig_alumno);$x++) {
		if ($asig_alumno[$x]['caracter'] == "Electiva" && $asig_alumno[$x]['nf'] == "** No cursada **" && $alumno_ca_efg[$j]['nf'] <> "") {
			$asig_alumno[$x]['asignatura'] .= ": ".$alumno_ca_efg[$j]['asignatura'];
			$asig_alumno[$x]['nf']          = $alumno_ca_efg[$j]['nf'];
			$asig_alumno[$x]['periodo']     = $alumno_ca_efg[$j]['periodo'];
			$j++;
		}
	}
}

$aAsig_alumno = array(); $j=0;
for ($x=0;$x<count($asig_alumno);$x++) {
	if ($asig_alumno[$x]['nf'] <> "** No cursada **") { $aAsig_alumno[$j] = $asig_alumno[$x]; $j++; }
}
$asig_alumno = $aAsig_alumno;

// Despliegue de asignaturas y notas
$nivel_aux = $sum_notas = $trab_grado1 = $trab_grado2 = 0;
$HTML = "<table border='0.1' cellspacing='0' cellpadding='1' width='100%' class='tabla'>"
      . "  <tr>"
      . "    <td align='center' valign='middle'><b>Nombre de la Asignatura</b></td>"
      . "    <td align='center' valign='middle' width='10%'><b>Nota</b></td>"
      . "    <td align='center' valign='middle' width='5%'><b>Periodo</b></td>"
      . "  </tr>";
      
$cont_apc = $cont_aph = $cont_apecr = $cont_noaprob = 0;
$sum_ga = $cont_asig_ga = $cont_asig_cur = 0;
$pag = 1; $filas = 0;
      
for ($x=0;$x<count($asig_alumno);$x++) {
	extract($asig_alumno[$x]);
	
	if ($nivel_aux <> $nivel) { 
		$HTML .= "<tr><td colspan='3' bgcolor='#E5E5E5'><i>{$NIVELES[$nivel-1]['nombre']} Semestre</i></td></tr>";
	}
	
	switch ($nf) {
		case "APC":
			$nf_palabras = "";
			$cont_apc++;
			break;
		case "APH":
			$nf_palabras = "";
			$cont_aph++;
			break;
		case "APECR":
			$nf_palabras = "";
			$cont_apecr++;
			break;
		case "** No cursada **":
			$nf_palabras = "";
			$cont_noaprob++;
			break;
		default:
			$nf = floatval($nf);
			$cont_asig_cur++;
			if ($pond_ga == 0) { $sum_ga += $nf; $cont_asig_ga++;}
			$nf_palabras = calificacion_palabras($nf);
			/*
			list($nf_entero,$nf_decimal) = explode(",","$nf");
			$nf_entero = num2palabras($nf_entero);
			$nf_decimal = num2palabras($nf_decimal);
			if ($nf_decimal == "un") { $nf_decimal = "uno"; } elseif ($nf_decimal == "") { $nf_decimal = "cero"; }
			$nf_palabras = "($nf_entero coma $nf_decimal)";
			*/
			$sum_notas += $nf;
			$nf = number_format($nf,1,",",".");
	}
	
	if ($periodo == "") { $periodo = "&nbsp;"; }
	
	$HTML .= "  <tr>"
	      .  "    <td>&nbsp;&nbsp;$asignatura</td>"
	      .  "    <td align='left'>&nbsp; $nf <small>$nf_palabras</small></td>"
	      .  "    <td align='center'>$periodo</td>"
	      .  "  </tr>";
	$nivel_aux = $nivel;
	$filas++;
	if (($filas > 20 && $pag == 1) || ($filas > 25 && $pag > 1)) { $HTML .= "<!-- PAGE BREAK -->"; $pag++; $filas = 0; }
}
$prom_notas = round($sum_notas/$cont_asig_cur,1);
$prom_notas_palabras = calificacion_palabras($prom_notas);
$HTML .= "  <tr><td colspan='3' bgcolor='#E5E5E5'>&nbsp;</td></tr>"
      .  "  <tr>"
	  .  "    <td align='right'>Promedio de asignaturas cursadas &nbsp;</td>"
	  .  "    <td align='left' colspan='2'>&nbsp; $prom_notas <small>($prom_notas_palabras)</small></td>"
	  .  "  </tr>"
	  .  "</table>";
$ASIGNATURAS_NOTAS = $HTML;

$texto_completacion_plan_estudios = "";
switch($al_estado) {
	case "Egresado":
		$texto_completacion_plan_estudios = "ha aprobado la totalidad de las asignaturas del plan de estudios año $al_ano_malla_actual, "
		                                  . "salvo el proceso de Graduación o Titulación,";
		break;
	case "Titulado":
	case "Licenciado":
	case "Graduado":
		$texto_completacion_plan_estudios = "ha aprobado la totalidad de las asignaturas del plan de estudios año $al_ano_malla_actual";
		break;
	default:
		$texto_completacion_plan_estudios = "ha aprobado parcialmente las asignaturas del plan de estudios año $al_ano_malla_actual";
		break;
}
		
$LEYENDA_NOTAS = "<small>";
if ($cont_apc > 0)     { $LEYENDA_NOTAS .= "  APC: Aprobado por Convalidación<br>"; }
if ($cont_aph > 0)     { $LEYENDA_NOTAS .= "  APH: Aprobado por Homologación<br>"; }
if ($cont_apecr > 0)   { $LEYENDA_NOTAS .= "  APECR; Aprobado por Examen de Conocimientos Relevantes<br>"; }
if ($cont_noaprob > 0) { $LEYENDA_NOTAS .= "  ** No cursada **: No cursada en esta instución o reprobada<br>"; }
$LEYENDA_NOTAS .= "</small>";

$TEXTO = "<h2 align='center'>CERTIFICADO DE CONCENTRACIÓN DE NOTAS</h2>"
       
       . "<p align='justify'>"
       . "La Secretaria General que suscribe, certifica que $vocativo_alumno <b>$nombre_alumno</b>, R.U.T. Nº <b>$rut_alumno</b> "
       . "de la cohorte <b>$cohorte</b> $texto_completacion_plan_estudios de la carrera o programa <b>$carrera_alumno</b>, "
       . "en jornada $jornada_alumno, de esta Casa de Estudios Superiores, según se detalla en el cuadro siguiente:"
       . "</p>"
       
       . $ASIGNATURAS_NOTAS
       
       . "<p align='left'>"
       . $LEYENDA_NOTAS
       . "<br>"
       
       . "NOTA: Escala de calificaciones de 1,0 a 7,0 con nota mínima de aprobación de 4,0<br>"
       . "<br>"
       
       . "Santiago, a $fecha_cert"
       . "</p>";

$texto_docto = $TEXTO;

?>
