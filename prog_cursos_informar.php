<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$EMAIL_VRA = "fortega@umcervantes.cl";

if (!is_numeric($_REQUEST['id_prog_curso'])) {
	echo(js("window.location='$enlbase=';"));
	exit;
} else {
	$id_prog_curso = $_REQUEST['id_prog_curso'];
	$confirmacion  = $_REQUEST['confirmacion'];
}


$SQL_prog_curso = "SELECT vpc.*,pc.fecha AS fecha_creacion
                   FROM vista_prog_cursos AS vpc
                   LEFT JOIN prog_cursos AS pc ON pc.id=vpc.id
                   WHERE vpc.id=$id_prog_curso";
$prog_curso = consulta_sql($SQL_prog_curso);
if (count($prog_curso) == 0) {
	echo(js("window.location='$enlbase=';"));
	exit;
}
extract($prog_curso[0],EXTR_PREFIX_ALL,"pc");

if ($confirmacion == "") {
	$confirmacion = md5($id_prog_curso);
	$msje = "Está seguro de informar esta programación de cursos?\\n"
	      . "Considere que informar su programación de cursos implicará una notificación inmediata "
	      . "al Vicerrector Académico para someter a su revisión la misma";
	$url_si = "$enlbase=$modulo&id_prog_curso=$id_prog_curso&confirmacion=$confirmacion";
	$url_no = "$enlbase=prog_cursos_ver&id_prog_curso=$id_prog_curso";
	echo(confirma_js($msje,$url_si,$url_no));
	exit;
} elseif ($confirmacion == md5($id_prog_curso)) {
	$SQLupdate_pc = "UPDATE prog_cursos SET fecha_mod=now(),informada=true WHERE id=$id_prog_curso";
	if (consulta_dml($SQLupdate_pc) > 0) {
		$cabeceras = "From: SGU" . "\r\n"
		           . "Content-Type: text/plain;charset=utf-8" . "\r\n";
		           
		$cuerpo = "La escuela $pc_escuela ha informado su programación de cursos del periodo $periodo, para su revisión"
		        . "Esta programación está disponible en el SGU, en el módulo 'Prog. Cursos VRA'.";
		         
		$asunto = "Prog. Cursos: $pc_escuela informa";
		
		mail($EMAIL_VRA,$asunto,$cuerpo,$cabeceras);
		
		$emails_escuela = consulta_sql("SELECT email FROM usuarios WHERE id_escuela=$pc_id_escuela and tipo IN (1,2) AND activo;");
		for($x=0;$x<count($emails_escuela);$x++) {
			mail($emails_escuela[$x]['email'],$asunto,$cuerpo,$cabeceras);
		}
		
		echo(msje_js("Se ha informado exitosamente la programación de cursos. Recibirá una notificación por email"));
		echo(js("window.location='$enlbase=prog_cursos_ver&id_prog_curso=$id_prog_curso';")); 
	} else {
		echo(msje_js("ATENCIÓN: Ha ocurrido un error al intentar la notificación.\\n"
		            ."Es posible que algunos cursos no tengan toda la información debida."));
		echo(js("window.location='$enlbase=prog_cursos_ver&id_prog_curso=$id_prog_curso';"));
	}
	exit;
}
		
?>
