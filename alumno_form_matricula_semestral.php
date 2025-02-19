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
                 WHERE id_alumno=$id_alumno AND ano=$ANO_MATRICULA AND estado IN ('E','F') AND trim(tipo)='Anual'";
$contrato = consulta_sql($SQL_contrato);
if (count($contrato) > 0) {
	echo(msje_js("Este alumno ya tiene un contrato Anual emitido para este periodo. "
	            ."Revise los documentos existentes en el módulo <Documentos de Matrícula>.\\n\\n"));
	echo(js("location.href='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
	exit;
}

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
extract($alumno[0]);

$SQL_aranceles = "SELECT carrera,jornada,round(monto_matricula/2) AS monto_matricula,
                         round(monto_arancel/2)::int4 AS monto_arancel,
                         round(monto_arancel_credito/2)::int4 AS monto_arancel_credito,round(cuotas/2) AS cuotas
                  FROM vista_aranceles 
                  WHERE id_carrera=$id_carrera AND id_jornada='$id_jornada' AND ano=$ANO_MATRICULA and semestre=1;";
//echo($SQL_aranceles);
/*                  
$SQL_aranceles = "SELECT CASE WHEN congelado THEN arancel ELSE round(arancel*1.02) END AS monto_arancel,
                         CASE WHEN congelado THEN arancel ELSE round(arancel*1.02) END AS monto_arancel_credito,
                         CASE WHEN congelado THEN beca    ELSE round(beca*1.02)    END AS beca,
                         90000 AS monto_matricula,11 AS cuotas,ci.monto AS monto_cred_int 
                  FROM finanzas.contratos_al_2009
                  LEFT JOIN finanzas.cred_int_al_2009 AS ci USING (id_alumno) 
                  WHERE id_alumno=$id_alumno;";
*/                  
$aranceles = consulta_sql($SQL_aranceles);

if ($_REQUEST['guardar'] == "Guardar") {
	
	if ($_REQUEST['financiamiento'] == 'CREDITO') {
		$_REQUEST['monto_arancel'] = $aranceles[0]['monto_arancel_credito'];
	} elseif ($_REQUEST['financiamiento'] == 'CONTADO') {
		$_REQUEST['monto_arancel'] = $aranceles[0]['monto_arancel'];
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

	$aCampos = array('id_alumno','id_aval','id_carrera','jornada','id_beca_externa','nivel',
	                 'monto_matricula','monto_arancel',
	                 'cod_beca_mat','monto_beca_mat','porc_beca_mat',
	                 'id_convenio','id_beca_arancel','monto_beca_arancel','porc_beca_arancel',
	                 'financiamiento',
	                 'mat_efectivo','mat_cheque','mat_cant_cheques','mat_tarj_cred','mat_cant_tarj_cred',
	                 'arancel_efectivo',
	                 'arancel_cheque','arancel_cant_cheques','arancel_diap_cheque','arancel_mes_ini_cheque','arancel_ano_ini_cheque',
	                 'arancel_pagare_coleg','arancel_cuotas_pagare_coleg','arancel_diap_pagare_coleg','arancel_mes_ini_pagare_coleg',
	                 'arancel_cred_interno',
	                 'arancel_tarjeta_credito','arancel_cant_tarj_credito',
	                 'ano','semestre');

	foreach ($_REQUEST AS $campo => $valor) {
		if (substr($campo,0,4) == "mat_" || substr($campo,0,8) == "arancel_" || substr($campo,0,6) == "monto_") {
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

		if ($_REQUEST['arancel_cred_interno'] > 0) {
			$uf = consulta_sql("SELECT valor FROM finanzas.valor_uf WHERE fecha=now()::date;");
			if (count($uf) == 1) {
				setlocale(LC_ALL,'C');
				$uf_valor = intval($uf[0]['valor']);
				$monto    = floatval(round((intval($_REQUEST['arancel_cred_interno']) / $uf_valor),2));
				$monto   += $aranceles[0]['monto_cred_int']; 
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
		
		if (is_numeric($id_contrato)) {
			echo(js("window.open('contrato.php?id_contrato=$id_contrato&tipo=al_antiguo');"));
		}

		if (is_numeric($id_pagare_colegiatura)) {
			echo(js("window.open('pagare_colegiatura.php?id_pagare_colegiatura=$id_pagare_colegiatura');"));
		}

		if (is_numeric($id_pagare_cred_interno)) {
			echo(js("window.open('pagare_cred_interno.php?id_pagare_cred_interno=$id_pagare_cred_interno');"));
		}
		
		echo(js("location.href='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
		exit;
	}
} 

$convenios      = consulta_sql("SELECT id,nombre||' ('||porcentaje||'%)' AS nombre from convenios ORDER BY nombre;");
$becas          = consulta_sql("SELECT id,nombre from becas;");
$becas_externas = consulta_sql("SELECT id,nombre from finanzas.becas_externas;");

$CUOTAS = array();
for ($x=1;$x<=12;$x++) { $CUOTAS = array_merge($CUOTAS,array(array("id"=>$x,"nombre"=>$x))); } 

$dias_pago = array(array("id"=>5 ,"nombre"=>"5"),
                   array("id"=>15,"nombre"=>"15"),
                   array("id"=>30,"nombre"=>"30"));
                   
$anos_ini = array(array("id"=>2009,"nombre"=>"2009"),
                  array("id"=>2010,"nombre"=>"2010"));

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
<form name="formulario" action="principal.php" method="post" onSubmit="if ((!verif_matric_finan()) || (!verif_arancel_finan())) { return false; }">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_alumno" value="<?php echo($id_alumno); ?>">
<input type="hidden" name="id_carrera" value="<?php echo($alumno[0]['id_carrera']); ?>">
<input type="hidden" name="jornada" value="<?php echo($alumno[0]['id_jornada']); ?>">
<input type="hidden" name="id_aval" value="<?php echo($id_aval); ?>">
<input type="hidden" name="monto_matricula" value="<?php echo($aranceles[0]['monto_matricula']); ?>">
<input type="hidden" name="monto_arancel" value="<?php echo($aranceles[0]['monto_arancel']); ?>">
<input type="hidden" name="monto_arancel_credito" value="<?php echo($aranceles[0]['monto_arancel_credito']); ?>">
<input type="hidden" name="ano" value="2010">
<input type="hidden" name="semestre" value="1">

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
    <td class='celdaNombreAttr'>Beca Externa:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name='id_beca_externa'>
        <option value=''>-- Ninguna --</option>
        <?php echo(select($becas_externas,$_REQUEST['id_beca_externa'])); ?>        
      </select>
      <sub>(Esta beca no genera descuentos ni es una forma de pago)</sub>
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
    <td class='celdaValorAttr' colspan="4">
      <?php echo($alumno[0]['carrera']); ?> <b>Jornada:</b> <?php echo($alumno[0]['jornada']); ?> en el
      <select name="nivel">
        <?php echo(select($NIVELES,$_REQUEST['nivel'])); ?>
      </select> nivel
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center">Valores <sub>(Actuliazados del 2009)</sub></td>
  </tr>
  <tr>    
    <td class='celdaValorAttr' colspan="4" align='center'>
      <b>Matrícula:</b> $<?php echo(number_format($aranceles[0]['monto_matricula'],0,',','.')); ?>&nbsp;&nbsp;&nbsp;
      <b>Arancel contado:</b> $<?php echo(number_format($aranceles[0]['monto_arancel'],0,',','.')); ?>&nbsp;&nbsp;&nbsp;
      <b>Arancel crédito:</b> $<?php echo(number_format($aranceles[0]['monto_arancel_credito'],0,',','.')); ?><br>
      <b>Beca:</b> $<?php echo(number_format($aranceles[0]['beca'],0,',','.')); ?>
      <b>Crédito Interno (acumulado):</b> UF <?php echo(number_format($aranceles[0]['monto_cred_int'],2,',','.')); ?>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center">Descuentos (Matrícula y/o Arancel)</td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'><u>Beca (Matrícula):</u></td>
    <td class='celdaValorAttr' colspan="3">
      <select name='cod_beca_mat' onChange="becas_mat(this.value);">
        <option value=''>-- Ninguna --</option>
        <?php echo(select($becas_mat,$_REQUEST['cod_beca_mat'])); ?>        
      </select>
      Monto: $<input type='text' class='montos' size='10' name='monto_beca_mat' value="<?php echo($_REQUEST['monto_beca_mat']); ?>" 
                     onKeyUp="puntitos(this,this.value.charAt(this.value.length-1),'monto_beca_mat')"
                     onBlur="if (this.value != '') { formulario.porc_beca_mat.value=''; } calc_matric();" disabled> ó
      Porcentaje: <input type='text' class="montos" size="2" name='porc_beca_mat' value="<?php echo($_REQUEST['porc_beca_mat']); ?>" 
                         onBlur="if (this.value != '') { formulario.monto_beca_mat.value=''; } calc_matric();" disabled>%
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Convenio (Arancel):</u></td>
    <td class='celdaValorAttr' colspan="3">
      <select name='id_convenio' onChange="convenio(this.value);">
        <option value=''>-- Ninguno --</option>
			<?php echo(select($convenios,$_REQUEST['id_convenio'])); ?>        
      </select>    
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Beca (Arancel):</u></td>
    <td class='celdaValorAttr' colspan="3">
      <select name='id_beca_arancel' onChange="becas(this.value);">
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
      Financiamiento:
      <select name='financiamiento' onChange="finan()">
			<?php echo(select($financiamientos,$_REQUEST['financiamiento'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="2" style="text-align: center">Matrícula</td>
    <td class='celdaNombreAttr' colspan="2" style="text-align: center">Arancel</td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'><u>Monto a Financiar:</u></td>
    <td class='celdaValorAttr'>
      $<input type="text" size="10"style="border: none" name="matric_finan" value="0" readonly>
      <input type="button" size="5" value="Calcular" onClick="calc_matric();"
    </td>
    <td class='celdaNombreAttr'><u>Monto a Financiar:</u></td>
    <td class='celdaValorAttr'>
      $<input type="text" size="10" style="border: none" name="arancel_finan" value="0" readonly>
      <input type="button" size="5" name="calcular" value="Calcular" onClick="calc_arancel();"
    </td>
  </tr>

<script>
function calc_matric() {
	var matric_rebajada = formulario.monto_matricula.value,monto_beca=0;
	
	if (formulario.cod_beca_mat.value != '') {
		if (formulario.monto_beca_mat.value != '' && formulario.porc_beca_mat.value == '') {
			monto_beca_mat = formulario.monto_beca_mat.value;
			matric_rebajada = matric_rebajada - monto_beca_mat.replace('.','');
		}
		if (formulario.monto_beca_mat.value == '' && formulario.porcentaje_beca_mat.value != '') {				
			matric_rebajada = matric_rebajada - Math.round(formulario.monto_matricula.value * (formulario.porcentaje_beca_mat.value/100));
		}
	}
	formulario.matric_finan.value = matric_rebajada;
}

function calc_arancel() {
	var arancel_rebajado = 0,monto_beca=0;
	
	if (formulario.financiamiento.value == "CREDITO") {
		arancel_rebajado = formulario.monto_arancel_credito.value;
	} else {
		arancel_rebajado = formulario.monto_arancel.value;
	}
		
	if (formulario.id_convenio.value != '') {
		arancel_rebajado = arancel_rebajado - Math.round(arancel_rebajado * 0.2);
	} else {
		if (formulario.id_beca_arancel.value != '') {
			if (formulario.monto_beca_arancel.value != '' && formulario.porc_beca_arancel.value == '') {
				monto_beca_arancel = formulario.monto_beca_arancel.value;
				arancel_rebajado = arancel_rebajado - monto_beca_arancel.replace('.','').replace('.','');
			}
			if (formulario.monto_beca_arancel.value == '' && formulario.porc_beca_arancel.value != '') {				
				arancel_rebajado = arancel_rebajado - Math.round(arancel_rebajado * (formulario.porc_beca_arancel.value/100));
			}
		}
	}
	formulario.arancel_finan.value = arancel_rebajado;
}

function convenio(id_convenio) {
	if (id_convenio != '') {
		formulario.id_beca_arancel.disabled=true;
		formulario.monto_beca_arancel.disabled=true;
		formulario.porc_beca_arancel.disabled=true;
	} else {
		formulario.id_beca_arancel.disabled=false;
//		formulario.monto_beca_arancel.disabled=false;
//		formulario.porc_beca_arancel.disabled=false;
	}
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

function becas_mat(cod_beca_mat) {
	if (cod_beca_mat != '') {
		formulario.monto_beca_mat.disabled=false;
		formulario.porc_beca_mat.disabled=false;
	} else {
		formulario.monto_beca_mat.disabled=true;
		formulario.monto_beca_mat.value=null;
		formulario.porc_beca_mat.disabled=true;
		formulario.porc_beca_mat.value=null;
	}
}	

function finan() {
	formulario.matric_finan.value = 0;
	formulario.arancel_finan.value = 0;
	calc_matric();
	calc_arancel();
}	

function verif_matric_finan() {
	var total_matric_finan=0;
	var mat_efectivo  = formulario.mat_efectivo.value,
	    mat_cheque    = formulario.mat_cheque.value,
	    mat_tarj_cred = formulario.mat_tarj_cred.value
	    diferencia    = 0;
	    
	total_matric_finan = mat_efectivo.replace('.','')*1 +
	                     mat_cheque.replace('.','')*1 +
	                     mat_tarj_cred.replace('.','')*1;
	
	diferencia = formulario.matric_finan.value - total_matric_finan;
	 
	if (total_matric_finan == formulario.matric_finan.value) {
		return true;
	} else {
		alert("El monto a financiar de la matrícula no está completo o hay un error lógico. Actualmente los valores ingresados suman $"+total_matric_finan+" restando financiar un monto de $"+diferencia);
		return false;
	}
}

function verif_arancel_finan() {
	var total_arancel_finan=0;
	var arancel_efectivo        = formulario.arancel_efectivo.value,
	    arancel_cheque          = formulario.arancel_cheque.value,
	    arancel_pagare_coleg    = formulario.arancel_pagare_coleg.value,
	    arancel_cred_interno    = formulario.arancel_cred_interno.value,
	    arancel_tarjeta_credito = formulario.arancel_tarjeta_credito.value,
	    diferencia              = 0;
	    
	total_arancel_finan = arancel_efectivo.replace('.','').replace('.','')*1 +
	                      arancel_cheque.replace('.','').replace('.','')*1 +
	                      arancel_pagare_coleg.replace('.','').replace('.','')*1 +
	                      arancel_cred_interno.replace('.','').replace('.','')*1 +
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
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='mat_efectivo' value="<?php echo($_REQUEST['mat_efectivo']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
    </td>
    <td class='celdaNombreAttr'>Efectivo:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='arancel_efectivo' value="<?php echo($_REQUEST['arancel_efectivo']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cheque(s):</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='mat_cheque' value="<?php echo($_REQUEST['mat_cheque']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
      Cant.: <input type='text' size='2' name='mat_cant_cheques' value="<?php echo($_REQUEST['mat_cant_cheques']); ?>">
    </td>  
    <td class='celdaNombreAttr'>Cheque(s):</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='arancel_cheque' value="<?php echo($_REQUEST['arancel_cheque']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
      <sub>
        Cant.: <select name='arancel_cant_cheques'><?php echo(select($CUOTAS,$_REQUEST['arancel_cant_cheques'])); ?></select><br>
        Día venc.: <select name='arancel_diap_cheque'><?php echo(select($dias_pago,$_REQUEST['arancel_diap_cheque'])); ?></select>
        Mes inicio: <select name="arancel_mes_ini_cheque"><?php echo(select($meses_palabra,$_REQUEST['arancel_mes_ini_cheque'])); ?></select>
        Año inicio: <select name="arancel_ano_ini_cheque"><?php echo(select($anos_ini,$_REQUEST['arancel_ano_ini_cheque'])); ?></select>
      </sub>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tarjetas de Crédito:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='mat_tarj_cred' value="<?php echo($_REQUEST['mat_tarj_cred']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
      Cant.: <input type='text' size='2' name='mat_cant_tarj_cred' value="<?php echo($_REQUEST['mat_cant_tarj_cred']); ?>">
    </td>
    <td class='celdaNombreAttr'>Pagaré Colegiatura:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='arancel_pagare_coleg' value="<?php echo($_REQUEST['arancel_pagare_coleg']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
      <sub>
        Cuotas: <select name='arancel_cuotas_pagare_coleg'><?php echo(select($CUOTAS,$_REQUEST['arancel_cuotas_pagare_coleg'])); ?></select><br>
        Día pago: <select name='arancel_diap_pagare_coleg'><?php echo(select($dias_pago,$_REQUEST['arancel_diap_pagare_coleg'])); ?></select>
        Mes inicio: <select name="arancel_mes_ini_pagare_coleg"><?php echo(select($meses_palabra,$_REQUEST['arancel_mes_ini_pagare_coleg'])); ?></select>
      </sub>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td class='celdaNombreAttr'>Crédito Interno:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='arancel_cred_interno' value="<?php echo($_REQUEST['arancel_cred_interno']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">                      
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td class='celdaNombreAttr'>Tarjetas de Cŕédito:</td>
    <td class='celdaValorAttr'>
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
