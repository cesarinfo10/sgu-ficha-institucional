<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");
include("sinergia/func_sinergia.php");

$ids_carreras = $_SESSION['ids_carreras'];

$semestre_periodo  = $_REQUEST['semestre_periodo'];
$ano_periodo       = $_REQUEST['ano_periodo'];
$prueba            = $_REQUEST['prueba'];
$nivel             = $_REQUEST['nivel'];
$categoria         = $_REQUEST['categoria'];
$id_carrera        = $_REQUEST['id_carrera'];
$jornada           = $_REQUEST['jornada'];
$semestre_cohorte  = $_REQUEST['semestre_cohorte'];
$cohorte           = $_REQUEST['cohorte'];
$estado            = $_REQUEST['estado'];
$moroso_financiero = $_REQUEST['moroso_financiero'];
$admision          = $_REQUEST['admision'];
$regimen           = $_REQUEST['regimen'];
$genero            = $_REQUEST['genero'];
$rango_etario      = $_REQUEST['rango_etario'];
$mod_ant           = $_REQUEST['mod_ant'];

$SQL_interpretacion = "SELECT categoria_nombre,descripcion,sugerencias FROM sinergia.interpretaciones WHERE tipo='grupal' AND categoria='$categoria' AND nivel='$nivel'";
$interpretacion = consulta_sql($SQL_interpretacion);
if (count($interpretacion) == 0) { 		echo(js("parent.jQuery.fancybox.close();")); }
extract($interpretacion[0]);
$HTML = "<b>Descripción de la ".$categoria_nombre." «".$categoria."» y el nivel «".$nivel."»:</b>"
      . "<blockquote style='text-align: justify'>".nl2br($descripcion)."</blockquote>"
      . "<b>Sugerencia(s):</b>"
      . "<blockquote style='text-align: justify'>".nl2br($sugerencias)."</blockquote>";
?>
<div class="texto">
	<?php echo($HTML); ?>
</div>
