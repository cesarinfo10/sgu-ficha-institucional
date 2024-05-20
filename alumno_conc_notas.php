<?php
session_start();
include("funciones.php");

$id_alumno = $_REQUEST['id_alumno'];

$modulo = "alumno_conc_notas";
include("validar_modulo.php");


if (!$_SESSION['autentificado'] || $id_alumno == "") {
	header("Location: index.php");
	exit;
}

$alumno = consulta_sql("SELECT c.regimen,m.ano AS malla FROM alumnos AS a LEFT JOIN carreras AS c ON c.id=carrera_actual LEFT JOIN mallas AS m ON m.id=a.malla_actual WHERE a.id=$id_alumno");
if (count($alumno) > 0) {
	$regimen = trim($alumno[0]['regimen']);
	$malla   = trim($alumno[0]['malla']);
	if ($malla == "2018") {
		header("Location: alumno_conc_notas_$regimen"."_malla$malla.php?id_alumno=$id_alumno");
		exit;
	}
	header("Location: alumno_conc_notas_$regimen.php?id_alumno=$id_alumno");
} else {
	echo(js("window.close()"));
}
?>
