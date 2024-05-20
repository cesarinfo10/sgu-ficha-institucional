<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_tarea = $_REQUEST['id_tarea'];

if (!is_numeric($id_tarea)) {
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}


if ($_REQUEST['eliminar'] == "NO") {
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}
	
if ($_REQUEST['eliminar'] == "Si") {
	$token = md5($id_tarea);
	if ($_REQUEST['token'] == $token) {
		$SQLupd = "UPDATE gestion.poas SET estado='Eliminada' WHERE id=$id_tarea";
		if (consulta_dml($SQLupd) == 1) {
			email_eliminar_tarea($id_tarea);
			echo(msje_js("Se ha marcado como Eliminada esta tarea exitósamente"));
			echo(js("parent.jQuery.fancybox.close();"));
			exit;
		}
	}
}

$SQL_tarea = "SELECT tipo_act,gu.nombre AS unidad,actividad,prioridad,
                     to_char(fecha_prog_termino,'DD-tmMon-YYYY') AS fecha_prog_termino,fecha_prog_termino_hist,poas.fecha_prog_termino AS fecha_prog_ter,
                     to_char(fecha_fin_real,'DD-tmMon-YYYY') AS fecha_fin_real,
                     coalesce(p.nombre,'** Ninguno **') AS proyecto,estado,poas.comentarios 
              FROM gestion.poas 
              LEFT JOIN gestion.unidades AS gu ON gu.id=poas.id_unidad
              LEFT JOIN gestion.proyectos AS p ON p.id=poas.id_proyecto
              WHERE poas.id=$id_tarea";
//echo($SQL_tarea);
$tarea = consulta_sql($SQL_tarea);

if (count($tarea) == 1) {
	
	$msje_confirm = "Está intentando marcar como Eliminada la  tarea: "
	              . str_replace("\n"," ",trim($tarea[0]['actividad']))."\\n\\n"
	              . "¿Desea continuar?";
	$token  = md5($id_tarea);
	$url_si = "$enlbase_sm=tarea_poa_eliminar&id_tarea=$id_tarea&eliminar=Si&token=$token";
	$url_no = "$enlbase_sm=tarea_poa_eliminar&id_tarea=$id_tarea&eliminar=NO";
	echo(confirma_js($msje_confirm,$url_si,$url_no));
		
}

function email_eliminar_tarea($id_tarea) {
	
	$SQL_tarea = "SELECT actividad,u.nombre AS unidad,id_unidad,fecha_prog_termino 
	              FROM gestion.poas 
	              LEFT JOIN gestion.unidades AS u ON u.id=poas.id_unidad 
	              WHERE poas.id=$id_tarea";
	$tarea = consulta_sql($SQL_tarea);
	
	$fecha_prog_termino = strftime("%A %e-%b-%Y",strtotime($tarea[0]['fecha_prog_termino']));
	$unidad             = $tarea[0]['unidad'];
	$actividad          = $tarea[0]['actividad'];
	
	$usuarios = consulta_sql("SELECT email FROM usuarios WHERE id_unidad={$tarea[0]['id_unidad']}");
	
	$CR = "\r\n";
			
	$cabeceras = "From: SGU" . $CR
	           . "Content-Type: text/plain;charset=utf-8" . $CR;
	           
	$asunto = "POA: Tarea Eliminada";
	
	$cuerpo = "Estimad@ Responsable de $unidad" . $CR.$CR
	        . "Se ha marcado como Eliminada la siguiente tarea de su POA:" . $CR.$CR
	        . $actividad . $CR
	        . "Fecha de Término: $fecha_prog_termino" . $CR.$CR
	        . "Atte.," . $CR.$CR
	        . "Dirección de Aseguramiento de la Calidad";
	        
	for ($x=0;$x<count($usuarios);$x++) { mail($usuarios[$x]['email'],$asunto,$cuerpo,$cabeceras); }
}

?>
