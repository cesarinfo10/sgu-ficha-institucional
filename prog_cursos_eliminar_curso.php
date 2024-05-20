<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

//include("validar_modulo.php");

if (is_numeric($_REQUEST['id_prog_curso']) && is_numeric($_REQUEST['id_pc_det'])) {
	$id_prog_curso = $_REQUEST['id_prog_curso'];
	$id_pc_det     = $_REQUEST['id_pc_det'];
} else {
	echo(js("window.location='$enlbase=';"));
	exit;
}

if ($_REQUEST['elimina'] == "") {

	$condicion = "";
	if ($_SESSION['tipo'] == 1 && $_SESSION['tipo'] == 2) {
		$condicion = "AND NOT (vobo_vra OR vobo_vraf)";
	}
	
	$SQL_pc_det = "SELECT cod_asignatura||'-'||seccion||' '||asignatura AS asig
	               FROM prog_cursos_detalle pcd
	               LEFT JOIN vista_prog_asig AS vpa ON vpa.id=pcd.id_prog_asig 
	               WHERE pcd.id=$id_pc_det AND pcd.id_prog_curso=$id_prog_curso $condicion";
	$pc_det = consulta_sql($SQL_pc_det);
	if (count($pc_det) == 1) {
		$elimina = md5($id_pc_det);
		$url_si = "$enlbase=prog_cursos_eliminar_curso&id_prog_curso=$id_prog_curso&id_pc_det=$id_pc_det&elimina=$elimina";
		$url_no = "$enlbase=prog_cursos_ver&id_prog_curso=$id_prog_curso";
		echo(confirma_js("Está seguro de eliminar el curso «{$pc_det[0]['asig']}» de esta programación?",$url_si,$url_no));
	} else {
		echo(js("Este curso ya está aprobado por VRA o bien por VRAF. No lo puede borrar usted."));
	}
} elseif ($_REQUEST['elimina'] == md5($id_pc_det)) {
	$SQLdelete_pc_det = "DELETE FROM prog_cursos_detalle WHERE id=$id_pc_det AND id_prog_curso=$id_prog_curso";
	consulta_dml($SQLdelete_pc_det);
}
echo(js("window.location='$enlbase=prog_cursos_ver&id_prog_curso=$id_prog_curso';"));	
exit;

?>

<!-- Inicio: <?php echo($modulo); ?> -->


<!-- Fin: <?php echo($modulo); ?> -->
