<?php
include("funciones.php");
session_start();
log_sgu("Saliendo del sistema",$_SESSION['usuario'],"",session_id());
session_destroy();
header("Location: index.php");
?>