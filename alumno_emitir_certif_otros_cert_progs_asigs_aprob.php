<?php

$pa_aprob = consulta_sql("SELECT id_prog_asig,asignatura FROM vista_alumnos_cursos WHERE situacion='Aprobado' AND id_alumno=$id_alumno ORDER BY asignatura");
$pa_apc   = consulta_sql("SELECT id_pa AS id_prog_asig,asignatura FROM vista_alumnos_cursos WHERE situacion='Convalidado' AND id_alumno=$id_alumno ORDER BY asignatura");
$pa_aph   = consulta_sql("SELECT id_pa_homo AS id_prog_asig,asignatura FROM vista_alumnos_cursos WHERE situacion='Homologado' AND id_alumno=$id_alumno ORDER BY asignatura");
$pa_apecr = consulta_sql("SELECT id_pa AS id_prog_asig,asignatura FROM vista_alumnos_cursos WHERE situacion='Examen Con. Rel.' AND id_alumno=$id_alumno ORDER BY asignatura");

$HTML = "Escoja las asignaturas a certificar:<br><br>"
      . "<b>Cursadas y aprobadas:</b><br><div style='column-count: 2'>";
for ($x=0;$x<count($pa_aprob);$x++) {
	$HTML .= "<input type='checkbox' name='otros[{$pa_aprob[$x]['id_prog_asig']}]' value='{$pa_aprob[$x]['id_prog_asig']}' id='otros[{$pa_aprob[$x]['id_prog_asig']}]'> "
	      .  "<label for='otros[{$pa_aprob[$x]['id_prog_asig']}]'>{$pa_aprob[$x]['asignatura']}</label><br>";
}
$HTML .= "</div>";

if (count($pa_apc) > 0) {
	$HTML .= "<hr>"
	      . "<b>Convalidadas:</b><br><div style='column-count: 2'>";
	for ($x=0;$x<count($pa_apc);$x++) {
		$HTML .= "<input type='checkbox' name='otros[{$pa_apc[$x]['id_prog_asig']}]' value='{$pa_apc[$x]['id_prog_asig']}' id='otros[{$pa_apc[$x]['id_prog_asig']}]'> "
			  .  "<label for='otros[{$pa_apc[$x]['id_prog_asig']}]'>{$pa_apc[$x]['asignatura']}</label><br>";
	}
	$HTML .= "</div>";
}

if (count($pa_aph) > 0) {
	$HTML .= "<hr>"
	      . "<b>Homologadas:</b><br><div style='column-count: 2'>";
	for ($x=0;$x<count($pa_aph);$x++) {
		$HTML .= "<input type='checkbox' name='otros[{$pa_aph[$x]['id_prog_asig']}]' value='{$pa_aph[$x]['id_prog_asig']}' id='otros[{$pa_aph[$x]['id_prog_asig']}]'> "
			  .  "<label for='otros[{$pa_aph[$x]['id_prog_asig']}]'>{$pa_aph[$x]['asignatura']}</label><br>";
	}
	$HTML .= "</div>";		
}

if (count($pa_apecr) > 0) {
	$HTML .= "<hr>"
	      . "<b>Aprobadas por Examen de Conocimientos Relevantes:</b><br><div style='column-count: 2'>";
	for ($x=0;$x<count($pa_apecr);$x++) {
		$HTML .= "<input type='checkbox' name='otros[{$pa_apecr[$x]['id_prog_asig']}]' value='{$pa_apecr[$x]['id_prog_asig']}' id='otros[{$pa_apecr[$x]['id_prog_asig']}]'> "
			  .  "<label for='otros[{$pa_apecr[$x]['id_prog_asig']}]'>{$pa_apecr[$x]['asignatura']}</label><br>";
	}
	$HTML .= "</div>";
}


echo($HTML);

?>