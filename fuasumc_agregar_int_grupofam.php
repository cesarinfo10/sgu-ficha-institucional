<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");
if (isset($_SESSION['tipo'])) { 
	include("validar_modulo.php"); 
} else { 
	$datos_modulo = modulos($modulo); 
	$nombre_modulo = $datos_modulo['nombre'];
}

$id_fuas    = $_REQUEST['id_fuas'];
$id_gf      = $_REQUEST['id_gf'];
$id_alumno  = $_REQUEST['id_alumno'];
$forma      = $_REQUEST['forma'];

if ($_REQUEST['guardar'] == "Guardar") {
	$_REQUEST['ing_liq_mensual_prom'] = str_replace(".","",$_REQUEST['ing_liq_mensual_prom']);
	$aCampos = array('id_fuas','rut','parentesco','apellidos','nombres',
	                 'jefe_hogar','fecha_nacimiento','enfermo_cronico','nombre_enfermedad',
	                 'nivel_educ','cat_ocupacional','ing_liq_mensual_prom');
	                 
	if ($forma == "crear") {
		$SQLinsert = "INSERT INTO dae.fuas_grupo_familiar " . arr2sqlinsert($_REQUEST,$aCampos);
		//echo($SQLinsert);
		if (consulta_dml($SQLinsert) == 1) {
			echo(msje_js("Se ha registrado con éxito el integrante del grupo familiar.\\n\\n"
						."A continuación, debe subir los respaldos de Ingreso Líquido "
						."(certificados de cotizaciones, certificado de censantía, "
						."carpeta tributaria, etc.)"));
			echo(js("parent.jQuery.fancybox.close()"));
			exit;
		} else {
			echo(msje_js("ERROR: El formulario no pudo guardarse.\\n\\n"
						."Es posible que el integrante ya este previamente registrado en esta postulación."));
		}
	}
	
	if ($forma == "editar") {
		$SQLupd = "UPDATE dae.fuas_grupo_familiar SET " . arr2sqlupdate($_REQUEST,$aCampos) . " WHERE id=$id_gf;";
		if (consulta_dml($SQLupd) == 1) {
			echo(msje_js("Se han guardado con éxito las modificaciones del integrante del grupo familiar."));
			echo(js("parent.jQuery.fancybox.close()"));
			exit;
		} else {
			echo(msje_js("ERROR: El formulario no pudo guardarse.\\n\\n"
						."Es posible que el integrante ya este previamente registrado en esta postulación (según su RUT)."));
		}
	}

}

if ($forma == "editar" && !empty($id_gf)) {
	$fuas_gf = consulta_sql("SELECT * FROM dae.fuas_grupo_familiar WHERE id=$id_gf");
	$_REQUEST = array_merge($_REQUEST,$fuas_gf[0]);
}

$PARENTESCOS         = consulta_sql("SELECT * FROM vista_parentescos");
$NIVEL_ESTUDIOS      = consulta_sql("SELECT * FROM dae.nivel_estudios");
$ACTIVIDADES         = consulta_sql("SELECT * FROM dae.actividades");
?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<form name="formulario" action="principal_sm.php" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_fuas" value="<?php echo($id_fuas); ?>">
<input type="hidden" name="id_gf" value="<?php echo($id_gf); ?>">
<input type="hidden" name="forma" value="<?php echo($forma); ?>">

<div style='margin-top: 5px'>
  <input type='submit' name='guardar' value='Guardar' tabindex='99' onClick="return valida_rut(formulario.rut);">
  <input type="button" name="cancelar" value="Cancelar" onclick="history.back();">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Integrante Familiar</td></tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><input type='text' size='10' name='rut' class='boton' value="<?php echo($_REQUEST['rut']); ?>" required></td>
    <td class='celdaNombreAttr'>Parentesco:</td>
    <td class='celdaValorAttr'><select name="parentesco" class='filtro' required><option value="">-- Seleccione --</option><?php echo(select($PARENTESCOS,$_REQUEST['parentesco'])); ?></select></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Apellidos:</td>
    <td class='celdaValorAttr'><input type='text' size='25' name='apellidos' class='boton' value="<?php echo($_REQUEST['apellidos']); ?>" onBlur="var valor=this.value;this.value=valor.toUpperCase();" required></td>
    <td class='celdaNombreAttr'>Nombres:</td>
    <td class='celdaValorAttr'><input type='text' size='25' name='nombres' class='boton' value="<?php echo($_REQUEST['nombres']); ?>" onBlur="var valor=this.value;this.value=valor.toUpperCase();" required></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Jefe de Hogar?:</td>
    <td class='celdaValorAttr'><select name="jefe_hogar" class='filtro' required><option value=''>-- Seleccione --</option><?php echo(select($sino,$_REQUEST['jefe_hogar'])); ?></select></td>
    <td class='celdaNombreAttr'>F. Nacimiento:</td>
    <td class='celdaValorAttr'><input type='date' name='fecha_nacimiento' class='boton' value="<?php echo($_REQUEST['fecha_nacimiento']); ?>" required></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Enfermo Crónico?:</u></td>
    <td class='celdaValorAttr'><select name='enfermo_cronico' class='filtro' onChange="acciones_enfermo_cronico(this.value);" required><option value=''>-- Seleccione --</option><?php echo(select($sino,$_REQUEST['enfermo_cronico'])); ?></select></td>
    <td class='celdaNombreAttr'><u>Nombre Enfermedad:</u></td>
    <td class='celdaValorAttr'><input type='text' size="20" name='nombre_enfermedad' value="<?php echo($_REQUEST['nombre_enfermedad']); ?>" class='boton' onBlur="var valor=this.value;this.value=valor.toUpperCase();" disabled></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan='2'><u>Nivel Educacional:</u></td>
    <td class='celdaValorAttr' colspan='2'><select name='nivel_educ' class='filtro' required><option value=''>-- Seleccione --</option><?php echo(select($NIVEL_ESTUDIOS,$_REQUEST['nivel_educ'])); ?></select></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan='2'><u>Categoría Ocupacional:</u></td>
    <td class='celdaValorAttr' colspan='2'><select name='cat_ocupacional' class='filtro' required><option value=''>-- Seleccione --</option><?php echo(select($ACTIVIDADES,$_REQUEST['cat_ocupacional'])); ?></select></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='vertical-align: top' colspan='2'><u>Ingreso Líquido Mensual Promedio:</u></td>
    <td class='celdaValorAttr' colspan='2'>
      $<input type='text' size="9" name='ing_liq_mensual_prom' value="<?php echo($_REQUEST['ing_liq_mensual_prom']); ?>" class='montos' onKeyUp="puntitos(this,this.value.charAt(this.value.length-1),this.name);" required><br>
      <small><br>Una vez que complete este formulario, podrá adjuntar los documentos<br>que respalden este Ingreso Mensual</small>
    </td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="4">
      <blockquote><blockquote><blockquote>
	  <small>
		Un jefe de hogar debe justificar sus ingresos con uno de los siguientes documentos de respaldo:<br>
		<br>
	    - Certificado de Cotizaciones de AFP (para trabajadores dependientes o contratados)<br>
	    - Carpeta Tributaria (para trabajadores independientes)<br>
	    - Certificado de Pensiones (para jubilados, pensionados o montepiados)<br>
	    - Certificado de Cesantía (para mayores de 18 años que no tengan empleo)<br>
	    <br>
	    NOTA: En un grupo familiar pueden existir más de un jefe de hogar.
	  </small>
	  </blockquote></blockquote></blockquote>
    </td>
  </tr>
</table>
</form>

<script language="Javascript">
acciones_enfermo_cronico('<?php echo($_REQUEST['enfermo_cronico']) ?>');

function acciones_enfermo_cronico(valor) {
	if (valor == 't') { 
		formulario.nombre_enfermedad.required=true;
		formulario.nombre_enfermedad.disabled=false;
	} else {
		formulario.nombre_enfermedad.required=false;
		formulario.nombre_enfermedad.disabled=true;
	}
}

function acciones_pert_pueblo_orig(valor) {
	
	if (valor != 'Ninguno') { 
		formulario.acred_pert_pueblo_orig.required=true;
		formulario.acred_pert_pueblo_orig.disabled=false;
	} else {
		formulario.acred_pert_pueblo_orig.required=false;
		formulario.acred_pert_pueblo_orig.disabled=true;
	}
}

//puntitos(document.formulario.total_pago,document.formulario.total_pago.value.charAt(document.formulario.total_pago.value.length-1),document.formulario.total_pago.name);


</script>
