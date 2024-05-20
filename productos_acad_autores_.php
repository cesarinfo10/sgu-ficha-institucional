<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_actividad  = $_REQUEST['id_actividad'];
$id_tipo_ind   = $_REQUEST['id_tipo_ind'];
$elim_id       = $_REQUEST['elim_id'];

if ($id_tipo_ind > 0) {
    consulta_dml("INSERT INTO vcm.indicadores_act VALUES (default,$id_actividad,$id_tipo_ind,null)");
    consulta_dml("INSERT INTO vcm.actividades_audit VALUES ($id_actividad,{$_SESSION['id_usuario']},'Agrega indicador de Actividad',default)");
}

if ($elim_id > 0) {
    consulta_dml("DELETE FROM vcm.indicadores_act WHERE id=$elim_id");
    consulta_dml("INSERT INTO vcm.actividades_audit VALUES ($id_actividad,{$_SESSION['id_usuario']},'Elimina indicador de Actividad',default)");
}

if ($_REQUEST["guardar"] == "💾 Guardar") {
    $indicadores = $_REQUEST['indicadores'];
    $SQL_upd = "";
    foreach($indicadores AS $id_tipo_ind => $valor) {
        $SQL_upd .= "UPDATE vcm.indicadores_act SET valor=$valor WHERE id=$id_tipo_ind;";
    }
    if (consulta_dml($SQL_upd) > 0) {
        consulta_dml("INSERT INTO vcm.actividades_audit VALUES ($id_actividad,{$_SESSION['id_usuario']},'Ingreso/modificación de los indicadores',default)");
		echo(msje_js("Se han guardado exitosamente los datos."));
		echo(js("parent.jQuery.fancybox.close();"));
    }
}

$SQL_act = "SELECT *,
                   to_char(fecha_inicio,'DD-tmMonth-YYYY HH24:MI') AS fec_inicio,
                   to_char(fecha_termino,'DD-tmMonth-YYYY HH24:MI') AS fec_termino,
                   dimension,array_to_string(difusion,',') AS difusion,
                   coalesce(nombre_unidad1,'')||','||coalesce(nombre_unidad2,'')||','||coalesce(nombre_unidad3,'') AS unidades
            FROM vista_vcm_actividades AS act
            WHERE id=$id_actividad";
$act = consulta_sql($SQL_act);

if (count($act) == 1) {
    
    $INDICADORES = consulta_sql("SELECT id,nombre FROM vcm.indicadores_act_tipo ORDER BY nombre");

    $indicadores = consulta_sql("SELECT ind.*,it.nombre,porcentaje FROM vcm.indicadores_act AS ind LEFT JOIN vcm.indicadores_act_tipo AS it ON it.id=ind.id_tipo WHERE id_actividad=$id_actividad ORDER BY it.nombre");

    if (count($indicadores) > 0) { 
        $ids_ind = implode(",",array_column($indicadores,"id_tipo"));
        $INDICADORES = consulta_sql("SELECT id,nombre FROM vcm.indicadores_act_tipo WHERE id NOT IN ($ids_ind) ORDER BY nombre");
    }

    $HTML = $HTML_indicadores = "";    
    for($x=0;$x<count($indicadores);$x++) {
        $enl_elim = "$enlbase_sm=$modulo&id_actividad=$id_actividad&elim_id={$indicadores[$x]['id']}";
        $elim = "<a class='enlaces' href='#' onClick=\"if (confirm('Desea eliminar este indicador ({$indicadores[$x]['nombre']}) de la actividad?')) { location.href='$enl_elim'; } \"><big style='color: red'>✗</big></a>";
        
        $porcentaje = ($indicadores[$x]['porcentaje'] == 't') ? "<b>%</b>" : "";

        $HTML .= "<tr class='filaTabla'>\n"
              .  "  <td class='textoTabla' align='right'>$elim<label for='indicadores[{$indicadores[$x]['id']}]'>{$indicadores[$x]['nombre']}:</label></td>\n"
              .  "  <td class='textoTabla'><input type='number' style='width: 30px' class='montos' id='indicadores[{$indicadores[$x]['id']}]' name='indicadores[{$indicadores[$x]['id']}]' value='{$indicadores[$x]['valor']}' required>$porcentaje</td>\n"
              .  "</tr>\n";
    }
    $HTML .= "<tr class='filaTabla'>\n"
          .  "  <td class='textoTabla' colspan='2' align='center'>\n"
          .  "    <select name='id_tipo_ind' class='filtro' style='max-width: none' onChange='submitform();'>\n"
          .  "      <option value=''>-- Agregar --</option>\n"
          .         select($INDICADORES,"")
          .  "    </select>\n"
          .  "  </td>\n"
          .  "</tr>\n";
    $HTML_indicadores = $HTML;

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
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Indicadores</td></tr>
  <tr>
    <td class='celdaValorAttr' colspan="4">
      <table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
        <tr class='filaTituloTabla'>
	      <td class='tituloTabla'>Nombre</td>
	      <td class='tituloTabla'>Resultado</td>
	    </tr>
        <?php echo($HTML_indicadores); ?>
      </table>
    </td>
  </tr>

</table>
</form>