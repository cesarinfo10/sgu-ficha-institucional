<?php

session_start();
include("funciones.php");

$modulo = "comprobante_inscripcion_asignaturas";

if($argv[1]<>"") {
	$id_alumno = $argv[1];
}

$id_alumno = $_SESSION['id'];

$SQL_alumno = "SELECT id,rut,nombre,carrera,cohorte
               FROM vista_alumnos AS va
               WHERE id='$id_alumno';";
$alumno = consulta_sql($SQL_alumno);
if (count($alumno) == 1) {
	$SQL_alumno_cursos_inscritos = "SELECT vci.id_curso,vci.cod_asignatura||'-'||vci.seccion||' '||vci.asignatura AS asignatura,vci.semestre||'-'||vci.ano AS periodo,vci.profesor,
                                               coalesce(vci.sesion1,'')||coalesce(vci.sesion2,'')||coalesce(vci.sesion3,'') AS horario,to_char(vci.fecha,'DD-MM-YYYY HH24:MI') AS fecha,
											   c.tipo_clase 
	                                FROM vista_cursos_inscritos AS vci
									LEFT JOIN cursos AS c ON c.id=vci.id_curso 
	                                WHERE vci.id_alumno=$id_alumno AND vci.cerrada
	                                ORDER BY vci.asignatura;";
	$alumno_cursos_inscritos = consulta_sql($SQL_alumno_cursos_inscritos);

	$SQL_cursos_ins_alu = "SELECT ic.id_curso FROM inscripciones_cursos AS ic WHERE id_alumno=$id_alumno  AND cerrada";
	$SQL_mis_horarios = "SELECT id,cod_asignatura||'-'||seccion AS cod_asig,asignatura AS nombre_asig,
	                            dia1 AS dia_curso,horario1 AS horario_curso,sala1 AS sala_curso
	                     FROM vista_cursos
	                     WHERE id IN ($SQL_cursos_ins_alu)
	                     UNION ALL
	                     SELECT id,cod_asignatura||'-'||seccion AS cod_asig,asignatura AS nombre_asig,
	                            dia2 AS dia_curso,horario2 AS horario_curso,sala2 AS sala_curso
	                     FROM vista_cursos
	                     WHERE id IN ($SQL_cursos_ins_alu)
	                     UNION ALL
	                     SELECT id,cod_asignatura||'-'||seccion AS cod_asig,asignatura AS nombre_asig,
	                            dia3 AS dia_curso,horario3 AS horario_curso,sala3 AS sala_curso
	                     FROM vista_cursos
	                     WHERE id IN ($SQL_cursos_ins_alu)
	                     ORDER BY horario_curso,dia_curso";
	$mis_horarios = consulta_sql($SQL_mis_horarios);
	
	$HTML_mis_horarios = "";
	
	$horarios = consulta_sql("SELECT trim(id) as id FROM horarios ORDER BY id");

	for ($y=0;$y<count($horarios);$y++) {
		$horario = $horarios[$y]['id'];
	
	//for ($mod_horario=65;$mod_horario<73;$mod_horario++) {
	
		//$horario = chr($mod_horario);
				
		$HTML_mis_horarios .= "<tr bgcolor='#ffffff'><td valign='middle' align='center'><b>$horario</b></td>";
		
		for ($dia=1;$dia<7;$dia++) {
	
			$encontrado = false;
			$celda_horario = ""; 
			for ($x=0;$x<count($mis_horarios);$x++) {
				extract($mis_horarios[$x]);
	
				if ($dia_curso == $dia && trim($horario_curso) == $horario) {
	
			   	$enl         = "$enlbase=ver_mi_curso&id_curso=$id_curso";
					//$nombre_asig = "<a class='enlitem' href='$enl'>$nombre_asig</a>";
	
	 				if ($encontrado) { $celda_horario .= "<hr width='100%' size=1 noshade>"; }
					$celda_horario  .= "<u>$cod_asig</u><br>"
					                .  "<b>$nombre_asig</b><br>"  
					                .  "Sala: $sala_curso";
					$encontrado = true;
				} 
				
			}
			
			
			if (!$encontrado) {
				$HTML_mis_horarios .= "<td width='120'>&nbsp;</td>";
			} else {
				$HTML_mis_horarios .= "<td width='120' align='center' valign='middle'>$celda_horario</td>";
			}
			
		}
			
		$HTML_mis_horarios .= "</tr>\n";
	
	}



	extract($alumno[0]);
	$IDENTIFICACION_ALUMNO = "<table cellpadding='2' cellspacing='0' border='0' align='center'>".$LF
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
	
	$LISTA_DE_CURSOS = "<table cellpadding='3' cellspacing='1' border='0' bgcolor='#1A1A1A' align='center'>".$LF
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
		                  . "    <td>$horario<br>$tipo_clase</td>".$LF
		                  . "    <td>$fecha</td>".$LF
		                  . "  </tr>";
	}
	$LISTA_DE_CURSOS .= "</table>".$LF
	                  . "<center>Cursos inscritos: " . count($alumno_cursos_inscritos) . "</center><br>".$LF;
	                  

   $HORARIO = "<table cellpadding='3' cellspacing='1' border='0' bgcolor='#1A1A1A' align='center'>".$LF
            . "  <tr bgcolor='#e5e5e5'>".$LF
            . "    <td align='center' colspan='7'><b>Horario</b></td>".$LF
            . "  </tr>".$LF
            . "  <tr bgcolor='#e5e5e5'>".$LF
            . "    <td align='center'><small><b>Módulo</b></small></td>".$LF
            . "    <td align='center'><b>Lunes</b></td>".$LF
            . "    <td align='center'><b>Martes</b></td>".$LF
            . "    <td align='center'><b>Miércoles</b></td>".$LF
            . "    <td align='center'><b>Jueves</b></td>".$LF
            . "    <td align='center'><b>Viernes</b></td>".$LF
            . "    <td align='center'><b>Sábado</b></td>".$LF
            . "  </tr>".$LF
            . $HTML_mis_horarios.$LF
            . "</table>".$LF
            . "<br><br><br><br>".$LF;


	$FIRMAS = "<table width='100%' align='center'>".$LF
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
