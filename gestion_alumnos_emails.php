<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");
$modulo_destino = "editar_alumno_datos_contacto";
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div><br>
<?php include("alumnos_buscar_emails.php"); ?>
<!-- Fin: <?php echo($modulo); ?> -->
