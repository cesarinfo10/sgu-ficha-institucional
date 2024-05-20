<?php
session_start();
include("funciones.php");
include("conversor_num2palabras.php");

$ID_MALLA_ELECTIVOS = 19;

$fecha_emision = strftime("%e de %B de %Y");

$id_alumno = $_REQUEST['id_alumno'];

if (!$_SESSION['autentificado'] || !is_numeric($id_alumno)) {
	header("Location: index.php");
	exit;
}

$modulo = "alumno_conc_notas_PRE";
include("validar_modulo.php");

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
                     ORDER BY dm.nivel,cod_asignatura;";

$asig_alumno = consulta_sql($SQL_avance_malla);
//var_dump($asig_alumno);

$al_cohorte  = "$al_semestre_cohorte-$al_cohorte ";
$al_cohorte .= ($al_mes_cohorte > 0) ? "({$meses_palabra[$al_mes_cohorte-1]['nombre']})" : "";

if (count($asig_alumno) == 0) {
}

$SQL_electivos = "SELECT id_prog_asig FROM vista_detalle_malla WHERE id_malla=$ID_MALLA_ELECTIVOS 
                  EXCEPT ALL 
                  SELECT id_prog_asig FROM vista_detalle_malla WHERE id_malla=$al_malla_actual";

$SQL_alumno_ca_efg = "SELECT asignatura,nf,semestre||'° / '||ano as periodo 
                      FROM vista_alumnos_cursos 
                      WHERE id_alumno=$id_alumno AND id_prog_asig IN ($SQL_electivos) AND id_estado=1";
$alumno_ca_efg = consulta_sql($SQL_alumno_ca_efg);
if (count($alumno_ca_efg) > 0) {
	$j=0;
	for ($x=0;$x<count($asig_alumno);$x++) {
		if ($asig_alumno[$x]['caracter'] == "Electiva" && $asig_alumno[$x]['nf'] == "** No cursada **") {
			$asig_alumno[$x]['asignatura'] .= ": ".$alumno_ca_efg[$j]['asignatura'];
			$asig_alumno[$x]['nf'] = $alumno_ca_efg[$j]['nf'];
			$asig_alumno[$x]['periodo'] = $alumno_ca_efg[$j]['periodo'];
			$j++;
		}
	}
}

// Despliegue de asignaturas y notas
$nivel_aux = $sum_notas = $trab_grado1 = $trab_grado2 = 0;
$HTML = "<table border='0.1' cellspacing='0' cellpadding='2' width='100%'>"
      . "  <tr>"
      . "    <td align='center' valign='middle'><b>Nombre de la Asignatura</b></td>"
      . "    <td align='center' valign='middle'><b>Nota</b></td>"
      . "    <td align='center' valign='middle'><b>Periodo</b></td>"
      . "  </tr>";
      
$cont_apc = $cont_aph = $cont_apecr = $cont_noaprob = 0;
$sum_ga = $cont_asig_ga = 0;
      
for ($x=0;$x<count($asig_alumno);$x++) {
	extract($asig_alumno[$x]);
	
	if ($nivel_aux <> $nivel) { 
		$HTML .= "<tr><td colspan='3'>&nbsp;</td></tr><tr><td colspan='3' bgcolor='#E5E5E5'><i>{$NIVELES[$nivel-1]['nombre']} Semestre</i></td></tr>";
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
			if ($pond_ga == 0) { $sum_ga += $nf; $cont_asig_ga++;}
			list($nf_entero,$nf_decimal) = explode(",","$nf");
			$nf_entero = num2palabras($nf_entero);
			$nf_decimal = num2palabras($nf_decimal);
			if ($nf_decimal == "un") { $nf_decimal = "uno"; } elseif ($nf_decimal == "") { $nf_decimal = "cero"; }
			$nf_palabras = "($nf_entero coma $nf_decimal)";
			$nf = number_format($nf,1,",",".");
	}
	
	if ($periodo == "") { $periodo = "&nbsp;"; }
	
	$HTML .= "  <tr>"
	      .  "    <td>&nbsp;&nbsp;$asignatura</td>"
	      .  "    <td align='left'>&nbsp; $nf <small>$nf_palabras</small></td>"
	      .  "    <td align='center'>$periodo</td>"
	      .  "  </tr>";
	$nivel_aux = $nivel;
	$sum_notas += $nf;
}
$HTML .= "</table>";
$ASIGNATURAS_NOTAS = $HTML;


// Tabla de ponderaciones
$HTML = "<!-- PAGE BREAK -->"
      . "<center><b>Tabla de Ponderaciones Final</b></center>"
      . "<table border='1' cellspacing='0' cellpadding='1' width='100%'>"
      . "  <tr>"
      . "    <td></td>"
      . "    <td align='center'><b>Notas</b></td>"
      . "    <td align='center'><b>Ponderación</b></td>"
      . "    <td align='center'><b>Calificación Ponderada</b></td>"
      . "  </tr>";
$prom_general      = round($sum_ga/$cont_asig_ga,1);
$pond_prom_general = 0.45;
$prom_general_pond = $prom_general * $pond_prom_general;      
$HTML .= "  <tr>"
      .  "    <td>Promedio General Asignaturas</td>"
      .  "    <td align='center'>".number_format($prom_general,1,",",".")."</td>"
      .  "    <td align='center'>". $pond_prom_general*100 ."%</td>"
      .  "    <td align='center'>".number_format($prom_general_pond,2,",",".")."</td>"
      .  "  </tr>";
$examen_anual_1      = floatval($al_examen_anual_1);
$pond_examen_anual_1 = 0.1;
$examen_anual_1_pond = $examen_anual_1 * $pond_examen_anual_1;
$HTML .= "  <tr>"
      .  "    <td>Examen Anual (1)</td>"
      .  "    <td align='center'>".number_format($examen_anual_1,1,",",".")."</td>"
      .  "    <td align='center'>". $pond_examen_anual_1*100 ."%</td>"
      .  "    <td align='center'>".number_format($examen_anual_1_pond,2,",",".")."</td>"
      .  "  </tr>";      
$examen_anual_2      = floatval($al_examen_anual_2);
$pond_examen_anual_2 = 0.1;
$examen_anual_2_pond = $examen_anual_2 * $pond_examen_anual_2;
$HTML .= "  <tr>"
      .  "    <td>Examen Anual (2)</td>"
      .  "    <td align='center'>".number_format($examen_anual_2,1,",",".")."</td>"
      .  "    <td align='center'>". $pond_examen_anual_2*100 ."%</td>"
      .  "    <td align='center'>".number_format($examen_anual_2_pond,2,",",".")."</td>"
      .  "  </tr>";      
$pond_trab_grado_1 = 0.15;
$trab_grado_1_pond = $trab_grado1 * $pond_trab_grado_1;
$HTML .= "  <tr>"
      .  "    <td>Trabajo de Grado I</td>"
      .  "    <td align='center'>".number_format($trab_grado1,1,",",".")."</td>"
      .  "    <td align='center'>". $pond_trab_grado_1*100 ."%</td>"
      .  "    <td align='center'>".number_format($trab_grado_1_pond,2,",",".")."</td>"
      .  "  </tr>";      
$pond_trab_grado_2 = 0.2;
$trab_grado_2_pond = $trab_grado2 * $pond_trab_grado_2;
$HTML .= "  <tr>"
      .  "    <td>Trabajo de Grado II</td>"
      .  "    <td align='center'>".number_format($trab_grado2,1,",",".")."</td>"
      .  "    <td align='center'>". $pond_trab_grado_2*100 ."%</td>"
      .  "    <td align='center'>".number_format($trab_grado_2_pond,2,",",".")."</td>"
      .  "  </tr>";
$prom_final = round($prom_general_pond + $examen_anual_1_pond + $examen_anual_2_pond + $trab_grado_1_pond + $trab_grado_2_pond,1);
$HTML .= "  <tr>"
      .  "    <td align='right'><b>Promedio Final de Graduación</b></td>"
      .  "    <td align='center'></td>"
      .  "    <td align='center'></td>"
      .  "    <td align='center'><b>".number_format($prom_final,1,",",".")."</b></td>"
      .  "  </tr>";
$HTML .= "</table>";

$TABLA_POND_NOTA_FINAL = "";
if ($al_estado == "Titulado" || $al_estado == "Licenciado" || $al_estado == "Graduado") {
	$TABLA_POND_NOTA_FINAL = $HTML;
}

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
if ($cont_aph > 0)     { $LEYENDA_NOTAS .= "  APH: Aprobado por Homologación<br>"; }
if ($cont_apc > 0)     { $LEYENDA_NOTAS .= "  APC: Aprobado por Convalidación<br>"; }
if ($cont_apecr > 0)   { $LEYENDA_NOTAS .= "  APECR: Aprobado por Examen de Conocimientos Relevantes<br>"; }
if ($cont_noaprob > 0) { $LEYENDA_NOTAS .= "  ** No cursada **: No cursada en esta instución o reprobada<br>"; }
$LEYENDA_NOTAS .= "</small>";

$TITULO = "<b>CONCENTRACIÓN DE NOTAS</b>";

$TEXTO = "La Secretaria General que suscribe, certifica que $al_vocativo <b>$al_nombre</b>, R.U.T. Nº <b>$al_rut</b> "
       . "de la cohorte <b>$al_cohorte</b> $texto_completacion_plan_estudios de la carrera o programa <b>$al_carrera</b>, "
       . "en jornada $al_jornada, de esta Casa de Estudios Superiores, según se detalla en el cuadro siguiente:<br><br>"
       . $ASIGNATURAS_NOTAS
       . "<br>"
       . $LEYENDA_NOTAS
       . "<br>"
       . $TABLA_POND_NOTA_FINAL
       . "<br>"
       . "NOTA: Escala de calificaciones de 1,0 a 7,0 con nota mínima de aprobación de 4,0<br>"
       . "<br>"
       . "Santiago, a $fecha_emision";
       
$FIRMAS = "<table width='100%'>".$LF
		. "  <tr>".$LF
		. "    <td align='center' valign='bottom' width='45%'></td>".$LF
		. "    <td align='center' valign='top' width='10%'></td>".$LF
		. "    <td align='center' valign='bottom' width='45%'><hr noshade size='2'><b>$SECRETARIA_GENERAL_nombre</b><br>Secretaría General</td>".$LF
		. "  </tr>".$LF
		. "</table>".$LF;


$HTML = "<html>".$LF
      . "  <head>".$LF
      . "    <title>UMC - SGU - Concentración de Notas: $al_nombre</title>".$LF
      . "    <style>".$LF
      . "      td { font-size: 12px; font-family: sans,arial,helvetica; }".$LF
      . "      @media print {".$LF
      . "        @page {page-break-after: always; size: 21.5cm 25cm; }".$LF
      . "        td { font-size: 12px; font-family: sans,arial,helvetica; }".$LF
      . "      }".$LF
      . "    </style>".$LF
      . "  </head>".$LF
      . "  <body>".$LF
      . "    <table width='100%'>".$LF
      . "      <tr>".$LF
      . "        <td>".$LF
      . "          <table width='100%'><tr><td align='center'>$TITULO</td></tr></table><br><br>".$LF
      . "          <table width='100%'><tr><td valign='top' align='justify'>$TEXTO</td></tr></table><br><br><br>".$LF
      . "          <table width='100%'><tr><td valign='top' align='center'>$FIRMAS</td></tr></table><br><br>".$LF
      . "        </td>".$LF
      . "      </tr>".$LF
      . "    </table>".$LF
      . "  </body>".$LF
      . "</html>".$LF;

$archivo = "concentracion_notas_PRE_".$id_alumno;
$HTML = iconv("UTF-8","ISO-8859-1//TRANSLIT",$HTML);	
$hand=fopen("tmp/cn/".$archivo.".html","w");
fwrite($hand,$HTML);
fclose($hand);
$html2pdf = "htmldoc -t pdf --fontsize 11 --fontspacing 1 --no-strict --size 21.5x27cm --bodyfont helvetica "
		  . "--left 1cm --top 5cm --right 1cm --bottom 0.8cm --footer 't /' --header '   ' --no-embedfonts "
		  . "--compression=9 --permissions print,no-copy,no-annotate,no-modify "
		  . "--webpage tmp/cn/$archivo.html ";
header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=$archivo.pdf");
passthru($html2pdf);

unlink("tmp/cn/".$archivo.".html");

//echo(js("window.close();"));
		

?>
