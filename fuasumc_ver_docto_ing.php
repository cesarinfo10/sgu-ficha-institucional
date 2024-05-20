<?php

include("funciones.php");

$id_docto = $_REQUEST['id_docto'];

$docto = consulta_sql("SELECT docto,tipo_docto FROM dae.fuas_doctos_ing WHERE id=$id_docto");
if (count($docto) > 0) {
	extract($docto[0]);
	header('Cache-control: cache, store');
	header('Content-type: application/pdf');
	header("Content-Disposition: attachment; filename=\"$tipo_docto_$id_docto.pdf\"");
	echo pg_unescape_bytea($docto);
}

?>