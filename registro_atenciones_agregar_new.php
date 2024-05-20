<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_alumno = $_REQUEST['id_alumno'];
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

$fecha_compromiso   = (empty($_REQUEST['fecha_compromiso'])) ? "null" : "'{$_REQUEST['fecha_compromiso']}'";
$id_motivo = $_REQUEST['id_motivo']; //(empty($_REQUEST['id_motivo_no_remat'])) ? "null" : $_REQUEST['id_motivo_no_remat'];
$tipo_contacto      = $_REQUEST['tipo_contacto'];
$obtiene_respuesta  = $_REQUEST['obtiene_respuesta'];
$obtiene_resuelto  = $_REQUEST['obtiene_resuelto'];
$id_operador        = $_SESSION['id_usuario'];
$comentarios        = $_REQUEST['comentarios'];
$comentarios_derivado = $_REQUEST['comentarios_derivado'];
$semestre_mat       = $SEMESTRE_MATRICULA;
$ano_mat            = $ANO_MATRICULA;
$id_area_derivacion = $_REQUEST['id_area_derivacion'];
$modo_ver        = $_REQUEST['modo_ver'];

if ($modo_ver == "SI") {
  $varAtributo = "disabled";  
} else {
  $varAtributo = "required";
}

if ($obtiene_respuesta='t') {
  $respuestaOrigen = 'Contactado';
} else {
  $respuestaOrigen = 'Sin respuesta';
}

if ($_REQUEST['guardar'] == "Guardar") {
/*		
	$fecha_compromiso   = (empty($_REQUEST['fecha_compromiso'])) ? "null" : "'{$_REQUEST['fecha_compromiso']}'";
	$id_motivo = $_REQUEST['id_motivo']; //(empty($_REQUEST['id_motivo_no_remat'])) ? "null" : $_REQUEST['id_motivo_no_remat'];
  $tipo_contacto      = $_REQUEST['tipo_contacto'];
  $obtiene_respuesta  = $_REQUEST['obtiene_respuesta'];
  $obtiene_resuelto  = $_REQUEST['obtiene_resuelto'];
	$id_operador        = $_SESSION['id_usuario'];
	$comentarios        = $_REQUEST['comentarios'];
  $comentarios_derivado = $_REQUEST['comentarios_derivado'];
	$semestre_mat       = $SEMESTRE_MATRICULA;
	$ano_mat            = $ANO_MATRICULA;
  $id_area_derivacion = $_REQUEST['id_area_derivacion'];
  $modo_ver        = $_REQUEST['modo_ver'];
  */
/*
  echo('<br>id_motivo = '.$id_motivo);
  echo('<br>obtiene_respuesta = '.$obtiene_respuesta);
  echo('<br>obtiene_resuelto = '.$obtiene_resuelto);
*/
//echo("* * *MODO_VER=".$modo_ver);
//echo('<br>id_area_derivacion = '.$id_area_derivacion);

if ($id_area_derivacion == "") {
  $id_area_derivacion = "null";
  $fecha_derivacion = "null";
} else {
  $fecha_derivacion = "now()";
}
if ($obtiene_resuelto == 't') {
  $id_area_derivacion = "null";
}

	$SQLins_atencion = "INSERT INTO atenciones_proretencion (
                                                        id_alumno,
                                                        id_motivo,
                                                        comentarios,
                                                        tipo_contacto,
                                                        respuesta_contacto,
                                                        resuelto,
                                                        id_unidad_derivada,
                                                        id_usuario_origen,
                                                        fecha_derivacion
                                                        )
							VALUES ($id_alumno,
                      $id_motivo,
                      '$comentarios',
                      '$tipo_contacto',
                      '$obtiene_respuesta',
                      '$obtiene_resuelto',
                      $id_area_derivacion,
                      $id_operador,
                      $fecha_derivacion
                      );";
	if (consulta_dml($SQLins_atencion) > 0) {

/*
BUSCAR  A LOS USUARIOS PARA ENVIAR CORREO DERIVACION DE AREA
*/
    if ($id_area_derivacion <> "") {
      $SQL_correo = "select email as email_usuario from usuarios where id_unidad = $id_area_derivacion and activo = 't' and email is not null";
      $envio_correo = consulta_sql($SQL_correo);
      for ($x=0;$x<count($envio_correo);$x++) {
            extract($envio_correo[$x]);
            /*AQUI DEBE ENVIAR CORREO*/
            //chemp
            $sql_motivo = "select tmp.id as id_motivo, tmp.nombre as nombre_motivo, tmcp.clasificacion as grupo_motivo from tipo_motivo_proretencion as tmp
            left join tipo_motivo_clasif_proretencion as tmcp on tmcp.id = tmp.id_clasificacion
            and tmp.id = $id_motivo";
            $my_motivo = consulta_sql($sql_motivo);
            extract($my_motivo[0]);

            
            $sql_operador = "select nombre_usuario as nombre_usuario_operador, nombre as nombre_operador, apellido as apellido_operador from usuarios where id = $id_operador";
            $my_operador = consulta_sql($sql_operador);
            extract($my_operador[0]);




            $asunto = "SGU: Derivación respecto al estudiante : $rut - $nombre";
            $cuerpo = "Se le informa que a su área ha llegado una derivación asociada al estudiante : \n\n\n $rut - $nombre. \n";
            $cuerpo .= "Código Interno : $id \n";            
            $cuerpo .= "Teléfono : $telefono \n";
            $cuerpo .= "Tel Móvil : $tel_movil \n";
            $cuerpo .= "e-Mail : $email \n";
            $cuerpo .= "\n";
            $cuerpo .= "Grupo Motivo Asociado  : $grupo_motivo\n";
            $cuerpo .= "Motivo  : $nombre_motivo\n";
            $cuerpo .= "\n";
            $cuerpo .= "Comentario  : \n";
            $cuerpo .= "$comentarios \n";
            $cuerpo .= "\n\n\n";

            $cuerpo .= "Usuario quien informa esta situación : \n"; 
            $cuerpo .= "id : $id_operador \n";
            $cuerpo .= "usuario : $nombre_usuario_operador \n";
            $cuerpo .= "nombre : $nombre_operador $apellido_operador \n";
            $cuerpo .= "\n\n\n";
            $cuerpo .= "\n\n";
            $cuerpo .= "Este es un correo automático, favor no responder.";

            $cabeceras = "From: SGU" . "\r\n"
                  . "Content-Type: text/plain;charset=utf-8" . "\r\n";

            mail($email_usuario,$asunto,$cuerpo,$cabeceras);
             
            //mail("rmazuela@corp.umc.cl",$asunto,$cuerpo,$cabeceras);
            echo(msje_js("Correo para $email_usuario")); 
      }
      
    }

		echo(msje_js("Se ha registrado satisfactoriamente la atención."));
    echo(js("window.location='$enlbase_sm=registro_atenciones_new&id_alumno=$id_alumno';"));
		exit;
	}

}

//$MOTIVOS = consulta_sql("SELECT id,tipo||': '||nombre AS nombre FROM gestion.atenciones_remat_motivos ORDER BY tipo,nombre");
//$MOTIVOS = consulta_sql("SELECT id,concat(clasificacion,': ',nombre) AS nombre FROM tipo_motivo_aux ORDER BY nombre");
/*
$MOTIVOS = consulta_sql("
                    select id, nombre, grupo from (					 
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
*/
$MOTIVOS = consulta_sql("
select tmp.id as id, tmp.nombre as nombre, tmcp.clasificacion as grupo from tipo_motivo_proretencion as tmp
left join tipo_motivo_clasif_proretencion as tmcp on tmcp.id = tmp.id_clasificacion
order by tmcp.clasificacion, tmp.nombre
");
$TIPOS_CONTACTO = consulta_sql("SELECT id,nombre FROM vista_gar_tipo_contacto");
//$TIPOS_CONTACTO = consulta_sql("SELECT id,nombre FROM tipo_contacto_aux");

$AREA_DERIVACION = consulta_sql("SELECT id,nombre FROM gestion.unidades where proretencion = true order by nombre");

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name='formulario' action='principal_sm.php' method='post'>
<input type='hidden' name='modulo' value='<?php echo($modulo); ?>'>
<input type='hidden' name='id_alumno' value='<?php echo($id_alumno); ?>'>

<div style='margin-top: 5px'>
  <!--<input type="submit" name="guardar" value="Guardar" onClick="if (formulario.fecha_compromiso.value == '' && formulario.id_motivo_no_remat.value == '' && formulario.obtiene_respuesta.value=='t') { alert('ERROR: Debe registrar un Compromiso de rematricula o bien un Motivo de no rematricula'); return false; }"> -->
  <?php 
  if ($modo_ver == "SI") {    

  } else {
    echo("<input type='submit' name='guardar' value='Guardar' onClick=javascript:verificarDatos();>");
  }
  ?>
  
  <!--<input type="submit" name="id_derivar" value="Derivar">-->

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
      <?php
        if ($modo_ver == "SI") {
          echo("<select class='filtro' name='tipo_contacto' style='max-width: none' disabled>");
          echo(select($TIPOS_CONTACTO,$tipo_contacto));
          echo("</select>&nbsp;");
          echo($respuestaOrigen);
        } else {
?>
                <select class="filtro" name="tipo_contacto" style='max-width: none' required>
                <?php echo(select($TIPOS_CONTACTO,$tipo_contacto)); ?>
                </select>&nbsp;
                <input type='radio' name='obtiene_respuesta' value='f' id='obtiene_respuesta_no' onClick='formulario.fecha_compromiso.disabled = true; formulario.id_motivo_no_remat.disabled = true;' required>
                <label for='obtiene_respuesta_no'>Sin respuesta</label>&nbsp;&nbsp;&nbsp;
                <input type='radio' name='obtiene_respuesta' value='t' id='obtiene_respuesta_si' onClick='formulario.fecha_compromiso.disabled = false; formulario.id_motivo_no_remat.disabled = false;' required>
                <label for='obtiene_respuesta_si'>Contactado</label>

          <?php
        }
      ?>
	    <!--<select class="filtro" name="tipo_contacto" style='max-width: none' required> 
      <?php //echo(select($TIPOS_CONTACTO,$tipo_contacto)); ?>
      </select>&nbsp;
      <input type='radio' name='obtiene_respuesta' value='f' id='obtiene_respuesta_no' onClick='formulario.fecha_compromiso.disabled = true; formulario.id_motivo_no_remat.disabled = true;' required>
      <label for='obtiene_respuesta_no'>Sin respuesta</label>&nbsp;&nbsp;&nbsp;
      <input type='radio' name='obtiene_respuesta' value='t' id='obtiene_respuesta_si' onClick='formulario.fecha_compromiso.disabled = false; formulario.id_motivo_no_remat.disabled = false;' required>
      <label for='obtiene_respuesta_si'>Contactado</label>
      -->
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
      <?php
        if ($modo_ver == "SI") { ?>
          <select class="filtro" name="id_motivo" style='max-width: none' disabled>
        <?php } else { ?>
        <select class="filtro" name="id_motivo" style='max-width: none' >
      <?php   } ?>
  
	  
		  <option value=''>-- Seleccione --</option>
      <?php echo(select_group($MOTIVOS,$id_motivo)); ?>
    </select>
	</td>
  
  </tr>



  <tr>
    <td class='celdaNombreAttr'><u>Comentarios:</u></td>
    <td class='celdaValorAttr' colspan='3'><textarea name='comentarios' class='grande' <?php echo($varAtributo); ?>><?php echo($comentarios); ?></textarea></td>
  </tr>
  <?php
  if ($modo_ver=="SI") { ?>
          <tr>
            <td class='celdaNombreAttr'><u>Respuesta comentario derivado:</u></td>
            <td class='celdaValorAttr' colspan='3'><textarea name='comentarios_derivado' class='grande' <?php echo($varAtributo); ?>><?php echo($comentarios_derivado); ?></textarea></td>
          </tr>
  <?php } ?>


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
<!--
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
    -->
  
  <tr>
    <td class='celdaNombreAttr'><u>Área derivación :</u></td>
    <td class='celdaValorAttr' colspan='3'>
    <?php
        if ($modo_ver == "SI") { ?>
	            <select class="filtro" name="id_area_derivacion" id="id_area_derivacion" style='max-width: none' disabled>
    <?php } else { ?>
              <select class="filtro" name="id_area_derivacion" id="id_area_derivacion" style='max-width: none'>
      <?php } ?>
		  <option value=''>-- Ninguna --</option>
      <?php echo(select($AREA_DERIVACION,$id_area_derivacion)); ?>
    </select>
	  </td>
  
  </tr>

</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->

<script>
  function verificarDatos() {
    obtiene_resuelto_si = $("#obtiene_resuelto_si").value();
    alert("resuelto = " + obtiene_resuelto_si);
    return false;
  }
</script>