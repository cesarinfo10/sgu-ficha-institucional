<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$aCampos = array('nombre','id_usuario_reg','ano','fecha_inicio','fecha_termino','id_estado',
                 'palabras_clave','id_tipo','alcance','id_medio_public','public_formato',
                 'revista_nombre','revista_numero','revista_editorial','revista_ciudad','revista_pais','revista_factor_impacto','revista_enlace',
                 'libro_nombre','libro_editorial','libro_ciudad','libro_pais','libro_enlace',
                 'proyecto_organismo','proyecto_id_invest_princ',
                 'informe_organismo',
                 'ponencia_nombre_congreso','ponencia_modalidad','ponencia_ciudad','ponencia_pais'                 
                );

if ($_REQUEST['guardar'] == "💾 Guardar") {

	$SQL_ins = "INSERT INTO dpii.productos_acad ".arr2sqlinsert($_REQUEST,$aCampos);
	if (consulta_dml($SQL_ins) > 0) {
        $producto_acad = consulta_sql("SELECT id FROM dpii.productos_acad WHERE nombre='{$_REQUEST['nombre']}' AND ano={$_REQUEST['ano']} ORDER BY id DESC LIMIT 1");
        $id_prod_acad = $producto_acad[0]['id'];
        consulta_dml("INSERT INTO dpii.productos_acad_audit VALUES ($id_prod_acad,{$_SESSION['id_usuario']},default,default)");
		echo(msje_js("Se han guardado exitosamente los datos."));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	} else {
		echo(msje_js("ERROR: Ha ocurrido un error y NO se han guardado los datos."));
	}
}

$_REQUEST['autores']     = "** Se agregan a posterior **";
$_REQUEST['asignaturas'] = "** Se agregan a posterior **";
?>
<!-- Fin: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<?php include("productos_acad_formulario.php"); ?>