<?php

session_start();
include("funciones.php");

$modulo = "acta";

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

//include("validar_modulo.php");

if($argv[1]=="") {
	$id_curso = $_REQUEST['id_curso'];
} else {
	$id_curso = $argv[1];
}

$SQL_curso = "SELECT vc.id AS id_curso,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,
                     vc.carrera AS carrera_ramo,vc.profesor,vc.sesion1,vc.sesion2,vc.sesion3,
                     vc.semestre||'-'||vc.ano AS periodo,u.nombre||' '||u.apellido AS nombre_director,
                     cantidad_alumnos(vc.id) AS cant_alumnos,carrera,cb.cod 
               FROM vista_cursos AS vc
               LEFT JOIN cursos AS cu USING (id)
               LEFT JOIN vista_cursos_cod_barras AS cb USING (id)
               LEFT JOIN carreras AS c ON c.id=vc.id_carrera
               LEFT JOIN escuelas AS e ON e.id=c.id_escuela
               LEFT JOIN usuarios AS u ON u.id=e.id_director               
               WHERE vc.id='$id_curso'
               ORDER BY carrera,cod_asignatura;";
$curso = consulta_sql($SQL_curso);
//echo($SQL_curso);
if (count($curso) >= 1) {
	$SQL_alumnos_curso = "SELECT va.id AS id_alumno,
                                     CASE
                                       WHEN ca.id_estado IN (6,10,11) THEN '<strike>-- '||initcap(va.nombre)||' --</strike>'
                                       ELSE initcap(va.nombre)
                                     END AS nombre_alumno	                              
	                       FROM cargas_academicas AS ca
	                       LEFT JOIN vista_alumnos AS va ON va.id=ca.id_alumno 
	                       WHERE id_curso IN (SELECT id FROM cursos WHERE id=$id_curso OR id_fusion=$id_curso)
	                       ORDER BY fecha_mod,nombre";
	$alumnos = consulta_sql($SQL_alumnos_curso);

	extract($curso[0]);
	$IDENTIFICACION_CURSO = "<table style='border: 1px solid'>"
	                      . "  <tr><td nowrap align='center'>"
	                      . "    $asignatura<br>"
	                      . "    $carrera<br>"
	                      . "    $profesor<br>"
	                      . "    $sesion1 $sesion2 $sesion3<br>"
	                      . "    $periodo<br>"
	                      . "    Nº Acta: $id_curso<br>"
	                      . "    <img src='/sgu/php-barcode/barcode.php?code=$cod&scale=1'><br>"
	                      . "  </td></tr>"
	                      . "</table>";
	                      
	$borde = "border: 1px solid #000000;";
	$LISTA_DE_CURSO = "<table cellpadding='0' cellspacing='0' border='0' bgcolor='#d4d4d4' style='width: 8.40cm; border: 1px solid #000000'>".$LF
	                . "  <tr bgcolor='#e5e5e5'>".$LF
	                . "    <td align='center' style='width: 8.40cm; $borde' colspan='2'><b>Alumnos</b></td>".$LF
	                . "  </tr>".$LF
	                . "  <tr bgcolor='#e5e5e5'>".$LF
	                . "    <td align='center' style='width: 1.45cm; $borde'><b>ID</b></td>".$LF
	                . "    <td align='center' style='width: 6.95cm; $borde'><b>Nombre</b></td>".$LF
	                . "  </tr>";

	for($x=0;$x<count($alumnos);$x++) {
		extract($alumnos[$x]);		
		$LISTA_DE_CURSO .= "  <tr bgcolor='#ffffff' style='height: 0.61cm; $borde'>".$LF
		                 . "    <td align='center' style='height: 0.61cm; $borde'>$id_alumno</td>".$LF
		                 . "    <td align='left' style='height: 0.61cm; $borde; padding-left: 2px'>$nombre_alumno</td>".$LF
		                 . "  </tr>";              
	}
	$LISTA_DE_CURSO .= "</table>".$LF
	                 . "Alumno(a)s inscrito(a)s: $cant_alumnos<br>".$LF;

	
	$HTML = ""; 
	include("libro_de_clases_fmt.php");
	
	echo($HTML);
	echo(js("window.print()"));
	echo(js("window.close()"));

	
	/*$HTML = iconv("UTF-8","ISO-8859-1",$HTML);
	$archivo = "acta_".$id_curso;
	$hand=fopen($archivo,"w");
	fwrite($hand,$HTML);
	fclose($hand);
	$html2pdf = "htmldoc -t pdf --fontsize 10 --fontspacing 1 --no-strict --size 21.5x33cm --bodyfont helvetica "
	          . "--left 1in --top 1cm --right 1cm --bottom 1cm --footer '  /' --header '   ' --no-embedfonts "
	          . "--compression=9 --encryption --permissions print,no-copy,no-annotate,no-modify "
	          . "--webpage $archivo ";
	header("Content-Type: application/pdf");
	header("Content-Disposition: attachment; filename=$archivo.pdf");
	passthru($html2pdf);
	unlink($archivo);*/	
}
?>
