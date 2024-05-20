<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

//include("validar_modulo.php");

$id_curso = $_REQUEST['id_curso'];
if ($id_curso == "") {
	echo(js("location.href='$enlbase=portada';"));
	exit;
}

$SQL_curso = "SELECT cantidad_alumnos(id) AS cant_alumnos FROM cursos WHERE id=$id_curso";
$curso = consulta_sql($SQL_curso);

if (count($curso) > 0) {
	$SQL_calc_nf_curso = "UPDATE cargas_academicas
	                      SET nota_final = calc_nf_2011(solemne1,nota_catedra,solemne2,recuperativa)
	                      WHERE id_curso IN (SELECT id FROM cursos WHERE $id_curso IN (id,id_fusion)) 
	                        AND (id_estado IS NULL OR id_estado NOT IN (6,10));";
	$calc_nf_curso = consulta_dml($SQL_calc_nf_curso);

	$SQL_calc_situacion_curso = "UPDATE cargas_academicas
	                             SET id_estado=CASE WHEN (id_estado IS NULL OR id_estado NOT IN (6,10)) AND nota_final BETWEEN 4 AND 7 THEN 1 
	                                                WHEN (id_estado IS NULL OR id_estado NOT IN (6,10)) AND nota_final < 4             THEN 2
	                                                ELSE id_estado
	                                            END
	                             WHERE id_curso IN (SELECT id FROM cursos WHERE $id_curso IN (id,id_fusion))";
	$calc_situacion_curso = consulta_dml($SQL_calc_situacion_curso);

	if ($calc_nf_curso > 0) {
		echo(msje_js("Se ha calculado la Nota Final para los alumno(a)s del curso"));
	}

//	if ($calc_nf_curso <> $curso[0]['cant_alumnos']) {
//		echo(msje_js("No se ha calculado la Nota Final para todo(a)s lo(a)s alumno(a)s del curso.\\n"
//		            ."Es probable que falten notas por ingresar"));
//	}
		
 	echo(js("window.location='$enlbase=calificaciones_ver_curso&id_curso=$id_curso';"));
	
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<!-- Fin: <?php echo($modulo); ?> -->

