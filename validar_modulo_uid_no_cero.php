<?php

permiso_ejecutar($_SESSION['id_usuario'],$modulo);

//permiso_ejecutar($_SESSION['id_usuario'],$modulo);

$datos_modulo = modulos($modulo);
$nombre_modulo = $datos_modulo['nombre'];
$enlace_volver = enlace_volver();

?>
