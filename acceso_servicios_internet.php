<?php

session_start();
include("funciones.php");

$id   = $_REQUEST['id'];
$tipo = $_REQUEST['tipo'];

if (!$_SESSION['autentificado'] || !is_numeric($id) || empty($tipo)) {
	header("Location: index.php");
	exit;
}

$modulo = "acceso_servicios_internet";
include("validar_modulo.php");

if ($tipo == "profesor") {
	$SQL_profesor = "SELECT nombre,nombre_usuario,email,clave FROM vista_usuarios WHERE id='$id'";
	$profesor = consulta_sql($SQL_profesor);
	if (count($profesor) == 0) {
		header("Location: index.php");
		exit;
	}
	extract($profesor[0]);
}
	
$fecha = strftime("%e de %B de %Y",strtotime(time());

$HTML = "";
	 
include("acceso_servicios_".$tipo."_formato.php");
	
//echo($HTML);
$HTML = iconv("UTF-8","ISO-8859-1",$HTML);
$archivo = "acceso_servicios_".$tipo."_".$nombre_usuario;
$hand=fopen($archivo,"w");
fwrite($hand,$HTML);
fclose($hand);
$html2pdf = "htmldoc -t pdf --fontsize 11 --fontspacing 1.2 --no-strict --size 21.5x27.9cm --bodyfont helvetica "
          . "--left 1.5cm --top 1cm --right 1.5cm --bottom 1cm --footer '  /' --header '   ' --no-embedfonts "
          . "--compression=9 --encryption --permissions print,no-copy,no-annotate,no-modify "
          . "--webpage $archivo ";
header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=$archivo.pdf");
passthru($html2pdf);
unlink($archivo);
echo(js("window.close();"));	

?>
