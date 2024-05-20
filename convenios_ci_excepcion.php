<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");
include("validar_modulo.php");

$id_alumno    = $_REQUEST['id_alumno'];
$valor_minimo = str_replace(".","",$_REQUEST['valor_minimo']);
$rut          = $_REQUEST['rut'];
$fecha_liq    = $_REQUEST['fecha'];

if ($_REQUEST['guardar'] == "Guardar" && $id_alumno>0 && $valor_minimo > 0) {
	$ci_excepcion = consulta_sql("SELECT * FROM finanzas.convenios_ci_excepciones WHERE id_alumno=$id_alumno");
	if (count($ci_excepcion) > 0) {
		$SQL_excepcion_ci = "UPDATE finanzas.convenios_ci_excepciones SET valor_min='$valor_minimo' WHERE id_alumno=$id_alumno";
	} else {
		$SQL_excepcion_ci = "INSERT INTO finanzas.convenios_ci_excepciones (id_alumno,valor_min) VALUES ($id_alumno,$valor_minimo)";
	}
	
	if (consulta_dml($SQL_excepcion_ci) > 0) {
		echo(msje_js("Se ha guardado la excepción. Ahora se debe proseguir con la emisión del convenio."));
	} else {
		echo(msje_js("ERROR: NO se ha guardado la excepción. Informe este error al Departamento de Informática."));
	}

	echo(js("parent.jQuery.fancybox.close();"));
	exit;

}

if (!empty($rut)) {
	$SQL_alumno = "SELECT a.id AS id_alumno,trim(a.rut) AS rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre,c.alias||'-'||a.jornada AS carrera,
						  a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.mes_cohorte,ae.nombre AS estado,a.id_pap,
						  CASE WHEN m.id_alumno IS NOT NULL THEN 'Si' ELSE 'No' END AS matriculado,moroso_financiero
				   FROM alumnos AS a
				   LEFT JOIN carreras AS c ON c.id=a.carrera_actual
				   LEFT JOIN al_estados AS ae ON ae.id=a.estado
				   LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND semestre=$SEMESTRE AND ano=$ANO) 
				   WHERE a.rut='$rut' AND c.regimen='PRE'";
	$alumno = consulta_sql($SQL_alumno); 
	if (count($alumno) == 0) {
		echo(msje_js("ERROR: El RUT ingresado no corresponde a un alumno."));
		echo(js("parent.jQuery.fancybox.close()"));
		exit;
	}
	extract($alumno[0]);

	$SQL_contratos = "SELECT CASE WHEN arancel_cheque > 0 THEN round(arancel_cheque/arancel_cant_cheques)
								  WHEN arancel_pagare_coleg > 0 THEN round(arancel_pagare_coleg / arancel_cuotas_pagare_coleg)
								  WHEN arancel_efectivo > 0 THEN round(arancel_efectivo/a.cuotas)
								  WHEN arancel_tarjeta_credito > 0 THEN round(arancel_tarjeta_credito/a.cuotas)
							 END AS max_valor_cuota
					  FROM finanzas.contratos AS c
					  LEFT JOIN aranceles AS a ON (a.ano=c.ano AND a.id_carrera=c.id_carrera AND a.jornada=c.jornada AND a.semestre=1)
					  WHERE (c.id_alumno = $id_alumno OR c.id_pap = $id_pap) AND c.estado IS NOT NULL ORDER BY c.ano DESC LIMIT 1";
	$SQL_contratos = "SELECT max_valor_cuota FROM ($SQL_contratos) AS foo";
	$contratos = consulta_sql($SQL_contratos);
	$ult_valor_cuota = $contratos[0]['max_valor_cuota'];

	$SQL_contratos = "SELECT c.arancel_cred_interno,
							 round(coalesce(c.arancel_cred_interno,0)/uf.valor,2) AS arancel_cred_interno_uf,
							 CASE WHEN c.monto_condonacion>0 
							      THEN (c.monto_condonacion::float/(c.monto_arancel-monto_beca_arancel_calc-c.arancel_cred_interno)::float)*round(coalesce(c.arancel_cred_interno,0)/uf.valor,2)
							      ELSE 0
							 END AS desc_cred_interno_uf
					  FROM finanzas.contratos     AS c
					  LEFT JOIN vista_contratos   AS vc USING (id)
					  LEFT JOIN usuarios          AS u  ON u.id=c.id_emisor
					  LEFT JOIN finanzas.valor_uf AS uf ON uf.fecha=c.fecha::date
					  WHERE (c.id_alumno = $id_alumno OR c.id_pap = $id_pap) AND c.estado IS NOT NULL
					  ORDER BY c.fecha DESC";
	$SQL_contratos = "SELECT sum(arancel_cred_interno_uf) AS arancel_cred_interno_uf,sum(desc_cred_interno_uf) AS desc_cred_interno_uf FROM ($SQL_contratos) AS foo";
	$contratos = consulta_sql($SQL_contratos);
	$cred_interno_uf = $contratos[0]['arancel_cred_interno_uf'];
	$cred_interno_desc_uf = $contratos[0]['desc_cred_interno_uf'];
	if (count($contratos) > 0) {
		$SQL_cred_interno = "SELECT max(monto) AS monto FROM finanzas.pagares_cred_interno WHERE id_contrato IN (SELECT id FROM finanzas.contratos WHERE (id_alumno = $id_alumno OR id_pap = $id_pap) AND estado IS NOT NULL)";
		$cred_interno = consulta_sql($SQL_cred_interno);
		$ult_cred_interno = $cred_interno[0]['monto']*1;
		if ($cred_interno_uf < $ult_cred_interno) { $cred_interno_uf = $ult_cred_interno; }
		$cred_interno_uf = $cred_interno_uf - $cred_interno_desc_uf;
	}

	$valor_uf = consulta_sql("SELECT valor FROM finanzas.valor_uf WHERE fecha=now()::date");
	$valor_uf = $valor_uf[0]['valor'];
	
	$valor_cuota_ci_uf = round($ult_valor_cuota / $valor_uf,2);
	$valor_cuota_ci = $ult_valor_cuota;
	
	$max_cuotas_ci = round($cred_interno_uf / $valor_cuota_ci_uf,0);
	if ($max_cuotas_ci > 48) { 
		$max_cuotas_ci = 48; 
		$valor_cuota_ci_uf = round($cred_interno_uf / $max_cuotas_ci,2); 
		$valor_cuota_ci = round($valor_cuota_ci_uf * $valor_uf);
	}

	$ult_valor_cuota = number_format($ult_valor_cuota,0,',','.');
	$valor_cuota_ci_uf = number_format($valor_cuota_ci_uf,2,',','.');
	$valor_cuota_ci  = number_format($valor_cuota_ci,0,',','.');
	
}

?>
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div style="margin-top: 5px">

<form name='formulario' action='<?php echo($_SERVER['SCRIPT_NAME']); ?>' method="get">
<input type='hidden' name='modulo' value='<?php echo($modulo); ?>'>
<input type='hidden' name='id_alumno' value='<?php echo($id_alumno); ?>'>
<input type='hidden' name='rut' value='<?php echo($rut); ?>'>
<input type='hidden' name='cred_interno_uf' value='<?php echo($cred_interno_uf); ?>'>
<input type='hidden' name='valor_uf' value='<?php echo($valor_uf); ?>'>
<?php
	if (empty($rut)) { 
		include_once("ingresar_rut.php");
	} else {
?>
<input type="submit" name="guardar" value="Guardar">
<input type="button" name="Cancelar" value="Cencelar">
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style="margin-top: 5px">
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Alumno(a)</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='text-align: right'>RUT:</td>
    <td class='textoTabla'><?php echo($rut); ?></td>
    <td class='celdaNombreAttr' style='text-align: right'>ID:</td>
    <td class='textoTabla'><?php echo($id_alumno); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='text-align: right'>Nombre:</td>
    <td class='textoTabla' colspan='3'><?php echo($nombre); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='text-align: right'>Monto de última cuota:</td>
    <td class='textoTabla' colspan='3'>$<?php echo($ult_valor_cuota); ?><br>Según último contrato pactado, sin considerar repactaciones.</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='text-align: right' nowrap>Crédito Solidario adeudado:</td>
    <td class='textoTabla' colspan='3'>UF <?php echo($cred_interno_uf); ?><br>Descontadas las condonaciones aplicadas a contratos, si corresponde.</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes para Emitir Liquidación</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='text-align: right'>Monto mínimo de cuota:</td>
    <td class='textoTabla' colspan='3'>UF <?php echo($valor_cuota_ci_uf); ?><br>Calculado al valor de la UF de hoy, equivalente a $<?php echo($valor_cuota_ci); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='text-align: right' nowrap>Cantidad máxima de cuotas:</td>
    <td class='textoTabla' colspan='3'><?php echo($max_cuotas_ci); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='text-align: right'>Monto mínimo Autorizado:</td>
    <td class='textoTabla'>
      $<input type='text' class='montos' size='10' name='valor_minimo' value="<?php echo($valor_cuota_ci); ?>"  onChange='calc_cuotas_max()'
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
    </td>
    <td class='celdaNombreAttr' style='text-align: right'>Máximo de cuotas:</td>
    <td class='textoTabla'><input type='text' class='boton' size='2' name='cuotas_max' value="" readonly></td>
  </tr>
</table>
<?php } ?>
</form>

<script>

calc_cuotas_max();

function calc_cuotas_max() {
	var valor_uf        = Number(formulario.valor_uf.value),
	    valor_minimo    = Number(formulario.valor_minimo.value.replace('.','').replace('.','')),
	    cred_interno_uf = Number(formulario.cred_interno_uf.value.replace(',','.'))	;
	
	if (valor_minimo <= 0) {
		alert("ERROR: Debe asignar un valor mínimo. El monto debe ser superior a cero.");
		return false;
	}
	formulario.cuotas_max.value = Math.ceil(cred_interno_uf/(valor_minimo/valor_uf));	
}
	
</script>
