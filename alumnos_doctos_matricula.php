<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_alumno = $_REQUEST['id_alumno'];
if (!is_numeric($id_alumno)) {
	echo(js("location.href='$enlbase=gestion_alumnos';"));
	exit;
}

$SQL_alumno = "SELECT a.id,va.nombre,va.rut,a.admision FROM vista_alumnos va LEFT JOIN alumnos a USING (id) WHERE id=$id_alumno;";
$alumno     = consulta_sql($SQL_alumno);
if (count($alumno) > 0) {

	if ($_REQUEST['accion'] == "anular" && $_REQUEST['conf'] == "") {
		$conf = md5($id_alumno);
		$url_si = "$enlbase=$modulo&id_alumno=$id_alumno&id_contrato={$_REQUEST['id_contrato']}&conf=$conf&accion=anular";
		$url_no = "$enlbase=$modulo&id_alumno=$id_alumno";
		echo(confirma_js("¿Está seguro de anular este contrato?",$url_si,$url_no));
	} elseif ($_REQUEST['accion'] == "anular" && $_REQUEST['conf'] == md5($id_alumno)) {
		$id_contrato = $_REQUEST['id_contrato'];
		$SQLupdate = "UPDATE finanzas.contratos SET estado=null,estado_fecha=now(),estado_id_usuario={$_SESSION['id_usuario']} WHERE id=$id_contrato";
		consulta_dml($SQLupdate);
	}

	$SQL_doctos = "SELECT c.id AS id_contrato,c.ano AS periodo,to_char(c.fecha,'DD-MM-YYYY') AS fecha,c.monto_arancel,
	                      CASE c.estado WHEN 'E' THEN 'Emitido'
	                                    WHEN 'F' THEN 'Firmado'
	                                    ELSE 'Nulo'
	                      END AS estado,trim(c.tipo) AS tipo,
	                      pc.id AS id_pagare_colegiatura,pc.cuotas,pc.monto AS monto_pagare_colegiatura,
	                      c.arancel_cred_interno,pci.id AS id_pagare_cred_interno,pci.monto AS monto_pagare_cred_interno
	               FROM finanzas.contratos AS c
	               LEFT JOIN finanzas.pagares_colegiatura AS pc ON pc.id_contrato=c.id
	               LEFT JOIN finanzas.pagares_cred_interno AS pci ON pci.id_contrato=c.id
	               WHERE c.id_alumno=$id_alumno
	               ORDER BY c.fecha DESC";
	//echo($SQL_doctos);
	$doctos     = consulta_sql($SQL_doctos);
	if (count($doctos) > 0) {
		$HTML_doctos = "";
		for ($x=0;$x<count($doctos);$x++) {
			extract($doctos[$x]);
			
			$acciones = "";
			if ($estado <> "Nulo" && ($_SESSION['tipo'] == 0 || $_SESSION['tipo'] == 4 || $_SESSION['tipo'] == 5)) {
				$acciones .= " <a href='$enlbase=$modulo&id_alumno=$id_alumno&id_contrato=$id_contrato&accion=anular' class='boton'>Anular</a> ";
			}
			
			switch ($tipo) {
				case "Anual":
					$tipo_contrato = "al_antiguo";
					break;
				case "Semestral" || "Modular":
					$tipo_contrato = "al_antiguo";
					break;
				case "Estival":
					$tipo_contrato = "estival";
					break;
				case "Egresado":
					$tipo_contrato = "al_egresado";
					break;
			}
			
			if ($alumno[0]['admision'] == "10" || $alumno[0]['admision'] == "20") {
				$tipo_contrato .= "_modular";
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
		echo($SQL_doctos);
		echo(msje_js("Este alumno no tiene Documentos de Matrícula asociados"));
		echo(js("location.href='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
	}
} else {	
	//echo(js("location.href='$enlbase=gestion_alumnos';"));
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
    <td class='celdaValorAttr'><?php echo($alumno[0]['rut']); ?></td>
    <td class='celdaNombreAttr'>ID Postulante:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['id']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($alumno[0]['nombre']); ?></td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'>Admisión:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($alumno[0]['admision']); ?></td>
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
