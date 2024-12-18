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
	$aId_pc_det      = $_REQUEST['id_pc_det'];
	$aId_profesor    = $_REQUEST['id_profesor'];
	$aAl_proyectado  = $_REQUEST['al_proyectado'];
	$aCupo           = $_REQUEST['cupo'];
	$aTipo           = $_REQUEST['tipo'];
	$aHrs_semanal    = $_REQUEST['hrs_semanal'];
	$aCursos         = array();
	for($x=0;$x<count($aId_pc_det);$x++) {
		$id_pc_det = $aId_pc_det[$x];
		
		if ($aTipo[$id_pc_det] <> "m") {
			if ($aAl_proyectado[$id_pc_det] <= 6) {
				$aTipo[$id_pc_det] = "t";
				$horas_semestrales = $aHrs_semanal[$id_pc_det] * 18 * 0.5; 
			} elseif ($aAl_proyectado[$id_pc_det] > 6) {
				$aTipo[$id_pc_det] = "r";
				$horas_semestrales = $aHrs_semanal[$id_pc_det] * 18;
			}
			
			if ($aHrs_semanal[$id_pc_det] < 4) {
				$aTipo[$id_pc_det] = "r";
				$horas_semestrales = $aHrs_semanal[$id_pc_det] * 18;
			} 
		} elseif ($aTipo[$id_pc_det] == "m") {
			$horas_semestrales = $aHrs_semanal[$id_pc_det] * 18 * 0.78;
		}
		
		$aCursos_aux = array(array("id_pc_det"         => $id_pc_det,
		                           "id_profesor"       => $aId_profesor[$id_pc_det],
		                           "al_proyectado"     => $aAl_proyectado[$id_pc_det],
		                           "cupo"              => $aCupo[$id_pc_det],
		                           "tipo"              => $aTipo[$id_pc_det],
		                           "horas_semestrales" => intval($horas_semestrales)));		                           
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
	echo(js("window.location='$enlbase=prog_cursos_ver&id_prog_curso=$id_prog_curso';"));
	exit;
}

$SQL_pc_det = "SELECT pcd.id,pcd.id_prog_asig,cod_asignatura,asignatura,seccion,al_proyectado,pcd.id_profesor,cupo,tipo,
                      \"horas semanales\" as hrs_semanal
               FROM prog_cursos_detalle AS pcd
               LEFT JOIN vista_prog_asig AS vpa ON vpa.id=pcd.id_prog_asig
               LEFT JOIN vista_profesores AS vp ON vp.id=pcd.id_profesor
               WHERE id_prog_curso=$id_prog_curso AND (pcd.id_profesor IS NULL OR pcd.al_proyectado IS NULL) AND NOT (vobo_vra OR vobo_vraf)   
               ORDER BY cod_asignatura";
$pc_det = consulta_sql($SQL_pc_det);

$profesores = consulta_sql("SELECT id,nombre||' - '||grado_academico AS nombre FROM vista_profesores ORDER BY nombre");

$TIPOS = array(array("id"=>"m","nombre"=>"Modular"));
               
$HTML_pc_det = "";
if (count($pc_det) > 0) {	
	for ($x=0;$x<count($pc_det);$x++) {
		extract($pc_det[$x]);
		
		$profesor = "<select name='id_profesor[$id]' style='font-weight: normal'>"
		          . " <option value=''>-- Seleccione --</option>"
		          . select($profesores,$id_profesor)
		          . "</select>";
		          
		$al_proyectado = "<input type='text' style='text-align: right;font-weight: normal' size='2' name='al_proyectado[$id]' value='$al_proyectado'>";		
		
		$cupo = "<input type='text' style='text-align: right;font-weight: normal' size='2' name='cupo[$id]' value='$cupo'>";

		$tipo = "<select name='tipo[$id]' style='font-weight: normal'>"
		      . " <option value=''>Reg/Tut</option>"
		      . select($TIPOS,$tipo)
		      . "</select>";

		$HTML_pc_det .= "<tr class='filaTabla'>"
		             .  "<input type='hidden' name='id_pc_det[]' value='$id'>"
		             .  "<input type='hidden' name='hrs_semanal[$id]' value='$hrs_semanal'>"
		             .  "  <td class='textoTabla'>$cod_asignatura $asignatura</td>"
		             .  "  <td class='textoTabla' align='center'>$seccion</td>"
		             .  "  <td class='textoTabla'>$profesor</td>"
		             .  "  <td class='textoTabla' align='center'>$al_proyectado</td>"
		             .  "  <td class='textoTabla'>$tipo</td>"
		             .  "  <td class='textoTabla' align='center'>$cupo</td>"
		             .  "</tr>";
	}
} else {
	$HTML_pc_det = "<tr class='filaTabla'>"
	             . "  <td class='textoTabla' colspan='6' align='center'>*** No hay cursos para editar ***</td>"
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

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Asignatura</td>
    <td class='tituloTabla'>Sec.</td>
    <td class='tituloTabla'>Profesor</td>
    <td class='tituloTabla'>Al. Proy.</td>
    <td class='tituloTabla'>Tipo</td>
    <td class='tituloTabla'>Cupo</td>
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