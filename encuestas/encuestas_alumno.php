<?php

if ($_REQUEST['id_alumno'] == "") { echo(js("window.location='http://www.umcervantes.cl';")); }

$id_alumno         = $_REQUEST['id_alumno'];
$ANO_Encuesta      = $_REQUEST['ano'];
$SEMESTRE_Encuesta = $_REQUEST['semestre'];

$SQL_cursos_actuales = "SELECT id FROM cursos WHERE ano=$ANO_Encuesta AND semestre=$SEMESTRE_Encuesta AND (seccion < 9 OR fec_fin<now()::date)";

$SQL_alumno_cursos = "SELECT ca.id_curso,ca.id_alumno,vc.profesor,
                             vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,
                             CASE WHEN ee.id IS NULL THEN false ELSE true END AS contestada
                      FROM cargas_academicas AS ca
                      LEFT JOIN vista_cursos AS vc ON vc.id=ca.id_curso
                      LEFT JOIN encuestas.estudiantil AS ee ON (ee.id_alumno=ca.id_alumno AND ee.id_curso=ca.id_curso)
                      WHERE ca.id_alumno='$id_alumno' AND ca.id_curso IN ($SQL_cursos_actuales)
                        AND (ca.id_estado IS NULL OR ca.id_estado <> 6);";
$alumno_cursos_encuestas = consulta_sql($SQL_alumno_cursos);
if (count($alumno_cursos_encuestas) == 0) {
	echo(msje_js("ERROR: Tú no tienes cursos inscritos.\\n"
                    ."Por favor acércate a la brevedad a la Coordinación de Escuela correspondiente a tú carrera\\n"
                    ."Gracias"));
	echo(js("window.location='http://www.umcervantes.cl';"));
	exit;
}

$HTML_cursos_encuestas = "";
for ($x=0; $x<count($alumno_cursos_encuestas); $x++) {
	extract($alumno_cursos_encuestas[$x]);
	$contestar = "<font color='#009900'>Contestada</font>";

	if ($contestada == "f") {
		$enl = "?modulo=encuestas&id_alumno=$id_alumno&id_curso=$id_curso&arch_encuesta=estudiantil&ano=$ANO_Encuesta&semestre=$SEMESTRE_Encuesta";
		$enlace = "<a class='enlitem' href='$enl'>";
		$js_onClick = "onClick=\"window.location='$enl';\"";
		$contestar = "<a href='$enl'>CONTESTAR</a>";
	}

		
	$HTML_cursos_encuestas .= "<tr class='filaTabla' $js_onClick>\n"
	                       .  "  <td class='textoTabla'> $profesor</td>\n"
	                       .  "  <td class='textoTabla'> $asignatura</td>\n"
	                       .  "  <td class='textoTabla' align='center'> $contestar</td>\n"
	                       .  "</tr>\n";
		                   
}

?>

<div align="center" class="tituloModulo">
  Encuesta de Evaluación Docente de los Estudiantes
</div>
<br>
<div class="texto" align="justify">
  En el marco de la finalización de este periodo académico y el deseo de mejorar cada vez más la educación
  que impartimos, se realizará el proceso de evaluación del desempeño de nuestros profesores.<br>
  <br>
  Dicho proceso, da cuenta de nuestra preocupación constante de generar espacios de participación y ejercicio del 
  derecho de los estudiantes a expresarse respecto de la educación que reciben por parte de la Universidad.<br>
  <br>
  Para dar por finalizado el proceso y poder ingresar a su correo electrónico y a SGU, todas la preguntas deben ser 
  contestadas. Al terminar de contestar las preguntas del cuestionario, debe pinchar el botón "Terminar Encuesta", a 
  continuación el sistema guardará los datos y mostrará un mensaje de éxito en la aplicación de éste.<br>
  <br>
<!--  A partir del Viernes 21 de diciembre, tendrán acceso al cuestionario electrónico. Les rogamos encarecidamente, 
  contestarlo antes del 19 de Enero de 2008, fecha en que daremos por finalizado el proceso. <b>Además, los estudiantes que
  no lo hayan realizado, no podrán tomar ramos para el Primer semestre 2008</b>.<br>
  <br> -->
  A continuación se despliegan los cursos que estás realizando en el periodo
  <?php echo("$SEMESTRE_Encuesta-$ANO_Encuesta"); ?>:<br>
  <br>
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#FFFFFF" class="tabla" align="center">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Profesor</td>
    <td class='tituloTabla'>Asignatura</td>
    <td class='tituloTabla'>Estado de la encuesta</td>
  </tr>
  <?php echo($HTML_cursos_encuestas);?>
</table>
<br>
 
