<?php
session_start();
include("funciones.php");
//include("conversor_num2palabras.php");

$modulo = "ccss_ver_pago_imprimir";
include("validar_modulo.php");
$id_pago = $_REQUEST['id'];

$script_name  = $_SERVER['SCRIPT_NAME'];
$enlbase = $script_name."?modulo";

$SQL_socios = "SELECT char_comma_sum(nombres||' '||apellidos||' (R.U.T.: '||rut||')') 
               FROM finanzas.ccss_socios 
               WHERE id IN (SELECT id_socio FROM finanzas.ccss_sociedades_socios WHERE id_sociedad=s.id)";

$SQL_pago = "SELECT p.id AS nro_comprobante,to_char(p.fecha,'DD \"de\" tmMonth \"de\" YYYY') AS fecha,u.nombre_usuario AS cajero,
                    s.razon_social AS sociedad,s.rut,($SQL_socios) AS socios,
                    efectivo,cheque,transferencia
             FROM finanzas.ccss_pagos              AS p
             LEFT JOIN vista_usuarios              AS u   ON u.id=id_cajero
             LEFT JOIN finanzas.ccss_pagos_detalle AS pd  ON pd.id_pago=p.id 
             LEFT JOIN finanzas.ccss_cobros        AS cob ON cob.id=id_cobro 
             LEFT JOIN finanzas.ccss_compromisos   AS c   ON c.id=cob.id_compromiso
             LEFT JOIN finanzas.ccss_sociedades    AS s   ON s.id=c.id_sociedad
             WHERE p.id=$id_pago";
$pago     = consulta_sql($SQL_pago);

if (count($pago) > 0) {
	extract($pago[0]);
	
	$socios = explode(",",$socios);
	$soc = "";
	for ($x=0;$x<count($socios);$x++) {
		$soc .= $socios[$x];
		if ($x+2 == count($socios)) { $soc .= " y "; } elseif ($x+2 < count($socios)) { $soc .= ", "; }
	}
	$socios = $soc;
	
	$efectivo      = number_format($pago[0]['efectivo'],0,",",".");
	$cheque        = number_format($pago[0]['cheque'],0,",",".");
	$transferencia = number_format($pago[0]['transferencia'],0,",",".");
	
	$monto_total = $pago[0]['cheque'] + $pago[0]['efectivo'] + $pago[0]['transferencia'];
	$monto_total = number_format($monto_total,0,",",".");
	
	$SQL_pago_detalle = "SELECT DISTINCT ON (com.ano) com.ano
	                     FROM finanzas.ccss_pagos_detalle    AS pd
	                     LEFT JOIN finanzas.ccss_cobros      AS c   ON c.id=pd.id_cobro
	                     LEFT JOIN finanzas.ccss_compromisos AS com ON com.id=c.id_compromiso
	                     WHERE pd.id_pago=$id_pago
	                     ORDER BY com.ano";
	$pago_detalle     = consulta_sql($SQL_pago_detalle);
	
	$ano = "";
	for ($x=0;$x<count($pago_detalle);$x++) {
		$ano .= $pago_detalle[$x]['ano'];
		if ($x+2 == count($pago_detalle)) { $ano .= " y "; } elseif ($x+2 < count($pago_detalle)) { $ano .= ", "; }
	}
	
	$HTML_medios_pago = "";
	if ($pago[0]['efectivo'] > 0)      { $HTML_medios_pago .= "Efectivo por $$efectivo<br>"; }
	if ($pago[0]['transferencia'] > 0) { $HTML_medios_pago .= "Transferencia Bancaria por $$transferencia<br>"; }
	
	if ($pago[0]['cheque'] > 0) {
		$HTML_medios_pago .= "Cheque(s) por $$cheque, según el siguiente detalle de documento(s):";
		$SQL_cheques = "SELECT if.nombre AS inst_finan,nro_cuenta,numero,monto,
		                       to_char(fecha_venc,'DD-tmMon-YYYY') AS fecha_venc,
		                       rut_emisor,nombre_emisor,telefono_emisor
						FROM finanzas.ccss_cheques AS ch
						LEFT JOIN finanzas.inst_financieras AS if ON if.codigo=ch.cod_inst_finan
						WHERE ch.id_pago = $id_pago
						ORDER BY ch.fecha_venc";
		$cheques     = consulta_sql($SQL_cheques);
		
		$HTML_cheques = "<table bgcolor='#ffffff' border='1' cellspacing='1' cellpadding='1'>\n"
		              . "  <tr>\n"
		              . "    <td align='center'><small><b>Fecha Venc.</b></small></td>\n"
		              . "    <td align='center'><small><b>Nº<br>Cuota</b></small></td>\n"
		              . "    <td align='center'><small><b>Monto</b></small></td>\n"
		              . "    <td align='center'><small><b>Inst. Financiera</b></small></td>\n"
		              . "    <td align='center'><small><b>Nº Cuenta</b></small></td>\n"
		              . "    <td align='center'><small><b>Nº Docto.</b></small></td>\n"
		              . "    <td align='center'><small><b>Emisor</b></small></td>\n"
		              . "  </tr>\n";
		              
		for($x=0;$x<count($cheques);$x++) {
			$monto = number_format($cheques[$x]['monto'],0,",",".");
			$nro_cuota = $x + 1;
			$HTML_cheques .=  "<tr>\n"
						  .   "  <td align='center'><small>{$cheques[$x]['fecha_venc']}</small></td>\n"
						  .   "  <td align='center'><small>$nro_cuota</small></td>\n"
						  .   "  <td align='right'><small>$$monto</small></td>\n"
						  .   "  <td align='center'><small>{$cheques[$x]['inst_finan']}</small></td>\n"
						  .   "  <td align='center'><small>{$cheques[$x]['nro_cuenta']}</small></td>\n"
						  .   "  <td align='center'><small>{$cheques[$x]['numero']}</small></td>\n"
						  .   "  <td><small>{$cheques[$x]['rut_emisor']}<br>{$cheques[$x]['nombre_emisor']}<br>{$cheques[$x]['telefono_emisor']}</small></td>\n"
						  .   "</tr>\n";
		}
		$HTML_medios_pago .= $HTML_cheques."</table>";
	}
	
	$fecha_hoy = strftime("%A %e de %B de %Y");

	include("fmt/ccss_recibo.php");

	$HTML = "<html>".$LF
		  . "  <head>".$LF
		  . "    <title>UMC - SGU - Recibo CCSS</title>".$LF
		  . "    <style>".$LF
		  . "      td { font-size: 10px; font-family: sans,arial,helvetica; }".$LF
		  . "      @media print {".$LF
		  . "        @page {page-break-after: always; size: 21.5cm 25cm; }".$LF
		  . "        td { font-size: 11px; font-family: sans,arial,helvetica; }".$LF
		  . "      }".$LF
		  . "    </style>".$LF
		  . "  </head>".$LF
		  . "  <body>".$LF
		  . $texto_docto.$LF
		  . "  </body>".$LF
		  . "</html>".$LF;
	$HTML = iconv("UTF-8","ISO-8859-1//TRANSLIT",$HTML);
	$archivo = "recibo_ccss_".$id_pago;
	file_put_contents("tmp/$archivo",$HTML);
	$html2pdf = "htmldoc -t pdf --fontsize 11 --fontspacing 1.5 --no-strict --size 21.5x27.94cm --bodyfont helvetica "
			  . "--left 2cm --top 1cm --right 2cm --bottom 1cm --footer '   ' --header '   ' --no-embedfonts "
			  . "--compression=9 --encryption --permissions print,no-copy,no-annotate,no-modify "
			  . "--webpage tmp/$archivo ";
	header("Content-Type: application/pdf");
	header("Content-Disposition: attachment; filename=$archivo.pdf");
	passthru($html2pdf);
	unlink("tmp/$archivo");
}
echo(js("parent.jQuery.fancybox.close()"));
echo(js("window.close()"));

?>
