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

$SQL_curso = "SELECT vc.id AS id_curso,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,
                     vc.carrera AS carrera_ramo,vc.profesor,
                     vc.semestre||'-'||vc.ano AS periodo,u.nombre||' '||u.apellido AS nombre_director,
                     cantidad_alumnos(vc.id) AS cant_alumnos,carrera,fecha_acta
              FROM vista_cursos AS vc
              LEFT JOIN cursos USING (id)
              LEFT JOIN carreras AS c ON c.id=vc.id_carrera
              LEFT JOIN escuelas AS e ON e.id=c.id_escuela
              LEFT JOIN usuarios AS u ON u.id=e.id_director
              WHERE vc.id='$id_curso'
              ORDER BY carrera,cod_asignatura;";
$curso = consulta_sql($SQL_curso);

if (count($curso) == 1) {
	extract($curso[0]);
	if ($tipo <> "complementaria") {
		$cod_auth = md5("nro_acta_$id_curso");

		$SQL_calc_acta = "SELECT c.id,c.cerrado,c.fecha_acta,
								 CASE WHEN count(ca.id) = count(ca.id_estado) THEN true ELSE false END AS acta_imprimible
						  FROM cargas_academicas AS ca
						  LEFT JOIN cursos AS c ON c.id=ca.id_curso
						  WHERE ca.id_curso=$id_curso
						  GROUP BY c.id,c.cerrado,c.fecha_acta";
		$calc_acta = consulta_sql($SQL_calc_acta);
			
		if ($_REQUEST['cod_continuar'] == "" && ($calc_acta[0]['acta_imprimible'] == "f" || $calc_acta[0]['cerrado'] == "t") && $_SESSION['tipo']<>0) {
			$msje = "Este curso actualmente está cerrado o bien no se han calculado las situaciones (y/o notas) finales.\\n"
				  . "Desea proceder de todas formas?";
			$url_si = "acta.php?id_curso=$id_curso&cod_continuar=$cod_auth";
			$url_no = "javascript:window.close();";
			echo(confirma_js($msje,$url_si,$url_no));
			exit;
		}
		
		if ((!empty($_REQUEST['cod_continuar']) && $_REQUEST['cod_continuar'] <> $cod_auth)) {
			echo(js('window.close();'));
			exit;
		}
		
		// Identificar a los alumnos que han eliminado formalmente el curso.
		$SQL_alumnos_eliminar = "SELECT id_ca
								 FROM vista_cursos_alumnos AS vca
								 LEFT JOIN alumnos AS a ON a.id=vca.id_alumno 
								 WHERE (vca.id_situacion=6 AND a.estado NOT IN (6,7)) AND vca.id_curso=$id_curso";

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
			}
			
			if ($_REQUEST['cod_eliminacion'] == $cod_auth) {
				$SQLdelete_alumnos_eliminar = "DELETE FROM cargas_academicas WHERE id IN ($SQL_alumnos_eliminar)";
				consulta_dml($SQLdelete_alumnos_eliminar);
				$curso = consulta_sql($SQL_curso);
				extract($curso[0]);
			}
		}

		$SQL_alumnos_curso = "SELECT id_ca,id_alumno,vca.id_curso AS id_curso_ca,vca.rut,initcap(nombre_alumno) AS nombre_alumno,
									 s1,nc,s2,recup,nf,situacion
							  FROM vista_cursos_alumnos AS vca
							  WHERE id_curso=$id_curso
							  ORDER BY id_curso,nombre_alumno";
	} elseif ($tipo == "complementaria") {
		$SQL_alumnos_curso = "SELECT id_ca,id_alumno,vca.id_curso AS id_curso_ca,vca.rut,initcap(nombre_alumno) AS nombre_alumno,
									 s1,nc,s2,recup,nf,situacion
							  FROM vista_cursos_alumnos AS vca
							  WHERE id_curso=$id_curso 
							    AND (fecha_mod>'$fecha_acta'::timestamp OR fecha_mod_notas>'$fecha_acta'::timestamp)
							  ORDER BY id_curso,nombre_alumno";
	}
	$alumnos = consulta_sql($SQL_alumnos_curso);
	if (count($alumnos) == 0) {
		echo(msje_js("Esta acta no contiene alumnos luego de procesar. No se puede continuar"));
		echo(js("window.close();"));
		exit;
	}

	$fecha_emision = strftime("%x %X");

	$IDENTIFICACION_CURSO = "<table cellpadding='2' cellspacing='0' border='0' width='100%'>".$LF
	                      . "  <tr>".$LF
	                      . "    <td align='right'>Carrera:</td>"
	                      . "    <td colspan='3' bgcolor='#E5F8FF' nowrap>$carrera</td>"
	                      . "  </tr>".$LF
	                      . "  <tr>".$LF
	                      . "    <td align='right'>Número de acta:</td>".$LF
	                      . "    <td bgcolor='#E5F8FF' nowrap>$id_curso</td>".$LF
	                      . "    <td align='right'>Nº de Inscritos:</td>".$LF
	                      . "    <td bgcolor='#E5F8FF' nowrap>$cant_alumnos alumno(a)s</td>".$LF
	                      . "  </tr>".$LF
	                      . "  <tr>".$LF
	                      . "    <td align='right'>Asignatura:</td>".$LF
	                      . "    <td bgcolor='#E5F8FF' colspan='3' nowrap>$asignatura</td>".$LF
	                      . "  </tr>".$LF
	                      . "  <tr>".$LF
	                      . "    <td align='right'>Profesor:</td>".$LF
	                      . "    <td bgcolor='#E5F8FF' nowrap>$profesor</td>".$LF
	                      . "    <td align='right'>Periodo:</td>".$LF
	                      . "    <td bgcolor='#E5F8FF' nowrap>$periodo</td>".$LF
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
	$LISTA_DE_CURSO .= "</table>".$LF
	                 . "Alumno(a)s inscrito(a)s: $cant_alumnos<br>".$LF;

	//$nombre_real_usuario = nombre_real_usuario($_SESSION['usuario'],$_SESSION['tipo']);
	//$nombre_regacad = $nombre_real_usuario;
	$nombre_regacad = "Andrea Aranela Suazo";
	

	$FIRMAS = "<table width='100%'>".$LF
	        . "  <tr>".$LF
	        . "    <td align='center' valign='bottom' width='45%'><hr noshade size='2'><b>$nombre_director</b><br>Dirección de Programa</td>".$LF
	        . "    <td align='center' valign='top' width='10%'></td>".$LF
	        . "    <td align='center' valign='bottom' width='45%'><hr noshade size='2'><b>$nombre_regacad</b><br>Jefe (a) REGISTRO ACADÉMICO</td>".$LF
	        . "  </tr>".$LF
	        . "</table>".$LF
	        . "<!-- <img width='130' src='img/firma_marellano.png'> <img width='200' src='img/firma_aaranela.png'> -->";

	if ($tipo == "complementaria") {
		$FIRMAS = "<table width='100%'>".$LF
				. "  <tr>".$LF
				. "    <td align='center' valign='top' width='30%'><hr noshade size='2'><b>$nombre_director</b><br>Dirección de Programa</td>".$LF
				. "    <td align='center' valign='top' width='5%'></td>".$LF
				. "    <td align='center' valign='top' width='30%'></td>".$LF
				. "    <td align='center' valign='top' width='5%'></td>".$LF
				. "    <td align='center' valign='top' width='30%'><hr noshade size='2'><b>$nombre_regacad</b><br>Jefa de Registro Académico</td>".$LF
				. "  </tr>".$LF
				. "</table>".$LF;
	}
	
	$HTML = "";
	
	
	$archivo = "acta_".$id_curso;
	if ($tipo == "complementaria") {
		include("acta_complementaria_formato.php");
		$archivo = "acta_complementaria_".$id_curso;
	}
	elseif ($tipo <> "complementaria") { include("acta_formato.php"); }
	
	//echo($HTML);
	
	$HTML = iconv("UTF-8","ISO-8859-1//TRANSLIT",$HTML);
	$hand=fopen($archivo,"w");
	fwrite($hand,$HTML);
	fclose($hand);
	$html2pdf = "htmldoc -t pdf --fontsize 10 --fontspacing 1 --no-strict --size 21.5x33cm --bodyfont helvetica "
	          . "--left 1in --top 5.5cm --right 1cm --bottom 1cm --footer '  /' --header '   ' --no-embedfonts "
	          . "--compression=9 --encryption --permissions print,no-copy,no-annotate,no-modify "
	          . "--webpage $archivo ";
	header("Content-Type: application/pdf");
	header("Content-Disposition: attachment; filename=$archivo.pdf");
	passthru($html2pdf);
	unlink($archivo);
	if ($tipo == "complementaria") {
		consulta_dml("UPDATE cursos SET cerrado=true,fecha_acta_comp=now(),id_usuario_emisor_acta={$_SESSION['id_usuario']},recep_acta_comp=false WHERE id=$id_curso;");
	} else {
		consulta_dml("UPDATE cursos SET cerrado=true,fecha_acta=now(),id_usuario_emisor_acta={$_SESSION['id_usuario']},recep_acta=false WHERE id=$id_curso;");
	}
	//echo(js("window.close();"));
		
}
?>
