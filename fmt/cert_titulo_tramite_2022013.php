<?php 
$grados_y_o_titulos = "";
if ($nombre_titulo_alumno <> "") { $titulo = "el Título Profesional de <b>$nombre_titulo_alumno</b>"; }
if ($nombre_grado_alumno <> "") { $grado = "el Grado Académico de <b>$nombre_grado_alumno</b>"; }

if ($titulo <> "" && $grado <> "") { $grados_y_o_titulos = "$grado y $titulo"; }
elseif ($titulo <> "") { $grados_y_o_titulos = $titulo; }
elseif ($grado <> "") { $grados_y_o_titulos = $grado; }

$texto_docto = "<h2 align='center'>CERTIFICADO DE TÍTULO EN TRÁMITE</h2>"
             . "<p align='justify'>"
             . "La Secretaria General de la Universidad Miguel de Cervantes que suscribe certifica "
			 . "que $vocativo_alumno <b>$nombre_alumno</b>, Rut  Nº <b>$rut_alumno</b>, de la "
			 . "carrera de <b>$carrera_alumno</b>, rindió Examen para obtener $grados_y_o_titulos, "
			 . "el día <b>$examen_grado_titulo_fecha_alumno</b>, aprobándolo satisfactoriamente con "
			 . "nota <b>$examen_grado_titulo_calif_alumno</b> ($examen_grado_titulo_calif_palabras)."
			 . "<br><br>"
			 . "El Expediente de Graduación y Títulación se encuentra en trámite en esta Casa de "
			 . "Estudios Superiores."
			 . "<br><br>"
			 . "Esta Universidad autónoma, otorga grados y títulos oficiales, cumpliendo con las exigencias establecidas en el DFL N.º 2, de 2009, del Ministerio de Educación, y está autorizada por dicho Ministerio de Educación por Decreto exento N°1169, de 27 de noviembre de 1997, publicado en el Diario Oficial de 17 de diciembre del mismo año."
			 . $texto_adicional
			 . "Santiago, $fecha_cert."
			 . "</p>";
?>
