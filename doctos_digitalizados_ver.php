<?php

include("funciones.php");

$id = $_REQUEST['id'];

$SQL_docto = "SELECT rut,dd.mime,ddt.nombre,nombre_archivo,archivo 
              FROM doctos_digitalizados dd 
              LEFT JOIN doctos_digital_tipos ddt on ddt.id=id_tipo 
              WHERE dd.id='$id';";
$docto     = consulta_sql($SQL_docto);
if (count($docto) > 0) {
	header("Cache-control: cache, store");
	header("Content-type: {$docto[0]['mime']}");
	header("Content-Disposition: attachment; filename=\"{$docto[0]['nombre']}_{$docto[0]['rut']}_{$docto[0]['nombre_archivo']}\"");
	echo pg_unescape_bytea($docto[0]['archivo']);
}

?>