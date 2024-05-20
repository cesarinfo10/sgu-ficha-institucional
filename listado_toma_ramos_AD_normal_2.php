<?php

include("funciones.php");

$SQL_alumnos = "SELECT id AS id_alumno,rut,nombre,carrera,admision
                FROM vista_alumnos
                WHERE cohorte=2007 AND semestre_cohorte=1 AND admision='Normal' AND 
                      rut in ('12952982-2','8829232-4','13058618-K','10866006-6','7304600-9','16914763-9','13552736-K',
                              '13757729-1','15601400-1','13204120-2','13289326-8','15791810-9','9081558-K','12638609-5')
                ORDER BY carrera,nombre";
$alumnos = consulta_sql($SQL_alumnos);

$SQL_alumnos2 = "SELECT id FROM alumnos WHERE cohorte=2007 AND semestre_cohorte=1 AND
                 rut IN ('12952982-2','8829232-4','13058618-K','10866006-6','7304600-9','16914763-9','13552736-K',
                         '13757729-1','15601400-1','13204120-2','13289326-8','15791810-9','9081558-K','12638609-5')
                 ORDER BY carrera_actual,apellidos";

$SQL_cursos = "SELECT vac.id_curso, vac.id_alumno AS id_alumno_c,
                    CASE WHEN vac.id_curso IS NOT NULL
                         THEN coalesce(vac.ano,'0')||'-'||coalesce(vac.semestre,'0')
                         WHEN vac.id_curso IS NULL AND vac.id_pa IS NOT NULL
                         THEN a.cohorte::text||'-0'
                    END AS periodo, vac.asignatura                    
            FROM vista_alumnos_cursos AS vac
            JOIN alumnos AS a ON a.id=vac.id_alumno
            WHERE vac.id_alumno IN ($SQL_alumnos2) 
            ORDER BY periodo,vac.asignatura;";


$cursos = consulta_SQL($SQL_cursos);

$salida = $carrera_aux = "";
$LF = "\n";
for ($x=0;$x<count($alumnos);$x++) {
	$carrera_aux = $carrera;
	extract($alumnos[$x]);

	if ($carrera_aux <> $carrera) {
		$salida .= $carrera.$LF.chr(12);
	}
	
	$salida .= "UNIVERSIDAD MIGUEL DE CERVANTES - Carga Académica Primer Semestre 2007 ".$LF.$LF	
	         . "      ID: $id_alumno".$LF
	         . "     RUT: $rut".$LF
	         . "  Nombre: $nombre".$LF
	         . " Carrera: $carrera".$LF
	         . "Admisión: $admision".$LF
	         . str_repeat("=",97).$LF
	         . " Periodo | ID Curso | Asignatura ".$LF      
	         . str_repeat("=",97).$LF;
	$cont = 0;
	for($y=0;$y<count($cursos);$y++) {
		extract($cursos[$y]);
		if ($id_alumno == $id_alumno_c) {
			$cont++;
			$salida .= "  $periodo |     $id_curso | $asignatura".$LF;
		}
	}
	if ($cont == 0) {
		$salida .= "1.".str_repeat("_",80).$LF.$LF
		         . "2.".str_repeat("_",80).$LF.$LF
		         . "3.".str_repeat("_",80).$LF.$LF
		         . "4.".str_repeat("_",80).$LF.$LF
		         . "5.".str_repeat("_",80).$LF.$LF
		         . "6.".str_repeat("_",80).$LF.$LF
		         . "7.".str_repeat("_",80).$LF.$LF
		         . "8.".str_repeat("_",80).$LF.$LF
	            . str_repeat("=",97).$LF
	         . "Estimado(a) Coordinador(a):".$LF
	         . "Ruego a Ud. agregar las asignaturas que le corresponden a este alumno de admisión Extraordinaria y "
	         . "hacerla llegar a Registro Académico entre el 26 y 30 e marzo del 2007".$LF.$LF
	         . "Le saluda atentamente ".$LF.$LF
	         . "\t\t\t\t Dirección de Docencia".$LF.$LF;
	} else {
		$salida .= str_repeat("=",97).$LF;
	}
	/*$salida .= "  * Alumno nuevo con documentación incompleta.".$LF*/
	$salida .= chr(12);
}

echo($salida);
?>
