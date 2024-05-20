<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");
$id_tabla  = $_REQUEST['id_tabla'];
$id_alumno = $_REQUEST['id_alumno'];
$id_motivo = $_REQUEST['id_motivo']; //(empty($_REQUEST['id_motivo_no_remat'])) ? "null" : $_REQUEST['id_motivo_no_remat'];
$tipo_contacto      = $_REQUEST['tipo_contacto'];
$obtiene_respuesta  = $_REQUEST['obtiene_respuesta'];
$obtiene_resuelto  = $_REQUEST['obtiene_resuelto'];
$comentarios        = $_REQUEST['comentarios'];
$comentarios_derivado = $_REQUEST['comentarios_derivado'];
$id_area_derivacion = $_REQUEST['id_area_derivacion'];
$su_comentario = $_REQUEST['su_comentario'];
$id_operador        = $_SESSION['id_usuario'];
$modo_ver        = $_REQUEST['modo_ver'];
$es_mi_caso        = $_REQUEST['es_mi_caso'];
//echo("MODO_VER=".$modo_ver);
if ($obtiene_respuesta='t') {
  $respuestaOrigen = 'Contactado';
} else {
  $respuestaOrigen = 'Sin respuesta';
}

//echo("</br>id_alumno=".$id_alumno);
//echo("</br>id_motivo=".$id_motivo);
//echo("</br>tipo_contacto=".$tipo_contacto);
//echo("</br>obtiene_respuesta=".$obtiene_respuesta);
//echo("</br>obtiene_resuelto=".$obtiene_resuelto);
//echo("</br>comentarios=".$comentarios);
//echo("</br>id_area_derivacion=".$id_area_derivacion);

//$periodo   = "$SEMESTRE_MATRICULA-$ANO_MATRICULA";
 
//if ($SEMESTRE_MATRICULA == 1) { $mes = "03"; } else { $mes = "08"; }

//$fecha_compromiso_min = date("Y-m-d");
//$fecha_compromiso_max = "$ANO-$mes-31"; 

$alumno = consulta_sql("SELECT va.id,trim(va.rut) AS rut,nombre,a.email,a.telefono,a.tel_movil FROM vista_alumnos va LEFT JOIN alumnos a USING(id) WHERE a.id=$id_alumno");
if (count($alumno) == 0) {
	$postulante = consulta_sql("SELECT id,trim(rut) AS rut,nombre FROM vista_pap WHERE rut='$rut'");
	extract($postulante[0]);
} else {
	extract($alumno[0]);
}

if ($_REQUEST['guardar'] == "Guardar") {
/*		
	//$fecha_compromiso   = (empty($_REQUEST['fecha_compromiso'])) ? "null" : "'{$_REQUEST['fecha_compromiso']}'";
	$id_motivo = $_REQUEST['id_motivo']; //(empty($_REQUEST['id_motivo_no_remat'])) ? "null" : $_REQUEST['id_motivo_no_remat'];
  $tipo_contacto      = $_REQUEST['tipo_contacto'];
  $obtiene_respuesta  = $_REQUEST['obtiene_respuesta'];
  $obtiene_resuelto  = $_REQUEST['obtiene_resuelto'];
	//$id_operador        = $_SESSION['id_usuario'];
	$comentarios        = $_REQUEST['comentarios'];
	//$semestre_mat       = $SEMESTRE_MATRICULA;
	//$ano_mat            = $ANO_MATRICULA;
  $id_area_derivacion = $_REQUEST['id_area_derivacion'];
*/
  /*
  echo('<br>id_motivo = '.$id_motivo);
  echo('<br>obtiene_respuesta = '.$obtiene_respuesta);
  echo('<br>obtiene_resuelto = '.$obtiene_resuelto);
*/
if ($id_area_derivacion == "") {
  $id_area_derivacion = "null";
}
if ($obtiene_resuelto == 't') {
  $id_area_derivacion = "null";
}

$SQLins_atencion = "update atenciones_proretencion set 
  id_usuario_derivado = $id_operador,
  comentarios_derivado = '$su_comentario',
  resuelto_derivado = '$obtiene_resuelto',
  fecha_derivado = now()
  where id = $id_tabla
  ";


	if (consulta_dml($SQLins_atencion) > 0) {
		echo(msje_js("Se ha registrado satisfactoriamente la atención."));
		//echo(js("window.location='$enlbase_sm=remat_atenciones&id_alumno=$id_alumno';"));
    //echo(js("window.location='$enlbase_sm=registro_atenciones_new&id_alumno=$id_alumno';"));
    echo(js("window.location='$enlbase=atenciones_proretencion';"));
		exit;
	}

}

//$MOTIVOS = consulta_sql("SELECT id,tipo||': '||nombre AS nombre FROM gestion.atenciones_remat_motivos ORDER BY tipo,nombre");
//$MOTIVOS = consulta_sql("SELECT id,nombre AS nombre FROM tipo_motivo_aux ORDER BY nombre");
$MOTIVOS = consulta_sql("
                    select id, nombre from (					 
                      SELECT a.id as id,concat(
                                      (
                                        select b.clasificacion from tipo_motivo_clasif_proretencion b
                                        where b.id = a.id_clasificacion
                                      ),': ',a.nombre) AS nombre 
                      FROM tipo_motivo_proretencion a 
                      --ORDER BY a.nombre
                    )
                    as A
                    order by nombre
                    ");

$TIPOS_CONTACTO = consulta_sql("SELECT id,nombre FROM vista_gar_tipo_contacto");
//$TIPOS_CONTACTO = consulta_sql("SELECT id,nombre FROM tipo_contacto_aux");

$AREA_DERIVACION = consulta_sql("SELECT id,nombre FROM gestion.unidades where proretencion = true order by nombre");

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name='formulario' action='principal.php' method='post'>
<input type='hidden' name='modulo' value='<?php echo($modulo); ?>'>
<input type='hidden' name='id_alumno' value='<?php echo($id_alumno); ?>'>

<div style='margin-top: 5px'>
  <!--<input type="submit" name="guardar" value="Guardar" onClick="if (formulario.fecha_compromiso.value == '' && formulario.id_motivo_no_remat.value == '' && formulario.obtiene_respuesta.value=='t') { alert('ERROR: Debe registrar un Compromiso de rematricula o bien un Motivo de no rematricula'); return false; }"> -->
  <?php if ($modo_ver=="SI") {
  } else {
    ?>
      <input type="submit" name="guardar" value="Guardar" >
  <?php } ?>

  
  <!--<input type="submit" name="id_derivar" value="Derivar">-->

  <input type="button" name="volver" value="Volver" onClick="history.back();">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="6" style="text-align: center; ">Antecedentes Personales del Estudiante :</td></tr>
  
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
	    <select class="filtro" name="tipo_contacto" style='max-width: none' disabled>
        <?php echo(select($TIPOS_CONTACTO,$tipo_contacto)); ?>
      </select>&nbsp;
      <!--
      <input type='radio' name='obtiene_respuesta' value='f' id='obtiene_respuesta_no' onClick='formulario.fecha_compromiso.disabled = true; formulario.id_motivo_no_remat.disabled = true;' required>
      <label for='obtiene_respuesta_no'>Sin respuesta</label>&nbsp;&nbsp;&nbsp;
      <input type='radio' name='obtiene_respuesta' value='t' id='obtiene_respuesta_si' onClick='formulario.fecha_compromiso.disabled = false; formulario.id_motivo_no_remat.disabled = false;' required>
      <label for='obtiene_respuesta_si'>Contactado</label>
      -->
      <?php echo($respuestaOrigen); ?>
    </td>
  </tr>
<!--
  <tr>
    <td class='celdaNombreAttr'><u>Compromiso Rematrícula:</u></td>
    <td class='celdaValorAttr' colspan='3'>
	  <input type='date' min="<?php echo($fecha_compromiso_min); ?>" max="<?php echo($fecha_compromiso_max); ?>" name='fecha_compromiso' class='boton' onBlur="formulario.id_motivo_no_remat.disabled = (this.value !== '');" disabled>
	</td>
  </tr>
-->
  <tr>
    <td class='celdaNombreAttr'><u>Motivo :</u></td>
    <td class='celdaValorAttr' colspan='3'>
	  <select class="filtro" name="id_motivo" style='max-width: none' disabled>
		  <option value=''>-- Seleccione --</option>
      <?php echo(select($MOTIVOS,$id_motivo)); ?>
    </select>
	</td>
  
  </tr>



  <tr>
    <td class='celdaNombreAttr'><u>Comentario Origen:</u></td>
    <td class='celdaValorAttr' colspan='3'><textarea name='comentarios' class='grande' readonly><?php echo($comentarios); ?></textarea></td>
  </tr>
  <tr>
    <?php if ($es_mi_caso == "SI") {
      $str_comentario = "Su comentario:";
    } else {
      $str_comentario = "Comentario resolución:";
    } 
      ?>

    <td class='celdaNombreAttr'><u><?php echo($str_comentario); ?></u></td>
    <?php
      if ($modo_ver=="SI") {
        $atributo = "readonly";
      } else {
        $atributo = "required";
      }
      //echo("COLOCA : $atributo");
    ?>      
    <td class='celdaValorAttr' colspan='3'><textarea name='su_comentario' class='grande' <?php echo($atributo); ?>><?php echo($comentarios_derivado); ?></textarea></td>
  </tr>
  <?php
  if ($modo_ver=="SI") {
     
      } else {
        ?>
              <tr>

            <td class='celdaNombreAttr'><u></u></td>
            <td class='celdaValorAttr' colspan="3">
              Resuelto
                <input type='radio' name='obtiene_resuelto' value='t' id='obtiene_resuelto_si' onClick='formulario.id_area_derivacion.disabled = true' required>
                <label for='obtiene_resuelto_si'>Sí</label>&nbsp;&nbsp;&nbsp;
                <input type='radio' name='obtiene_resuelto' value='f' id='obtiene_resuelto_no'  onClick='formulario.id_area_derivacion.disabled = false' selected required>
                <label for='obtiene_resuelto_no'>No</label>
              </td>

            </tr>
    
    <?php  }
  ?>    
  <input type='hidden' name='id_tabla' value='<?php echo($id_tabla); ?>' id='id_tabla'>
        

  <!--
  <tr>
    <td class='celdaNombreAttr'><u>Área derivación :</u></td>
    <td class='celdaValorAttr' colspan='3'>
	  <select class="filtro" name="id_area_derivacion" id="id_area_derivacion" style='max-width: none'>
		  <option value=''>-- Ninguna --</option>
      <?php echo(select($AREA_DERIVACION,$id_area_derivacion)); ?>
    </select>
	  </td>
  
  </tr>

-->

</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
