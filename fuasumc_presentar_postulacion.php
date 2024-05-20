<?php
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

if (isset($_SESSION['tipo'])) { 
	include("validar_modulo.php"); 
} else { 
	$datos_modulo = modulos($modulo); 
	$nombre_modulo = $datos_modulo['nombre'];
}

$id_fuas   = $_REQUEST['id_fuas'];

$SQL_fuas = "SELECT  FROM dae.fuas LEFT JOIN dae.fuas_doctos_ing AS fdi ON fdi.id_fuas=fuas.id WHERE jefe_hogar AND fuas.id="

$SQL_fuas_doctos = "SELECT id 
                    FROM dae.fuas_doctos_ing
                    WHERE id_fuas=$id_fuas 
                       OR id_fuas_grupo_familiar IN (SELECT id FROM dae.fuas_grupo_familiar WHERE id_fuas=$id_fuas)";
$fuas_doctos = consulta_sql($SQL_fuas_doctos);

$SQL_fuas_jefes_hogar = "SELECT 1 FROM dae.fuas WHERE id=$id_fuas AND jefe_hogar UNION ALL SELECT 1 FROM dae.fuas_grupo_familiar WHERE id_fuas=$id_fuas AND jefe_hogar";
$fuas_jefes_hogar = consulta_dml($SQL_fuas_jefes_hogar);

if (count($fuas_doctos) <> count($fuas_jefes_hogar)) {
	echo(msje_js("ERROR: En tu postulación no se encuentran subidos todos los "
	            ."documentos de acreditación de renta (Certificado de Cotizaciones "
	            ."de AFP, Carpeta Tributaria o Certificado de Pensiones).\\n\\n"
	            ."Debes subir los documentos que corresponde para así presentar tu postulación")); 
}
?>
