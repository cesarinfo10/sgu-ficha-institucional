<html lang="es">
  <head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type">
    <link rel="shortcut icon" href="img/logo_sgu.ico">
    <title>UMC - SGU - Registro Acad&eacute;mico</title>
  </head>
  <body bgcolor="#ffffff" topmargin="5" leftmargin="5" rightmargin="5">
<?php

session_start();
include("funciones.php");

$modulo = "comprobante_inscripcion_asignaturas";

if($argv[1]<>"") {
	$id_alumno = $argv[1];
}

$id_alumno = $_REQUEST['id_alumno'];

$SQL_alumno = "SELECT id,rut,nombre,carrera,cohorte
               FROM vista_alumnos AS va
               WHERE id='$id_alumno';";
$alumno = consulta_sql($SQL_alumno);
if (count($alumno) == 1) {
	$SQL_alumno_cursos_inscritos = "SELECT id_curso,cod_asignatura||'-'||seccion||' '||asignatura AS asignatura,semestre||'-'||ano AS periodo,profesor,
                                               coalesce(sesion1,'')||coalesce(sesion2,'')||coalesce(sesion3,'') AS horario,to_char(fecha,'DD-MM-YYYY HH24:MI') AS fecha 
	                                FROM vista_cursos_inscritos 
	                                WHERE id_alumno=$id_alumno AND cerrada
	                                ORDER BY asignatura;";
	$alumno_cursos_inscritos = consulta_sql($SQL_alumno_cursos_inscritos);

	extract($alumno[0]);
	$IDENTIFICACION_ALUMNO = "<table cellpadding='2' cellspacing='0' border='0'>".$LF
	                       . "  <tr>".$LF
	                       . "    <td align='right'>ID:</td>".$LF
	                       . "    <td bgcolor='#E5F8FF'>$id</td>".$LF
	                       . "    <td align='right'>R.U.T.:</td>".$LF
	                       . "    <td bgcolor='#E5F8FF'>$rut</td>".$LF
	                       . "  </tr>".$LF
	                       . "  <tr>".$LF
	                       . "    <td align='right'>Nombre:</td>".$LF
	                       . "    <td bgcolor='#E5F8FF' colspan='3'>$nombre</td>".$LF
	                       . "  </tr>".$LF
	                       . "  <tr>".$LF
	                       . "    <td align='right'>Carrera:</td>".$LF
	                       . "    <td bgcolor='#E5F8FF'>$carrera</td>".$LF
	                       . "    <td align='right'>Año Ingreso (Cohorte):</td>".$LF
	                       . "    <td bgcolor='#E5F8FF'>$cohorte</td>".$LF
	                       . "  </tr>".$LF
	                       . "</table><br>";
	
	$LISTA_DE_CURSOS = "<table cellpadding='3' cellspacing='1' border='0' bgcolor='#1A1A1A'>".$LF
	                 . "  <tr bgcolor='#e5e5e5'>".$LF
	                 . "    <td align='center' colspan='6'><b>Cursos Inscritos</b></td>".$LF
	                 . "  </tr>".$LF
	                 . "  <tr bgcolor='#e5e5e5'>".$LF
	                 . "    <td align='center'><b>ID</b></td>".$LF
	                 . "    <td align='center'><b>Asignatura</b></td>".$LF
	                 . "    <td align='center'><b>Periodo</b></td>".$LF
	                 . "    <td align='center'><b>Profesor Cátedra</b></td>".$LF
	                 . "    <td align='center'><b>Horario</b></td>".$LF
	                 . "    <td align='center'><b>Fecha</b></td>".$LF
	                 . "  </tr>";
	for($x=0;$x<count($alumno_cursos_inscritos);$x++) {
		extract($alumno_cursos_inscritos[$x]);
		$LISTA_DE_CURSOS .= "  <tr bgcolor='#ffffff'>".$LF
		                  . "    <td>$id_curso</td>".$LF
		                  . "    <td>$asignatura</td>".$LF
		                  . "    <td>$periodo</td>".$LF
		                  . "    <td>$profesor</td>".$LF
		                  . "    <td>$horario</td>".$LF
		                  . "    <td>$fecha</td>".$LF
		                  . "  </tr>";
	}
	$LISTA_DE_CURSOS .= "</table>".$LF
	                  . "Cursos inscritos: " . count($alumno_cursos_inscritos) . "<br>".$LF
	                  . "<br><br><br><br>".$LF;

	$FIRMAS = "<table width='100%'>".$LF
	        . "  <tr>".$LF
	        . "    <td align='center' width='40%'><hr noshade size='1'><b>$nombre</b><br>Alumno</td>".$LF
	        . "    <td align='center' valign='top' width='10%'></td>".$LF
	        . "    <td align='center' valign='top' width='30%'><img src='img/logo_RegAcad.jpg'><br><b>$nombre_real_usuario</b><br>REGISTRO ACADÉMICO</td>".$LF
	        . "  </tr>".$LF
	        . "</table>".$LF;
	
	$HTML = ""; 
	include("comprobante_ci_formato.php");
	
	echo($HTML);
}
?>
  </body>
</html>
