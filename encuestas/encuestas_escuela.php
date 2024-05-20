<?php

if ($_REQUEST['id_evaluador'] == "") { echo(js("window.location='http://www.umcervantes.cl';")); }

$id_evaluador = $_REQUEST['id_evaluador'];
 
$ANO          = $_REQUEST['ano'];
$SEMESTRE     = $_REQUEST['semestre'];

//$ANO      = 2010;
//$SEMESTRE = 2;

$SQL_evaluador = "SELECT initcap(nombre||' '||apellido) AS nombre,id_escuela
                  FROM usuarios
                  WHERE id='$id_evaluador' AND tipo IN (1,2);";
$evaluador = consulta_sql($SQL_evaluador);
if (count($evaluador) > 0) {
	$id_escuela=$evaluador[0]['id_escuela'];
	
	$SQL_cursos_actuales = "SELECT id FROM cursos WHERE ano=$ANO AND semestre=$SEMESTRE";
	
	$SQL_evaluador_profesores = "SELECT DISTINCT ON (vc.profesor) vc.id_profesor,vc.profesor,
	                                CASE WHEN eed.id IS NULL THEN false ELSE true END AS contestada
	                         FROM vista_cursos AS vc
	                         LEFT JOIN carreras AS c ON c.id=vc.id_carrera
	                         LEFT JOIN encuestas.evaluacion_docente AS eed
	                                ON (eed.id_profesor=vc.id_profesor AND eed.id_evaluador='$id_evaluador')
	                         WHERE c.id_escuela='$id_escuela' AND vc.ano=$ANO AND vc.semestre=$SEMESTRE
	                         ORDER BY vc.profesor;";
	$evaluador_profesores_encuestas = consulta_sql($SQL_evaluador_profesores);
	if (count($evaluador_profesores_encuestas) == 0) {
		echo(msje_js("ERROR: Usted no tiene profesores para evaluar en este periodo ($SEMESTRE-$ANO)."));
		echo(js("window.location='". $_SERVER['HTTP_REFERER'] . "';"));
		exit;
	}
	
	$HTML_evaluador_encuestas = "";
	for ($x=0; $x<count($evaluador_profesores_encuestas); $x++) {
		extract($evaluador_profesores_encuestas[$x]);
		$contestar = "<font color='#009900'>Contestada</font>";

		$enl = "?modulo=encuestas&id_evaluador=$id_evaluador&id_profesor=$id_profesor&arch_encuesta=evaluacion_docente&ano=$ANO&semestre=$SEMESTRE";
		$enlace = "<a class='enlitem' href='$enl'>";
		$js_onClick = "onClick=\"window.location='$enl';\"";

		if ($contestada == "f") {
			$contestar = "<a href='$enl'>CONTESTAR</a>";
		} else {
			# permitia editar
			#$contestar = "<font color='#009900'>Contestada</font> <a href='$enl'>[Editar]</a>";
			$contestar = "<font color='#009900'>Contestada</font> ";
		}
	
		$HTML_evaluador_encuestas .= "<tr class='filaTabla' $js_onClick>\n"
		                          .  "  <td class='textoTabla'> $profesor</td>\n"
		                          .  "  <td class='textoTabla' align='center'> $contestar</td>\n"
		                          .  "</tr>\n";
	}
} else {
	echo(msje_js("ERROR: Usted no es un evaluador válido."));
	echo(js("window.location='". $_SERVER['HTTP_REFERER'] . "';"));
	exit;
}

?>

<div align="center" class="tituloModulo">
  Encuesta de Evaluación Docente (Directores o Coordinadores)
</div>
<br>
<div class="texto" align="justify">
  Estimado(a) <?php echo($evaluador[0]['nombre']); ?>:<br>
  <br>
  Tal como se acordara en el Consejo Docente, el proceso de evaluación de los docentes de vuestras
  respectivas escuelas ha comenzado y tiene una duración de un mes. Les recordamos que deben contestar
  un cuestionario por profesor y éste debe ser contestado por el Director o por el Coordinador, según
  delegación de éste –en ningún caso deben contestar ambos el cuestionario de un mismo profesor.<br>
  <br>
  Todas las preguntas deben ser contestadas a excepción <!-- de aquellas marcadas como "no observadas" y -->
  del cuadro de observación, que es optativo.<br>
  <br>
  Al terminar de contestar las preguntas del cuestionario, debe pinchar el botón "Terminar Encuesta",
  a continuación el sistema guardará los datos y mostrará un mensaje de éxito en la aplicación de éste.<br>
  <br>
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#FFFFFF" class="tabla" align="center">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Profesor</td>
    <td class='tituloTabla'>Estado de la encuesta</td>
  </tr>
  <?php echo($HTML_evaluador_encuestas);?>
</table>
<br>
