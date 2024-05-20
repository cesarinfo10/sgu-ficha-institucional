<?php

session_start();
include("funciones.php");
//include("conversor_num2palabras.php");

if ($argv[1]=="") {
	$id_pagare_cred_interno = $_REQUEST['id_pagare_cred_interno'];
} elseif (is_numeric($argv[1])) {
	$id_pagare_cred_interno = $argv[1];
}

if (!$_SESSION['autentificado'] || !is_numeric($id_pagare_cred_interno)) {
	header("Location: index.php");
	exit;
}

$modulo = "pagare_cred_interno";
include("validar_modulo.php");

$SQL_fec_ven = "(p.fecha + '18 months'::interval)::date";

/*
$SQL_fec_ven = "CASE WHEN p.fecha < '2010-10-30' THEN (p.fecha + '12 months'::interval)::date
                     WHEN p.fecha < '2011-02-24' THEN (p.fecha + '18 months'::interval)::date
                     WHEN p.fecha >= '2011-02-24' THEN '2012-06-30'
                END";
*/
                
$SQL_pagare_cred_interno = "SELECT p.*,a.rf_nombre AS nombre_rf,a.rf_rut AS rut_rf,
                                   (a.rf_direccion||', '||a.rf_com||', '||a.rf_reg) AS domicilio_rf,
                                   $SQL_fec_ven AS fecha_venc,c.estado,
                            CASE WHEN c.id_alumno IS NOT NULL THEN al.nombre
                                 WHEN c.id_pap    IS NOT NULL THEN vp.nombre
                            END AS nombre_al,
                            CASE WHEN c.id_alumno IS NOT NULL THEN al.rut
                                 WHEN c.id_pap    IS NOT NULL THEN vp.rut
                            END AS rut_al,
                            CASE WHEN c.id_alumno IS NOT NULL THEN al.direccion||', '||al.comuna||', '||al.region
                                 WHEN c.id_pap    IS NOT NULL THEN vp.direccion||', '||vp.comuna||', '||vp.region
                            END AS domicilio_al
                     FROM finanzas.pagares_cred_interno AS p
                     LEFT JOIN finanzas.contratos AS c ON p.id_contrato=c.id
                     LEFT JOIN vista_avales AS a ON a.id=c.id_aval
                     LEFT JOIN vista_alumnos AS al ON al.id=c.id_alumno
                     LEFT JOIN vista_pap AS vp ON vp.id=c.id_pap
                     WHERE p.id='$id_pagare_cred_interno'";
$pagare_cred_interno = consulta_sql($SQL_pagare_cred_interno);

if (count($pagare_cred_interno) == 1) {

	extract($pagare_cred_interno[0]);

	$monto = number_format($monto,2,',','.');

	$fecha_venc = strftime("%e de %B de %Y",strtotime($fecha_venc));
		
	$fecha_pagare = strftime("%e de %B de %Y",strtotime($fecha));
	
	$imagen_fondo = "../img/logo_umc_fondo_oficio.jpg";
	if ($estado == '') {
		$imagen_fondo = "../img/logo_umc_fondo_oficio_nulo.jpg";
	}
	
	$HTML = ""; 
	include("pagare_cred_interno_formato.php");
	
	//echo($HTML);
	$HTML = iconv("UTF-8","ISO-8859-1",$HTML);
	$archivo = "pagare_cred_interno_".$id_pagare_cred_interno;
	$hand=fopen("tmp/$archivo","w");
	fwrite($hand,$HTML);
	fclose($hand);
	$html2pdf = "htmldoc -t pdf --fontsize 11 --fontspacing 1.5 --no-strict --size 21.5x33cm --bodyfont helvetica "
	          . "--left 2cm --top 2cm --right 2cm --bottom 2cm --footer '   ' --header '   ' --no-embedfonts "
	          . "--compression=9 --encryption --permissions print,no-copy,no-annotate,no-modify "
	          . "--webpage tmp/$archivo ";
	header("Content-Type: application/pdf");
	header("Content-Disposition: attachment; filename=$archivo.pdf");
	passthru($html2pdf);
	unlink("tmp/$archivo");
	echo(js("window.close();"));	
}
?>
