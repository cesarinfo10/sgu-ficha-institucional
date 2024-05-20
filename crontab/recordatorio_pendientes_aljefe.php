<?php

include("funciones.php");
	
$SQL_tareas = "SELECT actividad,fecha_prog_termino,id_unidad,u.nombre AS unidad,u.alias AS unidad_alias,
                      u2.id AS id_unidad_jefe,u2.nombre AS unidad_jefe 
			   FROM gestion.poas 
			   LEFT JOIN gestion.unidades AS u ON u.id=poas.id_unidad
			   LEFT JOIN gestion.unidades AS u2 ON u2.id=u.dependencia
			   WHERE estado='Pendiente'
			   ORDER BY id_unidad_jefe,id_unidad,fecha_prog_termino";
$tareas = consulta_sql($SQL_tareas);

$CR = "\r\n";

$cabeceras = "From: SGU" . $CR
		   . "Content-Type: text/plain;charset=utf-8" . $CR;

for ($x=0;$x<count($tareas);$x++) {
	$id_unidad_aux = $tareas[$x]['id_unidad'];	
	
	$tareas_pendientes = ""; $total_tareas = 0;	$num_tarea = 1;
	while ($id_unidad_aux == $tareas[$x]['id_unidad']) {	
		$fecha_prog_termino = strftime("%A %e-%b-%Y",strtotime($tareas[$x]['fecha_prog_termino']));
		
		$tareas_pendientes .= $num_tarea.".-"
		                   .  "\t" . str_replace("\n","\n\t",$tareas[$x]['actividad']) . $CR
						   .  "\t" . "Fecha de Término: $fecha_prog_termino" . $CR.$CR;
		$total_tareas++;
		$x++;
		$num_tarea++;
	}
	
	$x--;
	
	$asunto = "🔴 POA [{$tareas[$x]['unidad_alias']}]: Tiene $total_tareas tarea(s) pendiente(s)";		

	$cuerpo = "Estimad@ Superior de {$tareas[$x]['unidad']}" . $CR.$CR
			. "Actualmente esta unidad tiene la(s) siguiente(s) tarea(s) pendiente(s):" . $CR.$CR
			. $tareas_pendientes
			. "Por favor, gestione el término de esta(s) a la brevedad." . $CR.$CR
			. "Gracias" . $CR
			. "Atte.," . $CR.$CR
			. "Dirección de Aseguramiento de la Calidad";

	$usuarios = consulta_sql("SELECT email FROM usuarios WHERE activo AND id_unidad={$tareas[$x]['id_unidad_jefe']}");
	
	for ($j=0;$j<count($usuarios);$j++) { mail($usuarios[$j]['email'],$asunto,$cuerpo,$cabeceras); }
}

?>
