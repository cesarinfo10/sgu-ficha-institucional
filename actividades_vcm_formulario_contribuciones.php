<?php
include("funciones.php");

$tipo_publico  = "'".str_replace(",","','",$_REQUEST['tipo_publico'])."'";

$SQL_contribs = "SELECT id,nombre FROM vcm.contribuciones WHERE ARRAY[" . $tipo_publico . "]::vcm_tipo_publico[] && tipo_publico";
$contribs = consulta_sql($SQL_contribs);

echo(json_encode($contribs));
?>