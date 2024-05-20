<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_actividad  = $_REQUEST['id_actividad'];
$cancelar      = $_REQUEST['cancelar'];
$reactivar     = $_REQUEST['reactivar'];
$finalizar     = $_REQUEST['finalizar'];
$archivar      = $_REQUEST['archivar'];
$token         = $_REQUEST['token'];
$conf_cancelar = $_REQUEST['conf_cancelar'];

if ($conf_cancelar == md5("Si$token")) { 
    consulta_dml("UPDATE vcm.actividades SET estado='Cancelada' WHERE id=$id_actividad");
    consulta_dml("INSERT INTO vcm.actividades_audit VALUES ($id_actividad,{$_SESSION['id_usuario']},'Cancela actividad',default)");
    echo(js("parent.jQuery.fancybox.close();"));
    exit;
}

if ($reactivar == "Si" && $token == md5($id_actividad)) {
    consulta_dml("UPDATE vcm.actividades SET estado='Programada' WHERE id=$id_actividad");
    consulta_dml("INSERT INTO vcm.actividades_audit VALUES ($id_actividad,{$_SESSION['id_usuario']},'Reactiva actividad',default)");
}

if ($finalizar == "Si" && $token == md5($id_actividad) && permiso_ejecutar($_SESSION["id_usuario"],'actividades_vcm_visar_termino')) {
	$participacion = consulta_sql("SELECT id FROM vcm.participacion_act WHERE id_actividad=$id_actividad AND (cant_personas IS NOT NULL OR cant_personas_virtuales IS NOT NULL)");
	$doctos = consulta_sql("SELECT id FROM vcm.documentos_act WHERE id_actividad=$id_actividad");
	$indicadores = consulta_sql("SELECT id FROM vcm.indicadores_act WHERE id_actividad=$id_actividad");

	if (count($participacion) == 0 || count($doctos) == 0 || count($indicadores) == 0) {
		echo(msje_js("ERROR: No es posible Finalizar esta actividad debido a que no tiene registrados la participación, los documentos o los indicadores.\\n\\n"
		            ."Debe registrar esta información antes de dar por Finalizada una actividad."));
		consulta_dml("INSERT INTO vcm.actividades_audit VALUES ($id_actividad,{$_SESSION['id_usuario']},'Intenta finalizar una actividad que está incompleta',default)");	
	} else {
		consulta_dml("UPDATE vcm.actividades SET estado='Finalizada' WHERE id=$id_actividad");
		consulta_dml("INSERT INTO vcm.actividades_audit VALUES ($id_actividad,{$_SESSION['id_usuario']},'Finaliza actividad',default)");	
		echo(msje_js("Se ha registrado la Finalización de la actividad satisfactoriamente."));
	}	
}

if ($archivar == "Si" && $token == md5($id_actividad) && permiso_ejecutar($_SESSION["id_usuario"],'actividades_vcm_visar_termino')) {
	consulta_dml("UPDATE vcm.actividades SET estado='Archivada' WHERE id=$id_actividad");
	consulta_dml("INSERT INTO vcm.actividades_audit VALUES ($id_actividad,{$_SESSION['id_usuario']},'Archiva actividad',default)");	
	echo(msje_js("Se ha archivado esta actividad."));
}

$SQL_asist_tot = "SELECT sum(coalesce(cant_personas,0)+coalesce(cant_personas_virtuales,0)) 
                  FROM vcm.participacion_act
			      WHERE id_actividad=act.id";

$SQL_asist = "SELECT char_comma_sum(tipo_publico||': '||coalesce((coalesce(cant_personas,0)+coalesce(cant_personas_virtuales,0))::text,'*')) 
              FROM vcm.participacion_act
			  WHERE id_actividad=act.id";

$SQL_ind = "SELECT char_comma_sum(it.nombre||': '||coalesce(valor::text||CASE WHEN porcentaje THEN '%' ELSE '' END,'*'))
            FROM vcm.indicadores_act AS ind 
            LEFT JOIN vcm.indicadores_act_tipo AS it ON it.id=ind.id_tipo 
            WHERE id_actividad=act.id";

$SQL_doctos = "SELECT char_comma_sum(doc_tipo.nombre||':'||doctos.id::text||':'||pg_size_pretty(length(archivo)::bigint)) 
               FROM vcm.documentos_act AS doctos
			   LEFT JOIN vcm.documentos_act_tipo AS doc_tipo ON doc_tipo.id=doctos.id_tipo
               WHERE id_actividad=act.id";

$SQL_act = "SELECT *,
                   to_char(fecha_inicio,'DD-tmMonth-YYYY HH24:MI') AS fec_inicio,
                   to_char(fecha_termino,'DD-tmMonth-YYYY HH24:MI') AS fec_termino,
                   dimension,($SQL_asist) AS tipo_publico,($SQL_asist_tot) AS asist_tot,
                   array_to_string(difusion,',') AS difusion,
                   ($SQL_ind) AS indicadores,($SQL_doctos) AS doctos,
                   coalesce(nombre_unidad1,'')||','||coalesce(nombre_unidad2,'')||','||coalesce(nombre_unidad3,'')||','||coalesce(organizador_externo,'') AS unidades
            FROM vista_vcm_actividades AS act
            WHERE id=$id_actividad";
$act = consulta_sql($SQL_act);

if (count($act) == 1) {
    if ($cancelar == "Si" && $token == md5($id_actividad)) {
        $token2 = md5("Si$token");
        $enl_si = "$enlbase_sm=$modulo&id_actividad=$id_actividad&conf_cancelar=$token2&token=$token";
        $enl_no = "#";
        echo(confirma_js("¿Está seguro de establecer la Cancelación de esta Actividad ({$act[0]['nombre']})?",$enl_si,$enl_no));
    }

    $token = md5($id_actividad);
    $_REQUEST = array_merge($act[0],$_REQUEST);
	$estado = "<span class='".str_replace(" ","",$_REQUEST['estado'])."'>&nbsp;{$_REQUEST['estado']}&nbsp;</span>";
    $_REQUEST['tipo_publico'] = str_replace(",","<br>",$_REQUEST['tipo_publico']). "<br><b>Total: {$act[0]['asist_tot']}</b>";
    $_REQUEST['difusion'] = str_replace(",","<br>",$_REQUEST['difusion']);
    $_REQUEST['unidades'] = str_replace(",","<br>",$_REQUEST['unidades']);
    $_REQUEST['indicadores'] = str_replace(",","<br>",$_REQUEST['indicadores']);

    $fHTML_cursos = "<tr><td class='celdaValorAttr' colspan='3'>%s</td><td class='celdaValorAttr' align='center'>%s</td></tr>";
    $HTNL_cursos = (!empty($_REQUEST["curso1"])) ? sprintf($fHTML_cursos,$_REQUEST['curso1'],$_REQUEST['fecha_eval1']) : sprintf($fHTML_cursos,"&nbsp;","&nbsp;");
    $HTNL_cursos .= (!empty($_REQUEST["curso2"])) ? sprintf($fHTML_cursos,$_REQUEST['curso2'],$_REQUEST['fecha_eval2']) : "";
    $HTNL_cursos .= (!empty($_REQUEST["curso3"])) ? sprintf($fHTML_cursos,$_REQUEST['curso3'],$_REQUEST['fecha_eval3']) : "";

	if ($act[0]['doctos'] <> "") {
		$documentos = explode(",",$act[0]['doctos']);
		$HTML_doctos = "";
		for($x=0;$x<count($documentos);$x++) { 
		  $docto = explode(":",$documentos[$x]);
		  $HTML_doctos .= "<a href='actividades_vcm_doctos_descargar.php?id={$docto[1]}' class='enlaces' target='_blank'>📥 {$docto[0]} ({$docto[2]})</a><br>";
		}	
	} else { 
    $HTML_doctos = "<br><center>** Sin documentos **</center><br><br>"; 
  }

  if ($_REQUEST['indicadores'] == "") { $_REQUEST['indicadores'] = "<br><center>** Sin indicadores **</center><br><br>"; }

}

?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div class='texto' style="margin-top: 5px">
<?php switch ($_REQUEST['estado']) { case "Programada": case "Realizada": case "Pendiente": ?>
  <a href="<?php echo("$enlbase_sm=actividades_vcm_editar&id_actividad=$id_actividad")?>" class="boton">📝 Editar</a> &nbsp;
  <a href="<?php echo("$enlbase_sm=actividades_vcm_ver&id_actividad=$id_actividad&finalizar=Si&token=$token")?>" class="boton">🏁 Finalizar</a> &nbsp;
  <a href="<?php echo("$enlbase_sm=actividades_vcm_ver&id_actividad=$id_actividad&archivar=Si&token=$token")?>" class="boton">📦 Archivar</a> &nbsp;
  <a href="<?php echo("$enlbase_sm=actividades_vcm_asistencia&id_actividad=$id_actividad")?>" class="boton" id="sgu_fancybox_small">📋 Reg. Asistencia</a>
  <a href="<?php echo("$enlbase_sm=actividades_vcm_ind_satisfaccion&id_actividad=$id_actividad")?>" class="boton" id="sgu_fancybox_small">📶 Reg. Indicadores</a>
  <a href="<?php echo("$enlbase_sm=actividades_vcm_doctos&id_actividad=$id_actividad")?>" class="boton" id="sgu_fancybox_small">📎 Adm. Documentos</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <a href="<?php echo("$enlbase_sm=actividades_vcm_ver&id_actividad=$id_actividad&cancelar=Si&token=$token")?>" class="boton">⛔ Cancelar</a> 
<?php break; case "Cancelada": ?>
  <a href="<?php echo("$enlbase_sm=actividades_vcm_ver&id_actividad=$id_actividad&reactivar=Si&token=$token")?>" class="boton">✅ Reactivar</a> &nbsp;
<?php break; } ?>

<?php  if (perm_ejec_modulo($_SESSION["id_usuario"],'actividades_vcm_visar_termino')) { ?>
  <a href="<?php echo("$enlbase_sm=actividades_vcm_editar&id_actividad=$id_actividad")?>" class="boton">📝 Editar</a> &nbsp;
  <a href="<?php echo("$enlbase_sm=actividades_vcm_asistencia&id_actividad=$id_actividad")?>" class="boton" id="sgu_fancybox_small">📋 Reg. Asistencia</a>
  <a href="<?php echo("$enlbase_sm=actividades_vcm_ind_satisfaccion&id_actividad=$id_actividad")?>" class="boton" id="sgu_fancybox_small">📶 Reg. Indicadores</a>
  <a href="<?php echo("$enlbase_sm=actividades_vcm_doctos&id_actividad=$id_actividad")?>" class="boton" id="sgu_fancybox_small">📎 Adm. Documentos</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php  } ?>
</div>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Actividad</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['nombre'] . " " . $estado); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Objetivo:</label></td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['objetivo']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Dimensión/Tipo:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['dimension']." / ".$_REQUEST['nombre_tipo_act']); ?></td>
    <td class='celdaNombreAttr'>Alcance:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['alcance']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Año:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['ano']); ?></td>
    <td class='celdaNombreAttr'>Modalidad:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['modalidad']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha y hora de Inicio:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['fec_inicio']); ?></td>
    <td class='celdaNombreAttr'>Fecha y hora de Término:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['fec_termino']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Público Objetivo:</td>
    <td class='celdaValorAttr' align='right'>
	  <?php echo($_REQUEST['tipo_publico']); ?><br>
	  <b>Contrib. Interna:</b> <?php echo($_REQUEST['contribucion_interna']); ?><br>
	  <b>Contrib. Externa:</b> <?php echo($_REQUEST['contribucion_externa']); ?>
	</td>
    <td class='celdaNombreAttr'>Difusión:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['difusion']); ?></td>
  </tr>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Enlaces</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Videoconferencia:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['enl_videoconferencia']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Videograbación:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['enl_conferencia_grabada']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Encuesta:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['enl_encuesta']); ?></td>
  </tr>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Organización</td></tr>

  <tr>
    <td class='celdaNombreAttr'>Responsable:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['nombre_responsable']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Unidad(es) Organizadora(s):</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['unidades']); ?></td>
  </tr>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de Articulación</td></tr>
  <tr>
    <td class='celdaNombreAttr' colspan="3" style="text-align: center; ">Curso</td>
    <td class='celdaNombreAttr' style="text-align: center; ">Fecha Evaluación</td>
  </tr>

  <?php echo($HTNL_cursos); ?>

  <tr>
    <td class='celdaNombreAttr' colspan="2" style="text-align: center; ">Indicadores</td>
    <td class='celdaNombreAttr' colspan="2" style="text-align: center; ">Documentos</td>
  </tr>
  
  <tr>
    <td class='celdaValorAttr' colspan="2"><?php echo($_REQUEST['indicadores']); ?></td>
    <td class='celdaValorAttr' colspan="2"><?php echo($HTML_doctos); ?></td>
  </tr>


</table>


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
</script>