<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$rut       = $_REQUEST['rut'];
$eliminado = $_REQUEST['eliminado'];

if (empty($_REQUEST['eliminado'])) { $eliminado = "f"; } 

$alumno = consulta_sql("SELECT id,trim(rut) AS rut,nombre FROM vista_alumnos WHERE rut='$rut'");
if (count($alumno) == 0) {
	$postulante = consulta_sql("SELECT id,trim(rut) AS rut,nombre FROM vista_pap WHERE rut='$rut'");
	extract($postulante[0]);
} else {
	extract($alumno[0]);
}

if ($_REQUEST['eliminar'] === "Eliminar documentos Seleccionados") {
	$aIds_doctos = array();
	$x = 0;
	foreach ($_REQUEST['id_docto'] AS $id_docto => $valor) {
		if ($valor == "on") { 
			$aIds_doctos[$x] = $id_docto;
			$x++;
		 }
	 }
	 $ids_doctos = implode(",",$aIds_doctos);
	 
	 consulta_dml("UPDATE doctos_digitalizados SET eliminado=true WHERE id IN ($ids_doctos)");	
}

if ($_REQUEST['restaurar'] === "Restaurar documentos Seleccionados") {
	$aIds_doctos = array();
	$x = 0;
	foreach ($_REQUEST['id_docto'] AS $id_docto => $valor) {
		if ($valor == "on") { 
			$aIds_doctos[$x] = $id_docto;
			$x++;
		 }
	 }
	 $ids_doctos = implode(",",$aIds_doctos);
	 
	 consulta_dml("UPDATE doctos_digitalizados SET eliminado=false WHERE id IN ($ids_doctos)");	
}

$cond = "";
if ($eliminado == "t" || $eliminado == "f") { $cond = "AND eliminado='$eliminado'"; }

$SQL_doctos = "SELECT dd.id,ddt.nombre AS contenido,to_char(fecha,'DD-MM-YYYY HH24:MI') AS fecha,u.nombre_usuario AS usuario,
                      (length(archivo)::float/1024::float)::numeric(5,1) AS tamano
               FROM doctos_digitalizados AS dd 
               LEFT JOIN doctos_digital_tipos AS ddt ON ddt.id=id_tipo
               LEFT JOIN usuarios AS u ON u.id=id_usuario 
               WHERE dd.rut='$rut' $cond
               ORDER BY dd.fecha DESC";
$doctos = consulta_sql($SQL_doctos);

for ($x=0;$x<count($doctos);$x++) {
	extract($doctos[$x]);

	$elim = "";
	if ($_SESSION['tipo_usuario'] == 0) {
		$elim = "<input type='checkbox' id='marcar' name='id_docto[$id]'>";
	}
	
	$contenido = "<a href='doctos_digitalizados_ver.php?id=$id' target='_blank' class='enlaces'>$contenido</a>";
	
	$HTML .= "<tr class='filaTabla' style='vertical-align: middle'>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: center;'>$elim</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle'>$contenido</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle' align='right'>$fecha</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle'>$usuario</td>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: right'>$tamano KB</td>\n"
		  . "</tr>\n";

}

if (count($doctos) == 0) {
	$HTML = "<tr class='filaTabla' style='vertical-align: middle'>\n"
		  . "  <td class='textoTabla' style='vertical-align: middle; text-align: center;' colspan='5'>\n"
		  . "    ** No hay documentos registrados. **"
		  . "  </td>\n"
		  . "</tr>\n";
}

$ELIMINADO = array(array('id'=>"f",'nombre'=>"Activos"),
                   array('id'=>"t",'nombre'=>"Eliminados"));

verif_estado_carpeta_doctos($rut) 
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name='formulario' action='principal_sm.php' method='get'>
<input type='hidden' name='modulo' value='<?php echo($modulo); ?>'>
<input type='hidden' name='rut' value='<?php echo($rut); ?>'>

<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="6" style="text-align: center; ">Antecedentes Personales del (la) Postulante/Alumno(a)</td></tr>
  <tr>
    <td class='celdaNombreAttr'><u>RUT:</u></td>
    <td class='celdaValorAttr'><?php echo($rut); ?></td>
    <td class='celdaNombreAttr'><u>Nombre:</u></td>
    <td class='celdaValorAttr' colspan="3"><?php echo($nombre); ?></td>
  </tr>
</table>
<table class="tabla" style="margin-top: 5px">
  <tr>
	<td class='celdaFiltro'>
	  Acciones:<br>
      <input type='button' onClick="window.location='<?php echo("$enlbase_sm=doctos_digitalizados_agregar&rut=$rut"); ?>';" value="Agregar">
      <?php if ($eliminado == 'f') { ?>
      <input type='submit' name='eliminar' value='Eliminar documentos Seleccionados'
          onClick="return confirm('¿Está seguro de eliminar los documentos seleccionados?');">
       <?php } else { ?>
      <input type='submit' name='restaurar' value='Restaurar documentos Seleccionados'
          onClick="return confirm('¿Está seguro de restaurar los documentos seleccionados?');">
       <?php } ?>
    </td>
  	<td class='celdaFiltro'>
      Ver doctos:<br>
      <select class="filtro" name="eliminado" onChange="submitform();">
        <?php echo(select($ELIMINADO,$eliminado)); ?>
      </select>
  	</td>
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'></td>
    <td class='tituloTabla'>Contenido</td>
    <td class='tituloTabla'>Fecha</td>
    <td class='tituloTabla'>Usuario</td>
    <td class='tituloTabla'>Tamaño</td>
  </tr>
  <?php echo($HTML); ?>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
