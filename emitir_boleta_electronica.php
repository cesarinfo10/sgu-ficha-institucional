<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$rut        = $_REQUEST['rut'];
$validar    = $_REQUEST['validar'];
$rut_valido = false;

$nro_boleta_e = consulta_sql("SELECT last_value AS id FROM finanzas.pagos_nro_boleta_e_seq;");
$nro_boleta_e = $nro_boleta_e[0]['id'];
$folios = consulta_sql("SELECT folio_inicial,folio_final FROM finanzas.folios_doctos_pago WHERE tipo_docto='Boleta Electrónica' AND activo");
$fecha_max = date("Y-m-d");
$fecha_min = date("Y-m-d",strtotime('yesterday'));

$problemas = false;
if (count($folios) == 0) {
	$problemas = true;
	echo(msje_js("ERROR: No hay rangos de folios activos. \\n\\n"
	            ."No es posible emitir boletas electrónicas"));
} else {	            
	if ($nro_boleta_e+1 < $folios[0]['folio_inicial']) {
		$problemas = true;
		echo(msje_js("ERROR: El número de boleta electrónica está fuera de rango. "
					."Es posible que se encuentre activo un rango sin terminar de "
					."utilizar el rango anterior \\n\\n"
					."Debe corregir el rango de folios activo. \\n\\n"
					."N° Boleta último: $nro_boleta_e \\n"
					."Folio Inicial: {$folios[0]['folio_inicial']} \\n"
					."Folio Final: {$folios[0]['folio_final']}"));
	}

	if ($nro_boleta_e >= $folios[0]['folio_final']) {

		$problemas = true;
		echo(msje_js("ERROR: El rango de folios de boletas electrónicas está terminado. \\n\\n"
					."No es posible emitir nuevas boletas electrónicas. "
					."Debe solicitar al SII el timbraje electrónico de un nuevo rango "
					."y registrarlo en SGU y el ERP. \\n\\n"
					."N° Boleta último: $nro_boleta_e \\n"
					."Folio Inicial: {$folios[0]['folio_inicial']} \\n"
					."Folio Final: {$folios[0]['folio_final']}"));

	} elseif ($nro_boleta_e > $folios[0]['folio_final']-2000) {

		$boletas_restantes = $folios[0]['folio_final'] - $nro_boleta_e;
		echo(msje_js("ATENCIÓN: El rango de folios de boletas electrónicas está próximo a terminar. \\n\\n"
					."Quedan $boletas_restantes folios para emitir. "
					."Debe solicitar al SII un timbraje de un nuevo rango y registrarlo en SGU y el ERP. \\n\\n"
					."N° Boleta último: $nro_boleta_e \\n"
					."Folio Inicial: {$folios[0]['folio_inicial']} \\n"
					."Folio Final: {$folios[0]['folio_final']}"));
	}
}

if ($problemas) {
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

if ($_REQUEST["fecha_pago"] == "") { $_REQUEST["fecha_pago"] = date("Y-m-d"); }

if ($_REQUEST['validar'] == "Validar" && $rut <> "") {

	$SQL_id_alumno = "SELECT id FROM vista_alumnos WHERE trim(rut)='$rut'";

	$SQL_alumno = "SELECT char_comma_sum(id::text) AS id,nombre FROM vista_alumnos WHERE trim(rut)='$rut' GROUP BY nombre";
	$alumno     = consulta_sql($SQL_alumno);

	$SQL_pap = "SELECT id,nombre FROM vista_pap WHERE rut='$rut'";
	$pap     = consulta_sql($SQL_pap);

	$id_alumno = $id_pap = 0;

	if (count($alumno) == 0 && count($pap) > 0) {
		echo(msje_js("ATENCIÓN: El RUT ingresado corresponde a un postulante que aún NO ES ALUMNO.\\n"
		            ."Se procede de todas maneras."));
	}

	if (count($alumno) == 0 && count($pap) == 0) {
		echo(msje_js("El RUT ingresado no corresponde a un alumno o postulante. Intente nuevamente por favor."));
		echo(js("window.location='$enlbase=$modulo';"));
		exit;
	}
	if (count($pap) == 1) {
		$id_pap = $pap[0]['id'];
		$nombre = $pap[0]['nombre'];
	}
	if (count($alumno) >= 1) {
		$id_alumno = implode(",",array_column($alumno,"id"));
		//$id_alumno = $alumno[0]['id'];
		$nombre    = $alumno[0]['nombre'];
	}

	$SQL_solic_excep = "SELECT s.id FROM gestion.solicitudes s LEFT JOIN gestion.solic_tipos st ON st.id=s.id_tipo WHERE st.alias='solic_excep_finan' AND id_alumno IN ($id_alumno) AND estado IN ('Presentada','En preparación')";
	//echo("<!-- $SQL_solic_excep -->\n");
	//echo("<!-- $SQL_alumno -->");
	if (count(consulta_sql($SQL_solic_excep)) > 0) { 
		echo(msje_js("ERROR: No se puede proseguir con el pago mientras exista una Excepción Financiera presentada."));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}

	$rut_valido = true;

	$cobros_oi = false;
/*
	if ($id_alumno <> "") {
		$SQL_cobros = "SELECT to_char(cob.fecha_venc,'DD-tmMon-YYYY') as fec_venc,cob.fecha_venc,g.nombre AS glosa,cob.monto,
							  cob.nro_cuota,'' AS id_contrato,'' AS ano,
							  CASE WHEN cob.pagado THEN 'Si' ELSE 'No' END AS pagado,
							  CASE WHEN cob.abonado THEN 'Si' ELSE 'No' END AS abonado,
							  cob.monto_abonado,cob.id AS id_cobro,'' AS id_pagare
					   FROM finanzas.cobros      AS cob
					   LEFT JOIN finanzas.glosas AS g ON g.id=cob.id_glosa
					   WHERE cob.id_alumno IN ($SQL_id_alumno) AND cob.fecha_venc <= now()::date AND NOT pagado
					   ORDER BY cob.fecha_venc,cob.id_contrato";
		$cobros     = consulta_sql($SQL_cobros);
		if (count($cobros) > 0) {
			$cobros_oi = true;
			echo(msje_js("ATENCIÓN: Este alumno tiene registrados Otros Cobros, "
			            ."los cuales deben ser pagados en primer lugar.\\n\\n"
			            ."Una vez pagados estos, se podrá seguir pagando los cobros de Matrículas y/o Aranceles."));
		}
	}
*/

	if (!$cobros_oi) {
		$SQL_contratos = "SELECT c.id AS id_contrato,c.tipo,car.nombre AS carrera,
								 CASE c.jornada WHEN 'D' THEN 'Diurna' ELSE 'Vespertina' END AS jornada,
								 CASE WHEN c.tipo='Semestral' THEN text(c.semestre)||'-'||text(c.ano) ELSE text(c.ano) END AS periodo,
								 pc.id AS id_pagare
						  FROM finanzas.contratos AS c
						  LEFT JOIN alumnos       AS a   ON a.id=c.id_alumno
						  LEFT JOIN pap                  ON pap.id=c.id_pap
						  LEFT JOIN carreras      AS car ON car.id=c.id_carrera
						  LEFT JOIN finanzas.pagares_colegiatura AS pc ON pc.id_contrato=c.id
						  WHERE c.estado IS NOT NULL AND (c.id_alumno IN ($SQL_id_alumno) OR c.id_pap=$id_pap)
						  ORDER BY c.fecha DESC";
	//	$contratos  = consulta_sql($SQL_contratos);
	//	if (count($contratos) == 0) {			
	//		echo(msje_js("El RUT ingresado no tiene contratos asociados. No se puede continuar."));
	//		echo(js("window.location='$enlbase=$modulo';"));
	//		exit;
	//	}
	
		$SQL_convenios_ci = "SELECT id FROM finanzas.convenios_ci WHERE id_alumno IN ($SQL_id_alumno) AND NOT nulo";

		$SQL_cobros = "SELECT to_char(cob.fecha_venc,'DD-tmMon-YYYY') as fec_venc,cob.fecha_venc,g.nombre AS glosa,cob.monto,
							  monto_uf,cob.nro_cuota,cob.id_contrato,cob.id_convenio_ci,
							  coalesce(c.ano,date_part('year',cci.fecha)) AS ano_docto,coalesce(pc.id,plci.id) AS id_pagare,
							  CASE WHEN cob.pagado THEN 'Si' ELSE 'No' END AS pagado,
							  CASE WHEN cob.abonado THEN 'Si' ELSE 'No' END AS abonado,
							  cob.monto_abonado,cob.id AS id_cobro
					   FROM finanzas.cobros                   AS cob
					   LEFT JOIN finanzas.glosas              AS g ON g.id=cob.id_glosa
					   LEFT JOIN finanzas.contratos           AS c ON c.id=cob.id_contrato
					   LEFT JOIN finanzas.pagares_colegiatura AS pc ON pc.id_contrato=cob.id_contrato
					   LEFT JOIN finanzas.convenios_ci        AS cci ON cci.id=cob.id_convenio_ci
					   LEFT JOIN finanzas.pagares_liqci       AS plci ON (plci.id_convenio_ci=cci.id AND plci.version=1)
					   WHERE (cob.id_contrato IN (SELECT id_contrato FROM ($SQL_contratos) AS foo) OR cob.id_convenio_ci IN ($SQL_convenios_ci) OR cob.id_alumno IN ($SQL_id_alumno))
						 AND cob.id_glosa NOT IN (21,22) AND NOT cob.pagado
					   ORDER BY cob.fecha_venc,cob.id_contrato";
		//var_dump($SQL_cobros);
		$cobros     = consulta_sql($SQL_cobros);
		if (count($cobros) == 0) {
			//echo(msje_js("El RUT ingresado tiene Contratos o Convenio(s) de Liquidación de Crédito Interno emitido(s), pero no tiene cobros asociados o bien estos se encuentran pagados."));
			echo(msje_js("ATENCIÓN: El RUT ingresado no tiene cobros impagos asociados a Contratos, Convenios LCI u otros. No se puede continuar."));
			echo(js("window.location='$enlbase=$modulo';"));
			exit;
		}
	}

	$HTML_cobros = "";
	$deuda_total = $deuda_vencida = 0;
	for ($y=0;$y<count($cobros);$y++) {			
		$HTML = "";
		extract($cobros[$y]);

		if ($pagado == "No" && $abonado == "No") { $deuda_total += $monto; }
		if ($pagado == "No" && $abonado == "Si") { $deuda_total += $monto-$monto_abonado; }

		$monto_f = number_format($monto,0,',','.');

		if ($monto_uf > 0) { $monto_uf_f = "[UF ".number_format($monto_uf,2,',','.')."]"; }
		
		$saldo = "";
		if ($abonado == "Si") { $saldo = "($".number_format($monto-$monto_abonado,0,',','.').")"; }
		
		if ($pagado == "Si") { $abonado = ""; }
		
		$id_pagare   = number_format($id_pagare,0,',','.');
		
		$nro_docto = "";
		if ($id_contrato > 0) {
			$nro_docto = "<a id='sgu_fancybox' href='$enlbase_sm=form_matricula_ver&id_contrato=$id_contrato#cobros' class='enlaces'>"
			           .    number_format($id_contrato,0,',','.')
			           . "</a>";
		}
		if ($id_convenio_ci > 0) {
			$nro_docto = "<a id='sgu_fancybox' href='$enlbase_sm=ver_convenio_ci&id_convenio_ci=$id_convenio_ci#cobros' class='enlaces'>"
			           .    number_format($id_convenio_ci,0,',','.')
			           . "</a>";
		}

		$HTML =  "<tr class='filaTabla'>\n"
			  .  "  <td class='textoTabla' align='right' style='color: #7F7F7F'>$id_cobro</td>\n"
			  .  "  <td class='textoTabla' align='center'><input type='checkbox' name='aId_cobro[$id_cobro]' id='aId_cobro[$id_cobro]'></td>\n"
//			  .  "  <td class='textoTabla' align='right'><label for='aId_cobro[$id_cobro]'>$fec_venc</label></td>\n"
			  .  "  <td class='textoTabla' align='right'>$fec_venc</td>\n"
			  .  "  <td class='textoTabla'>$glosa</td>\n"
			  .  "  <td class='textoTabla' align='right'>$nro_cuota</td>\n"
			  .  "  <td class='textoTabla' align='right'>$monto_uf_f $$monto_f</td>\n"
			  .  "  <td class='textoTabla' align='center'><span class='$abonado'>$abonado <small>$saldo</small></span></td>\n"
			  .  "  <td class='textoTabla' align='right'>$nro_docto</td>"
			  .  "  <td class='textoTabla' align='center'>$ano_docto</td>"
			  .  "  <td class='textoTabla' align='right'>$id_pagare</td>"
			  .  "</tr>\n";
		
		if (strtotime($fecha_venc) <= time()) { 
			$HTML_cobros_vencidos .= $HTML;
			if ($pagado == "No" && $abonado == "No") { $deuda_vencida += $monto; }
			if ($pagado == "No" && $abonado == "Si") { $deuda_vencida += $monto-$monto_abonado; }
		} else {
			$HTML_cobros .= $HTML;
		}
	}
	if ($HTML_cobros <> "" || $HTML_cobros_vencidos <> "") {
		//$_REQUEST['EF'] = $deuda_vencida;
		$deuda_vencida = number_format($deuda_vencida,0,',','.');
		$HTML_cobros_vencidos .= "<tr class='filaTabla'><td class='textoTabla' align='right' colspan='6'>"
							  .  "  <b><span class='No'>Deuda Vencida: $$deuda_vencida</span></b>"
							  .  "</td><td class='textoTabla' colspan='5'> </td></tr>\n";

		$deudaTotal = number_format($deuda_total,0,',','.');                      
		$HTML_cobros .= "<tr class='filaTabla'><td class='textoTabla' align='right' colspan='6'>"
					 .  "  <b>Deuda Total <small>(incluye Deuda Vencida)</small>: $$deudaTotal</b>"
					 . "</td><td class='textoTabla' colspan='5'> </td></tr>\n";
		
		if ($id_pagare <> "") { $id_pagare = " Pagaré Nº $id_pagare "; }

		$id_contrato = "<a href='$enlbase=form_matricula_ver&id_contrato=$id_contrato' target='_blank' class='enlaces'>$id_contrato</a>";

		$datos_contrato = "Nº $id_contrato $id_pagare<br> del Periodo $periodo de Tipo $tipo";
		
		$HTML_contratos .= tabla_contrato_cobros($datos_contrato)
						.  $HTML_cobros_vencidos
						.  $HTML_cobros
						.  "</table>\n";

	}
	
	
	$SQL_boleta = "SELECT nro_boleta FROM finanzas.pagos WHERE nro_boleta IS NOT NULL and id_cajero = {$_SESSION['id_usuario']} ORDER BY nro_boleta DESC LIMIT 1";
	$boleta     = consulta_sql($SQL_boleta);
	$ultima_nro_boleta = "***";
	if (count($boleta) > 0) { $ultima_nro_boleta = "Última boleta: " . $boleta[0]['nro_boleta']; }
	
}

if ($_REQUEST['pagar'] == "Registrar") {
	$id_alumno      = $_REQUEST['id_alumno'];
	$id_pap         = $_REQUEST['id_pap'];
	$fecha_pago     = $_REQUEST['fecha_pago'];
	$forma_pago     = $_REQUEST['forma_pago'];
	$monto_pago     = str_replace(".","",$_REQUEST['monto_pago']);
	$cant_cuotas    = $_REQUEST['cant_cuotas'];
	$aId_cobros     = $_REQUEST['aId_cobro'];
	$cod_operacion  = $_REQUEST['cod_operacion'];
	
	$ids_cobros = array();
	foreach ($aId_cobros AS $id_cobro => $valor) { $ids_cobros = array_merge($ids_cobros,array($id_cobro)); }
	$ids_cobros = implode(",",$ids_cobros);
	
	$boleta_valida = consulta_sql("SELECT nro_boleta FROM finanzas.pagos WHERE nro_boleta = $nro_boleta");
	if (count($boleta_valida) > 0) {
		echo(msje_js("Número de Boleta ya utilizado. Debe usar otro"));
		echo(js("history.back();"));
		exit;
	}	
	
	if ($forma_pago == "deposito" || $forma_pago == "tarj_debito" || $forma_pago == "tarj_credito") {
		$boleta_valida = consulta_sql("SELECT coalesce(nro_boleta_e,nro_boleta) as nro_docto FROM finanzas.pagos WHERE cod_operacion = '$cod_operacion'");
		if (count($boleta_valida) > 0) {
			echo(msje_js("ERROR: Código de operación ya utilizado en el documento {$boleta_valida[0]['nro_docto']}. Debe usar otro"));
			echo(js("history.back();"));
			exit;
		}	
	}
	
	$total_pago = $monto_pago;

	$SQL_id_alumno = "SELECT id FROM vista_alumnos WHERE trim(rut)='$rut'";
	
	$cobros_oi = false;	
/*
	if ($id_alumno <> "") {
		$cond_cobros = "WHERE c.id_alumno IN ($SQL_id_alumno) AND c.fecha_venc <= now()::date AND NOT pagado ";
		if ($ids_cobros <> "") { $cond_cobros = "WHERE c.id IN ($ids_cobros)"; }
		
		$SQL_cobros = "SELECT c.id,to_char(fecha_venc,'DD-tmMon-YYYY') as fecha_venc,g.nombre::varchar(30) AS glosa,monto,nro_cuota,id_contrato AS id_contrato_c,
							  monto_abonado,abonado
					   FROM finanzas.cobros      AS c
					   LEFT JOIN finanzas.glosas AS g ON g.id=c.id_glosa
					   $cond_cobros
					   ORDER BY c.fecha_venc,c.id_contrato";
		$cobros     = consulta_sql($SQL_cobros);
		if (count($cobros) > 0) { $cobros_oi = true; }
	}
*/
	if (!$cobros_oi) {	
		$SQL_contratos = "SELECT c.id 
						  FROM finanzas.contratos AS c
						  LEFT JOIN vista_alumnos AS va  ON va.id=c.id_alumno
						  LEFT JOIN alumnos       AS a   ON a.id=va.id
						  LEFT JOIN vista_pap     AS vp  ON vp.id=c.id_pap
						  LEFT JOIN pap                  ON pap.id=vp.id
						  LEFT JOIN carreras      AS car ON car.id=c.id_carrera
						  WHERE c.estado IS NOT NULL AND (c.id_alumno IN ($SQL_id_alumno) OR c.id_pap=$id_pap)
						  ORDER BY c.fecha DESC";

		$SQL_convenios_ci = "SELECT id FROM finanzas.convenios_ci WHERE id_alumno IN ($SQL_id_alumno) AND NOT nulo";

		$cond_cobros = "WHERE NOT pagado AND (c.id_contrato IN ($SQL_contratos) OR c.id_convenio_ci IN ($SQL_convenios_ci) OR c.id_alumno IN ($SQL_id_alumno)) AND c.id_glosa NOT IN (21,22)";
		if ($ids_cobros <> "") { $cond_cobros = "WHERE c.id IN ($ids_cobros)"; }

		$SQL_cobros = "SELECT c.id,to_char(fecha_venc,'DD-tmMon-YYYY') as fecha_venc,g.nombre::varchar(30) AS glosa,monto,monto_uf,nro_cuota,id_contrato AS id_contrato_c,
							  monto_abonado,abonado
					   FROM finanzas.cobros c
					   LEFT JOIN finanzas.glosas g ON g.id=c.id_glosa
					   $cond_cobros
					   ORDER BY c.fecha_venc,id_contrato";
		$cobros     = consulta_sql($SQL_cobros);
	}

	if (count($cobros) > 0) {
		$total_cobros = 0;
		$vencimientos = "Fecha            \\tGlosa  ".str_repeat("\\t",5)."Monto     \\tAccion \\n".str_repeat("-",130)."\\n";
		$monto_paga = $total_pago;
		for ($x=0;$x<count($cobros);$x++) {
			$vencimientos .= $cobros[$x]['fecha_venc'] . "\\t" . $cobros[$x]['glosa'];			
			if (strlen($cobros[$x]['glosa']) < 30) { $vencimientos .= str_repeat(" ",40-strlen($cobros[$x]['glosa'])); }
			if ($cobros[$x]['monto_abonado'] > 0) {
				$monto_cobro = $cobros[$x]['monto'] - $cobros[$x]['monto_abonado'];
				$total_cobros += $monto_cobro;
				$vencimientos .= "\\t $".number_format($monto_cobro,0,",",".");
			} else {
				$monto_cobro = $cobros[$x]['monto'];
				$total_cobros += $monto_cobro;
				$vencimientos .= "\\t $".number_format($monto_cobro,0,",",".");
			}
			
			if ($monto_cobro <= $monto_paga) { $accion = "Paga"; } 
			elseif ($monto_cobro > $monto_paga && $monto_paga > 0) { $accion = "Abona $".number_format($monto_paga,0,",","."); }
			else { $accion = "Nada"; }
			
			$monto_paga -= $monto_cobro;
			
			$vencimientos .= "\\t$accion\\n";
		}

		$val_pago = md5($rut);
		$enl_val_pago = $_SERVER['REQUEST_URI']."&val_pago=$val_pago";
		
		if ($_REQUEST['val_pago'] == "") {
		
			if ($ids_cobros <> "" && $total_pago > $total_cobros) {
				echo(msje_js("Se ha seleccionado cobros/vencimientos específicos a pagar, pero el total a pagar supera los cobros seleccionados.\\n\\n"
							."NO SE PUEDE CONTINUAR"));
				exit;		
			} elseif ($ids_cobros <> "" && $total_pago <= $total_cobros) {
				$msje_abono = "";
				if ($ids_cobros <> "" && $total_pago < $total_cobros) { $msje_abono = ", por lo que se realizará un ABONO a el cobro indicado"; }
				
				$msje_pago = "ATENCIÓN: Ha seleccionado cobros específicos para pagar con los siguientes vencimientos:\\n\\n"
							. $vencimientos . "\\n"
							."Seleccionó cobros por un total de $".number_format($total_cobros,0,",",".")."\\n\\n"
							."El monto total del pago es de $".number_format($total_pago,0,",",".").$msje_abono;
				echo(confirma_js($msje_pago,$enl_val_pago,"#"));
				echo(js("parent.jQuery.fancybox.close();"));
				exit;
			}

			$msje_abono = "";
			if ($ids_cobros == "") {
				if ($total_pago < $total_cobros) { $msje_abono = ", por lo que se realizará un ABONO a el cobro indicado"; }
				$msje_pago = "ATENCIÓN: Se procederá a pagar los siguiente vencimientos:\\n\\n"
							. $vencimientos . "\\n"
							."Cobros por un total de $".number_format($total_cobros,0,",",".")."\\n\\n"
							."El monto total del pago es de $".number_format($total_pago,0,",",".").$msje_abono;
				echo(confirma_js($msje_pago,$enl_val_pago,"#"));
				echo(js("parent.jQuery.fancybox.close();"));
				exit;
			}
		
		}
		
	}

	if ($_REQUEST['val_pago'] == md5($rut)) {

		$EF = $DE = $CH = $CHF = $TR = $TC = $TD = $cant_cheques = $cant_cuotas_TC = "null";
		switch ($forma_pago) {
			case "efectivo":
				$EF = $monto_pago;
				break;
			case "deposito":
				$DE = $monto_pago;
				break;
			case "cheque":
				$CH = $monto_pago;
				break;
			case "cheque_afecha":
				$CHF = $monto_pago;
				$cant_cheques = $cant_cuotas;
				break;
			case "transferencia":
				$TR = $monto_pago;
				break;
			case "tarj_credito":
				$TC = $monto_pago;
				$cant_cuotas_TC = $cant_cuotas;
				break;
			case "tarj_debito":
				$TD = $monto_pago;
				break;
		}

		$SQL_insPago = "INSERT INTO finanzas.pagos (efectivo,deposito,cheque,cheque_afecha,cant_cheques,transferencia,tarj_credito,cant_cuotas_tarj_credito,tarj_debito,nro_boleta_e,id_cajero,cod_operacion,fecha)
							VALUES ($EF,$DE,$CH,$CHF,$cant_cheques,$TR,$TC,$cant_cuotas_TC,$TD,nextval('finanzas.pagos_nro_boleta_e_seq'::regclass),{$_SESSION['id_usuario']},'$cod_operacion','$fecha_pago'::date);";
		if (consulta_dml($SQL_insPago) > 0) {
			$pago    = consulta_sql("SELECT last_value AS id FROM finanzas.pagos_id_seq;");
			$id_pago = $pago[0]['id'];
			$nro_boleta_e = consulta_sql("SELECT last_value AS id FROM finanzas.pagos_nro_boleta_e_seq;");
			$nro_boleta_e = $nro_boleta_e[0]['id'];
		} else {
			echo(msje_js("Ha ocurrido un error, no ha podido guardarse la boleta.\\n\\n "
						."Es muy probable que el Nº de Boleta ya se encuentre registrado.\\n\\n "
						."Por favor comunique este error al Departamento de Informática."));
			exit;
		}	
		
		$SQL_updCobro = "";
		$ids_cobros = array();
		$tot_pago = $total_pago;
		//var_dump($cobros);

		for ($x=0;$x<count($cobros);$x++) {
			$monto = $cobros[$x]['monto'];
			
			$ids_cobros[$x] = $cobros[$x]['id'];
			
			if ($cobros[$x]['abonado'] == "t") { $monto -= $cobros[$x]['monto_abonado']; }
			
			if ($tot_pago < $monto) {
				$monto = $tot_pago;
				$SQL_updCobro .= "UPDATE finanzas.cobros SET abonado=true,monto_abonado=coalesce(monto_abonado,0)+$tot_pago WHERE id={$cobros[$x]['id']};
								  INSERT INTO finanzas.pagos_detalle VALUES ($id_pago,{$cobros[$x]['id']},$monto);";
				$tot_pago -= $monto;
				break;
			}
			if ($tot_pago == $monto) {
				$SQL_updCobro .= "UPDATE finanzas.cobros SET pagado=true,abonado=false,monto_abonado=null WHERE id={$cobros[$x]['id']};
								  INSERT INTO finanzas.pagos_detalle VALUES ($id_pago,{$cobros[$x]['id']},$monto);";
				$tot_pago -= $monto;
				break;
			}
			if ($tot_pago > $monto) {
				$SQL_updCobro .= "UPDATE finanzas.cobros SET pagado=true,abonado=false,monto_abonado=null WHERE id={$cobros[$x]['id']};
								  INSERT INTO finanzas.pagos_detalle VALUES ($id_pago,{$cobros[$x]['id']},$monto);";
				$tot_pago -= $monto;
			}		
		}
		
		consulta_dml($SQL_updCobro);
		//echo($SQL_updCobro);
		actualiza_morosidad($rut);
		echo(msje_js("Pago registrado correctamente. Imprima el comprobante que se emite a continuación"));
		error_reporting(6);
		include("integracion/boletas.php");
		//api_manager_agregar_alumno($rut);

		$respuesta_api = null;

		// Enviar a la API: 24-2-23 se desconecta la API por problemas de operacion de Manager 24-2-23
		api_manager_agrmod_alumno($rut);
		$respuesta_api = api_manager_crear_boleta($id_pago);
		
		$bol_e_cod_erp = "null";
		if (is_numeric($respuesta_api)) {
			echo(msje_js("Boleta aceptada por la API Manager"));
			$bol_e_cod_erp = intval($respuesta_api);
		} else {
			echo(msje_js("ERROR: la boleta NO ha sido aceptada por la API Manager:\\n\\n"
			            .$respuesta_api));
		}
		consulta_dml("UPDATE finanzas.pagos SET bol_e_respuesta_api = '$respuesta_api',bol_e_cod_erp = $bol_e_cod_erp WHERE id=$id_pago");
		comp_pago_email($id_pago);
		echo(js("location.href='$enlbase_sm=ver_pago&id_pago=$id_pago';"));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	
	} else {
		echo(msje_js("Ha fallado la comprobación del pago. NO se puede registrar"));
	}

}

$cuotas_TC = array();
for ($x=0;$x<12;$x++) { $cuotas_TC[$x] = array('id'=>$x+1,'nombre'=>$x+1); }

$FORMAS_PAGO = array(array('id' => "efectivo",      'nombre' => "Efectivo"),
                     array('id' => "deposito",      'nombre' => "Depósito"),
                     array('id' => "cheque",        'nombre' => "Cheque al Día"),
                     array('id' => "cheque_afecha", 'nombre' => "Cheque(s) a Fecha"),
                     array('id' => "transferencia", 'nombre' => "Transferencia"),
                     array('id' => "tarj_debito",   'nombre' => "Tarjeta de Débito"),
                     array('id' => "tarj_credito",  'nombre' => "Tarjeta de Crédito"));
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>

<?php	if ($rut_valido) { ?>
<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="get"
      onSubmit="if (!valida_pago() || !valida_rut(formulario.rut.value)) { return false; }">

<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="rut" value="<?php echo($rut); ?>">
<input type="hidden" name="id_alumno" value="<?php echo($id_alumno); ?>">
<input type="hidden" name="id_pap" value="<?php echo($id_pap); ?>">
<input type="hidden" name="deuda_total" value="<?php echo($deuda_total); ?>">
<input type="hidden" name="total_pago" value="0">

<table style='margin-top: 5px'>
 <tr>
  <td>
	<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" width='100%'>
	  <tr class='filaTituloTabla'><td class='tituloTabla' colspan="6" style="text-align: center; ">Antecedentes Personales del Alumno(a)</td></tr>
	  <tr class='filaTituloTabla'>
		<td class='tituloTabla' style='text-align: right'>RUT:</td>
		<td class='celdaValorAttr' bgcolor='#FFFFFF'><?php echo($rut); ?></td>
		<td class='tituloTabla' style='text-align: right'>ID:</td>
		<td class='celdaValorAttr' bgcolor='#FFFFFF'><?php echo($id_alumno); ?></td>
		<td class='tituloTabla' style='text-align: right'>Nombre:</td>
		<td class='celdaValorAttr' bgcolor='#FFFFFF'><?php echo($nombre); ?></td>
	  </tr>
	</table>
	<?php echo($HTML_contratos); ?>	
	<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" align='right' style='margin-top: 5px'>
	  <tr>
		<td class='celdaNombreAttr'>N° Boleta:</td>
		<td class='celdaValorAttr'>* Automático *</td>
		<td class='celdaNombreAttr'>Fecha:</td>
		<td class='celdaValorAttr'>
		  <input type='date' min="<?php echo($fecha_min); ?>" max="<?php echo($fecha_max); ?>" name='fecha_pago' class="boton" value="<?php echo($_REQUEST['fecha_pago']); ?>"><br>
		</td>
	  </tr>
	  <tr>
		<td class='celdaNombreAttr'>Cód. Operación:</td>
		<td class='celdaValorAttr' colspan="3"><input type="text" name="cod_operacion" class="boton" value="<?php echo($_REQUEST['cod_operacion']); ?>" disabled></td>
	  </tr>	
	  <tr>
		<td class='celdaNombreAttr'>
		  <select name="forma_pago" class='filtro' onChange="acciones_forma_pago(this.value);" required>
			<option value=''>Forma de Pago:</option>
			<?php echo(select($FORMAS_PAGO,$_REQUEST['forma_pago'])); ?>
		  </select>:
		</td>
		<td class='celdaValorAttr' colspan="3">
		  $<input type='text' class='montos' min="1" size='7' name='monto_pago' value="<?php echo($_REQUEST['monto_pago']); ?>"
				  onBlur="puntitos(this,this.value.charAt(this.value.length-1),this.name); calc_total();" required>
		  <select name="cant_cuotas" style='display: none' class='filtro' disabled>
			<option value="">Cant:</option>
			<option>1</option>
			<option>2</option>
			<option>3</option>
			<option>4</option>
			<option>5</option>
			<option>6</option>
			<option>7</option>
			<option>8</option>
			<option>9</option>
			<option>10</option>
			<option>11</option>
			<option>12</option>
			<option>13</option>
			<option>14</option>
			<option>15</option>
			<option>16</option>
			<option>17</option>
			<option>18</option>
			<option>19</option>
			<option>20</option>
			<option>21</option>
			<option>22</option>
			<option>23</option>
			<option>24</option>
		  </select>
		</td>
	  </tr>    
	  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; "><input type='submit' name='pagar' value='Registrar' onClick="return valida_pago();"></td></tr>
	</table>
  </td>
 </tr>
</table>
</form>

<?php	} else { ?>
	
<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="get" onSubmit="return valida_rut(formulario.rut);">
  <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
  <table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
    <tr>
      <td class='celdaNombreAttr'>RUT:</td>
      <td class='celdaValorAttr'>
        <input type='text' size="12" name='rut' onChange="var valor=this.value;this.value=valor.toUpperCase();" tabindex="1" value="<?php echo($rut); ?>">
        <script>formulario.rut.focus();</script>
        <input type="submit" name="validar" value="Validar" tabindex="2">
      </td>
    </tr>
  </table>
</form>
<?php	} ?>

<!-- Fin: <?php echo($modulo); ?> -->

<?php
function tabla_contrato_cobros($datos_contrato) {
	$HTML = "<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
	           <tr class='filaTituloTabla'>
	             <td class='tituloTabla' rowspan='2' style='color: #7F7F7F'>Id</td>
	             <td class='tituloTabla' rowspan='2'>P.<br>E.</td>
	             <td class='tituloTabla' rowspan='2'>Fecha<br>Vencimiento</td>
	             <td class='tituloTabla' rowspan='2'>Glosa</td>
	             <td class='tituloTabla' rowspan='2'>Nº<br>Cuota</td>
	             <td class='tituloTabla' rowspan='2'>Monto</td>
	             <td class='tituloTabla' rowspan='2'>Abonado?<br><small>(saldo)</small></td>
	             <td class='tituloTabla' colspan='2'>Contrato</td>
	             <td class='tituloTabla' rowspan='2'>Nº<br>Pagaré</td>
	           </tr>
	           <tr class='filaTituloTabla'>
	             <td class='tituloTabla'>Nº</td>
	             <td class='tituloTabla'>Año</td>
	           </tr>";
	return $HTML;
}

function actualiza_morosidad($rut) {

	$SQL_contratos_alumno = "SELECT rut 
							 FROM (SELECT rut,sum(cant_cont) AS cant_cont
							       FROM (SELECT vcr.rut,(select count(cob.id) 
										                 from finanzas.cobros cob 
												         where cob.id_contrato=c.id 
													       and not pagado 
													       and monto-coalesce(monto_abonado,0) > 20000
													       and fecha_venc+'5 days'::interval < now()::date 
													       and id_glosa>1) as cant_cont
								         FROM finanzas.contratos       AS c 
								         LEFT JOIN carreras            AS car ON car.id=c.id_carrera
								         LEFT JOIN vista_contratos_rut AS vcr ON vcr.id=c.id
								         WHERE ano>=2013 AND estado IS NOT NULL AND vcr.rut='$rut') AS foo
								   GROUP BY rut) AS foo2
							 WHERE cant_cont=0";

	$SQL_upd_moroso = "UPDATE alumnos 
					   SET moroso_financiero=false
					   WHERE rut IN ($SQL_contratos_alumno);";
					   
	if (consulta_dml($SQL_upd_moroso) > 0) {
		echo(msje_js("ATENCIÓN: Se ha cambiado el estado del estudiante"));
	}
}

?>
<script>
/*
function calc_total() {
	var EF = document.formulario.EF.value,
	    CH = document.formulario.CH.value,
	    CHF = document.formulario.CHF.value,
	    TR = document.formulario.TR.value,
	    TC = document.formulario.TC.value;
	    TD = document.formulario.TD.value;
	    
	document.formulario.total_pago.value = EF.replace('.','').replace('.','')*1 
	                                     + CH.replace('.','').replace('.','')*1
	                                     + CHF.replace('.','').replace('.','')*1
	                                     + TR.replace('.','').replace('.','')*1
	                                     + TC.replace('.','').replace('.','')*1
	                                     + TD.replace('.','').replace('.','')*1;

	puntitos(document.formulario.total_pago,document.formulario.total_pago.value.charAt(document.formulario.total_pago.value.length-1),document.formulario.total_pago.name);
}
*/

function acciones_forma_pago(forma_pago) {
	switch (forma_pago) {
		case 'efectivo':
		case 'cheque':
		case 'transferencia':
			formulario.cod_operacion.value=null;
			formulario.cod_operacion.disabled=true;
			formulario.cod_operacion.required=false;
			break; 
		case 'deposito':
		case 'tarj_debito':
			formulario.cod_operacion.disabled=false;
			formulario.cod_operacion.required=true;
			formulario.cod_operacion.focus();
			break; 
		case 'tarj_credito': 
			formulario.cod_operacion.disabled=false;
			formulario.cod_operacion.required=true;
			formulario.cod_operacion.focus();
			formulario.cant_cuotas.style.display=''; 
			formulario.cant_cuotas.disabled=false; 
			formulario.cant_cuotas.required=true; 
			break; 
		case 'cheque_afecha': 
			formulario.cod_operacion.disabled=true;
			formulario.cod_operacion.required=false;
			formulario.cant_cuotas.style.display=''; 
			formulario.cant_cuotas.disabled=false; 
			formulario.cant_cuotas.required=true; 
			break; 
		default: 
			formulario.cant_cuotas.style.display='none'; 
			formulario.cant_cuotas.disabled=true; 
			formulario.cant_cuotas.required=false;
	}
}

function calc_total() {
	
	var monto_pago = formulario.monto_pago.value;
	    
	formulario.total_pago.value = monto_pago.replace('.','').replace('.','')*1;
	
	puntitos(document.formulario.total_pago,document.formulario.total_pago.value.charAt(document.formulario.total_pago.value.length-1),document.formulario.total_pago.name);
}

calc_total();
puntitos(document.formulario.total_pago,document.formulario.total_pago.value.charAt(document.formulario.total_pago.value.length-1),document.formulario.total_pago.name);
puntitos(document.formulario.monto_pago,document.formulario.monto_pago.value.charAt(document.formulario.monto_pago.value.length-1),document.formulario.monto_pago.name);

function valida_pago() {
	var valida_pago = false,
	    total_pago = formulario.total_pago.value;
	total_pago = total_pago.replace('.','').replace('.','')*1;
	if (formulario.monto_pago.value != "") {
		if (total_pago == 0) {
			alert("El total del pago está en cero. No es posible ingresar un pago en cero.");
		}
		if (total_pago > formulario.deuda_total.value) {
			alert("No puede ingresar un monto de pago superior a la Deuda Total");
		} else if (total_pago > 0) {
			valida_pago = confirm('Por favor confirme el pago por $'+document.formulario.total_pago.value+' (pinche en Aceptar). \n\n'
		                         +'Una vez confirmado el pago no podrá deshacer la acción.',true,false);
		}
	}
	return valida_pago;
}
</script>

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 1000,
		'height'			: 400,
		'afterClose'		: function () { location.reload(false); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 500,
		'height'			: 350,
		'maxHeight'			: 350,
		'afterClose'		: function () { location.reload(false); },
		'type'				: 'iframe'
	});
});
</script>
