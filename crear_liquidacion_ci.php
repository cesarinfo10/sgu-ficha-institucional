<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");
include("validar_modulo.php");

$id_alumno = $_REQUEST['id_alumno'];
$rut       = $_REQUEST['rut'];
$fecha_liq = $_REQUEST['fecha'];

if (empty($fecha_liq)) { $fecha_liq = date("d-m-Y"); }

if ($_REQUEST['guardar'] == "Guardar y Continuar") { 
	$_REQUEST['modulo'] = "crear_convenio_ci";
	$enl = "principal_sm.php?".http_build_query($_REQUEST);
	echo(js("location='$enl';"));
}

$SQL_alumno = "SELECT a.id AS id_alumno,trim(a.rut) AS rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre,c.alias||'-'||a.jornada AS carrera,
                      a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.mes_cohorte,ae.nombre AS estado,a.id_pap,
                      CASE WHEN m.id_alumno IS NOT NULL THEN 'Si' ELSE 'No' END AS matriculado,moroso_financiero
               FROM alumnos AS a
               LEFT JOIN carreras AS c ON c.id=a.carrera_actual
               LEFT JOIN al_estados AS ae ON ae.id=a.estado
               LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND semestre=$SEMESTRE AND ano=$ANO) ";

if (!empty($id_alumno))                { $SQL_alumno .= "WHERE a.id=$id_alumno"; }
if (!empty($rut) && empty($id_alumno)) { $SQL_alumno .= "WHERE a.rut='$rut'"; }
if (!empty($id_alumno) || !empty($rut)) { 
	$alumno = consulta_sql($SQL_alumno); 
	if (count($alumno) == 0) {
		echo(msje_js("ERROR: El RUT ingresado no corresponde a un alumno."));
		echo(js("parent.jQuery.fancybox.close()"));
		exit;
	} elseif (count($alumno) > 1) {
		$HTML_alumnos = "";
		for ($x=0;$x<count($alumno);$x++) {
			extract($alumno[$x]);
			
			$enl = "$enlbase_sm=$modulo&id_alumno=$id_alumno&rut=$rut";
			$nombre = "<a class='enlitem' href='$enl'>$nombre</a>";
			
			if ($moroso_financiero == "t") { $estado .= " <sup>(M)</sup>"; }
			if ($mes_cohorte <> "") { $mes_cohorte = "(".substr($meses_palabra[$mes_cohorte-1]['nombre'],0,3).")"; }
			
			$HTML_alumnos .= "  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n"
						   . "    <td class='textoTabla'>$id</td>\n"
						   . "    <td class='textoTabla'>$rut</td>\n"
						   . "    <td class='textoTabla'>$nombre</td>\n"
						   . "    <td class='textoTabla'>$carrera</td>\n"
						   . "    <td class='textoTabla'>$regimen</td>\n"
						   . "    <td class='textoTabla'>$cohorte $mes_cohorte</td>\n"
						   . "    <td class='textoTabla'>$estado</td>\n"
						   . "    <td class='textoTabla'>$matriculado</td>\n"
						   . "  </tr>\n";
		}
	} elseif (count($alumno) == 1) {
		extract($alumno[0]);
		$SQL_contratos = "SELECT c.id,to_char(c.fecha,'DD-MM-YYYY') AS fecha,c.tipo,
		                         CASE WHEN c.tipo='Semestral' THEN text(c.semestre)||'-'||text(c.ano) ELSE text(c.ano) END AS periodo,
		                         CASE c.estado WHEN 'E' THEN 'Emitido'
		                                       WHEN 'F' THEN 'Firmado' 
		                                       WHEN 'R' THEN 'Retirado' 
		                                       WHEN 'S' THEN 'Suspendido' 
		                                       WHEN 'A' THEN 'Abandonado' 
		                                       ELSE 'Nulo' 
		                         END AS estado,c.monto_arancel,
		                         CASE WHEN c.id_convenio IS NOT NULL       THEN round(c.monto_arancel*0.2,0)
		                              WHEN c.porc_beca_arancel IS NOT NULL THEN round(c.monto_arancel*(c.porc_beca_arancel/100),0)
		                              ELSE c.monto_beca_arancel
		                         END AS monto_beca_arancel,c.arancel_cred_interno,
		                         round(coalesce(c.arancel_cred_interno,0)/uf.valor,2) AS arancel_cred_interno_uf,
		                         CASE WHEN c.monto_condonacion>0 THEN '(C)' END AS condonacion,c.monto_condonacion,
		                         to_char(c.fecha_condonacion,'DD-MM-YYYY') AS fecha_condonacion,
		                         vc.monto_pagado,vc.monto_saldot,vc.monto_moroso,vc.cuotas_morosas,vc.mat_pagada,u.nombre_usuario AS emisor,
		                         c.comentarios
		                  FROM finanzas.contratos     AS c
		                  LEFT JOIN vista_contratos   AS vc USING (id)
		                  LEFT JOIN usuarios          AS u  ON u.id=c.id_emisor
		                  LEFT JOIN finanzas.valor_uf AS uf ON uf.fecha=c.fecha::date
		                  WHERE (c.id_alumno = $id_alumno OR c.id_pap = $id_pap) AND c.estado IS NOT NULL AND NOT c.ci_liquidado
		                  ORDER BY c.fecha DESC";
		$contratos = consulta_sql($SQL_contratos);
		if (count($contratos) > 0) {
			$SQL_cred_interno = "SELECT max(monto) AS monto FROM finanzas.pagares_cred_interno WHERE id_contrato IN (SELECT id FROM finanzas.contratos WHERE (id_alumno = $id_alumno OR id_pap = $id_pap) AND estado IS NOT NULL)";
			$cred_interno = consulta_sql($SQL_cred_interno);
			$ult_cred_interno = $cred_interno[0]['monto']*1;
			$HTML_contratos = $HTML = "";
			$tot_cred_interno_uf = $descuentos = 0;
			for ($x=0;$x<count($contratos);$x++) {
				extract($contratos[$x]);
				
				$porc_beca_arancel = ($monto_beca_arancel / $monto_arancel) * 100;
				$porc_cred_interno = ($arancel_cred_interno / $monto_arancel) * 100;
				$arancelEfectivo   = $monto_arancel - $monto_beca_arancel - $arancel_cred_interno;
				$tot_cred_interno_uf += $arancel_cred_interno_uf;

				$cond = "";
				if ($monto_condonacion > 0) {
					$porc_cond_arancel  = round($monto_condonacion / $arancelEfectivo*100,0);
					$descuentos        += round($arancel_cred_interno_uf*($porc_cond_arancel/100),2);
					$monto_condonacion *= -1;
					$arancel_cobrable   = $arancelEfectivo + $monto_condonacion;
					$monto_condonacion  = money_format("%7#7.0n",$monto_condonacion);
					$arancel_cobrable   = money_format("%7#7.0n",$arancel_cobrable);
					$cond               = "<div><small>Cond: $monto_condonacion ($porc_cond_arancel%)</small></div>"
					                    . "<div><small>Arancel Cobrable:</small></div>"
					                    . "<div><b>$arancel_cobrable</b></div>";
				}
				
				$monto_arancel        = number_format($monto_arancel,0,',','.');
				$porc_beca_arancel    = number_format($porc_beca_arancel,0,',','.');
				$monto_beca_arancel   = number_format($monto_beca_arancel,0,',','.');
				$arancel_cred_interno = number_format($arancel_cred_interno,0,',','.');
				$porc_cred_interno    = number_format($porc_cred_interno,0,',','.');
				$arancelEfectivo      = number_format($arancelEfectivo,0,',','.');
				$arancel_cred_interno_uf = number_format($arancel_cred_interno_uf,2,',','.');
				
				$monto_pagado         = money_format("%(#7.0n",$monto_pagado);
				$monto_saldot         = money_format("%(#7.0n",$monto_saldot);
				
				if ($monto_moroso > 0) { 
					$monto_moroso   = "<span style='color: #ff0000'>".money_format("%(#7.0n",$monto_moroso)."</span>";
					$cuotas_morosas = "<span style='color: #ff0000'>($cuotas_morosas)</span>";
					$estado_financiero = "<span class='no'>MOROSO</span>";
				} else {
					$estado_financiero = "<span class='si'>Al día</span>";
				}
				
				if ($emisor <> "") { $emisor = "($emisor)"; }
				
				if (!empty($comentarios)) {
					$comentarios = str_replace("###","blockquote",wordwrap(nl2br($comentarios),90)); 
					$id = "<div title='header=[Observaciones] fade=[on] body=[$comentarios]' style='background: #BFE4BF; border-radius: 25px; padding: 0px 2px 0px 2px'>$id</div>";
				}
				
				$HTML .= "  <tr class='filaTabla' $background onClick=\"window.location='$enl';\">\n"
				      . "    <td class='textoTabla' align='center'>"
				      . "      <div>$id</div>"
				      . "      <div>$estado</div>"
				      . "      <div><small>$condonacion</small></div>"
				      . "      <div><small>$estado_financiero</small></div>"
				      . "    </td>\n"
				      . "    <td class='textoTabla' align='center'>$periodo<br><small>$tipo</small></td>\n"
				      . "    <td class='textoTabla' align='right'>$$monto_arancel</td>\n"
				      . "    <td class='textoTabla' align='right'>$$monto_beca_arancel<br><small>$nombre_beca ($porc_beca_arancel%)</small></td>\n"
				      . "    <td class='textoTabla' bgcolor='#FFFF7F' align='right'>"
				      . "      <div>$$arancel_cred_interno</div>"
				      . "      <div><small>($porc_cred_interno%)</small></div>"
				      . "      <div>UF: $arancel_cred_interno_uf</div>"
				      . "    </td>\n"
				      . "    <td class='textoTabla' align='right' style='vertical-align: middle'>"
				      . "      <div>$$arancelEfectivo</div>"
				      . "      $cond"
				      . "    </td>\n"
				      . "    <td class='textoTabla' align='right'><div>$monto_pagado&nbsp;</div><div>[$monto_saldot]</div></td>\n"
				      . "    <td class='textoTabla' align='right'><div>$monto_moroso</div><div>$cuotas_morosas</div></td>\n"
				      . "    <td class='textoTabla'><div>$fecha</div><div align='center'>$emisor</div></td>\n"
				      . "  </tr>\n";
			}
			
			if ($ult_cred_interno > $tot_cred_interno_uf) { 
				$monto_adicional_uf = round($ult_cred_interno - $tot_cred_interno_uf,2);
				$_REQUEST['monto_adicional_uf'] = $monto_adicional_uf;
				$comentarios = "- Se agrega como monto adicional crédito(s) interno(s) otorgados antes del año 2010.\n\n";
			}
			if ($descuentos > 0) {
				$_REQUEST['descuentos'] = $descuentos; 
				$comentarios .= "- Se aplica descuento por condonaciones realizadas a los contratos que tienen adjunto un Crédito Interno.\n\n";
			}
			$msje = "<div style='width: 170pt; font-size: 8pt'>"
			      . "  El descuento propuesto corresponde a la proporción de condonaciones realizadas sobre los contratos.<br><br>"
			      . "  El monto adicional propuesto corresponde a creditos otorgados antes del año 2010. También se puede usar con el fin de formalizar el cobro de alguna multa u otros que defina la VRAF."
			      . "</div>";
			
			$HTML .= "  <tr><td class='celdaNombreAttr' colspan='9' style='text-align: center'>Antecedentes del Convenio de Liquidación de Crédito Interno</td></tr>"
			      .  "  <tr>\n"
			      .  "    <td class='celdaNombreAttr' colspan='4' style='vertical-align: middle'>Total Crédito(s) Interno(s):</td>\n"
			      .  "    <td class='celdaValorAttr' bgcolor='#FFFF7F' style='text-align: right'><b>UF</b> <input type='text' size='2' name='monto_liqci_uf' value='$tot_cred_interno_uf' class='montos' readonly>&nbsp;</td>\n"
			      .  "    <td class='celdaValorAttr' bgcolor='#FFFF7F' style='text-align: right'>$<input type='text' size='4' name='monto_liqci' value='$tot_cred_interno' class='montos' readonly>&nbsp;</td>\n"
			      .  "    <td class='celdaValorAttr' colspan='3' rowspan='4' style='vertical-align: middle'>$msje</td>\n"
			      .  "  </tr>\n";
			$HTML_contratos = $HTML;
			$valor_uf = consulta_sql("SELECT valor FROM finanzas.valor_uf WHERE fecha='$fecha_liq'::date");
			$valor_uf = $valor_uf[0]['valor'];
		}
	}
}

?>
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div style="margin-top: 5px">
<form name='formulario' action='<?php echo($_SERVER['SCRIPT_NAME']); ?>' method="post">
<input type='hidden' name='modulo' value='<?php echo($modulo); ?>'>
<input type='hidden' name='valor_uf' value='<?php echo($valor_uf); ?>'>
<input type='hidden' name='id_alumno' value='<?php echo($id_alumno); ?>'>
<input type='hidden' name='id_pap' value='<?php echo($id_pap); ?>'>
<input type='hidden' name='rut' value='<?php echo($rut); ?>'>
<?php if (empty($rut)) { include_once("ingresar_rut.php"); } ?>
<?php if (count($alumno) == 1) { ?>
<script src="js/Kalendae/kalendae.standalone.js" type="text/javascript" charset="utf-8"></script>
<div>
  <input type='submit' name='guardar' value='Guardar y Continuar'>
  <input type='button' name='restablecer' value='Restablecer' onClick="location='<?php echo($_SERVER['REQUEST_URI']); ?>';">
  <input type='button' name='cancelar' value='Cancelar' onClick="parent.jQuery.fancybox.close();">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style="margin-top: 5px">
  <tr>
    <td class='celdaNombreAttr' colspan="7" style="text-align: center; ">Antecedentes Personales del Alumno(a)</td>
    <td class='celdaNombreAttr' style='text-align: right'>Fecha:</td>
    <td class='textoTabla'><input type='text' size='8' name='fecha' id='fecha_liq' value='<?php echo($fecha_liq); ?>' onChange="submitform();"></td>
    <script type='text/javascript' charset='utf-8'>  var k1 = new Kalendae.Input('fecha_liq', { format: 'DD-MM-YYYY', weekStart: 1 } ); </script>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='text-align: right'>RUT:</td>
    <td class='textoTabla' colspan='2'><?php echo($rut); ?></td>
    <td class='celdaNombreAttr' style='text-align: right'>ID:</td>
    <td class='textoTabla' colspan='3'><?php echo($id_alumno); ?></td>
    <td class='celdaNombreAttr' style='text-align: right'>Valor UF:</td>
    <td class='textoTabla'>$<?php echo(number_format($valor_uf,2,',','.')); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='text-align: right'>Nombre:</td>
    <td class='textoTabla' colspan='6'><?php echo($nombre); ?></td>
    <td class='celdaNombreAttr' style='text-align: right'>Últ. Créd. Int.:</td>
    <td class='textoTabla'>UF <?php echo($ult_cred_interno); ?></td>
  </tr>
  <tr class='filaTituloTabla'><td class='tituloTabla' colspan='9'>Contratos</td></tr>
  <tr class='filaTituloTabla'>
    <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Nº<br>Estado</td>
    <td class='tituloTabla'>Periodo<br>y Tipo</td>    
    <td class='tituloTabla'>Arancel</td>
    <td class='tituloTabla'>Beca</td>
    <td class='tituloTabla'>Crédito<br>Interno</td>
    <td class='tituloTabla'>Arancel<br>Efectivo</td>
    <td class='tituloTabla'>Monto Pagado<br>[Saldo Total]</td>
    <td class='tituloTabla'>Monto<br>Moroso</td>
    <td class='tituloTabla'>Fecha y<br>Emisor</td>
  </tr>
  <?php echo($HTML_contratos); ?>
  <tr>
    <td class='celdaNombreAttr' colspan='4' style='vertical-align: middle'>Descuentos:</td>
    <td class='celdaValorAttr' style='text-align: right'>(<b>UF</b> <input type='text' size='2' name='descuento' value='<?php echo($_REQUEST['descuentos']); ?>' class='montos' onChange="if (verif_descto(this.value)) { calc_valores(); }" style='color: red'>)</td>
    <td class='celdaValorAttr' style='text-align: right'>($<input type='text' size='4' name='descuento_inicial' value='<?php echo($descuento_inicial); ?>' class='montos' style='color: red' readonly>)</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan='4' style='vertical-align: middle'>Monto Adicional:</td>
    <td class='celdaValorAttr' style='text-align: right'><b>UF</b> <input type='text' size='2' name='monto_adicional_uf' value='<?php echo($_REQUEST['monto_adicional_uf']); ?>' onChange="calc_valores(this.name);" class='montos'>&nbsp;</td>
    <td class='celdaValorAttr' style='text-align: right'>$<input type='text' size='4' name='monto_adicional' value='0' class='montos' onChange="calc_valores(this.name);" onkeyup="puntitos(this,this.value.charAt(this.value.length-1),'monto_adicional')">&nbsp;</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan='4' style='vertical-align: middle'>Valor Total del Convenio:</td>
    <td class='celdaValorAttr' style='text-align: right'><b>UF</b> <input type='text' size='2' name='monto_convenio_uf' value='<?php echo($monto_convenio_uf); ?>' class='montos' readonly>&nbsp;</td>
    <td class='celdaValorAttr' style='text-align: right'>$<input type='text' size='4' name='monto_convenio' value='<?php echo($monto_convenio); ?>' class='montos' readonly>&nbsp;</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan='4'>Comentarios:</td>
    <td class='celdaValorAttr' colspan='5'><textarea name='comentarios' class='general' rows='4' cols='50'><?php echo($comentarios); ?></textarea></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="9"><input type='submit' name='guardar' value='Guardar y Continuar'></td></tr>
</table>
<?php } ?>
</form>
</div>

<?php if (count($alumno) > 1) { ?>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'><td class='tituloTabla' colspan='8'>Seleccionar alumno</td></tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>RUT</td>
    <td class='tituloTabla'>Nombre</td>
    <td class='tituloTabla'>Carrera</td>
    <td class='tituloTabla'>Regimen</td>
    <td class='tituloTabla'>Cohorte</td>
    <td class='tituloTabla'>Estado</td>
    <td class='tituloTabla'>Mat?</td>
  </tr>
  <?php echo($HTML_alumnos); ?>
</table>
<?php } ?>

<script>
	function calc_valores(campo) {
		var monto_liqci_uf      = Number(formulario.monto_liqci_uf.value.replace(',','.')),
		    descuento           = Number(formulario.descuento.value.replace(',','.')),
		    monto_adicional     = Number(formulario.monto_adicional.value.replace('.','').replace('.','')),
		    valor_uf            = Number(formulario.valor_uf.value),
		    monto_adicional_uf  = Number(formulario.monto_adicional_uf.value.replace(',','.')),
		    tot_cred_interno    = 0,
		    descuento_incial    = 0;
		
		monto_liqci                   = Math.round(monto_liqci_uf * valor_uf);
		formulario.monto_liqci.value  = monto_liqci;
		
		descuento_inicial                  = Math.round(descuento * valor_uf);
		formulario.descuento_inicial.value = descuento_inicial;
		
		if (campo == "monto_adicional_uf") { monto_adicional = 0; }
		if (campo == "monto_adicional") { monto_adicional_uf = 0; }
		
		if (monto_adicional_uf > 0 || campo=="monto_adicional_uf") {
			monto_adicional                  = Math.round(monto_adicional_uf * valor_uf);
			formulario.monto_adicional.value = monto_adicional;
		}
		
		if (monto_adicional > 0 || campo=="monto_adicional") {
			monto_adicional_uf                  = Math.round((monto_adicional / valor_uf) * 100) / 100;
			formulario.monto_adicional_uf.value = monto_adicional_uf;
		}
		
		formulario.monto_convenio_uf.value = Math.round((monto_liqci_uf - descuento + monto_adicional_uf) * 100) / 100;
		
		formulario.monto_convenio.value    = Math.round(monto_liqci - descuento_inicial + monto_adicional);
		
		puntitos(document.formulario.monto_liqci,document.formulario.monto_liqci.value.charAt(document.formulario.monto_liqci.value.length-1),document.formulario.monto_liqci.name);
		puntitos(document.formulario.descuento_inicial,document.formulario.descuento_inicial.value.charAt(document.formulario.descuento_inicial.value.length-1),document.formulario.descuento_inicial.name);
		puntitos(document.formulario.monto_adicional,document.formulario.monto_adicional.value.charAt(document.formulario.monto_adicional.value.length-1),document.formulario.monto_adicional.name);
		puntitos(document.formulario.monto_convenio,document.formulario.monto_convenio.value.charAt(document.formulario.monto_convenio.value.length-1),document.formulario.monto_convenio.name);

	}
	
	function verif_descto(descuento_uf) {
		var monto_convenio_uf = Number(formulario.monto_convenio_uf.value.replace(',','.'));
		
		if (monto_convenio_uf < descuento_uf) {
			alert("El monto de Descuento ingresado es superior al Total Crédito(s) Interno(s). No es posible hacer esta acción.");
			formulario.restablecer.click();
			return false;
		}
		
		return true;
	}
	
	calc_valores();
	
</script>
