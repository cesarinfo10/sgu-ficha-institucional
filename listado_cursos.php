<?php

include("funciones.php");

$SQL_cursos = "SELECT id AS id_curso,cod_asignatura||'-'||seccion AS cod_asignatura,asignatura,carrera AS carrera_ramo,profesor 
               FROM vista_cursos
               WHERE ano=2007 AND semestre=1
               ORDER BY carrera,cod_asignatura";
$cursos = consulta_sql($SQL_cursos);

$SQL_cursos2 = "SELECT id FROM vista_cursos WHERE ano=2007 AND semestre=1 ORDER BY carrera,cod_asignatura";

$SQL_alumnos_cursos = "SELECT vca.id_curso AS id_curso_ca,trim(vca.rut)::char(12) AS rut,
                              nombre_alumno::char(35) as nombre_alumno
                       FROM vista_cursos_alumnos AS vca LEFT JOIN alumnos AS a ON a.id=vca.id_alumno 
                       WHERE id_curso IN ($SQL_cursos2)
                       ORDER BY id_curso,nombre_alumno";
$alumnos = consulta_SQL($SQL_alumnos_cursos);

$salida = $carrera_ramo_aux = "";
$LF = "\n";
for ($x=0;$x<count($cursos);$x++) {
	$carrera_ramo_aux = $carrera_ramo;
	extract($cursos[$x]);

	if ($carrera_ramo_aux <> $carrera_ramo) {
		$salida .= $carrera_ramo.$LF.chr(12);
	}
	
	$salida .= "UNIVERSIDAD MIGUEL DE CERVANTES".$LF.$LF
	         . "Número de acta: $id_curso".$LF
	         . "    Asignatura: $cod_asignatura $asignatura".$LF
	         . "      Profesor: $profesor".$LF	         
	         ."                                                             Control de asistencia ".$LF
	         . str_repeat("=",97).$LF
	         . "    RUT          | Nombre alumno                       |".str_repeat("   |",10).$LF      
	         . str_repeat("=",97).$LF;
	$nro_lista = 0;
	for($y=0;$y<count($alumnos);$y++) {
		extract($alumnos[$y]);
		if ($id_curso_ca == $id_curso) {
			$nro_lista++;
			$nro = sprintf("%02s",$nro_lista);
			$salida .= " $nro $rut | $nombre_alumno |".str_repeat("___|",10).$LF;
		}
	}
	$salida .= str_repeat("=",97).$LF
	         . "Estimado(a) Sr(a) Profesor (a):".$LF
	         . "Ruego a Ud. no agregar alumnos a esta lista que tiene vigencia hasta el 20 de abril "
	         . "de 2007. Si un alumno no aparece en ella debe, este deberá acercarse a la Coordinación Académica "
	         . "de la Escuela correspondiente.".$LF
	         . "Le saluda atentamente ".$LF.$LF
	         . "\t\t\t\t Dirección de Docencia".$LF.$LF
/*	         . "  * Alumno nuevo con documentación incompleta.".$LF
	         . " ** Alumno antiguo que no está matriculado para el semestre académico actual.".$LF*/
	         . chr(12);
	         
}

echo($salida);
?>