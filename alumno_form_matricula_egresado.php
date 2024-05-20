<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$id_alumno          = $_REQUEST['id_alumno'];
$tipo_contrato      = $_REQUEST['tipo'];
$ano_contrato       = $_REQUEST['ano'];
$semestre_contrato  = $_REQUEST['semestre'];

if (empty($ano_contrato))      { $ano_contrato      = $ANO; }
if (empty($semestre_contrato)) { $semestre_contrato = $SEMESTRE; }

if (!is_numeric($id_alumno)) {
	echo(js("location.href='$enlbase=gestion_alumnos';"));
	exit;
}

$SQL_contrato = "SELECT id FROM finanzas.contratos 
                 WHERE id_alumno=$id_alumno AND ano=$ANO_MATRICULA AND estado IN ('E','F') AND trim(tipo)='Egresado'";
$contrato = consulta_sql($SQL_contrato);
if (count($contrato) > 0) {
	echo(msje_js("Este alumno ya tiene un contrato Egresado emitido para este periodo. "
	            ."Revise los documentos existentes en el módulo <Documentos de Matrícula>."));
	echo(js("location.href='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
	exit;
}

$monto_mat_carrera = consulta_sql("SELECT monto_matricula FROM aranceles WHERE ano=$ANO_MATRICULA AND id_carrera=(SELECT carrera_actual FROM alumnos WHERE id=$id_alumno)");
//$MONTO_MATRICULA = 180000;
$MONTO_MATRICULA = round($monto_mat_carrera[0]['monto_matricula']*1.5,0);

$SQL_alumno = "SELECT va.id,va.rut,va.nombre,va.direccion,va.comuna,va.region,
                      al.genero AS cod_genero,al.nacionalidad AS cod_nac,al.comuna AS cod_comuna,
                      al.region AS cod_region,al.email,al.pasaporte,al.telefono,al.tel_movil,al.id_aval,
                      c.nombre AS carrera,al.carrera_actual AS id_carrera,
                      CASE al.jornada WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada,
                      al.jornada AS id_jornada
               FROM alumnos AS al
               LEFT JOIN vista_alumnos AS va USING (id)
               LEFT JOIN carreras AS c ON c.id=carrera_actual
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

	$aCampos = array('id_alumno','id_carrera','jornada','tipo',
	                 'monto_matricula','cod_beca_mat','monto_beca_mat','porc_beca_mat',
	                 'financiamiento',
	                 'mat_efectivo','mat_tarj_cred','mat_cant_tarj_cred',
	                 'ano','semestre');

	foreach ($_REQUEST AS $campo => $valor) {
		if (substr($campo,0,4) == "mat_" || substr($campo,0,6) == "monto_") {
			$_REQUEST[$campo] = str_replace(".","",$valor);
		}
	}
	
	$SQLinsert_contrato = "INSERT INTO finanzas.contratos " . arr2sqlinsert($_REQUEST,$aCampos) .";"
	                    . "SELECT currval('finanzas.contratos_id_seq') AS id";
	$contrato = consulta_sql($SQLinsert_contrato);

	if (count($contrato) == 1) {
		$id_contrato = $contrato[0]['id'];
		echo(msje_js("Se ha guardado exitosamente el contrato"));

		$diap        = strftime("%d");
		$mesp        = strftime("%m");
		$anop        = strftime("%Y");	

		if ($_REQUEST['mat_efectivo'] > 0 || $_REQUEST['mat_tarj_cred'] > 0) {
			$id_glosa    = 1; // Matricula
			$cant_cuotas = 1;
			if ($_REQUEST['mat_efectivo'] > 0) {
				$monto_total = $_REQUEST['mat_efectivo'];
			}			
			elseif ($_REQUEST['mat_tarj_cred'] > 0) {
				$monto_total += $_REQUEST['mat_tarj_cred'];
			}
			$monto_cuota = intval($monto_total/$cant_cuotas);
			$SQL_cobros  = generar_cobros($id_contrato,$id_glosa,$cant_cuotas,$monto_cuota,$monto_total,$diap,$mesp,$anop);
			consulta_sql($SQL_cobros);
		}
		
		if (is_numeric($id_contrato)) {
			echo(js("window.open('contrato.php?id_contrato=$id_contrato&tipo=al_egresado');"));
		}

		echo(js("location.href='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
		exit;
	}
} 


?>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>
<br>
<form name="formulario" action="principal.php" method="post" onSubmit="if (!verif_matric_finan()) { return false; }">
<input type="hidden" name="modulo"          value="<?php echo($modulo); ?>">
<input type="hidden" name="id_alumno"       value="<?php echo($id_alumno); ?>">
<input type="hidden" name="id_carrera"      value="<?php echo($alumno[0]['id_carrera']); ?>">
<input type="hidden" name="jornada"         value="<?php echo($alumno[0]['id_jornada']); ?>">
<input type="hidden" name="monto_matricula" value="<?php echo($MONTO_MATRICULA); ?>">
<input type="hidden" name="ano"             value="<?php echo($ano_contrato); ?> ">
<input type="hidden" name="semestre"        value="<?php echo($semestre_contrato); ?>">
<input type="hidden" name="tipo"            value="<?php echo($tipo_contrato); ?>">
<input type="hidden" name="financiamiento"  value="CONTADO">

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
    <td class='celdaNombreAttr' colspan="4" style="text-align: center">Carrera en que se Matricula</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr' colspan="3">
      <?php echo($alumno[0]['carrera']); ?> <b>Jornada:</b> <?php echo($alumno[0]['jornada']); ?> 
    </td>
  </tr>

  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center">Valores</td>
  </tr>
  <tr>    
    <td class='celdaValorAttr' colspan="4" align='center'>
       <b>Matrícula:</b> $<?php echo(number_format($MONTO_MATRICULA,0,',','.')); ?>&nbsp;&nbsp;&nbsp;
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center">Descuentos de Arancel</td>
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
    <td class='celdaNombreAttr' colspan="4" style="text-align: center">
      Financiamiento Matrícula
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Monto a Financiar:</u></td>
    <td class='celdaValorAttr' colspan="3">
      $<input type="text" size="10"style="border: none" name="matric_finan" value="0" readonly>
      <input type="button" size="5" value="Calcular" onClick="calc_matric();"
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
	calc_matric();
}	

function verif_matric_finan() {
	var total_matric_finan=0;
	var mat_efectivo  = formulario.mat_efectivo.value,
	    mat_tarj_cred = formulario.mat_tarj_cred.value
	    diferencia    = 0;
	    
	total_matric_finan = mat_efectivo.replace('.','')*1 +
	                     mat_tarj_cred.replace('.','')*1;
	
	diferencia = formulario.matric_finan.value - total_matric_finan;
	 
	if (total_matric_finan == formulario.matric_finan.value) {
		return true;
	} else {
		alert("El monto a financiar de la matrícula no está completo o hay un error lógico. Actualmente los valores ingresados suman $"+total_matric_finan+" restando financiar un monto de $"+diferencia);
		return false;
	}
}

finan();

</script>  

  <tr>
    <td class='celdaNombreAttr'>Efectivo:</td>
    <td class='celdaValorAttr' colspan="3">
      $<input type='text' class='montos' size='10' name='mat_efectivo' value="<?php echo($_REQUEST['mat_efectivo']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tarjetas de Cŕédito:</td>
    <td class='celdaValorAttr' colspan="3">
      $<input type='text' class='montos' size='10' name='mat_tarj_cred' value="<?php echo($_REQUEST['mat_tarj_cred']); ?>"
              onkeyup="puntitos(this,this.value.charAt(this.value.length-1),this.name)">
      Cant.: <input type='text' size='2' name='mat_cant_tarj_cred' value="<?php echo($_REQUEST['mat_cant_tarj_cred']); ?>">
    </td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="4" align="center">
      <b>Cant:</b> Se refiere a Cantidad de Cheques o Cantidad de Cuotas de Tarjetas
    </td>
  </tr>
</table>

</form>
