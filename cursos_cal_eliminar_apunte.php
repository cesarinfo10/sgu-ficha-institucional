<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_cal_apunte = $_REQUEST['id_cal_apunte'];
$id_curso      = $_REQUEST['id_curso'];
$conf          = $_REQUEST['conf'];

if (!is_numeric($id_cal_apunte)) {
	echo(js("location.href='principal.php?modulo=gestion_cursos';"));
	exit;
}

$cal_apuntes = consulta_sql("SELECT nombre_archivo FROM cal_apuntes WHERE id=$id_cal_apunte");
if (count($cal_apuntes) == 0) {
	echo(js("location.href='principal.php?modulo=gestion_cursos';"));
	exit;
}

if (count($cal_apuntes) == 1 && empty($conf)) {
	$nombre_archivo = $cal_apuntes[0]['nombre_archivo'];
	$msje = "Está seguro de eliminar el archivo \'$nombre_archivo\'?";
	$conf = md5($id_cal_apunte);
	$url_si = "$enlbase_sm=cursos_cal_eliminar_apunte&id_cal_apunte=$id_cal_apunte&conf=$conf&id_curso=$id_curso";
	$url_no = "$enlbase_sm=cursos_calendarizacion&id_curso=$id_curso";
	echo(confirma_js($msje,$url_si,$url_no));
	exit;
}

if (count($cal_apuntes) == 1 && $conf == md5($id_cal_apunte)) {
	$SQLDEL_apunte = "DELETE FROM cal_apuntes WHERE id=$id_cal_apunte";
	consulta_dml($SQLDEL_apunte);
	echo(js("window.location='$enlbase_sm=cursos_calendarizacion&id_curso=$id_curso';"));
	exit;
}

?>
