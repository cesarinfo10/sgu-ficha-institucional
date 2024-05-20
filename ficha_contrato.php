<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_contrato = $_REQUEST['id_contrato'];
if (!is_numeric($id_contrato))
	header("Location: index.php");
	exit;
}

$SQL_contrato = "SELECT c.id,to_char(c.fecha,'DD-MM-YYYY') AS fecha,av.rf_parentezco,c.tipo,c.estado,
                         CASE WHEN c.tipo='Semestral' THEN text(c.semestre)||'-'||text(c.ano) ELSE text(c.ano) END AS periodo,
                         coalesce(upper(a.apellidos)||' '||initcap(a.nombres),upper(pap.apellidos)||' '||initcap(pap.nombres))AS nombre_al,
                         upper(av.rf_apellidos)||' '||initcap(av.rf_nombres) AS nombre_rf,
                         coalesce(a.rut,pap.rut) AS rut_al,av.rf_rut,
                         CASE c.estado WHEN 'E' THEN 'Emitido' WHEN 'F' THEN 'Firmado' ELSE 'Nulo' END AS estado,
                         CASE WHEN c.porc_beca_mat  IS NOT NULL THEN c.monto_matricula*c.porc_beca_mat
                              WHEN c.monto_beca_mat IS NOT NULL THEN c.monto_matricula-c.monto_beca_mat
                              ELSE c.monto_matricula
                         END AS monto_matricula,c.monto_arancel,
                         CASE WHEN c.id_convenio IS NOT NULL        THEN c.monto_arancel*0.2
                              WHEN c.porc_beca_arancel IS NOT NULL  THEN c.monto_arancel*(c.porc_beca_arancel/100)
                              ELSE c.monto_beca_arancel
                         END AS monto_beca_arancel,arancel_cred_interno,trim(car.alias) AS carrera,c.jornada,
                         CASE WHEN c.id_alumno IS NOT NULL THEN 'A' 
                              WHEN c.id_pap IS NOT NULL THEN 'N'
                         END AS tipo_alumno,
                         CASE WHEN arancel_efectivo IS NOT NULL        THEN ARRAY['Efectivo'::text,0::text,arancel_efectivo::text]
                              WHEN arancel_cheque IS NOT NULL          THEN ARRAY['Cheque(s)'::text,arancel_cant_cheques::text,arancel_cheque::text]
                              WHEN arancel_pagare_coleg IS NOT NULL    THEN ARRAY['Pagaré'::text,arancel_cuotas_pagare_coleg::text,arancel_pagare_coleg::text]
                              WHEN arancel_tarjeta_credito IS NOT NULL THEN ARRAY['T/Crédito'::text,0::text,arancel_tarjeta_credito::text]
                         END AS forma_pago
                   FROM finanzas.contratos AS c
                   LEFT JOIN finanzas.pagares_cred_interno AS pci ON pci.id_contrato=c.id
                   LEFT JOIN finanzas.pagares_colegiatura  AS pc  ON pc.id_contrato=c.id
                   LEFT JOIN vista_alumnos                 AS va  ON va.id=c.id_alumno
                   LEFT JOIN alumnos                       AS a   ON a.id=va.id
                   LEFT JOIN vista_pap                     AS vp  ON vp.id=c.id_pap
                   LEFT JOIN pap                                  ON pap.id=vp.id
                   LEFT JOIN vista_avales                  AS vav ON vav.id=c.id_aval
                   LEFT JOIN avales                        AS av  ON av.id=vav.id
                   LEFT JOIN carreras                      AS car ON car.id=c.id_carrera
                   WHERE c.id=$id_contrato";
$contrato     = consulta_sql($SQL_contrato);
if (count($contrato) > 0) {
	extract($contrato[0]);
	$SQL_cobros = "SELECT fc.id AS id_cobro,fc.id_contrato,to_char(fc.fecha_venc,'DD-MM-YYYY') AS fecha_venc,
						  fg.nombre AS glosa,fc.nro_cuota,fc.monto,fc.fecha_pago
				   FROM finanzas.cobros AS fc
				   LEFT JOIN finanzas.glosas AS fg ON fg.id=fc.id_glosa
				   WHERE id_contrato=$id_contrato
						OR id_alumno=$id_alumno OR id_pap=$id_pap
				   ORDER BY fc.fecha_venc";
	$cobros     = consulta_sql($SQL_cobros);
			
	$HTML_cobros = $HTML_cobros_vencidos = "";
	$deuda_total = $deuda_vencida = 0;
	for ($x=0;$x<count($cobros);$x++) {
		extract($cobros[$x]);
		$deuda_total += $monto;
		$monto = number_format($monto,0,',','.');

		$HTML = "<tr class='filaTabla'>\n"
			  . "  <td class='textoTabla' align='center'><input type='checkbox' name='id_cobro[$id_cobro]' value='$id_cobro'></td>\n"
			  . "  <td class='textoTabla' align='right'>$id_contrato</td>\n"
			  . "  <td class='textoTabla' align='center'>$fecha_venc</td>\n"
			  . "  <td class='textoTabla'>$glosa</td>\n"
			  . "  <td class='textoTabla' align='right'>$nro_cuota</td>\n"
			  . "  <td class='textoTabla' align='right'>$$monto</td>\n"
			  . "</tr>\n";
		if (strtotime($fecha_venc) <= time()) { 
			$HTML_cobros_vencidos .= $HTML;
			$deuda_vencida = $deuda_total; 
		} else { $HTML_cobros .= $HTML; }
	}
	$deuda_vencida = number_format($deuda_vencida,0,',','.');                      
	$HTML_cobros_vencidos .= "<tr class='filaTabla'><td class='textoTabla' align='right' colspan='6'>"
						  .  "  <b>Deuda Vencida: $$deuda_vencida</b></td></tr>\n";
	
	$deuda_total = number_format($deuda_total,0,',','.');                      
	$HTML_cobros .= "<tr class='filaTabla'><td class='textoTabla' align='right' colspan='6'>"
				 .  "  <b>Deuda Total: $$deuda_total</b></td></tr>\n";
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div><br>
<form name="formulario" action="principal.php" method="get" onSubmit="return valida_rut(formulario.rut);">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="rut" value="<?php echo($rut); ?>">

<input type="button" name="cancelar" value="Cancelar" onClick="history.back();">
<br><br>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla">
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Alumno</td></tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($rut_al); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($id); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($nombre_al); ?></td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Pagar</td>
    <td class='tituloTabla'>Contrato</td>
    <td class='tituloTabla'>Fecha<br>Vencimiento</td>    
    <td class='tituloTabla' width="200">Glosa</td>
    <td class='tituloTabla'>Nº<br>Cuota</td>
    <td class='tituloTabla'>Monto</td>
  </tr>
  <tr class='filaTabla'><td colspan="6" class='textoTabla'><i>Cobros Vencidos</i></td></tr>
  <?php echo($HTML_cobros_vencidos); ?>
  <tr class='filaTabla'><td colspan="6" class='textoTabla'><i>Cobros por Vencer</i></td></tr>
  <?php echo($HTML_cobros); ?>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
