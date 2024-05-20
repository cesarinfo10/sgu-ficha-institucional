<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_actividad  = $_REQUEST['id_actividad'];
$elim_id       = $_REQUEST['elim_id'];

if ($elim_id > 0) {
    consulta_dml("DELETE FROM vcm.participacion_act WHERE id=$elim_id");
    consulta_dml("INSERT INTO vcm.actividades_audit VALUES ($id_actividad,{$_SESSION['id_usuario']},'Elimina tipo de público de Actividad',default)");
    consulta_dml("UPDATE vcm.actividades SET tipo_publico=(SELECT array_agg(tipo_publico) FROM vcm.participacion_act WHERE id_actividad=$id_actividad) WHERE id=$id_actividad");
}

if ($_REQUEST["guardar"] == "💾 Guardar") {
    $participacion_presencial = $_REQUEST['tipo_publico_presencial'];
    $participacion_virtual    = $_REQUEST['tipo_publico_virtual'];
    $SQL_upd = "";
    foreach($participacion_presencial AS $id_part_act => $cant_personas) {
        $SQL_upd .= "UPDATE vcm.participacion_act SET cant_personas=$cant_personas WHERE id=$id_part_act;";
    }
    foreach($participacion_virtual AS $id_part_act => $cant_personas) {
        $SQL_upd .= "UPDATE vcm.participacion_act SET cant_personas_virtuales=$cant_personas WHERE id=$id_part_act;";
    }
    if (consulta_dml($SQL_upd) > 0) {
        consulta_dml("INSERT INTO vcm.actividades_audit VALUES ($id_actividad,{$_SESSION['id_usuario']},'Ingreso/modificación de la participación',default)");
		echo(msje_js("Se han guardado exitosamente los datos."));
		echo(js("parent.jQuery.fancybox.close();"));
    }
}

$SQL_asist = "SELECT char_comma_sum(tipo_publico||': '||coalesce(cant_personas::text,'*')) 
              FROM vcm.participacion_act
			  WHERE id_actividad=act.id";

$SQL_doctos = "SELECT char_comma_sum(doc_tipo.nombre||':'||doctos.id::text) 
               FROM vcm.documentos_act AS doctos
			   LEFT JOIN vcm.documentos_act_tipo AS doc_tipo ON doc_tipo.id=doctos.id_tipo
               WHERE id_actividad=act.id";

$SQL_act = "SELECT *,
                   to_char(fecha_inicio,'DD-tmMonth-YYYY HH24:MI') AS fec_inicio,
                   to_char(fecha_termino,'DD-tmMonth-YYYY HH24:MI') AS fec_termino,
                   dimension,($SQL_asist) AS tipo_publico,array_to_string(difusion,',') AS difusion,
                   coalesce(nombre_unidad1,'')||','||coalesce(nombre_unidad2,'')||','||coalesce(nombre_unidad3,'') AS unidades
            FROM vista_vcm_actividades AS act
            WHERE id=$id_actividad";
$act = consulta_sql($SQL_act);

if (count($act) == 1) {

    $asistencia = consulta_sql("SELECT * FROM vcm.participacion_act WHERE id_actividad=$id_actividad ORDER BY tipo_publico");

    $HTML_cant_publico = "";
    $HTML_cant_publico_presencial = "<td class='tituloTabla'>Presenciales</td>";
    $HTML_cant_publico_virtual    = "<td class='tituloTabla'>Virtuales</td>";
    if ($act[0]['modalidad'] == "Presencial") { $HTML_cant_publico .= $HTML_cant_publico_presencial; }
    if ($act[0]['modalidad'] == "Virtual")    { $HTML_cant_publico .= $HTML_cant_publico_virtual; }
    if ($act[0]['modalidad'] == "Híbrido")    { $HTML_cant_publico .= $HTML_cant_publico_presencial . $HTML_cant_publico_virtual; }

    $HTML = $HTML_tipo_publico_asistencia = "";
    $HTML = "<table class='tabla'>\n"
          . "<tr class='filaTituloTabla'><td class='tituloTabla'>Tipo Público</td>$HTML_cant_publico</tr>";
    for($x=0;$x<count($asistencia);$x++) {

        $enl_elim = "$enlbase_sm=$modulo&id_actividad=$id_actividad&elim_id={$asistencia[$x]['id']}";
        $elim = "<a class='enlaces' href='#' onClick=\"if (confirm('Desea eliminar este tipo de público ({$asistencia[$x]['tipo_publico']}) de la actividad?')) { location.href='$enl_elim'; } \"><big style='color: red'>✗</big></a>";

        $HTML_cant_publico = "";
        $HTML_cant_publico_presencial = "<td class='textoTabla' align='center'>"
                                      . "  <input type='number' style='width: 30px' class='montos' 
                                                  name='tipo_publico_presencial[{$asistencia[$x]['id']}]' 
                                                  value='{$asistencia[$x]['cant_personas']}' required>"
                                      . "</td>\n";
        $HTML_cant_publico_virtual    = "<td class='textoTabla' align='center'>"
                                      . "  <input type='number' style='width: 30px' class='montos' 
                                                  name='tipo_publico_virtual[{$asistencia[$x]['id']}]' 
                                                  value='{$asistencia[$x]['cant_personas_virtuales']}' required>"
                                      . "</td>\n";
        if ($act[0]['modalidad'] == "Presencial") { $HTML_cant_publico .= $HTML_cant_publico_presencial; }
        if ($act[0]['modalidad'] == "Virtual")    { $HTML_cant_publico .= $HTML_cant_publico_virtual; }
        if ($act[0]['modalidad'] == "Híbrido")    { $HTML_cant_publico .= $HTML_cant_publico_presencial . $HTML_cant_publico_virtual; }
    
        $HTML .= "<tr class='filaTabla'>\n"
              .  "  <td class='textoTabla' align='right'>$elim<label for='tipo_publico[{$asistencia[$x]['id']}]'>{$asistencia[$x]['tipo_publico']}:</label></td>\n"
              .     $HTML_cant_publico
              .  "</tr>\n";
    }
    $HTML .= "</table>\n";
    $HTML_tipo_publico_asistencia = $HTML;

    $_REQUEST = array_merge($act[0],$_REQUEST);
    $_REQUEST['tipo_publico'] = str_replace(",","<br>",$_REQUEST['tipo_publico']);
}

?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_actividad" value="<?php echo($id_actividad); ?>">
<div style='margin-top: 5px'>
  <input type="submit" name='guardar' value="💾 Guardar">
  <input type="button" name='cancelar' value="❌ Cancelar" onClick="parent.jQuery.fancybox.close();">
</div>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Actividad</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['nombre']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Objetivo:</label></td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['objetivo']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Dimensión/Tipo:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['dimension']." / ".$_REQUEST['nombre_tipo_act']); ?></td>
    <td class='celdaNombreAttr'>Alcance:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['alcance']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Año:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['ano']); ?></td>
    <td class='celdaNombreAttr'>Modalidad:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['modalidad']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha y hora de Inicio:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['fec_inicio']); ?></td>
    <td class='celdaNombreAttr'>Fecha y hora de Término:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['fec_termino']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Público Objetivo:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($HTML_tipo_publico_asistencia); ?></td>
  </tr>
</table>
</form>