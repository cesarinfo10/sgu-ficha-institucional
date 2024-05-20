<?php 

if ($rpnp_alumno <> "") {
	$RPNP = "El RPNP es el N° $rpnp_alumno.";
}

$SQL_asig_alumno = "SELECT vac.asignatura,nivel,s1,nc,s2,nf 
                    FROM vista_alumnos_cursos AS vac
                    LEFT JOIN vista_detalle_malla AS vdm ON (vdm.id_prog_asig=vac.id_prog_asig AND vdm.id_malla=$al_malla_actual)
					WHERE id_alumno=$id_alumno AND situacion='Aprobado'
					ORDER BY nivel,asignatura";
$asig_alumno = consulta_sql($SQL_asig_alumno);
if (count($asig_alumno) == 0) {
}
extract($asig_alumno[0]);

$nota_final = round($s1 * 0.3 + $nc * 0.3 + $s2 * 0.4,1);

$nf = floatval($nota_final);
list($nf_entero,$nf_decimal) = explode(",","$nf");
$nf_entero = num2palabras($nf_entero);
$nf_decimal = num2palabras($nf_decimal);
if ($nf_decimal == "un") { $nf_decimal = "uno"; } elseif ($nf_decimal == "") { $nf_decimal = "cero"; }
$nota_final_palabras = "($nf_entero coma $nf_decimal)";

$duracion_carrera_alumno = num2palabras($duracion_carrera_alumno);

$texto_docto = "<h2 align='center'>CERTIFICADO</h2><p align='justify'>"
             . "La Secretaria General de la Universidad Miguel de Cervantes que suscribe certifica que $vocativo_alumno <b>$nombre_alumno</b>, Rut Nº <b>$rut_alumno</b>, "
             . "ha cumplido con todas las exigencias y requisitos académicos reglamentarios y ha obtenido nota $nota_final $nota_final_palabras, por lo cual "
             . "se le ha otorgado el <b>$carrera_alumno</b> (cohorte $mes_cohorte $cohorte). "
             . "<br><br>"
             . "Asimismo, también se certifica que el mencionado Programa de Postítulo se imparte a distancia, durante $duracion_carrera_alumno semestres, "
             . "con un total de $horas_totales_carrera_alumno horas pedagógicas, desde el $fecha_inicio_programa al $fecha_titulacion_alumno. $RPNP "
             . "<br><br>"
             . "Esta Universidad autónoma, otorga grados y títulos oficiales, cumpliendo con las exigencias establecidas en el DFL N.º 2, de 2009, "
             . "del Ministerio de Educación, y está autorizada por dicho Ministerio de Educación por Decreto exento N°1169, de 27 de noviembre de "
             . "1997, publicado en el Diario Oficial de 17 de diciembre del mismo año."
             . "<br><br>"
             . "Se extiende el presente certificado a petición del(a) interesado(a) para los fines que estime convenientes."
             . "<br><br>"
             . "Santiago, $fecha_cert."
             . "</p>";

?>
