<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_cat_indicador = $_REQUEST['id_cat_indicador'];
$ano              = $_REQUEST['ano'];

$aCampos = array('id_cat_indicador','ano','valor');

if ($_REQUEST['guardar'] == "Guardar") {

	$SQL_ins = "INSERT INTO gestion.indicadores ".arr2sqlinsert($_REQUEST,$aCampos);
	if (consulta_dml($SQL_ins) > 0) {
		echo(msje_js("Se han guardado exitosamente los datos."));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	} else {
		echo(msje_js("ERROR: Ha ocurrido un error y NO se han guardado los datos."));
	}
}
$indicador_cat = consulta_sql("SELECT abierto FROM gestion.indicadores_categorias WHERE id={$_REQUEST['id_cat_indicador']}");
if ($indicador_cat[0]['abierto'] == "f") {
	echo(msje_js("ERROR: Este indicador está cerrado.\\n\\n No es posible agregar valores."));
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}
include("indicadores_valores_formulario.php");
?>


<!-- Fin: <?php echo($modulo); ?> -->
