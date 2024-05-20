<?php

include("../../funciones.php");
include("../../conversor_num2palabras.php");

$id_pagare_colegiatura = "1113,1117,1118,1119,1120,1121,1122,1123,1125,1126,1132,1138,1139,1141,1145,1152,1153,1154,1159,1160,1161,1162,1163,1164,1165,1175,1181,1183,1194,1201,1211,1215,1217,1218,1219,1223,1225,1227,1228,1234,1238,1254,1266,1267,1269,1273,1276,1277,1280,1283,1284,1288,1291,1293,1294,1298,1299,1302,1306,1309,1311,1313,1318,1320,1321,1322,1324,1326,1327,1328,1329,1330,1332,1333,1334,1335,1349,1353,1355,1357,1358,1360,1362,1363,1364,1369,1372,1379,1381,1382,1384,1385,1388,1392,1393,1395,1398,1402,1403,1404,1408,1410,1411,1412,1413,1414,1415,1416,1417,1419,1420,1421,1430,1431,1433,1434";

$SQL_pagare_coleg = "SELECT p.id AS id_pagare_colegiatura,p.*,p.fecha::date,a.*,c.estado 
                     FROM finanzas.pagares_colegiatura AS p
                     LEFT JOIN finanzas.contratos AS c ON p.id_contrato=c.id
                     LEFT JOIN vista_avales AS a ON a.id=c.id_aval
                     WHERE p.id IN ($id_pagare_colegiatura)";
$pagare_coleg     = consulta_sql($SQL_pagare_coleg);

for ($x=0;$x<count($pagare_coleg);$x++) {

	extract($pagare_coleg[$x]);

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

	$imagen_fondo = "../../img/logo_umc_fondo_oficio.jpg";
	if ($estado == '') {
		$imagen_fondo = "../../img/logo_umc_fondo_oficio_nulo.jpg";
	}
	
	$HTML = ""; 
	include("pagare_colegiatura_formato.php");
	
	//echo($HTML);
	$HTML = iconv("UTF-8","ISO-8859-1",$HTML);
	$archivo = "pagare_colegiatura_".$id_pagare_colegiatura;
	$hand=fopen($archivo,"w");
	fwrite($hand,$HTML);
	fclose($hand);
	$html2pdf = "htmldoc -t pdf --fontsize 11 --fontspacing 1.2 --no-strict --size 21.5x33cm --bodyfont helvetica "
	          . "--left 1.5cm --top 1cm --right 1.5cm --bottom 1cm --footer '   ' --header '   ' --no-embedfonts "
	          . "--compression=9 --encryption --permissions print,no-copy,no-annotate,no-modify "
	          . "--webpage -f $archivo.pdf $archivo ";
	exec($html2pdf);
	unlink($archivo);
}
?>
