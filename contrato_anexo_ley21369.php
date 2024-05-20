<?php

session_start();
include("funciones.php");
//include("conversor_num2palabras.php");

$id_contrato = $_REQUEST['id_contrato'];
//$fmt = $_REQUEST['tipo'];

if (!$_SESSION['autentificado'] || !is_numeric($id_contrato)) {
	header("Location: index.php");
	exit;
}

//$modulo = "contrato";
//include("validar_modulo.php");

$SQL_contrato = "SELECT c.id AS id_contrato,
                        CASE WHEN c.id_pap IS NOT NULL THEN vp.nombre WHEN c.id_alumno IS NOT NULL THEN val.nombre END AS nombre_al,
                        CASE WHEN c.id_pap IS NOT NULL THEN upper(vp.nacionalidad) WHEN c.id_alumno IS NOT NULL THEN upper(val.nacionalidad) END AS nacionalidad_al,
                        CASE WHEN c.id_pap IS NOT NULL THEN trim(vp.rut) WHEN c.id_alumno IS NOT NULL THEN trim(val.rut) END AS rut_al,
                        pap.profesion AS profesion_al,c.nivel,c.estado,coalesce(al.email,pap.email) AS email_al,
                        CASE pap.est_civil WHEN 'S' THEN 'Soltero(a)' WHEN 'C' THEN 'Casado(a)' 
                                           WHEN 'D' THEN 'Divorciado(a)' WHEN 'V' THEN 'Viudo(a)' END AS est_civil_al,
                        CASE WHEN c.id_pap IS NOT NULL
                             THEN vp.direccion||', '||vp.comuna||', '||vp.region
                             WHEN c.id_alumno IS NOT NULL
                             THEN val.direccion||', '||val.comuna||', '||val.region END AS domicilio_al,
                        CASE WHEN c.id_pap IS NOT NULL THEN trim(vp.telefono) WHEN c.id_alumno IS NOT NULL THEN trim(val.telefono) END AS telefono_al,
                        CASE WHEN c.id_pap IS NOT NULL THEN trim(vp.tel_movil) WHEN c.id_alumno IS NOT NULL THEN trim(val.tel_movil) END AS tel_movil_al,
                        ca.nombre AS carrera_al,
                        CASE c.jornada WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada_al,
                        upper(va.rf_nombre) AS nombre_rf,upper(va.rf_nac) AS nacionalidad_rf,a.rf_profesion AS profesion_rf,
                        CASE a.rf_est_civil WHEN 'S' THEN 'Soltero(a)' WHEN 'C' THEN 'Casado(a)' 
                                         WHEN 'D' THEN 'Divorsiado(a)' WHEN 'V' THEN 'Viudo(a)' END AS est_civil_rf,
                        trim(va.rf_rut) AS rut_rf,va.rf_direccion||', '||va.rf_com||', '||va.rf_reg AS domicilio_rf,
                        trim(va.rf_telefono) AS telefono_rf,trim(va.rf_tel_movil) AS tel_movil_rf,va.rf_email AS email_rf,
                        c.cod_beca_mat,c.monto_beca_mat,c.porc_beca_mat,
                        c.monto_matricula,c.monto_arancel,c.id_convenio,c.id_beca_arancel,c.porc_beca_arancel,con.nombre AS nombre_convenio,
                        c.monto_beca_arancel,c.arancel_efectivo,c.arancel_cred_interno,c.arancel_cant_cheques,
                        c.arancel_pagare_coleg,c.arancel_cuotas_pagare_coleg,c.arancel_diap_pagare_coleg,
                        c.arancel_mes_ini_pagare_coleg,c.arancel_ano_ini_pagare_coleg,c.arancel_cheque,c.arancel_cant_cheques,c.arancel_diap_cheque,
                        c.arancel_mes_ini_cheque,c.arancel_ano_ini_cheque,c.arancel_tarjeta_credito,c.fecha::date AS fecha,c.ano,c.ano as ano_contrato,c.semestre,
                        c.tipo AS tipo_contrato,pc.id AS nro_pagare
                 FROM finanzas.contratos AS c
                 LEFT JOIN vista_pap                    AS vp  ON vp.id=c.id_pap
                 LEFT JOIN vista_avales                 AS va  ON va.id=c.id_aval
                 LEFT JOIN convenios					AS con ON con.id=c.id_convenio
                 LEFT JOIN carreras                     AS ca  ON ca.id=c.id_carrera
                 LEFT JOIN pap                                 ON pap.id=c.id_pap
                 LEFT JOIN alumnos                      AS al  ON al.id=c.id_alumno
                 LEFT JOIN vista_alumnos                AS val ON val.id=c.id_alumno
                 LEFT JOIN avales                       AS a   ON a.id=va.id
                 LEFT JOIN finanzas.pagares_colegiatura AS pc  ON pc.id_contrato=c.id
                 WHERE c.id='$id_contrato'";
$contrato = consulta_sql($SQL_contrato);

if (count($contrato) == 1) {

	extract($contrato[0]);
	
	$HTML_mat = "<table cellpadding='0' cellspacing='0' border='0' align='center'>";
	
	if (!empty($cod_beca_mat) && is_numeric($porc_beca_mat)) {
		$monto_beca_matricula = round($monto_matricula * ($porc_beca_mat/100),0);
	} elseif (!empty($cod_beca_mat) && is_numeric($monto_beca_mat)) {
		$monto_beca_matricula = $monto_beca_mat;
	}
	
	if ($monto_beca_matricula > 0) {
		$HTML_mat .= "  <tr>"
                  .  "    <td nowrap>&raquo; Con el otorgamiento de una beca de Matrícula por la suma de</td>"
                  .  "    <td align='right'>\$".number_format($monto_beca_matricula,0,",",".")."</td>"
                  .  "  </tr>";
    }
    
	$HTML_mat .= "  <tr>"
	          .  "    <td nowrap>&raquo; Al contado, en este acto, con la suma de</td>"
	          .  "    <td align='right'>\$".number_format($monto_matricula - $monto_beca_matricula,0,",",".")."</td>"
	          .  "  </tr>"
	          .  "</table>";

	if (is_numeric($id_convenio)) {
		$monto_convenio = round($monto_arancel * .2,0);
	}	
	
	if (is_numeric($id_beca_arancel) && is_numeric($porc_beca_arancel)) {
		$monto_beca_arancel = round($monto_arancel * ($porc_beca_arancel/100),0);
	}
	
	if ($arancel_cuotas_pagare_coleg > 0) {
		
		if ($arancel_ano_ini_pagare_coleg == "") { $arancel_ano_ini_pagare_coleg = $ano; }
		
		$cant_cuotas = "$arancel_cuotas_pagare_coleg cuotas del Pagaré Nº $nro_pagare";
		$mes         = $arancel_mes_ini_pagare_coleg;
		$diap        = $arancel_diap_pagare_coleg;
		$valor_cuota = floor($arancel_pagare_coleg/$cant_cuotas);
		$monto_total = $arancel_pagare_coleg;
		$ano         = $arancel_ano_ini_pagare_coleg;
	} elseif ($arancel_cant_cheques > 0) {
		$cant_cuotas = "$arancel_cant_cheques cheques";
		$mes         = $arancel_mes_ini_cheque;
		$diap        = $arancel_diap_cheque;
		$valor_cuota = floor($arancel_cheque/$cant_cuotas);
		$monto_total = $arancel_cheque;
		$ano         = $arancel_ano_ini_cheque;
	}

	$tabla_pagare_cheques = "";
	
	if ($monto_beca_matricula > 0) {
		$monto_beca_matricula_palabras = num2palabras($monto_beca_matricula);
		$monto_beca_matricula          = number_format($monto_beca_matricula,0,',','.');
		
		if (strtotime($fecha) <= strtotime("2010-12-31")) {
			$fecha_descuento     = "31 de diciembre de 2010";
		} else {
			$fecha_descuento     = strftime("%e de %B de %Y",strtotime($fecha));
		} 
		$descuento_matricula = "Los alumnos nuevos que se matriculen antes del $fecha_descuento recibirán una beca "
		                     . "de $ $monto_beca_matricula ($monto_beca_matricula_palabras pesos) cantidad que se imputará "
		                     . "inmediatamente al valor de la matrícula, referido más arriba. ";
	}
	
	if ($arancel_cuotas_pagare_coleg > 0 || $arancel_cant_cheques > 0) {
		$tabla_pagare_cheques = "sin que ello constituya novación en $cant_cuotas con los siguientes vencimientos y valores"
		                      . "<br><br>"
		                      . "<table align='center' width='100%'><tr><td align='right' valign='top' nowrap>";
		$diap_aux = $diap;
		for($x=1;$x<=$cant_cuotas;$x++) {
			$diap = $diap_aux;
			if ($mes > 12) { $mes=1; $ano++; }
			
			if ($mes == 2 && $diap > 28) { $diap = 28; }
			
			if ($x == $cant_cuotas) { $valor_cuota = $monto_total - ($valor_cuota * ($x-1)); }

			$valor_cuota_f = number_format($valor_cuota,0,',','.');

			$tabla_pagare_cheques .= "$diap de {$meses_palabra[$mes-1]['nombre']} de $ano, por la suma de \$ $valor_cuota_f<br>";
			$mes++;
			if ($x == round($cant_cuotas/2,0)) { $tabla_pagare_cheques .= "</td><td align='right' valign='top' nowrap>"; }
		}
		$tabla_pagare_cheques .= "</td></tr></table><br>";
	}

	$arancel_efectivo += $arancel_tarjeta_credito;
	
	$arancel_saldo = $monto_arancel - $monto_convenio - $monto_beca_arancel - $arancel_efectivo - $arancel_cred_interno; 
	
	$monto_matricula_palabras = num2palabras($monto_matricula);
	$monto_arancel_palabras   = num2palabras($monto_arancel);
	
	if ($id_convenio > 0) { $monto_beca_convenio = round($monto_arancel*0.2,0); }
	
	$monto_matricula      = number_format($monto_matricula,0,',','.');
	$monto_arancel        = number_format($monto_arancel,0,',','.');
	$monto_convenio       = number_format($monto_convenio,0,',','.');
	
	$monto_beca_arancel_umc = $monto_beca_arancel_excelencia = 0;
	$HTML_beca_especial_1er_ano_2016 = "";

	$HTML_arancel = "<table cellpadding='0' cellspacing='0' border='0' align='center'>";
	
	switch ($id_beca_arancel) {
		case 7:
			$monto_beca_arancel_umc        = number_format($monto_beca_arancel,0,',','.');
			$HTML_arancel .= "  <tr>"
			              .  "    <td nowrap>&raquo; Con el otorgamiento de la beca UMC por la suma de</td>"
			              .  "    <td align='right'>\$ $monto_beca_arancel_umc</td>"
			              .  "  </tr>";
			break;
		case 1:
		case 6:
			$monto_beca_arancel_excelencia = number_format($monto_beca_arancel,0,',','.');
			$HTML_arancel .= "  <tr>"
			              .  "    <td nowrap>&raquo; Con el otorgamiento de la beca de Excelencia por la suma de</td>"
			              .  "    <td align='right'>\$ $monto_beca_arancel_excelencia</td>"
			              .  "  </tr>";
			break;
		case 5:
		case 10:
			$monto_convenio                = number_format($monto_beca_arancel,0,',','.');
			$HTML_arancel .= "  <tr>"
		                  .  "    <td nowrap>&raquo; Con el otorgamiento de la beca de Procedencia por la suma de</td>"
		                  .  "    <td align='right'>\$ $monto_convenio</td>"
		                  .  "  </tr>";
		    break;
		case 11:
		case 12:
		case 13:
		case 14:
		case 15:
			$monto_beca_especial =  number_format($monto_beca_arancel,0,',','.');
			$HTML_arancel .= "  <tr>"
			              .  "    <td nowrap>&raquo; Con el otorgamiento de la beca Especial por la suma de</td>"
			              .  "    <td align='right'>\$ $monto_beca_especial</td>"
			              .  "  </tr>";
			break;
	}
	
	if ($id_convenio > 0) { 
		$monto_beca_convenio =  number_format($monto_beca_convenio,0,',','.');
		$HTML_arancel .= "  <tr>"
//		              .  "    <td>&raquo; Con el otorgamiento de la beca convenio:<br>&nbsp;&nbsp; \"$nombre_convenio\" por la suma de</td>"
		              .  "    <td>&raquo; Con el otorgamiento de la beca de Procedencia por la suma de</td>"
		              .  "    <td align='right' valign='bottom'>\$ $monto_beca_convenio</td>"
		              .  "  </tr>";		
	}

	if ($arancel_cred_interno > 0) {
		$arancel_cred_interno = number_format($arancel_cred_interno,0,',','.');
		$HTML_arancel .= "  <tr>"
		              .  "    <td nowrap>&raquo; Crédito Solidario UMC</td>"
		              .  "    <td align='right'>\$ $arancel_cred_interno</td>"
		              .  "  </tr>";
	}
	
	$HTML_arancel .= "  <tr>"
	              .  "    <td nowrap>&raquo; Al contado, en este acto, la suma de</td>"
	              .  "    <td align='right'>\$ $arancel_efectivo</td>"
	              .  "  </tr>"
	              .  "</table>";
	              
	$arancel_efectivo     = number_format($arancel_efectivo,0,',','.');
	
	if ($arancel_saldo > 0) {
		$arancel_saldo        = number_format($arancel_saldo,0,',','.');
		$tabla_pagare_cheques = "y el saldo de $ $arancel_saldo se paga " . $tabla_pagare_cheques;
	}

    $fecha="2022-09-26";
	
	$fecha_contrato = strftime("%e días del mes de %B del año %Y",strtotime($fecha));
	
	$nivel_al = $NIVELES[$nivel-1]['nombre'];
	
	$imagen_fondo = "../img/logo_umc_fondo_oficio.jpg";
	if ($estado == '') {
		$imagen_fondo = "../img/logo_umc_fondo_oficio_nulo.jpg";
	}

	$HTML = "";
	 
	include("contrato_anexo_ley21369_formato.php");
//	include("fmt/contratos/contrato_$fmt.php");
	
	//echo($HTML);
	
	$HTML = iconv("UTF-8","ISO-8859-1//TRANSLIT",$HTML);
	$archivo = "contrato_anexo_ley21369_$id_contrato";
	$hand=fopen("tmp/$archivo","w");
	fwrite($hand,$HTML);
	fclose($hand);
	$html2pdf = "htmldoc -t pdf --fontsize 11 --fontspacing 1.2 --no-strict --size 21.5x33cm --bodyfont Arial "
	          . "--left 1.5cm --top 1cm --right 1.5cm --bottom 1cm --footer '  /' --header '   ' --no-embedfonts "
	          . "--compression=9 --encryption --permissions print,no-copy,no-annotate,no-modify "
	          . "--webpage tmp/$archivo ";
	header("Content-Type: application/pdf");
	header("Content-Disposition: attachment; filename=$archivo.pdf");
	passthru($html2pdf);
	unlink("tmp/$archivo");
	echo(js("window.close();"));	
}
?>
