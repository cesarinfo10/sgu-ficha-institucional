<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_actividad  = $_REQUEST['id_actividad'];
$id_tipo_docto = $_REQUEST['id_tipo_docto'];
$elim_id       = $_REQUEST['elim_id'];

if ($id_tipo_docto > 0) {
    echo(js("location.href='$enlbase_sm=actividades_vcm_doctos_subir&id_actividad=$id_actividad&id_tipo_docto=$id_tipo_docto';"));
}

if ($elim_id > 0) {
    consulta_dml("DELETE FROM vcm.documentos_act WHERE id=$elim_id");
    consulta_dml("INSERT INTO vcm.actividades_audit VALUES ($id_actividad,{$_SESSION['id_usuario']},'Elimina documento de Actividad',default)");
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
    
    $TIPOS_DOCTOS = consulta_sql("SELECT id,nombre FROM vcm.documentos_act_tipo ORDER BY nombre");

    $SQL_doctos = "SELECT doctos.id,to_char(doctos.fecha_reg,'DD-tmMon-YYYY HH24:MI') AS fecha_reg,u.nombre_usuario,dt.nombre,
                          pg_size_pretty(length(archivo)::bigint) AS tamano,id_tipo
                   FROM vcm.documentos_act AS doctos 
                   LEFT JOIN vcm.documentos_act_tipo AS dt ON dt.id=doctos.id_tipo 
                   LEFT JOIN usuarios AS u ON u.id=doctos.id_usuario_reg
                   WHERE id_actividad=$id_actividad 
                   ORDER BY dt.nombre";
    $documentos = consulta_sql($SQL_doctos);

    if (count($documentos) > 0) { 
        $ids_doctos = implode(",",array_column($documentos,"id_tipo"));
        $TIPOS_DOCTOS = consulta_sql("SELECT id,nombre FROM vcm.documentos_act_tipo WHERE id NOT IN ($ids_doctos) ORDER BY nombre");
    }

    $HTML = $HTML_doctos = "";    
    for($x=0;$x<count($documentos);$x++) {
        $enl_elim = "$enlbase_sm=$modulo&id_actividad=$id_actividad&elim_id={$documentos[$x]['id']}";
        $elim = "<a class='enlaces' href='#' onClick=\"if (confirm('Desea desvincular este documento ({$documentos[$x]['nombre']}) de la actividad?')) { location.href='$enl_elim'; } \"><big style='color: red'>✗</big></a>";
        
        $HTML .= "<tr class='filaTabla'>\n"
              .  "  <td class='textoTabla'>$elim {$documentos[$x]['nombre']}</td>\n"
              .  "  <td class='textoTabla' align='rigth'>{$documentos[$x]['tamano']}</td>\n"
              .  "  <td class='textoTabla' align='center'>{$documentos[$x]['fecha_reg']}</td>\n"
              .  "  <td class='textoTabla'>{$documentos[$x]['nombre_usuario']}</td>\n"
              .  "</tr>\n";
    }
    $HTML .= "<tr class='filaTabla'>\n"
          .  "  <td class='textoTabla' colspan='4' align='center'>\n"
          .  "    <select name='id_tipo_docto' class='filtro' style='max-width: none' onChange='submitform();'>\n"
          .  "      <option value=''>-- Agregar --</option>\n"
          .         select($TIPOS_DOCTOS,"")
          .  "    </select>\n"
          .  "  </td>\n"
          .  "</tr>\n";
    $HTML_doctos = $HTML;

    $_REQUEST = array_merge($act[0],$_REQUEST);
}

?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_actividad" value="<?php echo($id_actividad); ?>">

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
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Documentos</td></tr>
  <tr>
    <td class='celdaValorAttr' colspan="4">
      <table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
        <tr class='filaTituloTabla'>
	      <td class='tituloTabla'>Nombre</td>
	      <td class='tituloTabla'>Tamaño</td>
	      <td class='tituloTabla'>Fecha</td>
	      <td class='tituloTabla'>Operador</td>
	    </tr>
        <?php echo($HTML_doctos); ?>
      </table>
    </td>
  </tr>

</table>
</form>