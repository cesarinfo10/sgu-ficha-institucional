<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_contrato = $_REQUEST['id_contrato'];

if (!is_numeric($id_contrato)) {
	echo(js("location.href='$enlbase=gestion_contratos';"));
	exit;
}

$SQL_contrato = "SELECT c.*,coalesce(va.id,vp.id) AS id,coalesce(va.rut,vp.rut) AS rut,coalesce(va.nombre,vp.nombre) AS nombre,
                        coalesce(va.direccion,vp.direccion) AS direccion,coalesce(va.comuna,vp.comuna) AS comuna,
                        coalesce(va.telefono,vp.telefono) AS telefono,coalesce(va.tel_movil,vp.tel_movil) AS tel_movil,
                        coalesce(va.region,vp.region) AS region,ca.nombre AS carrera,coalesce(al.id_aval,pap.id_aval) AS id_aval,
                        CASE c.jornada WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada,
                        coalesce(be.nombre,'Ninguna') AS beca_externa,coalesce(c.cod_beca_mat,'Ninguna') beca_mat,
                        coalesce(con.nombre||' - 	20%','Ninguno') AS convenio,
                        coalesce(b.nombre,'Ninguna') AS beca_arancel,to_char(c.fecha,'DD-MM-YYYY HH24:MI') AS fecha,
                        pc.id AS id_pagare_colegiatura,pci.id AS id_pagare_cred_interno,c.monto_condonacion,c.motivo_condonacion,
                        to_char(c.fecha_condonacion,'DD-MM-YYYY') AS fecha_cond,ca.regimen,c.estado AS id_estado,c.comentarios,
                        CASE c.estado 
                              WHEN 'E' THEN 'Emitido' 
                              WHEN 'F' THEN 'Firmado' 
                              WHEN 'R' THEN 'Retirado' 
                              WHEN 'S' THEN 'Suspendido' 
                              WHEN 'A' THEN 'Abandonado' 
                              WHEN 'Z' THEN 'Reemplazado' 
                              ELSE 'Nulo'
                         END AS estado,to_char(c.estado_fecha,'DD-tmMon-YYYY HH24:MI') AS estado_fecha,u.nombre_usuario AS estado_usuario,
                         vc.monto_moroso,beca_conectividad_monto,to_char(beca_conectividad_fecha,'DD-tmMon-YYYY HH24:MI') AS beca_conectividad_fecha,
                         u2.nombre_usuario as beca_conectividad_operador
                 FROM finanzas.contratos                 AS c
                 LEFT JOIN vista_contratos AS vc USING (id)
                 LEFT JOIN alumnos                       AS al  ON al.id=c.id_alumno
                 LEFT JOIN vista_alumnos                 AS va  ON va.id=c.id_alumno
                 LEFT JOIN vista_pap                     AS vp  ON vp.id=c.id_pap
                 LEFT JOIN pap                                  ON pap.id=c.id_pap
                 LEFT JOIN carreras                      AS ca  ON ca.id=c.id_carrera
                 LEFT JOIN finanzas.becas_externas       AS be  ON be.id=c.id_beca_externa
                 LEFT JOIN convenios                     AS con ON con.id=c.id_convenio
                 LEFT JOIN becas                         AS b   ON b.id=c.id_beca_arancel
                 LEFT JOIN finanzas.pagares_colegiatura  AS pc  ON pc.id_contrato=c.id
	             LEFT JOIN finanzas.pagares_cred_interno AS pci ON pci.id_contrato=c.id
	             LEFT JOIN usuarios						 AS u   ON u.id=c.estado_id_usuario
	             LEFT JOIN usuarios						 AS u2  ON u2.id=c.beca_conectividad_id_operador
                 WHERE c.id=$id_contrato";
$contrato     = consulta_sql($SQL_contrato);
if (count($contrato) == 0) {
	echo(js("location.href='$enlbase=gestion_contratos';"));
	exit;
}

$SQL_cobros = "SELECT c.id,to_char(c.fecha_venc,'DD-tmMon-YYYY') as fec_venc,c.fecha_venc,g.nombre AS glosa,monto,nro_cuota,
                      CASE WHEN pagado THEN 'Si' ELSE 'No' END AS pagado,
                      CASE WHEN abonado THEN 'Si' ELSE 'No' END AS abonado,
                      monto_abonado,id_glosa,
                      (SELECT char_comma_sum(coalesce('B/'||nro_boleta::text,'BE/'||nro_boleta_e::text,'F/'||nro_factura::text,'')) FROM (SELECT nro_boleta,nro_boleta_e,nro_factura FROM finanzas.pagos WHERE id IN (SELECT id_pago FROM finanzas.pagos_detalle WHERE id_cobro=c.id) ORDER by id) AS foo) AS nro_docto,
                      (SELECT char_comma_sum(id_pago::text) FROM (SELECT id_pago FROM finanzas.pagos_detalle WHERE id_cobro=c.id ORDER BY id_pago) AS foo) AS id_pago,
                      (SELECT char_comma_sum(to_char(fecha,'DD-tmMon-YYYY')) FROM (SELECT fecha FROM finanzas.pagos WHERE id IN (SELECT id_pago FROM finanzas.pagos_detalle WHERE id_cobro=c.id) ORDER BY id) AS foo) AS fecha_pago
               FROM finanzas.cobros c
               LEFT JOIN finanzas.glosas g ON g.id=c.id_glosa
               WHERE c.id_contrato=$id_contrato
               ORDER BY c.fecha_venc,c.id";
$cobros     = consulta_sql($SQL_cobros);
if (count($cobros) == 0 && $contrato[0]['monto_condonacion'] == 0) { generar_cobros_contrato($id_contrato); $cobros = consulta_sql($SQL_cobros); }

$cobros_arancel = consulta_sql("SELECT id FROM finanzas.cobros WHERE id_contrato=$id_contrato AND id_glosa > 1");
if (count($cobros_arancel) == 0 && $contrato[0]['monto_condonacion'] == "") { generar_cobros_contrato($id_contrato); $cobros = consulta_sql($SQL_cobros); }


$HTML_cobros = $HTML_cobros_vencidos = "";
$deuda_total = $deuda_vencida = $total_pagado = 0;
$primer_nopag = false;
for ($x=0;$x<count($cobros);$x++) {
	extract($cobros[$x]);

	$monto_f = number_format($monto,0,',','.');

	$saldo_f = "";
	$saldo   = 0;
	if ($abonado == "Si") { 
		$saldo   = $monto - $monto_abonado;
		$saldo_f = "($".number_format($saldo,0,',','.').")";
	}
	
	if (!$primer_nopag && $pagado == "No" && $abonado == "No" && ($id_glosa==1 || $id_glosa==2 || $id_glosa==2 || $id_glosa==21)) {
		$enl_fec_venc = "$enlbase_sm=form_matricula_cambiar_fec_venc&id_contrato=$id_contrato&id_cobro=$id";
		$fec_venc     = "<a id='sgu_fancybox_small' href='$enl_fec_venc' class='boton' title='Prorrogar vencimiento' style='font-size: 8pt'>$fec_venc</a>";
		$primer_nopag = true;
	}

	if ($pagado == "Si") {
		$total_pagado += $monto;
		$abonado = "";
	} elseif ($abonado == "Si") {
		$total_pagado += $monto_abonado;
		$deuda_total += $monto - $monto_abonado;
	} else {
		$deuda_total += $monto - $monto_abonado;
	}

	$nro_docto = explode(",",$nro_docto);
	$id_pago    = explode(",",$id_pago);
	$fecha_pago = str_replace(",","<br>",$fecha_pago);
	
	$nro_doc = "";
	for($i=0;$i<count($nro_docto);$i++) {
		$nro_doc .= "<a href='$enlbase_sm=ver_pago&id_pago={$id_pago[$i]}' id='sgu_fancybox_medium' class='enlaces'>{$nro_docto[$i]}</a><br>";
	}
	$nro_docto = $nro_doc;
	
	$id_pago = str_replace(",","<br>",implode(",",$id_pago));
	
	$HTML = "<tr class='filaTabla' style='vertical-align: middle'>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: center; color: #7F7F7F'>$id</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='right'>$fec_venc</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle'>$glosa</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='right'>$nro_cuota</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='right'>$$monto_f</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='center'><span class='$pagado'>$pagado</span></td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='center'><span class='$abonado'>$abonado <small>$saldo_f</small></span></td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='right'>$nro_docto</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: right; color: #7F7F7F'>$id_pago</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='right'>$fecha_pago</td>\n"
		  . "</tr>\n";

	if (strtotime($fecha_venc) < time()) { 
		$HTML_cobros_vencidos .= $HTML;
		if ($pagado == "No") { $deuda_vencida += $monto - $monto_abonado; }
	} else { 
		$HTML_cobros .= $HTML;
	}
}

if ($contrato[0]['monto_moroso'] > 0) { $estado_financiero = " <span style='color: #FF0000'><b>Moroso</b></span>"; }
if ($contrato[0]['morosidad_manual'] == "t") { $morosidad_manual = " <span style='color: #FF0000'><b>M.M.</b></span>"; }

$deuda_vencida = number_format($deuda_vencida,0,',','.');
$total_pagado  = number_format($total_pagado,0,',','.');
$HTML_cobros_vencidos .= "<tr><td class='textoTabla' align='right' colspan='5'>"
					  .  "  <b><span class='Si'>Total Pagado: $$total_pagado</span></b></td><td class='textoTabla' colspan='5'> </td></tr>\n"
                      .  "<tr><td class='textoTabla' align='right' colspan='5'>"
					  .  "  <b><span class='No'>Deuda Vencida: $$deuda_vencida</span></b></td><td class='textoTabla' colspan='5'> </td></tr>\n";

$deuda_total = number_format($deuda_total,0,',','.');                      
$HTML_cobros .= "<tr><td class='textoTabla' align='right' colspan='5'>"
			 .  "  <b>Deuda Total <small>(incluye Deuda Vencida)</small>: $$deuda_total</b></td><td class='textoTabla' colspan='5'> </td></tr>\n";

$id_aval  = $contrato[0]['id_aval'];
$SQL_aval = "SELECT id,rf_rut,rf_nombre,rf_direccion,rf_com,rf_reg
			 FROM vista_avales WHERE id=$id_aval;";
$aval     = consulta_sql($SQL_aval);

$monto_mat_financiar     = $contrato[0]['monto_matricula'];
$monto_arancel_financiar = $contrato[0]['monto_arancel'];

if ($contrato[0]['porc_beca_mat'] > 0)  { $monto_mat_financiar -= $monto_mat_financiar * ($contrato[0]['porc_beca_mat'] / 100); }
if ($contrato[0]['monto_beca_mat'] > 0) { $monto_mat_financiar -= $contrato[0]['monto_beca_mat']; }

if ($contrato[0]['porc_beca_arancel'] > 0)  { $monto_arancel_financiar -= $monto_arancel_financiar * ($contrato[0]['porc_beca_arancel'] / 100); }
if ($contrato[0]['monto_beca_arancel'] > 0) { $monto_arancel_financiar -= $contrato[0]['monto_beca_arancel']; }
if ($contrato[0]['id_convenio'] > 0)        { $monto_arancel_financiar -= $monto_arancel_financiar * 0.2; }


switch (trim($contrato[0]['tipo'])) {
	case "Anual":
		$tipo_contrato = "al_antiguo";
		if ($contrato[0]['id_pap'] > 0) { $tipo_contrato = "al_nuevo"; }
		if ($contrato[0]['regimen'] == "DIP") { $tipo_contrato .= "_DIP"; }
		if ($contrato[0]['regimen'] == "DIP-D") { $tipo_contrato .= "_DIP-D"; }
		if ($contrato[0]['regimen'] == "POST-G") { $tipo_contrato .= "_POST"; }
		break;
	case "Semestral":
		$tipo_contrato = "al_antiguo";
		if ($contrato[0]['id_pap'] > 0) { $tipo_contrato = "al_nuevo"; }
		if ($contrato[0]['regimen'] == "DIP") { $tipo_contrato .= "_DIP"; }
		if ($contrato[0]['regimen'] == "DIP-D") { $tipo_contrato .= "_DIP-D"; }
		break;
	case "Modular":
		$tipo_contrato = "al_antiguo";
		if ($contrato[0]['id_pap'] > 0) { $tipo_contrato = "al_nuevo"; }
		break;
	case "Estival":
		$tipo_contrato = "estival";
		break;
	case "Egresado":
		$tipo_contrato = "al_egresado";
		break;
}

if ($contrato[0]['regimen'] == "POST") { $tipo_contrato .= "_POST"; }

$estado_contrato = $color = "";
switch ($contrato[0]['id_estado']) {
	case "E":
		$color = "#009900";
		break;
	case "A" || "S" || "R":
		$color = "#BFBFBF";
		break;
	case "":
		$color = "#FF0000";
		break;
}
$estado_contrato = "<b><span style='color: $color'>{$contrato[0]['estado']}</span></b>";
if ($contrato[0]['estado_fecha'] <> "") { $estado_contrato .= " desde el {$contrato[0]['estado_fecha']} por {$contrato[0]['estado_usuario']}"; }

$enl_contrato = "contrato.php?id_contrato=$id_contrato&tipo=$tipo_contrato";
$boton_pag_coleg = $boton_pag_cred_int = $boton_anexo_contrato_ley21369 = "";
if ($contrato[0]['id_pagare_colegiatura'] <> "") {
	$enl_pag_colegiatura = "pagare_colegiatura.php?id_pagare_colegiatura={$contrato[0]['id_pagare_colegiatura']}";
	$boton_pag_coleg     = "<input type='button' value='Pagaré de Colegiatura' onClick=\"window.open('$enl_pag_colegiatura');\">";
}

if ($contrato[0]['id_pagare_cred_interno'] <> "") {	
	$enl_pag_cred_int   = "pagare_cred_interno.php?id_pagare_cred_interno={$contrato[0]['id_pagare_cred_interno']}";
	$boton_pag_cred_int = "<input type='button' value='Pagaré de Crédito Interno' onClick=\"window.open('$enl_pag_cred_int');\">";
}

$enl_anexo_ley21369            = "contrato_anexo_ley21369.php?id_contrato=$id_contrato";
$boton_anexo_contrato_ley21369 = "<input type='button' value='Anexo Ley 21369' onClick=\"window.open('$enl_anexo_ley21369');\">";

?>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>
<?php if($_REQUEST['imprimir'] <> "Imprimir") { ?>
<table cellpadding="1" border="0" cellspacing="2" width="auto" style="margin-top: 5px">
  <tr>
    <td class="celdaFiltro">
      Acciones:<br>
      <a href='#' onClick="window.open('<?php echo("$enlbase_sm=$modulo&id_contrato=$id_contrato&imprimir=Imprimir"); ?>');" class="boton">Imprimir</a>
      <a href='#' onClick="window.open('<?php echo("$enlbase_sm=$modulo&id_contrato=$id_contrato&imprimir=Imprimir&obs=NO"); ?>');" class="boton">Imprimir s/Obs</a>
      <a href="<?php echo("$enlbase_sm=form_matricula_estado&id_contrato=$id_contrato"); ?>" id='sgu_fancybox_small' class="boton">Estado</a>
      <a href="<?php echo("$enlbase_sm=form_matricula_comentarios&id_contrato=$id_contrato"); ?>" id='sgu_fancybox_small' class="boton">Observaciones</a>
      <a href="#" onClick="history.back();" class="boton">Volver</a>
    </td>
    <td class="celdaFiltro">
      Documentos formateados:<br>
      <input type="button" value="Contrato" onClick="window.open('<?php echo($enl_contrato); ?>');">
      <?php echo($boton_pag_coleg); ?>
      <?php echo($boton_pag_cred_int); ?>
      <?php echo($boton_anexo_contrato_ley21369); ?>
    </td>
  </tr>
</table>
<?php } ?>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style="margin-top: 5px">
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Generales del Contrato</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Número:</td>
    <td class='celdaValorAttr'><?php echo($id_contrato); ?></td>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['fecha']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Estado:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($estado_contrato . $estado_financiero  . $morosidad_manual); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tipo:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['tipo']); ?></td>
    <td class='celdaNombreAttr'>Periodo:</td>
    <td class='celdaValorAttr'><?php if ($contrato[0]['semestre']<>"") { echo($contrato[0]['semestre'].'-'); } echo($contrato[0]['ano']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Alumno</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['rut']); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['id']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($contrato[0]['nombre']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Dirección:</td>
    <td class='celdaValorAttr' colspan="3">
      <?php echo($contrato[0]['direccion']); ?>, <?php echo($contrato[0]['comuna']); ?>, <?php echo($contrato[0]['region']); ?>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Teléfono:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['telefono']); ?></td>
    <td class='celdaNombreAttr'>Tél. Móvil:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['tel_movil']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Beca Externa:</td>
    <td class='celdaValorAttr' colspan="3">
      <?php echo($contrato[0]['beca_externa']); ?>
      <small>(Esta beca no genera descuentos ni es una forma de pago)</small>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr' colspan="4">
      <?php echo($contrato[0]['carrera']); ?> <b>jornada:</b> <?php echo($contrato[0]['jornada']); ?>
      en el <b>nivel</b> <?php echo($contrato[0]['nivel']); ?>º
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">
      Antecedentes del Responsable Financiero
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
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center">Valores Nominales y Descuentos (Matrícula y/o Arancel)</td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'><u>Monto Matrícula:</u></td>
    <td class='celdaValorAttr'>$<?php echo(number_format($contrato[0]['monto_matricula'],0,',','.')); ?></td>
    <td class='celdaNombreAttr'><u>Monto Arancel:</u></td>
    <td class='celdaValorAttr'>$<?php echo(number_format($contrato[0]['monto_arancel'],0,',','.')); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Beca (Matrícula):</u></td>
    <td class='celdaValorAttr' colspan="3">
      <?php echo($contrato[0]['beca_mat']); ?>
      <b>Monto:</b> $<?php echo(number_format($contrato[0]['monto_beca_mat'],0,',','.')); ?> ó
      <b>Porcentaje:</b> <?php echo(number_format($contrato[0]['porc_beca_mat'],0,',','.')); ?>%
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Convenio (Arancel):</u></td>
    <td class='celdaValorAttr' colspan="3"><?php echo($contrato[0]['convenio']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Beca (Arancel):</u></td>
    <td class='celdaValorAttr' colspan="3">
      <?php echo($contrato[0]['beca_arancel']); ?>
      <b>Monto:</b> $<?php echo(number_format($contrato[0]['monto_beca_arancel'],0,',','.')); ?> ó
      <b>Porcentaje:</b> <?php echo(number_format($contrato[0]['porc_beca_arancel'],0,',','.')); ?>%
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="2">Financiamiento:</td>
    <td class='celdaValorAttr' colspan="2"><?php echo($contrato[0]['financiamiento']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="2" style="text-align: center">Matrícula</td>
    <td class='celdaNombreAttr' colspan="2" style="text-align: center">Arancel</td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'><u>Monto a Financiar:</u></td>
    <td class='celdaValorAttr'>$<?php echo(number_format($monto_mat_financiar,0,',','.')); ?></td>
    <td class='celdaNombreAttr'><u>Monto a Financiar:</u></td>
    <td class='celdaValorAttr'>$<?php echo(number_format($monto_arancel_financiar,0,',','.')); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Efectivo:</td>
    <td class='celdaValorAttr'>$<?php echo(number_format($contrato[0]['mat_efectivo'],0,',','.')); ?></td>
    <td class='celdaNombreAttr'>Efectivo:</td>
    <td class='celdaValorAttr'>$<?php echo(number_format($contrato[0]['arancel_efectivo'],0,',','.')); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cheque(s):</td>
    <td class='celdaValorAttr'>
      $<?php echo(number_format($contrato[0]['mat_cheque'],0,',','.')); ?>
      <small><b>Cant:</b> <?php echo(number_format($contrato[0]['mat_cant_cheques'],0,',','.')); ?></small>
    </td>
    <td class='celdaNombreAttr'>Cheque(s):</td>
    <td class='celdaValorAttr'>
      $<?php echo(number_format($contrato[0]['arancel_cheque'],0,',','.')); ?>
      <small>
        <b>Cant.:</b> <?php echo(number_format($contrato[0]['arancel_cant_cheques'],0,',','.')); ?><br>
        <b>Día venc.:</b> <?php echo(number_format($contrato[0]['arancel_diap_cheque'],0,',','.')); ?>
        <b>Mes inicio:</b> <?php echo(number_format($contrato[0]['arancel_mes_ini_cheque'],0,',','.')); ?>
        <b>Año inicio:</b> <?php echo(number_format($contrato[0]['arancel_ano_ini_cheque'],0,',','.')); ?>
      </small>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tarjetas de Crédito:</td>
    <td class='celdaValorAttr'>
      $<?php echo(number_format($contrato[0]['mat_tarj_cred'],0,',','.')); ?>
      <small><b>Cant:</b> <?php echo(number_format($contrato[0]['mat_cant_tarj_cred'],0,',','.')); ?></small>
    </td>
    <td class='celdaNombreAttr'>Pagaré Colegiatura:</td>
    <td class='celdaValorAttr'>
      $<?php echo(number_format($contrato[0]['arancel_pagare_coleg'],0,',','.')); ?>
      <small>
        <b>Cuotas:</b> <?php echo(number_format($contrato[0]['arancel_cuotas_pagare_coleg'],0,',','.')); ?><br>
        <b>Día pago:</b> <?php echo(number_format($contrato[0]['arancel_diap_pagare_coleg'],0,',','.')); ?>
        <b>Mes inicio:</b> <?php echo($meses_palabra[$contrato[0]['arancel_mes_ini_pagare_coleg']-1]['nombre']); ?>
      </small>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td class='celdaNombreAttr'>Crédito Interno:</td>
    <td class='celdaValorAttr'>$<?php echo(number_format($contrato[0]['arancel_cred_interno'],0,',','.')); ?></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td class='celdaNombreAttr'>Tarjetas de Cŕédito:</td>
    <td class='celdaValorAttr'>
      $<?php echo(number_format($contrato[0]['arancel_tarjeta_credito'],0,',','.')); ?>
      <small><b>Cant:</b> <?php echo(number_format($contrato[0]['arancel_cant_tarj_cred'],0,',','.')); ?></small>
    </td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="4" align="center">
      <small><b>Cant:</b> Se refiere a Cantidad de Cheques o Cantidad de Cuotas de Tarjetas</small>
    </td>
  </tr>
<?php if ($contrato[0]['monto_condonacion'] > 0) { ?>  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center">Condonación</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['fecha_cond']); ?></td>
    <td class='celdaNombreAttr'>Monto:</td>
    <td class='celdaValorAttr'>$<?php echo(number_format($contrato[0]['monto_condonacion'],0,',','.')); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: left">Motivo:</td></tr>
  <tr><td class='celdaValorAttr' colspan="4"><?php echo(nl2br($contrato[0]['motivo_condonacion'])); ?></td></tr>
<?php } ?>
<?php if ($contrato[0]['beca_conectividad_monto'] > 0) { ?>  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center">Beca Conectividad</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['beca_conectividad_fecha']); ?> por <?php echo($contrato[0]['beca_conectividad_operador']); ?></td>
    <td class='celdaNombreAttr'>Monto:</td>
    <td class='celdaValorAttr' style="background: #FFFF00">$<?php echo(number_format($contrato[0]['beca_conectividad_monto'],0,',','.')); ?></td>
  </tr>
<?php } ?>
<?php if ($contrato[0]['comentarios'] <> "" && $_REQUEST['obs'] == "") { ?>  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center">Observaciones</td></tr>
  <tr><td class='celdaValorAttr' colspan="4"><?php echo(nl2br(str_replace("###","blockquote",wordwrap($contrato[0]['comentarios'],90)))); ?></td></tr>
<?php } ?>
</table>
<?php if($_REQUEST['imprimir'] <> "Imprimir") { ?>
<table cellpadding="1" border="0" cellspacing="2" width="auto" style="margin-top: 5px">
  <tr>
    <td class="celdaFiltro">
      <a id="cobros">Acciones:</a><br>
      <a href='<?php echo("$enlbase_sm=emitir_boleta_electronica&rut={$contrato[0]['rut']}"); ?>' class='boton' id='sgu_fancybox_medium'>Pagar (Bol-E)</a>
      <!-- <a href='<?php echo("$enlbase_sm=gestion_caja2&rut={$contrato[0]['rut']}"); ?>' class='boton' id='sgu_fancybox_medium'>Pagar</a> -->
      <!-- <a href="<?php echo("$enlbase_sm=contrato_registrar_cheques&id_contrato=$id_contrato"); ?>" id='sgu_fancybox' class="boton">Registrar Cheques</a> -->
      <a href="<?php echo("$enlbase_sm=contrato_registrar_cheques_bol-e&id_contrato=$id_contrato"); ?>" id='sgu_fancybox' class="boton">Registrar Cheques (Bol-E)</a>
      &nbsp;
      <a id='sgu_fancybox' href='<?php echo("$enlbase_sm=gestion_boletas&texto_buscar={$contrato[0]['rut']}&buscar=Buscar"); ?>' class='boton'>Ver boletas asociadas</a>
      &nbsp;
      <a id='sgu_fancybox_small' href='<?php echo("$enlbase_sm=form_matricula_cambiar_dia_pago&id_contrato=$id_contrato"); ?>' class='boton'>Cambiar día de pago</a>
      <a href="<?php echo("$enlbase_sm=contrato_renegociar&id_contrato=$id_contrato"); ?>" id='sgu_fancybox_small' class="boton">Renegociación</a>
      <a href="<?php echo("$enlbase_sm=contrato_condonar&id_contrato=$id_contrato"); ?>" id='sgu_fancybox_small' class="boton">Condonación</a>
      &nbsp;
      <a href="#" onClick="history.back();" class="boton">Volver</a>
    </td>
  </tr>
</table>
<?php } ?>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr class='filaTituloTabla'>
    <td colspan="10" class='tituloTabla'>
      Cobros Asociados<br><small>Contrato Nº <?php echo($id_contrato." ".$contrato[0]['rut']." ".$contrato[0]['nombre']); ?></small>
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='color: #7F7F7F'>ID</td>
    <td class='tituloTabla'>Fecha<br>Vencimiento</td>
    <td class='tituloTabla'>Glosa</td>
    <td class='tituloTabla'>Nº<br>Cuota</td>
    <td class='tituloTabla'>Monto</td>
    <td class='tituloTabla'>Pagado?</td>
    <td class='tituloTabla'>Abono? <small>(saldo)</small></td>
    <td class='tituloTabla'>Nº<br>Boleta</td>
    <td class='tituloTabla' style='color: #7F7F7F'>ID<br>Pago</td>
    <td class='tituloTabla'>Fecha<br>Pago</td>
  </tr>
  <tr><td colspan="10" class='textoTabla'><i>Cobros Vencidos</i></td></tr>
  <?php echo($HTML_cobros_vencidos); ?>
  <tr><td colspan="10" class='textoTabla'><i>Cobros por Vencer</i></td></tr>
  <?php echo($HTML_cobros); ?>
</table>
<?php 
if($_REQUEST['imprimir'] == "Imprimir") { 
	echo(js("window.print();"));
	echo(js("window.close();"));
}
?>
<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 1200,
		'height'			: 400,
		'maxHeight'			: 800,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_medium").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 1000,
		'maxHeight'			: 600,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 800,
		'height'			: 600,
		'maxHeight'			: 800,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});
</script>
<!-- Fin: <?php echo($modulo); ?> -->

<?php

function generar_cobros_contrato($id_contrato) {
	$contrato = consulta_sql("SELECT * FROM finanzas.contratos WHERE id=$id_contrato");
	if (count($contrato) == 1) {
		if ($contrato[0]['arancel_pagare_coleg'] > 0 && $contrato[0]['arancel_cuotas_pagare_coleg'] > 0) {
			$id_glosa    = 2; // mensualidad de pagare de colegiatura
			$cant_cuotas = $contrato[0]['arancel_cuotas_pagare_coleg'];
			$monto_cuota = intval($contrato[0]['arancel_pagare_coleg']/$contrato[0]['arancel_cuotas_pagare_coleg']);
			$monto_total = $contrato[0]['arancel_pagare_coleg'];
			$diap        = $contrato[0]['arancel_diap_pagare_coleg'];
			$mesp        = $contrato[0]['arancel_mes_ini_pagare_coleg'];
			$anop        = $contrato[0]['arancel_ano_ini_pagare_coleg'];
			$SQL_cobros  = generar_cobros($id_contrato,$id_glosa,$cant_cuotas,$monto_cuota,$monto_total,$diap,$mesp,$anop);
			consulta_sql($SQL_cobros);
		}

		if ($contrato[0]['arancel_cheque'] > 0 && $contrato[0]['arancel_cant_cheques'] > 0) {
			$id_glosa    = 21; // mensualidad cheques
			$cant_cuotas = $contrato[0]['arancel_cant_cheques'];
			$monto_total = $contrato[0]['arancel_cheque'];
			$monto_cuota = intval($monto_total/$cant_cuotas);
			$diap        = $contrato[0]['arancel_diap_cheque'];
			$mesp        = $contrato[0]['arancel_mes_ini_cheque'];
			$anop        = $contrato[0]['arancel_ano_ini_cheque'];
			$SQL_cobros  = generar_cobros($id_contrato,$id_glosa,$cant_cuotas,$monto_cuota,$monto_total,$diap,$mesp,$anop);
			consulta_sql($SQL_cobros);
		}
		
		if ($contrato[0]['arancel_efectivo'] > 0) {
			$id_glosa    = 3; // arancel completo
      if ($contrato[0]['ano'] > date("Y")) { $id_glosa = 10003; } // arancel completo anticipado
			$cant_cuotas = 1;
			$monto_total = $contrato[0]['arancel_efectivo'];
			$monto_cuota = intval($monto_total/$cant_cuotas);
			$diap        = strftime("%d",strtotime($contrato[0]['fecha']));
			$mesp        = strftime("%m",strtotime($contrato[0]['fecha']));
			$anop        = strftime("%Y",strtotime($contrato[0]['fecha']));
			$SQL_cobros  = generar_cobros($id_contrato,$id_glosa,$cant_cuotas,$monto_cuota,$monto_total,$diap,$mesp,$anop);
			consulta_sql($SQL_cobros);
		}
		
		if ($contrato[0]['arancel_tarjeta_credito'] > 0) {
			$id_glosa    = 3; // arancel completo
      if ($contrato[0]['ano'] > date("Y")) { $id_glosa = 10003; } // arancel completo anticipado
			$cant_cuotas = 1;
			$monto_total = $contrato[0]['arancel_tarjeta_credito'];
			$monto_cuota = intval($monto_total/$cant_cuotas);
			$diap        = strftime("%d",strtotime($contrato[0]['fecha']));
			$mesp        = strftime("%m",strtotime($contrato[0]['fecha']));
			$anop        = strftime("%Y",strtotime($contrato[0]['fecha']));
			$SQL_cobros  = generar_cobros($id_contrato,$id_glosa,$cant_cuotas,$monto_cuota,$monto_total,$diap,$mesp,$anop);
			consulta_sql($SQL_cobros);
		}
		
		if ($_REQUEST['mat_efectivo'] > 0 || $_REQUEST['mat_cheque'] > 0 || $_REQUEST['mat_tarj_cred'] > 0) {
			$id_glosa    = 1; // Matricula
      if ($contrato[0]['ano'] > date("Y")) { $id_glosa = 10001; } // matricula anticipada
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
			$diap        = strftime("%d",strtotime($contrato[0]['fecha']));
			$mesp        = strftime("%m",strtotime($contrato[0]['fecha']));
			$anop        = strftime("%Y",strtotime($contrato[0]['fecha']));
			$SQL_cobros  = generar_cobros($id_contrato,$id_glosa,$cant_cuotas,$monto_cuota,$monto_total,$diap,$mesp,$anop);
			consulta_sql($SQL_cobros);
		}

/*
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
*/
	}
}

?>
