<?php

include("funciones.php");

$id = $_REQUEST['id'];

$SQL_cal_apunte = "SELECT nombre_archivo,tipo_mime,archivo FROM cal_apuntes WHERE id='$id';";
$cal_apunte     = consulta_sql($SQL_cal_apunte);

if (count($cal_apunte) > 0) {
	header("Cache-control: cache, store");
	header("Content-type: {$cal_apunte[0]['tipo_mime']}");
	header("Content-Disposition: attachment; filename=\"{$cal_apunte[0]['nombre_archivo']}\"");
	echo pg_unescape_bytea($cal_apunte[0]['archivo']);
}

?>