<?php 

$condicion_estado = "egresado y Licenciado";
if ($vocativo_alumno == "doña") { $condicion_estado = "egresada y Licenciada"; }

$texto_docto = "<h2 align='center'>C E R T I F I C A D O</h2><p align='justify'>
La Secretaria General que suscribe certifica que $vocativo_alumno <b>$nombre_alumno</b>, Rut Nº <b>$rut_alumno</b>, $condicion_estado en Ciencias Jurídicas de esta Institución, no registra durante su vida académica amonestaciones, anotaciones negativas y/o sumarios en su hoja de vida por transgresiones a las disposiciones reglamentarias y disciplinarias de la Corporación Universidad Miguel de Cervantes.

Esta Universidad autónoma, otorga grados y títulos oficiales, cumpliendo con las exigencias establecidas en el DFL N.º 2, de 2009, del Ministerio de Educación, y está autorizada por dicho Ministerio de Educación por Decreto exento N°1169, de 27 de noviembre de 1997, publicado en el Diario Oficial de 17 de diciembre del mismo año.

$texto_adicional

Santiago, $fecha_cert.
</p>";

?>
