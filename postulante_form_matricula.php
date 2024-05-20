<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

// Descomentando $ANO_MATRICULA y $SEMESTRE_MATRICULA establece un valor distinto del global definido en funciones.php
//$ANO_MATRICULA = 2012;
//$SEMESTRE_MATRICULA = 1;

$id_pap  = $_REQUEST['id_pap'];
if (!is_numeric($id_pap)) {
	echo(js("location.href='principal.php?modulo=gestion_postulantes';"));
	exit;
}
$id_carrera    = $_REQUEST['carrera_mat'];
$jornada       = $_REQUEST['jornada_mat'];
$tipo_contrato = $_REQUEST['tipo_contrato'];
$ano           = $_REQUEST['ano'];
$semestre      = $_REQUEST['semestre'];

$SQL_pap = "SELECT vp.id,vp.rut,vp.nombre,vp.direccion,vp.comuna,vp.region,
                   pap.genero AS cod_genero,pap.nacionalidad AS cod_nac,pap.comuna AS cod_comuna,
                   pap.region AS cod_region,pap.email,pap.pasaporte,pap.telefono,pap.tel_movil,pap.id_aval,
                   coalesce(vp.carrera1_post,0) AS carrera1_post, coalesce(vp.carrera2_post,0) AS carrera2_post,
                   coalesce(vp.carrera3_post,0) AS carrera3_post,vp.admision,pap.arancel_promo
            FROM pap
            LEFT JOIN vista_pap AS vp USING (id)
            WHERE vp.id=$id_pap;";
$pap = consulta_sql($SQL_pap);
if (count($pap) > 0) {
	$id_aval = $pap[0]['id_aval'];
	$SQL_aval = "SELECT id,rf_rut,rf_nombre,rf_direccion,rf_com,rf_reg
	             FROM vista_avales WHERE id=$id_aval;";
	$aval     = consulta_sql($SQL_aval);
} else {
	echo(js("location.href='principal.php?modulo=gestion_postulantes';"));
	exit;
}

if ($tipo_contrato == "Semestral" || $tipo_contrato == "Anual" || $tipo_contrato == "Modular") { $SEMESTRE_MATRICULA = 1; }

if ($pap[0]['arancel_promo'] == "t") {
	$SQL_aranceles = "SELECT carrera,jornada,coalesce(monto_matricula,0) AS monto_matricula,monto_arancel_promo AS monto_arancel,monto_arancel_promo_credito AS monto_arancel_credito,cuotas 
					FROM vista_aranceles 
					WHERE id_carrera=$id_carrera AND id_jornada='$jornada' AND ano=$ano;";
} elseif ($pap[0]['arancel_promo'] == "f") {
//							least(monto_arancel,monto_arancel_especial) AS monto_arancel,
//							least(monto_arancel_credito,monto_arancel_credito_especial) AS monto_arancel_credito,


	$SQL_aranceles = "SELECT carrera,jornada,
	                         coalesce(monto_matricula,0) AS monto_matricula,
							 monto_arancel,
							 monto_arancel_credito,
							 cuotas 
					FROM vista_aranceles 
					WHERE id_carrera=$id_carrera AND id_jornada='$jornada' AND ano=$ano;";
}
$aranceles = consulta_sql($SQL_aranceles);
if (count($aranceles) == 0) {
	echo(msje_js("ERROR: No están definidos los aranceles para la carrera y/o jornada en que se intenta matricular. Verifique estos datos en la ficha del postulante."));
	echo(js("location.href='$enlbase=ver_postulante&id_pap=$id_pap';"));
	exit;
}
if ($tipo_contrato == "Semestral") {
	$aranceles[0]['monto_arancel']         = round($aranceles[0]['monto_arancel']/2,0);
	$aranceles[0]['monto_arancel_credito'] = round($aranceles[0]['monto_arancel_credito']/2,0);
	$aranceles[0]['monto_matricula']       = round($aranceles[0]['monto_matricula']/2,0);
	$aranceles[0]['cuotas']                = ceil($aranceles[0]['cuotas']/2);
}

if ($tipo_contrato == "Modular") {
	$FACTOR_ARANCELES = 0.727272727;
	$aranceles[0]['monto_arancel']         = round($aranceles[0]['monto_arancel']*$FACTOR_ARANCELES,0);
	$aranceles[0]['monto_arancel_credito'] = round($aranceles[0]['monto_arancel_credito']*$FACTOR_ARANCELES,0);
	$aranceles[0]['monto_matricula']       = round($aranceles[0]['monto_matricula']*$FACTOR_ARANCELES,0);
//	$aranceles[0]['monto_matricula']       = 100000;
	$aranceles[0]['cuotas']                = ceil($aranceles[0]['cuotas']*$FACTOR_ARANCELES);
}

$problemas = false;
if (!empty($_REQUEST['id_contrato_preimp'])) {		
	$contrato_preimp = consulta_sql("SELECT id FROM finanzas.contratos WHERE estado IS NOT NULL AND id_contrato_preimp={$_REQUEST['id_contrato_preimp']}");
	if (count($contrato_preimp) > 0) {
		echo(msje_js("ERROR: Es número de contrato preimpreso {$_REQUEST['id_contrato_preimp']} ya "
					 ."se encuentra utlizado en el contrato {$contrato_preimp[0]['id']}.\\n\\n"
					 ."No se puede continuar."));
		$problemas = true;	
	}
}

if ($_REQUEST['guardar'] == "Guardar" && !$problemas) {
	
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
		$_REQUEST['arancel_mes_ano_pagare_coleg'] = "";		
	}

	if ($_REQUEST['arancel_tarjeta_credito'] == "" || $_REQUEST['arancel_tarjeta_credito'] == 0) {
		$_REQUEST['arancel_cant_tarj_credito'] = "";
	}

	$aCampos = array('id_pap','id_aval','id_carrera','jornada','id_contrato_preimp','id_beca_externa','nivel',
	                 'monto_matricula','monto_arancel',
	                 'cod_beca_mat','monto_beca_mat','porc_beca_mat',
	                 'id_convenio','id_beca_arancel','monto_beca_arancel','porc_beca_arancel',
	                 'financiamiento',
	                 'mat_efectivo','mat_cheque','mat_cant_cheques','mat_tarj_cred','mat_cant_tarj_cred',
	                 'arancel_efectivo',
	                 'arancel_cheque','arancel_cant_cheques','arancel_diap_cheque','arancel_mes_ini_cheque','arancel_ano_ini_cheque',
	                 'arancel_pagare_coleg','arancel_cuotas_pagare_coleg','arancel_diap_pagare_coleg','arancel_mes_ini_pagare_coleg','arancel_ano_ini_pagare_coleg',
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
			                        . "VALUES ($id_contrato,{$_REQUEST['arancel_cuotas_pagare_coleg']},{$_REQUEST['arancel_diap_pagare_coleg']},{$_REQUEST['arancel_mes_ini_pagare_coleg']},{$_REQUEST['arancel_ano_ini_pagare_coleg']},{$_REQUEST['arancel_pagare_coleg']});"
			                        . "SELECT currval('finanzas.pagares_colegiatura_id_seq') AS id";
			$pagare_colegiatura = consulta_sql($SQLinsert_pagare_coleg);
			if (count($pagare_colegiatura) == 1) {
				$id_pagare_colegiatura = $pagare_colegiatura[0]['id'];
				echo(msje_js("Se ha guardado exitosamente el pagaré de colegiatura"));
				$id_glosa    = 2; // mensualidad de pagare de colegiatura
				$cant_cuotas = $_REQUEST['arancel_cuotas_pagare_coleg'];
				$monto_cuota = intval($_REQUEST['arancel_pagare_coleg']/$_REQUEST['arancel_cuotas_pagare_coleg']);
				$monto_total = $_REQUEST['arancel_pagare_coleg'];
				$diap        = $_REQUEST['arancel_diap_pagare_coleg'];
				$mesp        = $_REQUEST['arancel_mes_ini_pagare_coleg'];
				$anop        = $_REQUEST['arancel_ano_ini_pagare_coleg'];
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
		
		if ($_REQUEST['arancel_efectivo'] > 0) {
			$id_glosa    = 3; // arancel completo
			if (intval($ano) > intval(date("Y"))) { $id_glosa = 10003; } // arancel completo anticipado
			$cant_cuotas = 1;
			$monto_total = $_REQUEST['arancel_efectivo'];
			$monto_cuota = intval($monto_total/$cant_cuotas);
			$diap        = strftime("%d");
			$mesp        = strftime("%m");
			$anop        = strftime("%Y");
			$SQL_cobros  = generar_cobros($id_contrato,$id_glosa,$cant_cuotas,$monto_cuota,$monto_total,$diap,$mesp,$anop);
			consulta_sql($SQL_cobros);
		}
		
		if ($_REQUEST['arancel_tarjeta_credito'] > 0) {
			$id_glosa    = 3; // arancel completo
			if (intval($ano) > intval(date("Y"))) { $id_glosa = 10003; } // arancel completo anticipado
			$cant_cuotas = 1;
			$monto_total = $_REQUEST['arancel_tarjeta_credito'];
			$monto_cuota = intval($monto_total/$cant_cuotas);
			$diap        = strftime("%d");
			$mesp        = strftime("%m");
			$anop        = strftime("%Y");
			$SQL_cobros  = generar_cobros($id_contrato,$id_glosa,$cant_cuotas,$monto_cuota,$monto_total,$diap,$mesp,$anop);
			consulta_sql($SQL_cobros);
		}
		
		if ($_REQUEST['mat_efectivo'] > 0 || $_REQUEST['mat_cheque'] > 0 || $_REQUEST['mat_tarj_cred'] > 0) {
			$id_glosa    = 1; // Matricula
			if (intval($ano) > intval(date("Y"))) { $id_glosa = 10001; } // matricula anticipada
			$cant_cuotas = 1;
			if ($_REQUEST['mat_efectivo'] > 0) {
				$monto_total = $_REQUEST['mat_efectivo'];
			} 
			elseif ($_REQUEST['mat_cheque'] > 0) {
				$monto_total = $_REQUEST['mat_cheque'];
			} 
			elseif ($_REQUEST['mat_tarj_cred'] > 0) {
				$monto_total = $_REQUEST['mat_tarj_cred'];
			}
			$monto_cuota = intval($monto_total/$cant_cuotas);
			$diap        = strftime("%d");
			$mesp        = strftime("%m");
			$anop        = strftime("%Y");
			$SQL_cobros  = generar_cobros($id_contrato,$id_glosa,$cant_cuotas,$monto_cuota,$monto_total,$diap,$mesp,$anop);
			consulta_sql($SQL_cobros);
		}

		if ($_REQUEST['arancel_cred_interno'] > 0) {
			$uf = consulta_sql("SELECT valor FROM finanzas.valor_uf WHERE fecha=now()::date;");
			if (count($uf) == 1) {
				setlocale(LC_ALL,'C');
				$uf_valor = intval($uf[0]['valor']);
				$monto    = floatval(round((intval($_REQUEST['arancel_cred_interno']) / $uf_valor),2));
				
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
			if ($pap[0]['admision'] == "Normal") { 
				$fmt_contrato = "al_nuevo";
				$regimen = consulta_sql("SELECT regimen FROM finanzas.contratos c LEFT JOIN carreras car ON car.id=c.id_carrera WHERE c.id=$id_contrato");
				$regimen = $regimen[0]['regimen'];
				if ($regimen == "POST") { $fmt_contrato .= "_POST"; }
				if ($regimen == "DIP") { $fmt_contrato .= "_DIP"; }
			} elseif ($pap[0]['admision'] == "Extraordinaria") {
				$fmt_contrato = "al_nuevo_conv";
			} elseif ($pap[0]['admision'] == "Modular" || $pap[0]['admision'] == "Modular (Extr.)") {
				$fmt_contrato = "al_nuevo_modular";
			}
			
			echo(js("window.open('contrato.php?id_contrato=$id_contrato&tipo=$fmt_contrato');"));
		}

		if (is_numeric($id_pagare_colegiatura)) {
			echo(js("window.open('pagare_colegiatura.php?id_pagare_colegiatura=$id_pagare_colegiatura');"));
		}

		if (is_numeric($id_pagare_cred_interno)) {
			echo(js("window.open('pagare_cred_interno.php?id_pagare_cred_interno=$id_pagare_cred_interno');"));
		}
		
		echo(js("location.href='$enlbase=form_matricula_ver&id_contrato=$id_contrato';"));
		exit;
	}
} 

$convenios      = consulta_sql("SELECT id,nombre||' ('||porcentaje::int4||'%)' AS nombre from convenios WHERE activo ORDER BY nombre;");
$becas          = consulta_sql("SELECT id,nombre FROM becas WHERE al_nuevos ORDER BY nombre");
$becas_externas = consulta_sql("SELECT id,nombre from finanzas.becas_externas;");

$CUOTAS     = array();
$max_cuotas = $aranceles[0]['cuotas'];
/*
switch ($tipo_contrato) {
	case "Modular":
		$max_cuotas = 8;
		break;
	case "Semestral":
		$max_cuotas = 6;
		break;
}
*/
for ($x=1;$x<=$max_cuotas;$x++) { $CUOTAS = array_merge($CUOTAS,array(array("id"=>$x,"nombre"=>$x))); } 

$dias_pago = array(array("id"=>5 ,"nombre"=>"5"),
                   array("id"=>15,"nombre"=>"15"),
                   array("id"=>30,"nombre"=>"30"));

$ano_ini = strftime("%Y");                   
$anos_ini = array(array("id"=>$ano_ini-1,"nombre"=>$ano_ini-1),
                  array("id"=>$ano_ini,  "nombre"=>$ano_ini),
                  array("id"=>$ano_ini+1,"nombre"=>$ano_ini+1));
                  
for ($x=0;$x<12;$x++) { $meses_palabra[$x]['nombre'] = substr($meses_palabra[$x]['nombre'],0,3); }

$mes_actual = date("m");

//if ($_REQUEST['arancel_cant_cheques'] == "")         { $_REQUEST['arancel_cant_cheques'] = $aranceles[0]['cuotas']; }
if ($_REQUEST['arancel_cant_cheques'] == "")         { $_REQUEST['arancel_cant_cheques'] = 10; }
if ($_REQUEST['arancel_mes_ini_cheque'] == "")       { $_REQUEST['arancel_mes_ini_cheque'] = $mes_actual; }
if ($_REQUEST['arancel_ano_ini_cheque'] == "")       { $_REQUEST['arancel_ano_ini_cheque'] = $ANO_MATRICULA; }
if ($_REQUEST['arancel_mes_ini_pagare_coleg'] == "") { $_REQUEST['arancel_mes_ini_pagare_coleg'] = $mes_actual; } 
//if ($_REQUEST['arancel_cuotas_pagare_coleg'] == "")  { $_REQUEST['arancel_cuotas_pagare_coleg'] = $aranceles[0]['cuotas']; }
if ($_REQUEST['arancel_cuotas_pagare_coleg'] == "")  { $_REQUEST['arancel_cuotas_pagare_coleg'] = 10; }
if ($_REQUEST['arancel_ano_ini_pagare_coleg'] == "") { $_REQUEST['arancel_ano_ini_pagare_coleg'] = $ANO_MATRICULA; }
//if (time() < strtotime("2009-12-31")) { $_REQUEST['cod_beca_mat'] = "UMC"; $_REQUEST['monto_beca_mat'] = "75000"; }

$PORCs_BECA_MAT = array(array('id' =>  10, 'nombre' => "10%"),
                        array('id' =>  20, 'nombre' => "20%"),
                        array('id' =>  30, 'nombre' => "30%"),
                        array('id' =>  40, 'nombre' => "40%"),
                        array('id' =>  50, 'nombre' => "50%"),
                        array('id' =>  60, 'nombre' => "60%"),
                        array('id' =>  70, 'nombre' => "70%"),
                        array('id' =>  80, 'nombre' => "80%"),
                        array('id' =>  90, 'nombre' => "90%"),
                        array('id' => 100, 'nombre' => "100%"));

$PORCs_BECA_ARANCEL = array(array('id' =>  20, 'nombre' => "20%"),
                            array('id' =>  25, 'nombre' => "25%"),
                            array('id' =>  30, 'nombre' => "30%"),
                            array('id' =>  35, 'nombre' => "35%"),
                            array('id' =>  40, 'nombre' => "40%"),
                            array('id' =>  41, 'nombre' => "41%"),
                            array('id' =>  42, 'nombre' => "42%"),
                            array('id' =>  50, 'nombre' => "50%"),
                            array('id' =>  60, 'nombre' => "60%"),
                            array('id' =>  75, 'nombre' => "75%"),
                            array('id' =>  80, 'nombre' => "80%"),
                            array('id' => 100, 'nombre' => "100%"));

$regimen_mat = consulta_sql("SELECT regimen FROM carreras WHERE id=$id_carrera");
$regimen_mat = $regimen_mat[0]['regimen'];
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>

<form name="formulario" action="principal.php" method="post" onSubmit="if ((!verif_matric_finan()) || (!verif_arancel_finan())) { return false; }">
<input type="hidden" name="modulo"                value="<?php echo($modulo); ?>">
<input type="hidden" name="id_pap"                value="<?php echo($id_pap); ?>">
<input type="hidden" name="id_aval"               value="<?php echo($id_aval); ?>">
<input type="hidden" name="carrera_mat"           value="<?php echo($id_carrera); ?>">
<input type="hidden" name="jornada_mat"           value="<?php echo($jornada); ?>">
<input type="hidden" name="id_carrera"            value="<?php echo($id_carrera); ?>">
<input type="hidden" name="jornada"               value="<?php echo($jornada); ?>">
<input type="hidden" name="monto_matricula"       value="<?php echo($aranceles[0]['monto_matricula']); ?>">
<input type="hidden" name="monto_beca_mat"        value="">
<input type="hidden" name="monto_arancel"         value="<?php echo($aranceles[0]['monto_arancel']); ?>">
<input type="hidden" name="monto_arancel_credito" value="<?php echo($aranceles[0]['monto_arancel_credito']); ?>">
<input type="hidden" name="ano"                   value="<?php echo($ano); ?>">
<input type="hidden" name="semestre"              value="<?php echo($semestre	); ?>">
<input type="hidden" name="tipo_contrato"         value="<?php echo($tipo_contrato); ?>">
<input type="hidden" name="tipo"                  value="<?php echo($tipo_contrato); ?>">
<input type="hidden" name="id_emisor"             value="<?php echo($_SESSION['id_usuario']); ?>">

<div style="margin-top: 5px">
  <input type="submit" name="guardar" value="Guardar" tabindex="99">
  <input type="button" name="cancelar" value="Cancelar" onclick="history.back();">
</div>

<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style="margin-top: 5px">
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">
      Antecedentes Personales del Postulante</sup>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($pap[0]['rut']); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($pap[0]['id']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($pap[0]['nombre']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Dirección:</td>
    <td class='celdaValorAttr' colspan="3">
      <?php echo($pap[0]['direccion']); ?>, <?php echo($pap[0]['comuna']); ?>, <?php echo($pap[0]['region']); ?>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Beca Externa:</td>
    <td class='celdaValorAttr'>
      <select name='id_beca_externa' class="filtro">
        <option value=''>-- Ninguna --</option>
        <?php echo(select($becas_externas,$_REQUEST['id_beca_externa'])); ?>        
      </select><br>
      <sub>(Esta beca no genera descuentos ni es una forma de pago)</sub>
    </td>
	<td class='celdaNombreAttr'>N° Contrato Preimpreso:</td>
    <td class='celdaValorAttr'><input type="text" size="8" class="boton" name="id_contrato_preimp" value=""></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">
      Antecedentes del Responsable Financiero<br><sup>(Apoderado, Sostenedor,Aval o deudor directo)</sup> 
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
      <?php echo($aranceles[0]['carrera']); ?> <b>Jornada:</b> <?php echo($aranceles[0]['jornada']); ?> en el
      <select name="nivel" class="filtro">
        <?php echo(select($NIVELES,$_REQUEST['nivel'])); ?>
      </select> nivel
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center">Valores</td>
  </tr>
  <tr>    
    <td class='celdaValorAttr' colspan="4" align='center'>
      <b>Matrícula:</b> $<?php echo(number_format($aranceles[0]['monto_matricula'],0,',','.')); ?>&nbsp;&nbsp;&nbsp;
      <b>Arancel contado:</b> $<?php echo(number_format($aranceles[0]['monto_arancel'],0,',','.')); ?>&nbsp;&nbsp;&nbsp;
      <b>Arancel crédito:</b> $<?php echo(number_format($aranceles[0]['monto_arancel_credito'],0,',','.')); ?>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center">Descuentos (Matrícula y/o Arancel)</td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'><u>Beca (Matrícula):</u></td>
    <td class='celdaValorAttr' colspan="3">
      <select name='cod_beca_mat' onChange="becas_mat(this.value);" class="filtro">
        <option value=''>-- Ninguna --</option>
        <?php echo(select($becas_mat,$_REQUEST['cod_beca_mat'])); ?>        
      </select>
      <!-- Monto: $<input type='text' class='montos' size='10' name='monto_beca_mat_old' value="<?php echo($_REQUEST['monto_beca_mat']); ?>" 
                     onKeyUp="puntitos(this,this.value.charAt(this.value.length-1),'monto_beca_mat')"
                     onBlur="if (this.value != '') { formulario.porc_beca_mat.value=''; } calc_matric();" disabled> ó -->
      <span id="porc_beca_mat" style="display: none">
      Porcentaje: <!-- <input type='text' class="montos" size="2" name='porc_beca_mat_old' value="<?php echo($_REQUEST['porc_beca_mat']); ?>" 
                         onBlur="if (this.value != '') { formulario.monto_beca_mat.value=''; } calc_matric();" disabled>% -->
      <select name='porc_beca_mat' onChange="calc_matric();"  class="filtro" disabled>
        <option value=''>--</option>
        <?php echo(select($PORCs_BECA_MAT,$_REQUEST['porc_beca_mat'])); ?>        
      </select>
      </span>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Convenio (Arancel):</u></td>
    <td class='celdaValorAttr' colspan="3">
      <select name='id_convenio' onChange="convenio(this.value);" class="filtro" style="max-width: none">
        <option value=''>-- Ninguno --</option>
			<?php echo(select($convenios,$_REQUEST['id_convenio'])); ?>        
      </select>    
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Beca (Arancel):</u></td>
    <td class='celdaValorAttr' colspan="3">
      <select name='id_beca_arancel' onChange="becas(this.value);" class="filtro" style="max-width: none">
		<option value=''>-- Ninguna --</option>
		<?php echo(select($becas,$_REQUEST['id_beca_arancel'])); ?>        
      </select>
      <span id="porc_beca_arancel" style="display: none">
<?php
	switch ($regimen_mat) {
		case "POST-GD":
		case "POST-TD":
		case "DIP-D":
?>
      Monto: $<input type='text' class="montos" size="10" name='monto_beca_arancel' value="<?php echo($_REQUEST['monto_beca_arancel']); ?>"
                     onkeyup="puntitos(this,this.value.charAt(this.value.length-1),'monto_beca_arancel')"
                     onBlur="if (this.value != '') { formulario.porc_beca_arancel.value=''; } calc_arancel();" disabled> ó

      Porcentaje: <input type='text' class="montos" size="2" name='porc_beca_arancel' value="<?php echo($_REQUEST['porc_beca_arancel']); ?>"
                         onBlur="if (this.value != '') { formulario.monto_beca_arancel.value=''; } calc_arancel();" disabled>%
<?php
			break;
		default:
?>
      <input type="hidden" name="monto_beca_arancel" value="">
      Porcentaje: 
      <select name='porc_beca_arancel' onChange="calc_arancel();" class="filtro" disabled>
        <option value=''>--</option>
        <?php echo(select($PORCs_BECA_ARANCEL,$_REQUEST['porc_beca_arancel'])); ?>        
      </select>
<?php
	}
?>
      </span>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center">
      Financiamiento:
      <select name='financiamiento' onChange="finan()">
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
      $<input type="text" size="6" style="border: none" class="montos" name="matric_finan" value="0" readonly>
      <input type="button" size="5" value="Calcular" onClick="calc_matric();"
    </td>
    <td class='celdaNombreAttr'><u>Monto a Financiar:</u></td>
    <td class='celdaValorAttr'>
      $<input type="text" size="7" style="border: none" class="montos" name="arancel_finan" value="0" readonly>
      <input type="button" size="5" name="calcular" value="Calcular" onClick="calc_arancel();"
    </td>
  </tr>

<script>
function calc_matric() {
	var matric_rebajada = parseInt(formulario.monto_matricula.value);
	var monto_matricula = parseInt(formulario.monto_matricula.value);
	var monto_beca_mat  = parseInt(formulario.monto_beca_mat.value.replace('.',''));
	var porc_beca_mat   = parseFloat(formulario.porc_beca_mat.value);
	var monto_beca=0;
	
	if (formulario.cod_beca_mat.value != '') {
		if (!isNaN(monto_beca_mat) && isNaN(porc_beca_mat)) {
			if (monto_beca_mat > monto_matricula) {
				alert("No es posible otorgar una Beca de Matrícula superior al Valor de Matrícula");
				formulario.monto_beca_mat.value = null;
			} else {
				matric_rebajada = matric_rebajada - monto_beca_mat;
			}
		}
		if (isNaN(monto_beca_mat) && !isNaN(porc_beca_mat)) {
			if (porc_beca_mat > 100) {
				alert("No es posible otorgar una Beca de Matrícula superior al 100%");
				formulario.porc_beca_mat.value = null;
			} else {
				matric_rebajada = matric_rebajada - Math.round(monto_matricula * (porc_beca_mat/100));
			}
		}
	}
	formulario.matric_finan.value = matric_rebajada;
	puntitos(formulario.matric_finan,formulario.matric_finan.value.charAt(formulario.matric_finan.value.length-1),formulario.matric_finan.name);

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
				var monto_beca_arancel = parseInt(formulario.monto_beca_arancel.value.replace('.','').replace('.',''));
				if (monto_beca_arancel > arancel_rebajado) {
					alert("No es posible otorgar una Beca de Arancel superior al valor de éste");
				} else {					
					monto_beca_arancel = formulario.monto_beca_arancel.value;
					arancel_rebajado = arancel_rebajado - monto_beca_arancel.replace('.','').replace('.','');
				}
			}
			if (formulario.monto_beca_arancel.value == '' && formulario.porc_beca_arancel.value != '') {
				if (formulario.porc_beca_arancel.value > 100) {
					alert("No es posible otorgar una Beca de Arancel superior al 100%");
				} else {
					arancel_rebajado = arancel_rebajado - Math.round(arancel_rebajado * (formulario.porc_beca_arancel.value/100));
				}
			}
		}
	}
	formulario.arancel_finan.value = arancel_rebajado;
	puntitos(formulario.arancel_finan,formulario.arancel_finan.value.charAt(formulario.arancel_finan.value.length-1),formulario.arancel_finan.name);
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
	if (id_beca_arancel != '') {
		formulario.monto_beca_arancel.disabled=false;		
		formulario.porc_beca_arancel.disabled=false;
		document.getElementById("porc_beca_arancel").style.display='';
		//formulario.porc_beca_arancel.required=true;
	} else {
		formulario.monto_beca_arancel.disabled=true;
		formulario.monto_beca_arancel.value=null;
		formulario.porc_beca_arancel.disabled=true;
		formulario.porc_beca_arancel.value=null;
		document.getElementById("porc_beca_arancel").style.display='none';
		formulario.porc_beca_arancel.required=false;
	}
	calc_arancel();
}	

function becas_mat(cod_beca_mat) {
	if (cod_beca_mat != '') {
		//formulario.monto_beca_mat.disabled=false;
		formulario.porc_beca_mat.disabled=false;
		document.getElementById("porc_beca_mat").style.display='';
		formulario.porc_beca_mat.required=true;
	} else {
		//formulario.monto_beca_mat.disabled=true;
		//formulario.monto_beca_mat.value=null;
		formulario.porc_beca_mat.disabled=true;
		formulario.porc_beca_mat.value=null;
		document.getElementById("porc_beca_mat").style.display='none';
		formulario.porc_beca_mat.required=false;
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
	var total_matric_finan=0;
	var mat_efectivo  = formulario.mat_efectivo.value,
	    mat_cheque    = formulario.mat_cheque.value,
	    mat_tarj_cred = formulario.mat_tarj_cred.value
	    diferencia    = 0;
	    
	total_matric_finan = mat_efectivo.replace('.','')*1 +
	                     mat_cheque.replace('.','')*1 +
	                     mat_tarj_cred.replace('.','')*1;
	
	diferencia = formulario.matric_finan.value.replace('.','')*1 - total_matric_finan;
	 
	if (total_matric_finan == formulario.matric_finan.value.replace('.','')*1) {
		return true;
	} else {
		alert("El monto a financiar de la matrícula no está completo o hay un error lógico. Actualmente los valores ingresados suman $"+total_matric_finan+" restando financiar un monto de $"+diferencia);
		return false;
	}
}

function verif_arancel_finan() {
	var total_arancel_finan=0;
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
	
	diferencia = formulario.arancel_finan.value.replace('.','').replace('.','')*1 - total_arancel_finan;
	
	if (total_arancel_finan == formulario.arancel_finan.value.replace('.','').replace('.','')*1) {
		return true;
	} else {
		alert("El monto a financiar del arancel no está completo o hay un error lógico. Actualmente los valores ingresados suman $"+total_arancel_finan+" restando financiar un monto de $"+diferencia);
		return false;
	}
}

function mat_forma_pago(campo) {
	var x;
	
	for (x=0;x<formulario.length;x++) {
		if (formulario.elements[x].name.substr(0,4) == "mat_") {
			if (formulario.elements[x].name == campo.name) {
				campo.value = formulario.matric_finan.value;
			} else {
				formulario.elements[x].value = null;
			}
		}
	}
}

/*
function arancel_forma_pago(campo) {
	var x,y;
	var formas_pago = ['arancel_efectivo','arancel_cheque','arancel_pagare_coleg','arancel_cred_interno','arancel_tarjeta_credito'];	
	var arancel_total_finan = 0;
	var arancel_xfinan = 0;
	var arancel_finan = parseInt(formulario.arancel_finan.value.replace('.','').replace('.',''));
	
	y=0;
	for (x=0;x<formulario.length;x++) {
		if (formas_pago[y] == formulario.elements[x].name) {
			arancel_total_finan += formulario.elements[x].value;
			y++;
		}
	}
	//alert(arancel_total_finan);
	if (arancel_total_finan >= arancel_finan) {
		arancel_xfinan = 0;
	} else {
		arancel_xfinan = arancel_finan - arancel_total_finan;
	}
	
	for (y=0;y<formulario.length;y++) {
		for (x=0;x<formas_pago.length;x++) {
			if (formas_pago[x] == campo.name) {
				if (arancel_xfinan == arancel_finan) {
					campo.value = arancel_finan;
				} else {
					campo.value = arancel_xfinan;
				}
				puntitos(campo,campo.value.charAt(campo.value.length-1),campo.name);
			} else {
				if (formas_pago[x] == formulario.elements[y].name && formulario.elements[y] == '') {
					formulario.elements[y].value = null;
				}
			}
		}
	}
}
*/
finan();
		
</script>  

  <tr>
    <td class='celdaNombreAttr'>Efectivo/Transferencia:</td>
    <td class='celdaValorAttr'>
      $<input type='text' name='mat_efectivo' class='montos' size='6' onClick="mat_forma_pago(this);" value="<?php echo($_REQUEST['mat_efectivo']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
    </td>
    <td class='celdaNombreAttr'>Efectivo/Transferencia:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='7' name='arancel_efectivo' onClick="arancel_forma_pago(this);" value="<?php echo($_REQUEST['arancel_efectivo']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cheque(s):</td>
    <td class='celdaValorAttr'>
      $<input type='text' name='mat_cheque' class='montos' size='6' onClick="mat_forma_pago(this);" value="<?php echo($_REQUEST['mat_cheque']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
      Cant.: <input type='text' size='2' name='mat_cant_cheques' value="<?php echo($_REQUEST['mat_cant_cheques']); ?>">
    </td>  
    <td class='celdaNombreAttr'>Cheque(s):</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='7' name='arancel_cheque' onClick="arancel_forma_pago(this);" value="<?php echo($_REQUEST['arancel_cheque']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)"
              onBlur="if (this.value != '') { document.getElementById('arancel_cheque_1er_venc').style.display=''; } else { document.getElementById('arancel_cheque_1er_venc').style.display='none'; }">
      <span id="arancel_cheque_1er_venc" style="display: none">
      <sub>
        Cant.: <select name='arancel_cant_cheques'><?php echo(select($CUOTAS,$_REQUEST['arancel_cant_cheques'])); ?></select><br>
      </sub>
      <table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" align="center">
        <tr>
          <td class='celdaNombreAttr'><sub>Día Venc:</sub></td>
          <td class='celdaNombreAttr'><sub>Mes inicio:</sub></td>
          <td class='celdaNombreAttr'><sub>Año inicio:</sub></td></tr>
        <tr>
          <td class='celdaValorAttr'><select name='arancel_diap_cheque'><?php echo(select($dias_pago,$_REQUEST['arancel_diap_cheque'])); ?></select></td>
          <td class='celdaValorAttr'><select name="arancel_mes_ini_cheque"><?php echo(select($meses_palabra,$_REQUEST['arancel_mes_ini_cheque'])); ?></select></td>
          <td class='celdaValorAttr'><select name="arancel_ano_ini_cheque"><?php echo(select($anos_ini,$_REQUEST['arancel_ano_ini_cheque'])); ?></select></td>
        </tr>
      </table>
      </span>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tarjetas de Crédito:</td>
    <td class='celdaValorAttr'>
      $<input type='text' name='mat_tarj_cred' class='montos' size='6' onClick="mat_forma_pago(this);" value="<?php echo($_REQUEST['mat_tarj_cred']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
      Cant.: <input type='text' size='2' name='mat_cant_tarj_cred' value="<?php echo($_REQUEST['mat_cant_tarj_cred']); ?>">
    </td>
    <td class='celdaNombreAttr'>Pagaré Colegiatura:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='7' name='arancel_pagare_coleg' onClick="arancel_forma_pago(this);" value="<?php echo($_REQUEST['arancel_pagare_coleg']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)" 
              onBlur="if (this.value != '') { document.getElementById('arancel_pagare_coleg_1er_venc').style.display=''; } else { document.getElementById('arancel_pagare_coleg_1er_venc').style.display='none'; }">              
      <span id="arancel_pagare_coleg_1er_venc" style="display: none">
      <sub>
        Cuotas: <select name='arancel_cuotas_pagare_coleg'><?php echo(select($CUOTAS,$_REQUEST['arancel_cuotas_pagare_coleg'])); ?></select><br>
      </sub>
      <table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" align="center">
        <tr>
          <td class='celdaNombreAttr'><sub>Día pago:</sub></td>
          <td class='celdaNombreAttr'><sub>Mes inicio:</sub></td>
          <td class='celdaNombreAttr'><sub>Año inicio:</sub></td>
        </tr>
        <tr>
          <td class='celdaValorAttr'><select name='arancel_diap_pagare_coleg'><?php echo(select($dias_pago,$_REQUEST['arancel_diap_pagare_coleg'])); ?></select></td>
          <td class='celdaValorAttr'><select name="arancel_mes_ini_pagare_coleg"><?php echo(select($meses_palabra,$_REQUEST['arancel_mes_ini_pagare_coleg'])); ?></select></td>
          <td class='celdaValorAttr'><select name="arancel_ano_ini_pagare_coleg"><?php echo(select($anos_ini,$_REQUEST['arancel_ano_ini_pagare_coleg'])); ?></select></td>
        </tr>
      </table>
      </span>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td class='celdaNombreAttr'>Crédito Interno:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='7' name='arancel_cred_interno' onClick="arancel_forma_pago(this);" value="<?php echo($_REQUEST['arancel_cred_interno']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">                      
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td class='celdaNombreAttr'>Tarjeta de Crédito:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='7' name='arancel_tarjeta_credito' onClick="arancel_forma_pago(this);" value="<?php echo($_REQUEST['arancel_tarjeta_credito']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)"
              onBlur="if (this.value != '') { document.getElementById('arancel_tarj_credito_cuotas').style.display=''; } else { document.getElementById('arancel_tarj_credito_cuotas').style.display='none'; }">              
      <span id="arancel_tarj_credito_cuotas" style="display: none">
        Cuotas: <select name='arancel_cant_tarj_cred'><?php echo(select($CUOTAS,$_REQUEST['arancel_cant_tarj_cred'])); ?></select><br>
      </span>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td class='celdaNombreAttr'>Total Financiado:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='7' name='arancel_total_finan'>
    </td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="4" align="center">
      <b>Cant:</b> Se refiere a Cantidad de Cheques o Cantidad de Cuotas de Tarjetas
    </td>
  </tr>
</table>

</form>


