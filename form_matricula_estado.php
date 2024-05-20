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
                        to_char(c.fecha,'DD-MM-YYYY') AS fecha,monto_condonacion,coalesce(c.estado,'') as estado,morosidad_manual,
                        c.id_beca_externa,c.finan_ext_monto
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

if ($_REQUEST['guardar'] == "Guardar") {
	$estado           = $_REQUEST['estado'];
	$morosidad_manual = $_REQUEST['morosidad_manual'];
	$id_beca_externa  = $_REQUEST['id_beca_externa'];
	$finan_ext_monto  = intval(str_replace(".","",$_REQUEST['finan_ext_monto']));

	if ($estado <> $contrato[0]['estado']) {
		if ($estado == "") { $estado = "null"; } else { $estado = "'$estado'"; }
		$SQL_contrato_upd = "UPDATE finanzas.contratos SET estado=$estado,fec_mod_estado=now(),estado_fecha=now(),estado_id_usuario={$_SESSION['id_usuario']} WHERE id=$id_contrato;";
	}
	if ($morosidad_manual <> $contrato[0]['morosidad_manual']) {
		$SQL_contrato_upd .= "UPDATE finanzas.contratos SET morosidad_manual='$morosidad_manual' WHERE id=$id_contrato";
	}

  if ($id_beca_externa <> $contrato[0]['id_beca_externa']) {
    if ($id_beca_externa == "") { $id_beca_externa = "null"; }
		$SQL_contrato_upd .= "UPDATE finanzas.contratos SET id_beca_externa=$id_beca_externa,finan_ext_monto=$finan_ext_monto WHERE id=$id_contrato";
  }
  
  if ($id_beca_externa <> $contrato[0]['id_beca_externa']) {
    if ($id_beca_externa == "") { $id_beca_externa = "null"; }
		$SQL_contrato_upd .= "UPDATE finanzas.contratos SET id_beca_externa=$id_beca_externa,finan_ext_monto=$finan_ext_monto WHERE id=$id_contrato";
  }
	
	if (consulta_dml($SQL_contrato_upd) > 0) {
		echo(msje_js("Se ha guardado exitósamente los cambios."));
		if ($estado <> "null" || $estado <> "'E'") {
			$url_si = "$enlbase_sm=contrato_condonar&id_contrato=$id_contrato";
			$url_no = "#";
			echo(confirma_js("Desea condonar este contrato?",$url_si,$url_no));
		}
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}
}

if ($_REQUEST['estado'] == "")           { $_REQUEST['estado']           = $contrato[0]['estado']; }
if ($_REQUEST['morosidad_manual'] == "") { $_REQUEST['morosidad_manual'] = $contrato[0]['morosidad_manual']; }
if ($_REQUEST['id_beca_externa'] == "")  { $_REQUEST['id_beca_externa']  = $contrato[0]['id_beca_externa']; }
if ($_REQUEST['finan_ext_monto'] == "")  { $_REQUEST['finan_ext_monto']  = $contrato[0]['finan_ext_monto']; }

$estados_contrato = array(array("id"=>'E',"nombre"=>'Emitido'),
                          array("id"=>'R',"nombre"=>'Retirado'),
                          array("id"=>'S',"nombre"=>'Suspendido'),
                          array("id"=>'A',"nombre"=>'Abandonado'),
                          array("id"=>'Z',"nombre"=>'Reemplazado'),
                          array("id"=>'' ,"nombre"=>'Nulo'));

$BECAS_EXTERNAS = consulta_sql("SELECT id,nombre FROM finanzas.becas_externas");
?>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal_sm.php" method="get">
<input type="hidden" name="modulo"           value="<?php echo($modulo); ?>">
<input type="hidden" name="id_contrato"      value="<?php echo($id_contrato); ?>">

<div style="margin-top: 5px">
  <input type="submit" name="guardar" value="Guardar">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style="margin-top: 5px">
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
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Estado del Contrato</td></tr>
  <tr>
    <td class='celdaNombreAttr'><u>Estado:</u></td>
    <td class='celdaValorAttr'>
      <select name='estado' class='filtro'>
        <?php echo(select($estados_contrato,$_REQUEST['estado'])); ?>
      </select>
    </td>
    <td class='celdaNombreAttr'><u>Morosidad manual:</u></td>
    <td class='celdaValorAttr'>
      <select name='morosidad_manual' class='filtro'>
        <?php echo(select($sino,$_REQUEST['morosidad_manual'])); ?>
      </select>
    </td>
  </tr>
  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Beneficios Fiscales</td></tr>

  <tr>
    <td class='celdaNombreAttr'>Beca:</td>
    <td class='celdaValorAttr'>
      <select name='id_beca_externa' class='filtro' onChange="formulario.finan_ext_monto.required=(this.value != ''); if(this.value==''){formulario.finan_ext_monto.value='';}" >
        <option value="">-- Sin Beca --</option>
        <?php echo(select($BECAS_EXTERNAS,$_REQUEST['id_beca_externa'])); ?>
      </select>
    </td>
    <td class='celdaNombreAttr'>Monto:</td>
    <td class='celdaValorAttr'><input type='text' size="6" name="finan_ext_monto" onKeyUp="js_number_format(this);" value="<?php echo(number_format($contrato[0]['finan_ext_monto'],0,",",".")); ?>" class="montos"></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>CAE:</td>
    <td class='celdaValorAttr'>
      <select name='cae_presel' class='filtro' onChange="formulario.cae_monto.required=(this.value != ''); if(this.value==''){ formulario.cae_monto.value=''; }" >
        <option value="">-- Seleccione --</option>
        <?php echo(select($sino,$_REQUEST['cae_presel'])); ?>
      </select>
    </td>
    <td class='celdaNombreAttr'>Monto:</td>
    <td class='celdaValorAttr'><input type='text' size="6" name="cae_monto" onKeyUp="js_number_format(this);" value="<?php echo(number_format($contrato[0]['cae_monto'],0,",",".")); ?>" class="montos"></td>
  </tr>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->

<script language="Javascript1.2">

function js_number_format(campo) {
  var numero_formateado = new Intl.NumberFormat('es-CL'), 
      valor="";
  if (campo.value != '') {
    valor = parseInt(campo.value.replace(".","").replace(".","").replace(".",""));
  }
  return campo.value=numero_formateado.format(valor);
}

</script>