<?php

$SQL_det_solic = "SELECT sef.*,coalesce(c.semestre||'-','')||c.ano AS periodo 
                  FROM gestion.solic_excep_finan AS sef
				  LEFT JOIN finanzas.contratos AS c ON c.id=sef.id_contrato 
				  WHERE id_solicitud=$id_solic";
$detalle_solic = consulta_sql($SQL_det_solic);
$filas_contratos = count($detalle_solic);
$tot_monto_saldot = $tot_monto_moroso = $tot_monto_pie = $tot_monto_cuota = $tot_monto_apagar = 0;
$HTML = "";

for($x=0;$x<$filas_contratos;$x++) {
	extract($detalle_solic[$x]);

	$monto_apagar = $monto_pie + $monto_cuota*$nro_cuotas;

	$tot_monto_saldot += $monto_saldot;
	$tot_monto_moroso += $monto_moroso;
	$tot_monto_pie    += $monto_pie;
	$tot_monto_cuota  += $monto_cuota;
	$tot_monto_apagar += $monto_apagar;

	$monto_saldot = number_format($monto_saldot,0,",",".");
	$monto_moroso = number_format($monto_moroso,0,",",".");
	$monto_pie    = number_format($monto_pie,0,",",".");
	$monto_cuota  = number_format($monto_cuota,0,",",".");
	$venc_1ro     = "$diap_ini-$mesp_ini-$anop_ini";
	$monto_apagar = number_format($monto_apagar,0,",",".");

	$HTML .= "    <tr class='filaTabla'>\n"
		  .  "      <td class='textoTabla' align='center'>$id_contrato</td>\n"
		  .  "      <td class='textoTabla' align='center'>$periodo</td>\n"
		  .  "      <td class='textoTabla' align='right'>$monto_saldot</td>\n"
		  .  "      <td class='textoTabla' align='right'>$monto_moroso</td>\n"
		  .  "      <td class='textoTabla' align='right'>$monto_pie</td>\n";
	  
	if ($x == 0) {
		$HTML .= "      <td class='textoTabla' rowspan='$filas_contratos' style='vertical-align: middle; text-align: center'>$nro_cuotas</td>\n"
			  .  "      <td class='textoTabla' rowspan='$filas_contratos' style='vertical-align: middle; text-align: center'>$venc_1ro</td>\n";
	}

	$HTML .= "      <td class='textoTabla' align='right'>$monto_cuota</td>\n"
		  .  "      <td class='textoTabla' align='right'>$monto_apagar</td>\n"
		  .  "    </tr>\n";
}

$porc_pie = round($tot_monto_pie * 100 / $tot_monto_moroso,0);
$color_pie = "black";
if ($porc_pie <  10) { $color_pie = "red"; }
if ($porc_pie >= 10) { $color_pie = "yellow"; }
if ($porc_pie >= 30) { $color_pie = "orange"; }
if ($porc_pie >= 50) { $color_pie = "green"; }
$porc_pie = "<span style='background: $color_pie; color: black'>$porc_pie%</span>";

$tot_monto_saldot = number_format($tot_monto_saldot,0,",",".");
$tot_monto_moroso = number_format($tot_monto_moroso,0,",",".");
$tot_monto_pie    = number_format($tot_monto_pie,0,",",".");
$tot_monto_cuota  = number_format($tot_monto_cuota,0,",",".");
$tot_monto_apagar = number_format($tot_monto_apagar,0,",",".");

if (count($docto_adj) == 0) {
	$boton_subir_comp = "<a href='$enlbase_sm=solicitudes_docto_adj&id_solic=$id_solic' class='botoncito'>Adjuntar Documento: {$solic[0]['tipo_docto_oblig']} por el Monto del Pie</a>";
} else {

  if ($solic[0]['estado'] == "En preparación") {
    $msje_elim = "¿Está seguro de eliminar el documento Comprobante de Pago?";
    $enl_elim  = "$enlbase_sm=solicitudes_ver&elim_id_docto_adj={$docto_adj[0]['id']}&id_solic=$id_solic&id_alumno=$id_alumno&tipo=$tipo_solic";
    $boton = "<a href='#' onClick=\"if (confirm('$msje_elim')) { window.location='$enl_elim'; }\" class='enlaces' style='color: red'><big>✘</big></a> ";  
  }

  if (empty($dir_solic)) { $dir_solic = "."; }
	$enl_docto = "$dir_solic/solicitudes_docto_adj_ver.php?id_docto_adj={$docto_adj[0]['id']}";
	$boton .= "<small><a target='_blank' href='$enl_docto' class='enlaces'>Ver Comprobante de Pago <i>subido el {$docto_adj[0]['fecha']}</i></a></small>";
	$boton_subir_comp = $boton;
}

$HTML .= "    <tr>\n"
	  .  "      <td class='celdaNombreAttr' valign='botton' align='right' colspan='2'>Total:</td>\n"
	  .  "      <td class='celdaNombreAttr' valign='botton' align='right'>$tot_monto_saldot</td>\n"
	  .  "      <td class='celdaNombreAttr' valign='botton' align='right'>$tot_monto_moroso</td>\n"
	  .  "      <td class='celdaNombreAttr' valign='botton' align='right'>$tot_monto_pie $porc_pie</td>\n"
	  .  "      <td class='celdaNombreAttr' valign='botton' style='text-align: left; vertical-align: middle' colspan='2'>$boton_subir_comp</td>\n"
	  .  "      <td class='celdaNombreAttr' valign='botton' align='right'>$tot_monto_cuota</td>\n"
	  .  "      <td class='celdaNombreAttr' valign='botton' align='right'>$tot_monto_apagar</td>\n"
	  .  "    </tr>\n";

$HTML_contratos = $HTML;

$estado = $solic[0]['estado'];
$estado = "<span class='".str_replace(" ","",$estado)."'>$estado</span>";
?>

<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr'>Tipo:</td>
    <td class='celdaValorAttr'><?php echo($solic[0]['nombre_tipo_solic']); ?></td>
    <td class='celdaNombreAttr'>Fecha creación:</td>
    <td class='celdaValorAttr'><?php echo($solic[0]['fecha_solic']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Estado:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo("$estado <i>desde el {$solic[0]['estado_fecha']}</i>"); ?></td>
  </tr>

<?php if (!empty($solic[0]['resp_obs'])) { ?>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Observaciones</td></tr>
  <tr><td class='celdaValorAttr' colspan="4"><?php echo(nl2br(wordwrap($solic[0]['resp_obs'],120))); ?><br><br></td></tr>
<?php } ?>

<?php if (!empty($solic[0]['responsables']) && !empty($_SESSION['id_usuario'])) { ?>
  <tr>
    <td class='celdaNombreAttr'>Responsable(s):</td>
    <td class='celdaValorAttr' colspan="3"><?php echo(str_replace(",","<br>",$solic[0]['responsables'])); ?></td>
  </tr>
<?php } ?>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center;">Antecedentes Personales y Curriculares</td></tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($solic[0]['rut']); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($solic[0]['id_alumno']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($solic[0]['nombre']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr'><?php echo($solic[0]['carrera']); ?></td>
    <td class='celdaNombreAttr'>Cohorte:</td>
    <td class='celdaValorAttr'><?php echo($solic[0]['cohorte']); ?></td>
  </tr>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de Contacto</td></tr>

  <tr>
    <td class='celdaNombreAttr' style='vertical-align: top'><u>e-mail:</u></td>
    <td class='celdaValorAttr' colspan='3'><?php echo($solic[0]['email']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='vertical-align: top'><u>Tel. Fijo:</u></td>
    <td class='celdaValorAttr'><?php echo($solic[0]['telefono']); ?></td>
    <td class='celdaNombreAttr' style='vertical-align: top'><u>Tel. Móvil:</u></td>
    <td class='celdaValorAttr'><?php echo($solic[0]['tel_movil']); ?></td>
  </tr>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Excepción Financiera</td></tr>

  <tr>
    <td colspan="4">
      <table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" width='100%'>
        <tr class='filaTituloTabla'>
          <td class='tituloTabla' colspan="4">Contrato(s)</td>
          <td class='tituloTabla' colspan="5">Propuesta de Repactación</td>
        </tr>
        <tr class='filaTituloTabla'>
          <td class='tituloTabla'>N°</td>
          <td class='tituloTabla'>Periodo</td>
          <td class='tituloTabla'>S. Total</td>
          <td class='tituloTabla'>M. Moroso</td>
          <td class='tituloTabla'>Monto Pie</td>
          <td class='tituloTabla'>N° Cuotas</td>
          <td class='tituloTabla'>1er Vencimiento</td>
          <td class='tituloTabla'>Monto Cuota</td>
          <td class='tituloTabla'>Total a Pagar</td>
        </tr>
        <?php echo($HTML_contratos); ?>
      </table>
    </td>
  </tr>

<?php if (!empty($solic[0]['comentarios'])) { ?>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Comentarios</td></tr>
  <tr><td class='celdaValorAttr' colspan="4"><?php echo(nl2br(wordwrap($solic[0]['comentarios'],120))); ?><br><br></td></tr>
<?php } ?>

<?php if (!isset($_SESSION['tipo'])) { ?>
  <tr>
    <td class='celdaValorAttr' colspan="2" width='50%'>
      <b>Glosario:</b><br><br>
      <i>
      <b>S. Total:</b> Este monto comprende cuotas o saldos de estas que se encuentren vencidas y por vencer no pagadas a la fecha.<br>
      <b>M. Moroso:</b> Este monto corresponde sólo a las cuotas o saldos de estas que se encuentren vencidas y no pagadas.<br>
      <b>Monto Pie:</b> Este monto deberá pagarlo inmediatamente, ya que es requisito para la aprobación de esta solicitud.<br>
      <b>Monto Cuota:</b> Este monto se calculará automáticamente en base al Monto del Pie y el número de cuotas seleccionado.<br>
      <b>Total a Pagar:</b> Este monto es identico a S. Total. siempre, en virtud de que no se aplican intereses por esta operación.<br>
      </i>
      <br>
    </td>
    <td class='celdaValorAttr' colspan="2" width='50%'>
      <b>Considera lo siguiente en tu solicitud:</b><br>
      <ol type='a'>
	      <li>El Monto del Pie debe ser a lo menos un 50% del M. Moroso o bien un 30% del S. Total.<br><br></li>
        <li>Procura que el Monto del Pie abarque la mayor parte del contrato más antiguo (están ordenados por periodo).<br><br></li>
	      <li>Mientras más grande sea el Monto del Pie, el N° de cuotas puede ser mayor.<br><br></li>
	      <li>El Vencimiento de la 1ra cuota no puede ser superior a 30 días.</li>
      </ol>
    </td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="4">
	    <b>NOTA:</b> En caso de no proponer un plan de pago según las consideraciones antes descritas, 
            la solicitud podrá ser Rechazada o bien derivada a la Vicerrectoría de Administración y Finanzas.
    </td>
  </tr>
<?php } ?>
</table>