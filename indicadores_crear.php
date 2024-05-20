<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$aCampos = array('id_unidad','nombre','descripcion','alias','cod_procedencia','relevancia','pde','pde_nro_indicador',
                 'agrupador','subagrupador','mecanismo','mecanismo_cadena','valor_porcentaje',
                 'valor_decimales','periodicidad','period_anual_dia','period_anual_mes','period_semestral_1ro_dia',
                 'period_semestral_1ro_mes','period_semestral_2do_dia','period_semestral_2do_mes','period_mensual_dia','period_semanal_dia_sem',
                 'period_hora','orden'
                );

if ($_REQUEST['guardar'] == "Guardar") {

	$_REQUEST['guardar'] = "";
	$_REQUEST['subitem'] = ($_REQUEST['subitem'] == "") ? "f" : "t";
	$_REQUEST['mecanismo_cadena'] = (!empty($_REQUEST['mecanismo_cadena'])) ? pg_escape_string($_REQUEST['mecanismo_cadena']) : "";
	$relevancia = array();
	foreach($_REQUEST['relevancia'] AS $relev => $valor) { if ($valor=="on") { $relevancia[] = $relev; } }
	$_REQUEST['relevancia'] = "{".implode(",",$relevancia)."}";

	$SQL_ins = "INSERT INTO gestion.indicadores_categorias ".arr2sqlinsert($_REQUEST,$aCampos);
	if (consulta_dml($SQL_ins) > 0) {
		echo(msje_js("Se han guardado exitosamente los datos."));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	} else {
		echo(msje_js("ERROR: Ha ocurrido un error y NO se han guardado los datos."));
	}
}

include("indicadores_formulario.php");
?>


<!-- Fin: <?php echo($modulo); ?> -->
