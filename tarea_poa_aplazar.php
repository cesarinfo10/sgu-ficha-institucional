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

if ($_REQUEST['guardar'] == "Guardar") {
	if (strtotime($_REQUEST['fecha_prog_termino']) >= time()) { $estado = "Aplazada"; } else { $estado="Pendiente"; }
	
	$SQLupd = "UPDATE gestion.poas 
	           SET estado='$estado',
	               fecha_prog_termino_hist=array_prepend(fecha_prog_termino,fecha_prog_termino_hist),
	               fecha_prog_termino='{$_REQUEST['fecha_prog_termino']}'
	           WHERE id=$id_tarea";
	if (consulta_dml($SQLupd) == 1) {
		email_aplazar_tarea($id_tarea);
		echo(msje_js("Se ha guardado los cambios de la tarea exitósamente"));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
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

$fec_prog_ter_hist = "";
if ($tarea[0]['fecha_prog_termino_hist'] <> "") {
	$fecha_prog_termino_hist = explode(",",str_replace(array("{","}"),"",$tarea[0]['fecha_prog_termino_hist']));
	$fec_prog_ter_hist = "<hr><small><b>Fechas anteriores:</b><div align='right'>";
	for($x=0;$x<count($fecha_prog_termino_hist);$x++) {
		$fec_prog_ter_hist .= strftime("%d-%b-%Y",strtotime($fecha_prog_termino_hist[$x]))."<br>";
	}
	$fec_prog_ter_hist .= "</div></small>";
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
<div style="margin-top: 5px">
  <input type="submit" name="guardar" value="Guardar">
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
    <td class="celdaNombreAttr">Nueva fecha de Término:</td>
    <td class="celdaValorAttr"><input type="date" name="fecha_prog_termino" value="<?php echo($tarea[0]['fecha_prog_ter']); ?>" class="boton" required></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Proyecto:</td>
    <td class="celdaValorAttr" colspan='3'><?php echo($tarea[0]['proyecto']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr"><u>Estado:</u></td>
    <td class="celdaValorAttr" colspan='3'><span class="<?php echo($tarea[0]['estado']); ?>"><?php echo($tarea[0]['estado']); ?></span></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Observaciones:</td>
    <td class="celdaValorAttr" colspan="3"><?php echo(nl2br($tarea[0]['comentarios'])); ?></td>
  </tr>
</table>
</form>

<?php

function email_aplazar_tarea($id_tarea) {
	
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
	           
	$asunto = "POA: Tarea Aplazada";
	
	$cuerpo = "Estimad@ Responsable de $unidad" . $CR.$CR
	        . "Se aplazó la siguiente tarea de su POA:" . $CR.$CR
	        . $actividad . $CR
	        . "Nueva fecha de término: $fecha_prog_termino" . $CR.$CR
	        . "Para completar esta tarea, debe usar el módulo POA en el "
	        . "SGU y usar el botón «Terminar». El SGU le pedirá que "
	        . "escoja y suba el archivo de evidencia respectivo." . $CR.$CR
	        . "Luego, la unidad de Aseguramiento de la Calidad, "
	        . "evaluará el contenido del archivo y si corresponde otorgará "
	        . "el OK a la tarea." . $CR.$CR
	        . "Gracias!" . $CR
	        . "Atte.," . $CR.$CR
	        . "Dirección de Aseguramiento de la Calidad";
	        
	for ($x=0;$x<count($usuarios);$x++) { mail($usuarios[$x]['email'],$asunto,$cuerpo,$cabeceras); }
}

?>
