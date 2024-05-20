<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$aCampos = array('nombre','id_responsable','ano','fecha_inicio','fecha_termino',
                 'id_unidad1','id_unidad2','id_unidad3','organizador_externo',
                 'id_tipo','alcance','objetivo','modalidad','tipo_publico','contrib_interna','contrib_externa',
                 'id_curso1','fecha_eval1','id_curso2','fecha_eval2','id_curso3','fecha_eval3',
                 'id_curso4','fecha_eval4','id_curso5','fecha_eval5','id_curso6','fecha_eval6',
                 'difusion'
                );

if ($_REQUEST['guardar'] == "💾 Guardar") {
	$_REQUEST['fecha_inicio']  = $_REQUEST['fec_inicio']." ".$_REQUEST['hora_inicio']; 
	$_REQUEST['fecha_termino'] = $_REQUEST['fec_termino']." ".$_REQUEST['hora_termino'];
    $_REQUEST['difusion']      = "{".implode(",",$_REQUEST['_difusion'])."}";
    $_REQUEST['tipo_publico']  = "{".implode(",",$_REQUEST['_tipo_publico'])."}";

	$SQL_ins = "INSERT INTO vcm.actividades ".arr2sqlinsert($_REQUEST,$aCampos);
	if (consulta_dml($SQL_ins) > 0) {
        $actividad = consulta_sql("SELECT id FROM vcm.actividades WHERE nombre='{$_REQUEST['nombre']}' AND ano={$_REQUEST['ano']} ORDER BY id DESC LIMIT 1");
        $id_actividad = $actividad[0]['id'];
        consulta_dml("INSERT INTO vcm.actividades_audit VALUES ($id_actividad,{$_SESSION['id_usuario']},default,default)");
        for($x=0;$x<count($_REQUEST['_tipo_publico']);$x++) { 
            consulta_dml("INSERT INTO vcm.participacion_act VALUES (default,$id_actividad,'{$_REQUEST['_tipo_publico'][$x]}',null)");
        }
		echo(msje_js("Se han guardado exitosamente los datos."));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	} else {
		echo(msje_js("ERROR: Ha ocurrido un error y NO se han guardado los datos."));
	}
}

?>
<!-- Fin: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<?php include("actividades_vcm_formulario.php"); ?>