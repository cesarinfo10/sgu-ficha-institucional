<?php

session_start();
include("funciones.php");
include("conversor_num2palabras.php");

$id_convenio_ci = $_REQUEST['id_convenio_ci'];
$fmt = $_REQUEST['tipo'];

if (!$_SESSION['autentificado'] || !is_numeric($id_contrato) || empty($fmt)) {
	header("Location: index.php");
	exit;
}

$modulo = "contrato";
include("validar_modulo.php");

$SQL_contrato = "SELECT c.id AS id_contrato,
                        CASE WHEN c.id_pap IS NOT NULL THEN vp.nombre WHEN c.id_alumno IS NOT NULL THEN val.nombre END AS nombre_al,
                        CASE WHEN c.id_pap IS NOT NULL THEN upper(vp.nacionalidad) WHEN c.id_alumno IS NOT NULL THEN upper(val.nacionalidad) END AS nacionalidad_al,
                        CASE WHEN c.id_pap IS NOT NULL THEN trim(vp.rut) WHEN c.id_alumno IS NOT NULL THEN trim(val.rut) END AS rut_al,
                        pap.profesion AS profesion_al,c.nivel,c.estado,
                        CASE pap.est_civil WHEN 'S' THEN 'Soltero(a)' WHEN 'C' THEN 'Casado(a)' 
                                           WHEN 'D' THEN 'Divorsiado(a)' WHEN 'V' THEN 'Viudo(a)' END AS est_civil_al,
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
                        trim(va.rf_telefono) AS telefono_rf,trim(va.rf_tel_movil) AS tel_movil_rf,
                        c.cod_beca_mat,c.monto_beca_mat,c.porc_beca_mat,
                        c.monto_matricula,c.monto_arancel,c.id_convenio,c.id_beca_arancel,c.porc_beca_arancel,
                        c.monto_beca_arancel,c.arancel_efectivo,c.arancel_cred_interno,c.arancel_cant_cheques,
                        c.arancel_pagare_coleg,c.arancel_cuotas_pagare_coleg,c.arancel_diap_pagare_coleg,
                        c.arancel_mes_ini_pagare_coleg,c.arancel_ano_ini_pagare_coleg,c.arancel_cheque,c.arancel_cant_cheques,c.arancel_diap_cheque,
                        c.arancel_mes_ini_cheque,c.arancel_ano_ini_cheque,c.arancel_tarjeta_credito,c.fecha::date AS fecha,c.ano,
                        c.tipo AS tipo_contrato,pc.id AS nro_pagare
                 FROM finanzas.contratos AS c
                 LEFT JOIN vista_pap                    AS vp  ON vp.id=c.id_pap
                 LEFT JOIN vista_avales                 AS va  ON va.id=c.id_aval
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
	
	if (!empty($cod_beca_mat) && is_numeric($porc_beca_mat)) {
		$monto_beca_matricula = $monto_matricula * ($porc_beca_mat/100);
	} elseif (!empty($cod_beca_mat) && is_numeric($monto_beca_mat)) {
		$monto_beca_matricula = $monto_beca_mat;
	}

	if (is_numeric($id_convenio)) {
		$monto_convenio = $monto_arancel * .2;
	}
	
	if (is_numeric($id_beca_arancel) && is_numeric($porc_beca_arancel)) {
		$monto_beca_arancel = $monto_arancel * ($porc_beca_arancel/100);
	}
	
	if ($arancel_cuotas_pagare_coleg > 0) {
		
		if ($arancel_ano_ini_pagare_coleg == "") { $arancel_ano_ini_pagare_coleg = $ano; }
		
		$cant_cuotas = "$arancel_cuotas_pagare_coleg cuotas del Pagaré Nº $nro_pagare";
		$mes         = $arancel_mes_ini_pagare_coleg;
		$diap        = $arancel_diap_pagare_coleg;
		$valor_cuota = $arancel_pagare_coleg/$cant_cuotas;
		$monto_total = $arancel_pagare_coleg;
		$ano         = $arancel_ano_ini_pagare_coleg;
	} elseif ($arancel_cant_cheques > 0) {
		$cant_cuotas = "$arancel_cant_cheques cheques";
		$mes         = $arancel_mes_ini_cheque;
		$diap        = $arancel_diap_cheque;
		$valor_cuota = $arancel_cheque/$cant_cuotas;
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
		$tabla_pagare_cheques = "y sin que ello constituya novación en $cant_cuotas con los siguientes vencimientos y valores"
		                      . "<br><br>"
		                      . "<table align='center'>";
		$diap_aux = $diap;
		for($x=1;$x<=$cant_cuotas;$x++) {
			$diap = $diap_aux;
			if ($mes > 12) {
				$mes=1;
				$ano++;
			}
			if ($mes == 2 && $diap > 28) {
				$diap = 28;
			}
			
			if ($x == $cant_cuotas) {
				$valor_cuota = $monto_total - ($valor_cuota * ($x-1));
			}

			$valor_cuota_f = number_format($valor_cuota,0,',','.');

			$tabla_pagare_cheques .= "<tr>"
			                      .  "  <td>$diap de {$meses_palabra[$mes-1]['nombre']} de $ano</td>"
			                      .  "  <td>por la suma de \$ $valor_cuota_f</td>"
			                      .  "</tr>";
			$mes++;
		}
		$tabla_pagare_cheques .= "</table><br>";
	}

	$arancel_efectivo += $arancel_tarjeta_credito;
	
	$arancel_saldo = $monto_arancel - $monto_convenio - $monto_beca_arancel - $arancel_efectivo - $arancel_cred_interno; 
	
	$monto_matricula_palabras = num2palabras($monto_matricula);
	$monto_arancel_palabras   = num2palabras($monto_arancel);
	
	$monto_matricula      = number_format($monto_matricula,0,',','.');
	$monto_arancel        = number_format($monto_arancel,0,',','.');
	$monto_convenio       = number_format($monto_convenio,0,',','.');
	
	$monto_beca_arancel_umc = $monto_beca_arancel_excelencia = 0;
	if ($id_beca_arancel >= 7) { 
		$monto_beca_arancel_umc        = number_format($monto_beca_arancel,0,',','.');
	} else {
		$monto_beca_arancel_excelencia = number_format($monto_beca_arancel,0,',','.');
	}
	
	$arancel_efectivo     = number_format($arancel_efectivo,0,',','.');
	$arancel_cred_interno = number_format($arancel_cred_interno,0,',','.');
	$arancel_saldo        = number_format($arancel_saldo,0,',','.');
	
	$fecha_contrato = strftime("%e días del mes de %B del año %Y",strtotime($fecha));
	
	$nivel_al = $NIVELES[$nivel-1]['nombre'];
	
	$imagen_fondo = "img/logo_umc_fondo_oficio.jpg";
	if ($estado == '') {
		$imagen_fondo = "img/logo_umc_fondo_oficio_nulo.jpg";
	}

	$HTML = "";
	 
	include("contrato_".$fmt."_formato.php");
	
	//echo($HTML);
	$HTML = iconv("UTF-8","ISO-8859-1",$HTML);
	$archivo = "contrato_".$fmt."_".$id_contrato;
	$hand=fopen($archivo,"w");
	fwrite($hand,$HTML);
	fclose($hand);
	$html2pdf = "htmldoc -t pdf --fontsize 11 --fontspacing 1.2 --no-strict --size 21.5x33cm --bodyfont helvetica "
	          . "--left 1.5cm --top 1cm --right 1.5cm --bottom 1cm --footer '  /' --header '   ' --no-embedfonts "
	          . "--compression=9 --encryption --permissions print,no-copy,no-annotate,no-modify "
	          . "--webpage $archivo ";
	header("Content-Type: application/pdf");
	header("Content-Disposition: attachment; filename=$archivo.pdf");
	passthru($html2pdf);
	unlink($archivo);
	echo(js("window.close();"));	
}
?>
