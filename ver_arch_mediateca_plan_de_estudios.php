<?php

include("funciones.php");

$id = $_REQUEST['id'];

$SQL_arch_malla = "SELECT arch_nombre,arch_data FROM mallas_archivos WHERE id='$id';";
$arch_malla     = consulta_sql($SQL_arch_malla);
if (count($arch_malla) > 0) {
	header('Cache-control: cache, store');
	header('Content-type: application/pdf');
	header("Content-Disposition: attachment; filename=\"{$arch_malla[0]['arch_nombre']}\"");
	echo pg_unescape_bytea($arch_malla[0]['arch_data']);
}

?>