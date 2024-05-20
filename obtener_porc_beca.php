<?php
include("funciones.php");
$id_beca = $_REQUEST['id_beca'];
$porc_beca = consulta_sql("SELECT porcentaje::int2 FROM becas WHERE id=$id_beca");
$porc_beca = $porc_beca[0]['porcentaje'];
echo($porc_beca);
?>
