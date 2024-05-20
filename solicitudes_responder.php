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

$id_solic   = $_REQUEST['id_solic'];

$SQL_solic = "SELECT st.nombre AS nombre_tipo_solic,st.alias AS tipo_solic,s.estado,st.tipo_docto_oblig,
                     to_char(s.estado_fecha,'DD-tmMon-YYYY HH24:MI') AS estado_fecha,
					 to_char(s.fecha,'DD-tmMon-YYYY HH24:MI') AS fecha_solic,
					 s.email,s.telefono,s.tel_movil,
                     va.rut,va.id AS id_alumno,va.nombre,va.carrera||'-'||a.jornada AS carrera,
					 a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.id_pap
              FROM gestion.solicitudes AS s 
			  LEFT JOIN gestion.solic_tipos AS st ON st.id=s.id_tipo
			  LEFT JOIN vista_alumnos       AS va ON va.id = s.id_alumno 
			  LEFT JOIN alumnos             AS a  ON a.id = s.id_alumno 
			  WHERE s.id=$id_solic";
$solic = consulta_sql($SQL_solic);
if (count($solic) == 0) {
	echo(msje_js("ERROR: No es posible acceder a esta solicitud."));
	echo(js("parent.jQuery.fancybox.close()"));
	exit;
}

$docto_adj = consulta_sql("SELECT id,to_char(fecha,'DD-tmMon-YYYY HH24:MI') AS fecha FROM gestion.solic_doctos_adj WHERE id_solicitud=$id_solic");

$boton_respuesta = $boton_reasignar = "";
$solic_resp = consulta_sql("SELECT 1 FROM gestion.solic_respuestas WHERE NOT visto_bueno AND id_soliciud=$id_solic AND id_usuario={$_SESSION['id_usuario']} AND fecha_reasinacion IS NULL");
if (count($solic_resp) == 0) {
	$boton_respuesta = "<a href='$enlbase_sm=solicitudes_responder&id_solic=$id_solic&id_usuario={$_SESSION['id_usuario']}' id='sgu_fancybox' class='boton'>Responder</a> ";
	$boton_reasignar = "<a href='$enlbase_sm=solicitudes_reasignar&id_solic=$id_solic&id_usuario={$_SESSION['id_usuario']}' id='sgu_fancybox' class='boton'>Reasignar</a> ";
}

$tipo_solic = $solic[0]['tipo_solic'];

?>

<div class="tituloModulo">
  Ver Solicitud
</div>
<div style='margin-top: 5px' class='texto'>
  <input type="button" name="cancelar" value="Volver" onclick="history.back();">
</div>

<?php include_once("Solicitudes/".$tipo_solic."_ver.php"); ?>
