	<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$admin_poa = consulta_sql("SELECT poas_admin FROM usuarios WHERE id={$_SESSION['id_usuario']}");
$admin_poa = ($admin_poa[0]['poas_admin'] == "t") ? true : false;

$ano                = $_REQUEST['ano'];
$mes_desglose       = $_REQUEST['mes_desglose'];
$id_unidad          = $_REQUEST['id_unidad'];
$id_prioridad       = $_REQUEST['id_prioridad'];
$id_estado          = $_REQUEST['id_estado'];
$id_proyecto        = $_REQUEST['id_proyecto'];
$tipo_tarea         = $_REQUEST['tipo_tarea'];
$tiempo_comentarios = $_REQUEST['tiempo_comentarios'];

if (empty($ano))                { $ano = $ANO; }
if (empty($mes_desglose))       { $mes_desglose = date("m"); }
if (empty($id_unidad))          { $id_unidad = "-1"; }
if (empty($id_prioridad))       { $id_prioridad = "-1"; }
if (empty($id_proyecto))        { $id_proyecto = "-1"; }
if ($_SESSION['id_unidad'] <> "" && !$admin_poa) { $id_unidad = $_SESSION['id_unidad']; }
if (empty($id_estado))          { $id_estado = "'OK','Terminada','Eliminada'"; }
if (empty($tipo_tarea))         { $tipo_tarea = "-1"; }
if (empty($tiempo_comentarios)) { $tiempo_comentarios = "-1"; }

$condiciones = "WHERE date_part('year',fecha_prog_termino)=$ano ";

if ($mes_desglose > 0) { $condiciones .= " AND date_part('month',fecha_prog_termino)=$mes_desglose "; }

if ($id_unidad <> "-1") { $condiciones .= " AND poas.id_unidad='$id_unidad' "; }

if ($id_prioridad <> "-1") { $condiciones .= " AND prioridad='$id_prioridad' "; }

if ($id_proyecto <> "-1") { $condiciones .= " AND id_proyecto='$id_proyecto' "; }

if ($tipo_tarea <> "-1") { $condiciones .= " AND tipo_act='$tipo_tarea' "; }

if ($id_estado == "'OK','Terminada','Eliminada'") { $condiciones .= " AND estado NOT IN ($id_estado) "; }
elseif ($id_estado <> "-1") { $condiciones .= " AND estado = '$id_estado' "; }

switch ($tiempo_comentarios) {
	case "dia":
		$condiciones .= " AND poas.comentarios_ult_fecha BETWEEN now()-'1 days'::interval AND now() ";
		break;
	case "semana":
		$condiciones .= " AND poas.comentarios_ult_fecha BETWEEN now()-'7 days'::interval AND now() ";
		break;
	case "mes":
		$condiciones .= " AND poas.comentarios_ult_fecha BETWEEN now()-'1 month'::interval AND now() ";
		break;
	case "semestre":
		$condiciones .= " AND poas.comentarios_ult_fecha BETWEEN now()-'6 month'::interval AND now() ";
		break;
	case "ano":
		$condiciones .= " AND poas.comentarios_ult_fecha BETWEEN now()-'1 years'::interval AND now() ";
		break;
}

$SQL_tareas = "SELECT poas.id,tipo_act,coalesce(p.nombre,'') AS proyecto,actividad,prioridad,
                      to_char(fecha_prog_termino,'DD-tmMon-YYYY') AS fecha_prog_termino,fecha_prog_termino_hist,
                      to_char(fecha_fin_real,'DD-tmMon-YYYY') AS fecha_fin_real,
                      estado,poas.comentarios,CASE WHEN evidencia IS NOT NULL THEN 1 ELSE 0 END AS evidencia,
                      date_part('month',fecha_prog_termino) AS mes_termino,gu.alias AS unidad
               FROM gestion.poas AS poas
               LEFT JOIN gestion.unidades  AS gu ON gu.id=poas.id_unidad
               LEFT JOIN gestion.proyectos AS p  ON p.id=poas.id_proyecto
               $condiciones
               ORDER BY poas.fecha_prog_termino";
//echo($SQL_tareas);
$tareas = consulta_sql($SQL_tareas);
$SQL_tabla_completa = "COPY ($SQL_tareas) to stdout WITH CSV HEADER";
$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'>Tabla Completa</a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);


$HTML = "";
if (count($tareas) > 0) {
	$total_tareas = "en total ".count($tareas);
	$aTot_tareas = $elem = array();
	$mes_tareas = 0;
	for($x=0;$x<count($tareas);$x++) {
		extract($tareas[$x]);
		if ($mes_termino > $mes_tareas) {
			$mt = $mes_tareas+1;
			$url = $_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"]."&mes_desglose=$mt";
			$mes_palabra = $meses_palabra[$mes_tareas]['nombre'];
			$HTML .= "  <tr class='filaTabla' $background>\n"
			      .  "    <td class='celdaNombreAttr' style='text-align: center' colspan='6'><i><b><a href='$url' class='enlaces'>$mes_palabra $ano</a></b></i></td>\n"
			      .  "  </tr>\n";
			if ($mes_tareas == 0 || $mes_termino > $mes_tareas) { $mes_tareas++; }
		}
		if ($mes_termino == $mes_tareas) {
			$actividad = nl2br($actividad);
			if ($comentarios == "") { $comentarios = "<br>"; }
			$comentarios = "<small>".nl2br($comentarios)."</small>";
			$aTot_tareas[$estado]++;
			
			$elem = array_merge($elem,array("ed_$x","ap_$x","obs_$x","est_$x","elim_$x","evid_$x"));
			
			$boton_editar  = "<small><br><br></small><span id='ed_$x' style='visibility: hidden'><a href='$enlbase_sm=editar_tarea_poa&id_tarea=$id' id='sgu_fancybox_small' class='botoncito'>Editar Tarea</a></span>";

			$boton_aplazar = "<small><br><br></small><span id='ap_$x' style='visibility: hidden'></span>";
			if ($estado <> "OK" && $estado <> "Eliminada") {
				$boton_aplazar = "<small><br><br></small><span id='ap_$x' style='visibility: hidden'><a href='$enlbase_sm=tarea_poa_aplazar&id_tarea=$id' id='sgu_fancybox_small' class='botoncito'>Aplazar</a></span>";
			}
			
			$boton_obs = "<div style='text-align: center'><span id='obs_$x' style='visibility: hidden'><a href='$enlbase_sm=tarea_poa_comentarios&id_tarea=$id' id='sgu_fancybox_small' class='botoncito'>Añadir observación</a></div></span>";
			
			$boton_ver_evidencia = "<span id='evid_$x' style='visibility: hidden'></span>";
			$boton_elim = "<small><br><br></small><span id='elim_$x' style='visibility: hidden'></span>";
			if ($estado == "Nueva" || $estado == "Pendiente" || $estado == "Aplazada") {
				$boton_elim = "<div style='text-align: center'><span id='elim_$x' style='visibility: hidden'><a href='$enlbase_sm=tarea_poa_eliminar&id_tarea=$id' class='botoncito' id='sgu_fancybox_small'>Eliminar</a></span></div>";
				$estado = "<div class='$estado'>$estado</div>";
				$boton_terminar = "<span id='est_$x' style='visibility: hidden'><a href='$enlbase_sm=tarea_poa_terminar&id_tarea=$id' id='sgu_fancybox_small' class='botoncito'>Terminar</a></span>";
				$estado .= "<small><br></small>$boton_terminar";
			} elseif ($estado == "Terminada") {
				$boton_ver_evidencia = "<a href='ver_evidencia_poa.php?id_tarea=$id' target='_blank' class='enlaces'>Ver evidencia</a>";
				$estado = "<div class='$estado'>$estado</div>";
				$estado .= "<small><br>$boton_ver_evidencia<small><br>$fecha_fin_real<br></small><span id='est_$x' style='visibility: hidden'><a href='$enlbase_sm=tarea_poa_darok&id_tarea=$id' id='sgu_fancybox_small' class='botoncito'>Dar OK</a></span>";
			} elseif ($estado == "OK") {
				if ($evidencia == 1) { $boton_ver_evidencia = "<small><br><a href='ver_evidencia_poa.php?id_tarea=$id' target='_blank' class='enlaces'>Ver evidencia</a></small>"; }
				$estado = "<div class='$estado'>$estado</div>";
				$estado .= "$boton_ver_evidencia<small><br>$fecha_fin_real</small>";
			} else {
				$estado = "<div class='$estado'>$estado</div>";
			}
			
			$fec_prog_ter_hist = "";
			if ($fecha_prog_termino_hist <> "") {
				$fecha_prog_termino_hist = explode(",",str_replace(array("{","}"),"",$fecha_prog_termino_hist));
				$fec_prog_ter_hist = "<hr><small><b>Fechas anteriores:</b><div align='right'>";
				for($j=0;$j<count($fecha_prog_termino_hist);$j++) {
					$fec_prog_ter_hist .= strftime("%d-%b-%Y",strtotime($fecha_prog_termino_hist[$j]))."<br>";
				}
				$fec_prog_ter_hist .= "</div></small>";
			}
			
			$HTML_unidad = "";
			if ($id_unidad == "-1") { $HTML_unidad = "    <td class='textoTabla' align='center'>$unidad</td>\n"; }
			
			$prioridad = "<div style='margin: 3px; text-align: right'><span class='$prioridad'>$prioridad</span></div>";
			
			$proyecto = ($proyecto <> "") ? "<b>$proyecto</b><br>" : "";
			
			$HTML .= "  <tr class='filaTabla' $background onMouseOver=\"elementos=['ed_$x','ap_$x','obs_$x','est_$x','elim_$x'];elementos.forEach(mostrar_elementos);\" onMouseOut=\"elementos=['ed_$x','ap_$x','obs_$x','est_$x','elim_$x'];elementos.forEach(ocultar_elementos);\">\n"
			      .  "    <td class='textoTabla' align='center'>$tipo_act $boton_editar</td>\n"
			      .  $HTML_unidad
			      .  "    <td class='textoTabla' width='300'>$proyecto $actividad $prioridad $boton_elim</td>\n"
			      .  "    <td class='textoTabla' align='center'>$fecha_prog_termino $fec_prog_ter_hist $boton_aplazar</td>\n"
			      .  "    <td class='textoTabla' align='center'>$estado</td>\n"
			      .  "    <td class='textoTabla' width='300'>$comentarios $boton_obs</td>\n"
			      .  "  </tr>\n";
		} else {
			$x--;
		}
	}
	foreach($aTot_tareas AS $estado => $cantidad) { $total_tareas .= ", $cantidad $estado(s)"; }
} else {
	$HTML .= "  <tr class='filaTabla'>\n"
	      .  "    <td class='textoTabla' align='center' colspan='6'><br>** No hay tareas para los filtros seleccionados **<br><br></td>"
	      .  "  </tr>";
}
$HTML_tareas = $HTML;

$ANOS_TAREAS = consulta_sql("SELECT id,nombre FROM vista_poas_anos");

$cond_unidades = "";
if ($_SESSION['id_unidad'] <> "" && !$admin_poa) { $cond_unidades = "WHERE u.activa AND u.id = {$_SESSION['id_unidad']}"; $id_unidad = $_SESSION['id_unidad']; }
if ($admin_poa) { $cond_unidades = "WHERE u.activa AND u.id IN (SELECT id_unidad FROM gestion.poas WHERE date_part('year',fecha_prog_termino) = '$ano')"; }
$UNIDADES = consulta_sql("SELECT u.id,u.nombre||' ('||u.alias||')' AS nombre,d.nombre AS grupo FROM gestion.unidades AS u LEFT JOIN gestion.unidades AS d ON d.id=u.dependencia $cond_unidades ORDER BY coalesce(u.dependencia,0),u.nombre");


$ESTADOS = consulta_sql("SELECT id,nombre FROM vista_poa_estados");
$ESTADOS = array_merge(array(array('id'=>"'OK','Terminada','Eliminada'", 'nombre'=>"Todas NO Terminadas")),$ESTADOS);

$MIS_PROYECTOS = consulta_sql("SELECT id,nombre FROM gestion.proyectos WHERE id_unidad={$_SESSION['id_unidad']} ORDER BY nombre");
$OTROS_PROYECTOS = consulta_sql("SELECT id,nombre FROM gestion.proyectos WHERE id_unidad<>{$_SESSION['id_unidad']} ORDER BY nombre");

$TIPOS_TAREA = consulta_sql("SELECT id,nombre FROM vista_poa_tipo_act");

$PRIORIDADES = consulta_sql("SELECT id,nombre FROM vista_poas_prioridades");

if ($id_unidad > 0) { $nombre_unidad = consulta_sql("SELECT 'de '||nombre||' ('||alias||')' AS nombre FROM gestion.unidades WHERE activa AND id=$id_unidad"); }

$TIEMPOS_COMENTARIOS = array(array('id'=>"dia",     'nombre'=>"en el último día"),
                             array('id'=>"semana",  'nombre'=>"en la última semana"),
                             array('id'=>"mes",     'nombre'=>"en el último mes"),
                             array('id'=>"semestre",'nombre'=>"en el último semestre"),
                             array('id'=>"ano",     'nombre'=>"en el último año"));
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<table cellpadding="1" border="0" cellspacing="2" width="auto" style="margin-top: 5px">
  <tr>
	<td class="celdaFiltro">
	  Año:<br>
	  <select class='filtro' name="ano" onChange="submitform();">
		<?php echo(select($ANOS_TAREAS,$ano)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
	  Unidad Administrativa:<br>
	  <select class='filtro' name="id_unidad" style="max-width: none" onChange="submitform();">
		<option value="-1">Todas</option>
		<?php echo(select_group($UNIDADES,$id_unidad)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
	  Mes desglosado:<br>
	  <select class='filtro' name="mes_desglose" onChange="submitform();">
		<option value="-1">Todos</option>
		<?php echo(select($meses_palabra,$mes_desglose)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
	  Estado Tarea(s):<br>
	  <select class='filtro' name="id_estado" onChange="submitform();">
		<option value="-1">Todas</option>
		<?php echo(select($ESTADOS,$id_estado)); ?>
	  </select>
	</td>
  </tr>
</table>
<table cellpadding="1" border="0" cellspacing="2" width="auto">
  <tr>
	<td class="celdaFiltro">
	  Tipo Tarea:<br>
	  <select class='filtro' name="tipo_tarea" onChange="submitform();">
		<option value="-1">Todos</option>
		<?php echo(select($TIPOS_TAREA,$tipo_tarea)); ?>
	  </select>
	</td>
<!--
	<td class="celdaFiltro">
	  Proyectos:<br>
	  <select class='filtro' name="id_proyecto" onChange="submitform();">
		<option value="-1">** Sin Proyecto **</option>
		<optgroup label="Mis Proyectos">
		  <?php echo(select($MIS_PROYECTOS,$id_proyecto)); ?>
		</optgroup>
		<optgroup label="Proyectos de otros">
          <?php echo(select($OTROS_PROYECTOS,$id_proyecto)); ?>
		</optgroup>
	  </select>
	</td>
-->
    <td class="celdaFiltro">
	  Prioridad:<br>
	  <select class='filtro' name="id_prioridad" onChange="submitform();">
		<option value="-1">Todas</option>
		<?php echo(select($PRIORIDADES,$id_prioridad)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
	  Tareas con Observaciones:<br>
	  <select class='filtro' name="tiempo_comentarios" onChange="submitform();">
		<option value="-1">Todas</option>
		<?php echo(select($TIEMPOS_COMENTARIOS,$tiempo_comentarios)); ?>
	  </select>
	</td>
<!--  </tr>
</table>
<table cellpadding="1" border="0" cellspacing="2" width="auto">
  <tr> -->
	<td class="celdaFiltro">
	  Acciones:<br>
	  <a href="<?php echo("$enlbase_sm=crear_tarea_poa&id_unidad=$id_unidad&id_proyecto=$id_proyecto&tipo_tarea=$tipo_tarea")?>" class="boton" id="sgu_fancybox_small">Nueva Tarea</a>
      <?php echo($boton_tabla_completa); ?>

<!--	  <a href="<?php echo("$enlbase_sm=resumen_tareas_unidades")?>" class="boton" id="sgu_fancybox_small">Resumen por Unidades</a> -->
<!--	  <a href="<?php echo("$enlbase_sm=resumen_tareas_tipos")?>" class="boton" id="sgu_fancybox_small">Resumen por Tipo de Tarea</a>  -->
<!--	  <a href="<?php echo("$enlbase_sm=gestion_proyectos")?>" class="boton" id="sgu_fancybox_small">Proyectos</a>  -->
	</td>
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="6">Tareas <?php echo($nombre_unidad[0]['nombre']); ?><br><small><?php echo($total_tareas); ?></small></td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Tipo</td>
    <?php if ($id_unidad == "-1") { ?><td class='tituloTabla'>Unidad</td><?php } ?>
    <td class='tituloTabla'>Actividad</td>    
    <td class='tituloTabla'>F. Término</td>
    <td class='tituloTabla'>Estado</td>
    <td class='tituloTabla'>Observaciones</td>
  </tr>
  <?php echo($HTML_tareas); ?>
</table>
</form>

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'		: false,
		'autoDimensions': false,
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic',
		'width'			: 850,
		'maxHeight'		: 650,
		'afterClose'	: function () { location.reload(true); },
		'type'			: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'		: false,
		'autoDimensions': false,
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic',
		'width'			: 1000,
		'maxHeight'		: 700,
		'afterClose'	: function () { location.reload(true); },
		'type'			: 'iframe'
	});
});

function mostrar_elementos(id_elem) {
	document.getElementById(id_elem).style.visibility='visible';
}

function ocultar_elementos(id_elem) {
	document.getElementById(id_elem).style.visibility='hidden';
}
</script>
