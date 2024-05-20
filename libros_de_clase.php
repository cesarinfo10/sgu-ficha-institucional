<?php

include("funciones.php");

$SQL_cursos = "SELECT id AS id_curso,cod_asignatura||'-'||seccion AS cod_asignatura,asignatura,carrera AS carrera_ramo,profesor,
                      sesion1,sesion2,sesion3 
               FROM vista_cursos
               WHERE id IN (2177,2412)
               ORDER BY carrera,cod_asignatura";
$cursos = consulta_sql($SQL_cursos);

$SQL_cursos2 = "SELECT id FROM vista_cursos WHERE ano=2007 AND semestre=1 ORDER BY carrera,cod_asignatura";

$SQL_alumnos_cursos = "SELECT vca.id_curso AS id_curso_ca,trim(vca.rut)::char(12) AS rut,
                              (CASE estado WHEN 0 THEN '* ' ELSE '' END||
                               CASE situacion WHEN 'Suspendido' THEN '-- ' ELSE '' END||
                               initcap(nombre_alumno))::char(35) as nombre_alumno,s1
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
	
	$salida .= "UNIVERSIDAD MIGUEL DE CERVANTES".str_repeat("\t",16)."Número de acta: $id_curso".$LF	
	         . "Libro de Clases (lista definitiva)".$LF.$LF
	         . "Asignatura: $cod_asignatura $asignatura".$LF
	         . "  Profesor: $profesor".str_repeat("\t",6)."Horario: $sesion1 $sesion2 $sesion3".$LF
	         . str_repeat(" ",50).str_repeat("=",105).$LF
	         . str_repeat(" ",50)."| Control de asistencia".str_repeat(" ",37)."|| Ev. Formativas       || Ev. Solemnes |".$LF      
	         . str_repeat("=",150)."| NF ".$LF
	         . " Alumno ".str_repeat(" ",42).str_repeat("| ",29)."% ||C1|C2|C3|C4|C5|C6| NC || S1 | S2 | Re |".$LF 
	         . " RUT          Nombre ".str_repeat(" ",29).str_repeat("| ",29)."  ||  |  |  |  |  |  |    ||    |    |    |".$LF
	         . str_repeat("=",155).$LF;
	for($y=0;$y<count($alumnos);$y++) {
		extract($alumnos[$y]);
		if ($id_curso_ca == $id_curso) {
			if ($s1 == "") { $s1 = "____"; } else { $s1 = $s1 . " "; }
			$salida .= " $rut $nombre_alumno ".str_repeat("|_",28)."|___||__|__|__|__|__|__|____||$s1|____|____|____".$LF;
		}
	}
	$salida .= str_repeat("=",155).$LF
	         . "Estimado(a) Sr(a) Profesor (a): "
	         . "Ruego a Ud. no agregar alumnos a esta lista definitiva. "
	         . "Le saluda atentamente ".$LF
	         . "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t Dirección de Docencia".$LF
	         . " * Alumno(a) antiguo que no está matriculado para el semestre académico actual.".$LF
	         . " -- Alumno(a) que se ha retirado del curso.".$LF
	         . chr(12);
	         
}

echo($salida);
//file_put_contents("listado_libros_de_clases.txt",$salida);

?>
