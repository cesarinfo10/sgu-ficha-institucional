<?php 

include("funciones.php");
$SEMESTRE_InsAsig = 2;
$ANO_InsAsig = 2007;

$SQL_ins_cursos = "SELECT distinct on (id_alumno) id_alumno FROM inscripciones_cursos;";
$alumnos_ins_cursos = consulta_sql($SQL_ins_cursos);

for ($y=0;$y<count($alumnos_ins_cursos);$y++) {
	
	$id_alumno = $alumnos_ins_cursos[$y]['id_alumno'];

	$SQL_alumno = "SELECT va.id,va.rut,va.nombre,va.estado,va.malla_actual,va.cohorte,va.carrera,va.id_malla_actual,va.id_carrera
	               FROM vista_alumnos AS va 
	               WHERE va.id=$id_alumno;";
	$alumno = consulta_sql($SQL_alumno);
	

	extract($alumno[0]);
	
	$SQL_pa_cursos_ins = "SELECT id_curso FROM inscripciones_cursos WHERE id_alumno=$id_alumno";
	
	$SQL_pa_aprob = "SELECT CASE WHEN id_estado = 1                    THEN c.id_prog_asig
	                             WHEN id_estado = 3 AND homologada     THEN ca.id_pa_homo
	                             WHEN id_estado = 4 AND convalidado    THEN ca.id_pa
	                             WHEN id_estado = 5 AND examen_con_rel THEN ca.id_pa
	                        END AS id_prog_asig
	                 FROM cargas_academicas AS ca
	                 LEFT JOIN cursos AS c ON c.id=ca.id_curso 
	                 WHERE id_alumno=$id_alumno AND id_estado IN (1,3,4,5)";
	
	$SQL_pa_restantes = "SELECT id_prog_asig FROM detalle_mallas
	                     WHERE id_malla=$id_malla_actual AND id_prog_asig NOT IN ($SQL_pa_aprob)";
	
	$SQL_pa_rest_sinprereq = "SELECT dm.id_prog_asig
	                          FROM detalle_mallas AS dm
	                          FULL JOIN requisitos_malla AS rm ON rm.id_dm=dm.id
	                          WHERE dm.id_malla=$id_malla_actual AND rm.id_dm_req IS NULL
	                            AND dm.id_prog_asig NOT IN ($SQL_pa_aprob)";
	
	$SQL_pa_rest_conprereq = "SELECT dm1.id_prog_asig,dm2.id_prog_asig AS id_prog_asig_req
	                          FROM requisitos_malla AS rm
	                          LEFT JOIN detalle_mallas AS dm1 ON dm1.id=rm.id_dm
	                          LEFT JOIN detalle_mallas AS dm2 ON dm2.id=rm.id_dm_req
	                          WHERE dm1.id_malla=$id_malla_actual 
	                            AND dm1.id_prog_asig IN ($SQL_pa_restantes)";
	
	$SQL_pa_incursables = "SELECT pa_con_prereq.id_prog_asig
	                       FROM ($SQL_pa_rest_conprereq) AS pa_con_prereq
	                       LEFT JOIN ($SQL_pa_aprob) AS pa_aprob ON pa_aprob.id_prog_asig=pa_con_prereq.id_prog_asig_req
	                       WHERE pa_aprob.id_prog_asig IS NULL";
	
	$SQL_pa_electivos = "SELECT id_prog_asig
	                     FROM vista_cursos
	                     WHERE ano=$ANO_InsAsig AND semestre=$SEMESTRE_InsAsig
	                       AND carrera='Electivos de Formación General'";
	
	$SQL_pa_cursables = "(SELECT id_prog_asig
	                      FROM ($SQL_pa_rest_conprereq) AS pa_con_prereq
	                      WHERE id_prog_asig NOT IN ($SQL_pa_incursables)
	                     ) UNION (
	                      $SQL_pa_rest_sinprereq
	                     ) UNION (
	                      $SQL_pa_electivos
	                     )";
	
	
	$SQL_cursos_propuestos = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,vc.profesor,
	                                 vc.semestre||'-'||vc.ano AS periodo,
	                                 coalesce(vc.sesion1,'')||coalesce(vc.sesion2,'')||coalesce(vc.sesion3,'') AS horario,
	                                 CASE WHEN tomados.id_curso IS NOT NULL THEN 'Si' ELSE 'No' END AS inscrita
	                          FROM vista_cursos AS vc
	                          LEFT JOIN ($SQL_pa_cursos_ins) AS tomados ON tomados.id_curso=vc.id
	                          WHERE vc.ano=$ANO_InsAsig AND vc.semestre=$SEMESTRE_InsAsig
	                            AND vc.id_prog_asig IN ($SQL_pa_cursables)
	                          ORDER BY cod_asignatura;";
	//echo($SQL_cursos_propuestos);
	
	$SQL_cursos_ins_alu = "SELECT c.id_prog_asig,ic.id_curso 
	                       FROM inscripciones_cursos AS ic
	                       LEFT JOIN cursos AS c ON c.id=ic.id_curso
	                       WHERE id_alumno=$id_alumno AND NOT alza_prereq";
	$SQL_comprobacion = "SELECT ins_alu.id_curso
	                     FROM ($SQL_cursos_ins_alu) AS ins_alu
	                     LEFT JOIN ($SQL_pa_cursables) AS pa_cursables ON pa_cursables.id_prog_asig=ins_alu.id_prog_asig
	                     WHERE pa_cursables.id_prog_asig IS NULL";
	$comprobacion_prereq = consulta_sql($SQL_comprobacion);
	if (count($comprobacion_prereq) > 0 ) {
		echo("Se ha detectado incoherencias en la Inscripcion de Cursos de este alumno(a): $id_alumno $carrera\n");
//		$SQLdelete = "DELETE FROM inscripciones_cursos WHERE id_alumno=$id_alumno AND id_curso IN ($SQL_comprobacion);";
//		consulta_dml($SQLdelete);
		$SQLdelete = "";
	}
	
}
?>
