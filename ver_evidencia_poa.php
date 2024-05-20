<?php

include("funciones.php");

$id = $_REQUEST['id_tarea'];

$evidencia = consulta_sql("SELECT evidencia,evidencia_mime,evidencia_ext,evidencia_filename FROM gestion.poas WHERE id=$id");
if (count($evidencia) > 0) {
	$evidencia_ext      = $evidencia[0]['evidencia_ext'];
	$evidencia_mime     = $evidencia[0]['evidencia_mime'];
	$evidencia_filename = $evidencia[0]['evidencia_filename'];
	
	$filename = $evidencia_filename."_evidencia_".$id."_".".".$evidencia_ext;
	
	header('Cache-control: cache, store');
	header('Content-type: $evidencia_mime');
	header("Content-Disposition: attachment; filename=\"$filename\"");
	echo pg_unescape_bytea($evidencia[0]['evidencia']);
}

echo(js("window.close();"));

?>