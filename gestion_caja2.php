<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$rut        = $_REQUEST['rut'];
$validar    = $_REQUEST['validar'];
$rut_valido = false;

if (empty($_REQUEST["EF"])) { $_REQUEST["EF"] = 0; }
if (empty($_REQUEST["CH"])) { $_REQUEST["CH"] = 0; }
if (empty($_REQUEST["CHF"])) { $_REQUEST["CHF"] = 0; }
if (empty($_REQUEST["TR"])) { $_REQUEST["TR"] = 0; }
if (empty($_REQUEST["TC"])) { $_REQUEST["TC"] = 0; }
if (empty($_REQUEST["TD"])) { $_REQUEST["TD"] = 0; }
if ($_REQUEST["fecha_pago"] == "") { $_REQUEST["fecha_pago"] = date("d-m-Y"); }

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
		$id_alumno = $alumno[0]['id'];
		$nombre    = $alumno[0]['nombre'];
	}

	$rut_valido = true;

	$cobros_oi = false;
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
		} else {
			/*
			$SQL_cobros = "SELECT to_char(cob.fecha_venc,'DD-tmMon-YYYY') as fec_venc,cob.fecha_venc,g.nombre AS glosa,cob.monto,
								  cob.nro_cuota,'' AS id_contrato,'' AS ano,
								  CASE WHEN cob.pagado THEN 'Si' ELSE 'No' END AS pagado,
								  CASE WHEN cob.abonado THEN 'Si' ELSE 'No' END AS abonado,
								  cob.monto_abonado,cob.id AS id_cobro,'' AS id_pagare
						   FROM finanzas.cobros      AS cob
						   LEFT JOIN finanzas.glosas AS g ON g.id=cob.id_glosa
						   WHERE cob.id_alumno=$id_alumno AND cob.fecha_venc > now()::date AND NOT pagado
						   ORDER BY cob.fecha_venc,cob.id_contrato";
			$cobros     = consulta_sql($SQL_cobros);
			if (count($cobros) > 0) {
				$cobros_oi = true;
				$msje = "ATENCIÓN: Este alumno tiene registrados Otros Cobros que vencen en el futuro, "
				      . "los cuales pueden ser pagados en primer lugar.\\n\\n"
				      . "Desea pagar estos Otros cobros en priner lugar?.";
				echo(confirma_js());
			}
			*/			
		}
	}

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
		$contratos  = consulta_sql($SQL_contratos);
		if (count($contratos) == 0) {
			echo(msje_js("El RUT ingresado no tiene contratos asociados. No se puede continuar."));
			echo(js("window.location='$enlbase=$modulo';"));
			exit;
		}
	
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
					   WHERE (cob.id_contrato IN (SELECT id_contrato FROM ($SQL_contratos) AS foo) OR cob.id_convenio_ci IN ($SQL_convenios_ci))
						 AND cob.id_glosa NOT IN (21,22) AND NOT cob.pagado
					   ORDER BY cob.fecha_venc,cob.id_contrato";
		//var_dump($SQL_cobros);
		$cobros     = consulta_sql($SQL_cobros);
		if (count($cobros) == 0) {
			echo(msje_js("El RUT ingresado tiene Contratos o Convenio(s) de Liquidación de Crédito Interno emitido(s), pero no tiene cobros asociados o bien estos se encuentran pagados."));
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
			  .  "  <td class='textoTabla' align='right'><label for='aId_cobro[$id_cobro]'>$fec_venc</label></td>\n"
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
	$nro_boleta     = $_REQUEST['nro_boleta'];
	$fecha_pago     = $_REQUEST['fecha_pago'];
	$EF             = str_replace(".","",$_REQUEST['EF']);
	$CH             = str_replace(".","",$_REQUEST['CH']);
	$CHF            = str_replace(".","",$_REQUEST['CHF']);
	$cant_cheques   = $_REQUEST['cant_cheques'];
	$TR             = str_replace(".","",$_REQUEST['TR']);
	$TC             = str_replace(".","",$_REQUEST['TC']);
	$cant_cuotas_TC = $_REQUEST['cant_cuotas_TC'];
	$TD             = str_replace(".","",$_REQUEST['TD']);
	$aId_cobros     = $_REQUEST['aId_cobro'];
	
	$ids_cobros = array();
	foreach ($aId_cobros AS $id_cobro => $valor) { $ids_cobros = array_merge($ids_cobros,array($id_cobro)); }
	$ids_cobros = implode(",",$ids_cobros);
	
	$boleta_valida = consulta_sqL("SELECT nro_boleta FROM finanzas.pagos WHERE nro_boleta = $nro_boleta");
	if (count($boleta_valida) > 0) {
		echo(msje_js("Número de Boleta ya utilizado. Debe usar otro"));
		echo(js("history.back();"));
		exit;
	}
	
	$total_pago = $EF + $CH + $CHF + $TR + $TC + $TD;

	$SQL_id_alumno = "SELECT id FROM vista_alumnos WHERE trim(rut)='$rut'";
	
	$cobros_oi = false;	
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

		$cond_cobros = "WHERE NOT pagado AND (c.id_contrato IN ($SQL_contratos) OR c.id_convenio_ci IN ($SQL_convenios_ci)) AND c.id_glosa NOT IN (21,22)";
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

		$val_pago = md5($rut+$nro_boleta);
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

	if ($_REQUEST['val_pago'] == md5($rut+$nro_boleta)) {	
		if ($cant_cheques == "") { $cant_cheques = "null"; }
		if ($cant_cuotas_TC == "") { $cant_cuotas_TC = "null"; }

		$SQL_insPago = "INSERT INTO finanzas.pagos (efectivo,cheque,cheque_afecha,cant_cheques,transferencia,tarj_credito,cant_cuotas_tarj_credito,tarj_debito,nro_boleta,id_cajero,fecha)
								VALUES ($EF,$CH,$CHF,$cant_cheques,$TR,$TC,$cant_cuotas_TC,$TD,$nro_boleta,{$_SESSION['id_usuario']},'$fecha_pago'::date);";
		if (consulta_dml($SQL_insPago) > 0) {
			$pago    = consulta_sql("SELECT last_value AS id FROM finanzas.pagos_id_seq;");
			$id_pago = $pago[0]['id'];
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

		
		echo(msje_js("Pago registrado correctamente. Imprima el comprobante que se emite a continuación"));
		echo(js("location.href='$enlbase_sm=ver_pago&id_pago=$id_pago';"));
		comp_pago_email($id_pago);
		//echo(js("parent.jQuery.fancybox.close();"));
		exit;
	
	} else {
		echo(msje_js("Ha fallado la comprobación del pago. NO se puede registrar"));
	}

}

$cuotas_TC = array();
for ($x=0;$x<12;$x++) { $cuotas_TC[$x] = array('id'=>$x+1,'nombre'=>$x+1); }
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>

<?php	if ($rut_valido) { ?>
<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="get"
      onSubmit="if (!enblanco2(<?php echo($requeridos); ?>) || !valida_rut(formulario.rut.value)) { return false; }">

<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="rut" value="<?php echo($rut); ?>">
<input type="hidden" name="id_alumno" value="<?php echo($id_alumno); ?>">
<input type="hidden" name="id_pap" value="<?php echo($id_pap); ?>">
<input type="hidden" name="deuda_total" value="<?php echo($deuda_total); ?>">

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
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Forma de pago</td></tr>
  <tr>
    <td class='celdaNombreAttr' rowspan="3">Número Boleta:</td>
    <td class='celdaValorAttr' rowspan="3">
      <input type='text' size='10' name='nro_boleta' value="<?php echo($_REQUEST['nro_boleta']); ?>" id='nro_boleta' onLoad='this.focus();'><br>
      <sub><?php echo($ultima_nro_boleta); ?></sub>
    </td>
    <td class='celdaNombreAttr'>Efectivo:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='EF' value="<?php echo($_REQUEST['EF']); ?>"
              onBlur="puntitos(this,this.value.charAt(this.value.length-1),this.name); calc_total(this.value);">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cheque al Día:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='CH' value="<?php echo($_REQUEST['CH']); ?>"
              onBlur="puntitos(this,this.value.charAt(this.value.length-1),this.name); calc_total(this.value);">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cheque(s) a Fecha:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='CHF' value="<?php echo($_REQUEST['CHF']); ?>"
              onBlur="puntitos(this,this.value.charAt(this.value.length-1),this.name); calc_total(this.value);">
      <select name="cant_cheques">
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
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' rowspan="3">Fecha:</td>
    <td class='celdaValorAttr' rowspan="3">
      <input type='text' size='10' name='fecha_pago' value="<?php echo($_REQUEST['fecha_pago']); ?>"><br>
      <sup>DD-MM-AAAA</sup>
    </td>
    <td class='celdaNombreAttr'>Transferencia:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='TR' value="<?php echo($_REQUEST['TR']); ?>"
              onBlur="puntitos(this,this.value.charAt(this.value.length-1),this.name); calc_total(this.value);">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tarjeta de Crédito:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='TC' value="<?php echo($_REQUEST['TC']); ?>"
              onBlur="puntitos(this,this.value.charAt(this.value.length-1),this.name); calc_total(this.value);">
      <select name="cant_cuotas_TC">
        <option value="">Cant:</option>
        <?php echo(select($cuotas_TC,$_REQUEST['cant_cuotas_tarj_credito'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tarjeta de Débito:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='TD' value="<?php echo($_REQUEST['TD']); ?>"
              onBlur="puntitos(this,this.value.charAt(this.value.length-1),this.name); calc_total(this.value);">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="3">Total Pago:</td>
    <td class='celdaNombreAttr' style='text-align: left'>
      $<input type='text' class='montos' size='10' name='total_pago' value=""
              onChange="puntitos(this,this.value.charAt(this.value.length-1),this.name)" readonly>
      <input type='submit' name='pagar' value='Registrar' onClick="return valida_pago();">
    </td>
  </tr>
</table>
  </td>
 </tr>
</table>

<script>document.getElementById("nro_boleta").focus();</script>
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
?>
<script>
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

calc_total();
puntitos(document.formulario.total_pago,document.formulario.total_pago.value.charAt(document.formulario.total_pago.value.length-1),document.formulario.total_pago.name);
puntitos(document.formulario.EF,document.formulario.EF.value.charAt(document.formulario.EF.value.length-1),document.formulario.EF.name);

function valida_pago() {
	var valida_pago = false,
	    total_pago = document.formulario.total_pago.value;
	total_pago = total_pago.replace('.','').replace('.','')*1;
	if (document.formulario.nro_boleta.value != "") {
		if (total_pago == 0) {
			alert("El total del pago está en cero. No es posible ingresar un pago en cero.");
		}
		if (total_pago > document.formulario.deuda_total.value) {
			alert("No puede ingresar un monto de pago superior a la Deuda Total");
		} else if (total_pago > 0) {
			valida_pago = confirm('Por favor confirme el pago por $'+document.formulario.total_pago.value+' (pinche en Aceptar). \n\n'
		                         +'Una vez confirmado el pago no podrá deshacer la acción.',true,false);
		}
	} else {
		alert('Debe ingresar el Nº de Boleta');
		formulario.nro_boleta.focus();
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
