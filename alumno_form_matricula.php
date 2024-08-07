<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

//$REAJUSTE_ARANCEL = "1.07";
$MONTO_MATRICULA = 198000;

include("validar_modulo.php");

$id_alumno          = $_REQUEST['id_alumno'];
$tipo_contrato      = $_REQUEST['tipo'];
$ano_contrato       = $_REQUEST['ano'];
$semestre_contrato  = $_REQUEST['semestre'];

/*
 * Los valores de año y semestre del contrato deben venir definidos el módulo precendente (alumno_matricular)
if (empty($ano_contrato))      { $ano_contrato      = $ANO; }
if (empty($semestre_contrato)) { $semestre_contrato = $SEMESTRE; }
*/

if ($tipo_contrato == "Anual") { $semestre_contrato = null; }

if (!is_numeric($id_alumno)) {
	echo(js("location.href='$enlbase=gestion_alumnos';"));
	exit;
}

/*
$SQL_contrato = "SELECT id FROM finanzas.contratos 
                 WHERE id_alumno=$id_alumno AND ano=$ano_contrato AND semestre=$semestre_contrato
                   AND estado IN ('E','F') AND trim(tipo)='$tipo_contrato'";
$contrato = consulta_sql($SQL_contrato);
if (count($contrato) > 0) {
	echo(msje_js("Este alumno ya tiene un contrato Anual emitido para este periodo. "
	            ."Revise los documentos existentes en el módulo <Documentos de Matrícula>.\\n\\n"));
	echo(js("location.href='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
	exit;
}
*/

$SQL_alumno = "SELECT va.id,va.rut,va.nombre,va.direccion,va.comuna,va.region,
                      al.genero AS cod_genero,al.nacionalidad AS cod_nac,al.comuna AS cod_comuna,
                      al.region AS cod_region,al.email,al.pasaporte,al.telefono,al.tel_movil,al.id_aval,
                      c.nombre AS carrera,al.carrera_actual AS id_carrera,al.jornada AS id_jornada,
                      CASE al.jornada WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada,c.regimen,
                      al.admision,al.cohorte,al.cohorte_reinc,pap.arancel_promo
               FROM alumnos AS al			   
               LEFT JOIN vista_alumnos AS va USING (id)
			   LEFT JOIN pap ON pap.id=al.id_pap
               LEFT JOIN carreras AS c ON c.id=carrera_actual
               WHERE va.id=$id_alumno;";
$alumno = consulta_sql($SQL_alumno);
if (count($alumno) > 0) {	
	$id_aval  = $alumno[0]['id_aval'];
	$SQL_aval = "SELECT id,rf_rut,rf_nombre,rf_direccion,rf_com,rf_reg
	             FROM vista_avales WHERE id=$id_aval;";
	$aval     = consulta_sql($SQL_aval);
} else {
	echo(js("location.href='$enlbase=gestion_alumnos';"));
	exit;
}

$id_carrera = $alumno[0]['id_carrera'];
$id_jornada = $alumno[0]['id_jornada'];

//if ($alumno[0]['regimen'] == "POST-GD") { $max_cuotas = 30; $MONTO_MATRICULA = 0; $REAJUSTE_ARANCEL = 1; }
//if ($alumno[0]['regimen'] == "POST-TD") { $max_cuotas = 12; $MONTO_MATRICULA = 0; $REAJUSTE_ARANCEL = 1; }

/*	
$SQL_aranceles = "SELECT CASE WHEN congelado THEN arancel      ELSE round(arancel*$REAJUSTE_ARANCEL)      END AS monto_arancel,
                         CASE WHEN congelado THEN arancel      ELSE round(arancel*$REAJUSTE_ARANCEL)      END AS monto_arancel_credito,
                         CASE WHEN congelado THEN beca         ELSE round(beca*$REAJUSTE_ARANCEL)         END AS beca,
                         CASE WHEN congelado THEN cred_interno ELSE round(cred_interno*$REAJUSTE_ARANCEL) END AS cred_interno,
                         $MONTO_MATRICULA AS monto_matricula,11 AS cuotas,ci.monto AS monto_cred_int,financiamiento 
                  FROM finanzas.contratos_al_2017
                  LEFT JOIN finanzas.cred_int_al_2017 AS ci USING (id_alumno) 
                  WHERE id_alumno=$id_alumno;";
*/
/*
$SQL_aranceles = "SELECT round(arancel*$REAJUSTE_ARANCEL) AS monto_arancel,
                         round(arancel*$REAJUSTE_ARANCEL) AS monto_arancel_credito,
                         $MONTO_MATRICULA AS monto_matricula,11 AS cuotas,financiamiento 
                  FROM finanzas.contratos_al_2017
                  WHERE id_alumno=$id_alumno;";
*/
//echo($SQL_aranceles);
$aranceles = consulta_sql($SQL_aranceles);
//var_dump($aranceles);	

$SQL_ult_pci = "SELECT monto,arancel_cred_interno 
                FROM vista_contratos_rut AS vcr 
                LEFT JOIN finanzas.contratos AS c USING (id) 
                LEFT JOIN finanzas.pagares_cred_interno AS pci ON pci.id_contrato=c.id 
                WHERE vcr.rut = '{$alumno[0]['rut']}' AND c.estado IS NOT NULL AND pci.id IS NOT NULL AND NOT c.ci_liquidado 
                ORDER BY pci.fecha DESC 
                LIMIT 1";
echo("<!-- $SQL_ult_pci -->");
$ult_pci = consulta_sql($SQL_ult_pci);
$monto_acum_ci = $ult_pci[0]['monto'];
$monto_ult_ci = $ult_pci[0]['arancel_cred_interno'];

if (count($aranceles) == 0) {
	
	$cohorte_alumno = $alumno[0]['cohorte'];
	$cohorte_reinc  = $alumno[0]['cohorte_reinc'];
	
	$SQL_reajuste  = "SELECT coalesce(mul(factor::numeric),1) FROM finanzas.reajuste_aranceles WHERE ano BETWEEN $cohorte_alumno+1 AND $ano_contrato";

	//if ($alumno[0]['regimen'] == "POST-GD" || $alumno[0]['regimen'] == "POST-TD") { $SQL_reajuste  = "SELECT 1"; }
	
	$ano_arancel = 0;
	if ($cohorte_alumno < 2010) { $ano_arancel = 2010; } else { $ano_arancel = $cohorte_alumno; }
/*
	$SQL_aranceles = "SELECT carrera,jornada,$MONTO_MATRICULA AS monto_matricula,round(monto_arancel*($SQL_reajuste)) AS monto_arancel,
	                         round(monto_arancel_credito*($SQL_reajuste)) AS monto_arancel_credito,cuotas 
	                  FROM vista_aranceles 
	                  WHERE id_carrera=$id_carrera AND id_jornada='$id_jornada' 
	                    AND ano=$ano_arancel;";
*/

	$sql_monto_arancel         = ($alumno[0]['arancel_promo'] == 'f') ? "monto_arancel" : "LEAST(monto_arancel_especial,monto_arancel_promo)";
	$sql_monto_arancel_credito = ($alumno[0]['arancel_promo'] == 'f') ? "monto_arancel_credito" : "LEAST(monto_arancel_credito_especial,monto_arancel_promo_credito)";

	$SQL_aranceles = "SELECT carrera,jornada,$MONTO_MATRICULA AS monto_matricula,
	                         round($sql_monto_arancel*($SQL_reajuste)) AS monto_arancel,
	                         round($sql_monto_arancel_credito*($SQL_reajuste)) AS monto_arancel_credito,
							 cuotas 
	                  FROM vista_aranceles 
	                  WHERE id_carrera=$id_carrera AND id_jornada='$id_jornada' 
	                    AND ano=$ano_arancel;";
	//echo($SQL_aranceles);
	$aranceles = consulta_sql($SQL_aranceles);
	if (count($aranceles) == 0) {
		if ($cohorte_reinc > 0) { 
			$ano_arancel = $cohorte_reinc;
			$SQL_reajuste  = "SELECT coalesce(mul(factor::numeric),1) FROM finanzas.reajuste_aranceles WHERE ano BETWEEN $cohorte_reinc+1 AND $ano_contrato";

			$SQL_aranceles = "SELECT carrera,jornada,$MONTO_MATRICULA AS monto_matricula,round(monto_arancel*($SQL_reajuste)) AS monto_arancel,
			                         round(monto_arancel_credito*($SQL_reajuste)) AS monto_arancel_credito,cuotas 
			                  FROM vista_aranceles 
			                  WHERE id_carrera=$id_carrera AND id_jornada='$id_jornada' 
			                    AND ano=$ano_arancel;";
			//echo($SQL_aranceles);
			$aranceles = consulta_sql($SQL_aranceles);
		} else {
			echo(msje_js("ERROR: No ha sido posible definir el arancel a aplicar. Comuníquese con el Departamento de Informática."));
			echo(js("location.href='$enlbase=gestion_alumnos';"));
			exit;
		}
	} 
} else {
	if (trim($aranceles[0]['financiamiento']) == "CREDITO") {
		$aranceles[0]['monto_arancel'] = round($aranceles[0]['monto_arancel']/1.10);
	} elseif (trim($aranceles[0]['financiamiento']) == "CONTADO") {
		$aranceles[0]['monto_arancel_credito'] = round($aranceles[0]['monto_arancel_credito']*1.10);
	}
	if (empty($_REQUEST['guardar']) && empty($_REQUEST['arancel_cred_interno'])) { 
		$_REQUEST['arancel_cred_interno'] = $aranceles[0]['cred_interno'];
	}
}

$becaumc = consulta_sql("SELECT beca_otorgada FROM dae.fuas WHERE ano=$ANO_MATRICULA AND id_alumno=$id_alumno AND estado='Validado'");
if (count($becaumc) == 1) { 
	if ($_REQUEST['id_beca_arancel'] == "" && ($_REQUEST['porc_beca_arancel'] == "" || $_REQUEST['monto_beca_arancel'] = "")) {
		echo(msje_js("ATENCIÓN: Este alumno tiene una Postulación a Beca UMC aprobada por un ".$becaumc[0]['beca_otorgada']."%"));
		$_REQUEST['id_beca_arancel'] = 7; 
		$_REQUEST['porc_beca_arancel'] = $becaumc[0]['beca_otorgada'];
	}
}

if ($tipo_contrato == "Semestral") {
	$aranceles[0]['monto_arancel']         = round($aranceles[0]['monto_arancel']/2,0);
	$aranceles[0]['monto_arancel_credito'] = round($aranceles[0]['monto_arancel_credito']/2,0);
	$aranceles[0]['beca']                  = round($aranceles[0]['beca']/2,0);
	$aranceles[0]['monto_matricula']       = round($aranceles[0]['monto_matricula']/2,0);
	$aranceles[0]['cuotas']                = round($aranceles[0]['cuotas']/2,0);
}

if ($tipo_contrato == "Modular") {
	$SQL_aranceles = "SELECT carrera,jornada,monto_matricula,monto_arancel,monto_arancel_credito,cuotas 
	                  FROM vista_aranceles 
	                  WHERE id_carrera=$id_carrera AND id_jornada='$id_jornada' AND ano=$ANO_MATRICULA;";
	$aranceles = consulta_sql($SQL_aranceles);
	$FACTOR_ARANCELES = 0.727272727;
	$aranceles[0]['monto_arancel']         = round($aranceles[0]['monto_arancel']*$FACTOR_ARANCELES,0);
	$aranceles[0]['monto_arancel_credito'] = round($aranceles[0]['monto_arancel_credito']*$FACTOR_ARANCELES,0);
//	$aranceles[0]['monto_matricula']       = round($aranceles[0]['monto_matricula']*$FACTOR_ARANCELES,0);
	$aranceles[0]['monto_matricula']       = 100000;
	$aranceles[0]['cuotas']                = round($aranceles[0]['cuotas']*$FACTOR_ARANCELES,0);
}

if ($_REQUEST['guardar'] == "Guardar") {
	
	if ($_REQUEST['financiamiento'] == 'CREDITO') {
		$_REQUEST['monto_arancel'] = $aranceles[0]['monto_arancel_credito'];
	} elseif ($_REQUEST['financiamiento'] == 'CONTADO') {
		$_REQUEST['monto_arancel'] = $aranceles[0]['monto_arancel'];
	}
	
	if ($_REQUEST['arancel_cheque'] == "" || $_REQUEST['arancel_cheque'] == 0) {
		$_REQUEST['arancel_cheque']         = "";
		$_REQUEST['arancel_cant_cheques']   = "";
		$_REQUEST['arancel_diap_cheque']    = "";
		$_REQUEST['arancel_mes_ini_cheque'] = "";
		$_REQUEST['arancel_ano_ini_cheque'] = "";
	}

	if ($_REQUEST['arancel_pagare_coleg'] == "" || $_REQUEST['arancel_pagare_coleg'] == 0) {
		$_REQUEST['arancel_pagare_coleg']         = "";
		$_REQUEST['arancel_cuotas_pagare_coleg']  = "";
		$_REQUEST['arancel_diap_pagare_coleg']    = "";
		$_REQUEST['arancel_mes_ini_pagare_coleg'] = "";		
	}

	if ($_REQUEST['arancel_tarjeta_credito'] == "" || $_REQUEST['arancel_tarjeta_credito'] == 0) {
		$_REQUEST['arancel_cant_tarj_credito'] = "";
	}

	$aCampos = array('id_alumno','id_aval','id_carrera','jornada','id_beca_externa','nivel',
	                 'monto_matricula','monto_arancel',
	                 'cod_beca_mat','monto_beca_mat','porc_beca_mat',
	                 'id_convenio','id_beca_arancel','monto_beca_arancel','porc_beca_arancel',
	                 'financiamiento',
	                 'mat_efectivo','mat_cheque','mat_cant_cheques','mat_tarj_cred','mat_cant_tarj_cred',
	                 'arancel_efectivo',
	                 'arancel_cheque','arancel_cant_cheques','arancel_diap_cheque','arancel_mes_ini_cheque','arancel_ano_ini_cheque',
	                 'arancel_pagare_coleg','arancel_cuotas_pagare_coleg','arancel_diap_pagare_coleg','arancel_mes_ini_pagare_coleg',
	                 'arancel_cred_interno',
	                 'arancel_tarjeta_credito','arancel_cant_tarj_credito',
	                 'ano','semestre','tipo','id_emisor');

	foreach ($_REQUEST AS $campo => $valor) {
		if (substr($campo,0,4) == "mat_" || substr($campo,0,8) == "arancel_" || substr($campo,0,6) == "monto_") {
			$_REQUEST[$campo] = str_replace(".","",$valor);
		}
	}
	
	$SQLinsert_contrato = "INSERT INTO finanzas.contratos " . arr2sqlinsert($_REQUEST,$aCampos) .";"
	                    . "SELECT currval('finanzas.contratos_id_seq') AS id";
	$contrato = consulta_sql($SQLinsert_contrato);

	if (count($contrato) == 1) {
		$id_contrato = $contrato[0]['id'];
		echo(msje_js("Se ha guardado exitosamente el contrato"));
		
		if ($_REQUEST['arancel_pagare_coleg'] > 0 && $_REQUEST['arancel_cuotas_pagare_coleg'] > 0) {
			$SQLinsert_pagare_coleg = "INSERT INTO finanzas.pagares_colegiatura (id_contrato,cuotas,dia_pago,mes_inicio,ano_inicio,monto) "
			                        . "VALUES ($id_contrato,{$_REQUEST['arancel_cuotas_pagare_coleg']},{$_REQUEST['arancel_diap_pagare_coleg']},{$_REQUEST['arancel_mes_ini_pagare_coleg']},{$_REQUEST['ano']},{$_REQUEST['arancel_pagare_coleg']});"
			                        . "SELECT currval('finanzas.pagares_colegiatura_id_seq') AS id";
			$pagare_colegiatura = consulta_sql($SQLinsert_pagare_coleg);			
			if (count($pagare_colegiatura) == 1) {
				$id_pagare_colegiatura = $pagare_colegiatura[0]['id'];
				echo(msje_js("Se ha guardado exitosamente el pagaré de colegiatura"));
				$id_glosa    = 2; // mensualidad de pagare de colegiatura
				$cant_cuotas = $_REQUEST['arancel_cuotas_pagare_coleg'];
				$monto_cuota = intval($_REQUEST['arancel_pagare_coleg']/$_REQUEST['arancel_cuotas_pagare_coleg']);
				$monto_total = $_REQUEST['arancel_pagare_coleg'];
				$diap        = trim($_REQUEST['arancel_diap_pagare_coleg']);
				$mesp        = trim($_REQUEST['arancel_mes_ini_pagare_coleg']);
				$anop        = trim($_REQUEST['ano']);
				$SQL_cobros  = generar_cobros($id_contrato,$id_glosa,$cant_cuotas,$monto_cuota,$monto_total,$diap,$mesp,$anop);
				consulta_sql($SQL_cobros);
			}
		}

		if ($_REQUEST['arancel_cheque'] > 0 && $_REQUEST['arancel_cant_cheques'] > 0) {
			$id_glosa    = 21; // mensualidad cheques
			$cant_cuotas = $_REQUEST['arancel_cant_cheques'];
			$monto_total = $_REQUEST['arancel_cheque'];
			$monto_cuota = intval($monto_total/$cant_cuotas);
			$diap        = $_REQUEST['arancel_diap_cheque'];
			$mesp        = $_REQUEST['arancel_mes_ini_cheque'];
			$anop        = $_REQUEST['arancel_ano_ini_cheque'];
			$SQL_cobros  = generar_cobros($id_contrato,$id_glosa,$cant_cuotas,$monto_cuota,$monto_total,$diap,$mesp,$anop);
			consulta_sql($SQL_cobros);
		}

		$diap        = strftime("%d");
		$mesp        = strftime("%m");
		$anop        = strftime("%Y");		

		if ($_REQUEST['arancel_efectivo'] > 0) {
			$id_glosa    = 3; // arancel completo
			if (intval($ano_contrato) > intval(date("Y"))) { $id_glosa = 10003; } // arancel completo anticipado
			$cant_cuotas = 1;
			$monto_total = $_REQUEST['arancel_efectivo'];
			$monto_cuota = intval($monto_total/$cant_cuotas);
			$SQL_cobros  = generar_cobros($id_contrato,$id_glosa,$cant_cuotas,$monto_cuota,$monto_total,$diap,$mesp,$anop);
			consulta_sql($SQL_cobros);
		}
		
		if ($_REQUEST['arancel_tarjeta_credito'] > 0) {
			$id_glosa    = 3; // arancel completo
			if (intval($ano_contrato) > intval(date("Y"))) { $id_glosa = 10003; } // arancel completo anticipado
			$cant_cuotas = 1;
			$monto_total = $_REQUEST['arancel_tarjeta_credito'];
			$monto_cuota = intval($monto_total/$cant_cuotas);
			$SQL_cobros  = generar_cobros($id_contrato,$id_glosa,$cant_cuotas,$monto_cuota,$monto_total,$diap,$mesp,$anop);
			consulta_sql($SQL_cobros);
		}
		
		if ($_REQUEST['mat_efectivo'] > 0 || $_REQUEST['mat_cheque'] > 0 || $_REQUEST['mat_tarj_cred'] > 0) {
			$id_glosa    = 1; // Matricula
			if (intval($ano_contrato) > intval(date("Y"))) { $id_glosa = 10001; } // matricula anticipada
			$cant_cuotas = 1;
			$monto_cuota = $monto_total = 0;
			if ($_REQUEST['mat_efectivo'] > 0)  { $monto_total += $_REQUEST['mat_efectivo'];  }
			if ($_REQUEST['mat_cheque'] > 0)    { $monto_total += $_REQUEST['mat_cheque'];    }
			if ($_REQUEST['mat_tarj_cred'] > 0) { $monto_total += $_REQUEST['mat_tarj_cred']; }
			$monto_cuota = intval($monto_total/$cant_cuotas);
			$SQL_cobros  = generar_cobros($id_contrato,$id_glosa,$cant_cuotas,$monto_cuota,$monto_total,$diap,$mesp,$anop);
			consulta_sql($SQL_cobros);
		}
		
		if ($_REQUEST['arancel_cred_interno'] > 0 || $_REQUEST['monto_cred_int'] > 0 || $_REQUEST['monto_acum_ci'] > 0) {
			$uf = consulta_sql("SELECT valor FROM finanzas.valor_uf WHERE fecha=now()::date;");
			if (count($uf) == 1) {
				setlocale(LC_ALL,'C');
				$uf_valor = intval($uf[0]['valor']);
				$monto    = floatval(round((intval($_REQUEST['arancel_cred_interno']) / $uf_valor),2));
				//$monto   += $aranceles[0]['monto_cred_int']; 
				$monto   += $monto_acum_ci; 
				$SQLinsert_pagare_cred_int = "INSERT INTO finanzas.pagares_cred_interno (id_contrato,monto) "
			                              . "     VALUES ($id_contrato,$monto);"
			                              . "SELECT currval('finanzas.pagares_cred_interno_id_seq') AS id";
				$pagare_cred_int = consulta_sql($SQLinsert_pagare_cred_int);			
				if (count($pagare_cred_int) == 1) {
					$id_pagare_cred_interno = $pagare_cred_int[0]['id'];
					echo(msje_js("Se ha guardado exitosamente el pagaré de Crédito Interno"));
				}
			} else {
				echo(msje_js("ERROR: No ha sido posible crear el pagaré de Crédito Interno, "
				            ."debido a que no se ha encontrado el valor de la UF para hoy. "
				            ."Por favor informe de este mensaje a el Departamento de Informática"));
			}
		}
		
		if (is_numeric($id_contrato)) {
			if ($alumno[0]['admision'] == "10" || $alumno[0]['admision'] == "20") {
				echo(js("window.open('contrato.php?id_contrato=$id_contrato&tipo=al_antiguo_modular');"));
			}
			echo(js("window.open('contrato.php?id_contrato=$id_contrato&tipo=al_antiguo');"));
		}

		if (is_numeric($id_pagare_colegiatura)) {
			echo(js("window.open('pagare_colegiatura.php?id_pagare_colegiatura=$id_pagare_colegiatura');"));
		}

		if (is_numeric($id_pagare_cred_interno)) {
			echo(js("window.open('pagare_cred_interno.php?id_pagare_cred_interno=$id_pagare_cred_interno');"));
		}
		
		echo(js("location.href='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
		exit;
	}
} 

$convenios      = consulta_sql("SELECT id,nombre||' ('||porcentaje||'%)' AS nombre FROM convenios WHERE activo ORDER BY nombre;");
$becas          = consulta_sql("SELECT id,nombre from becas WHERE al_antiguos");
$becas_externas = consulta_sql("SELECT id,nombre from finanzas.becas_externas;");

$CUOTAS = array();
$max_cuotas = 12;
if ($alumno[0]['regimen'] == "POST-GD") { $max_cuotas = 30; }
for ($x=1;$x<=$max_cuotas;$x++) { $CUOTAS = array_merge($CUOTAS,array(array("id"=>$x,"nombre"=>$x))); } 

$dias_pago = array(array("id"=>5 ,"nombre"=>"5"),
                   array("id"=>15,"nombre"=>"15"),
                   array("id"=>30,"nombre"=>"30"));
                   
$anos_ini = array(array("id"=>date("Y"),"nombre"=>date("Y")),
                  array("id"=>date("Y")+1,"nombre"=>date("Y")+1));

if ($_REQUEST['arancel_cant_cheques'] == "") { $_REQUEST['arancel_cant_cheques'] = $aranceles[0]['cuotas']; }
if ($_REQUEST['arancel_mes_ini_cheque'] == "") { $_REQUEST['arancel_mes_ini_cheque'] = 3; }
if ($_REQUEST['arancel_ano_ini_cheque'] == "") { $_REQUEST['arancel_ano_ini_cheque'] = $ANO_MATRICULA; }
if ($_REQUEST['arancel_mes_ini_pagare_coleg'] == "") { $_REQUEST['arancel_mes_ini_pagare_coleg'] = 3; } 
if ($_REQUEST['arancel_cuotas_pagare_coleg'] == "") { $_REQUEST['arancel_cuotas_pagare_coleg'] = $aranceles[0]['cuotas']; }
//if (time() < strtotime("2009-12-31")) { $_REQUEST['cod_beca_mat'] = "UMC"; $_REQUEST['monto_beca_mat'] = "75000"; }
?>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>
<br>
<form name="formulario" action="principal.php" method="post" onSubmit="if ((!verif_matric_finan()) || (!verif_arancel_finan())) { return false; }">
<input type="hidden" name="modulo"                value="<?php echo($modulo); ?>">
<input type="hidden" name="id_alumno"             value="<?php echo($id_alumno); ?>">
<input type="hidden" name="id_carrera"            value="<?php echo($alumno[0]['id_carrera']); ?>">
<input type="hidden" name="jornada"               value="<?php echo($alumno[0]['id_jornada']); ?>">
<input type="hidden" name="id_aval"               value="<?php echo($id_aval); ?>">
<input type="hidden" name="monto_matricula"       value="<?php echo($aranceles[0]['monto_matricula']); ?>">
<input type="hidden" name="monto_arancel"         value="<?php echo($aranceles[0]['monto_arancel']); ?>">
<input type="hidden" name="monto_arancel_credito" value="<?php echo($aranceles[0]['monto_arancel_credito']); ?>">
<input type="hidden" name="monto_cred_int"        value="<?php echo($aranceles[0]['monto_cred_int']); ?>">
<input type="hidden" name="monto_acum_ci"         value="<?php echo($monto_acum_ci); ?>">
<input type="hidden" name="ano"                   value="<?php echo($ano_contrato); ?> ">
<input type="hidden" name="semestre"              value="<?php echo($semestre_contrato); ?>">
<input type="hidden" name="tipo"                  value="<?php echo($tipo_contrato); ?>">
<input type="hidden" name="id_emisor"             value="<?php echo($_SESSION['id_usuario']); ?>">

<table class="tabla">
  <tr>
    <td class="tituloTabla">
      <input type="submit" name="guardar" value="Guardar" tabindex="99">
    </td>
    <td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar" onclick="history.back();"></td>
  </tr>
</table>
<br>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla">
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">
      Antecedentes Personales del Alumno</sup>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['rut']); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['id']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($alumno[0]['nombre']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Dirección:</td>
    <td class='celdaValorAttr' colspan="3">
      <?php echo($alumno[0]['direccion']); ?>, <?php echo($alumno[0]['comuna']); ?>, <?php echo($alumno[0]['region']); ?>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Beca Externa:</td>
    <td class='celdaValorAttr' colspan="3">
      <select class='filtro' name='id_beca_externa'>
        <option value=''>-- Ninguna --</option>
        <?php echo(select($becas_externas,$_REQUEST['id_beca_externa'])); ?>
      </select>
      <sub>(Esta beca no genera descuentos ni es una forma de pago)</sub>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">
      Antecedentes del Responsable Financiero<!-- <br><sup>(Apoderado, Sostenedor,Aval o deudor directo)</sup> -->
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($aval[0]['rf_rut']); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($aval[0]['id']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($aval[0]['rf_nombre']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Dirección:</td>
    <td class='celdaValorAttr' colspan="3">
      <?php echo($aval[0]['rf_direccion']); ?>, <?php echo($aval[0]['rf_com']); ?>, <?php echo($aval[0]['rf_reg']); ?>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center">Carrera en que se Matricula</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr' colspan="4">
      <?php echo($alumno[0]['carrera']); ?> <b>Jornada:</b> <?php echo($alumno[0]['jornada']); ?> en el
      <select class='filtro' name="nivel">
        <?php echo(select($NIVELES,$_REQUEST['nivel'])); ?>
      </select> nivel
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center">Valores (<?php echo("$tipo_contrato $semestre_contrato-$ano_contrato"); ?>)</td>
  </tr>
  <tr>    
    <td class='celdaValorAttr' colspan="4" align='center'>
      <b>Matrícula:</b> $<?php echo(number_format($aranceles[0]['monto_matricula'],0,',','.')); ?>&nbsp;&nbsp;&nbsp;
      <b>Arancel contado:</b> $<?php echo(number_format($aranceles[0]['monto_arancel'],0,',','.')); ?>&nbsp;&nbsp;&nbsp;
      <b>Arancel crédito:</b> $<?php echo(number_format($aranceles[0]['monto_arancel_credito'],0,',','.')); ?><br>
      <b>Beca:</b> $<?php echo(number_format($aranceles[0]['beca'],0,',','.')); ?>
      <!-- <b>Crédito Interno (acumulado):</b> UF <?php echo(number_format($aranceles[0]['monto_cred_int'],2,',','.')); ?> -->
      <b>Crédito Interno (acumulado):</b> UF <?php echo(number_format($monto_acum_ci,2,',','.')); ?>
      <!-- <b>Último Crédito Interno asignado:</b> $<?php echo(number_format($aranceles[0]['cred_interno'],0,',','.')); ?> -->
      <b>Último Crédito Interno asignado:</b> $<?php echo(number_format($arancel_cred_interno,0,',','.')); ?>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center">Descuentos (Matrícula y/o Arancel)</td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'><u>Beca (Matrícula):</u></td>
    <td class='celdaValorAttr' colspan="3">
      <select class='filtro' name='cod_beca_mat' onChange="becas_mat(this.value);">
        <option value=''>-- Ninguna --</option>
        <?php echo(select($becas_mat,$_REQUEST['cod_beca_mat'])); ?>        
      </select>
      Monto: $<input type='text' class='montos' size='10' name='monto_beca_mat' value="<?php echo($_REQUEST['monto_beca_mat']); ?>" 
                     onKeyUp="puntitos(this,this.value.charAt(this.value.length-1),'monto_beca_mat')"
                     onBlur="if (this.value != '') { formulario.porc_beca_mat.value=''; } calc_matric();" disabled> ó
      Porcentaje: <input type='text' class="montos" size="2" name='porc_beca_mat' value="<?php echo($_REQUEST['porc_beca_mat']); ?>" 
                         onBlur="if (this.value != '') { formulario.monto_beca_mat.value=''; } calc_matric();" disabled>%
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Convenio (Arancel):</u></td>
    <td class='celdaValorAttr' colspan="3">
      <select name='id_convenio' onChange="convenio(this.value);">
        <option value=''>-- Ninguno --</option>
			<?php echo(select($convenios,$_REQUEST['id_convenio'])); ?>        
      </select>    
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Beca (Arancel):</u></td>
    <td class='celdaValorAttr' colspan="3">
      <select class='filtro' name='id_beca_arancel' onChange="becas(this.value); obtener_porc_beca(this.value);">
        <option value=''>-- Ninguna --</option>
			<?php echo(select($becas,$_REQUEST['id_beca_arancel'])); ?>        
      </select><span id="porc_beca_max"></span>
      Monto: $<input type='text' class="montos" size="10" name='monto_beca_arancel' value="<?php echo($_REQUEST['monto_beca_arancel']); ?>"
                     onkeyup="puntitos(this,this.value.charAt(this.value.length-1),'monto_beca_arancel')"
                     onBlur="if (this.value != '') { formulario.porc_beca_arancel.value=''; } calc_arancel();" disabled> ó
      Porcentaje: <input type='number' min="0" max="100" size="2" name='porc_beca_arancel' value="<?php echo($_REQUEST['porc_beca_arancel']); ?>"
                         onBlur="if (this.value != '') { formulario.monto_beca_arancel.value=''; } calc_arancel();"  class="boton" disabled>%      
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center">
      Financiamiento:
      <select class='filtro' name='financiamiento' onChange="finan()">
			<?php echo(select($financiamientos,$_REQUEST['financiamiento'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="2" style="text-align: center">Matrícula</td>
    <td class='celdaNombreAttr' colspan="2" style="text-align: center">Arancel</td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'><u>Monto a Financiar:</u></td>
    <td class='celdaValorAttr'>
      $<input type="text" size="10" class='montos' style="border: none" name="matric_finan" value="0" readonly>
      <input type="button" size="5" value="Calcular" onClick="calc_matric();"
    </td>
    <td class='celdaNombreAttr'><u>Monto a Financiar:</u></td>
    <td class='celdaValorAttr'>
      $<input type="text" size="10" class='montos' style="border: none" name="arancel_finan" value="0" readonly>
      <input type="button" size="5" name="calcular" value="Calcular" onClick="calc_arancel();"
    </td>
  </tr>

<script>
function calc_matric() {
	var matric_rebajada = formulario.monto_matricula.value,monto_beca=0;
	
	if (formulario.cod_beca_mat.value != '') {
		if (formulario.monto_beca_mat.value != '' && formulario.porc_beca_mat.value == '') {
			monto_beca_mat = formulario.monto_beca_mat.value;
			matric_rebajada = matric_rebajada - monto_beca_mat.replace('.','');
		}
		if (formulario.monto_beca_mat.value == '' && formulario.porc_beca_mat.value != '') {				
			matric_rebajada = matric_rebajada - Math.round(formulario.monto_matricula.value * (formulario.porc_beca_mat.value/100));
		}
	}
	formulario.matric_finan.value = matric_rebajada;
	puntitos(document.formulario.matric_finan,document.formulario.matric_finan.value.charAt(document.formulario.matric_finan.value.length-1),document.formulario.matric_finan.name);
}

function calc_arancel() {
	var arancel_rebajado = 0,monto_beca=0;
	
	if (formulario.financiamiento.value == "CREDITO") {
		arancel_rebajado = formulario.monto_arancel_credito.value;
	} else {
		arancel_rebajado = formulario.monto_arancel.value;
	}
		
	if (formulario.id_convenio.value != '') {
		arancel_rebajado = arancel_rebajado - Math.round(arancel_rebajado * 0.2);
	} else {
		if (formulario.id_beca_arancel.value != '') {
			if (formulario.monto_beca_arancel.value != '' && formulario.porc_beca_arancel.value == '') {
				monto_beca_arancel = formulario.monto_beca_arancel.value;
				arancel_rebajado = arancel_rebajado - monto_beca_arancel.replace('.','').replace('.','');
			}
			if (formulario.monto_beca_arancel.value == '' && formulario.porc_beca_arancel.value != '') {				
				arancel_rebajado = arancel_rebajado - Math.round(arancel_rebajado * (formulario.porc_beca_arancel.value/100));
			}
		}
	}
	formulario.arancel_finan.value = arancel_rebajado;
	puntitos(document.formulario.arancel_finan,document.formulario.arancel_finan.value.charAt(document.formulario.arancel_finan.value.length-1),document.formulario.arancel_finan.name);
}

function convenio(id_convenio) {
	if (id_convenio != '') {
		formulario.id_beca_arancel.disabled=true;
		formulario.monto_beca_arancel.disabled=true;
		formulario.porc_beca_arancel.disabled=true;
	} else {
		formulario.id_beca_arancel.disabled=false;
//		formulario.monto_beca_arancel.disabled=false;
//		formulario.porc_beca_arancel.disabled=false;
	}
	calc_arancel();
}	

function becas(id_beca_arancel) {	
	var porcentaje_beca = 0;
	
	if (id_beca_arancel != '') {
//		var xmlhttp = new XMLHttpRequest();        
//		xmlhttp.open("GET", "obtener_porc_beca.php?id_beca=" + id_beca_arancel, true);
//		xmlhttp.send();
//		porcentaje_beca = xmlhttp.responseText;        
//		alert(porcentaje_beca);
        
		formulario.monto_beca_arancel.disabled=false;
		formulario.porc_beca_arancel.disabled=false;
	} else {
		formulario.monto_beca_arancel.disabled=true;
		formulario.monto_beca_arancel.value=null;
		formulario.porc_beca_arancel.disabled=true;
		formulario.porc_beca_arancel.value=null;
	}
	calc_arancel();
}	

function becas_mat(cod_beca_mat) {
	if (cod_beca_mat != '') {
		formulario.monto_beca_mat.disabled=false;
		formulario.porc_beca_mat.disabled=false;
	} else {
		formulario.monto_beca_mat.disabled=true;
		formulario.monto_beca_mat.value=null;
		formulario.porc_beca_mat.disabled=true;
		formulario.porc_beca_mat.value=null;
	}
	calc_matric();
}	

function finan() {
	formulario.matric_finan.value = 0;
	formulario.arancel_finan.value = 0;
	calc_matric();
	calc_arancel();
}	

function verif_matric_finan() {
	var total_matric_finan=0,matric_finan=formulario.matric_finan.value;
	var mat_efectivo  = formulario.mat_efectivo.value,
	    mat_cheque    = formulario.mat_cheque.value,
	    mat_tarj_cred = formulario.mat_tarj_cred.value
	    diferencia    = 0;
	    
	total_matric_finan = mat_efectivo.replace('.','')*1 +
	                     mat_cheque.replace('.','')*1 +
	                     mat_tarj_cred.replace('.','')*1;
	
	matric_finan = matric_finan.replace('.','').replace('.','')*1
	diferencia = matric_finan - total_matric_finan;
	 
	if (total_matric_finan == matric_finan) {
		return true;
	} else {
		alert("El monto a financiar de la matrícula no está completo o hay un error lógico. Actualmente los valores ingresados suman $"+total_matric_finan+" restando financiar un monto de $"+diferencia);
		return false;
	}
}

function verif_arancel_finan() {
	var total_arancel_finan=0,arancel_finan=formulario.arancel_finan.value;
	var arancel_efectivo        = formulario.arancel_efectivo.value,
	    arancel_cheque          = formulario.arancel_cheque.value,
	    arancel_pagare_coleg    = formulario.arancel_pagare_coleg.value,
	    arancel_cred_interno    = formulario.arancel_cred_interno.value,
	    arancel_tarjeta_credito = formulario.arancel_tarjeta_credito.value,
	    diferencia              = 0;
	    
	total_arancel_finan = arancel_efectivo.replace('.','').replace('.','')*1 +
	                      arancel_cheque.replace('.','').replace('.','')*1 +
	                      arancel_pagare_coleg.replace('.','').replace('.','')*1 +
	                      arancel_cred_interno.replace('.','').replace('.','')*1 +
	                      arancel_tarjeta_credito.replace('.','').replace('.','')*1;
	
	arancel_finan = arancel_finan.replace('.','').replace('.','')*1
	diferencia = arancel_finan - total_arancel_finan;
	
	if (total_arancel_finan == arancel_finan) {
		return true;
	} else {
		alert("El monto a financiar del arancel no está completo o hay un error lógico. Actualmente los valores ingresados suman $"+total_arancel_finan+" restando financiar un monto de $"+diferencia);
		return false;
	}
}

finan();
puntitos(document.formulario.arancel_finan,document.formulario.arancel_finan.value.charAt(document.formulario.arancel_finan.value.length-1),document.formulario.arancel_finan.name);
puntitos(document.formulario.matric_finan,document.formulario.matric_finan.value.charAt(document.formulario.matric_finan.value.length-1),document.formulario.matric_finan.name);

		
</script>  
<?php if (!empty($_REQUEST['porc_beca_arancel']) && !empty($_REQUEST['id_beca_arancel'])) { echo(js("becas('{$_REQUEST['id_beca_arancel']}')")); }?>
  <tr>
    <td class='celdaNombreAttr'>Efectivo:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='mat_efectivo' value="<?php echo($_REQUEST['mat_efectivo']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
    </td>
    <td class='celdaNombreAttr'>Efectivo:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='arancel_efectivo' value="<?php echo($_REQUEST['arancel_efectivo']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cheque(s):</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='mat_cheque' value="<?php echo($_REQUEST['mat_cheque']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
      Cant.: <input type='text' size='2' name='mat_cant_cheques' value="<?php echo($_REQUEST['mat_cant_cheques']); ?>">
    </td>  
    <td class='celdaNombreAttr'>Cheque(s):</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='arancel_cheque' value="<?php echo($_REQUEST['arancel_cheque']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
      <sub>
        Cant.: <select class='filtro' name='arancel_cant_cheques'><?php echo(select($CUOTAS,$_REQUEST['arancel_cant_cheques'])); ?></select><br>
        Día venc.: <select class='filtro' name='arancel_diap_cheque'><?php echo(select($dias_pago,$_REQUEST['arancel_diap_cheque'])); ?></select>
        Mes inicio: <select class='filtro' name="arancel_mes_ini_cheque"><?php echo(select($meses_palabra,$_REQUEST['arancel_mes_ini_cheque'])); ?></select>
        Año inicio: <select class='filtro' name="arancel_ano_ini_cheque"><?php echo(select($anos_ini,$_REQUEST['arancel_ano_ini_cheque'])); ?></select>
      </sub>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tarjetas de Crédito:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='mat_tarj_cred' value="<?php echo($_REQUEST['mat_tarj_cred']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
      Cant.: <input type='text' size='2' name='mat_cant_tarj_cred' value="<?php echo($_REQUEST['mat_cant_tarj_cred']); ?>">
    </td>
    <td class='celdaNombreAttr'>Pagaré Colegiatura:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='arancel_pagare_coleg' value="<?php echo($_REQUEST['arancel_pagare_coleg']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
      <sub>
        Cuotas: <select class='filtro' name='arancel_cuotas_pagare_coleg'><?php echo(select($CUOTAS,$_REQUEST['arancel_cuotas_pagare_coleg'])); ?></select><br>
        Día pago: <select class='filtro' name='arancel_diap_pagare_coleg'><?php echo(select($dias_pago,$_REQUEST['arancel_diap_pagare_coleg'])); ?></select>
        Mes inicio: <select class='filtro' name="arancel_mes_ini_pagare_coleg"><?php echo(select($meses_palabra,$_REQUEST['arancel_mes_ini_pagare_coleg'])); ?></select>
      </sub>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td class='celdaNombreAttr'>Crédito Interno:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='arancel_cred_interno' value="<?php echo($_REQUEST['arancel_cred_interno']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">                      
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td class='celdaNombreAttr'>Tarjetas de Cŕédito:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='arancel_tarjeta_credito' value="<?php echo($_REQUEST['arancel_tarjeta_credito']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
      Cant.: <input type='text' size='2' name='arancel_cant_tarj_cred' value="<?php echo($_REQUEST['arancel_cant_tarj_cred']); ?>">
    </td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="4" align="center">
      <b>Cant:</b> Se refiere a Cantidad de Cheques o Cantidad de Cuotas de Tarjetas
    </td>
  </tr>
</table>

</form>

<script>
function obtener_porc_beca(id_beca_arancel){
        var parametros = {
                "id_beca" : id_beca_arancel
        };
        $.ajax({
                data:  parametros, //datos que se envian a traves de ajax
                url:   'obtener_porc_beca.php', //archivo que recibe la peticion
                type:  'get', //método de envio
                success:  function (response) { //una vez que el archivo recibe el request lo procesa y lo devuelve
                        $("#porc_beca_max").html(response);                        
                }
        });
}
</script>
