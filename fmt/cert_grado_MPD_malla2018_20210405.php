<?php 

if ($rpnp_alumno <> "") {
	$RPNP = "Además, está inscrito en el Registro Público Nacional de Profesionales bajo el N° $rpnp_alumno.";
}

$SQL_asig_alumno = "SELECT vac.asignatura,nivel,nf 
                    FROM vista_alumnos_cursos AS vac
                    LEFT JOIN vista_detalle_malla AS vdm ON (vdm.id_prog_asig=vac.id_prog_asig AND vdm.id_malla=$al_malla_actual)
					WHERE id_alumno=$id_alumno AND vac.id_estado IN (1,3,4)
					ORDER BY nivel,asignatura";
$asig_alumno = consulta_sql($SQL_asig_alumno);
if (count($asig_alumno) == 0) {
}
// Despliegue de asignaturas y notas
$nivel_aux = $sum_notas = $trab_grado1 = $trab_grado2 = 0;
for ($x=0;$x<count($asig_alumno);$x++) {
	extract($asig_alumno[$x]);
	$nf = floatval($nf);
	$sum_notas += $nf;
    /*
	if (stristr($asignatura,"Actividad de Aplicación") == "Actividad de Aplicación") { $trab_grado1 = $nf; $sum_notas -= $nf; }
	if (stristr($asignatura,"Actividad de Aplicación") == "Actividad de Aplicación Práctica (Análisis PEI)") { $trab_grado1 = $nf; $sum_notas -= $nf; }
	if (stristr($asignatura,"Trabajo de Grado") == "Trabajo de Grado")  { $trab_grado2 = $nf; $sum_notas -= $nf; }
    */
	if (stristr($asignatura,"Trabajo de Grado") == "Trabajo de Grado")  { $trab_grado = $nf; $sum_notas -= $nf; }
	if (stristr($asignatura,"Actividad de Aplicación") == "Actividad de Aplicación") { $act_aplic = $nf; $sum_notas -= $nf; }
	if (stristr($asignatura,"Actividad de Aplicación") == "Actividad de Aplicación Práctica (Análisis PEI)") { $act_aplic = $nf; $sum_notas -= $nf; }
}

/*
$prom_general      = round($sum_notas/(count($asig_alumno)-2),1);
$pond_prom_general = 0.45;
$prom_general_pond = $prom_general * $pond_prom_general;      
$examen_anual_1      = floatval($al_examen_anual_1);
$pond_examen_anual_1 = 0.1;
$examen_anual_1_pond = $examen_anual_1 * $pond_examen_anual_1;
$examen_anual_2      = floatval($al_examen_anual_2);
$pond_examen_anual_2 = 0.1;
$examen_anual_2_pond = $examen_anual_2 * $pond_examen_anual_2;
$pond_trab_grado_1 = 0.15;
$trab_grado_1_pond = $trab_grado1 * $pond_trab_grado_1;
$pond_trab_grado_2 = 0.2;
$trab_grado_2_pond = $trab_grado2 * $pond_trab_grado_2;
$nota_final = round($prom_general_pond + $examen_anual_1_pond + $examen_anual_2_pond + $trab_grado_1_pond + $trab_grado_2_pond,1);
*/

$prom_general      = round($sum_notas/(count($asig_alumno)-2),1);
$pond_prom_general = 0.45;
$prom_general_pond = $prom_general * $pond_prom_general;     

$examen_anual_1      = floatval($al_examen_anual_1);
$pond_examen_anual_1 = 0.1;
$examen_anual_1_pond = $examen_anual_1 * $pond_examen_anual_1;

$examen_anual_2      = floatval($al_examen_anual_2);
$pond_examen_anual_2 = 0.1;
$examen_anual_2_pond = $examen_anual_2 * $pond_examen_anual_2;

$pond_trab_grado = 0.15;
$trab_grado_pond = $trab_grado * $pond_trab_grado;

$pond_act_aplic = 0.2;
$act_aplic_pond = $act_aplic * $pond_act_aplic;

$nota_final = round($prom_general_pond + $examen_anual_1_pond + $examen_anual_2_pond + $trab_grado_pond + $act_aplic_pond,1);


$nf = floatval($nota_final);
list($nf_entero,$nf_decimal) = explode(",","$nf");
$nf_entero = num2palabras($nf_entero);
$nf_decimal = num2palabras($nf_decimal);
if ($nf_decimal == "un") { $nf_decimal = "uno"; } elseif ($nf_decimal == "") { $nf_decimal = "cero"; }
$nota_final_palabras = "($nf_entero coma $nf_decimal)";

$duracion_carrera_alumno = num2palabras($duracion_carrera_alumno);

$nota_final = number_format($nota_final,1,',','.');

$texto_docto = "<h2 align='center'>CERTIFICADO</h2><p align='justify'>"
             . "La Secretaria General de la Universidad Miguel de Cervantes que suscribe certifica que $vocativo_alumno <b>$nombre_alumno</b>, Rut Nº <b>$rut_alumno</b>, "
             . "ha cumplido con todas las exigencias y requisitos académicos reglamentarios y ha obtenido nota $nota_final $nota_final_palabras, "
             . "por lo cual se le ha otorgado el grado de <b>$carrera_alumno</b> (cohorte $mes_cohorte $cohorte)."
             . "<br><br>"
             . "Asimismo, también se certifica que el mencionado Programa se imparte a distancia, durante $duracion_carrera_alumno semestres "
             . "con un total de $horas_totales_carrera_alumno horas pedagógicas, desde el $fecha_inicio_programa al $fecha_titulacion_alumno. $RPNP "
             . "<br><br>"
             . "Esta Universidad autónoma, otorga grados y títulos oficiales, cumpliendo con las exigencias establecidas en el DFL N.º 2, de 2009, del Ministerio de Educación, y está autorizada por dicho Ministerio de Educación por Decreto exento N°1169, de 27 de noviembre de 1997, publicado en el Diario Oficial de 17 de diciembre del mismo año."
             . "<br><br>"
             . "Se extiende el presente certificado a petición del(a) interesado(a) para los fines que estime convenientes."
             . "<br><br>"
             . "Santiago, $fecha_cert."
             . "</p>";

?>
