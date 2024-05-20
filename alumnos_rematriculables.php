<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");
$modulo_destino = "ver_alumno";
if (empty($_REQUEST['estado'])) { $_REQUEST['estado'] = 1; }
//$cond_base = "(SELECT CASE WHEN count(id_alumno)=0 THEN true ELSE false END FROM matriculas WHERE ano=$ANO_MATRICULA AND id_alumno=a.id)";
//$cond_base = "mat.id_alumno IS NULL";
$_REQUEST['matriculado'] = $matriculado = "f";
$_REQUEST['matriculado'] = "f";
$ANO = $ANO_MATRICULA;
$SEMESTRE = $SEMESTRE_MATRICULA;
//if (empty($_REQUEST['aprob_ant'])) { $_REQUEST['aprob_ant'] = "3"; }
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<div class="texto" style='margin-top: 5px'>
<?php include("alumnos_buscar.php"); ?>
<!-- Fin: <?php echo($modulo); ?> -->
