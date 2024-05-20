<?php

include("funciones.php");

$id = $_REQUEST['id'];

$profepost_cv = consulta_sql("SELECT cv FROM portalweb.profes_post WHERE id='$id'");
if (count($profepost_cv) > 0) {
	header('Cache-control: cache, store');
	header('Content-type: application/pdf');
	header("Content-Disposition: attachment; filename=\"cv_profepost_$id.pdf\"");
	echo pg_unescape_bytea($profepost_cv[0]['cv']);
}

echo(js("window.close();"));

?>