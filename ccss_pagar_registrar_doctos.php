<?php 
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_sociedad = $_REQUEST['id_sociedad'];

if (empty($_REQUEST["EF"])) { $_REQUEST["EF"] = 0; }
if (empty($_REQUEST["CH"])) { $_REQUEST["CH"] = 0; }
if (empty($_REQUEST["CHF"])) { $_REQUEST["CHF"] = 0; }
if (empty($_REQUEST["TR"])) { $_REQUEST["TR"] = 0; }
if (empty($_REQUEST["TC"])) { $_REQUEST["TC"] = 0; }
if (empty($_REQUEST["TD"])) { $_REQUEST["TD"] = 0; }
if (empty($_REQUEST["cant_cheques"])) { $_REQUEST["cant_cheques"] = 1; }
if ($_REQUEST["fecha_pago"] == "") { $_REQUEST["fecha_pago"] = date("d-m-Y"); }

if (!empty($id_sociedad)) {
	$SQL_socios   = "SELECT char_comma_sum(rut||' '||nombres||' '||apellidos) 
	                 FROM finanzas.ccss_socios 
	                 WHERE id IN (SELECT id_socio FROM finanzas.ccss_sociedades_socios WHERE id_sociedad=s.id)";
	$SQL_sociedad = "SELECT id,rut,razon_social,telefono,($SQL_socios) AS socios FROM finanzas.ccss_sociedades AS s WHERE s.id='$id_sociedad'";
	$sociedad     = consulta_sql($SQL_sociedad);
	
	if (count($sociedad) == 0) {
		echo(msje_js("ERROR: El RUT ingresado no corresponde a una sociedad.\\n"
		            ."Intente nuevamente por favor."));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}
	
	$inst_financieras = consulta_sql("SELECT codigo AS id,nombre FROM finanzas.inst_financieras ORDER BY nombre");

	$HTML   = "";
	$onBlur = "onBlur='copiar_datos_cheque();'";

	$monto_total  = str_replace(".","",$_REQUEST['CH']);	
	$monto_cheque = round($monto_total / $_REQUEST['cant_cheques'],0);
	$mes_ini = date("n");
	$dia_ini = ($mes_ini == 2) ? 28 : 30;
	$fecha_inicial = mktime(0,0,0,$mes_ini,$dia_ini,date("Y"));
	for ($y=1;$y<=$_REQUEST['cant_cheques'];$y++) {
		if ($y==$_REQUEST['cant_cheques'] && $monto_cheque<>$monto_total) { $monto_cheque = $monto_total; }
		$monto_total -= $monto_cheque;
		$monto_ch_f = number_format($monto_cheque,0,',','.');
		$fecha_venc = strftime("%d-%m-%Y",$fecha_inicial);
		if ($y>1) { $onBlur = ""; }
		
		$HTML_inst_finan = "<select name='inst_finan[$y]' style='max-width: 150px' $onBlur><option value=''>-- Seleccione --</option>".select($inst_financieras,null)."</select>";
		
		$HTML .=  "<tr class='filaTabla'>\n"
			  .   "  <td class='textoTabla' align='center'><input class='boton' type='text' name='fecha_venc[$y]' id='fecha_venc$y' size='8' value='$fecha_venc'></td>\n"
			  .   "<script type='text/javascript' charset='utf-8'>  var k$y = new Kalendae.Input('fecha_venc$y', { format: 'DD-MM-YYYY', weekStart: 1 } ); </script>"
			  .   "  <td class='textoTabla' align='right'>$y</td>\n"
			  .   "  <td class='textoTabla' align='right'>$<input type='text' class='montos' size='4' name='monto_cheque[$y]' value='$monto_ch_f' readonly></td>\n"
			  .   "  <td class='textoTabla' align='center'>$HTML_inst_finan</td>\n"
			  .   "  <td class='textoTabla' align='center'><input class='boton' type='text' name='nro_cuenta[$y]' size='8' $onBlur></td>\n"
			  .   "  <td class='textoTabla' align='center'><input class='boton' type='text' name='numero[$y]' size='7' $onBlur></td>\n"
			  .   "  <td class='textoTabla' align='center'><input class='boton' type='text' name='rut_emisor[$y]' size='10' value='{$sociedad[0]['rut']}' $onBlur onChange='var valor=this.value;this.value=valor.toUpperCase();'></td>\n"
			  .   "  <td class='textoTabla' align='center'><input class='boton' type='text' name='nombre_emisor[$y]' size='15' value='{$sociedad[0]['razon_social']}' $onBlur onChange='var valor=this.value;this.value=valor.toUpperCase();'></td>\n"
			  .   "  <td class='textoTabla' align='center'><input class='boton' type='text' name='telefono_emisor[$y]' size='8' value='{$sociedad[0]['telefono']}' $onBlur></td>\n"
			  .   "</tr>\n";
		$fecha_inicial = mktime(0,0,0,date("n")+$y,$dia_ini,date("Y"));
	}
} else {
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

if ($_REQUEST['registrar'] == "Registrar Pago y Doctos") {
	$id_sociedad  = $_REQUEST['id_sociedad'];
	$fecha_pago   = $_REQUEST['fecha_pago'];
	$cant_cheques = $_REQUEST['cant_cheques'];
	$rut          = $_REQUEST['rut'];
	$CH           = str_replace(".","",$_REQUEST['CH']);
	$EF           = str_replace(".","",$_REQUEST['EF']);
	$TR           = str_replace(".","",$_REQUEST['TR']);

	$total_pago = $CH + $EF + $TR;

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
		
		if ($cant_cheques == "") { $cant_cheques = "null"; }
		if ($cant_cuotas_TC == "") { $cant_cuotas_TC = "null"; }
		
		$CH = str_replace(".","",$CH);

		$SQL_insPago = "INSERT INTO finanzas.ccss_pagos (cheque,cant_cheques,efectivo,transferencia,id_cajero,fecha)
								VALUES ($CH,$cant_cheques,$EF,$TR,{$_SESSION['id_usuario']},'$fecha_pago'::date);";
		if (consulta_dml($SQL_insPago) > 0) {
			$pago    = consulta_sql("SELECT last_value AS id FROM finanzas.ccss_pagos_id_seq;");
			$id_pago = $pago[0]['id'];
		} else {
			echo(msje_js("Ha ocurrido un error, no ha podido guardarse el pago.\\n\\n "
						."Por favor comunique este error al Departamento de Informática."));
			echo(js("parent.jQuery.fancybox.close();"));
			exit;
		}	
		
		$aFecha_venc      = $_REQUEST['fecha_venc'];
		$aMonto_cheque    = $_REQUEST['monto_cheque'];
		$aInst_finan      = $_REQUEST['inst_finan'];
		$aNro_cuenta      = $_REQUEST['nro_cuenta'];
		$aNro_docto       = $_REQUEST['numero'];
		$aRut_emisor      = $_REQUEST['rut_emisor'];
		$aNombre_emisor   = $_REQUEST['nombre_emisor'];
		$aTelefono_emisor = $_REQUEST['telefono_emisor'];
		
		$SQLins_cheques = "";
		for ($x=1;$x<=$cant_cheques;$x++) {
			$SQLins_cheques .= "INSERT INTO finanzas.ccss_cheques (id_pago,cod_inst_finan,nro_cuenta,numero,monto,fecha_venc,rut_emisor,nombre_emisor,telefono_emisor)
			                                               VALUES ($id_pago,
			                                                       {$aInst_finan[$x]},
			                                                       '{$aNro_cuenta[$x]}',
			                                                       {$aNro_docto[$x]},"
			                                                      .str_replace(".","",$aMonto_cheque[$x]).",
			                                                       '{$aFecha_venc[$x]}'::date,
			                                                       '{$aRut_emisor[$x]}',
			                                                       '{$aNombre_emisor[$x]}',
			                                                       '{$aTelefono_emisor[$x]}');";
		}
		
		if (consulta_dml($SQLins_cheques) > 0) {
		
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
		
			echo(msje_js("Pago registrado correctamente. Imprima el comprobante que se emite a continuación"));
			echo(js("location.assign('ccss_ver_pago_imprimir.php?id=$id_pago');"));
			echo(js("parent.jQuery.fancybox.close();"));
			exit;
		} else {
			consulta_dml("DELETE FROM finanzas.ccss_pagos WHERE id=$id_pago");
			echo(msje_js("ERROR: El o los cheques ingresados no pueden ser registrados.\\n"
			            ."Es posible que alguno de los doctos ya se encuentren registrados anteriormente "
			            ."o bien se repitió un número de docto para una misma cuenta en los "
			            ."doctos ahora ingresados"));			
		}
	
	} else {
		echo(msje_js("Ha fallado la comprobación del pago. NO se puede registrar"));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}
}

?>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>
<script src="js/Kalendae/kalendae.standalone.js" type="text/javascript" charset="utf-8"></script>

<form name="formulario" action="principal_sm.php" method="get"
      onSubmit="if (!enblanco2(<?php echo($requeridos); ?>) || !valida_rut(formulario.rut.value)) { return false; }">

<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="rut" value="<?php echo($sociedad[0]['rut']); ?>">
<input type="hidden" name="id_sociedad" value="<?php echo($id_sociedad); ?>">
<input type="hidden" name="deuda_total" value="<?php echo($deuda_total); ?>">
<input type="hidden" name="CH" value="<?php echo($_REQUEST['CH']); ?>">
<input type="hidden" name="cant_cheques" value="<?php echo($_REQUEST['cant_cheques']); ?>">
<input type="hidden" name="CHF" value="0">
<input type="hidden" name="TC" value="0">
<input type="hidden" name="TD" value="0">
<div style='margin-top: 5px'>
  <input type='submit' name='registrar' value='Registrar Pago y Doctos' onClick="return valida_registro();" <?php echo($disabled); ?>>
  <input type="button" onClick="location.href='<?php echo($_SERVER['HTTP_REFERER']."&pagar=&validar=Validar"); ?>';" value='Cancelar'>
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla">
  <tr class='filaTituloTabla'><td class='tituloTabla' colspan="4" style="text-align: center; ">Antecedentes de la Sociedad</td></tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='text-align: right'>RUT:</td>
    <td class='celdaValorAttr' bgcolor='#FFFFFF'><?php echo($sociedad[0]['rut']); ?></td>
    <td class='tituloTabla' style='text-align: right'>Razón Social:</td>
    <td class='celdaValorAttr' bgcolor='#FFFFFF'><?php echo($sociedad[0]['razon_social']); ?></td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='text-align: right'>Socios:</td>
    <td class='celdaValorAttr' bgcolor='#FFFFFF' colspan='3'><?php echo($sociedad[0]['socios']); ?></td>
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr>
    <td colspan="2" class='celdaNombreAttr'>Fecha:</td>
    <td colspan="8" class='textoTabla'>
      <input type='text' size='10' name='fecha_pago' value="<?php echo($_REQUEST['fecha_pago']); ?>"><br>
      <sup>DD-MM-AAAA</sup>
    </td>
  </tr>
  <tr>
    <td colspan="2" class='celdaNombreAttr'>Cheque(s)</td>
    <td colspan="2" class='textoTabla'>
      $<input type='text' class='montos' size='8' name='CH' value="<?php echo($_REQUEST['CH']); ?>"
              onBlur="puntitos(this,this.value.charAt(this.value.length-1),this.name); calc_total(this.value); formulario.cant_cheques.value=1;" readonly>
    </td>
    <td class='celdaNombreAttr'>Efectivo</td>
    <td colspan="2" class='textoTabla'>
      $<input type='text' class='montos' size='8' name='EF' value="<?php echo($_REQUEST['EF']); ?>"
              onBlur="puntitos(this,this.value.charAt(this.value.length-1),this.name); calc_total(this.value); formulario.cant_cheques.value=1;" readonly>
    </td>
    <td class='celdaNombreAttr'>Transferencia</td>
    <td colspan="2" class='textoTabla'>
      $<input type='text' class='montos' size='8' name='TR' value="<?php echo($_REQUEST['TR']); ?>"
              onBlur="puntitos(this,this.value.charAt(this.value.length-1),this.name); calc_total(this.value); formulario.cant_cheques.value=1;" readonly>
    </td>
  </tr>
  <tr class='filaTituloTabla'><td colspan="9" class='tituloTabla'>Cheques Asociados</td></tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Fecha<br>Venc.</td>    
    <td class='tituloTabla'>Nº<br>Cuota</td>
    <td class='tituloTabla'>Monto</td>
    <td class='tituloTabla'>Inst.<br>Financiera</td>
    <td class='tituloTabla'>Nº Cuenta</td>
    <td class='tituloTabla'>Nº Docto.</td>
    <td class='tituloTabla'>RUT Emisor</td>
    <td class='tituloTabla'>Nombre Emisor</td>
    <td class='tituloTabla'>Teléfono<br>Emisor</td>
  </tr>
  <?php echo($HTML); ?>
  <tr class='filaTituloTabla'>
    <td colspan="9" class='tituloTabla' style="text-align: right">
      <input type='submit' name='registrar' value='Registrar Pago y Doctos' onClick="return valida_registro();" <?php echo($disabled); ?>>
    </td>
  </tr>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->

<script>

var cant_filas=<?php echo($_REQUEST['cant_cheques']); ?>,x=0;

function copiar_datos_cheque() {
	for (x=2;x<=cant_filas;x++) {		
		document.forms.formulario["inst_finan["+x+"]"].value      = document.forms.formulario["inst_finan[1]"].value;
		document.forms.formulario["nro_cuenta["+x+"]"].value      = document.forms.formulario["nro_cuenta[1]"].value;
		document.forms.formulario["numero["+x+"]"].value          = parseInt(document.forms.formulario["numero[1]"].value)+x-1;
		document.forms.formulario["rut_emisor["+x+"]"].value      = document.forms.formulario["rut_emisor[1]"].value;
		document.forms.formulario["nombre_emisor["+x+"]"].value   = document.forms.formulario["nombre_emisor[1]"].value;
		document.forms.formulario["telefono_emisor["+x+"]"].value = document.forms.formulario["telefono_emisor[1]"].value;
	}
}

function valida_registro() {
	var problemas=false;
	
	
	for (x=1;x<=cant_filas;x++) {
		if (document.forms.formulario["inst_finan["+x+"]"].value == "" ||
		    document.forms.formulario["nro_cuenta["+x+"]"].value  == "" ||
		    document.forms.formulario["numero["+x+"]"].value == "" ||
		    document.forms.formulario["rut_emisor["+x+"]"].value == "" ||
		    document.forms.formulario["nombre_emisor["+x+"]"].value == "" ||
		    document.forms.formulario["telefono_emisor["+x+"]"].value == "") {
				
			problemas = true;
		}
	}
	
	if (problemas) {
		alert("Deben registrarse todos los datos del o de los cheques que se despliegan.");
		return false;
	}
	
	return true;
}
</script>
