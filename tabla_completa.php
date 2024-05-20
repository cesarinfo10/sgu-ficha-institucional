<?php
session_start();

$modulo="tabla_completa";

include("funciones.php");

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_sesion = $_REQUEST['id_sesion'];

$archivo = "sql-fulltables/$id_sesion.sql";

if (file_exists($archivo)) {
	

	$data = shell_exec("psql -U sgu -h 10.111.0.113 -f $archivo regacad");
	
	header("Content-Type: text/csv");
	header("Content-Disposition: attachment; filename=$id_sesion.csv");
	echo($data);
	flush();
	//passthru($data);
	unlink($archivo);
	
}

//echo(js("window.close();"));

?>
