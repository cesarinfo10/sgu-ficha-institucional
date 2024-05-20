<?php

session_start();
include("funciones.php");
//include("conversor_num2palabras.php");

if ($argv[1]=="") {
	$id_pagare_colegiatura = $_REQUEST['id_pagare_colegiatura'];
} elseif (is_numeric($argv[1])) {
	$id_pagare_colegiatura = $argv[1];
}

if (!$_SESSION['autentificado'] || !is_numeric($id_pagare_colegiatura)) {
	header("Location: index.php");
	exit;
}

$modulo = "pagare_colegiatura";
include("validar_modulo.php");

$SQL_pagare_coleg = "SELECT p.*,p.fecha::date,a.*,c.estado 
                     FROM finanzas.pagares_colegiatura AS p
                     LEFT JOIN finanzas.contratos AS c ON p.id_contrato=c.id
                     LEFT JOIN vista_avales AS a ON a.id=c.id_aval
                     WHERE p.id='$id_pagare_colegiatura'";
$pagare_coleg     = consulta_sql($SQL_pagare_coleg);

if (count($pagare_coleg) == 1) {

	extract($pagare_coleg[0]);

	$nombre_replegal = "Francisco Cumplido Cereceda";
	$rut_replegal    = "2.964.705-4";

	$monto_cuotas = round($monto / $cuotas);
	
	$monto_palabras        = num2palabras($monto);
	$monto_cuotas_palabras = num2palabras($monto_cuotas);
	
	$monto        = number_format($monto,0,',','.');
	$monto_cuotas = number_format($monto_cuotas,0,',','.');
	
	$mes_inicio = $meses_palabra[$mes_inicio-1]['nombre'];
	
	$fecha_pagare = strftime("%e de %B de %Y",strtotime($fecha));

	
	$FIRMAS = "<table width='100%'>".$LF
	        . "  <tr>".$LF
	        . "    <td align='center' valign='top' width='70%'>&nbsp;</td>".$LF
	        . "    <td align='center' valign='top' width='30%'><hr noshade size='1'><b>Firma del Suscriptor</b></td>".$LF
	        . "  </tr>".$LF
	        . "</table><br>".$LF
	        . "<table width='100%'>".$LF
	        . "  <tr>".$LF
	        . "    <td valign='top' width='15%' nowrap>Representante Legal:</td><td valign='top' width='70%'>$rf_nombre</td>".$LF
	        . "    <td valign='top' width='5%' nowrap>RUT:</td>                <td valign='top' width='10%'>$rf_rut</td>".$LF
	        . "  </tr>".$LF
	        . "  <tr>".$LF
	        . "    <td valign='top' width='15%' nowrap>Nombre o Razón Social:</td><td valign='top' width='70%'>$rf_nombre</td>".$LF
	        . "    <td valign='top' width='5%' nowrap>RUT:</td>                  <td valign='top' width='10%'>$rf_rut</td>".$LF
	        . "  </tr>".$LF
	        . "  <tr>".$LF
	        . "    <td valign='top' widht='15%' nowrap>Domicilio:</td><td valign='top' width='70%'>$rf_direccion</td>".$LF
	        . "    <td valign='top' width='5%' nowrap>Comuna:</td>   <td valign='top' width='10%'>$rf_com</td>".$LF
	        . "  </tr>".$LF
	        . "</table>".$LF;

	$imagen_fondo = "../img/logo_umc_fondo_oficio.jpg";
	if ($estado == '') {
		$imagen_fondo = "../img/logo_umc_fondo_oficio_nulo.jpg";
	}
	
	$HTML = ""; 
	include("pagare_colegiatura_formato.php");
	
	//echo($HTML);
	$HTML = iconv("UTF-8","ISO-8859-1//TRANSLIT",$HTML);
	$archivo = "pagare_colegiatura_".$id_pagare_colegiatura;
	$hand=fopen("tmp/$archivo","w");
	fwrite($hand,$HTML);
	fclose($hand);
	$html2pdf = "htmldoc -t pdf --fontsize 11 --fontspacing 1.2 --no-strict --size 21.5x33cm --bodyfont helvetica "
	          . "--left 1.5cm --top 1cm --right 1.5cm --bottom 1cm --footer '   ' --header '   ' --no-embedfonts "
	          . "--compression=9 --encryption --permissions print,no-copy,no-annotate,no-modify "
	          . "--webpage tmp/$archivo ";
	header("Content-Type: application/pdf");
	header("Content-Disposition: attachment; filename=$archivo.pdf");
	passthru($html2pdf);
	unlink("tmp/$archivo");
	echo(js("window.close();"));	
}
?>
