<?php
session_start();
include("funciones.php");

$modulo = "alumno_conc_notas";
include("validar_modulo.php");

$id_alumno = $_REQUEST['id_alumno'];

if (!$_SESSION['autentificado'] || $id_alumno == "") {
	header("Location: index.php");
	exit;
}

$alumno = consulta_sql("SELECT c.regimen FROM alumnos AS a LEFT JOIN carreras AS c ON c.id=carrera_actual WHERE a.id=$id_alumno");
if (count($alumno) > 0) {
	$regimen = trim($alumno[0]['regimen']);
	header("Location: alumno_conc_notas_parcial_$regimen.php?id_alumno=$id_alumno");
} else {
	echo(js("window.close()"));
}
?>
