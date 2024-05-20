<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_alumno = $_REQUEST['id_alumno'];
$periodo   = "$SEMESTRE_MATRICULA-$ANO_MATRICULA";

if ($SEMESTRE_MATRICULA == 1) { $mes = "04"; } else { $mes = "09"; }

$fecha_compromiso_min = date("Y-m-d");

$fecha_compromiso_max = "$ANO_MATRICULA-$mes-30";

$alumno = consulta_sql("SELECT va.id,trim(va.rut) AS rut,nombre,a.email,a.telefono,a.tel_movil FROM vista_alumnos va LEFT JOIN alumnos a USING(id) WHERE a.id=$id_alumno");
if (count($alumno) == 0) {
	$postulante = consulta_sql("SELECT id,trim(rut) AS rut,nombre FROM vista_pap WHERE rut='$rut'");
	extract($postulante[0]);
} else {
	extract($alumno[0]);
}

if ($_REQUEST['guardar'] == "Guardar") {
		
	$fecha_compromiso   = (empty($_REQUEST['fecha_compromiso'])) ? "null" : "'{$_REQUEST['fecha_compromiso']}'";
	$id_motivo_no_remat = (empty($_REQUEST['id_motivo_no_remat'])) ? "null" : $_REQUEST['id_motivo_no_remat'];
  $tipo_contacto      = $_REQUEST['tipo_contacto'];
  $obtiene_respuesta  = $_REQUEST['obtiene_respuesta'];
	$id_operador        = $_SESSION['id_usuario'];
	$comentarios        = $_REQUEST['comentarios'];
	$semestre_mat       = $SEMESTRE_MATRICULA;
	$ano_mat            = $ANO_MATRICULA;

	$SQLins_atencion = "INSERT INTO gestion.atenciones_remat (id_alumno,tipo_contacto,obtiene_respuesta,fecha_compromiso,id_motivo_no_remat,comentarios,id_operador,semestre_mat,ano_mat) 
							VALUES ($id_alumno,'$tipo_contacto','$obtiene_respuesta',$fecha_compromiso,$id_motivo_no_remat,'$comentarios',$id_operador,$semestre_mat,$ano_mat);";
	if (consulta_dml($SQLins_atencion) > 0) {
		echo(msje_js("Se ha registrado satisfactoriamente la atención."));
		echo(js("window.location='$enlbase_sm=remat_atenciones&id_alumno=$id_alumno';"));
		exit;
	}

}

$MOTIVOS = consulta_sql("SELECT id,tipo||': '||nombre AS nombre FROM gestion.atenciones_remat_motivos ORDER BY tipo,nombre");
$TIPOS_CONTACTO = consulta_sql("SELECT id,nombre FROM vista_gar_tipo_contacto");
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name='formulario' action='principal_sm.php' method='post'>
<input type='hidden' name='modulo' value='<?php echo($modulo); ?>'>
<input type='hidden' name='id_alumno' value='<?php echo($id_alumno); ?>'>

<div style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Guardar" onClick="if (formulario.fecha_compromiso.value == '' && formulario.id_motivo_no_remat.value == '' && formulario.obtiene_respuesta.value=='t') { alert('ERROR: Debe registrar un Compromiso de rematricula o bien un Motivo de no rematricula'); return false; }">
  <input type="button" name="volver" value="Volver" onClick="history.back();">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="6" style="text-align: center; ">Antecedentes Personales del Estudiante</td></tr>
  <tr>
    <td class='celdaNombreAttr'><u>Código Interno:</u></td>
    <td class='celdaValorAttr'><?php echo($id); ?></td>
    <td class='celdaNombreAttr'><u>RUT:</u></td>
    <td class='celdaValorAttr'><?php echo($rut); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Nombre:</u></td>
    <td class='celdaValorAttr' colspan="3"><?php echo($nombre); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Teléfono:</u></td>
    <td class='celdaValorAttr'><?php echo($telefono); ?></td>
    <td class='celdaNombreAttr'><u>Tel. Móvil:</u></td>
    <td class='celdaValorAttr'><?php echo($tel_movil); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>e-Mail:</u></td>
    <td class='celdaValorAttr' colspan="3"><?php echo($email); ?></td>
  </tr>

  <tr><td class='celdaNombreAttr' colspan="6" style="text-align: center; ">Antecedentes de la Atención</td></tr>
  <tr>
    <td class='celdaNombreAttr'><u>Forma de contacto:</u></td>
    <td class='celdaValorAttr' colspan="3">
	    <select class="filtro" name="tipo_contacto" style='max-width: none' required>
        <?php echo(select($TIPOS_CONTACTO,$tipo_contacto)); ?>
      </select>&nbsp;
      <input type='radio' name='obtiene_respuesta' value='f' id='obtiene_respuesta_no' onClick='formulario.fecha_compromiso.disabled = true; formulario.id_motivo_no_remat.disabled = true;' required>
      <label for='obtiene_respuesta_no'>Sin respuesta</label>&nbsp;&nbsp;&nbsp;
      <input type='radio' name='obtiene_respuesta' value='t' id='obtiene_respuesta_si' onClick='formulario.fecha_compromiso.disabled = false; formulario.id_motivo_no_remat.disabled = false;' required>
      <label for='obtiene_respuesta_si'>Contactado</label>
    </td>
  </tr>

  <tr>
    <td class='celdaNombreAttr'><u>Compromiso Rematrícula:</u></td>
    <td class='celdaValorAttr' colspan='3'>
	  <input type='date' min="<?php echo($fecha_compromiso_min); ?>" max="<?php echo($fecha_compromiso_max); ?>" name='fecha_compromiso' class='boton' onBlur="formulario.id_motivo_no_remat.disabled = (this.value !== '');" disabled>
	</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Motivo de no rematricula:</u></td>
    <td class='celdaValorAttr' colspan='3'>
	  <select class="filtro" name="id_motivo_no_remat" style='max-width: none' onBlur="formulario.fecha_compromiso.disabled = (this.value !== '');" disabled>
		  <option value=''>-- Seleccione --</option>
      <?php echo(select($MOTIVOS,$id_motivo_no_remat)); ?>
    </select>
	</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Comentarios:</u></td>
    <td class='celdaValorAttr' colspan='3'><textarea name='comentarios' class='grande' required></textarea></td>
  </tr>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
