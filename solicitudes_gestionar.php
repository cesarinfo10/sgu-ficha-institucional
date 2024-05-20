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

$solic_resp = consulta_sql("SELECT id FROM gestion.solic_respuestas WHERE id_usuario={$_SESSION['id_usuario']} AND id_solicitud=$id_solic");
$id_solic_resp = $solic_resp[0]['id'];

$visto_bueno          = $_REQUEST['visto_bueno'];
$id_nuevo_responsable = $_REQUEST['id_nuevo_responsable'];
$observaciones        = $_REQUEST['observaciones'];
$obs_interna          = $_REQUEST['obs_interna'];

if ($_REQUEST['guardar'] == "Responder Solicitud") {
	$SQL_upd = "UPDATE gestion.solicitudes SET resp_obs=coalesce(resp_obs||'\n\n','')||'$observaciones' WHERE id=$id_solic; 
	            UPDATE gestion.solic_respuestas 
			    SET visto_bueno='$visto_bueno',fecha_respuesta=now(),obs_interna=coalesce(obs_interna||'\n\n','')||'$observaciones' 
				WHERE id=$id_solic_resp";
	consulta_dml($SQL_upd);
	notificar_respuesta($id_solic_resp);
	if ($visto_bueno == "f") {
		consulta_dml("UPDATE gestion.solicitudes SET estado='Rechazada',estado_fecha=now() WHERE id=$id_solic");
	} elseif ($visto_bueno == "t") {
		$SQL_resp_solic_aprob = "SELECT count(id) FROM gestion.solic_respuestas WHERE id_solicitud=$id_solic AND visto_bueno";
		$SQL_resp_solic_todas = "SELECT count(id) FROM gestion.solic_respuestas WHERE id_solicitud=$id_solic AND fecha_reasignacion IS NULL";
		$SQL_resp_solic = "UPDATE gestion.solicitudes SET estado='Aceptada',estado_fecha = now() WHERE id=$id_solic AND ($SQL_resp_solic_todas)=($SQL_resp_solic_aprob)";
		if (consulta_dml($SQL_resp_solic) == 1) {
			$SQL_solic = "SELECT id_tipo FROM gestion.solicitudes WHERE id=$id_solic";
			$solic_tipo = consulta_sql("SELECT alias FROM gestion.solic_tipos WHERE id=($SQL_solic)");
			$script_solic_aprobada = $solic_tipo[0]['alias']."_aprobada.php";
			include($script_solic_aprobada);
		}
	}
	echo(msje_js("Se ha respondido la solicitud."));
	echo(js("location.href='$enlbase=gestion_solicitudes';"));
}

if ($_REQUEST['guardar'] == "Reasignar Solicitud") {
	$SQL_updins = "UPDATE gestion.solic_respuestas 
			       SET fecha_reasignacion=now(),obs_interna=coalesce(obs_interna||'\n\n','')||'$obs_interna',id_usuario_reasig=$id_nuevo_responsable
				   WHERE id=$id_solic_resp;
				   INSERT INTO gestion.solic_respuestas (id_solicitud,id_usuario) VALUES ($id_solic,$id_nuevo_responsable)";
	consulta_dml($SQL_updins);

	$solic_resp = consulta_sql("SELECT id FROM gestion.solic_respuestas WHERE id_usuario=$id_nuevo_responsable AND id_solicitud=$id_solic");
	$id_solic_resp = $solic_resp[0]['id'];
	notificar_reasignacion($id_solic_resp);
	echo(msje_js("Se ha reasignado la solicitud."));
	echo(js("location.href='$enlbase=gestion_solicitudes';"));
}

/*
if (!empty($id_solic) && !empty($id_alumno) && $_REQUEST['anular'] == md5($id_solic)) {
	consulta_dml("UPDATE gestion.solicitudes SET estado='Anulada',estado_fecha=now() WHERE id=$id_solic AND id_alumno=$id_alumno");
}
*/

$SQL_solic_resp = "SELECT sr.id_usuario,u.nombre||' '||u.apellido AS nombre_usuario,gu.alias,
                          CASE WHEN visto_bueno = 't' THEN 'Aceptada' 
						       WHEN visto_bueno = 'f' THEN 'Rechazada' 
						       WHEN visto_bueno IS NULL AND fecha_reasignacion IS NOT NULL THEN 'Reasignada a '||gu2.alias||' ('||u2.nombre||' '||u2.apellido||')'
							   ELSE 'Sin responder' 
						  END AS vobo,
						  to_char(fecha_respuesta,'DD-tmMon-YYYY HH24:MI') AS fecha_respuesta,
						  to_char(fecha_reasignacion,'DD-tmMon-YYYY HH24:MI') AS fecha_reasignacion
				   FROM gestion.solic_respuestas AS sr
				   LEFT JOIN usuarios         AS u  ON u.id=sr.id_usuario
				   LEFT JOIN usuarios         AS u2 ON u2.id=sr.id_usuario_reasig
				   LEFT JOIN gestion.unidades AS gu ON gu.id=u.id_unidad
				   LEFT JOIN gestion.unidades AS gu2 ON gu2.id=u2.id_unidad
				   WHERE sr.id_solicitud = s.id";

$SQL_solic_resp = "SELECT char_comma_sum(alias||' ('||nombre_usuario||'): '||vobo||' '||coalesce(fecha_respuesta,fecha_reasignacion,'')) AS resp FROM ($SQL_solic_resp) AS solic_resp";

$SQL_solic = "SELECT st.nombre AS nombre_tipo_solic,st.alias AS tipo_solic,s.estado,st.tipo_docto_oblig,
                     to_char(s.estado_fecha,'DD-tmMon-YYYY HH24:MI') AS estado_fecha,s.comentarios,s.resp_obs,
					 to_char(s.fecha,'DD-tmMon-YYYY HH24:MI') AS fecha_solic,
					 s.email,s.telefono,s.tel_movil,
                     va.rut,va.id AS id_alumno,va.nombre,va.carrera||'-'||a.jornada AS carrera,
					 a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.id_pap,
					 ($SQL_solic_resp) AS responsables
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
$solic_resp = consulta_sql("SELECT 1 FROM gestion.solic_respuestas WHERE (NOT coalesce(visto_bueno,false)) AND id_solicitud=$id_solic AND id_usuario={$_SESSION['id_usuario']} AND fecha_reasignacion IS NULL");
if (count($solic_resp) == 1) {
	//$boton_respuesta = "<a href='$enlbase_sm=solicitudes_responder&id_solic=$id_solic&id_usuario={$_SESSION['id_usuario']}' id='sgu_fancybox_small' class='boton'>Responder</a> ";
	$boton_respuesta = "<input id='responder' type='button' onClick='acciones_responder();' value='Responder'> ";
	//$boton_reasignar = "<a href='$enlbase_sm=solicitudes_reasignar&id_solic=$id_solic&id_usuario={$_SESSION['id_usuario']}' id='sgu_fancybox_small' class='boton'>Reasignar</a> ";
	$boton_reasignar = "<input id='reasignar' type='button' onClick='acciones_reasignar();' value='Reasignar'>  ";
}

$tipo_solic = $solic[0]['tipo_solic'];

$SQL_solic_resp = "SELECT id_usuario FROM gestion.solic_respuestas WHERE id_solicitud=$id_solic";

$SQL_respomsables = "SELECT u.id,u.nombre||' '||u.apellido||' ('||gu.alias||')' AS nombre 
                     FROM usuarios AS u 
			 	 	 LEFT JOIN gestion.unidades AS gu ON gu.id=u.id_unidad  
				     WHERE activo AND u.id_unidad IS NOT NULL AND responsable_solic AND u.id NOT IN ($SQL_solic_resp)
					 ORDER BY u.nombre,u.apellido";
$RESPONSABLES = consulta_sql($SQL_respomsables);

?>

<div class="tituloModulo">
  Ver Solicitud
</div>
<div style='margin-top: 5px' class='texto'>
<?php

if ($solic[0]['estado'] == "Presentada") {

	echo($boton_respuesta);
	echo($boton_reasignar);

}

if ($solic[0]['estado'] == "En preparación") {

	$anular      = md5($id_solic);
	$enl_anular  = "$enlbase_sm=$modulo&anular=$anular&id_solic=$id_solic&tipo=$tipo_solic&id_alumno=$id_alumno";
	$msje_anular = "¿Está seguro de anular esta solicitud?";

	echo("<a href='#' onClick=\"if (confirm('$msje_anular')) { window.location='$enl_anular'; }\" class='boton'>Anular</a> ");
}
 
?>
  <input type="button" name="cancelar" value="Volver" onclick="history.back();">
</div>

<?php 

$dir_solic = "Solicitudes";
include_once("$dir_solic/".$tipo_solic."_ver.php"); ?>

<form name='formulario' action='<?php echo($_SERVER['SCRIPT_NAME']); ?>' method="post" id="form1">
<input type='hidden' name='modulo' value='<?php echo($modulo); ?>'>
<input type='hidden' name='id_solic' value='<?php echo($id_solic); ?>'>

<div id="solicitudes_responder" style="display: none">
  <table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
    <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Responder Solicitud</td></tr>

    <tr>
	  <td class='celdaNombreAttr'>V° B°:</td>
	  <td class='celdaValorAttr' colspan="3">
	    <span class="Aceptada" style='padding: 4px; margin: 2px; border: outset 2px'>&nbsp;<input type="radio" name="visto_bueno" value='t' id="aprueba"> <label style='font-size: 11pt' for="aprueba" >Aprobar</label>&nbsp;</span>&nbsp;&nbsp;
	    <span class="Rechazada" style='padding: 4px; margin: 2px; border: outset 2px'>&nbsp;<input type="radio" name="visto_bueno" onClick='formulario.observaciones.required=true; formulario.observaciones.focus();' value='f' id="rechaza"> <label style='font-size: 11pt' for="rechaza" required>Rechazar</label>&nbsp;</span><br><br>
	  </td>
    </tr>
    <tr>
	  <td class='celdaNombreAttr'>Observaciones:<br><small>Lo escrito aquí<br>es visible para<br>el estudiante</small></td>
	  <td class='celdaValorAttr' colspan="3">
	    <textarea name='observaciones' cols="70" rows="5" class='general'></textarea><br>
		<input type="submit" name="guardar" onClick='return evaluar_respuesta();' value="Responder Solicitud">
	  </td>
    </tr>
  </table>
</div>

<div id="solicitudes_reasignar" style="display: none">
  <table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
    <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Reasignar Solicitud</td></tr>

    <tr>
	  <td class='celdaNombreAttr'>Nuevo Responsable:</td>
	  <td class='celdaValorAttr' colspan="3">
	    <select name="id_nuevo_responsable" class='filtro' style='max-width: none'>
		  <option value="">-- Seleccione --</option>
		  <?php echo(select($RESPONSABLES,$id_nuevo_responsable)); ?>
	    </select>
	  </td>
    </tr>
    <tr>
	  <td class='celdaNombreAttr'>Obs. Interna:<br><small>Lo escrito aquí<br>NO es visible para<br>el estudiante</small></td>
	  <td class='celdaValorAttr' colspan="3">
	    <textarea name='obs_interna' cols="70" rows="5" class='general'></textarea><br>
		<input type="submit" name="guardar" value="Reasignar Solicitud">
	  </td>
    </tr>
  </table>
</div>
</form>

<?php

function notificar_respuesta($id_solic_resp) {}

function notificar_reasignacion($id_solic_resp) {}


?>

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 1200,
		'height'			: 600,
		'afterClose'		: function () {  },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_medium").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 900,
		'height'			: 750,
		'maxHeight'			: 750,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 850,
		'height'			: 400,
		'maxHeight'			: 400,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

function acciones_responder() {
	document.getElementById('solicitudes_responder').style.display=''; 
	document.getElementById('reasignar').style.display='none';
	formulario.visto_bueno.required=true;
}

function acciones_reasignar() {
	document.getElementById('solicitudes_reasignar').style.display='';
	document.getElementById('responder').style.display='none';
	formulario.id_nuevo_responsable.required=true;
	formulario.obs_interna.required=true;
}

function evaluar_respuesta() {
	var msje;
	if (formulario.visto_bueno.value == 'f' && formulario.observaciones.value == "") {
		alert("ERROR: Debe ingresar una observación explicativa del motivo de Rechazo.");
		return false;
	}
	if (formulario.visto_bueno.value == 'f') {
		msje = "Su respuesta de Rechazo, implica rechazar esta solicitud. \n\n"
		     + "Esto significa que los demás responsables que resten por responder, "
			 + "no podrán intervenir. \n\n"
			 + "¿Está seguro de continuar?";

		return confirm(msje);
	}

	if (formulario.visto_bueno.value == 't') { return true; }
}

</script>

<!-- Fin: <?php echo($modulo); ?> -->