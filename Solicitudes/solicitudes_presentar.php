<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");

if (isset($_SESSION['tipo'])) { 
	include("validar_modulo.php"); 
} else { 
	$datos_modulo  = modulos($modulo); 
	$nombre_modulo = $datos_modulo['nombre'];
}

$id_alumno  = $_REQUEST['id_alumno'];
$id_solic   = $_REQUEST['id_solic'];
$tipo_solic = $_REQUEST['tipo'];

$SQL_solic = "SELECT st.nombre AS nombre_tipo_solic,s.estado,tipo_docto_oblig,st.dias_plazo_respuesta,
                     st.unidades_responsable,st.escuela,
                     to_char(s.estado_fecha,'DD-tmMon-YYYY HH24:MI') AS estado_fecha,
					 to_char(s.fecha,'DD-tmMon-YYYY HH24:MI') AS fecha_solic,s.email,s.telefono,s.tel_movil,
                     va.rut,va.id AS id_alumno,va.nombre,va.carrera||'-'||a.jornada AS carrera,
					 a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.id_pap
              FROM gestion.solicitudes AS s 
			  LEFT JOIN gestion.solic_tipos AS st ON st.id = s.id_tipo
			  LEFT JOIN vista_alumnos       AS va ON va.id = s.id_alumno 
			  LEFT JOIN alumnos             AS a  ON a.id = s.id_alumno 
			  WHERE s.id=$id_solic";
$solic = consulta_sql($SQL_solic);
if (count($solic) == 0) {
	echo(msje_js("ERROR: No es posible acceder a esta solicitud."));
	echo(js("parent.jQuery.fancybox.close()"));
	exit;
}

if ($solic[0]['tipo_docto_oblig'] <> "") {
	$docto_adj = consulta_sql("SELECT 1 FROM gestion.solic_doctos_adj WHERE id_solicitud=$id_solic AND tipo='{$solic[0]['tipo_docto_oblig']}'");
	if (count($docto_adj) == 0) {
		echo(msje_js("ERROR: Esta solicitud obliga adjuntar o subir un documento de tipo «{$solic[0]['tipo_docto_oblig']}».\\n\\n"
	                ."Por favor use el botón Adjuntar Documento."));
		echo(js("history.back()"));
		exit;
	}
}

// Cambia estado de la solicitud
consulta_dml("UPDATE gestion.solicitudes SET estado='Presentada',estado_fecha=now(),fecha_presentada=now() WHERE id=$id_solic AND id_alumno=$id_alumno");

// Asigna el/los responsable(s) de aprobar la solicitud
$SQL_unidades_resp = "INSERT INTO gestion.solic_respuestas (id_solicitud,id_usuario)
                      SELECT $id_solic,u.id 
                      FROM usuarios AS u
					  LEFT JOIN gestion.unidades AS gu ON gu.id=u.id_unidad 
					  WHERE u.activo AND u.responsable_solic 
					    AND gu.alias IN (SELECT unnest('{$solic[0]['unidades_responsable']}'::text[]))";
consulta_dml($SQL_unidades_resp);

// Se notifica al email declarado por el estudiante
notificar_solic_estudiante($id_solic);

// se notifica a la lista de responsables de aprobar la solicitud
notificar_solic_responsables($id_solic);

echo(msje_js("Su solicitud ha sido presentada exitosamente.\\n\\n"
            ."En un plazo no mayor a {$solic[0]['dias_plazo_respuesta']} días hábiles recibirá su respuesta, "
		    ."la que será notificada al correo electrónico que ha declarado en esta solicitud.\\n\\n"
		    ."La institución hace todo lo posible por responder a su solicitud en el menor tiempo posible.\\n\\n"
		    ."Gracias"));
echo(js("history.back()"));
exit;


function notificar_solic_estudiante($id_solic) {}

function notificar_solic_responsables($id_solic) {}

?>