<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_convenio_ci = $_REQUEST['id_convenio_ci'];

if (!is_numeric($id_convenio_ci)) {
	echo(js("location.href='$enlbase=gestion_liquidaciones_cred_interno';"));
	exit;
}

$SQL_convenio_ci = "SELECT c.id,to_char(c.fecha,'DD-tmMon-YYYY') AS fecha,c.estado,
                           to_char(c.fecha_cambio_estado,'DD \"de\" tmMonth \"de\" YYYY \"a las\" HH24:MI \"horas\"') AS fecha_cambio_estado,
                            date_part('year',c.fecha) AS periodo,trim(a.rut) AS rut,c.id_alumno,
                            upper(a.apellidos)||' '||initcap(a.nombres) AS al_nombre,c.monto_liqci,
                            c.monto_liqci::float/uf.valor::float AS monto_liqci_uf,
                            c.descuento_inicial,c.monto_adicional,c.monto_adicional::float/uf.valor::float AS monto_adicional_uf,
                            c.descuento_inicial::float/uf.valor::float AS descuento_inicial_uf,
                            c.descuento_inicial::float*100/(c.monto_liqci+coalesce(c.monto_adicional,0))::float AS descuento_inicial_porc,
                            trim(car.nombre) AS carrera,CASE a.jornada WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada,
                            c.liqci_efectivo,c.liqci_cheque,coalesce(c.liqci_cant_cheques,0) AS liqci_cant_cheques,
                            c.liqci_pagare,coalesce(c.liqci_cuotas_pagare,0) AS liqci_cuotas_pagare,
                            c.liqci_diap_pagare,c.liqci_mes_ini_pagare,c.liqci_ano_ini_pagare,
                            c.liqci_tarj_credito,coalesce(c.liqci_cant_tarj_credito,0) AS liqci_cant_tarj_credito,
                            CASE WHEN c.monto_condonacion>0 THEN '(C)' END AS condonacion,c.monto_condonacion,
                            to_char(c.fecha_condonacion,'DD-MM-YYYY') AS fecha_condonacion,c.motivo_condonacion,
                            vc.total_pagado,vc.saldo_total,vc.monto_moroso,vc.cant_cuotas_morosas,
                            u.nombre_usuario AS emisor,c.comentarios,
                            a.direccion,va.comuna,va.region,a.telefono,a.tel_movil,a.email,a.genero,a.fec_nac,
                            a.cohorte,a.mes_cohorte,uf.valor AS valor_uf,c.nulo
                     FROM finanzas.convenios_ci AS c
                     LEFT JOIN finanzas.vista_convenios_ci AS vc USING (id)
                     LEFT JOIN alumnos         AS a   ON a.id=c.id_alumno
                     LEFT JOIN carreras        AS car ON car.id=a.carrera_actual
                     LEFT JOIN usuarios        AS u   ON u.id=c.id_emisor                             
                     LEFT JOIN vista_alumnos   AS va   ON va.id=c.id_alumno
                     LEFT JOIN finanzas.valor_uf AS uf ON uf.fecha=c.fecha
                     WHERE c.id=$id_convenio_ci";
$convenio_ci     = consulta_sql($SQL_convenio_ci);
if (count($convenio_ci) == 0) {
	echo(js("location.href='$enlbase=gestion_liquidaciones_cred_interno';"));
	exit;
}

$monto_final_uf = $convenio_ci[0]['monto_liqci_uf'] + $convenio_ci[0]['monto_adicional_uf'] - $convenio_ci[0]['descuento_inicial_uf'];

$SQL_cobros = "SELECT c.id,to_char(c.fecha_venc,'DD-tmMon-YYYY') as fec_venc,c.fecha_venc,g.nombre AS glosa,monto,monto_uf,nro_cuota,
                      CASE WHEN pagado THEN 'Si' ELSE 'No' END AS pagado,
                      CASE WHEN abonado THEN 'Si' ELSE 'No' END AS abonado,
                      monto_abonado,id_glosa,
                      (SELECT char_comma_sum(coalesce(nro_boleta::text,nro_boleta_e::text,'')) FROM (SELECT nro_boleta,nro_boleta_e FROM finanzas.pagos WHERE id IN (SELECT id_pago FROM finanzas.pagos_detalle WHERE id_cobro=c.id) ORDER by id) AS foo) AS nro_boleta,
                      (SELECT char_comma_sum(id_pago::text) FROM (SELECT id_pago FROM finanzas.pagos_detalle WHERE id_cobro=c.id ORDER BY id_pago) AS foo) AS id_pago,
                      (SELECT char_comma_sum(to_char(fecha,'DD-tmMon-YYYY')) FROM (SELECT fecha FROM finanzas.pagos WHERE id IN (SELECT id_pago FROM finanzas.pagos_detalle WHERE id_cobro=c.id) ORDER BY id) AS foo) AS fecha_pago
               FROM finanzas.cobros c
               LEFT JOIN finanzas.glosas g ON g.id=c.id_glosa
               WHERE c.id_convenio_ci=$id_convenio_ci
               ORDER BY c.fecha_venc,c.id";
$cobros     = consulta_sql($SQL_cobros);

if (count($cobros) == 0 && $convenio_ci[0]['monto_condonacion'] == 0) { generar_cobros_convenio_ci($id_convenio_ci); $cobros = consulta_sql($SQL_cobros);}

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
	
	if (!$primer_nopag && $pagado == "No" && $abonado == "No" && ($id_glosa==300)) {
		$enl_fec_venc = "$enlbase_sm=convenio_ci_cambiar_fec_venc&id_convenio_ci=$id_convenio_ci&id_cobro=$id";
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

	$nro_boleta = explode(",",$nro_boleta);
	$id_pago    = explode(",",$id_pago);
	$fecha_pago = str_replace(",","<br>",$fecha_pago);
	
	$nro_bol = "";
	for($i=0;$i<count($nro_boleta);$i++) {
		$nro_bol .= "<a href='$enlbase_sm=ver_pago&id_pago={$id_pago[$i]}' id='sgu_fancybox_medium' class='enlaces'>{$nro_boleta[$i]}</a><br>";
	}
	$nro_boleta = $nro_bol;
	
	if ($monto_uf > 0) { $monto_uf = "UF ".number_format($monto_uf,2,",","."); }
	
	$id_pago = str_replace(",","<br>",implode(",",$id_pago));
	
	$HTML = "<tr class='filaTabla' style='vertical-align: middle'>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: center; color: #7F7F7F'>$id</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='right'>$fec_venc</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle'>$glosa</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='right'>$nro_cuota</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='right'>$monto_uf $$monto_f</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='center'><span class='$pagado'>$pagado</span></td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='center'><span class='$abonado'>$abonado <small>$saldo_f</small></span></td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='right'>$nro_boleta</td>\n"
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
$deuda_vencida = number_format($deuda_vencida,0,',','.');
$total_pagado  = number_format($total_pagado,0,',','.');
$HTML_cobros_vencidos .= "<tr><td class='textoTabla' align='right' colspan='5'>"
					  .  "  <b><span class='Si'>Total Pagado: $$total_pagado</span></b></td><td class='textoTabla' colspan='5'> </td></tr>\n"
                      .  "<tr><td class='textoTabla' align='right' colspan='5'>"
					  .  "  <b><span class='No'>Deuda Vencida: $$deuda_vencida</span></b></td><td class='textoTabla' colspan='5'> </td></tr>\n";

$deuda_total = number_format($deuda_total,0,',','.');                      
$HTML_cobros .= "<tr><td class='textoTabla' align='right' colspan='5'>"
			 .  "  <b>Deuda Total <small>(incluye Deuda Vencida)</small>: $$deuda_total</b></td><td class='textoTabla' colspan='5'> </td></tr>\n";

$estado_cci = "<span class='".str_replace(" ","",$convenio_ci[0]['estado'])."'><b>{$convenio_ci[0]['estado']}</b></span> "
            . "<i>desde el {$convenio_ci[0]['fecha_cambio_estado']}</i>";

if ($convenio_ci[0]['nulo'] == "t") { $estado_cci = "<b style='color: #FF0000'>NULO</b> $estado_cci"; }
	
$enl_convenio_ci = "convenio_ci.php?id_convenio_ci=$id_contrato&tipo=$tipo_contrato";

$boton_pagare_liqci = "";
$pagare_liqci = consulta_sql("SELECT id,version FROM finanzas.pagares_liqci WHERE id_convenio_ci=$id_convenio_ci ORDER BY version");
for ($x=0;$x<count($pagare_liqci);$x++) {
	$enl_pagare_liqci = "pagare_liqci.php?id_pagare_liqci={$pagare_liqci[$x]['id']}&version={$pagare_liqci[$x]['version']}";
	$boton_pagare_liqci .= "<input type='button' value='Pagaré LCI {$pagare_liqci[$x]['id']}-{$pagare_liqci[$x]['version']}' onClick=\"window.open('$enl_pagare_liqci');\">&nbsp;";
}
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
      <a href='#' onClick="window.open('<?php echo("$enlbase_sm=$modulo&id_convenio_ci=$id_convenio_ci&imprimir=Imprimir"); ?>');" class="boton">Imprimir</a>
      <a href='#' onClick="window.open('<?php echo("$enlbase_sm=$modulo&id_convenio_ci=$id_convenio_ci&imprimir=Imprimir&obs=NO"); ?>');" class="boton">Imprimir s/Obs</a>
      <a href="#" onClick="history.back();" class="boton">Volver</a>
    </td>
    <td class="celdaFiltro">
      Gestionar:<br>
      <a href="<?php echo("$enlbase_sm=convenio_ci_anular&id_convenio_ci=$id_convenio_ci"); ?>" id='sgu_fancybox_small' class="boton">Anular</a>
      <a href="<?php echo("$enlbase_sm=convenio_ci_estado&id_convenio_ci=$id_convenio_ci"); ?>" id='sgu_fancybox_small' class="boton">Estado</a>
      <a href="<?php echo("$enlbase_sm=convenio_ci_comentarios&id_convenio_ci=$id_convenio_ci"); ?>" id='sgu_fancybox_small' class="boton">Observaciones</a>
    </td>
    <td class="celdaFiltro">
      Documentos formateados:<br>
      <!-- <input type="button" value="Convenio LCI" onClick="window.open('<?php echo($enl_convenio_ci); ?>');"> -->
      <?php echo($boton_pagare_liqci); ?>
    </td>
  </tr>
</table>
<?php } ?>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style="margin-top: 5px">
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Generales del Convenio</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Número:</td>
    <td class='celdaValorAttr'><?php echo($convenio_ci[0]['id']); ?></td>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><?php echo($convenio_ci[0]['fecha']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Estado:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($estado_cci); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Alumno</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($convenio_ci[0]['rut']); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($convenio_ci[0]['id_alumno']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($convenio_ci[0]['al_nombre']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Dirección:</td>
    <td class='celdaValorAttr' colspan="3">
      <?php echo($convenio_ci[0]['direccion']); ?>, <?php echo($convenio_ci[0]['comuna']); ?>, <?php echo($convenio_ci[0]['region']); ?>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Teléfono:</td>
    <td class='celdaValorAttr'><?php echo($convenio_ci[0]['telefono']); ?></td>
    <td class='celdaNombreAttr'>Tél. Móvil:</td>
    <td class='celdaValorAttr'><?php echo($convenio_ci[0]['tel_movil']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr' colspan="4">
      <?php echo($convenio_ci[0]['carrera']); ?> <b>jornada:</b> <?php echo($convenio_ci[0]['jornada']); ?>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center">Valores Nominales y Descuentos</td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'><u>Monto Acumulado:</u></td>
    <td class='celdaValorAttr'>UF <?php echo(number_format($convenio_ci[0]['monto_liqci_uf'],2,',','.')); ?></td>
    <td class='celdaNombreAttr'><u>Monto Adicional:</u></td>
    <td class='celdaValorAttr'>UF <?php echo(number_format($convenio_ci[0]['monto_adicional_uf'],2,',','.')); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Descuento:</u></td>
    <td class='celdaValorAttr' colspan="3">
      UF <?php echo(number_format($convenio_ci[0]['descuento_inicial_uf'],2,',','.')); ?> ó
      (<?php echo(number_format($convenio_ci[0]['descuento_inicial_porc'],2,',','.')); ?>%)
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center">Financiamiento del Convenio</td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'><u>Monto a Financiar:</u></td>
    <td class='celdaValorAttr' colspan='3' bgcolor='#FFFFAF'><b>UF <?php echo(number_format($monto_final_uf,2,',','.')); ?></b></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Efectivo:</td>
    <td class='celdaValorAttr' colspan='3'>
		UF <?php echo(number_format($convenio_ci[0]['liqci_efectivo']/$convenio_ci[0]['valor_uf'],2,',','.')); ?>
		<?php
			if ($convenio_ci[0]['liqci_efectivo'] > 0) { 
				echo("equivalentes al {$convenio_ci[0]['fecha']} a $".number_format($convenio_ci[0]['liqci_efectivo'],0,',','.')); 
			} ?>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cheque(s):</td>
    <td class='celdaValorAttr' colspan='3'>
      UF <?php echo(number_format($convenio_ci[0]['liqci_cheque']/$convenio_ci[0]['valor_uf'],2,',','.')); ?>
      <?php	if ($convenio_ci[0]['liqci_cheque'] > 0) {
				echo("equivalentes al {$convenio_ci[0]['fecha']} a $".number_format($convenio_ci[0]['liqci_cheque'],0,',','.')); ?>
      <small>
        <b>Cant.:</b> <?php echo(number_format($convenio_ci[0]['liqci_cant_cheques'],0,',','.')); ?>
        <b>1er Venc.:</b> <?php echo($convenio_ci[0]['liqci_diap_cheque']." de ".$meses_palabra[$convenio_ci[0]['liqci_mes_ini_cheque']-1]['nombre']." de ".$convenio_ci[0]['liqci_ano_ini_cheque']); ?>
      </small>
      <?php } ?>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Pagaré LCI:</td>
    <td class='celdaValorAttr' colspan='3'>
      UF <?php echo(number_format($convenio_ci[0]['liqci_pagare']/$convenio_ci[0]['valor_uf'],2,',','.')); ?>
      <?php if ($convenio_ci[0]['liqci_pagare'] > 0) { ?>
      <small>
        <b>Cuotas:</b> <?php echo(number_format($convenio_ci[0]['liqci_cuotas_pagare'],0,',','.')); ?>
        <b>1er Venc.:</b> <?php echo($convenio_ci[0]['liqci_diap_pagare']." de ".$meses_palabra[$convenio_ci[0]['liqci_mes_ini_pagare']-1]['nombre']." de ".$convenio_ci[0]['liqci_ano_ini_pagare']); ?>
      </small>
      <?php } ?>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tarjeta de Débito:</td>
    <td class='celdaValorAttr' colspan='3'>
      UF <?php echo(number_format($convenio_ci[0]['liqci_tarj_debito']/$convenio_ci[0]['valor_uf'],2,',','.')); ?>
    <?php
		if ($convenio_ci[0]['liqci_tarj_debito'] > 0) {
			echo("equivalentes al {$convenio_ci[0]['fecha']} a $".number_format($convenio_ci[0]['liqci_tarj_debito'],0,',','.')); 
		}
	?>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tarjeta de Crédito:</td>
    <td class='celdaValorAttr' colspan='3'>
      UF <?php echo(number_format($convenio_ci[0]['liqci_tarj_credito']/$convenio_ci[0]['valor_uf'],2,',','.')); ?>
      <?php
			if ($convenio_ci[0]['liqci_tarj_credito'] > 0) {
				echo("equivalentes al {$convenio_ci[0]['fecha']} a $".number_format($convenio_ci[0]['liqci_tarj_credito'],0,',','.'));
				echo("<small><b>Cant:</b> {$convenio_ci[0]['liqci_cant_tarj_debito']}</small>");
			}
      ?>
    </td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="4" align="center">
      <small><b>Cant:</b> Se refiere a Cantidad de Cheques o Cantidad de Cuotas de Tarjetas</small>
    </td>
  </tr>
<?php if ($convenio_ci[0]['monto_condonacion'] > 0) { ?>  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center">Condonación</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><?php echo($convenio_ci[0]['fecha_condonacion']); ?></td>
    <td class='celdaNombreAttr'>Monto:</td>
    <td class='celdaValorAttr'>$<?php echo(number_format($convenio_ci[0]['monto_condonacion'],0,',','.')); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: left">Motivo:</td></tr>
  <tr><td class='celdaValorAttr' colspan="4"><?php echo(nl2br($convenio_ci[0]['motivo_condonacion'])); ?></td></tr>
<?php } ?>
<?php if ($convenio_ci[0]['comentarios'] <> "" && $_REQUEST['obs'] == "") { ?>  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center">Observaciones</td></tr>
  <tr><td class='celdaValorAttr' colspan="4"><?php echo(nl2br(str_replace("###","blockquote",wordwrap($convenio_ci[0]['comentarios'],90)))); ?></td></tr>
<?php } ?>
</table>
<?php if($_REQUEST['imprimir'] <> "Imprimir") { ?>
<table cellpadding="1" border="0" cellspacing="2" width="auto" style="margin-top: 5px">
  <tr>
    <td class="celdaFiltro">
      <a id="cobros">Acciones:</a><br>
      <a href='<?php echo("$enlbase_sm=emitir_boleta_electronica&rut={$convenio_ci[0]['rut']}"); ?>' class='boton' id='sgu_fancybox_medium'>Pagar (BOL-E)</a>&nbsp;&nbsp;
      <a href='<?php echo("$enlbase_sm=gestion_caja2&rut={$convenio_ci[0]['rut']}"); ?>' class='boton' id='sgu_fancybox_medium'>Pagar</a>&nbsp;&nbsp;
      <a href='<?php echo("$enlbase_sm=gestion_boletas&texto_buscar={$convenio_ci[0]['rut']}&buscar=Buscar"); ?>' class='boton' id='sgu_fancybox' >Ver boletas asociadas</a>&nbsp;&nbsp;
<!--
      <a href='<?php echo("$enlbase_sm=convenio_ci_cambiar_dia_pago&id_convenio_ci=$id_convenio_ci"); ?>' id='sgu_fancybox_small' class='boton'>Cambiar día de pago</a>&nbsp;&nbsp;
      <a href="<?php echo("$enlbase_sm=convenio_ci_renegociar&id_convenio_ci=$id_convenio_ci"); ?>" id='sgu_fancybox_small' class="boton">Renegociación</a>&nbsp;&nbsp;
      <a href="<?php echo("$enlbase_sm=convenio_ci_condonar&id_convenio_ci=$id_convenio_ci"); ?>" id='sgu_fancybox_small' class="boton">Ajuste de sencillo</a>&nbsp;&nbsp;
-->
      <a href="<?php echo("$enlbase_sm=convenio_ci_recalcular_monto_cuotas&id_convenio_ci=$id_convenio_ci"); ?>" id='sgu_fancybox_small' class="boton">Recalcular cobros</a>&nbsp;&nbsp;
      <a href="#" onClick="history.back();" class="boton">Volver</a>
    </td>
  </tr>
</table>
<?php } ?>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr class='filaTituloTabla'>
    <td colspan="10" class='tituloTabla'>
      Cobros Asociados<br><small>Convenio Nº <?php echo($id_convenio_ci." ".$convenio_ci[0]['rut']." ".$convenio_ci[0]['al_nombre']); ?></small>
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
		'width'				: 1000,
		'height'			: 400,
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
		'width'				: 800,
		'maxHeight'			: 700,
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
		'width'				: 520,
		'height'			: 480,
		'maxHeight'			: 480,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});
</script>
<!-- Fin: <?php echo($modulo); ?> -->

<?php

function generar_cobros_convenio_ci($id_convenio_ci) {
	$convenio_ci = consulta_sql("SELECT * FROM finanzas.convenios_ci WHERE id=$id_convenio_ci");
	$valor_uf = consulta_sql("SELECT valor FROM finanzas.valor_uf WHERE fecha='{$convenio_ci[0]['fecha']}'::date");
	$valor_uf = $valor_uf[0]['valor'];
	$valor_uf_hoy = consulta_sql("SELECT valor FROM finanzas.valor_uf WHERE fecha=now()::date");
	$valor_uf_hoy = $valor_uf_hoy[0]['valor'];
	if (count($convenio_ci) == 1) {
		if ($convenio_ci[0]['liqci_efectivo'] > 0 || $convenio_ci[0]['liqci_tarj_debito'] > 0 || $convenio_ci[0]['liqci_tarj_credito'] > 0 ) {
			$id_glosa       = 302; // Pago Completo de Liquidación de Créd. Interno
			$cant_cuotas    = 1;
			$monto_total    = $convenio_ci[0]['liqci_efectivo'] + $convenio_ci[0]['liqci_tarj_debito'] + $convenio_ci[0]['liqci_tarj_credito'];
			$monto_total_uf = round($monto_total/$valor_uf,2);
			$monto_cuota    = round($monto_total/$cant_cuotas,0);
			$monto_cuota_uf = round($monto_total_uf/$cant_cuotas,2);
			$diap           = strftime("%d",strtotime($convenio_ci[0]['fecha']));
			$mesp           = strftime("%m",strtotime($convenio_ci[0]['fecha']));
			$anop           = strftime("%Y",strtotime($convenio_ci[0]['fecha']));
			$SQL_cobros     = generar_cobros_ci($id_convenio_ci,$id_glosa,$cant_cuotas,$monto_cuota,$monto_cuota_uf,$monto_total,$monto_total_uf,$diap,$mesp,$anop);
			consulta_sql($SQL_cobros);
		}
		
		if ($convenio_ci[0]['liqci_cheque'] > 0 && $convenio_ci[0]['liqci_cant_cheques'] > 0) {
			setlocale(LC_MONETARY,"en_US.UTF-8");
			setlocale(LC_NUMERIC,"en_US.UTF-8");
			$id_glosa       = 303; // Mensualidad de Cheque de Liquidación de Créd. Interno
			$cant_cuotas    = $convenio_ci[0]['liqci_cant_cheques'];
			$monto_total    = $convenio_ci[0]['liqci_cheque'];
			$monto_total_uf = round($monto_total/$valor_uf,2);
			$monto_cuota    = round($monto_total/$cant_cuotas,0);
			$monto_cuota_uf = round($monto_total_uf/$cant_cuotas,2);
			$diap           = $convenio_ci[0]['liqci_diap_cheque'];
			$mesp           = $convenio_ci[0]['liqci_mes_ini_cheque'];
			$anop           = $convenio_ci[0]['liqci_ano_ini_cheque'];
			$SQL_cobros     = generar_cobros_ci($id_convenio_ci,$id_glosa,$cant_cuotas,$monto_cuota,$monto_cuota_uf,$monto_total,$monto_total_uf,$diap,$mesp,$anop);
			consulta_sql($SQL_cobros);
		}

		if ($convenio_ci[0]['liqci_pagare'] > 0 && $convenio_ci[0]['liqci_cuotas_pagare'] > 0) {
			setlocale(LC_MONETARY,"en_US.UTF-8");
			setlocale(LC_NUMERIC,"en_US.UTF-8");
			$monto_total    = $convenio_ci[0]['liqci_pagare'];
			$monto_total_uf = round($monto_total / $valor_uf,2);
			$cant_cuotas    = $convenio_ci[0]['liqci_cuotas_pagare'];
			$diap           = $convenio_ci[0]['liqci_diap_pagare'];
			$mesp           = $convenio_ci[0]['liqci_mes_ini_pagare'];
			$anop           = $convenio_ci[0]['liqci_ano_ini_pagare'];
			$fecha_ini      = "$diap-$mesp-$anop";
			if ($diap>28 && $mesp==2) { $fecha_ini = "28-$mesp-$anop"; }
			$SQLins_pagare_liqci = "INSERT INTO finanzas.pagares_liqci (id_convenio_ci[0],monto,fecha_pago_ini,cuotas,fecha)
			                             VALUES ($id_convenio,$monto_total_uf,'$fecha_ini'::date,$cant_cuotas,'$fecha'::date);
			                       	SELECT currval('finanzas.pagares_liqci_id_seq') AS id";
			$pagare_liqci = consulta_sql($SQLins_pagare_liqci);
			$id_pagare_liqci = $pagare_liqci[0]['id'];
			$id_glosa       = 300; // Mensualidad de Pagaré de Liquidación de Créd. Interno
			$monto_cuota    = round($convenio_ci[0]['liqci_pagare']/$cant_cuotas,0);
			$monto_cuota_uf = round($monto_total_uf/$cant_cuotas,2);
			$SQL_cobros     = generar_cobros_ci($id_convenio_ci,$id_glosa,$cant_cuotas,$monto_cuota,$monto_cuota_uf,$monto_total,$monto_total_uf,$diap,$mesp,$anop);
			consulta_sql($SQL_cobros);
			$SQL_cobros_upd = "UPDATE finanzas.cobros
			                   SET monto=round(monto_uf*$valor_uf_hoy,0)
			                   WHERE id_convenio_ci=$id_convenio_ci AND id_glosa IN (300,301,302) AND NOT pagado";
			consulta_dml($SQL_cobros_upd);
		}

	}
}

?>
