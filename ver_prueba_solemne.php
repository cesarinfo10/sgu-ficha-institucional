<?php

include("funciones.php");

$modulo = "ver_prueba_solemne";
include("validar_modulo.php");

$id_curso = $_REQUEST['id_curso'];
$prueba   = $_REQUEST['prueba'];
$token    = $_REQUEST['token'];

$SQL_campo = $prueba."_arch";
$SQL_arch = "SELECT $SQL_campo 
             FROM cursos_pruebas cp 
             LEFT JOIN cursos c ON c.id=cp.id_curso 
             WHERE id_curso='$id_curso' AND md5(id_curso::text||id_profesor::text)='$token'";
$arch     = consulta_sql($SQL_arch);
if (count($arch) > 0) {
	header('Cache-control: cache, store');
	header('Content-type: application/pdf');
	header("Content-Disposition: attachment; filename=\"prueba_$prueba.pdf\"");
	echo pg_unescape_bytea($arch[0][$SQL_campo]);
} else {
	echo(msje_js("Error de consistencia. No se puede continuar"));
	exit;
}

?>