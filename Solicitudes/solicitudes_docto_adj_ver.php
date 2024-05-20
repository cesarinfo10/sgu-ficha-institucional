<?php

include("../funciones.php");

$id = $_REQUEST['id_docto_adj'];

$docto_adj = consulta_sql("SELECT archivo,tipo FROM gestion.solic_doctos_adj WHERE id='$id'");
if (count($docto_adj) > 0) {
	$tipo = $docto_adj[0]['tipo'];
	header('Cache-control: cache, store');
	header('Content-type: application/pdf');
	header("Content-Disposition: attachment; filename=\"$tipo$id.pdf\"");
	echo pg_unescape_bytea($docto_adj[0]['archivo']);
}

echo(js("window.close();"));

?>
