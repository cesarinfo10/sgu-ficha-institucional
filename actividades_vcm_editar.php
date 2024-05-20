<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_actividad = $_REQUEST['id_actividad'];

$aCampos = array('nombre','id_responsable','ano','fecha_inicio','fecha_termino',
                 'id_unidad1','id_unidad2','id_unidad3',"organizador_externo",
                 'id_tipo','alcance','objetivo','modalidad','tipo_publico','contrib_interna','contrib_externa',
                 'id_curso1','fecha_eval1','id_curso2','fecha_eval2','id_curso3','fecha_eval3',
                 'id_curso4','fecha_eval4','id_curso5','fecha_eval5','id_curso6','fecha_eval6',
                 'difusion','enl_videoconferencia','enl_conferencia_grabada'
                );

if ($_REQUEST['guardar'] == "💾 Guardar") {
	$_REQUEST['fecha_inicio']  = $_REQUEST['fec_inicio']." ".$_REQUEST['hora_inicio']; 
	$_REQUEST['fecha_termino'] = $_REQUEST['fec_termino']." ".$_REQUEST['hora_termino'];
    $_REQUEST['difusion']      = "{".implode(",",$_REQUEST['_difusion'])."}";
    $_REQUEST['tipo_publico']  = "{".implode(",",$_REQUEST['_tipo_publico'])."}";

	$SQL_upd = "UPDATE vcm.actividades SET ".arr2sqlupdate($_REQUEST,$aCampos)." WHERE id=$id_actividad";
	if (consulta_dml($SQL_upd) > 0) {
        consulta_dml("INSERT INTO vcm.actividades_audit VALUES ($id_actividad,{$_SESSION['id_usuario']},'Modificación',default)");
        for($x=0;$x<count($_REQUEST['_tipo_publico']);$x++) { 
            consulta_dml("INSERT INTO vcm.participacion_act VALUES (default,$id_actividad,'{$_REQUEST['_tipo_publico'][$x]}',null)");
        }
		echo(msje_js("Se han guardado exitosamente los datos."));
        echo(js("location.href='$enlbase_sm=actividades_vcm_ver&id_actividad=$id_actividad';"));
		//echo(js("parent.jQuery.fancybox.close();"));
		exit;
	} else {
		echo(msje_js("ERROR: Ha ocurrido un error y NO se han guardado los datos."));
	}
}

$SQL_act = "SELECT *,
                   fecha_inicio::date AS fec_inicio,to_char(fecha_inicio,'HH24:MI') AS hora_inicio,
                   fecha_termino::date AS fec_termino,to_char(fecha_termino,'HH24:MI') AS hora_termino
            FROM vcm.actividades 
            WHERE id=$id_actividad";
$act = consulta_sql($SQL_act);
if (count($act) > 0) {
    $_REQUEST = array_merge($act[0],$_REQUEST);
    $_REQUEST['_tipo_publico'] = explode(",",str_replace("\"","",trim($_REQUEST['tipo_publico'],"{}")));
    $_REQUEST['_difusion'] = explode(",",str_replace("\"","",trim($_REQUEST['difusion'],"{}")));
}

?>
<!-- Fin: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<?php include("actividades_vcm_formulario.php"); ?>