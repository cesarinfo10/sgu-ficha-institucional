<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

//include("validar_modulo.php");

$id_curso = $_REQUEST['id_curso'];
$token    = $_REQUEST['token'];
$volver   = $_REQUEST['volver'];

if ($id_curso == "" || $token == "") {
	echo(js("location.href='principal.php?modulo=calificaciones';"));
	exit;
}

$SQL_curso = "SELECT cantidad_alumnos(id) AS cant_alumnos,cant_notas_parciales FROM cursos WHERE id=$id_curso";
$curso = consulta_sql($SQL_curso);

if (count($curso) > 0) {
	$cod_auth = md5("id_curso_$id_curso");
	
	$SQL_calif_parc = "SELECT (count(c1)+count(c2)+count(c3)+count(c4)+count(c5)+count(c6)+count(c7))::float/cantidad_alumnos($id_curso) AS cant_np_registradas
	                   FROM vista_calificaciones_parciales
	                   WHERE id_curso IN (SELECT id FROM cursos WHERE $id_curso IN (id,id_fusion))";
	$calif_parc = consulta_sql($SQL_calif_parc);
	if (count($calif_parc) > 0) {		
		if (intval($calif_parc[0]['cant_np_registradas']) < intval($curso[0]['cant_notas_parciales'])) {
			echo(msje_js("No están registradas todas las notas parciales que ha definido en Cantidad de Notas Parciales\\n"
			            ."No se puede calcular el promedio"));
			echo(js("location.href='$enlbase_sm=calificaciones_ver_curso_califpar&id_curso=$id_curso&token=$token';"));
			exit;
		}

		if ($calif_parc[0]['cant_np_registradas'] > $curso[0]['cant_notas_parciales'] && $_REQUEST['cod_anular'] == "") {
			$msje = "ATENCIÓN: En este momento hay más notas parciales registradas de las que corresponde.\\n\\n"
			      . "Esto ocurrió por que inicialmente usted definió un número determinado, luego registró esta "
			      . "cantidad de notas parciales y posteriormente redefinió la cantidad de notas parciales a un "
			      . "número menor que el inicial.\\n\\n"
			      . "El SGU para calcular el promedio debe obligatoriamente anular dichas notas, que se han "
			      . "registrado demás. Para esto, SGU conservará el número de calificaciones definidas, que estén "
			      . "más a la izquierda, es decir, si registró 4 columnas de calificaciones parciales y ahora ha "
			      . "redefinido a 2 la cantidad de estas notas, entonces SGU anulará C3 y C4\\n\\n"
			      . "Desea continuar?";
			$url_si = "$enlbase_sm=calificaciones_calcular_nc&id_curso=$id_curso&cod_anular=$cod_auth&token=$token";
			$url_no = "$enlbase_sm=calificaciones_ver_curso_califpar&id_curso=$id_curso&token=$token";
			echo(confirma_js($msje,$url_si,$url_no));
			exit;
		}
		
		if ($calif_parc[0]['cant_np_registradas'] > $curso[0]['cant_notas_parciales'] && $_REQUEST['cod_anular'] == $cod_auth) {
			$notas_anular = "";
			for ($x=$curso[0]['cant_notas_parciales']+1;$x<=7;$x++) { $notas_anular .= "c$x=null,"; }
			$notas_anular = substr($notas_anular,0,-1);
			$SQL_anular = "UPDATE calificaciones_parciales 
			               SET $notas_anular 
			               WHERE id_ca IN (SELECT id FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE $id_curso IN (id,id_fusion)))";
			consulta_dml($SQL_anular);
			echo(msje_js("Se ha procedido a anular las calificaciones sobrantes, ahora se procederá a calcular " 
			            ."el promedio de notas parciales y establecer la Nota de Cátedra"));
		}
	} 
	
	$SQL_calc_nc_curso = "UPDATE cargas_academicas
	                      SET nota_catedra = (suma_notas_parciales/cant_notas_parciales)::numeric(2,1),nota_final=null
	                      FROM vista_calif_parciales_promedio
	                      WHERE cargas_academicas.id_curso IN (SELECT id FROM cursos WHERE $id_curso IN (id,id_fusion)) AND cargas_academicas.id=vista_calif_parciales_promedio.id_ca;";
	$calc_nc_curso = consulta_dml($SQL_calc_nc_curso);

	if ($calc_nc_curso > 0) {
		echo(msje_js("Se ha calculado la Nota de Cátedra (Promedio de Notas Parciales) para los alumno(a)s del curso"));
	}

 	echo(js("location.href='$enlbase_sm=calificaciones_ver_curso_califpar&id_curso=$id_curso&token=$token';"));
	
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<!-- Fin: <?php echo($modulo); ?> -->

