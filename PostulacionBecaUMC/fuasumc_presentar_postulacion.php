<?php
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

if (isset($_SESSION['tipo'])) { 
	include("validar_modulo.php"); 
} else { 
	$datos_modulo = modulos($modulo); 
	$nombre_modulo = $datos_modulo['nombre'];
}

$id_fuas = $_REQUEST['id_fuas'];

$problemas = false;
$msje_prob = "ERROR: No es posible presentar esta postulación "
           . "debido a que no están subidos los documentos de "
           . "respaldo de ingresos de:\\n\\n";

$SQL_fuas = "SELECT fuas.id,fdi.id AS id_docto
             FROM dae.fuas 
             LEFT JOIN dae.fuas_doctos_ing AS fdi ON fdi.id_fuas=fuas.id 
             WHERE fuas.id=$id_fuas";
$fuas = consulta_sql($SQL_fuas);
if ($fuas[0]['id_docto'] == "") { 
	$problemas = true;
	$msje_prob .= "- El o la Estudiante o Postulante a Beca UMC.\\n";
}

$SQL_fuas_gf = "SELECT gf.id,gf.nombres,gf.apellidos,fdi.id AS id_docto
                FROM dae.fuas_grupo_familiar AS gf
                LEFT JOIN dae.fuas_doctos_ing AS fdi ON fdi.id_fuas_grupo_familiar=gf.id
                WHERE gf.id_fuas=$id_fuas AND date_part('year',age(gf.fecha_nacimiento))>=18";
$fuas_gf = consulta_sql($SQL_fuas_gf);
for ($x=0;$x<count($fuas_gf);$x++) {
	if ($fuas_gf[$x]['id_docto'] == "") {
		$problemas = true;
		$msje_prob .= "- Integrante del grupo familiar: «{$fuas_gf[$x]['apellidos']} {$fuas_gf[$x]['nombres']}.\\n";
	}
}

$msje_prob .= "\\n"
           .  "Para completar esta información, debe incoporar los documentos respectivos, "
           .  "usando el botón «Subir Doctos Ingresos».";

if ($problemas) {
	echo(msje_js($msje_prob));	
} else {
	$SQLupd = "UPDATE dae.fuas SET fecha_presentacion=now(),estado='Presentado' WHERE id=$id_fuas";
	if (consulta_dml($SQLupd) == 1) {
		email_dae($id_fuas);
		echo(msje_js("Se ha presentado correctamente su postulación.\\n\\n"
		            ."Próximamente se le informará, al email que ha registrado, "
		            ."el resultado y el porcentaje de beca asignado."));
	} else {
		echo(msje_js("ERROR: No ha sido posible presentar su postulación.\\n\\n"
		            ."Por favor comunicate con la Dirección de Asuntos Estudiantiles."));
	}
			            
}
echo(js("parent.jQuery.fancybox.close()"));
exit;


function email_dae($id_fuas) {
	
	$SQL_fuas = "SELECT fuas.email,a.apellidos||' '||a.nombres AS nombre_al FROM dae.fuas LEFT JOIN alumnos AS a ON a.id=fuas.id_alumno WHERE id=$id_fuas";
	$fuas = consulta_sql($SQL_fuas);
	
	$CR = "\r\n";
	
	$cabeceras = "From: SGU" . $CR
	           . "Content-Type: text/plain;charset=utf-8" . $CR;
	           
	$asunto = "Postulación a Beca UMC";
	$cuerpo = "Estimad@ Responsable de Dirección de Asuntos Estudiantiles," . $CR.$CR
	        . "El o la estudiante $nombre_al ha presentado una postulación a Beca UMC, "
	        . "la que deberá validar en las siguientes 48 horas." . $CR.$CR;
			
	$cuerpo .= "Gracias" . $CR
			.  "Atte.," . $CR.$CR
			.  "Estudiantes UMC";
	        
	mail('nraggio@umcervantes.cl',$asunto,$cuerpo,$cabeceras);
}

?>
