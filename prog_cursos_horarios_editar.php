<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

if (!is_numeric($_REQUEST['id_prog_curso'])) {
	echo(js("window.location='$enlbase=';"));
	exit;
} else {
	$id_prog_curso = $_REQUEST['id_prog_curso'];
}

$prog_curso = consulta_sql("SELECT * FROM vista_prog_cursos WHERE id=$id_prog_curso");
if (count($prog_curso) == 0) {
	echo(js("window.location='$enlbase=';"));
	exit;
}
extract($prog_curso[0],EXTR_PREFIX_ALL,"pc");

if ($_REQUEST['guardar'] == "Guardar") {
	$aId_pc_det = $_REQUEST['id_pc_det'];
	$aDia1      = $_REQUEST['dia1'];
	$aDia2      = $_REQUEST['dia2'];
	$aDia3      = $_REQUEST['dia3'];
	$aHorario1  = $_REQUEST['horario1'];
	$aHorario2  = $_REQUEST['horario2'];
	$aHorario3  = $_REQUEST['horario3'];
	
	$aCursos = array();
	for($x=0;$x<count($aId_pc_det);$x++) {
		$id_pc_det = $aId_pc_det[$x];
	
		$aCursos_aux = array(array("id_pc_det" => $id_pc_det,
		                           "dia1"      => $aDia1[$id_pc_det],
		                           "horario1"  => $aHorario1[$id_pc_det],
		                           "dia2"      => $aDia2[$id_pc_det],
		                           "horario2"  => $aHorario2[$id_pc_det],
		                           "dia3"      => $aDia3[$id_pc_det],
		                           "horario3"  => $aHorario3[$id_pc_det]));		                           
		$aCursos = array_merge($aCursos,$aCursos_aux);
	}
	$SQLupdate_pcd = "";
	for($x=0;$x<count($aCursos);$x++) {
		
		$SQLupdate_pcd .= "UPDATE prog_cursos_detalle "
		               .  "SET " . arr2sqlupd($aCursos[$x])
		               .  " WHERE id={$aCursos[$x]['id_pc_det']};";
	}
	consulta_dml($SQLupdate_pcd);
	//echo($SQLupdate_pcd);
	echo(js("window.location='$enlbase=prog_cursos_horarios&id_prog_curso=$id_prog_curso';"));
	exit;
}

$SQL_pc_det = "SELECT pcd.id,pcd.id_prog_asig,trim(cod_asignatura) AS cod_asignatura,asignatura,seccion,horas_semestrales,
                      vp.nombre AS profesor,tipo,dia1,horario1,dia2,horario2,dia3,horario3,upper(tipo) AS tipo
               FROM prog_cursos_detalle AS pcd
               LEFT JOIN vista_prog_asig AS vpa ON vpa.id=pcd.id_prog_asig
               LEFT JOIN vista_profesores AS vp ON vp.id=pcd.id_profesor
               WHERE id_prog_curso=$id_prog_curso AND tipo<>'m' AND NOT (vobo_vra OR vobo_vraf)   
               ORDER BY cod_asignatura";
$pc_det = consulta_sql($SQL_pc_det);

$horarios = consulta_sql("SELECT id,id||'=>'||intervalo AS nombre FROM vista_horarios ORDER BY id");
            
$HTML_pc_det = "";
if (count($pc_det) > 0) {	
	for ($x=0;$x<count($pc_det);$x++) {
		extract($pc_det[$x]);
		
		$rojo = "; color: #ff0000";
		$dia1_style = $dia2_style = $dia3_style = $horario1_style  = $horario2_style = $horario3_style = "";
		if ($dia1 == "") { $dia1_style = $rojo; }
		if ($dia2 == "" && $horas_semestrales >= 72) { $dia2_style = $rojo; }
		if ($dia3 == "" && $horas_semestrales > 72) { $dia3_style = $rojo; }
		if ($horario1 == "") { $horario1_style = $rojo; }
		if ($horario2 == "" && $horas_semestrales >= 72) { $horario2_style = $rojo; }
		if ($horario3 == "" && $horas_semestrales > 72) { $horario3_style = $rojo; }
		
		$dia1 = "<select name='dia1[$id]' style='font-weight: normal $dia1_style'>"
		      . " <option value='' style='text-decoration:underline'>-- Día --</option>"
		      . select($dias_palabra,$dia1)
		      . "</select>";
		$horario1 = "<select name='horario1[$id]' style='font-weight: normal $horario1_style'>"
		          . " <option value='' style='text-decoration:underline'>-- Módulo --</option>"
		          . select($horarios,$horario1)
		          . "</select>";

		$dia2 = "<select name='dia2[$id]' style='font-weight: normal $dia2_style'>"
		      . " <option value='' style='text-decoration:underline'>-- Día --</option>"
		      . select($dias_palabra,$dia2)
		      . "</select>";

		$horario2 = "<select name='horario2[$id]' style='font-weight: normal $horario2_style'>"
		          . " <option value='' style='text-decoration:underline'>-- Módulo --</option>"
		          . select($horarios,$horario2)
		          . "</select>";

		$dia3 = "<select name='dia3[$id]' style='font-weight: normal $dia3_style'>"
		      . " <option value='' style='text-decoration:underline'>-- Día --</option>"
		      . select($dias_palabra,$dia3)
		      . "</select>";
		$horario3 = "<select name='horario3[$id]' style='font-weight: normal; $horario3_style'>"
		          . " <option value='' style='text-decoration:underline'>-- Módulo --</option>"
		          . select($horarios,$horario3)
		          . "</select>";
		          
		$HTML_pc_det .= "<tr class='filaTabla'>"
		             .  "<input type='hidden' name='id_pc_det[]' value='$id'>"		             
		             .  "  <td class='textoTabla'>$cod_asignatura-$seccion $asignatura</td>"
		             .  "  <td class='textoTabla'>$profesor</td>"
		             .  "  <td class='textoTabla' align='center'>$tipo<br>($horas_semestrales)</td>"
		             .  "  <td class='textoTabla'>$dia1<br>$horario1</td>"
		             .  "  <td class='textoTabla'>$dia2<br>$horario2</td>"
		             .  "  <td class='textoTabla'>$dia3<br>$horario3</td>"
		             .  "</tr>";
	}
} else {
	$HTML_pc_det = "<tr class='filaTabla'>"
	             . "  <td class='textoTabla' colspan='5' align='center'>*** No hay cursos para editar ***</td>"
	             . "</tr>";
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div><br>

<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_prog_curso" value="<?php echo($id_prog_curso); ?>">

<table cellpadding="4" cellspacing="0" border="0" class="tabla">
  <tr>
    <td align="center" class="textoTabla" style="vertical-align: middle;">
      <input type="submit" name="guardar" value="Guardar">
      <input type="button" name="cancelar" value="Cancelar">
    </td>
  </tr>
</table><br>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="celdaNombreAttr">Escuela:</td>
    <td class="celdaValorAttr"><?php echo($pc_escuela); ?></td>
    <td class="celdaNombreAttr">Periodo:</td>
    <td class="celdaValorAttr"><?php echo($pc_periodo); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Creador:</td>
    <td class="celdaValorAttr" colspan="3"><?php echo($pc_creador); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Fec. Creación:</td>
    <td class="celdaValorAttr"><?php echo($pc_fecha); ?></td>
    <td class="celdaNombreAttr">Fec. Últ. Mod.:</td>
    <td class="celdaValorAttr"><?php echo($pc_fecha_mod); ?></td>
  </tr>
</table><br>

<div class="texto">
  NOTA: Se excluyen de esta tabla los cursos de tipo Modular, ya que estos tienen un horario predefinido.
</div>
<div class="texto" style="color: #FF0000">
  En rojo, todos los módulos sin horario definido, que deben obligatoriamente defirnirse.
</div><br>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Asignatura</td>
    <td class='tituloTabla'>Profesor</td>
    <td class='tituloTabla'>Tipo<br>(Hrs)</td>
    <td class='tituloTabla'>1er Módulo</td>
    <td class='tituloTabla'>2do Módulo</td>
    <td class='tituloTabla'>3er Módulo</td>
  </tr>
  <?php echo($HTML_pc_det); ?>
</table> 

</form>

<!-- Fin: <?php echo($modulo); ?> -->

<?php
function arr2sqlupd($aTabla) {
	$arr2sqlupd = "";
	$aCampos = array_keys($aTabla);
	for($x=1;$x<count($aCampos);$x++) {
		if ($aTabla[$aCampos[$x]] == "") {
			$aTabla[$aCampos[$x]] = "null";
		} else {
			$aTabla[$aCampos[$x]] = "'{$aTabla[$aCampos[$x]]}'";
		}
		$arr2sqlupd .= "{$aCampos[$x]}={$aTabla[$aCampos[$x]]},";
	}
	return substr($arr2sqlupd,0,-1);
}
?>