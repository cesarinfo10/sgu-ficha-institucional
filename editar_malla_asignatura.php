<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_dm    = $_REQUEST['id_dm'];
$id_malla = $_REQUEST['id_malla'];

if (!is_numeric($id_dm) || !is_numeric($id_malla)) {
	echo(js("location.href='$enlbase=ver_plan_de_estudios&id_malla=$id_malla'"));
	exit;
}

$SQL_prerequisitos = "SELECT char_comma_sum(cod_asignatura_req||' '||asignatura_req) AS asignatura FROM vista_requisitos_malla WHERE id_dm=dm.id GROUP BY id_dm";

$SQL_detalle_malla = "SELECT vdm.*,pond_tns,pond_ga,pond_tp,pond_otros,($SQL_prerequisitos) AS prerequisitos 
                      FROM detalle_mallas dm 
                      LEFT JOIN vista_detalle_malla vdm USING(id) 
                      WHERE dm.id=$id_dm";
$detalle_malla = consulta_sql($SQL_detalle_malla);
if (count($detalle_malla) == 0) {
	echo(js("location.href='$enlbase=ver_plan_de_estudios&id_malla=$id_malla'"));
	exit;
}

if ($_REQUEST['guardar'] == "Guardar") {
	$aCampos = array("pond_tns","pond_ga","pond_tp","pond_otros");	
	$SQLupdate = "UPDATE detalle_mallas SET " . arr2sqlupdate($_REQUEST,$aCampos) . " WHERE id=$id_dm";
	if (consulta_dml($SQLupdate) > 0) {
		echo(msje_js("Se han guardado los cambios en esta asignatura."));
	} else {
		echo(msje_js("ERROR: No se han podido guardar los cambios."));
	}
	exit;
}

$SQL_malla = "SELECT m.id,m.ano,carrera,m.niveles,vm.id_escuela,m.id_carrera,m.cant_asig_oblig,c.regimen,
                     tns_nombre,tns_sem_req,ga_nombre,ga_sem_req,tp_nombre,tp_sem_req,otros_nombre,otros_sem_req
              FROM vista_mallas vm
              LEFT JOIN mallas m USING(id)
              LEFT JOIN carreras c ON m.id_carrera=c.id
              WHERE m.id=$id_malla;";
$malla = consulta_sql($SQL_malla);

$HTML_ponds = "";
$PORC_POND[] = array("id" => "", "nombre" => "No aplica (N/A)");
$PORC_POND[] = array("id" => 0, "nombre" => "Promedio General (PG)");
setlocale(LC_ALL,"C");
setlocale(LC_NUMERIC,"C");
for ($porc_pond=2.5;$porc_pond<=100;$porc_pond+=2.5) { $PORC_POND[] = array("id" => $porc_pond/100, "nombre" => "$porc_pond%"); }

if ($malla[0]['regimen'] == "PRE" && $malla[0]['tns_nombre'] <> "" && $malla[0]['tns_sem_req'] > 0) {
	$HTML_ponds .= "<tr>"
	            .  "  <td class='celdaNombreAttr'>Técnico de Nivel Superior (TNS):</td>"
	            .  "  <td class='celdaValorAttr' colspan='3'><select class='filtro' name='pond_tns'>".select($PORC_POND,$detalle_malla[0]['pond_tns'])."</select></td>"
	            .  "</tr>";
}
if (($malla[0]['regimen'] == "PRE" || $malla[0]['regimen'] == "POST-G" || $malla[0]['regimen'] == "POST-GD") && $malla[0]['ga_nombre'] <> "" && $malla[0]['ga_sem_req'] > 0) {
	$HTML_ponds .= "<tr>"
	            .  "  <td class='celdaNombreAttr'>Grado Académico (GA):</td>"
	            .  "  <td class='celdaValorAttr' colspan='3'><select class='filtro' name='pond_ga'>".select($PORC_POND,$detalle_malla[0]['pond_ga'])."</select></td>"
	            .  "</tr>";
}
if (($malla[0]['regimen'] == "PRE" || $malla[0]['regimen'] == "POST-T" || $malla[0]['regimen'] == "POST-TD") && $malla[0]['tp_nombre'] <> "" && $malla[0]['tp_sem_req'] > 0) {
	$HTML_ponds .= "<tr>"
	            .  "  <td class='celdaNombreAttr'>Título Profesional (TP):</td>"
	            .  "  <td class='celdaValorAttr' colspan='3'><select class='filtro' name='pond_tp'>".select($PORC_POND,$detalle_malla[0]['pond_tp'])."</select></td>"
	            .  "</tr>";
}
if ($malla[0]['otros_nombre'] <> "" && $malla[0]['otros_sem_req'] > 0) {
	$HTML_ponds .= "<tr>"
				.  "  <td class='celdaNombreAttr'>Otra Certificación (Otros):</td>"
				.  "  <td class='celdaValorAttr' colspan='3'><select class='filtro' name='pond_otros'>".select($PORC_POND,$detalle_malla[0]['pond_otros'])."</select></td>"
				.  "</tr>";
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_dm" value="<?php echo($id_dm); ?>">
<input type="hidden" name="id_malla" value="<?php echo($id_malla); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>: <?php echo($malla[0]['carrera']); ?> - <?php echo($malla[0]['ano']); ?>
</div>
<table class="tabla" style='margin-top: 5px'>
  <tr>
    <td>
      <input type="submit" name="guardar" value="Guardar">
      <!-- <input type="submit" name="eliminar" value="Eliminar asignatura"> -->
      <input type="button" name="cancelar" value="Cancelar"  onClick="window.location='<?php echo("$enlbase=editar_detalle_malla&id_malla=$id_malla"); ?>';">
    </td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr><td class='celdaNombreAttr' colspan='4' style='text-align: center'>Datos de la Asignatura en Plan de Estudios</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Asignatura:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($detalle_malla[0]['cod_asignatura']." ".$detalle_malla[0]['asignatura']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Caracter:</td>
    <td class='celdaValorAttr'><?php echo($detalle_malla[0]['caracter']); ?></select></td>
    <td class='celdaNombreAttr'>Nivel:</td>
    <td class='celdaValorAttr'><?php echo($NIVELES[$detalle_malla[0]['nivel']-1]['nombre']); ?> semestre</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Línea Temática:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($detalle_malla[0]['linea_tematica']); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan='4' style='text-align: center'>Ponderación de la Asignatura<br><small>(según los Títulos y/o Grados que otorga la malla)</small></td></tr>
  <?php echo($HTML_ponds); ?>
  <tr>
    <td class='celdaNombreAttr'>Pre-requisitos:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo(str_replace(", ","<br>",$detalle_malla[0]['prerequisitos'])); ?></td>
  </tr>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
