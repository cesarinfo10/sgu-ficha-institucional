<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_alumno = $_REQUEST['id_alumno'];
if (!is_numeric($id_alumno)) {
	echo(js("location.href='principal.php?modulo=gestion_alumnos';"));
	exit;
}

$SQL_alumno = "SELECT va.id,va.nombre,va.rut,va.carrera,va.semestre_cohorte||'-'||va.cohorte AS cohorte,va.estado,
                      a.estado AS id_estado,a.estado_tramite,
                      CASE WHEN m.id_alumno IS NOT NULL THEN 'Si' ELSE 'No' END AS matriculado
               FROM vista_alumnos AS va
               LEFT JOIN alumnos AS a USING (id)
               LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND semestre=$SEMESTRE AND ano=$ANO)               
               WHERE va.id=$id_alumno;";
$alumno = consulta_sql($SQL_alumno);
if (count($alumno) == 0) {
	echo(js("location.href='principal.php?modulo=gestion_alumnos';"));
	exit;
}
extract($alumno[0]);

$PERIODOS_MAT = array();
$ano_mat_ini = $ANO;
$x = 0;
if ($SEMESTRE == 2) { $ano_mat_ini++; }
for ($y=$ano_mat_ini;$y>=$ANO;$y--) {
	$PERIODOS_MAT = array_merge($PERIODOS_MAT,array($x => array("id" => $y,"nombre" => $y)));
	$x++; 
}

if ($alumno[0]['matriculado'] == "No") {
	if ($estado == "Vigente" || $estado == "Tesista" || $estado == "Egresado") {
		$HTML_matr = "<select name='periodo_mat'>"
		           . "  <option value=''>--Seleccione--</option>"
		           . select($PERIODOS_MAT,$ANO)
		           . "</select>"
	 	           . "<input type='submit' name='matricular' value='Matricular'>";
	} else {
		$HTML_matr = "<span class='rojo'>** Alumno no puede matricularse**</span>";
	}
}


$SQL_alumno_mat = "SELECT semestre AS sem_mat,ano AS ano_mat,fecha AS fecha_mat
                   FROM matriculas WHERE id_alumno=$id
                   ORDER BY ano DESC, semestre DESC;";
$alumno_mat = consulta_sql($SQL_alumno_mat);
$HTML_mat = "";
for ($x=0;$x<count($alumno_mat);$x++) {
	extract($alumno_mat[$x]);
	$per_mat = "$sem_mat-$ano_mat";
	if ($sem_mat == $SEMESTRE && $ano_mat == $ANO) {
		$HTML_mat .= "<b>$per_mat</b><br>";		
	} else {
		$HTML_mat .= "$per_mat<br>";
	}
}

$periodo_mat = $_REQUEST['periodo_mat'];
if ($_REQUEST['matricular'] == "Matricular" && is_numeric($periodo_mat)) {

	if ($alumno[0]['estado'] == "Vigente" || $alumno[0]['estado'] == "Tesista" || $alumno[0]['estado'] == "Egresado") {
		$SQLinsert_alumno_mat = "INSERT INTO matriculas (id_alumno,semestre,ano) VALUES ($id_alumno,2,$periodo_mat);";
	} else {
		echo(msje_js("Este alumno no se puede matricular, por que no se encuentra vigente y tiene el estado de: $estado. "
		            ."Es posible que sea necesario completar y enviar un formulario de reincorporación a Registro "
		            ." Académico para que este cambie el estado actual del alumno."));
		echo(js("window.location='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
		exit;		
	}
	
	if (consulta_dml($SQLinsert_alumno_mat) > 0) {
		echo(msje_js("Se ha matriculado exitosamente a este alumno para el periodo $periodo_mat."));
	} else {
		echo(msje_js("Ocurrió un problema mientras se intentaba guardar la matricula de este alumno.\\n"
		            ."Por favor, inténtelo más tarde"));
	}
	echo(js("window.location='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
}
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="get" onSubmit="return enblanco2('id_pa','id_pa_homo');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_alumno" value="<?php echo($id_alumno); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type="submit" name="guardar" value="Guardar"></td>
    <td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar" onClick="history.back();"></td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Alumno</td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">ID alumno:</td>
    <td class="celdaValorAttr"><?php echo($alumno[0]['id']); ?></td>
    <td class="celdaNombreAttr">RUT:</td>
    <td class="celdaValorAttr"><?php echo($alumno[0]['rut']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nombre:</td>
    <td class="celdaValorAttr" colspan="3"><?php echo($alumno[0]['nombre']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Carrrera:</td>
    <td class="celdaValorAttr"><?php echo($alumno[0]['carrera']); ?></td>
    <td class="celdaNombreAttr">Cohorte:</td>
    <td class="celdaValorAttr"><?php echo($alumno[0]['cohorte']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Matriculado:</td>
    <td class="celdaValorAttr"><?php echo($alumno[0]['matriculado']); ?></td>
    <td class="celdaNombreAttr">Matricular en el periodo:</td>
    <td class="celdaValorAttr"><?php echo($HTML_matr); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr" colspan="2">Periodos con matrícula:</td>
    <td class="celdaValorAttr" colspan="2"><?php echo($HTML_mat); ?></td>    
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

