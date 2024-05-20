<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");
include("validar_modulo.php");

$id_alumno           = $_REQUEST['id_alumno'];
$id_pap              = $_REQUEST['id_pap'];
$rut                 = $_REQUEST['rut'];
$monto_liqci_uf      = floatval(str_replace(",",".",$_REQUEST['monto_liqci_uf']));
$monto_liqci         = str_replace(".","",$_REQUEST['monto_liqci']);
$descuento           = floatval(str_replace(",",".",$_REQUEST['descuento']));
$descuento_inicial   = str_replace(".","",$_REQUEST['descuento_inicial']);
$monto_adicional     = str_replace(".","",$_REQUEST['monto_adicional']);
$monto_adicional_uf  = floatval(str_replace(",",".",$_REQUEST['monto_adicional_uf']));
$comentarios         = $_REQUEST['comentarios'];
$fecha               = $_REQUEST['fecha'];

$monto_convenio = $monto_liqci - $descuento_inicial + $monto_adicional;
$monto_convenio_uf = $monto_liqci_uf - $descuento + $monto_adicional_uf;

$valor_uf = consulta_sql("SELECT valor FROM finanzas.valor_uf WHERE fecha='$fecha'::date");
$valor_uf = $valor_uf[0]['valor'];

if ($_REQUEST['guardar'] == "Guardar y Terminar") {  
	$aCampos = array("fecha","id_alumno","monto_liqci","descuento_inicial","monto_adicional","liqci_efectivo",
	                 "liqci_cheque","liqci_cant_cheques","liqci_diap_cheque","liqci_mes_ini_cheque",
	                 "liqci_ano_ini_cheque","liqci_pagare","liqci_cuotas_pagare","liqci_diap_pagare",
	                 "liqci_mes_ini_pagare","liqci_ano_ini_pagare","liqci_tarj_debito","liqci_tarj_credito",
	                 "liqci_cant_tarj_credito","id_emisor","comentarios");
	
	foreach($_REQUEST AS $campo => $valor) {
		if ($campo <> "comentarios") {
			$_REQUEST[$campo] = str_replace(".","",$_REQUEST[$campo]);
		}
	}
	
	
	$SQLins_liqci = "INSERT INTO finanzas.convenios_ci " . arr2sqlinsert($_REQUEST,$aCampos) .";"
	                    . "SELECT currval('finanzas.convenios_ci_id_seq') AS id";
	$liqci = consulta_sql($SQLins_liqci);
	if (count($liqci) > 0) {
		$id_liqci = $id_convenio = $liqci[0]['id'];

		// Marcar contratos que establecen el convenio de liquidacion
		$SQL_upd_contratos = "UPDATE finanzas.contratos 
		                      SET ci_liquidado = true,id_convenio_liqci = $id_convenio 
		                      WHERE (id_alumno = $id_alumno OR id_pap = $id_pap) AND estado IS NOT NULL AND NOT ci_liquidado";
		consulta_dml($SQL_upd_contratos);                      
		
		echo(msje_js("Se ha guardado exitosamente el Convenio de Liquidación de Crédito Interno"));
		
		if ($_REQUEST['liqci_efectivo'] > 0 || $_REQUEST['liqci_tarj_debito'] > 0 || $_REQUEST['liqci_tarj_credito'] > 0 ) {
			setlocale(LC_MONETARY,"en_US.UTF-8");
			setlocale(LC_NUMERIC,"en_US.UTF-8");

			$contratos = consulta_sql("SELECT ano,arancel_cred_interno FROM finanzas.contratos WHERE id_convenio_liqci=$id_convenio AND arancel_cred_interno IS NOT NULL");

			$monto_total    = $_REQUEST['liqci_efectivo'] + $_REQUEST['liqci_tarj_debito'] + $_REQUEST['liqci_tarj_credito'];
			$monto_total_uf = round($monto_total/$valor_uf,2);

			$monto_tot_ci   = array_sum(array_column($contratos,"arancel_cred_interno"));
			$monto_reajuste = $monto_total - $monto_tot_ci;

			$cant_cuotas    = 1;
			$diap           = strftime("%d",strtotime($_REQUEST['fecha']));
			$mesp           = strftime("%m",strtotime($_REQUEST['fecha']));
			$anop           = strftime("%Y",strtotime($_REQUEST['fecha']));

			for($x=0;$x<count($contratos);$x++) {
				$ano_contrato = $contratos[$x]['ano'];
				$monto_cuota  = $contratos[$x]['arancel_cred_interno'];
				$monto_cuota_uf = round($monto_cuota/$valor_uf,2);

				$id_glosa = consulta_sql("SELECT id FROM finanzas.glosas WHERE nombre ~* 'CI$ano_contrato'");
				$id_glosa = $id_glosa[0]['id'];

				$SQL_cobros = generar_cobros_ci($id_convenio,$id_glosa,$cant_cuotas,$monto_cuota,$monto_cuota_uf,$monto_cuota,$monto_cuota_uf,$diap,$mesp,$anop);
				consulta_sql($SQL_cobros);				
			}

			$monto_cuota  = $monto_reajuste;
			$monto_cuota_uf = round($monto_cuota/$valor_uf,2);

			$id_glosa = consulta_sql("SELECT id FROM finanzas.glosas WHERE nombre ~* 'CI_Reajustes'");
			$id_glosa = $id_glosa[0]['id'];

			$SQL_cobros = generar_cobros_ci($id_convenio,$id_glosa,$cant_cuotas,$monto_cuota,$monto_cuota_uf,$monto_cuota,$monto_cuota_uf,$diap,$mesp,$anop);
			consulta_sql($SQL_cobros);				

/*
			$id_glosa       = 302; // Pago Completo de Liquidación de Créd. Interno
			$monto_total    = $_REQUEST['liqci_efectivo'] + $_REQUEST['liqci_tarj_debito'] + $_REQUEST['liqci_tarj_credito'];
			$monto_total_uf = round($monto_total/$valor_uf,2);
			$monto_cuota    = round($monto_total/$cant_cuotas,0);
			$monto_cuota_uf = round($monto_total_uf/$cant_cuotas,2);
			$diap           = strftime("%d",strtotime($_REQUEST['fecha']));
			$mesp           = strftime("%m",strtotime($_REQUEST['fecha']));
			$anop           = strftime("%Y",strtotime($_REQUEST['fecha']));
			$SQL_cobros     = generar_cobros_ci($id_convenio,$id_glosa,$cant_cuotas,$monto_cuota,$monto_cuota_uf,$monto_total,$monto_total_uf,$diap,$mesp,$anop);
			consulta_sql($SQL_cobros);
*/
		}
		
		if ($_REQUEST['liqci_cheque'] > 0 && $_REQUEST['liqci_cant_cheques'] > 0) {
			setlocale(LC_MONETARY,"en_US.UTF-8");
			setlocale(LC_NUMERIC,"en_US.UTF-8");
			$id_glosa       = 303; // Mensualidad de Cheque de Liquidación de Créd. Interno
			$cant_cuotas    = $_REQUEST['liqci_cant_cheques'];
			$monto_total    = $_REQUEST['liqci_cheque'];
			$monto_total_uf = round($monto_total/$valor_uf,2);
			$monto_cuota    = round($monto_total/$cant_cuotas,0);
			$monto_cuota_uf = round($monto_total_uf/$cant_cuotas,2);
			$diap           = $_REQUEST['liqci_diap_cheque'];
			$mesp           = $_REQUEST['liqci_mes_ini_cheque'];
			$anop           = $_REQUEST['liqci_ano_ini_cheque'];
			$SQL_cobros     = generar_cobros_ci($id_convenio,$id_glosa,$cant_cuotas,$monto_cuota,$monto_cuota_uf,$monto_total,$monto_total_uf,$diap,$mesp,$anop);
			consulta_sql($SQL_cobros);
		}

		if ($_REQUEST['liqci_pagare'] > 0 && $_REQUEST['liqci_cuotas_pagare'] > 0) {
			setlocale(LC_MONETARY,"en_US.UTF-8");
			setlocale(LC_NUMERIC,"en_US.UTF-8");
			$monto_total    = $_REQUEST['liqci_pagare'];
			$monto_total_uf = round($monto_total / $valor_uf,2);
			$cant_cuotas    = $_REQUEST['liqci_cuotas_pagare'];
			$diap           = $_REQUEST['liqci_diap_pagare'];
			$mesp           = $_REQUEST['liqci_mes_ini_pagare'];
			$anop           = $_REQUEST['liqci_ano_ini_pagare'];
			$fecha_ini      = "$diap-$mesp-$anop";
			if ($diap>28 && $mesp==2) { $fecha_ini = "28-$mesp-$anop"; }
			$SQLins_pagare_liqci = "INSERT INTO finanzas.pagares_liqci (id_convenio_ci,monto,fecha_pago_ini,cuotas,fecha)
			                             VALUES ($id_convenio,$monto_total_uf,'$fecha_ini'::date,$cant_cuotas,'$fecha'::date);
			                       	SELECT currval('finanzas.pagares_liqci_id_seq') AS id";
			$pagare_liqci = consulta_sql($SQLins_pagare_liqci);
			$id_pagare_liqci = $pagare_liqci[0]['id'];
			$id_glosa       = 300; // Mensualidad de Pagaré de Liquidación de Créd. Interno
			$monto_cuota    = round($_REQUEST['liqci_pagare']/$cant_cuotas,0);
			$monto_cuota_uf = round($monto_total_uf/$cant_cuotas,2);
			$SQL_cobros     = generar_cobros_ci($id_convenio,$id_glosa,$cant_cuotas,$monto_cuota,$monto_cuota_uf,$monto_total,$monto_total_uf,$diap,$mesp,$anop);
			consulta_sql($SQL_cobros);
		}

		if (is_numeric($id_pagare_liqci)) {
			echo(js("window.open('pagare_liqci.php?id=$id_pagare_liqci');"));
		}

		echo(js("parent.jQuery.fancybox.close();"));
		exit;

	}

}

$SQL_alumno = "SELECT a.id AS id_alumno,trim(a.rut) AS rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre,c.alias||'-'||a.jornada AS carrera,
                      a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.mes_cohorte,ae.nombre AS estado,a.id_pap,
                      CASE WHEN m.id_alumno IS NOT NULL THEN 'Si' ELSE 'No' END AS matriculado,moroso_financiero
               FROM alumnos AS a
               LEFT JOIN carreras AS c ON c.id=a.carrera_actual
               LEFT JOIN al_estados AS ae ON ae.id=a.estado
               LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND semestre=$SEMESTRE AND ano=$ANO) 
               WHERE a.id=$id_alumno";
$alumno = consulta_sql($SQL_alumno); 
if (count($alumno) == 0) {
	echo(msje_js("ERROR: El RUT ingresado no corresponde a un alumno."));
	echo(js("parent.jQuery.fancybox.close()"));
	exit;
}
extract($alumno[0]);

$excepcion_ci = consulta_sql("SELECT valor_min FROM finanzas.convenios_ci_excepciones WHERE id_alumno=$id_alumno");

$SQL_contratos = "SELECT CASE WHEN arancel_cheque > 0 THEN round(arancel_cheque/arancel_cant_cheques)
                              WHEN arancel_pagare_coleg > 0 THEN round(arancel_pagare_coleg / arancel_cuotas_pagare_coleg)
                              WHEN arancel_efectivo > 0 THEN round(arancel_efectivo/a.cuotas)
                              WHEN arancel_tarjeta_credito > 0 THEN round(arancel_tarjeta_credito/a.cuotas)
                         END AS max_valor_cuota
                  FROM finanzas.contratos AS c
                  LEFT JOIN aranceles AS a ON (a.ano=c.ano AND a.id_carrera=c.id_carrera AND a.jornada=c.jornada AND a.semestre=1)
                  WHERE (c.id_alumno = $id_alumno OR c.id_pap = $id_pap) AND c.estado IS NOT NULL ORDER BY c.ano DESC LIMIT 1";
$SQL_contratos = "SELECT max(max_valor_cuota) AS max_valor_cuota FROM ($SQL_contratos) AS foo";
$contratos = consulta_sql($SQL_contratos);
$ult_valor_cuota = $contratos[0]['max_valor_cuota'];

if (count($excepcion_ci) > 0) { 
	$ult_valor_cuota = $excepcion_ci[0]['valor_min'];
	echo(msje_js("Este alumno posee una excepción autorizada que altera los criterios de cálculo máximo de cuotas."));
}
if ($ult_valor_cuota > 0) { 
	$max_cuotas = round($monto_convenio / $ult_valor_cuota) + 1;
	if ($max_cuotas > 72) { $max_cuotas = round($monto_convenio / 100000); }
} else {
	$max_cuotas = 48;
}
$CUOTAS = array();
for ($x=1;$x<=$max_cuotas;$x++) { $CUOTAS = array_merge($CUOTAS,array(array("id"=>$x,"nombre"=>$x))); } 

$dias_pago = array(array("id"=>5 ,"nombre"=>"5"),
                   array("id"=>15,"nombre"=>"15"),
                   array("id"=>30,"nombre"=>"30"));

$ano_ini = strftime("%Y");
$anos_ini = array();
for ($ano=$ano_ini+1;$ano>=2013;$ano--) { $anos_ini = array_merge($anos_ini,array(array("id"=>$ano,"nombre"=>$ano))); } 

/*
$anos_ini = array(array("id"=>$ano_ini-1,"nombre"=>$ano_ini-1),
                  array("id"=>$ano_ini,  "nombre"=>$ano_ini),
                  array("id"=>$ano_ini+1,"nombre"=>$ano_ini+1));
*/

if (empty($_REQUEST['liqci_mes_ini_pagare'])) { $_REQUEST['liqci_mes_ini_pagare'] = date("m"); }
if (empty($_REQUEST['liqci_ano_ini_pagare'])) { $_REQUEST['liqci_ano_ini_pagare'] = date("Y"); }
if (empty($_REQUEST['liqci_mes_ini_cheque'])) { $_REQUEST['liqci_mes_ini_cheque'] = date("m"); }
if (empty($_REQUEST['liqci_ano_ini_cheque'])) { $_REQUEST['liqci_ano_ini_cheque'] = date("Y"); }
?>
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div style="margin-top: 5px">
<form name='formulario' action='<?php echo($_SERVER['SCRIPT_NAME']); ?>' method="post" onSubmit="if (!verif_finan()) { return false; }">
<input type='hidden' name='modulo' value='<?php echo($modulo); ?>'>
<input type='hidden' name='valor_uf' value='<?php echo($valor_uf); ?>'>
<input type='hidden' name='id_alumno' value='<?php echo($id_alumno); ?>'>
<input type='hidden' name='id_pap' value='<?php echo($id_pap); ?>'>
<input type='hidden' name='id_emisor' value='<?php echo($_SESSION['id_usuario']); ?>'>
<input type='hidden' name='rut' value='<?php echo($rut); ?>'>
<?php if (empty($rut)) { include_once("ingresar_rut.php"); } ?>
<?php if (count($alumno) == 1) { ?>
<div>
  <input type='submit' name='guardar' value='Guardar y Terminar'>
  <input type='button' name='restablecer' value='Volver' onClick="history.back();">
  <input type='button' name='cancelar' value='Cancelar' onClick="parent.jQuery.fancybox.close();">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style="margin-top: 5px">
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Alumno(a)</td></tr>
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
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes del Convenio de Liquidación de Credito(s) Interno(s)</td></tr>
    <tr>
    <td class='celdaNombreAttr' colspan='2' style='text-align: right'>Fecha:</td>
    <td class='textoTabla'><input type='text' size='6' name='fecha' class='boton' value='<?php echo($fecha); ?>' readonly></td>
    <td class='textoTabla' style='text-align: right'><b>Valor UF:</b> $<?php echo(number_format($valor_uf,2,',','.')); ?>&nbsp;</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan='2' style='vertical-align: middle'>Total Crédito(s) Interno(s):</td>
    <td class='celdaValorAttr' style='text-align: right'><b>UF</b> <input type='text' size='2' name='monto_liqci_uf' value='<?php echo($monto_liqci_uf); ?>' class='montos' readonly>&nbsp;</td>
    <td class='celdaValorAttr' style='text-align: right'>$<input type='text' size='4' name='monto_liqci' value='<?php echo(number_format($monto_liqci,0,",",".")); ?>' class='montos' readonly>&nbsp;</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan='2' style='vertical-align: middle'>Descuentos:</td>
    <td class='celdaValorAttr' style='text-align: right'>(<b>UF</b> <input type='text' size='2' name='descuento' value='<?php echo($descuento); ?>' class='montos' style='color: red' readonly>)</td>
    <td class='celdaValorAttr' style='text-align: right'>($<input type='text' size='4' name='descuento_inicial' value='<?php echo(number_format($descuento_inicial,0,",",".")); ?>' class='montos' style='color: red' readonly>)</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan='2' style='vertical-align: middle'>Monto Adicional:</td>
    <td class='celdaValorAttr' style='text-align: right'><b>UF</b> <input type='text' size='2' name='monto_adicional_uf' value='<?php echo($monto_adicional_uf); ?>' class='montos' readonly>&nbsp;</td>
    <td class='celdaValorAttr' style='text-align: right'>$<input type='text' size='4' name='monto_adicional' value='<?php echo(number_format($monto_adicional,0,",",".")); ?>' class='montos'readonly>&nbsp;</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan='2' style='vertical-align: middle'>Valor Total del Convenio:</td>
    <td class='celdaValorAttr' style='text-align: right'><b>UF</b> <input type='text' size='2' name='monto_convenio_uf' value='<?php echo($monto_convenio_uf); ?>' class='montos' readonly>&nbsp;</td>
    <td class='celdaValorAttr' style='text-align: right; background: yellow'>$<input type='text' size='4' name='monto_convenio' value='<?php echo(number_format($monto_convenio,0,",",".")); ?>' class='montos' readonly>&nbsp;</td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Financiamiento</td></tr>
  <tr>
    <td class='celdaNombreAttr' colspan='2'>Efectivo:</td>
    <td class='celdaValorAttr' colspan='2'>
      $<input type='text' class='montos' size='10' name='liqci_efectivo' value="<?php echo($_REQUEST['liqci_efectivo']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan='2'>Cheque(s):</td>
    <td class='celdaValorAttr' colspan='2'>
      $<input type='text' class='montos' size='10' name='liqci_cheque' value="<?php echo($_REQUEST['liqci_cheque']); ?>" onChange='calc_valor_cuota_cheque()'
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
      <small>
        Cantidad: <select class='filtro' name='liqci_cant_cheques' onChange='calc_valor_cuota_cheque()'><?php echo(select($CUOTAS,$_REQUEST['liqci_cant_cheques'])); ?></select>
        <b>$</b><input type='text' name='valor_cuota_cheque' value='0' size='4' class='montos' onChange="puntitos(this,this.value.charAt(this.value.length-1),this.name)" readonly>
      </small>
      <table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" align='center'>
        <tr>
          <td class='celdaNombreAttr'><sub>Día Venc</sub></td>
          <td class='celdaNombreAttr' style='text-align: center'><sub>Mes inicio</sub></td>
          <td class='celdaNombreAttr'><sub>Año inicio</sub></td></tr>
        <tr>
          <td class='celdaValorAttr'><select name='liqci_diap_cheque' class='filtro'><?php echo(select($dias_pago,$_REQUEST['liqci_diap_cheque'])); ?></select></td>
          <td class='celdaValorAttr'><select name="liqci_mes_ini_cheque" class='filtro'><?php echo(select($meses_palabra,$_REQUEST['liqci_mes_ini_cheque'])); ?></select></td>
          <td class='celdaValorAttr'><select name="liqci_ano_ini_cheque" class='filtro'><?php echo(select($anos_ini,$_REQUEST['liqci_ano_ini_cheque'])); ?></select></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan='2'>Pagaré de Liquidación CI:</td>
    <td class='celdaValorAttr' colspan='2'>
      $<input type='text' class='montos' size='10' name='liqci_pagare' value="<?php echo($_REQUEST['liqci_pagare']); ?>"  onChange='calc_valor_cuota_pagare()'
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
      <sub>
        Cuotas: <select name='liqci_cuotas_pagare' class='filtro' onChange='calc_valor_cuota_pagare()'><?php echo(select($CUOTAS,$_REQUEST['liqci_cuotas_pagare'])); ?></select>
        <b>UF</b><input type='text' name='valor_cuota_pagare' value='0' size='4' class='montos' readonly>
      </sub>
      <table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" align='center'>
        <tr>
          <td class='celdaNombreAttr'><sub>Día pago</sub></td>
          <td class='celdaNombreAttr' style='text-align: center'><sub>Mes inicio</sub></td>
          <td class='celdaNombreAttr'><sub>Año inicio</sub></td>
        </tr>
        <tr>
          <td class='celdaValorAttr'><select name='liqci_diap_pagare' class='filtro'><?php echo(select($dias_pago,$_REQUEST['liqci_diap_pagare'])); ?></select></td>
          <td class='celdaValorAttr'><select name="liqci_mes_ini_pagare" class='filtro'><?php echo(select($meses_palabra,$_REQUEST['liqci_mes_ini_pagare'])); ?></select></td>
          <td class='celdaValorAttr'><select name="liqci_ano_ini_pagare" class='filtro'><?php echo(select($anos_ini,$_REQUEST['liqci_ano_ini_pagare'])); ?></select></td>
        </tr>
      </table>  
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan='2'>Tarjetas de Débito:</td>
    <td class='celdaValorAttr' colspan='2'>
      $<input type='text' class='montos' size='10' name='liqci_tarj_debito' value="<?php echo($_REQUEST['liqci_tarj_debito']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
    </td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr' colspan='2'>Tarjetas de Cŕédito:</td>
    <td class='celdaValorAttr' colspan='2'>
      $<input type='text' class='montos' size='10' name='liqci_tarj_credito' value="<?php echo($_REQUEST['liqci_tarj_credito']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
      <sub>
        Cuotas: <select name='liqci_cant_tarj_credito' class='filtro'><?php echo(select($CUOTAS,$_REQUEST['liqci_cant_tarj_credito'])); ?></select>
      </sub>
    </td>
  </tr>  
  <tr><td class='celdaNombreAttr' colspan='4' style='text-align: left'>Comentarios:</td></tr>
  <tr><td class='celdaValorAttr' colspan='4'><textarea name='comentarios' class='general' rows='4' cols='50'><?php echo($comentarios); ?></textarea></td></tr>
  <tr><td class='celdaNombreAttr' colspan="4"><input type='submit' name='guardar' value='Guardar y Terminar'></td></tr>
</table>
<?php } ?>
</form>
</div>

<script>
function verif_finan() {
	var monto_convenio     = Number(formulario.monto_convenio.value.replace('.','').replace('.','')),
	    liqci_pagare       = Number(formulario.liqci_pagare.value.replace('.','').replace('.','')),
	    liqci_cheque       = Number(formulario.liqci_cheque.value.replace('.','').replace('.','')),
	    liqci_efectivo     = Number(formulario.liqci_efectivo.value.replace('.','').replace('.','')),
	    liqci_tarj_credito = Number(formulario.liqci_tarj_credito.value.replace('.','').replace('.','')),
	    liqci_tarj_debito  = Number(formulario.liqci_tarj_debito.value.replace('.','').replace('.','')),
	    monto_finan        = 0;
	    
	monto_finan = liqci_efectivo + liqci_cheque + liqci_pagare + liqci_tarj_credito + liqci_tarj_debito;
	    
	if (monto_finan > monto_convenio) { 
		alert("ERROR: Ha ingresado montos en las formas de pagos que sumados superan al Valor Total del Convenio.\n\nNo es posible continuar, debe corregir esto.");
		return false;
	}
	
	if (monto_finan < monto_convenio) { 
		alert("ERROR: Ha ingresado montos en las formas de pagos que sumados son inferiores al Valor Total del Convenio.\n\nNo es posible continuar, debe corregir esto.");
		return false;
	}
	
	return true;
}

function calc_valor_cuota_pagare() {
	var monto_convenio      = Number(formulario.monto_convenio.value.replace('.','').replace('.','')),
	    liqci_pagare        = Number(formulario.liqci_pagare.value.replace('.','').replace('.','')),
	    liqci_cuotas_pagare = Number(formulario.liqci_cuotas_pagare.value),
	    valor_uf            = Number(formulario.valor_uf.value),
	    valor_cuota_pagare  = 0;
	    
	if (liqci_pagare <= monto_convenio) {
		valor_cuota_pagare = (liqci_pagare / valor_uf) / liqci_cuotas_pagare;
		formulario.valor_cuota_pagare.value = Math.round(valor_cuota_pagare*100)/100;
		return true;
	} else {
		alert("ERROR: Ha ingresado un monto mayor al Valor Total del Convenio que debe financiar, en la casilla de Pagaré de Liquidación CI.\n\nDebe corregir este valor.");
		formulario.liqci_pagare.value = null;
		return false;
	}
}

function calc_valor_cuota_cheque() {
	var monto_convenio      = Number(formulario.monto_convenio.value.replace('.','').replace('.','')),
	    liqci_cheque        = Number(formulario.liqci_cheque.value.replace('.','').replace('.','')),
	    liqci_cant_cheques  = Number(formulario.liqci_cant_cheques.value),
	    valor_uf            = Number(formulario.valor_uf.value),
	    valor_cuota_cheque  = 0;
	    
	if (liqci_cheque <= monto_convenio) {
		valor_cuota_cheque = liqci_cheque / liqci_cant_cheques;
		formulario.valor_cuota_cheque.value = Math.round(valor_cuota_cheque);
		puntitos(document.formulario.valor_cuota_cheque,document.formulario.valor_cuota_cheque.value.charAt(document.formulario.valor_cuota_cheque.value.length-1),document.formulario.valor_cuota_cheque.name);
		return true;
	} else {
		alert("ERROR: Ha ingresado un monto mayor al Valor Total del Convenio que debe financiar, en la casilla de Cheque(s).\n\nDebe corregir este valor.");
		formulario.liqci_pagare.value = null;
		return false;
	}
}

calc_valor_cuota_cheque();
calc_valor_cuota_pagare();
</script>
