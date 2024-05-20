<?php

if($_REQUEST['nacionalidad'] == "") { $_REQUEST['nacionalidad'] = "CL"; }
if($_REQUEST['id'] == "") { $_REQUEST['id'] = "** Una vez creado el postulante tendrá ID **"; }
if($_REQUEST['admision'] == "") { $_REQUEST['admision'] = "1"; }

$comunas        = consulta_sql("SELECT id,nombre FROM comunas;");
$regiones       = consulta_sql("SELECT id,nombre||' ('||romano||')' AS nombre FROM regiones;");
$nacionalidades = consulta_sql("SELECT localizacion AS id,nacionalidad AS nombre FROM pais ORDER BY nacionalidad;");
$inst_edsup     = consulta_sql("SELECT ies.id,ies.nombre||' - '||p.nombre AS nombre FROM inst_edsup AS ies LEFT JOIN pais AS p ON p.localizacion=ies.pais ORDER BY ies.nombre;");
$referencias    = consulta_sql("SELECT id,nombre FROM admision.referencias WHERE activa ORDER BY nombre;");
$admision_subtipo = consulta_sql("SELECT id,nombre from pap_admision_subtipo;");
$regimenes      = consulta_sql("SELECT id,nombre,agrupador AS grupo FROM regimenes_ ORDER BY orden");

$carreras       = consulta_sql("SELECT c.id,c.nombre||' ('||alias||')' as nombre,r.nombre AS grupo FROM carreras AS c LEFT JOIN regimenes_ AS r ON r.id=c.regimen WHERE activa AND admision ORDER BY r.orden,nombre");
$carreras_novig = consulta_sql("SELECT c.id,c.nombre||' ('||alias||')' as nombre,'No Vigentes' AS grupo FROM carreras AS c LEFT JOIN regimenes_ AS r ON r.id=c.regimen WHERE NOT activa AND NOT admision ORDER BY r.orden,nombre");
$carreras = array_merge($carreras,$carreras_novig);

$origenes_bd    = consulta_sql("SELECT id,nombre FROM admision.origenes_bd");


$SEMESTRES_COHORTES = array(array("id"=>1,"nombre"=>1),
                            array("id"=>2,"nombre"=>2));
$COHORTES = $anos;
?>

<form name="formulario" action="principal.php" method="post"
      onSubmit="if (!val_nota('promedio_col','prom_nt_ies_pro') || !val_psu('puntaje_psu') || !valida_rut(formulario.rut.value)) { return false; }">

<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="validar" value="<?php echo($validar); ?>">
<input type="hidden" name="id_pap" value="<?php echo($id_pap); ?>">
<input type="hidden" name="cohorte" value="<?php echo($ANO_MATRICULA); ?>">
<input type="hidden" name="semestre_cohorte" value="<?php echo($SEMESTRE_MATRICULA); ?>">

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
      <input type="text" size="15" class='boton' name="rut" value="<?php echo($_REQUEST['rut']); ?>" <?php if($forma<>'editar'){ echo("readonly"); } ?>>
    </td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['id']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Apellidos:</u></td>
    <td class='celdaValorAttr'>
      <input type='text' class='boton' size="25" name='apellidos' value="<?php echo($_REQUEST['apellidos']); ?>" onBlur="var valor=this.value;this.value=valor.toUpperCase();" required>
      <script>formulario.apellidos.focus();</script>
    </td>
    <td class='celdaNombreAttr'><u>Nombres:</u></td>
    <td class='celdaValorAttr'>
      <input type='text' class='boton' size="25" name='nombres' value="<?php echo($_REQUEST['nombres']); ?>" onBlur="var valor=this.value;this.value=valor.toUpperCase();" required>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Género:</u></td>
    <td class='celdaValorAttr'>
      <select name='genero'  class='filtro' required>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($generos,$_REQUEST['genero'])); ?>        
      </select>
    </td>
    <td class='celdaNombreAttr'><u>Fecha de nacimiento:</u></td>
    <td class='celdaValorAttr'>
      <select name="fec_nac_dia" class='filtro' required>
        <option value="" style="text-align: center; ">- D&iacute;a -</option>
        <?php echo(select($dias_fn,$_REQUEST['fec_nac_dia'])); ?>
      </select>/
      <select name="fec_nac_mes" class='filtro' required>
        <option value="" style="text-align: center; ">- Mes -</option>
        <?php echo(select($meses_fn,$_REQUEST['fec_nac_mes'])); ?>
      </select>/
      <select name="fec_nac_ano" class='filtro' required>
        <option value="" style="text-align: center; ">- Año -</option>
        <?php echo(select($anos_fn,$_REQUEST['fec_nac_ano'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Estado Civil:</u></td>
    <td class='celdaValorAttr'>
      <select name='est_civil' class='filtro' required>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($estados_civiles,$_REQUEST['est_civil'])); ?>        
      </select>
    </td>
    <td class='celdaNombreAttr'><u>Profesión:</u></td>
    <td class='celdaValorAttr'><input type='text' size="20" class='boton' name='profesion' value="<?php echo($_REQUEST['profesion']); ?>" onBlur="var valor=this.value;this.value=valor.toUpperCase();" required></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Nacionalidad:</u></td>
    <td class='celdaValorAttr'>
      <select name='nacionalidad' class='filtro' onChange="if (this.value == 'CL') { formulario.pasaporte.disabled = true; } else { formulario.pasaporte.disabled = false; }" required>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($nacionalidades,$_REQUEST['nacionalidad'])); ?>        
      </select>
    </td>
    <td class='celdaNombreAttr'>Nro. Pasaporte:</td>
    <td class='celdaValorAttr'><input type='text' class='boton' size="15" name='pasaporte' value="<?php echo($_REQUEST['pasaporte']); ?>" disabled></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Direcci&oacute;n:</u></td>
    <td class='celdaValorAttr' colspan="3">
      <input type='text' size="50" class='boton' name='direccion' value="<?php echo($_REQUEST['direccion']); ?>" onBlur="var valor=this.value;this.value=valor.toUpperCase();" required><br>
      <sup>Av./Calle/Psje. # Villa/Población</sup>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Comuna:</u></td>
    <td class='celdaValorAttr'>
      <select name='comuna' class='filtro' required>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($comunas,$_REQUEST['comuna'])); ?>        
      </select>
    </td>
    <td class='celdaNombreAttr'><u>Región:</u></td>
    <td class='celdaValorAttr'>
      <select name='region' class='filtro' required>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($regiones,$_REQUEST['region'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Teléfono fijo:</u></td>
    <td class='celdaValorAttr'>
      <b>+56</b> <input type="number" size='9' class='boton' maxlength='9' min="100000001" pattern="[0-9]*" title="Ingrese sólo números" name="telefono" value="<?php echo($_REQUEST['telefono']); ?>" required>
    </td>
    <td class='celdaNombreAttr'><u>Teléfono móvil:</u></td>
    <td class='celdaValorAttr'>
      <b>+56</b> <input type="number" size='9' class='boton' maxlength='9' min="100000001" pattern="[0-9]*" title="Ingrese sólo números" name='tel_movil' value="<?php echo($_REQUEST['tel_movil']); ?>" required>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' rowspan="2">e-mail:</td>
    <td class='celdaValorAttr' rowspan="2">
      <input type='email' size="25" class='boton' name='email' value="<?php echo($_REQUEST['email']); ?>" onKeyUp="var valor=this.value;this.value=valor.toLowerCase();" required>
    </td>
    <td class='celdaNombreAttr'><u>Referencia:</u></td>
    <td class='celdaValorAttr'>
      <select name='referencia' class='filtro' required>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($referencias,$_REQUEST['referencia'])); ?>        
      </select><br>
      <small><b>Comentarios:</b></small>
      <input type='text' class='boton' size="25" name='referencia_comentarios' value="<?php echo($_REQUEST['referencia_comentarios']); ?>">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>BD Origen:</td>
    <td class='celdaValorAttr'>
      <select name='id_origen_bd' class='filtro'>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($origenes_bd,$_REQUEST['id_origen_bd'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Postulaci&oacute;n</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Admisión:</u></td>
    <td class='celdaValorAttr'>
      <select name="admision" class='filtro' required>
        <option value="">-- Seleccione --</option>
		  <?php echo(select($ADMISION,$_REQUEST['admision'])); ?>
      </select>
      <select name='admision_subtipo' class='filtro'>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($admision_subtipo,$_REQUEST['admision_subtipo'])); ?>
      </select>
    </td>
    <td class='celdaNombreAttr'>Fecha postulaci&oacute;n:</td>
    <td class='celdaValorAttr'>
      <input type='text' class='boton' name='fecha_post' value="<?php echo($_REQUEST['fecha_post']); ?>" disabled>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Periodo:</td>
    <td class='celdaValorAttr'>
      <select name="semestre_cohorte" class='filtro' required>
		<option value="">- Sem -</option>
        <?php echo(select($SEMESTRES_COHORTES,$_REQUEST['semestre_cohorte'])); ?>
      </select>-<select name="cohorte" class='filtro' required>
		<option value="">- Cohorte -</option>
		<?php echo(select($COHORTES,$_REQUEST['cohorte'])); ?>
      </select>
    </td>
    <td class='celdaNombreAttr'>Estado:</td>
    <td class='celdaValorAttr'><input type='text' class='boton' name='estado' value='' readonly></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Régimen:</u></td>
    <td class='celdaValorAttr' colspan="3">
      <select name="regimen" class='filtro' required>
        <option value="">-- Seleccione --</option>
		    <?php echo(select($regimenes,$_REQUEST['regimen'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>1ra Carrera:</u></td>
    <td class='celdaValorAttr' colspan="3">
      <select name='carrera1_post' style='max-width: 500px' class='filtro' required>
        <option value=''>-- Seleccione --</option>
        <?php echo(select_group($carreras,$_REQUEST['carrera1_post'])); ?>        
      </select>
      <b><u>Jornada:</u></b>
      <select name='jornada1_post' style='max-width: 500px' class='filtro' required>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($JORNADAS,$_REQUEST['jornada1_post'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>2da Carrera:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name='carrera2_post' style='max-width: 500px' class='filtro'><option value=''>-- Seleccione --</option><?php echo(select_group($carreras,$_REQUEST['carrera2_post'])); ?></select>
      <b>Jornada:</b>
      <select name='jornada2_post' style='max-width: 500px' class='filtro'><option value=''>-- Seleccione --</option><?php echo(select($JORNADAS,$_REQUEST['jornada2_post'])); ?></select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>3ra Carrera:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name='carrera3_post' style='max-width: 500px' class='filtro'><option value=''>-- Seleccione --</option><?php echo(select_group($carreras,$_REQUEST['carrera3_post'])); ?></select>    
      <b>Jornada:</b>
      <select name='jornada3_post' style='max-width: 500px' class='filtro'><option value=''>-- Seleccione --</option><?php echo(select($JORNADAS,$_REQUEST['jornada3_post'])); ?></select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>4ta Carrera:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name='carrera4_post' style='max-width: 500px' class='filtro'><option value=''>-- Seleccione --</option><?php echo(select_group($carreras,$_REQUEST['carrera4_post'])); ?></select>    
      <b>Jornada:</b>
      <select name='jornada4_post' style='max-width: 500px' class='filtro'><option value=''>-- Seleccione --</option><?php echo(select($JORNADAS,$_REQUEST['jornada4_post'])); ?></select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>5ta Carrera:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name='carrera5_post' style='max-width: 500px' class='filtro'><option value=''>-- Seleccione --</option><?php echo(select_group($carreras,$_REQUEST['carrera5_post'])); ?></select>    
      <b>Jornada:</b>
      <select name='jornada5_post' style='max-width: 500px' class='filtro'><option value=''>-- Seleccione --</option><?php echo(select($JORNADAS,$_REQUEST['jornada5_post'])); ?></select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>6ta Carrera:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name='carrera6_post' style='max-width: 500px' class='filtro'><option value=''>-- Seleccione --</option><?php echo(select_group($carreras,$_REQUEST['carrera6_post'])); ?></select>    
      <b>Jornada:</b>
      <select name='jornada6_post' style='max-width: 500px' class='filtro'><option value=''>-- Seleccione --</option><?php echo(select($JORNADAS,$_REQUEST['jornada6_post'])); ?></select>
    </td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="4" align="center">
      <b>Documentación Requerida</b><br>
      <sup>Marcar las casillas correspondientes a los documentos que presente materialmente el postulante</sup>
    </td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="2">
      <input id="cert_nacimiento" type='checkbox' name='cert_nacimiento' value="t" onClick="calc_estado();"
             <?php if ($_REQUEST['cert_nacimiento'] == "t") { echo("checked"); } ?>>
      <label for="cert_nacimiento">Certificado de nacimiento <sup>(ORIGINAL o Fotocopia Legalizada)</sup></label><br>
      <br>
      <input id="copia_ced_iden" type='checkbox' name='copia_ced_iden' value="t" onClick="calc_estado();"
             <?php if ($_REQUEST['copia_ced_iden'] == "t") { echo("checked"); } ?>>
      <label for="copia_ced_iden">Fotocopia Cédula Nacional de Identidad</label><br>
      <br>
      <input id="fotografias" type='checkbox' name='fotografias' value="t" onClick="calc_estado();"
             <?php if ($_REQUEST['fotografias'] == "t") { echo("checked"); } ?>>
      <label for="fotografias">2 Fotografías <sup>(Tamaño carnet con Nombre y RUT)</sup></label>
    </td>      
    <td class='celdaValorAttr' colspan="2">
      <table>
        <tr>
          <td class='celdaValorAttr'>
            <div style='text-align: center'>Licencia de Enseñanza Media</div>
            <input id="licencia_em" type='checkbox' name='licencia_em' value="t" 
                   onClick="if (formulario.licencia_em.value=='t' && formulario.licencia_em_comp_solic.value=='t') { formulario.licencia_em_comp_solic.checked = false; } calc_estado();"
                   <?php if ($_REQUEST['licencia_em'] == "t") { echo("checked"); } ?>>
            <label for="licencia_em">ORIGINAL o Fotocopia Legalizada</label>
            &nbsp;
            <input id="licencia_em_comp_solic" type='checkbox' name='licencia_em_comp_solic' value="t" 
                   onClick="if (formulario.licencia_em.value=='t' && formulario.licencia_em_comp_solic.value=='t') { formulario.licencia_em.checked = false; } calc_estado();"
                   <?php if ($_REQUEST['licencia_em_comp_solic'] == "t") { echo("checked"); } ?>>
            <label for="licencia_em_comp_solic">Comprobante de Solicitud</label>
          </td>
        </tr>
      </table>
      <table>
        <tr>
          <td class='celdaValorAttr'>
            <div style='text-align: center'>Concentración de Notas</div>
            <input id="conc_notas_em" type='checkbox' name='conc_notas_em' value="t" 
                   onClick="if (formulario.conc_notas_em.value=='t' && formulario.conc_notas_em_comp_solic.value=='t') { formulario.conc_notas_em_comp_solic.checked = false; } calc_estado();"
                   <?php if ($_REQUEST['conc_notas_em'] == "t") { echo("checked"); } ?>>
            <label for="conc_notas_em">ORIGINAL o Fotocopia Legalizada</label>
            &nbsp;
            <input id="conc_notas_em_comp_solic" type='checkbox' name='conc_notas_em_comp_solic' value="t" 
                   onClick="if (formulario.conc_notas_em.value=='t' && formulario.conc_notas_em_comp_solic.value=='t') { formulario.conc_notas_em.checked = false; } calc_estado();"
                   <?php if ($_REQUEST['conc_notas_em_comp_solic'] == "t") { echo("checked"); } ?>>
            <label for="conc_notas_em_comp_solic">Comprobante de Solicitud</label>
          </td>
        </tr>
      </table>
      <hr>
      <input id="boletin_psu" type='checkbox' name='boletin_psu' value="t"
             <?php if ($_REQUEST['boletin_psu'] == "t") { echo("checked"); } ?>>
      <label for="boletin_psu">Boletín PSU</label>
      <sup>(Opcional, obligatorio para optar a becas [ORIGINAL o Fotocopia Legalizada])</sup>
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
      <input type='text' class='boton' name='rbd_colegio' size="5" value="<?php echo($_REQUEST['rbd_colegio']); ?>">
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
      <input type='text' class='boton' name='ano_egreso_col' size="4" value="<?php echo($_REQUEST['ano_egreso_col']); ?>">
    </td>
    <td class='celdaNombreAttr'>Promedio EM:</td>
    <td class='celdaValorAttr'>
      <input type='text' class='boton' name='promedio_col' size="5" value="<?php echo($_REQUEST['promedio_col']); ?>">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>A&ntilde;o PSU:</td>
    <td class='celdaValorAttr'>
      <input type='text' class='boton' name='ano_psu' size="4" value="<?php echo($_REQUEST['ano_psu']); ?>">
    </td>
    <td class='celdaNombreAttr'>Puntaje PSU:</td>
    <td class='celdaValorAttr'>
      <input type='text' class='boton' name='puntaje_psu' size="4" value="<?php echo($_REQUEST['puntaje_psu']); ?>">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">
      Antecedentes de Estudios Superiores del Postulante<br>
      <sup>(La última Institución en que haya cursado asignaturas)</sup>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Instituci&oacute;n:</td>
    <td class='celdaValorAttr' colspan="3">
      <select id='id_inst_edsup_proced' name='id_inst_edsup_proced' class='filtro' style='max-width: 500px'>
        <option value=''>-- Seleccione --</option>
		  	<?php echo(select($inst_edsup,$_REQUEST['id_inst_edsup_proced'])); ?>        
      </select>

    <!--      <input name='id_inst_edsup_proced' list='ids_ies' class='filtro' style='max-width: 500pt'>
      <datalist id='ids_ies' name='ids_ies'>
        <option value=''>-- Seleccione --</option>
	  		<?php echo(select($inst_edsup,$_REQUEST['id_inst_edsup_proced'])); ?>        
      </datalist>
       <select name='id_inst_edsup_proced' class='filtro' style='max-width: 500pt'>
        <option value=''>-- Seleccione --</option>
			<?php echo(select($inst_edsup,$_REQUEST['id_inst_edsup_proced'])); ?>        
      </select>    -->
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr'>
      <input type='text' class='boton' name='carr_ies_pro' size="20" value="<?php echo($_REQUEST['carr_ies_pro']); ?>" onBlur="var valor=this.value;this.value=valor.toUpperCase();">
    </td>
    <td class='celdaNombreAttr'>Promedio Notas:</td>
    <td class='celdaValorAttr'>
      <input type='text' class='boton' name='prom_nt_ies_pro' size="4" value="<?php echo($_REQUEST['prom_nt_ies_pro']); ?>">
    </td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="4" align="center">
      <b>Documentaci&oacute;n Requerida (s&oacute;lo si convalida)</b><br>
      <sup>Marcar las casillas correspondientes a los documentos que presente materialmente el postulante</sup><br>
    </td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="2">
      <input id="conc_nt_ies_pro" class='boton' type='checkbox' name='conc_nt_ies_pro' value="t"
             <?php if ($_REQUEST['conc_nt_ies_pro'] == "t") { echo("checked"); } ?>>
      <label for="conc_nt_ies_pro">Concentraci&oacute;n de Notas</label><br>
      <sup>(ORIGINAL o Fotocopia Legalizada, visado por la Institución que lo emite)</sup>
    </td>      
    <td class='celdaValorAttr' colspan="2">
      <input id="prog_as_ies_pro" class='boton' type='checkbox' name='prog_as_ies_pro' value="t"
             <?php if ($_REQUEST['prog_as_ies_pro'] == "t") { echo("checked"); } ?>>
      <label for="prog_as_ies_pro">Programas de Asignaturas APROBADAS</label><br>
      <sup>(ORIGINALES o Fotocopias Legalizadas, visados por la Institución que lo emite)</sup>
    </td>
  </tr>
</table>

<!-- <script>
	tipo_admision('<?php echo($_REQUEST['admision']); ?>');
</script> -->

</form>

<script>
function calc_estado() {
	var licencia_em              = formulario.licencia_em.checked,
	    licencia_em_comp_solic   = formulario.licencia_em_comp_solic.checked,
	    conc_notas_em            = formulario.conc_notas_em.checked,
	    conc_notas_em_comp_solic = formulario.conc_notas_em_comp_solic.checked,
	    copia_ced_iden           = formulario.copia_ced_iden.checked,
	    cert_nacimiento          = formulario.cert_nacimiento.checked,
	    fotografias              = formulario.fotografias.checked,
	    estado                   = "";
	    
	if (licencia_em && conc_notas_em && cert_nacimiento && copia_ced_iden && fotografias) 
	{ estado = "Completo"; }	
	else if (licencia_em && (!conc_notas_em || conc_notas_em_comp_solic) && cert_nacimiento && copia_ced_iden && fotografias)
	{ estado = "Vigente"; }
	else if (!licencia_em && licencia_em_comp_solic && conc_notas_em && cert_nacimiento && copia_ced_iden && fotografias)
	{ estado = "Condicional s/LIC"; }
	else if (licencia_em && (!conc_notas_em || !cert_nacimiento || !copia_ced_iden || !fotografias)) 
	{ estado = "Condicional c/LIC"; }
	else 
	{ estado = "Provisorio"; }

	formulario.estado.value = estado;

}

calc_estado();

$(document).ready(function () {
      $('#id_inst_edsup_proced').selectize({
          sortField: 'text'
      });
  });
</script>
