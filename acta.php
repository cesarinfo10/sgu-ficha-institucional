<?php

session_start();
include("funciones.php");

$tipo = "";
if ($argv[1]=="") {
	$id_curso = $_REQUEST['id_curso'];
	$tipo     = $_REQUEST['tipo'];
} elseif (is_numeric($argv[1])) {
	$id_curso = $argv[1];
}

if (!$_SESSION['autentificado'] || !is_numeric($id_curso)) {
	header("Location: index.php");
	exit;
}

$modulo = "acta";
include("validar_modulo.php");

//$nombre_real_usuario = nombre_real_usuario($_SESSION['usuario'],$_SESSION['tipo']);
//$nombre_regacad = $nombre_real_usuario;
$nombre_regacad = "Andrea Aranela Suazo";

$SQL_curso = "SELECT vc.id AS id_curso,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,
                     vc.carrera AS carrera_ramo,vc.profesor,
                     vc.semestre||'-'||vc.ano AS periodo,u.nombre||' '||u.apellido AS nombre_director,
                     cantidad_alumnos(vc.id) AS cant_alumnos,carrera,fecha_acta
              FROM vista_cursos AS vc
              LEFT JOIN cursos USING (id)
              LEFT JOIN carreras AS c ON c.id=vc.id_carrera
              LEFT JOIN escuelas AS e ON e.id=c.id_escuela
              LEFT JOIN usuarios AS u ON u.id=e.id_director
              WHERE vc.id='$id_curso' AND id_fusion IS NULL
              ORDER BY carrera,cod_asignatura;";
$curso = consulta_sql($SQL_curso);

if (count($curso) == 1) {
	extract($curso[0]);
	
	$SQL_calc_acta = "SELECT CASE WHEN count(ca.id) = count(ca.id_estado) THEN true ELSE false END AS acta_imprimible
					  FROM cargas_academicas AS ca
					  WHERE ca.id_curso IN (SELECT id FROM cursos WHERE $id_curso IN (id,id_fusion))";
	$calc_acta = consulta_sql($SQL_calc_acta);
	
	if ($calc_acta[0]['acta_imprimible'] == "f") {
		echo(msje_js("No se han calculado las notas y/o las situaciones finales.\\n\\n"
		            ."No es posible continuar"));
		exit;
	} else {
		// Identificar a los alumnos que han eliminado formalmente el curso.
		$SQL_alumnos_eliminar = "SELECT id_ca
								 FROM vista_cursos_alumnos AS vca
								 LEFT JOIN alumnos AS a ON a.id=vca.id_alumno 
								 WHERE (vca.id_situacion=6 AND a.estado NOT IN (6,7)) 
								   AND vca.id_curso IN (SELECT id FROM cursos WHERE $id_curso IN (id,id_fusion))";

		$SQL_alumnos_curso_eliminar = "SELECT upper(nombre) AS nombre_alumno
									   FROM cargas_academicas AS ca
									   LEFT JOIN vista_alumnos AS va ON va.id=ca.id_alumno
									   WHERE ca.id IN ($SQL_alumnos_eliminar)";
		$alumnos_curso_eliminar = consulta_sql($SQL_alumnos_curso_eliminar);
		if (count($alumnos_curso_eliminar) > 0) {		
			$alumnos_eliminar = "";
			for ($x=0;$x<count($alumnos_curso_eliminar);$x++) {
				$alumnos_eliminar .= "- " . $alumnos_curso_eliminar[$x]['nombre_alumno'] . "\\n";
			}

			$cod_auth = md5("nro_acta_$id_curso");
			
			if ($_REQUEST['cod_eliminacion'] == "") {
				$msje = "En este curso hay alumnos que han eliminado (suspendido) esta asignatura. "
					  . "A continuación se detallan:\\n\\n"
					  . $alumnos_eliminar ."\\n"
					  . "Por lo tanto corresponde eliminarlos definitivamente del acta. Para obtener el acta "
					  . "pinche en Aceptar (borrará definitivamente a este(os) alumno(s) que se muestran. "
					  . "Si no está seguro, entonces pinche en Cancelar";
				$url_si = "acta.php?id_curso=$id_curso&cod_eliminacion=$cod_auth";
				$url_no = "javascript:window.close()";
				echo(confirma_js($msje,$url_si,$url_no));
				exit;
			} elseif ($_REQUEST['cod_eliminacion'] == $cod_auth) {
				$SQLdelete_alumnos_eliminar = "DELETE FROM cargas_academicas WHERE id IN ($SQL_alumnos_eliminar)";
				consulta_dml($SQLdelete_alumnos_eliminar);
				$curso = consulta_sql($SQL_curso);
				extract($curso[0]);
			}
		}
	}
	
	if ($tipo <> "complementaria") {
		$SQL_alumnos_curso = "SELECT id_ca,id_alumno,vca.id_curso AS id_curso_ca,vca.rut,initcap(nombre_alumno) AS nombre_alumno,
									 s1,nc,s2,recup,nf,situacion,carrera_curso
							  FROM vista_cursos_alumnos AS vca
							  WHERE id_curso IN (SELECT id FROM cursos WHERE $id_curso IN (id,id_fusion))
							  ORDER BY carrera_curso,nombre_alumno";
							  
	} elseif ($tipo == "complementaria") {
		$SQL_alumnos_curso = "SELECT id_ca,id_alumno,vca.id_curso AS id_curso_ca,vca.rut,initcap(nombre_alumno) AS nombre_alumno,
									 s1,nc,s2,recup,nf,situacion,carrera_curso
							  FROM vista_cursos_alumnos AS vca
							  WHERE id_curso IN (SELECT id FROM cursos WHERE $id_curso IN (id,id_fusion))
							    AND (fecha_mod>'$fecha_acta'::timestamp OR fecha_mod_notas>'$fecha_acta'::timestamp)
							  ORDER BY carrera_curso,nombre_alumno";
							  
	}
	
	$alumnos = consulta_sql($SQL_alumnos_curso);
	if (count($alumnos) == 0) {
		echo(msje_js("Esta acta no contiene alumnos luego de procesar. No se puede continuar"));
		echo(js("window.close();"));
		exit;
	}

	$fecha_emision = strftime("%x %X");
	
	$SQL_cursos = "SELECT vc.ano,vc.semestre,vc.profesor,u.nombre AS director,vc.carrera AS carrera_acta,
	                      char_comma_sum(vc.id::text) AS id_cursos,
	                      char_comma_sum(nombre_asig) AS asignatura,
	                      sum(cant_al_curso) AS cant_alumnos
	               FROM vista_cursos AS vc
	               LEFT JOIN cursos USING (id)
	               LEFT JOIN carreras AS c ON c.id=vc.id_carrera
	               LEFT JOIN escuelas AS e ON e.id=c.id_escuela
	               LEFT JOIN vista_usuarios AS u ON u.id=e.id_director
	               WHERE vc.id IN (SELECT id FROM cursos WHERE $id_curso IN (id,id_fusion))
	               GROUP BY vc.ano,vc.semestre,vc.profesor,u.nombre,vc.carrera
	               ORDER BY vc.ano,vc.semestre,vc.profesor,u.nombre,vc.carrera";
	$cursos = consulta_sql($SQL_cursos);

	$HTML = "";
	$_HTML = array();
	$z = 0;
	for($y=0;$y<count($cursos);$y++) {
		extract($cursos[$y]);
		if ($cant_alumnos > 0) {
			$IDENTIFICACION_CURSO = "<table cellpadding='2' cellspacing='0' border='0'>".$LF
								  . "  <tr>".$LF
								  . "    <td align='right'>Carrera:</td>"
								  . "    <td colspan='3' bgcolor='#E5F8FF' nowrap>$carrera_acta</td>"
								  . "  </tr>".$LF
								  . "  <tr>".$LF
								  . "    <td align='right'>Número de acta:</td>".$LF
								  . "    <td bgcolor='#E5F8FF' nowrap>".str_replace(",","<br>",$id_cursos)."</td>".$LF	
								  . "    <td align='right'>Periodo:</td>".$LF
								  . "    <td bgcolor='#E5F8FF' nowrap>$periodo</td>".$LF
								  . "  </tr>".$LF
								  . "  <tr>".$LF
								  . "    <td align='right'>Asignatura:</td>".$LF
								  . "    <td bgcolor='#E5F8FF' colspan='3' nowrap>".str_replace(",","<br>",$asignatura)."</td>".$LF
								  . "  </tr>".$LF
								  . "  <tr>".$LF
								  . "    <td align='right'>Profesor:</td>".$LF
								  . "    <td bgcolor='#E5F8FF' nowrap>$profesor</td>".$LF
								  . "    <td align='right'>Nº de Inscritos:</td>".$LF
								  . "    <td bgcolor='#E5F8FF' nowrap>$cant_alumnos alumno(a)s</td>".$LF
								  . "  </tr>".$LF
								  . "</table><br>";
			
			$LISTA_DE_CURSO = "<table cellpadding='3' cellspacing='2' border='0' bgcolor='#d4d4d4'>".$LF
							. "  <tr bgcolor='#e5e5e5'>".$LF
							. "    <td align='center' colspan='3'><b>Alumnos</b></td>".$LF
							. "    <td align='center' colspan='6'><b>Calificaciones</b></td>".$LF
							. "  </tr>".$LF
							. "  <tr bgcolor='#e5e5e5'>".$LF
							. "    <td align='center'><b>ID</b></td>".$LF
							. "    <td align='center'><b>RUT</b></td>".$LF
							. "    <td align='center' nowrap><b>Nombre</b></td>".$LF
							. "    <td align='center' bgcolor='#DCDCDC'><b>S1</b></td>".$LF
							. "    <td align='center'><b>NC</b></td>".$LF
							. "    <td align='center' bgcolor='#DCDCDC'><b>S2</b></td>".$LF
							. "    <td align='center'><b>Rec.</b></td>".$LF
							. "    <td align='center' bgcolor='#DCDCDC'><b>NF</b></td>".$LF
							. "    <td align='center'><b>Situación</b></td>".$LF
							. "  </tr>";

			for($x=0;$x<count($alumnos);$x++) {
				extract($alumnos[$x]);
				if ($carrera_curso == $carrera_acta) {
					$LISTA_DE_CURSO .= "  <tr bgcolor='#ffffff'>".$LF
									 . "    <td align='center'>$id_alumno</td>".$LF
									 . "    <td align='right'>$rut</td>".$LF
									 . "    <td nowrap>$nombre_alumno</td>".$LF
									 . "    <td align='center' bgcolor='#DCDCDC'>$s1</td>".$LF
									 . "    <td align='center'>$nc</td>".$LF
									 . "    <td align='center' bgcolor='#DCDCDC'>$s2</td>".$LF
									 . "    <td align='center'>$recup</td>".$LF
									 . "    <td align='center' bgcolor='#DCDCDC'>$nf</td>".$LF
									 . "    <td>$situacion</td>".$LF
									 . "  </tr>";
				}
			}
			$LISTA_DE_CURSO .= "</table>".$LF
							 . "Alumno(a)s inscrito(a)s: $cant_alumnos<br>".$LF;

			$FIRMAS = "<table width='100%'>".$LF
					. "  <tr>".$LF
					. "    <td align='center' valign='top' width='30%'><hr noshade size='2'><b>$director</b><br>Dirección de Escuela</td>".$LF
					. "    <td align='center' valign='top' width='5%'></td>".$LF
					. "    <td align='center' valign='top' width='30%'><hr noshade size='2'><b>$profesor</b><br>Profesor(a) del Curso</td>".$LF
					. "    <td align='center' valign='top' width='5%'></td>".$LF
					. "    <td align='center' valign='top' width='30%'><hr noshade size='2'><b>$nombre_regacad</b><br>Jefe (a) REGISTRO ACADÉMICO</td>".$LF
					. "  </tr>".$LF
					. "</table>".$LF;

			if ($tipo == "complementaria") {
				$FIRMAS = "<table width='100%'>".$LF
						. "  <tr>".$LF
						. "    <td align='center' valign='top' width='30%'><hr noshade size='2'><b>$director</b><br>Dirección de Escuela</td>".$LF
						. "    <td align='center' valign='top' width='5%'></td>".$LF
						. "    <td align='center' valign='top' width='30%'></td>".$LF
						. "    <td align='center' valign='top' width='5%'></td>".$LF
						. "    <td align='center' valign='top' width='30%'><hr noshade size='2'><b>$nombre_regacad</b><br>REGISTRO ACADÉMICO</td>".$LF
						. "  </tr>".$LF
						. "</table>".$LF;
			}

			if ($tipo <> "complementaria") { include("acta_formato.php"); } else { include("acta_complementaria_formato.php"); }
			$_HTML[$z] = iconv("UTF-8","ISO-8859-1//TRANSLIT",$HTML);
			$HTML = "";
			$z++;
		}
	}
	
	for($z=0;$z<count($_HTML);$z++) {
		if ($_HTML[$z] <> "") {
			$archivo = "tmp/acta_$z-".$id_curso;
			if ($tipo == "complementaria") { $archivo = "tmp/acta_complementaria_$z-".$id_curso; }
			$hand=fopen($archivo,"w");
			fwrite($hand,$_HTML[$z]);
			fclose($hand);
			$html2pdf = "htmldoc -t pdf --fontsize 10 --fontspacing 1 --no-strict --size 21.5x33cm --bodyfont helvetica "
					  . "--left 1in --top 5cm --right 1cm --bottom 1cm --footer '  /' --header '   ' --no-embedfonts "
					  . "--no-compression --permissions print,no-copy,no-annotate,no-modify --no-encryption "
					  . "--webpage $archivo -f $archivo.pdf";		
			shell_exec($html2pdf);
		}					
	}
	shell_exec("pdftk  tmp/acta_*".$id_curso.".pdf cat output tmp/acta_".$id_curso.".pdf");
	header("Content-Type: application/pdf");
	header("Content-Disposition: attachment; filename=acta_".$id_curso.".pdf");
	
	passthru("cat tmp/acta_$id_curso.pdf");
	array_map("unlink", glob("tmp/acta_*".$id_curso."*"));

	
	if ($tipo == "complementaria") {
		consulta_dml("UPDATE cursos SET cerrado=true,fecha_acta_comp=now(),id_usuario_emisor_acta={$_SESSION['id_usuario']},recep_acta_comp=false WHERE $id_curso IN (id,id_fusion);");
	} else {
		consulta_dml("UPDATE cursos SET cerrado=true,fecha_acta=now(),id_usuario_emisor_acta={$_SESSION['id_usuario']},recep_acta=false WHERE $id_curso IN (id,id_fusion);");
	}
	echo(js("window.close();"));
		
}
?>
