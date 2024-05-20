<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");

if (isset($_SESSION['tipo'])) { 
	include("validar_modulo.php"); 
} else { 
	$datos_modulo = modulos($modulo); 
	$nombre_modulo = $datos_modulo['nombre'];
}

$id_alumno  = $_REQUEST['id_alumno'];
$id_solic   = $_REQUEST['id_solic'];
$tipo_solic = $_REQUEST['tipo'];

if (!empty($id_solic) && !empty($id_alumno) && !empty($_REQUEST['elim_id_docto_adj'])) {
	consulta_dml("DELETE FROM gestion.solic_doctos_adj WHERE id={$_REQUEST['elim_id_docto_adj']}");
}

if (!empty($id_solic) && !empty($id_alumno) && $_REQUEST['anular'] == md5($id_solic)) {
	consulta_dml("UPDATE gestion.solicitudes SET estado='Anulada',estado_fecha=now() WHERE id=$id_solic AND id_alumno=$id_alumno");
}

$SQL_solic_resp = "SELECT sr.id_usuario,u.nombre_usuario,gu.alias,
                          CASE WHEN visto_bueno = 't' THEN 'Aceptada' 
						       WHEN visto_bueno = 'f' THEN 'Rechazada' 
						       WHEN visto_bueno IS NULL AND fecha_reasignacion IS NOT NULL THEN 'Reasignada a '||u2.nombre_usuario 
							   ELSE 'Sin responder' 
						  END AS vobo,
						  to_char(fecha_respuesta,'DD-tmMon-YYYY HH24:MI') AS fecha_respuesta,
						  to_char(fecha_reasignacion,'DD-tmMon-YYYY HH24:MI') AS fecha_reasignacion
				   FROM gestion.solic_respuestas AS sr
				   LEFT JOIN usuarios         AS u  ON u.id=sr.id_usuario
				   LEFT JOIN usuarios         AS u2 ON u2.id=sr.id_usuario_reasig
				   LEFT JOIN gestion.unidades AS gu ON gu.id=u.id_unidad
				   WHERE sr.id_solicitud = s.id";

$SQL_solic_resp = "SELECT char_comma_sum(alias||' ('||nombre_usuario||'): '||vobo||' '||coalesce(fecha_respuesta,fecha_reasignacion,'')) AS resp FROM ($SQL_solic_resp) AS solic_resp";

$SQL_solic = "SELECT st.nombre AS nombre_tipo_solic,s.estado,st.tipo_docto_oblig,
                     to_char(s.estado_fecha,'DD-tmMon-YYYY HH24:MI') AS estado_fecha,
					 to_char(s.fecha,'DD-tmMon-YYYY HH24:MI') AS fecha_solic,
					 s.email,s.telefono,s.tel_movil,s.comentarios,s.resp_obs,
                     va.rut,va.id AS id_alumno,va.nombre,va.carrera||'-'||a.jornada AS carrera,
					 a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.id_pap,
					 ($SQL_solic_resp) AS responsables
              FROM gestion.solicitudes AS s 
			  LEFT JOIN gestion.solic_tipos AS st ON st.id=s.id_tipo
			  LEFT JOIN vista_alumnos       AS va ON va.id = s.id_alumno 
			  LEFT JOIN alumnos             AS a  ON a.id = s.id_alumno 
			  WHERE s.id=$id_solic AND s.id_alumno=$id_alumno AND st.alias='$tipo_solic'";
$solic = consulta_sql($SQL_solic);
if (count($solic) == 0) {
	echo(msje_js("ERROR: No es posible acceder a esta solicitud."));
	echo(js("parent.jQuery.fancybox.close()"));
	exit;
}

$docto_adj = consulta_sql("SELECT id,to_char(fecha,'DD-tmMon-YYYY HH24:MI') AS fecha FROM gestion.solic_doctos_adj WHERE id_solicitud=$id_solic");

?>

<div class="tituloModulo">
  Ver Solicitud
</div>
<div style='margin-top: 5px' class='texto'>
<?php
	if ($solic[0]['estado'] == "En preparación") {

		$enl_presentar  = "$enlbase_sm=solicitudes_presentar&id_solic=$id_solic&tipo=$tipo_solic&id_alumno=$id_alumno";
		$msje_presentar = "¿Está seguro de Presentar esta solicitud?";
		echo("<a href='#' onClick=\"if (confirm('$msje_presentar')) { window.location = '$enl_presentar'; }\" class='boton'>Presentar</a> ");

		$anular      = md5($id_solic);
		$enl_anular  = "$enlbase_sm=solicitudes_ver&anular=$anular&id_solic=$id_solic&tipo=$tipo_solic&id_alumno=$id_alumno";
		$msje_anular = "¿Está seguro de anular esta solicitud?";

		echo("<a href='#' onClick=\"if (confirm('$msje_anular')) { window.location='$enl_anular'; }\" class='boton'>Anular</a> ");
	}

	if (count($docto_adj) == 0 && !empty($solic[0]['tipo_docto_oblig'])) { 
		echo("<a href='$enlbase_sm=solicitudes_docto_adj&id_solic=$id_solic' class='boton'>Adjuntar Documento</a> ");
	}
?>
  <input type="button" name="cancelar" value="Cerrar" onclick="parent.jQuery.fancybox.close();">
</div>

<?php include_once($tipo_solic."_ver.php"); ?>