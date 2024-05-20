<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");
$modulo_destino = "tomar_ramos";
$cond_base = "(SELECT count(id_alumno) FROM inscripciones_cursos WHERE id_alumno=a.id)=0";
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<div class="texto" style='margin-top: 5px'>
<?php include("alumnos_buscar.php"); ?>
<!-- Fin: <?php echo($modulo); ?> -->
