<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_convenio_ci = $_REQUEST['id_convenio_ci'];
$anular         = $_REQUEST['anular'];

if (!is_numeric($id_convenio_ci)) {
	echo(js("location.href='$enlbase=gestion_liquidaciones_cred_interno';"));
	exit;
}

if (!empty($id_convenio_ci) && $anular == "OK") {
	$SQL_upd_contratos = "UPDATE finanzas.contratos SET ci_liquidado=false, id_convenio_liqci=null WHERE id_convenio_liqci = $id_convenio_ci";
	$SQL_upd_convenio_ci = "UPDATE finanzas.convenios_ci SET nulo=true WHERE id=$id_convenio_ci";
	if (consulta_dml($SQL_upd_contratos) > 0) {
		echo(msje_js("Se han libarado los contratos asociados para emitir nuevamente un Convenio de Liquidación"));
	} else {
		echo(msje_js("ERROR: NO se han libarado los contratos asociados. Posiblemente no podrá emitir nuevamente un Convenio de Liquidación. . Comunique este error al Departamento de Informática."));
	}
	
	if (consulta_dml($SQL_upd_convenio_ci) > 0) {
		echo(msje_js("Se ha anulado el convenio. Ahora puede emitir nuevamente un Convenio de Liquidación para este alumno."));
	} else {
		echo(msje_js("ERROR: NO se ha anulado el convenio. Comunique este error al Departamento de Informática."));
	}
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

?>
<script>

pregunta_anular();

function pregunta_anular() {
	var url_base = window.location.href;
	
	if (confirm("Está seguro de anular este convenio?")) {
		location.href=url_base+"&anular=OK";
	} else {
		parent.jQuery.fancybox.close();
	}
}

</script>
