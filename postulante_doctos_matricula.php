<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_pap = $_REQUEST['id_pap'];
if (!is_numeric($id_pap)) {
	echo(js("location.href='principal.php?modulo=gestion_postulantes';"));
	exit;
}

$SQL_postulante = "SELECT id,nombre,rut,admision FROM vista_pap WHERE id=$id_pap;";
$postulante     = consulta_sql($SQL_postulante);
if (count($postulante) > 0) {
	
	//$tipo_contrato = "al_nuevo_modular";
	// Lo siguiente corre solo para primer semestre
	
	switch ($postulante[0]['admision']) {
		case "":
		case "Normal":
			$tipo_contrato = "al_nuevo";
			break;
		case "Extraordinaria":
			$tipo_contrato = "al_nuevo_conv";
			break;
		case "Modular":
			$tipo_contrato = "al_nuevo_modular";
			break;
		case "Modular (Extr.)":
			$tipo_contrato = "al_nuevo_modular";
			break;
	}
	
	
		
	if ($_REQUEST['accion'] == "anular" && $_REQUEST['conf'] == "") {
		$conf = md5($id_pap);
		$url_si = "$enlbase_sm=$modulo&id_pap=$id_pap&id_contrato={$_REQUEST['id_contrato']}&conf=$conf&accion=anular";
		$url_no = "$enlbase_sm=$modulo&id_pap=$id_pap";
		echo(confirma_js("¿Está seguro de anular este contrato?",$url_si,$url_no));
	} elseif ($_REQUEST['accion'] == "anular" && $_REQUEST['conf'] == md5($id_pap)) {
		$id_contrato = $_REQUEST['id_contrato'];
		$SQLupdate = "UPDATE finanzas.contratos SET estado=null,estado_fecha=now(),estado_id_usuario={$_SESSION['id_usuario']} WHERE id=$id_contrato";
		consulta_dml($SQLupdate);
	}

	$SQL_doctos = "SELECT c.id AS id_contrato,c.ano AS periodo,to_char(c.fecha,'DD-MM-YYYY') AS fecha,c.monto_arancel,
	                      CASE c.estado WHEN 'E' THEN 'Emitido'
	                                    WHEN 'F' THEN 'Firmado'
	                                    ELSE 'Nulo'
	                      END AS estado,pc.id AS id_pagare_colegiatura,pc.cuotas,pc.monto AS monto_pagare_colegiatura,
	                      c.arancel_cred_interno,pci.id AS id_pagare_cred_interno,pci.monto AS monto_pagare_cred_interno,regimen
	               FROM finanzas.contratos AS c
	               LEFT JOIN finanzas.pagares_colegiatura  AS pc  ON pc.id_contrato=c.id
	               LEFT JOIN finanzas.pagares_cred_interno AS pci ON pci.id_contrato=c.id
	               LEFT JOIN carreras                      AS car ON car.id=c.id_carrera
	               WHERE c.id_pap=$id_pap
	               ORDER BY c.fecha DESC";
	$doctos     = consulta_sql($SQL_doctos);
	//var_dump($doctos);
	if (count($doctos) > 0) {
		$HTML_doctos = "";
		for ($x=0;$x<count($doctos);$x++) {
			extract($doctos[$x]);
			
			if ($regimen == "POST") { $tipo_contrato .= "_POST"; }

			$acciones = "";
			if ($estado <> "Nulo" && ($_SESSION['tipo'] == 0 || $_SESSION['tipo'] == 4 || $_SESSION['tipo'] == 5)) {
				$acciones .= " <a href='$enlbase_sm=$modulo&id_pap=$id_pap&id_contrato=$id_contrato&accion=anular' class='boton'>Anular</a> ";
			}
			
			$id_contrato   = "<a href='contrato.php?id_contrato=$id_contrato&tipo=$tipo_contrato' class='enlaces'>[$id_contrato]</a>";
			$monto_arancel = number_format($monto_arancel,0,',','.');
			
			if (is_numeric($id_pagare_colegiatura)) {
				$enl                       = "pagare_colegiatura.php?id_pagare_colegiatura=$id_pagare_colegiatura";
				$id_pagare_colegiatura     = "<a href='$enl' class='enlaces'>[$id_pagare_colegiatura]</a>";
				$monto_pagare_colegiatura  = number_format($monto_pagare_colegiatura,0,',','.');
			}
			
			if (is_numeric($id_pagare_cred_interno)) {
				$enl                       = "pagare_cred_interno.php?id_pagare_cred_interno=$id_pagare_cred_interno";
				$id_pagare_cred_interno    = "<a href='$enl' class='enlaces'>[$id_pagare_cred_interno]</a>";
				$monto_pagare_cred_interno = "UF ".number_format($monto_pagare_cred_interno,2,',','.')
				                           . " ($" .number_format($arancel_cred_interno,0,',','.'). ")";
			}
			
			$HTML_doctos .= "<tr>"
			             .  "  <td class='textoTabla' align='center'>$acciones</td>"
			             .  "  <td class='textoTabla' align='center'>$periodo</td>"
			             .  "  <td class='textoTabla' align='center'>$fecha</td>"
			             .  "  <td class='textoTabla' align='right'>$id_contrato $$monto_arancel</td>"
			             .  "  <td class='textoTabla' align='right'>$id_pagare_colegiatura ($cuotas) $$monto_pagare_colegiatura</td>"
			             .  "  <td class='textoTabla' align='right'>$id_pagare_cred_interno $monto_pagare_cred_interno</td>"
			             .  "  <td class='textoTabla' align='center'>$estado</td>"
			             .  "</tr>";
		}
	} else {
		echo(msje_js("Este postulante no tiene Documentos de Matrícula asociados"));
		echo(js("location.href='principal.php?modulo=gestion_postulantes';"));
	}
} else {	
	echo(js("location.href='principal.php?modulo=gestion_postulantes';"));
	exit;
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<div class="texto"><input type='button' name='volver' onClick='javascript:history.back();' value="Volver"></div>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Postulante</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['rut']); ?></td>
    <td class='celdaNombreAttr'>ID Postulante:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['id']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($postulante[0]['nombre']); ?></td>
  </tr>  
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class="tituloTabla" colspan="7">Contratos, Pagarés de Colegiatura y Pagarés de Crédito Interno</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class="tituloTabla">&nbsp;</td>
    <td class="tituloTabla">Periodo</td>
    <td class="tituloTabla">Fecha</td>
    <td class="tituloTabla">Contrato<br><sub>[Folio] Monto Arancel</sub></td>
    <td class="tituloTabla">P. Colegiatura<br><sub>[Folio] (Cuotas) Monto</sub></td>
    <td class="tituloTabla">P. Crédito Interno<br><sub>[Folio] Monto</sub></td>
    <td class="tituloTabla">Estado</sub></td>
  </tr>
  <?php echo($HTML_doctos); ?>
</table>
<!-- Fin: <?php echo($modulo); ?> -->
