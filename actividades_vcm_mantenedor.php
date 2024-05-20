<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$tabla  = $_REQUEST['tabla'];

$tablas = array('tipos_act'            => "Tipos de Actividades",
                'documentos_act_tipo'  => "Tipos de Documentos",
                'indicadores_act_tipo' => "Tipos de Indicadores");

?>

<div class="tituloModulo">
  <?php echo($nombre_modulo. " " . $tablas[$tabla]); ?>
</div>

<?php include("actividades_vcm_mantenedor_$tabla.php"); ?>