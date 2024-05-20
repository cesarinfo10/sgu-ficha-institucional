<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_indicador_cat = $_REQUEST['id_indicador_cat'];

$aCampos = array('id_unidad','nombre','descripcion','alias','cod_procedencia','relevancia','pde','pde_nro_indicador',
                 'agrupador','subagrupador','mecanismo','mecanismo_cadena','valor_porcentaje',
                 'valor_decimales','periodicidad','period_anual_dia','period_anual_mes','period_anual_dia_ini','period_anual_mes_ini','period_semestral_1ro_dia',
                 'period_semestral_1ro_mes','period_semestral_2do_dia','period_semestral_2do_mes','period_mensual_dia','period_semanal_dia_sem',
                 'period_hora','orden','estandar','abierto','activo','estandar_tipo','totalizador','subitem'
                );

if ($_REQUEST['guardar'] == "Guardar") {

	$_REQUEST['guardar'] = "";
	$_REQUEST['subitem'] = ($_REQUEST['subitem'] == "") ? "f" : "t";

	$_REQUEST['mecanismo_cadena'] = (!empty($_REQUEST['mecanismo_cadena'])) ? pg_escape_string($_REQUEST['mecanismo_cadena']) : "";

	$relevancia = array();
	foreach($_REQUEST['relevancia'] AS $relev => $valor) { if ($valor=="on") { $relevancia[] = $relev; } }
	$_REQUEST['relevancia'] = "{".implode(",",$relevancia)."}";

	$SQL_upd = "UPDATE gestion.indicadores_categorias SET ".arr2sqlupdate($_REQUEST,$aCampos)." WHERE id=$id_indicador_cat";
	if (consulta_dml($SQL_upd) > 0) {
		echo(msje_js("Se han guardado exitosamente los datos."));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	} else {
		echo(msje_js("ERROR: Ha ocurrido un error y NO se han guardado los datos."));
	}
}

$indicadores_cat = consulta_sql("SELECT *,to_char(period_hora,'HH24:MI') AS period_hora FROM gestion.indicadores_categorias WHERE id=$id_indicador_cat");
$_REQUEST = array_merge($_REQUEST,$indicadores_cat[0]);

include("indicadores_formulario.php");
?>


<!-- Fin: <?php echo($modulo); ?> -->
