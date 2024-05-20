<?php

$id_fuas = $_REQUEST['id_fuas'];

if (empty($forma)) { $forma = $_REQUEST['forma']; }

if (empty($id_alumno)) { $id_alumno = $_REQUEST['id_alumno']; }

if (is_numeric($id_alumno)) {
	$SQL_alumno = "SELECT a.id AS id_alumno,rut,nombres,apellidos,c.nombre AS carrera,
	                      CASE a.jornada WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada,
	                      semestre_cohorte||'-'||cohorte AS cohorte
	               FROM alumnos AS a
	               LEFT JOIN carreras AS c ON c.id=a.carrera_actual
	               WHERE a.id=$id_alumno";
	$alumno = consulta_sql($SQL_alumno);
	$_REQUEST = array_merge($_REQUEST,$alumno[0]);
}

$comunas             = consulta_sql("SELECT id,nombre FROM comunas;");
$regiones            = consulta_sql("SELECT id,nombre||' ('||romano||')' AS nombre FROM regiones;");
$ESTADOS_CIVILES     = consulta_sql("SELECT * FROM vista_estados_civiles");
$TENENCIAS_DOM       = consulta_sql("SELECT * FROM vista_tenencias_dom");
$PUEBLOS_ORIGINARIOS = consulta_sql("SELECT * FROM vista_pueblos_originarios");
$NIVEL_ESTUDIOS      = consulta_sql("SELECT * FROM dae.nivel_estudios");
$ACTIVIDADES         = consulta_sql("SELECT * FROM dae.actividades");
?>

<div style='margin-top: 5px'>
<?php

if ($forma == 'editar') {
	echo("  <input type='submit' name='editar' value='Guardar' tabindex='99'>\n");
} else {
	echo("  <input type='submit' name='crear' value='Guardar' tabindex='99'>\n");
}

?>  
  <input type="button" name="cancelar" value="Cancelar" onclick="history.back();">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales y Curriculares del Alumno</td></tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['rut']); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['id_alumno']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Apellidos:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['apellidos']); ?></td>
    <td class='celdaNombreAttr'>Nombres:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['nombres']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($_REQUEST['carrera']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Jornada:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['jornada']); ?></td>
    <td class='celdaNombreAttr'>Cohorte:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['cohorte']); ?></td>
  </tr>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de Contacto del Alumno</td></tr>

  <tr>
    <td class='celdaNombreAttr' style='vertical-align: top'><u>e-mail:</u></td>
    <td class='celdaValorAttr' colspan='3'>
      <input type='email' size="40" name='email' value="<?php echo($_REQUEST['email']); ?>" onKeyUp="var valor=this.value;this.value=valor.toLowerCase();" class='boton' required>
      <small><br>En este correo se informará el resultado del Proceso de Postulación a Beca UMC</small>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='vertical-align: top'><u>Tel. Fijo:</u></td>
    <td class='celdaValorAttr'>
      <b>+56</b> <input type="number" size='9' maxlength='9' name="telefono" min="100000001" pattern="[0-9]*" title="Ingrese sólo números" name='telefono' value="<?php echo($_REQUEST['telefono']); ?>" class="boton" required>
      <small><br>Si no posee un número de red fija, ingrese<br>acá también su número de teléfono móvil</small>
    </td>
    <td class='celdaNombreAttr' style='vertical-align: top'><u>Tel. Móvil:</u></td>
    <td class='celdaValorAttr'>
      <b>+56</b> <input type="number" size='9' maxlength='9' name="tel_movil" min="100000001" pattern="[0-9]*" title="Ingrese sólo números" value="<?php echo($_REQUEST['tel_movil']); ?>" class="boton" required>
      <small><br>Si no posee un número de teléfono móvil,<br>ingrese acá también su número de teléfono fijo</small>
    </td>
  </tr>

  <tr><td class='celdaValorAttr' colspan="4"><small>&nbsp;</small></td></tr>  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de Caracterización del Alumno</td></tr>

  <tr>
    <td class='celdaNombreAttr'><u>Nivel Educacional:</u></td>
    <td class='celdaValorAttr'><select name='nivel_educ' class='filtro' required><option>-- Seleccione --</option><?php echo(select($NIVEL_ESTUDIOS,$_REQUEST['nivel_educ'])); ?></select></td>
    <td class='celdaNombreAttr'><u>Estado Civil:</u></td>
    <td class='celdaValorAttr'><select name='estado_civil' class='filtro' required><option>-- Seleccione --</option><?php echo(select($ESTADOS_CIVILES,$_REQUEST['estado_civil'])); ?></select></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Enfermo Crónico?:</u></td>
    <td class='celdaValorAttr'><select name='enfermo_cronico' class='filtro' onChange="acciones_enfermo_cronico(this.value);" required><option value=''>-- Seleccione --</option><?php echo(select($sino,$_REQUEST['enfermo_cronico'])); ?></select></td>
    <td class='celdaNombreAttr'><u>Nombre Enfermedad:</u></td>
    <td class='celdaValorAttr'><input type='text' size="20" name='nombre_enfermedad' value="<?php echo($_REQUEST['nombre_enfermedad']); ?>" class='boton' disabled></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan='2'><u>Pertenencia a Pueblo Originario:</u></td>
    <td class='celdaValorAttr' colspan='2'><select name='pertenece_pueblo_orig' class='filtro' onChange="acciones_pert_pueblo_orig(this.value);" required><?php echo(select($PUEBLOS_ORIGINARIOS,$_REQUEST['pertenece_pueblo_orig'])); ?></select></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan='2'><u>Pertenencia acreditada?:</u></td>
    <td class='celdaValorAttr' colspan='2'><select name='acred_pert_pueblo_orig' class='filtro' disabled><option value=''>-- Seleccione --</option><?php echo(select($sino,$_REQUEST['acred_pert_pueblo_orig'])); ?></select></td>
  </tr>
  <tr><td class='celdaValorAttr' colspan="4"><small>&nbsp;</small></td></tr>  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Laborales del Alumno</td></tr>  

  <tr>
    <td class='celdaNombreAttr'><u>Categoría Ocupacional:</u></td>
    <td class='celdaValorAttr'><select name='cat_ocupacional' class='filtro' required><option>-- Seleccione --</option><?php echo(select($ACTIVIDADES,$_REQUEST['cat_ocupacional'])); ?></select></td>
    <td class='celdaNombreAttr'><u>Jefe de Hogar?</u></td>
    <td class='celdaValorAttr'><select name='jefe_hogar' class='filtro' required><option value=''>-- Seleccione --</option><?php echo(select($sino,$_REQUEST['jefe_hogar'])); ?></select></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='vertical-align: top' colspan='2'><u>Ingreso Mensual Líquido Promedio:</u></td>
    <td class='celdaValorAttr' colspan='2'>
      $<input type='text' size="9" name='ing_liq_mensual_prom' value="<?php echo($_REQUEST['ing_liq_mensual_prom']); ?>" class='montos' onKeyUp="puntitos(this,this.value.charAt(this.value.length-1),this.name);">
<?php
if ($forma <> 'editar') { 
	echo("<small><br>Una vez que complete este formulario inicial,<br>podrá adjuntar los documentos que respalden este Ingreso Mensual</small>");
}
?>
    </td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="4">
      <blockquote><blockquote><blockquote><blockquote>
	  <small>
		Un jefe de hogar debe justificar sus ingresos con uno de los siguientes 	documentos de respaldo:<br>
		<br>
	    - Certificado de Cotizaciones de AFP (para trabajadores dependientes o contratados)<br>
	    - Carpeta Tributaria (para trabajadores independientes)<br>
	    - Certificado de Pensiones (para jubilados, pensionados o montepiados)<br>
	    - Certificado de Cesantía (para mayores de 18 años que no tengan empleo)<br>
	    <br>
	    NOTA: En un grupo familiar pueden existir más de un jefe de hogar.
	  </small>
	  </blockquote></blockquote></blockquote></blockquote>
    </td>
  </tr>  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Vivienda del Grupo Familiar</td></tr>
  <tr>
    <td class='celdaNombreAttr' style='vertical-align: top'><u>Dirección:</u></td>
    <td class='celdaValorAttr' colspan="3">
      <input type='text' size="40" name='domicilio_grupo_fam' value="<?php echo($_REQUEST['domicilio_grupo_fam']); ?>" onBlur="var valor=this.value;this.value=valor.toUpperCase();" class='boton' required>
      <small><br>Av./Calle/Psje. # [#Depto|#Block|#Torre] Villa/Población</small>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Comuna:</u></td>
    <td class='celdaValorAttr'><select name='comuna_grupo_fam' class='filtro' required><option value=''>-- Seleccione --</option><?php echo(select($comunas,$_REQUEST['comuna_grupo_fam'])); ?></select></td>
    <td class='celdaNombreAttr'><u>Región:</u></td>
    <td class='celdaValorAttr'><select name='region_grupo_fam' class='filtro' required><option value=''>-- Seleccione --</option><?php echo(select($regiones,$_REQUEST['region_grupo_fam'])); ?></select></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Tenencia:</u></td>
    <td class='celdaValorAttr' colspan='3'><select name='tenencia_dom_grupo_fam' class='filtro' required><option value=''>-- Seleccione --</option><?php echo(select($TENENCIAS_DOM,$_REQUEST['tenencia_dom_grupo_fam'])); ?></select></td>
  </tr>
</table>
<script language="Javascript">

acciones_enfermo_cronico('<?php echo($_REQUEST['enfermo_cronico']); ?>');

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

puntitos(document.formulario.ing_liq_mensual_prom,document.formulario.ing_liq_mensual_prom.value.charAt(document.formulario.ing_liq_mensual_prom.value.length-1),document.formulario.ing_liq_mensual_prom.name);

</script>
