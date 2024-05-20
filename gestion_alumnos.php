<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");
$modulo_destino = "ver_alumno";
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<div class="texto" style='margin-top: 5px'>
<?php include("alumnos_buscar.php"); ?>
<!-- Fin: <?php echo($modulo); ?> -->
