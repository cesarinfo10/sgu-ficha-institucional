<?php
$man_arch = fopen("/var/log/sgu/acceso.log","a");
$linea_log = strftime("%b %d %X") . " $sesion: $accion: $usuario: $mensaje\n";
fwrite($man_arch,$linea_log);
fclose($man_arch);
?>
