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

if (!empty($_REQUEST['comentarios'])) { 
	$fecha_comentario = strftime("%A %e-%b-%Y a las %R");
	$_REQUEST['comentarios'] = "El $fecha_comentario, {$_SESSION['usuario']} escribió:\n\n"
							 . $_REQUEST['comentarios']."\n"
							 . "<hr>";
	$SQL_comentarios = ",comentarios='{$_REQUEST['comentarios']}'";
}

if ($_REQUEST['darok'] == "Otorgar OK" && md5($id_tarea) == $_REQUEST['token']) {	
	$fecha_fin_real = $_REQUEST['fecha_fin_real'];
	$SQLupd = "UPDATE gestion.poas SET estado='OK',fecha_fin_real='$fecha_fin_real'::date $SQL_comentarios WHERE id=$id_tarea";
	if (consulta_dml($SQLupd) == 1) {
		email_darok_tarea($id_tarea,$_REQUEST['comentarios']);
		echo(msje_js("Se ha otorgado el OK a la tarea exitósamente"));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}
}

if ($_REQUEST['revocar'] == "Revocar" && md5($id_tarea) == $_REQUEST['token']) {	
	$SQLupd = "UPDATE gestion.poas 
	           SET estado=CASE WHEN fecha_prog_termino<now()::date THEN 'Pendiente' ELSE 'Nueva' END::poa_estados,
	               evidencia=null,
	               evidencia_ext=null,
	               evidencia_mime=null,
	               fecha_fin_real=null  $SQL_comentarios 	
	           WHERE id=$id_tarea";
	if (consulta_dml($SQLupd) == 1) {
		email_revocar_termino_tarea($id_tarea,$_REQUEST['comentarios']);
		echo(msje_js("Se ha Revocado el término a la tarea"));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}
}

$SQL_tarea = "SELECT tipo_act,gu.nombre AS unidad,actividad,prioridad,fecha_prog_termino AS fec_prog_termino,
                     to_char(fecha_prog_termino,'DD-tmMon-YYYY') AS fecha_prog_termino,fecha_prog_termino_hist,poas.fecha_prog_termino AS fecha_prog_ter,
                     fecha_fin_real as fec_fin_real,
                     to_char(fecha_fin_real,'DD-tmMon-YYYY') AS fecha_fin_real,
                     coalesce(p.nombre,'** Ninguno **') AS proyecto,estado,poas.comentarios 
              FROM gestion.poas 
              LEFT JOIN gestion.unidades AS gu ON gu.id=poas.id_unidad
              LEFT JOIN gestion.proyectos AS p ON p.id=poas.id_proyecto
              WHERE poas.id=$id_tarea";
//echo($SQL_tarea);
$tarea = consulta_sql($SQL_tarea);

$fec_prog_ter_hist = "";
if ($tarea[0]['fecha_prog_termino_hist'] <> "") {
	$fecha_prog_termino_hist = explode(",",str_replace(array("{","}"),"",$tarea[0]['fecha_prog_termino_hist']));
	$fec_prog_ter_hist = "<hr><small><b>Fechas anteriores:</b><div align='right'>";
	for($x=0;$x<count($fecha_prog_termino_hist);$x++) {
		$fec_prog_ter_hist .= strftime("%d-%b-%Y",strtotime($fecha_prog_termino_hist[$x]))."<br>";
	}
	$fec_prog_ter_hist .= "</div></small>";
}

$boton_ver_evidencia = "";
if ($tarea[0]['estado'] == "Terminada") {
	$boton_ver_evidencia = " <a href='ver_evidencia_poa.php?id_tarea=$id_tarea' target='_blank' class='enlaces'>Ver evidencia</a>";
}

$admin_poa = consulta_sql("SELECT poas_admin FROM usuarios WHERE id={$_SESSION['id_usuario']}");
$admin_poa = ($admin_poa[0]['poas_admin'] == "t") ? true : false;

$cond_unidades = "";
if ($_SESSION['id_unidad'] <> "" && !$admin_poa) { $cond_unidades = "WHERE id = {$_SESSION['id_unidad']}"; $_REQUEST['id_unidad'] = $_SESSION['id_unidad']; }
$UNIDADES = consulta_sql("SELECT id,nombre||' ('||alias||')' AS nombre FROM gestion.unidades $cond_unidades ORDER BY nombre");

$ESTADOS = consulta_sql("SELECT id,nombre FROM vista_poa_estados");

$TIPOS_TAREA = consulta_sql("SELECT id,nombre FROM vista_poa_tipo_act");

$PROYECTOS = consulta_sql("SELECT id,nombre FROM gestion.proyectos ORDER BY nombre");

?>
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>	
<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME'])?>" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_tarea" value="<?php echo($_REQUEST['id_tarea']); ?>">
<input type="hidden" name="id_usuario_reg" value="<?php echo($_REQUEST['id_usuario_reg']); ?>">
<input type="hidden" name="token" value="<?php echo(md5($id_tarea)); ?>">
<div style="margin-top: 5px">
  <input type="submit" name="darok" value="Otorgar OK" onClick="if (confirm('¿Está seguro de otorgar el OK a esta tarea?')) { submitform(); } else { return false; }">
  <input type="submit" name="revocar" value="Revocar" onClick="if (confirm('ATENCIÓN: \n\n ¿Está seguro de Revocar el Término a esta tarea?')) { submitform(); } else { return false; }">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr><td class="celdaNombreAttr" colspan="4" style="text-align: center">Antecedentes de la Tarea</td></tr>
  <tr>
    <td class="celdaNombreAttr"><u>Tipo de Tarea:</u></td>
    <td class="celdaValorAttr"><?php echo($tarea[0]['tipo_act']); ?></td>
    <td class="celdaNombreAttr"><u>Prioridad:</u></td>
    <td class="celdaValorAttr"><span class="<?php echo($tarea[0]['prioridad']); ?>"><?php echo($tarea[0]['prioridad']); ?></span></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr"><u>Unidad:</u></td>
    <td class="celdaValorAttr" colspan="3"><?php echo($tarea[0]['unidad']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr"><u>Actividad:</u></td>
    <td class="celdaValorAttr" colspan="3"><?php echo(nl2br($tarea[0]['actividad'])); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Término Programado:</td>
    <td class="celdaValorAttr"><?php echo($tarea[0]['fecha_prog_termino'].$fec_prog_ter_hist); ?></td>
    <td class="celdaNombreAttr">Término Real:<br>Nuevo Término Real:</td>
    <td class="celdaValorAttr"><?php echo($tarea[0]['fecha_fin_real']); ?><br><input type="date" class="boton" name="fecha_fin_real" value="<?php echo($tarea[0]['fec_prog_termino']); ?>"></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Proyecto:</td>
    <td class="celdaValorAttr" colspan='3'><?php echo($tarea[0]['proyecto']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr"><u>Estado:</u></td>
    <td class="celdaValorAttr" colspan='3'><span class="<?php echo($tarea[0]['estado']); ?>"><?php echo($tarea[0]['estado']); ?></span> <?php echo($boton_ver_evidencia); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Observaciones:</td>
    <td class="celdaValorAttr" colspan="3">
      <?php echo(nl2br($tarea[0]['comentarios'])); ?>
      <div class="celdaNombreAttr" style='text-align: center'>Añadir Observaciones</div>
      <textarea name="comentarios" value="" cols="40" rows="6" class="general"></textarea>
    </td>
  </tr>
</table>
</form>
<?php

function email_revocar_termino_tarea($id_tarea,$observacion) {
	
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
	
	if ($observacion <> "") {
		$observacion = "Se agregó la siguiente observación:" . $CR.$CR
	                 . $observacion . $CR.$CR;
	}

	$cabeceras = "From: SGU" . $CR
	           . "Content-Type: text/plain;charset=utf-8" . $CR;
	           
	$asunto = "POA: ⛔ Tarea con Término Revocado";
	
	$cuerpo = "Estimad@ Responsable de $unidad" . $CR.$CR
	        . "Se revocó el término de la siguiente tarea de su POA:" . $CR.$CR
	        . $actividad . $CR
	        . "Fecha de Término: $fecha_prog_termino" . $CR.$CR
	        . $observacion
	        . "Para completar nuevamente esta tarea, debe usar el módulo POA en el "
	        . "SGU y usar el botón «Terminar». El SGU le pedirá que "
	        . "escoja y suba el archivo de evidencia respectivo." . $CR.$CR
	        . "Luego, la unidad de Aseguramiento de la Calidad, "
	        . "evaluará el contenido del archivo y si corresponde otorgará "
	        . "el OK a la tarea." . $CR.$CR
	        . "Gracias" . $CR
	        . "Atte.," . $CR.$CR
	        . "Dirección de Aseguramiento de la Calidad";
	        
	for ($x=0;$x<count($usuarios);$x++) { mail($usuarios[$x]['email'],$asunto,$cuerpo,$cabeceras); }
}

function email_darok_tarea($id_tarea,$observacion) {
	
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

	if ($observacion <> "") {
		$observacion = "Se agregó la siguiente observación:" . $CR.$CR
	                 . $observacion . $CR.$CR;
	}

	$cabeceras = "From: SGU" . $CR
	           . "Content-Type: text/plain;charset=utf-8" . $CR;
	           
	$asunto = "POA: ✅ Tarea OK";
	
	$cuerpo = "Estimad@ Responsable de $unidad" . $CR.$CR
	        . "Se ha otorgado el OK a la siguiente tarea de su POA:" . $CR.$CR
	        . $actividad . $CR
	        . "Fecha de Término: $fecha_prog_termino" . $CR.$CR
	        . $observacion
	        . "Gracias!" . $CR
	        . "Atte.," . $CR.$CR
	        . "Dirección de Aseguramiento de la Calidad";
	        
	for ($x=0;$x<count($usuarios);$x++) { mail($usuarios[$x]['email'],$asunto,$cuerpo,$cabeceras); }
}
?>
