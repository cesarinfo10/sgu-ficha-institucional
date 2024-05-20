<?php

include("funciones.php");

$id = $_REQUEST['id'];

$docto = consulta_sql("SELECT archivo,archivo_mime,archivo_nombre,dt.nombre AS tipo_docto FROM vcm.documentos_act doctos LEFT JOIN vcm.documentos_act_tipo AS dt ON dt.id=doctos.id_tipo WHERE doctos.id=$id");
if (count($docto) == 1) {
	$tipo_docto     = $docto[0]['tipo_docto'];
	$archivo_nombre = $docto[0]['archivo_nombre'];
	$archivo_mime   = $docto[0]['archivo_mime'];
	
	$filename = "$tipo_docto [$id] $archivo_nombre";
	
	header('Cache-control: cache, store');
	header('Content-type: $archivo_mime');
	header("Content-Disposition: attachment; filename=\"$filename\"");
	echo pg_unescape_bytea($docto[0]['archivo']);
}

echo(js("window.close();"));

?>
