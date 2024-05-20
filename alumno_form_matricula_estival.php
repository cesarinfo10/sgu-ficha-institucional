<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$id_alumno  = $_REQUEST['id_alumno'];
if (!is_numeric($id_alumno)) {
	echo(js("location.href='$enlbase=gestion_alumnos';"));
	exit;
}

$SQL_contrato = "SELECT id FROM finanzas.contratos 
                 WHERE id_alumno=$id_alumno AND ano=$ANO_MATRICULA AND estado IN ('E','F') AND trim(tipo)='Estival'";
$contrato = consulta_sql($SQL_contrato);
if (count($contrato) > 0) {
	echo(msje_js("Este alumno ya tiene un contrato Estival emitido para este periodo. "
	            ."Revise los documentos existentes en el módulo <Documentos de Matrícula>."));
	echo(js("location.href='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
	exit;
}

$VALOR_CURSO = 9;
$VALOR_CURSO_credito = 10;

$SQL_alumno = "SELECT va.id,va.rut,va.nombre,va.direccion,va.comuna,va.region,
                      al.genero AS cod_genero,al.nacionalidad AS cod_nac,al.comuna AS cod_comuna,
                      al.region AS cod_region,al.email,al.pasaporte,al.telefono,al.tel_movil,al.id_aval,
                      va.carrera,al.carrera_actual AS id_carrera,
                      CASE al.jornada WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada,
                      al.jornada AS id_jornada
               FROM alumnos AS al
               LEFT JOIN vista_alumnos AS va USING (id)
               WHERE va.id=$id_alumno;";
$alumno = consulta_sql($SQL_alumno);
if (count($alumno) > 0) {	
	$id_aval  = $alumno[0]['id_aval'];
	$SQL_aval = "SELECT id,rf_rut,rf_nombre,rf_direccion,rf_com,rf_reg
	             FROM vista_avales WHERE id=$id_aval;";
	$aval     = consulta_sql($SQL_aval);
} else {
	echo(js("location.href='$enlbase=gestion_alumnos';"));
	exit;
}

if ($_REQUEST['guardar'] == "Guardar") {

	if ($_REQUEST['financiamiento'] == 'CREDITO') {
		$_REQUEST['monto_arancel'] = round($VALOR_CURSO_credito * $_REQUEST['cant_cursos'] * $_REQUEST['valor_uf']);
	} elseif ($_REQUEST['financiamiento'] == 'CONTADO') {
		$_REQUEST['monto_arancel'] = round($VALOR_CURSO * $_REQUEST['cant_cursos'] * $_REQUEST['valor_uf']);
	}	
	
	if ($_REQUEST['arancel_cheque'] == "" || $_REQUEST['arancel_cheque'] == 0) {
		$_REQUEST['arancel_cheque']         = "";
		$_REQUEST['arancel_cant_cheques']   = "";
		$_REQUEST['arancel_diap_cheque']    = "";
		$_REQUEST['arancel_mes_ini_cheque'] = "";
		$_REQUEST['arancel_ano_ini_cheque'] = "";
	}

	if ($_REQUEST['arancel_pagare_coleg'] == "" || $_REQUEST['arancel_pagare_coleg'] == 0) {
		$_REQUEST['arancel_pagare_coleg']         = "";
		$_REQUEST['arancel_cuotas_pagare_coleg']  = "";
		$_REQUEST['arancel_diap_pagare_coleg']    = "";
		$_REQUEST['arancel_mes_ini_pagare_coleg'] = "";		
	}

	if ($_REQUEST['arancel_tarjeta_credito'] == "" || $_REQUEST['arancel_tarjeta_credito'] == 0) {
		$_REQUEST['arancel_cant_tarj_credito'] = "";
	}

	$aCampos = array('id_alumno','id_aval','id_carrera','jornada','nivel','tipo',
	                 'monto_arancel','cant_cursos',
	                 'id_beca_arancel','monto_beca_arancel','porc_beca_arancel',
	                 'financiamiento',
	                 'arancel_efectivo',
	                 'arancel_cheque','arancel_cant_cheques','arancel_diap_cheque','arancel_mes_ini_cheque','arancel_ano_ini_cheque',
	                 'arancel_pagare_coleg','arancel_cuotas_pagare_coleg','arancel_diap_pagare_coleg','arancel_mes_ini_pagare_coleg',
	                 'arancel_tarjeta_credito',
	                 'ano','semestre');

	foreach ($_REQUEST AS $campo => $valor) {
		if (substr($campo,0,8) == "arancel_" || substr($campo,0,6) == "monto_") {
			$_REQUEST[$campo] = str_replace(".","",$valor);
		}
	}
	
	$SQLinsert_contrato = "INSERT INTO finanzas.contratos " . arr2sqlinsert($_REQUEST,$aCampos) .";"
	                    . "SELECT currval('finanzas.contratos_id_seq') AS id";
	$contrato = consulta_sql($SQLinsert_contrato);

	if (count($contrato) == 1) {
		$id_contrato = $contrato[0]['id'];
		echo(msje_js("Se ha guardado exitosamente el contrato"));
		
		if ($_REQUEST['arancel_pagare_coleg'] > 0 && $_REQUEST['arancel_cuotas_pagare_coleg'] > 0) {
			$SQLinsert_pagare_coleg = "INSERT INTO finanzas.pagares_colegiatura (id_contrato,cuotas,dia_pago,mes_inicio,ano_inicio,monto) "
			                        . "VALUES ($id_contrato,{$_REQUEST['arancel_cuotas_pagare_coleg']},{$_REQUEST['arancel_diap_pagare_coleg']},{$_REQUEST['arancel_mes_ini_pagare_coleg']},{$_REQUEST['ano']},{$_REQUEST['arancel_pagare_coleg']});"
			                        . "SELECT currval('finanzas.pagares_colegiatura_id_seq') AS id";
			$pagare_colegiatura = consulta_sql($SQLinsert_pagare_coleg);			
			if (count($pagare_colegiatura) == 1) {
				$id_pagare_colegiatura = $pagare_colegiatura[0]['id'];
				echo(msje_js("Se ha guardado exitosamente el pagaré de colegiatura"));
			}
		}

		if (is_numeric($id_contrato)) {
			echo(js("window.open('contrato.php?id_contrato=$id_contrato&tipo=estival');"));
		}

		if (is_numeric($id_pagare_colegiatura)) {
			echo(js("window.open('pagare_colegiatura.php?id_pagare_colegiatura=$id_pagare_colegiatura');"));
		}

		echo(js("location.href='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
		exit;
	}
} 

$becas = consulta_sql("SELECT id,nombre FROM becas;");
$uf    = consulta_sql("SELECT valor FROM finanzas.valor_uf WHERE fecha=now()::date;");

$CUOTAS = array();
for ($x=1;$x<=6;$x++) { $CUOTAS = array_merge($CUOTAS,array(array("id"=>$x,"nombre"=>$x))); } 

$dias_pago = array(array("id"=>5 ,"nombre"=>"5"),
                   array("id"=>15,"nombre"=>"15"),
                   array("id"=>30,"nombre"=>"30"));
                   
$anos_ini = array(array("id"=>2009,"nombre"=>"2009"),
                  array("id"=>2010,"nombre"=>"2010"));

$CANT_CURSOS = array();
for ($x=1;$x<=6;$x++) {
	$CANT_CURSOS = array_merge($CANT_CURSOS,array(array("id"=>$x,"nombre"=>$x)));
}

if ($_REQUEST['arancel_cant_cheques'] == "") { $_REQUEST['arancel_cant_cheques'] = $aranceles[0]['cuotas']; }
if ($_REQUEST['arancel_mes_ini_cheque'] == "") { $_REQUEST['arancel_mes_ini_cheque'] = 3; }
if ($_REQUEST['arancel_ano_ini_cheque'] == "") { $_REQUEST['arancel_ano_ini_cheque'] = 2010; }
if ($_REQUEST['arancel_mes_ini_pagare_coleg'] == "") { $_REQUEST['arancel_mes_ini_pagare_coleg'] = 3; } 
if ($_REQUEST['arancel_cuotas_pagare_coleg'] == "") { $_REQUEST['arancel_cuotas_pagare_coleg'] = $aranceles[0]['cuotas']; }
//if (time() < strtotime("2009-12-31")) { $_REQUEST['cod_beca_mat'] = "UMC"; $_REQUEST['monto_beca_mat'] = "75000"; }
?>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>
<br>
<form name="formulario" action="principal.php" method="post" onSubmit="if (!verif_arancel_finan()) { return false; }">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_alumno" value="<?php echo($id_alumno); ?>">
<input type="hidden" name="id_carrera" value="<?php echo($alumno[0]['id_carrera']); ?>">
<input type="hidden" name="jornada" value="<?php echo($alumno[0]['id_jornada']); ?>">
<input type="hidden" name="id_aval" value="<?php echo($id_aval); ?>">
<input type="hidden" name="valor_uf" value="<?php echo($uf[0]['valor']); ?>">
<input type="hidden" name="valor_curso" value="<?php echo($VALOR_CURSO); ?>">
<input type="hidden" name="valor_curso_credito" value="<?php echo($VALOR_CURSO_credito); ?>">
<input type="hidden" name="tipo" value="Estival">
<input type="hidden" name="ano" value="2010">
<input type="hidden" name="semestre" value="0">

<table class="tabla">
  <tr>
    <td class="tituloTabla">
      <input type="submit" name="guardar" value="Guardar" tabindex="99">
    </td>
    <td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar" onclick="history.back();"></td>
  </tr>
</table>
<br>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla">
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">
      Antecedentes Personales del Alumno</sup>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['rut']); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['id']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($alumno[0]['nombre']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Dirección:</td>
    <td class='celdaValorAttr' colspan="3">
      <?php echo($alumno[0]['direccion']); ?>, <?php echo($alumno[0]['comuna']); ?>, <?php echo($alumno[0]['region']); ?>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">
      Antecedentes del Responsable Financiero<br><sup>(Apoderado, Sostenedor,Aval o deudor directo)</sup> 
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
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center">Carrera en que se Matricula</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr' colspan="3">
      <?php echo($alumno[0]['carrera']); ?> <b>Jornada:</b> <?php echo($alumno[0]['jornada']); ?> en el
      <select name="nivel">
        <?php echo(select($NIVELES,$_REQUEST['nivel'])); ?>
      </select> nivel
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cantidad de cursos:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name="cant_cursos" onClick="calc_arancel();">
        <?php echo(select($CANT_CURSOS,$_REQUEST['cant_cursos'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center">Valores</td>
  </tr>
  <tr>    
    <td class='celdaValorAttr' colspan="4" align='center'>
      <b>Valor por Curso de Verano:</b> U.F. 10.-
      <b>Valor U.F.:</b> $<?php echo(number_format($uf[0]['valor'],2,',','.')); ?>&nbsp;&nbsp;&nbsp;
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center">Descuentos de Arancel</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Beca:</u></td>
    <td class='celdaValorAttr' colspan="3">
      <select name='id_beca_arancel' onChange="becas(this.value);" onClick="calc_arancel();">
        <option value=''>-- Ninguna --</option>
			<?php echo(select($becas,$_REQUEST['id_beca_arancel'])); ?>        
      </select>
      Monto: $<input type='text' class="montos" size="10" name='monto_beca_arancel' value="<?php echo($_REQUEST['monto_beca_arancel']); ?>"
                     onkeyup="puntitos(this,this.value.charAt(this.value.length-1),'monto_beca_arancel')"
                     onBlur="if (this.value != '') { formulario.porc_beca_arancel.value=''; } calc_arancel();" disabled> ó
      Porcentaje: <input type='text' class="montos" size="2" name='porc_beca_arancel' value="<?php echo($_REQUEST['porc_beca_arancel']); ?>"
                         onBlur="if (this.value != '') { formulario.monto_beca_arancel.value=''; } calc_arancel();" disabled>%
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center">
      Financiamiento Arancel:
      <select name='financiamiento' onChange="finan()">
			<?php echo(select($financiamientos,$_REQUEST['financiamiento'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Monto a Financiar:</u></td>
    <td class='celdaValorAttr' colspan="3">
      $<input type="text" size="10" style="border: none" name="arancel_finan" value="0" readonly>
      <input type="button" size="5" name="calcular" value="Calcular" onClick="calc_arancel();">
    </td>
  </tr>

<script>

function calc_arancel() {
	var arancel_rebajado = 0,monto_beca=0;
	
	if (formulario.financiamiento.value == "CREDITO") {
		arancel_rebajado = Math.round(formulario.valor_curso_credito.value * formulario.cant_cursos.value * formulario.valor_uf.value);
	} else {
		arancel_rebajado = Math.round(formulario.valor_curso.value * formulario.cant_cursos.value * formulario.valor_uf.value);
	}
		
	if (formulario.id_beca_arancel.value != '') {
		if (formulario.monto_beca_arancel.value != '' && formulario.porc_beca_arancel.value == '') {
			monto_beca_arancel = formulario.monto_beca_arancel.value;
			arancel_rebajado = arancel_rebajado - monto_beca_arancel.replace('.','').replace('.','');
		}
		if (formulario.monto_beca_arancel.value == '' && formulario.porc_beca_arancel.value != '') {				
			arancel_rebajado = arancel_rebajado - Math.round(arancel_rebajado * (formulario.porc_beca_arancel.value/100));
		}
	}
	formulario.arancel_finan.value = arancel_rebajado;
}

function becas(id_beca_arancel) {
	if (id_beca_arancel != '') {
		formulario.monto_beca_arancel.disabled=false;
		formulario.porc_beca_arancel.disabled=false;
	} else {
		formulario.monto_beca_arancel.disabled=true;
		formulario.monto_beca_arancel.value=null;
		formulario.porc_beca_arancel.disabled=true;
		formulario.porc_beca_arancel.value=null;
	}
}	

function finan() {
	formulario.arancel_finan.value = 0;
	calc_arancel();
}	

function verif_arancel_finan() {
	var total_arancel_finan=0;
	var arancel_efectivo        = formulario.arancel_efectivo.value,
	    arancel_cheque          = formulario.arancel_cheque.value,
	    arancel_pagare_coleg    = formulario.arancel_pagare_coleg.value,
	    arancel_tarjeta_credito = formulario.arancel_tarjeta_credito.value,
	    diferencia              = 0;
	    
	total_arancel_finan = arancel_efectivo.replace('.','').replace('.','')*1 +
	                      arancel_cheque.replace('.','').replace('.','')*1 +
	                      arancel_pagare_coleg.replace('.','').replace('.','')*1 +
	                      arancel_tarjeta_credito.replace('.','').replace('.','')*1;
	
	diferencia = formulario.arancel_finan.value - total_arancel_finan;
	
	if (total_arancel_finan == formulario.arancel_finan.value) {
		return true;
	} else {
		alert("El monto a financiar del arancel no está completo o hay un error lógico. Actualmente los valores ingresados suman $"+total_arancel_finan+" restando financiar un monto de $"+diferencia);
		return false;
	}
}

finan();
		
</script>  

  <tr>
    <td class='celdaNombreAttr'>Efectivo:</td>
    <td class='celdaValorAttr' colspan="3">
      $<input type='text' class='montos' size='10' name='arancel_efectivo' value="<?php echo($_REQUEST['arancel_efectivo']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cheque(s):</td>
    <td class='celdaValorAttr' colspan="3">
      $<input type='text' class='montos' size='10' name='arancel_cheque' value="<?php echo($_REQUEST['arancel_cheque']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
      <sub>
        Cant.: <select name='arancel_cant_cheques'><?php echo(select($CUOTAS,$_REQUEST['arancel_cant_cheques'])); ?></select>
        Día venc.: <select name='arancel_diap_cheque'><?php echo(select($dias_pago,$_REQUEST['arancel_diap_cheque'])); ?></select>
        Mes inicio: <select name="arancel_mes_ini_cheque"><?php echo(select($meses_palabra,$_REQUEST['arancel_mes_ini_cheque'])); ?></select>
        Año inicio: <select name="arancel_ano_ini_cheque"><?php echo(select($anos_ini,$_REQUEST['arancel_ano_ini_cheque'])); ?></select>
      </sub>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Pagaré Colegiatura:</td>
    <td class='celdaValorAttr' colspan="3">
      $<input type='text' class='montos' size='10' name='arancel_pagare_coleg' value="<?php echo($_REQUEST['arancel_pagare_coleg']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
      <sub>
        Cuotas: <select name='arancel_cuotas_pagare_coleg'><?php echo(select($CUOTAS,$_REQUEST['arancel_cuotas_pagare_coleg'])); ?></select>
        Día pago: <select name='arancel_diap_pagare_coleg'><?php echo(select($dias_pago,$_REQUEST['arancel_diap_pagare_coleg'])); ?></select>
        Mes inicio: <select name="arancel_mes_ini_pagare_coleg"><?php echo(select($meses_palabra,$_REQUEST['arancel_mes_ini_pagare_coleg'])); ?></select>
      </sub>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tarjetas de Cŕédito:</td>
    <td class='celdaValorAttr' colspan="3">
      $<input type='text' class='montos' size='10' name='arancel_tarjeta_credito' value="<?php echo($_REQUEST['arancel_tarjeta_credito']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
      Cant.: <input type='text' size='2' name='arancel_cant_tarj_cred' value="<?php echo($_REQUEST['arancel_cant_tarj_cred']); ?>">
    </td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="4" align="center">
      <b>Cant:</b> Se refiere a Cantidad de Cheques o Cantidad de Cuotas de Tarjetas
    </td>
  </tr>
</table>

</form>