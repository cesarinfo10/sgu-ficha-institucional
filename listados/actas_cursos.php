<?php

include("../funciones.php");

$SQL_cursos_exam = "SELECT id FROM cursos WHERE exam_cse";

$SQL_cursos = "SELECT vc.id AS id_curso,vc.cod_asignatura||'-'||vc.seccion AS cod_asignatura,vc.asignatura,vc.carrera AS carrera_ramo,
                      vc.profesor,vc.sesion1,vc.sesion2,vc.sesion3,vc.semestre||'-'||vc.ano AS periodo,
                      u.nombre||' '||u.apellido AS nombre_director 
               FROM vista_cursos AS vc
               LEFT JOIN carreras AS c ON c.id=vc.id_carrera
               LEFT JOIN escuelas AS e ON e.id=c.id_escuela
               LEFT JOIN usuarios AS u ON u.id=e.id_director
               WHERE vc.id IN ($SQL_cursos_exam)
               ORDER BY carrera,cod_asignatura";
$cursos = consulta_sql($SQL_cursos);

$SQL_alumnos_cursos = "SELECT va.id,ca.id_curso AS id_curso_ca,trim(va.rut)::char(12) AS rut,
                              va.nombre::char(50) as nombre_alumno,va.estado::char(6) AS estado,
                              CASE WHEN solemne1=-1
                                   THEN 'NSP' ELSE coalesce(solemne1::char(3),'   ') END AS s1,
                              CASE WHEN nota_catedra=-1
                                   THEN 'NSP' ELSE coalesce(nota_catedra::char(3),'   ') END AS nc,
                              CASE WHEN solemne2=-1
                                   THEN 'NSP' ELSE coalesce(solemne2::char(3),'   ') END AS s2,
                              CASE WHEN nota_final=-1
                                   THEN 'NSP' ELSE coalesce(nota_final::char(3),'   ') END AS nf,
                              cae.nombre AS situacion
                       FROM cargas_academicas AS ca
                       LEFT JOIN vista_alumnos AS va ON va.id=ca.id_alumno 
                       LEFT JOIN ca_estados AS cae ON cae.id=ca.id_estado
                       WHERE id_curso IN ($SQL_cursos_exam) 
                       ORDER BY id_curso,nombre_alumno;";
$alumnos = consulta_sql($SQL_alumnos_cursos);


//$carrera_ramo = $cursos[0]['carrera_ramo'];
$salida = $carrera_ramo_aux = "";
$LF = "\n";
for ($x=0;$x<count($cursos);$x++) {
	$carrera_ramo_aux = $carrera_ramo;
	extract($cursos[$x]);

	if ($carrera_ramo_aux <> $carrera_ramo) {
		$salida .= $carrera_ramo.$LF.chr(12);
	}

	$salida .= "UNIVERSIDAD MIGUEL DE CERVANTES - Sistema de Gestión Universitaria (SGU)".str_repeat("\t",3)."Número de acta: $id_curso".$LF	
	         . "Acta de Curso".$LF.$LF
	         . "\t"."Asignatura: $cod_asignatura $asignatura".str_repeat("\t",3)."Periodo: $periodo".$LF
	         . "\t"."  Profesor: $profesor".str_repeat("\t",3)."Horario: $sesion1 $sesion2 $sesion3".$LF
	         . str_repeat("=",127).$LF
	         . " Alumnos                                                                        | Calificaciones".$LF
	         . str_repeat("-",127).$LF
	         . " ID    RUT          Nombre                                             | Estado |  S1  |  NC  |  S2  | Rec. |  NF  | Situación".$LF
	         . str_repeat("=",127).$LF;
	$alumnos_inscritos = 0;
	
	for($y=0;$y<count($alumnos);$y++) {
		extract($alumnos[$y]);		
		if ($id_curso_ca == $id_curso) {
			$alumnos_inscritos++;
			$nro = sprintf("%02s",$nro_lista);
			$salida .= " $id $rut $nombre_alumno | $estado | $s1  | $nc  | $s2  |______|______|$situacion".$LF;
		}
	}

	$firma_director = str_repeat("_",strlen($nombre_director));
	$firma_profesor = str_repeat("_",strlen($profesor));
	
	$pie_firma_director = "Dirección de Escuela";
	$pie_firma_profesor = "Profesor(a) del Curso";
	
	if (strlen($nombre_director)-strlen($pie_firma_director)>0) {
		$pie_firma_director .= str_repeat(" ",strlen($nombre_director)-strlen($pie_firma_director)+1);
	} else {
		$firma_director = str_repeat("_",strlen($pie_firma_director));
		$nombre_director .= str_repeat(" ",strlen($pie_firma_director)-strlen($nombre_director)+1);
	}	
	if (strlen($profesor)-strlen($pie_firma_profesor)>0) {
		$pie_firma_profesor .= str_repeat(" ",strlen($profesor)-strlen($pie_firma_profesor)+1);
	} else {
		$firma_profesor = str_repeat("_",strlen($pie_firma_profesor));
		$profesor .= str_repeat(" ",strlen($pie_firma_profesor)-strlen($profesor)+1);
	}		

	$salida .= str_repeat("=",127).$LF
	         . "\t"."Total Alumno(a)s inscrito(a)s: $alumnos_inscritos"
	         . $LF
	         . $LF
	         . $LF
	         . $LF
	         . $firma_director    ."\t\t".$firma_profesor."\t\t"."_________________________".$LF
	         . $nombre_director   ."\t\t".$profesor      ."\t\t"."          C.S.E.         ".$LF
	         . $pie_firma_director."\t\t".$pie_firma_profesor.$LF
	         . chr(12);	         
}

echo($salida);
?>