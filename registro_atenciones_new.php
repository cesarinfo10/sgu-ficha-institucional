<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_alumno = $_REQUEST['id_alumno'];
$periodo   = $_REQUEST['periodo'];

if (empty($periodo)) { $periodo = "$SEMESTRE_MATRICULA-$ANO_MATRICULA"; } 
list($per_sem,$per_ano) = explode("-",$periodo);

$alumno = consulta_sql("SELECT va.id,trim(va.rut) AS rut,nombre,a.email,a.telefono,a.tel_movil FROM vista_alumnos va LEFT JOIN alumnos a USING(id) WHERE a.id=$id_alumno");
if (count($alumno) == 0) {
	$postulante = consulta_sql("SELECT id,trim(rut) AS rut,nombre FROM vista_pap WHERE rut='$rut'");
	extract($postulante[0]);
} else {
	extract($alumno[0]);
}

$SQL_remat_atenciones = "
                      select 
                      atpr.id id,
                      atpr.id_alumno id_alumno,
                      atpr.id_motivo id_motivo,
                      --(select moti.nombre from tipo_motivo_aux moti where moti.id = atpr.id_motivo) nombre_motivo,
                      --(select moti.nombre from tipo_motivo_proretencion moti where moti.id_clasificacion = atpr.id_motivo) nombre_motivo,
                      (
                        SELECT concat(
                          (
                            select b.clasificacion from tipo_motivo_clasif_proretencion b
                            where b.id = a.id_clasificacion
                          ),': <br>',a.nombre) AS nombre 
                          FROM tipo_motivo_proretencion a 
                          where a.id =  atpr.id_motivo                       
                      ) nombre_motivo,
                      to_char(atpr.fecha,'dd/mm/yyyy') fecha,    
                      to_char(atpr.fecha_derivacion,'dd/mm/yyyy') fecha_derivacion,   
                      atpr.comentarios comentarios,
                      atpr.comentarios_derivado comentarios_derivado,
                      atpr.tipo_contacto tipo_contacto,
                      atpr.respuesta_contacto respuesta_contacto,                      
                      --atpr.resuelto resuelto,
                      (
                        case coalesce(atpr.resuelto_derivado,'f') 
                        when 't' then 't'
                        when 'f' then
                        (
                          case coalesce(atpr.resuelto,'f') 
                          when 't' then 't'
                          when 'f' then 'f'
                          end
                        )
                        end
                      ) resuelto,                      
                      atpr.id_unidad_derivada id_unidad_derivada,
                      (select nombre from gestion.unidades where id = atpr.id_unidad_derivada) glosa_derivado 
                      ,(select concat(nombre,' ',apellido) from usuarios where id = id_usuario_origen) usuario_origen
                      ,(select concat(nombre,' ',apellido) from usuarios where id = id_usuario_derivado)  usuario_derivado                      
                      from atenciones_proretencion atpr
                      where 
                      atpr.id_alumno = $id_alumno
                      order by atpr.fecha desc, atpr.id desc
                      ";
$remat_atenciones = consulta_sql($SQL_remat_atenciones);

for ($x=0;$x<count($remat_atenciones);$x++) {
	extract($remat_atenciones[$x]);

  $compromiso_color = (strtotime($fecha_comp) > time()) ? "green" : "red";

  if ($resuelto=='t') {
        $resueltoFinal = "<span style='color: green'>Sí</span>";
  } else {
    $resueltoFinal = "<span style='color: red'>No</span>";
  }
  if ($respuesta_contacto=='t') {
      $respuesta_contactoFinal = "<span style='color: green'>Contactado</span>";
      
  } else {
    $respuesta_contactoFinal = "<span style='color: red'>Sin Respuesta</span>";
  }
  $mini = "
<a id='sgu_fancybox' 
href='$enlbase_sm=registro_atenciones_agregar_new
&id_tabla=$id
&id_alumno=$id_alumno
&id_motivo=$id_motivo
&tipo_contacto=$tipo_contacto
&obtiene_respuesta=$respuesta_contacto
&obtiene_resuelto=$resuelto
&comentarios=$comentarios
&id_area_derivacion=$id_unidad_derivada
&modo_ver=SI
&comentarios_derivado=$comentarios_derivado' 
>$nombre_motivo</a>";


	$HTML .= "<tr class='filaTabla' style='vertical-align: middle'>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: right; color: gray'>$id</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: center;'><small>$fecha</small></td>\n"
. "  <td class='textoTabla' style='vertical-align: middle' align='left'><small>$usuario_origen</small></td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='left'>$mini</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='center'>$tipo_contacto</td>\n"
      . "  <td class='textoTabla' style='vertical-align: middle' align='center'>$respuesta_contactoFinal</td>\n"
		  //. "  <td class='textoTabla' style='vertical-align: middle' align='center'><span style='color: $compromiso_color'>$fecha_compromiso</span></td>\n"      
      . "  <td class='textoTabla' style='vertical-align: middle' align='center'>$resueltoFinal</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='left'>$glosa_derivado</td>\n"
      . "  <td class='textoTabla' style='vertical-align: middle' align='center'>$fecha_derivacion</td>\n"
. "  <td class='textoTabla' style='vertical-align: middle' align='left'><small>$usuario_derivado</small></td>\n"
/*
      . "  <td class='textoTabla' style='vertical-align: middle'><a id='sgu_fancybox' 
href='$enlbase_sm=registro_atenciones_agregar_new
&id_tabla=$id
&id_alumno=$id_alumno
&id_motivo=$id_motivo
&tipo_contacto=$tipo_contacto
&obtiene_respuesta=$respuesta_contacto
&obtiene_resuelto=$resuelto
&comentarios=$comentarios
&id_area_derivacion=$id_unidad_derivada
&modo_ver=SI
&comentarios_derivado=$comentarios_derivado
' class='boton'>Ver</a></td>\n"
*/
. "</tr>\n";

}

if (count($remat_atenciones) == 0) {
	$HTML = "<tr class='filaTabla' style='vertical-align: middle'>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: center;' colspan='10'>\n"
		  . "    ** No hay atenciones registradas **"
		  . "  </td>\n"
		  . "</tr>\n";
}

$PERIODOS_REMAT = array();
for ($ano=2022;$ano<=date("Y")+1;$ano++) {
	for ($sem=1;$sem<=2;$sem++) {
		$PERIODOS_REMAT = array_merge($PERIODOS_REMAT,array(array('id'=>"$sem-$ano",'nombre'=>"$sem-$ano")));
	}
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name='formulario' action='principal_sm.php' method='get'>
<input type='hidden' name='modulo' value='<?php echo($modulo); ?>'>
<input type='hidden' name='id_alumno' value='<?php echo($id_alumno); ?>'>
<!--<input type='text' name='id_url_padre' id='id_url_padre' >-->


<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="6" style="text-align: center; ">Antecedentes Personales del Estudiante</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Código Interno:</td>
    <td class='celdaValorAttr'><?php echo($id_alumno); ?></td>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($rut); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($nombre); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Teléfono:</td>
    <td class='celdaValorAttr'><?php echo($telefono); ?></td>
    <td class='celdaNombreAttr'>Tel. Móvil:</td>
    <td class='celdaValorAttr'><?php echo($tel_movil); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>e-Mail:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($email); ?></td>
  </tr>
</table>
<table class="tabla" style="margin-top: 5px">
  <tr>
	<td class='celdaFiltro'>
	  Acciones:<br>
      <input type='button' onClick="window.location='<?php echo("$enlbase_sm=registro_atenciones_agregar_new&id_alumno=$id_alumno"); ?>';" value="Agregar">
      <!--
      <input type='button' onClick="window.location='<?php echo("$enlbase_sm=crud_clasificaciones_proretencion&id_alumno=$id_alumno"); ?>';" value="Edita Clasificaciones">
      <input type='button' onClick="window.location='<?php echo("$enlbase_sm=crud_motivos_proretencion&id_alumno=$id_alumno"); ?>';" value="Edita Motivos">
-->

    </td>

    <!--
  	<td class='celdaFiltro'>
      Periodo:<br>
      <select class="filtro" name="periodo" onChange="submitform();">
        <?php echo(select($PERIODOS_REMAT,$periodo)); ?>
      </select>
  	</td>
-->
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style="color: gray">ID</td>
    <td class='tituloTabla'>Fecha</td>
    <td class='tituloTabla'>Usuario Origen</td>
    <td class='tituloTabla'>Motivo</td>
    <td class='tituloTabla'>Tipo Contacto</td>
    <td class='tituloTabla'>Respuesta</td>
    <td class='tituloTabla'>Resuelto</td>
    <td class='tituloTabla'>Derivación</td>
    <td class='tituloTabla'>Fecha derivación</td>
    <td class='tituloTabla'>Usuario Derivado</td>

  </tr>
  <?php echo($HTML); ?>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
<script>
  /*
  function obtieneUrlPadre() {
    myurl = window.location.href;
    $("#id_url_padre").val(myurl);
  }
  $( document ).ready(function() {
    obtieneUrlPadre();
  });
  */
</script>
