<?php
session_start();
include("funciones.php");

$modulo = "certificado_digitalizado";
include("validar_modulo_uid_no_cero.php");

$cod = $_REQUEST['cod'];

$SQL_certificado = "SELECT archivo,folio
                    FROM alumnos_certificados AS ac
                    LEFT JOIN vista_alumnos_certificados_codbarras AS vac USING (folio)
                    WHERE vac.cod='$cod'";
$certificado = consulta_sql($SQL_certificado);
extract($certificado[0]);

if (count($certificado) > 0) {
	header("Cache-control: cache, store");
	header("Content-type: application/pdf");
	header("Content-Disposition: attachment; filename=\"cert_$cod.pdf\"");
	echo pg_unescape_bytea($certificado[0]['archivo']);
}

echo(js("window.close()"));
?>
