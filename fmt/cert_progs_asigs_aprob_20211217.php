<?php 


$forma_aprob = "";
switch ($estado) {
	case "Vigente":
	case "Suspendido":
	case "Abandono":
	case "Retirado":
	case "Eliminado":
		$forma_aprob = "parcialmente";
		break;
	case "Licenciado":
	case "Graduado":
	case "Titulado":
	case "Egresado":
		$forma_aprob = "completamente";
}

$texto_docto = "<h2 align='center'>C E R T I F I C A D O</h2><p align='justify'>
La Secretaria General que suscribe certifica que $vocativo_alumno <b>$nombre_alumno</b>, Rut Nº <b>$rut_alumno</b>, de la cohorte $cohorte, ha aprobado $forma_aprob las asignaturas del plan de estudios año $ano_malla de la carrera de $carrera_alumno, en esta Casa de Estudios Superiores.

Asimismo, se certifica que los documentos a partir de la página 2 corresponden a los programas de estudios de las asignaturas aprobadas.

Esta Universidad autónoma, otorga grados y títulos oficiales, cumpliendo con las exigencias establecidas en el DFL N.º 2, de 2009, del Ministerio de Educación, y está autorizada por dicho Ministerio de Educación por Decreto exento N°1169, de 27 de noviembre de 1997, publicado en el Diario Oficial de 17 de diciembre del mismo año.

$texto_adicional

Santiago, $fecha_cert.
</p>";

?>
