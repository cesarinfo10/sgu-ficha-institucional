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

$SQL_remat_atenciones = "SELECT grma.id,
                                to_char(grma.fecha,'DD-tmMon-YYYY HH24:MI') AS fecha,
                                u.nombre_usuario AS operador,
                                gu.alias AS unidad_operador,
                                semestre_mat||'-'||ano_mat AS periodo_mat,
                                tipo_contacto,
                                CASE WHEN obtiene_respuesta THEN 'Contactado' ELSE 'Sin respuesta' END AS obtiene_respuesta,
                                to_char(fecha_compromiso,'tmDay DD-tmMon-YYYY') AS fecha_compromiso,fecha_compromiso AS fecha_comp,
                                grmam.tipo||'<br>'||grmam.nombre AS motivo_no_remat,
                                comentarios
                         FROM gestion.atenciones_remat AS grma
                         LEFT JOIN usuarios AS u ON u.id=grma.id_operador
                         LEFT JOIN gestion.unidades AS gu ON gu.id=u.id_unidad
                         LEFT JOIN gestion.atenciones_remat_motivos AS grmam ON grmam.id=grma.id_motivo_no_remat
                         WHERE ano_mat=$per_ano AND semestre_mat=$per_sem AND id_alumno=$id_alumno
                         ORDER BY grma.fecha DESC";
$remat_atenciones = consulta_sql($SQL_remat_atenciones);

for ($x=0;$x<count($remat_atenciones);$x++) {
	extract($remat_atenciones[$x]);

  $compromiso_color = (strtotime($fecha_comp) > time()) ? "green" : "red";
		
	$HTML .= "<tr class='filaTabla' style='vertical-align: middle'>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: right; color: gray'>$id</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: center;'><small>$fecha<br>$operador ($unidad_operador)</small></td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='center'>$periodo_mat</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='center'>$tipo_contacto<br>$obtiene_respuesta</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='center'><span style='color: $compromiso_color'>$fecha_compromiso</span></td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle'>$motivo_no_remat</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle'><small>$comentarios</small></td>\n"
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
      <input type='button' onClick="window.location='<?php echo("$enlbase_sm=remat_atenciones_agregar&id_alumno=$id_alumno"); ?>';" value="Agregar">
    </td>
  	<td class='celdaFiltro'>
      Periodo:<br>
      <select class="filtro" name="periodo" onChange="submitform();">
        <?php echo(select($PERIODOS_REMAT,$periodo)); ?>
      </select>
  	</td>
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style="color: gray">ID</td>
    <td class='tituloTabla'>Fecha<br>Operador</td>
    <td class='tituloTabla'>Periodo</td>
    <td class='tituloTabla'>Contacto</td>
    <td class='tituloTabla'>Compromiso<br>Rematricula</td>
    <td class='tituloTabla'>Motivo<br>no rematricula</td>
    <td class='tituloTabla'>Comentarios</td>
  </tr>
  <?php echo($HTML); ?>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
