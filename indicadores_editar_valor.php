<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_indicador = $_REQUEST['id_indicador'];
$ano          = $_REQUEST['ano'];

$aCampos = array('id_cat_indicador','ano','valor');

if ($_REQUEST['guardar'] == "Guardar") {

	$SQL_upd = "UPDATE gestion.indicadores SET ".arr2sqlupdate($_REQUEST,$aCampos)." WHERE id=$id_indicador";
	if (consulta_dml($SQL_upd) > 0) {
		echo(msje_js("Se han guardado exitosamente los datos."));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	} else {
		echo(msje_js("ERROR: Ha ocurrido un error y NO se han guardado los datos."));
	}
}

$indicadores = consulta_sql("SELECT i.*,gic.abierto,gic.valor_porcentaje FROM gestion.indicadores AS i LEFT JOIN gestion.indicadores_categorias AS gic ON gic.id=i.id_cat_indicador WHERE i.id=$id_indicador");
if ($indicadores[0]['abierto'] == "f") {
	echo(msje_js("ERROR: Este indicador está cerrado.\\n\\n No es posible editarlo."));
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}
$_REQUEST = array_merge($_REQUEST,$indicadores[0]);

include("indicadores_valores_formulario.php");
?>


<!-- Fin: <?php echo($modulo); ?> -->
