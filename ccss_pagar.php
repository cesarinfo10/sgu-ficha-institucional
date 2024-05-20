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
	
	$SQL_socios   = "SELECT char_comma_sum(rut||' '||nombres||' '||apellidos) 
	                 FROM finanzas.ccss_socios 
	                 WHERE id IN (SELECT id_socio FROM finanzas.ccss_sociedades_socios WHERE id_sociedad=s.id)";
	$SQL_sociedad = "SELECT id,rut,razon_social,($SQL_socios) AS socios FROM finanzas.ccss_sociedades AS s WHERE trim(rut)='$rut'";
	$sociedad     = consulta_sql($SQL_sociedad);
	
	if (count($sociedad) == 0) {
		echo(msje_js("ERROR: El RUT ingresado no corresponde a una sociedad.\\n"
		            ."Intente nuevamente por favor."));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}
	$sociedad[0]['socios'] = str_replace(",","<br>",$sociedad[0]['socios']); 
		
	$rut_valido = true;
	
	$SQL_cobros = "SELECT cc.id AS id_cobro,cc.fecha_venc,to_char(cc.fecha_venc,'DD-tmMon-YYYY') AS fec_venc,cc.nro_cuota,cc.monto,
						  CASE WHEN cc.pagado  THEN 'Si' ELSE 'No' END AS pagado,
						  CASE WHEN cc.abonado THEN 'Si' ELSE 'No' END AS abonado,
						  cc.monto_abonado,c.ano,cc.id_compromiso
				   FROM finanzas.ccss_cobros AS cc
				   LEFT JOIN finanzas.ccss_compromisos AS c ON c.id=cc.id_compromiso
				   LEFT JOIN finanzas.ccss_sociedades AS s ON s.id=c.id_sociedad
				   WHERE s.rut='$rut' AND NOT cc.pagado
				   ORDER BY fecha_venc";
	$cobros     = consulta_sql($SQL_cobros);
	if (count($cobros) == 0) {
		echo(msje_js("El RUT ingresado no tiene cuotas pendientes de pago."));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}

	$HTML_cobros = "";
	$deuda_total = $deuda_vencida = 0;
	for ($y=0;$y<count($cobros);$y++) {			
		$HTML = "";
		extract($cobros[$y]);

		if ($pagado == "No" && $abonado == "No") { $deuda_total += $monto; }
		if ($pagado == "No" && $abonado == "Si") { $deuda_total += $monto-$monto_abonado; }

		$monto_f = number_format($monto,0,',','.');
		
		$saldo = "";
		if ($abonado == "Si") { $saldo = "($".number_format($monto-$monto_abonado,0,',','.').")"; }
		
		if ($pagado == "Si") { $abonado = ""; }
		
		$HTML =  "<tr class='filaTabla'>\n"
			  .  "  <td class='textoTabla' align='right' style='color: #7F7F7F'>$id_cobro</td>\n"
			  .  "  <td class='textoTabla' align='right'><label for='aId_cobro[$id_cobro]'>$fec_venc</label></td>\n"
			  .  "  <td class='textoTabla' align='right'>$nro_cuota</td>\n"
			  .  "  <td class='textoTabla' align='right'>$$monto_f</td>\n"
			  .  "  <td class='textoTabla' align='center'><span class='$abonado'>$abonado <small>$saldo</small></span></td>\n"
			  .  "  <td class='textoTabla' align='center'>$id_compromiso</td>"
			  .  "  <td class='textoTabla' align='center'>$ano</td>"
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
		$HTML_cobros_vencidos .= "<tr class='filaTabla'><td class='textoTabla' align='right' colspan='4'>"
							  .  "  <b><span class='No'>Compromiso Adeudado: $$deuda_vencida</span></b>"
							  .  "</td><td class='textoTabla' colspan='5'> </td></tr>\n";

		$deudaTotal = number_format($deuda_total,0,',','.');                      
		$HTML_cobros .= "<tr class='filaTabla'><td class='textoTabla' align='right' colspan='4'>"
					 .  "  <b>Compromiso Pendiente: $$deudaTotal</b><br> <small>(incluye Compromiso Adeudado)</small>"
					 . "</td><td class='textoTabla' colspan='5'> </td></tr>\n";
		
		$HTML_contratos .= tabla_contrato_cobros("")
						.  $HTML_cobros_vencidos
						.  $HTML_cobros
						.  "</table>\n";

	}	
}

if ($_REQUEST['pagar'] == "Registrar") {
	$id_sociedad    = $_REQUEST['id_sociedad'];
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
	
	$total_pago = $EF + $CH + $CHF + $TR + $TC + $TD;

	if ($ids_cobros <> "") { $cond_cobros = "WHERE c.id IN ($ids_cobros)"; }

	$SQL_cobros = "SELECT cc.id,cc.fecha_venc,to_char(cc.fecha_venc,'DD-tmMon-YYYY') AS fec_venc,cc.nro_cuota,cc.monto,
						  CASE WHEN cc.pagado  THEN 'Si' ELSE 'No' END AS pagado,
						  CASE WHEN cc.abonado THEN 'Si' ELSE 'No' END AS abonado,
						  cc.monto_abonado,c.ano,cc.id_compromiso
				   FROM finanzas.ccss_cobros AS cc
				   LEFT JOIN finanzas.ccss_compromisos AS c ON c.id=cc.id_compromiso
				   LEFT JOIN finanzas.ccss_sociedades AS s ON s.id=c.id_sociedad
				   WHERE s.rut='$rut' AND NOT cc.pagado
				   ORDER BY fecha_venc";
	$cobros     = consulta_sql($SQL_cobros);

	if (count($cobros) > 0) {
		$total_cobros = 0;
		$vencimientos = "Fecha      \\t\\tMonto     \\tAccion \\n".str_repeat("-",130)."\\n";
		$monto_paga = $total_pago;
		for ($x=0;$x<count($cobros);$x++) {
			$vencimientos .= $cobros[$x]['fec_venc']."\\t";
			if ($cobros[$x]['monto_abonado'] > 0) {
				$monto_cobro = $cobros[$x]['monto'] - $cobros[$x]['monto_abonado'];
				$total_cobros += $monto_cobro;
				$vencimientos .= " $".number_format($monto_cobro,0,",",".");
			} else {
				$monto_cobro = $cobros[$x]['monto'];
				$total_cobros += $monto_cobro;
				$vencimientos .= " $".number_format($monto_cobro,0,",",".");
			}
			
			if ($monto_cobro <= $monto_paga) { $accion = "Paga"; } 
			elseif ($monto_cobro > $monto_paga && $monto_paga > 0) { $accion = "Abona $".number_format($monto_paga,0,",","."); }
			else { $accion = "Nada"; }
			
			$monto_paga -= $monto_cobro;
			
			$vencimientos .= "\\t$accion\\n";
		}

		$val_pago = md5($rut+$nro_boleta);
		$enl_val_pago = $_SERVER['REQUEST_URI']."&val_pago=$val_pago";
		
		if ($CH > 0 || $CHF > 0 || $TR > 0 || $TC > 0 || $TD > 0) {
			//echo(js("location.href='{$_SERVER['REQUEST_URI']}&modulo=ccss_pagar_registrar_doctos';"));
			$enl_val_pago = $_SERVER['REQUEST_URI']."&modulo=ccss_pagar_registrar_doctos";
			$msje_cheques = "\\n\\n"
			              . "Ha indicado que el pago se realiza con Cheque(s), por lo que deberá "
			              . "registrar los documentos asociados antes de dar por registrado este pago.";
		}
		
		if ($_REQUEST['val_pago'] == "") {
		
			if ($ids_cobros <> "" && $total_pago > $total_cobros) {
				echo(msje_js("Se ha seleccionado cuotas específicas, pero el total a pagar supera los cobros seleccionados.\\n\\n"
							."NO SE PUEDE CONTINUAR"));
				echo(js("parent.jQuery.fancybox.close();"));
				exit;
			} elseif ($ids_cobros <> "" && $total_pago <= $total_cobros) {
				$msje_abono = "";
				if ($ids_cobros <> "" && $total_pago < $total_cobros) { $msje_abono = ", por lo que se realizará un ABONO a la cuota indicada"; }
				
				$msje_pago = "ATENCIÓN: Ha seleccionado cuotas específicas para pagar con los siguientes vencimientos:\\n\\n"
							. $vencimientos . "\\n"
							."Seleccionó cuotas por un total de $".number_format($total_cobros,0,",",".")."\\n\\n"
							."El monto total del pago es de $".number_format($total_pago,0,",",".").$msje_abono.$msje_cheques;
				echo(confirma_js($msje_pago,$enl_val_pago,"#"));
				echo(js("parent.jQuery.fancybox.close();"));
				exit;
			}

			$msje_abono = "";
			if ($ids_cobros == "") {
				if ($total_pago < $total_cobros) { $msje_abono = ", por lo que se realizará un ABONO a la cuota indicada"; }
				$msje_pago = "ATENCIÓN: Se procederá a pagar los siguiente vencimientos:\\n\\n"
							. $vencimientos . "\\n"
							."Cuotas por un total de $".number_format($total_cobros,0,",",".")."\\n\\n"
							."El monto total del pago es de $".number_format($total_pago,0,",",".").$msje_abono.$msje_cheques;
				echo(confirma_js($msje_pago,$enl_val_pago,"#"));
				echo(js("parent.jQuery.fancybox.close();"));
				exit;
			}
		
		}
		
	}

	if ($_REQUEST['val_pago'] == md5($rut+$nro_boleta)) {	
		if ($cant_cheques == "") { $cant_cheques = "null"; }
		if ($cant_cuotas_TC == "") { $cant_cuotas_TC = "null"; }

		$SQL_insPago = "INSERT INTO finanzas.ccss_pagos (efectivo,cheque,cant_cheques,transferencia,id_cajero,fecha)
								VALUES ($EF,$CH,$cant_cheques,$TR,{$_SESSION['id_usuario']},'$fecha_pago'::date);";
		if (consulta_dml($SQL_insPago) > 0) {
			$pago    = consulta_sql("SELECT last_value AS id FROM finanzas.ccss_pagos_id_seq;");
			$id_pago = $pago[0]['id'];
		} else {
			echo(msje_js("Ha ocurrido un error, no ha podido guardarse el pago.\\n\\n "
						."Por favor comunique este error al Departamento de Informática."));
			echo(js("parent.jQuery.fancybox.close();"));
			exit;
		}	
		
		$SQL_updCobro = "";
		$ids_cobros = array();
		$tot_pago = $total_pago;

		for ($x=0;$x<count($cobros);$x++) {
			$monto = $cobros[$x]['monto'];
			
			$ids_cobros[$x] = $cobros[$x]['id'];
			
			if ($cobros[$x]['abonado'] == "Si") { $monto -= $cobros[$x]['monto_abonado']; }
			
			if ($tot_pago < $monto) {
				$monto = $tot_pago;
				$SQL_updCobro .= "UPDATE finanzas.ccss_cobros SET abonado=true,monto_abonado=coalesce(monto_abonado,0)+$tot_pago WHERE id={$cobros[$x]['id']};
								  INSERT INTO finanzas.ccss_pagos_detalle VALUES ($id_pago,{$cobros[$x]['id']},$monto);";
				$tot_pago -= $monto;
				break;
			}
			if ($tot_pago == $monto) {
				$SQL_updCobro .= "UPDATE finanzas.ccss_cobros SET pagado=true,abonado=false,monto_abonado=null WHERE id={$cobros[$x]['id']};
								  INSERT INTO finanzas.ccss_pagos_detalle VALUES ($id_pago,{$cobros[$x]['id']},$monto);";
				$tot_pago -= $monto;
				break;
			}
			if ($tot_pago > $monto) {
				$SQL_updCobro .= "UPDATE finanzas.ccss_cobros SET pagado=true,abonado=false,monto_abonado=null WHERE id={$cobros[$x]['id']};
								  INSERT INTO finanzas.ccss_pagos_detalle VALUES ($id_pago,{$cobros[$x]['id']},$monto);";
				$tot_pago -= $monto;
			}		
		}
		
		consulta_dml($SQL_updCobro);
		//echo($SQL_updCobro);

		
		echo(msje_js("Pago registrado correctamente. Imprima el comprobante que se emite a continuación"));
		echo(js("location.assign('ccss_ver_pago_imprimir.php?id=$id_pago');"));
		exit;
	
	} else {
		echo(msje_js("Ha fallado la comprobación del pago. NO se puede registrar"));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
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
<input type="hidden" name="id_sociedad" value="<?php echo($sociedad[0]['id']); ?>">
<input type="hidden" name="deuda_total" value="<?php echo($deuda_total); ?>">
<input type="hidden" name="CHF" value="0">
<input type="hidden" name="TC" value="0">
<input type="hidden" name="TD" value="0">

<table style='margin-top: 5px'>
 <tr>
  <td>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" width='100%'>
  <tr class='filaTituloTabla'><td class='tituloTabla' colspan="4" style="text-align: center; ">Antecedentes de la Sociedad</td></tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='text-align: right'>RUT:</td>
    <td class='celdaValorAttr' bgcolor='#FFFFFF'><?php echo($rut); ?></td>
    <td class='tituloTabla' style='text-align: right'>Razón Social:</td>
    <td class='celdaValorAttr' bgcolor='#FFFFFF'><?php echo($sociedad[0]['razon_social']); ?></td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='text-align: right'>Socios:</td>
    <td class='celdaValorAttr' bgcolor='#FFFFFF' colspan='3'><?php echo($sociedad[0]['socios']); ?></td>
  </tr>
</table>
<?php echo($HTML_contratos); ?>	
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" align='right' style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Forma de pago</td></tr>
  <tr>
    <td class='celdaNombreAttr' rowspan="3">Fecha:</td>
    <td class='celdaValorAttr' rowspan="3">
      <input type='text' size='10' name='fecha_pago' value="<?php echo($_REQUEST['fecha_pago']); ?>"><br>
      <sup>DD-MM-AAAA</sup>
    </td>
    <td class='celdaNombreAttr'>Efectivo:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='EF' value="<?php echo($_REQUEST['EF']); ?>"
              onBlur="puntitos(this,this.value.charAt(this.value.length-1),this.name); calc_total(this.value);">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cheque(s):</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='CH' value="<?php echo($_REQUEST['CH']); ?>"
              onBlur="puntitos(this,this.value.charAt(this.value.length-1),this.name); calc_total(this.value); formulario.cant_cheques.value=1;">
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
    <td class='celdaNombreAttr'>Transferencia:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='TR' value="<?php echo($_REQUEST['TR']); ?>"
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
	             <td class='tituloTabla' rowspan='2'>Fecha<br>Vencimiento</td>
	             <td class='tituloTabla' rowspan='2'>Nº<br>Cuota</td>
	             <td class='tituloTabla' rowspan='2'>Monto</td>
	             <td class='tituloTabla' rowspan='2'>Abonado?<br><small>(saldo)</small></td>
	             <td class='tituloTabla' colspan='2'>Compromiso</td>
	           </tr>
	           <tr class='filaTituloTabla'>
	             <td class='tituloTabla'>N°</td>
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
		
	if (total_pago == 0) {
		alert("El total del pago está en cero. No es posible ingresar un pago en cero.");
	}
	if (total_pago > document.formulario.deuda_total.value) {
		alert("No puede ingresar un monto de pago superior a la Deuda Total");
	} else if (total_pago > 0) {
		valida_pago = confirm('Por favor confirme el pago por $'+document.formulario.total_pago.value+' (pinche en Aceptar). \n\n'
							 +'Una vez confirmado el pago no podrá deshacer la acción.',true,false);
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
