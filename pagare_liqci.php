<?php

session_start();
include("funciones.php");
//include("conversor_num2palabras.php");

$id_pagare_liqci = $_REQUEST['id_pagare_liqci'];
$version         = $_REQUEST['version'];

if (!$_SESSION['autentificado'] || !is_numeric($id_pagare_liqci)) {
	header("Location: index.php");
	exit;
}

$modulo = "pagare_liqci";
include("validar_modulo.php");

$SQL_fec_ven = "(p.fecha + '18 months'::interval)::date";
                
$SQL_pagare_liqci = "SELECT pci.monto,(pci.fecha_pago_ini+(cuotas-1||' months')::interval)::date AS fecha_venc,pci.cuotas,
                            pci.fecha,pci.monto,al.nombre,al.rut,al.direccion,al.comuna,al.region,pci.fecha_pago_ini,cci.nulo
                     FROM finanzas.pagares_liqci     AS pci
                     LEFT JOIN finanzas.convenios_ci AS cci ON cci.id=pci.id_convenio_ci
                     LEFT JOIN vista_alumnos         AS al  ON al.id=cci.id_alumno
                     WHERE pci.id='$id_pagare_liqci' AND pci.version='$version'";
$pagare_liqci = consulta_sql($SQL_pagare_liqci);

if (count($pagare_liqci) == 1) {

	extract($pagare_liqci[0]);

	$monto = number_format($monto,2,',','.');

	$fecha_venc = strftime("%e de %B de %Y",strtotime($fecha_venc));
	
	$fecha_ini = strftime("%e de %B de %Y",strtotime($fecha_pago_ini));
		
	$fecha_pagare = strftime("%e de %B de %Y",strtotime($fecha));
	
	$imagen_fondo = "../img/logo_umc_fondo_oficio.jpg";

	if ($nulo == 't') {
		$imagen_fondo = "../img/logo_umc_fondo_oficio_nulo.jpg";
	}
	
	$HTML = ""; 
	include("fmt/pagare_liq_cred_interno_fmt.php");
	
	//echo($HTML);
	$HTML = iconv("UTF-8","ISO-8859-1//TRANSLIT",$HTML);
	$archivo = "pagare_cred_interno_".$id_pagare_cred_interno;
	$hand=fopen("tmp/$archivo","w");
	fwrite($hand,$HTML);
	fclose($hand);
	$html2pdf = "htmldoc -t pdf --fontsize 11 --fontspacing 1.5 --no-strict --size 21.5x33cm --bodyfont helvetica "
	          . "--left 2cm --top 1cm --right 1cm --bottom 1cm --footer '  /' --header '   ' --no-embedfonts "
	          . "--compression=9 --encryption --permissions print,no-copy,no-annotate,no-modify "
	          . "--webpage tmp/$archivo ";
	header("Content-Type: application/pdf");
	header("Content-Disposition: attachment; filename=$archivo.pdf");
	passthru($html2pdf);
	unlink("tmp/$archivo");
	echo(js("window.close();"));	
}
?>
