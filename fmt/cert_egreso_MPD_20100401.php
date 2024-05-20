<?php 

if ($rpnp_alumno <> "") {
	$RPNP = "El RPNP es el N° $rpnp_alumno.";
}

$texto_docto = "<h2 align='center'>CERTIFICADO DE EGRESO</h2><p align='justify'>"
             . "La Secretaria General de la Universidad Miguel de Cervantes que suscribe certifica que $vocativo_alumno <b>$nombre_alumno</b>, Rut Nº <b>$rut_alumno</b>, "
             . "ha cumplido con todas las exigencias y requisitos académicos del <b>Programa de $carrera_alumno</b> (cohorte $mes_cohorte $cohorte) y es egresado del programa mencionado. "
             . "<br><br>"
             . "Se deja constancia que el Programa mencionado se imparte a distancia, siendo su fecha de inicio el $fecha_inicio_programa y de término el $fecha_titulacion_alumno, el cual tuvo "
             . "una duración de $duracion_carrera_alumno semestres con un total de $horas_totales_carrera_alumno horas pedagógicas. $RPNP "
             . "<br><br>"
             . "Esta Universidad autónoma, otorga grados y títulos oficiales, cumpliendo con las exigencias establecidas en el DFL N.º 2, de 2009, del Ministerio de Educación, y está autorizada por dicho Ministerio de Educación por Decreto exento N°1169, de 27 de noviembre de 1997, publicado en el Diario Oficial de 17 de diciembre del mismo año."
             . "<br><br>"
             . $texto_adicional
             . "<br><br>"
             . "Santiago, $fecha_cert."
             . "</p>";

?>
