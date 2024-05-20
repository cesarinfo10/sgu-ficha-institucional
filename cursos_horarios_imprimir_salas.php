<?php

session_start();
include("funciones.php");

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

if (!is_numeric($_REQUEST['semestre']) || !is_numeric($_REQUEST['ano'])) {
	echo(js("window.location='$enlbase=cursos_horarios';"));
	exit;
} else {
	$semestre   = $_REQUEST['semestre'];
	$ano        = $_REQUEST['ano'];
	$vacias     = $_REQUEST['vacias'];
}

$salas = consulta_sql("SELECT trim(codigo) AS codigo FROM salas ORDER BY codigo");

for ($x=0;$x<count($salas);$x++) {
	include("cursos_horarios_imprimir.php?ano=$ano&semestre=$semestre&jornada=D&sala={$salas[$x]['codigo']}");
	echo("<!-- PAGE BREAK -->");
}

for ($x=0;$x<count($salas);$x++) {
	include("cursos_horarios_imprimir.php?ano=$ano&semestre=$semestre&jornada=V&sala={$salas[$x]['codigo']}");
	echo("<!-- PAGE BREAK -->");
}

?>
