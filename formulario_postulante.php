<?php

if($_REQUEST['nacionalidad'] == "") { $_REQUEST['nacionalidad'] = "CL"; }
if($_REQUEST['id'] == "") { $_REQUEST['id'] = "** Una vez creado el postulante tendrá ID **"; }
if($_REQUEST['admision'] == "") { $_REQUEST['admision'] = "1"; }

$comunas        = consulta_sql("SELECT id,nombre FROM comunas ORDER BY nombre;");
$regiones       = consulta_sql("SELECT id,nombre||' ('||romano||')' AS nombre FROM regiones;");
$nacionalidades = consulta_sql("SELECT localizacion AS id,nacionalidad AS nombre FROM pais ORDER BY nacionalidad;");
$carreras       = consulta_sql("SELECT id,nombre||' ('||alias||')' AS nombre FROM carreras WHERE activa ORDER BY nombre;");
$inst_edsup     = consulta_sql("SELECT id,nombre FROM inst_edsup ORDER BY nombre;");
$convenios      = consulta_sql("SELECT id,nombre||' ('||porcentaje||'%)' AS nombre from convenios ORDER BY nombre;");
$ftesfinan      = consulta_sql("SELECT id,nombre from ftesfinan;");
$becas          = consulta_sql("SELECT id,nombre from becas;");
$creditos       = consulta_sql("SELECT id,nombre from creditos;");
$admision_subtipo = consulta_sql("SELECT id,nombre from pap_admision_subtipo;");

?>
<script language="Javascript">
	function tipo_admision(tipo) {
		if (tipo == 1) {
			formulario.id_inst_edsup_proced.disabled = true;
			formulario.carr_ies_pro.disabled = true;
			formulario.prom_nt_ies_pro.disabled = true;
			formulario.conc_nt_ies_pro.disabled = true;
			formulario.prog_as_ies_pro.disabled = true;
		} else {
			formulario.id_inst_edsup_proced.disabled = false;
			formulario.carr_ies_pro.disabled = false;
			formulario.prom_nt_ies_pro.disabled = false;
			formulario.conc_nt_ies_pro.disabled = false;
			formulario.prog_as_ies_pro.disabled = false;
		}
	}		
</script>
<form name="formulario" action="principal.php" method="get"
      onSubmit="if (!enblanco2(<?php echo($requeridos); ?>) || !val_nota('promedio_col','prom_nt_ies_pro') || !val_psu('puntaje_psu') || !valida_rut(formulario.rut.value)) { return false; }">

<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="validar" value="<?php echo($validar); ?>">
<input type="hidden" name="id_pap" value="<?php echo($id_pap); ?>">

<table class="tabla">
  <tr>
    <td class="tituloTabla">
<?php	if ($forma == 'editar') { ?>    
      <input type="submit" name="editar" value="Guardar" tabindex="99">
<?php } else { ?>      
      <input type="submit" name="crear" value="Crear" tabindex="99">
<?php } ?>      
    </td>
    <td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar" onclick="history.back();"></td>
  </tr>
</table>
<br>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla">
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Postulante</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>RUT:</u></td>
    <td class='celdaValorAttr'>
      <input type="text" size="15" name="rut" value="<?php echo($_REQUEST['rut']); ?>" <?php if($forma<>'editar'){ echo("readonly"); } ?>>
    </td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['id']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Nombres:</u></td>
    <td class='celdaValorAttr'>
      <input type='text' size="25" name='nombres' value="<?php echo($_REQUEST['nombres']); ?>" onBlur="var valor=this.value;this.value=valor.toUpperCase();">
      <script>formulario.nombres.focus();</script>
    </td>
    <td class='celdaNombreAttr'><u>Apellidos:</u></td>
    <td class='celdaValorAttr'><input type='text' size="25" name='apellidos' value="<?php echo($_REQUEST['apellidos']); ?>" onBlur="var valor=this.value;this.value=valor.toUpperCase();"></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>G&eacute;nero:</u></td>
    <td class='celdaValorAttr'>
      <select name='genero'>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($generos,$_REQUEST['genero'])); ?>        
      </select>
    </td>
    <td class='celdaNombreAttr'><u>Fecha de nacimiento:</u></td>
    <td class='celdaValorAttr'>
      <select name="fec_nac_dia">
        <option value="" style="text-align: center; ">- D&iacute;a -</option>
        <?php echo(select($dias_fn,$_REQUEST['fec_nac_dia'])); ?>
      </select>/
      <select name="fec_nac_mes">
        <option value="" style="text-align: center; ">- Mes -</option>
        <?php echo(select($meses_fn,$_REQUEST['fec_nac_mes'])); ?>
      </select>/
      <select name="fec_nac_ano">
        <option value="" style="text-align: center; ">- Año -</option>
        <?php echo(select($anos_fn,$_REQUEST['fec_nac_ano'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Nacionalidad:</u></td>
    <td class='celdaValorAttr'>
      <select name='nacionalidad' onChange="if (this.value == 'CL') { formulario.pasaporte.disabled = true; } else { formulario.pasaporte.disabled = false; }">
        <option value=''>-- Seleccione --</option>
			<?php echo(select($nacionalidades,$_REQUEST['nacionalidad'])); ?>        
      </select>
    </td>
    <td class='celdaNombreAttr'>Nro. Pasaporte:</td>
    <td class='celdaValorAttr'><input type='text' size="15" name='pasaporte' value="<?php echo($_REQUEST['pasaporte']); ?>" disabled></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Direcci&oacute;n:</u></td>
    <td class='celdaValorAttr'>
      <input type='text' size="25" name='direccion' value="<?php echo($_REQUEST['direccion']); ?>" onBlur="var valor=this.value;this.value=valor.toUpperCase();"><br>
      <sup>Av./Calle/Psje. # Villa/Población</sup>
    </td>
    <td class='celdaNombreAttr'><u>Comuna:</u></td>
    <td class='celdaValorAttr'>
      <select name='comuna'>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($comunas,$_REQUEST['comuna'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Región:</u></td>
    <td class='celdaValorAttr' colspan="3">
      <select name='region'>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($regiones,$_REQUEST['region'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Tel&eacute;fono fijo:</u></td>
    <td class='celdaValorAttr'><input type='text' size="10" name='telefono' value="<?php echo($_REQUEST['telefono']); ?>"></td>
    <td class='celdaNombreAttr'>Tel&eacute;fono m&oacute;vil:</td>
    <td class='celdaValorAttr'><input type='text' size="10" name='tel_movil' value="<?php echo($_REQUEST['tel_movil']); ?>"></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>e-mail:</td>
    <td class='celdaValorAttr' colspan="3"><input type='text' size="20" name='email' value="<?php echo($_REQUEST['email']); ?>" onKeyUp="var valor=this.value;this.value=valor.toLowerCase();"></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Postulaci&oacute;n</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Admisión:</u></td>
    <td class='celdaValorAttr'>
      <select name='admision' onChange="tipo_admision(this.value);">
        <option value=''>-- Seleccione --</option>
        <?php echo(select($ADMISION,$_REQUEST['admision'])); ?>        
      </select>

    </td>
    <td class='celdaNombreAttr'>Fecha postulaci&oacute;n:</td>
    <td class='celdaValorAttr'>
      <input type='text' name='fecha_post' value="<?php echo($_REQUEST['fecha_post']); ?>" disabled>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Carrera 1:</u></td>
    <td class='celdaValorAttr' colspan="3">
      <select name='carrera1_post'>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($carreras,$_REQUEST['carrera1_post'])); ?>        
      </select>    
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera 2:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name='carrera2_post'>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($carreras,$_REQUEST['carrera2_post'])); ?>        
      </select>    
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera 3:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name='carrera3_post'>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($carreras,$_REQUEST['carrera3_post'])); ?>        
      </select>    
    </td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="2">
      <b>Documentaci&oacute;n Requerida</b><br>
      <input type='checkbox' name='cert_nacimiento' value="t"
             <?php if ($_REQUEST['cert_nacimiento'] == "t") { echo("checked"); } ?>>
      Certificado de nacimiento<br>
      <input type='checkbox' name='conc_notas_em' value="t"
             <?php if ($_REQUEST['conc_notas_em'] == "t") { echo("checked"); } ?>>
      Concentraci&oacute;n de notas EM<br>
      <input type='checkbox' name='boletin_psu' value="t"
             <?php if ($_REQUEST['boletin_psu'] == "t") { echo("checked"); } ?>>
      Bolet&iacute;n PSU
    </td>      
    <td class='celdaValorAttr' colspan="2"><br>
      <input type='checkbox' name='copia_ced_iden' value="t"
             <?php if ($_REQUEST['copia_ced_iden'] == "t") { echo("checked"); } ?>>
      Fotocopia C&eacute;dula Nacional de Identidad<br>
      <input type='checkbox' name='licencia_em' value="t"
             <?php if ($_REQUEST['licencia_em'] == "t") { echo("checked"); } ?>>
      Licencia de Ense&ntilde;anza Media<br>
      <sup><b>
        NOTA: El postulante debe presentar el Certificado de Nacimiento (ORIGINAL)<br>
        o la Fotocopia de la Cédula Nacional de Identidad.        
      </b></sup>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">
      Antecedentes Escolares de Ense&ntilde;anza Media (EM) del Postulante
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RBD:</td>
    <td class='celdaValorAttr' colspan="4">
      <input type='text' name='rbd_colegio' size="5" value="<?php echo($_REQUEST['rbd_colegio']); ?>">
      Si no lo conoce, pinche
      <a href="javascript:;" onClick="window.open('buscar_colegio.php','Buscar Colegio','width=600,height=300,scrollbars=yes');">
        aquí
      </a>
      o consulte directamente al 
      <a href="http://www.mineduc.cl/index.php?id_portal=1&id_seccion=227&id_contenido=138" target="_blank">
        Ministerio de Educaci&oacute;n
      </a>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>A&ntilde;o Egreso EM:</td>
    <td class='celdaValorAttr'>
      <input type='text' name='ano_egreso_col' size="4" value="<?php echo($_REQUEST['ano_egreso_col']); ?>">
    </td>
    <td class='celdaNombreAttr'>Promedio EM:</td>
    <td class='celdaValorAttr'>
      <input type='text' name='promedio_col' size="5" value="<?php echo($_REQUEST['promedio_col']); ?>">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>A&ntilde;o PSU:</td>
    <td class='celdaValorAttr'>
      <input type='text' name='ano_psu' size="4" value="<?php echo($_REQUEST['ano_psu']); ?>">
    </td>
    <td class='celdaNombreAttr'>Puntaje PSU:</td>
    <td class='celdaValorAttr'>
      <input type='text' name='puntaje_psu' size="4" value="<?php echo($_REQUEST['puntaje_psu']); ?>">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">
      Antecedentes de Estudios Superiores del Postulante
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Instituci&oacute;n:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name='id_inst_edsup_proced' disabled>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($inst_edsup,$_REQUEST['id_inst_edsup_proced'])); ?>        
      </select>    
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr'>
      <input type='text' name='carr_ies_pro' size="20" value="<?php echo($_REQUEST['carr_ies_pro']); ?>" onBlur="var valor=this.value;this.value=valor.toUpperCase();" disabled>
    </td>
    <td class='celdaNombreAttr'>Promedio Notas:</td>
    <td class='celdaValorAttr'>
      <input type='text' name='prom_nt_ies_pro' size="4" value="<?php echo($_REQUEST['prom_nt_ies_pro']); ?>" disabled>
    </td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="2">
      <b>Documentaci&oacute;n Requerida (s&oacute;lo si convalida)</b><br>
      <input type='checkbox' name='conc_nt_ies_pro' value="t"
             <?php if ($_REQUEST['conc_nt_ies_pro'] == "t") { echo("checked"); } ?> disabled>
      Concentraci&oacute;n de Notas<br>
      <sup><b>Documento absolutamente OBLIGATORIO</b></sup>
    </td>      
    <td class='celdaValorAttr' colspan="2"><br>
      <input type='checkbox' name='prog_as_ies_pro' value="t"
             <?php if ($_REQUEST['prog_as_ies_pro'] == "t") { echo("checked"); } ?> disabled>
      Programas de Asignaturas APROBADAS
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">
      Antecedentes de Financiamiento
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Fuente de<br>Financiamiento:</td>
    <td class='celdaValorAttr'>
      <select name='id_fte_finan'>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($ftesfinan,$_REQUEST['id_fte_finan'])); ?>        
      </select>
    </td>
    <td class='celdaNombreAttr'>Paga Matrícula?</td>
    <td class='celdaValorAttr'>
      <select name='paga_matricula'>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($sino,$_REQUEST['paga_matricula'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Convenio:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name='id_convenio'>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($convenios,$_REQUEST['id_convenio'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Beca:</td>
    <td class='celdaValorAttr'>
      <select name='id_beca'>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($becas,$_REQUEST['id_beca'])); ?>        
      </select>
    </td>
    <td class='celdaNombreAttr'>Cr&eacute;dito:</td>
    <td class='celdaValorAttr'>
      <select name='id_credito'>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($creditos,$_REQUEST['id_credito'])); ?>        
      </select>
    </td>
  </tr>
</table>

<script>
	tipo_admision('<?php echo($_REQUEST['admision']); ?>');
</script>

</form>
