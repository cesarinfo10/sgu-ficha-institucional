<?php

include("funciones.php");

$id_doctos_digitalizados = $_REQUEST['id_doctos_digitalizados'];

//$evidencia = consulta_sql("SELECT evidencia,evidencia_mime,evidencia_ext,evidencia_filename FROM gestion.poas WHERE id=$id");
$evidencia = consulta_sql("SELECT nombre_archivo,  mime evidencia_mime, archivo FROM capac_doctos_digitalizados where id=$id_doctos_digitalizados");

if (count($evidencia) > 0) {
	//$evidencia_ext      = $evidencia[0]['evidencia_ext'];
	$evidencia_mime     = $evidencia[0]['evidencia_mime'];
	//$evidencia_filename = $evidencia[0]['evidencia_filename'];
	
	$filename = $evidencia[0]['nombre_archivo']; //$evidencia_filename."_evidencia_".$id."_".".".$evidencia_ext;
	
	header('Cache-control: cache, store');
	header('Content-type: $evidencia_mime');
	header("Content-Disposition: attachment; filename=\"$filename\"");
	echo pg_unescape_bytea($evidencia[0]['archivo']);
}

echo(js("window.close();"));

?>
