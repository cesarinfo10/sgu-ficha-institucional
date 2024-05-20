<?php

include("funciones.php");

$id_profesor = $_REQUEST['id_profesor'];

$SQL_profe_cv = "SELECT data FROM usuarios WHERE id='$id_profesor';";

$profe_cv = consulta_sql("SELECT arch_cv FROM usuarios WHERE id='$id_profesor'");
if (count($profe_cv) > 0) {
	header('Cache-control: cache, store');
	header('Content-type: application/pdf');
	header("Content-Disposition: attachment; filename=\"cv_profesor_$id_profesor.pdf\"");
	echo pg_unescape_bytea($profe_cv[0]['arch_cv']);
}

?>