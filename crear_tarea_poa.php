<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

if (empty($_REQUEST['prioridad'])) { $_REQUEST['prioridad'] = "Regular"; }

$admin_poa = consulta_sql("SELECT poas_admin FROM usuarios WHERE id={$_SESSION['id_usuario']}");
$admin_poa = ($admin_poa[0]['poas_admin'] == "t") ? true : false;

if ($_REQUEST['guardar'] == "Guardar") {
	$aCampos = array("id_usuario_reg","tipo_act","prioridad","estado","id_unidad","actividad","id_proyecto","fecha_prog_termino","comentarios");
	
	if (!empty($_REQUEST['comentarios'])) { 
		$fecha_comentario = strftime("%A %e-%b-%Y a las %R");
		$_REQUEST['comentarios'] = "El $fecha_comentario, {$_SESSION['usuario']} escribió:\n\n"
		                         . $_REQUEST['comentarios']."\n"
		                         . "<hr>";
	}
	if (strtotime($_REQUEST['fecha_prog_termino']) < time()) { $_REQUEST['estado'] = "Pendiente"; }

	$unidades = array($_REQUEST['id_unidad']);

	if ($_REQUEST['id_unidad'] == "-1") {
		$unidades = array_column(consulta_sql("SELECT id FROM gestion.unidades WHERE activa"),"id");
	}
	
	for ($x=0;$x<count($unidades);$x++) {
		$_REQUEST["id_unidad"] = $unidades[$x];
		
		$SQLinsert = "INSERT INTO gestion.poas " . arr2sqlinsert($_REQUEST,$aCampos);
		if (consulta_dml($SQLinsert) == 1) {
			email_nueva_tarea($_REQUEST['id_unidad'],$_REQUEST['actividad'],$_REQUEST['fecha_prog_termino']);
		}	
	}
	echo(msje_js("Se ha creado una nueva tarea con los datos ingresados exitósamente"));
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$cond_unidades = "WHERE u.activa ";
if ($_SESSION['id_unidad'] <> "" && !$admin_poa) { $cond_unidades .= "AND u.id = {$_SESSION['id_unidad']}"; $_REQUEST['id_unidad'] = $_SESSION['id_unidad']; }
$UNIDADES = consulta_sql("SELECT u.id,u.nombre||' ('||u.alias||')' AS nombre,u2.nombre AS grupo FROM gestion.unidades AS u LEFT JOIN gestion.unidades AS u2 ON u2.id=u.dependencia $cond_unidades ORDER BY coalesce(u.dependencia,0),u.nombre");
if ($admin_poa) {
	$todas_unidades = array('id' => "-1",'nombre' => "Todas las Unidades (Vicerrectorias y subordinadas)",'grupo' => ""); 
	$UNIDADES = array_merge($todas_unidades,$UNIDADES); 
}

$ESTADOS = consulta_sql("SELECT id,nombre FROM vista_poa_estados");

$TIPOS_TAREA = consulta_sql("SELECT id,nombre FROM vista_poa_tipo_act");

$PRIORIDADES = consulta_sql("SELECT id,nombre FROM vista_poas_prioridades");

$MIS_PROYECTOS = consulta_sql("SELECT id,nombre FROM gestion.proyectos WHERE id_unidad={$_SESSION['id_unidad']} ORDER BY nombre");
$OTROS_PROYECTOS = consulta_sql("SELECT id,nombre FROM gestion.proyectos WHERE id_unidad<>{$_SESSION['id_unidad']} ORDER BY nombre");

?>
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>	
<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME'])?>" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_usuario_reg" value="<?php echo($_SESSION['id_usuario']); ?>">
<input type="hidden" name="estado" value="Nueva">
<div style="margin-top: 5px">
  <input type="submit" name="guardar" value="Guardar">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr><td class="celdaNombreAttr" colspan="4" style="text-align: center">Antecedentes de la Tarea</td></tr>
  <tr>
    <td class="celdaNombreAttr"><u>Tipo de Tarea:</u></td>
    <td class="celdaValorAttr">
	  <select class='filtro' name="tipo_act" required>
		<option value="">-- Seleccione --</option>
		<?php echo(select($TIPOS_TAREA,$_REQUEST['tipo_tarea'])); ?>
	  </select>
	</td>
	<td class="celdaNombreAttr"><u>Prioridad:</u></td>
    <td class="celdaValorAttr">
	  <select class='filtro' name="prioridad" required>
		<?php echo(select($PRIORIDADES,$_REQUEST['prioridad'])); ?>
	  </select>
	</td>
  </tr>
  <tr>
    <td class="celdaNombreAttr"><u>Unidad:</u></td>
    <td class="celdaValorAttr" colspan="3">
	  <select class='filtro' name="id_unidad" style="max-width: none" required>
		<option value="">-- Seleccione --</option>
		<?php echo(select_group($UNIDADES,$_REQUEST['id_unidad'])); ?>
	  </select>
	</td>
  </tr>
  <tr>
    <td class="celdaNombreAttr"><u>Actividad:</u></td>
    <td class="celdaValorAttr" colspan="3">
      <textarea name="actividad" value="" cols="40" rows="3" class="general" required></textarea>
	</td>
  </tr>
<!--
  <tr>
    <td class="celdaNombreAttr">Proyecto:</td>
    <td class="celdaValorAttr" colspan="3">
	  <select class='filtro' name="id_proyecto" style="max-width: none">
		<option value="">Ninguno</option>
		<optgroup label="Mis Proyectos">
		  <?php echo(select($MIS_PROYECTOS,$_REQUEST[0]['id_proyecto'])); ?>
		</optgroup>
		<optgroup label="Proyectos de otros">
          <?php echo(select($OTROS_PROYECTOS,$_REQUEST[0]['id_proyecto'])); ?>
		</optgroup>
	  </select>
	</td>
  </tr>
-->
  <tr>
    <td class="celdaNombreAttr"><u>Fecha de Término:</u></td>
    <td class="celdaValorAttr" colspan="3">
      <input type="date" name="fecha_prog_termino" value="<?php //echo(date("Y-m-d")); ?>" class="boton" required>
	</td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Observaciones:</td>
    <td class="celdaValorAttr" colspan="3">
      <textarea name="comentarios" value="" cols="40" rows="6" class="general"></textarea>
	</td>
  </tr>
</table>
</form>

<?php

function email_nueva_tarea($id_unidad,$actividad,$fecha_prog_termino) {
	
	$fecha_prog_termino = strftime("%A %e-%b-%Y",strtotime($fecha_prog_termino));
	
	$unidad = consulta_sql("SELECT nombre FROM gestion.unidades WHERE id=$id_unidad");
	$unidad = $unidad[0]['nombre'];
	
	$usuarios = consulta_sql("SELECT email FROM usuarios WHERE id_unidad=$id_unidad");
	
	$CR = "\r\n";
			
	$cabeceras = "From: SGU" . $CR
	           . "Content-Type: text/plain;charset=utf-8" . $CR;
	           
	$asunto = "POA: Nueva Tarea";
	
	$cuerpo = "Estimad@ Responsable de $unidad" . $CR.$CR
	        . "Se agregó una nueva tarea a su POA:" . $CR.$CR
	        . $actividad . $CR
	        . "Fecha de Término: $fecha_prog_termino" . $CR.$CR
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
