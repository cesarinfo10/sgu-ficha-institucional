<?php

session_start();
include("funciones.php");

$sesion = $_REQUEST['sesion'];
$regimen = $_REQUEST['regimen'];

$fmt_contrato = "fmt/contrato_preimp_$regimen.php";
$fmt_pagare   = "fmt/pagare_colegiatura_preimp_$regimen.php";

$html_doctos = generar_doctos($sesion,$fmt_contrato,$fmt_pagare);

$HTML = iconv("UTF-8","ISO-8859-1//TRANSLIT",$html_doctos);
$archivo = "doctos_preimp_".$sesion;
file_put_contents("tmp/$archivo",$HTML);

$html2pdf = "htmldoc -t pdf --fontsize 11 --fontspacing 1.3 --no-strict --size 21.5x33cm --bodyfont helvetica "
		  . "--left 1cm --top 1cm --right 1cm --bottom 1cm --footer '   ' --header '   ' --no-embedfonts "
		  . "--compression=9 "
		  . "--webpage tmp/$archivo";
shell_exec($html2pdf);

header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=\"$archivo.pdf\"");
passthru($html2pdf);


unlink("tmp/$archivo");
unlink("tmp/$archivo.pdf");

echo(js("window.close()"));
echo(js("history.back()"));
echo(js("parent.jQuery.fancybox.close()"));

function generar_doctos($sesion,$fmt_contrato,$fmt_pagare) {
	global $REPRESENTANTE_LEGAL, $LF;
	
	$contratos_preimp = consulta_sql("SELECT id AS id_contrato FROM finanzas.contratos_preimp WHERE sesion='$sesion';");
	$HTML_docto = "";
	for ($x=0;$x<count($contratos_preimp);$x++) {
		extract($contratos_preimp[$x]);
		include($fmt_contrato);
		$HTML_docto .= $TEXTO . "<!-- PAGE BREAK -->";
		include($fmt_pagare);
		$HTML_docto .= $TEXTO . "<!-- PAGE BREAK --><!-- PAGE BREAK -->";

	}

	$HTML = "<html>".$LF
	      . "  <head>".$LF
		  . "    <title>UMC - SGU - Documentos de Matrícula Preimpresos</title>".$LF
		  . "    <style>".$LF
		  . "      td { font-size: 11px; font-family: sans,arial,helvetica; }".$LF
		  . "      @media print {".$LF
		  . "        @page {page-break-after: always; size: 21.5cm 25cm; }".$LF
		  . "        td { font-size: 11px; font-family: sans,arial,helvetica; }".$LF
		  . "      }".$LF
		  . "    </style>".$LF
		  . "  </head>".$LF
		  . "  <body background='../img/logo_umc_fondo_oficio.jpg'>".$LF
		  . "    <table border='0' width='100%'>"
		  . "      <tr>"
		  . "        <td align='justify'>"
		  .            $HTML_docto
		  . "        </td>"
		  . "      </tr>"
		  . "    </table>"
		  . "  </body>".$LF
		  . "</html>".$LF;

	return $HTML;
}
?>