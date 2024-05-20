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
                        to_char(c.fecha,'DD-MM-YYYY') AS fecha
                 FROM finanzas.contratos AS c
                 LEFT JOIN alumnos       AS al  ON al.id=c.id_alumno
                 LEFT JOIN vista_alumnos AS va  ON va.id=c.id_alumno
                 LEFT JOIN vista_pap     AS vp  ON vp.id=c.id_pap
                 LEFT JOIN pap                  ON pap.id=c.id_pap
                 WHERE c.id=$id_contrato";
$contrato     = consulta_sql($SQL_contrato);
if (count($contrato) == 0) {
	echo(js("location.href='$enlbase=gestion_contratos';"));
	exit;
}

//$SQL_cobros = "SELECT 1 FROM finanzas.cobros WHERE id_contrato=$id_contrato AND id_glosa IN (2,20) AND date_part('day',fecha_venc)={$contrato[0]['arancel_diap_pagare_coleg']}";
$SQL_cobros = "SELECT 1 FROM finanzas.cobros WHERE id_contrato=$id_contrato AND id_glosa IN (2,20) AND NOT pagado";
if (count(consulta_sql($SQL_cobros)) == 0) {
	echo(msje_js("No es posible cambiar el día de pago. "
	            ."Ya se ha realizado esto anteriormente o bien los cobros asociados al "
	            ."contrato no son derivados de un Pagaré de Colegiatura"));
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$arancel_diap_pagare_coleg = $_REQUEST['arancel_diap_pagare_coleg'];
if ($_REQUEST['guardar'] == "Guardar" && is_numeric($arancel_diap_pagare_coleg)) {
	$SQL_cobros_upd = "UPDATE finanzas.cobros
	                   SET fecha_venc = CASE WHEN date_part('month',fecha_venc) = 2 AND $arancel_diap_pagare_coleg = 30
	                                         THEN (to_char(fecha_venc,'YYYY-MM-')||'28')::date
	                                         ELSE (to_char(fecha_venc,'YYYY-MM-')||'$arancel_diap_pagare_coleg')::date
	                                    END
	                   WHERE id_contrato=$id_contrato AND id_glosa IN (2,20) 
	                     AND NOT pagado";
//	                     AND date_part('day',fecha_venc) IN ({$contrato[0]['arancel_diap_pagare_coleg']},28)";
	if (consulta_dml($SQL_cobros_upd) > 0) {
    //echo($SQL_cobros_upd);
		echo(msje_js("Se ha realizado el cambio de día de pago exitosamente"));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}
}

$dias_pago = array(array("id"=>5 ,"nombre"=>"5"),
                   array("id"=>15,"nombre"=>"15"),
                   array("id"=>30,"nombre"=>"30"));
?>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>
<br>
<form name="formulario" action="principal_sm.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_contrato" value="<?php echo($id_contrato); ?>">
<input type="submit" name="guardar" value="Guardar"><br><br>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla">
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
    <td class='celdaNombreAttr'>Tipo:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['tipo']); ?></td>
    <td class='celdaNombreAttr'>Periodo:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['semestre'].'-'.$contrato[0]['ano']); ?></td>
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
    <td class='celdaNombreAttr'>Día de Pago:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name='arancel_diap_pagare_coleg'>
        <?php echo(select($dias_pago,$contrato[0]['arancel_diap_pagare_coleg'])); ?>
      </select><br>
      <small><b>ATENCIÓN:</b> El cambio de día de pago afecta a cobros <b>NO pagados</b><br>y que deriven de un Pagaré de Colegiatura</small>
    </td>
  </tr>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
