<?php

if (time() > $FEC_FIN_TOMA_RAMOS && $FEC_INI_TOMA_RAMOS < time()) {
	echo(js("window.location='principal.php?modulo=portada';"));
	exit;
}

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}


$id_alumno = $_SESSION['id'];
$id_malla_actual = $_SESSION['malla_actual'];

//$SEMESTRE_InsAsig = 2;
//$ANO_InsAsig = 2020;

if ($_SESSION['moroso_financiero'] == "t") {
	echo(msje_js("En este momento no puedes realizar tu Toma de Ramos.\\n"
	            ."Para resolver esta situación, debes realizar una solicitud Excepción Financiera.\\n"
			    ."A continuación te redirigiremos al nuevo mecanimo de Solicitudes para que lleves a cabo el proceso"));
	echo(js("window.location='/sgu/Solicitudes/index.php?id_alumno=$id_alumno';"));
	exit;
}
$SQL_comp_matric = "SELECT id FROM matriculas WHERE id_alumno=$id_alumno AND ano=$ANO_InsAsig AND semestre=$SEMESTRE_InsAsig";
$comp_matric = consulta_sql($SQL_comp_matric);
if (count($comp_matric) == 0) {
	$ano_ant = $sem_ant = 0;
	if ($SEMESTRE_InsAsig == 1) {
		$ano_ant = $ANO_InsAsig -1;
		$sem_ant = 2;
	} else {
		$ano_ant = $ANO_InsAsig;
		$sem_ant = 1;
	}	
	$SQL_contrato_ant = "SELECT coalesce(mat_efectivo,mat_cheque,mat_tarj_cred) AS monto_matricula 
	                     FROM finanzas.contratos 
						 WHERE (id_alumno=$id_alumno OR id_pap={$_SESSION['id_pap']}) AND ano=$ano_ant AND semestre=$sem_ant
						   AND estado='E'";
	$contrato_ant = consulta_sql($SQL_contrato_ant);
	$monto_matricula = number_format($contrato_ant[0]['monto_matricual'],0,',','.');

	$periodo_mat = "$SEMESTRE_InsAsig-$ANO_InsAsig";

	$msje = "ERROR: Nuestros registros indican que no has completado tu proceso de Matrícula para el periodo $periodo_mat.\\n"
	      . "Por favor acercate a las oficinas de Contabilidad, en el 9º piso del efidicio para que "
	      . "realices este vital trámite.\\n"
	      . "No es posible que lleves a cabo la Inscripción de Asignaturas para el periodo actual "
	      . "($SEMESTRE_InsAsig-$ANO_InsAsig).\\n\\n"
		  . "Para matricularte debes estar al día con tus compromisos financieros. "
//		  . "Debes transferir el monto de $monto_matricula a la cuenta corriente 6763626881 "
//		  . "del Banco de Chile (el RUT de la UMC es el 73.124.400-6) y en el asunto de "
//		  . "la transferencia debe indicar RUT del estudiante y PAGO MATRICULA, por ejemplo "
//		  . "«16952475-5 PAGO MATRICULA».\\n\\n"
//		  . "A contar del tercer día debe contactarse al telefono 9 8288 4236 o al 9 5745 5034 "
//		  . "para realizar un contrato en línea, en horario de atención telefónica de "
//		  . "lunes a viernes de 10.30 a 14.00 horas y de 15.30 a 18.00 horas.\\n\\n"
//		  . "Una vez realizada la matrícula en línea, se le informará el día que debe "
//		  . "acercarse al edificio a firmar el contrato."
          . "";

	echo(msje_js($msje));
	echo(js("window.location='principal.php?modulo=portada';"));
	exit;
}



$SQL_pa_cursos_ins = "SELECT c.id_prog_asig FROM inscripciones_cursos AS ic LEFT JOIN cursos AS c ON c.id=ic.id_curso WHERE id_alumno=$id_alumno";

$SQL_pa_reprob = "SELECT c.id_prog_asig
                  FROM cargas_academicas AS ca
                  LEFT JOIN cursos AS c ON c.id=ca.id_curso 
                  WHERE id_alumno=$id_alumno AND id_estado=2
                  GROUP BY c.id_prog_asig HAVING count(c.id_prog_asig)>=2";
                 
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
                     WHERE true AND ano=$ANO_InsAsig AND semestre=$SEMESTRE_InsAsig
                       AND carrera='Electivos de Formación General'";

$SQL_pa_cursables = "(SELECT id_prog_asig
                      FROM ($SQL_pa_rest_conprereq) AS pa_con_prereq
                      WHERE id_prog_asig NOT IN ($SQL_pa_incursables)
                     ) UNION (
                      $SQL_pa_rest_sinprereq
                     ) UNION (
                      $SQL_pa_electivos
                     )";

$cond_jornada = "";
if ($_SESSION['jornada'] == "D") {
	$cond_jornada = "AND vc.seccion BETWEEN 1 AND 4";
}

$SQL_cursos_propuestos = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,
                                 vc.profesor,vc.semestre||'-'||vc.ano AS periodo,c.tipo_clase,
                                 coalesce(vc.sesion1,'')||coalesce(vc.sesion2,'')||coalesce(vc.sesion3,'') AS horario
                          FROM vista_cursos AS vc
						  LEFT JOIN cursos AS c USING(id)
                          WHERE vc.ano=$ANO_InsAsig AND vc.semestre=$SEMESTRE_InsAsig AND vc.id_prog_asig IN ($SQL_pa_cursables)
                            AND vc.seccion<9
                            AND vc.id_prog_asig NOT IN ($SQL_pa_cursos_ins) AND vc.id_prog_asig NOT IN ($SQL_pa_reprob) $cond_jornada
                          ORDER BY vc.seccion,vc.cod_asignatura;";
$cursos_propuestos = consulta_sql($SQL_cursos_propuestos);


if ($_REQUEST['toma_ramos'] == "Terminar Inscripción y Obtener Comprobante") {
	$SQLupdate_ci = "UPDATE inscripciones_cursos SET cerrada = true WHERE id_alumno=$id_alumno;";
	$cursos_inscritos = consulta_dml($SQLupdate_ci);

	$tipo_asistencia_curso = $_REQUEST['tipo_asistencia_curso'];

	$SQL_upd_insc_cursos = $SQL_upd_carga_academica = "";
	foreach ($tipo_asistencia_curso  as $id_curso_ins => $asist) {
		$SQL_upd_insc_cursos .= "UPDATE inscripciones_cursos SET asistencia='$asist' WHERE id_alumno=$id_alumno AND id_curso=$id_curso_ins;";
		$SQL_upd_carga_academica .= "UPDATE cargas_academicas SET asistencia='$asist' WHERE id_alumno=$id_alumno AND id_curso=$id_curso_ins;";
	}
	consulta_dml($SQL_upd_insc_cursos);

	$SQL_agregar_cursos_ins = "INSERT INTO cargas_academicas (id_curso,id_alumno,asistencia,valida,id_estado)
                               SELECT ic.id_curso,ic.id_alumno,ic.asistencia,ic.condicional,CASE WHEN ic.condicional THEN 22 ELSE NULL END
							   FROM inscripciones_cursos AS ic
							   LEFT JOIN cargas_academicas AS ca ON (ca.id_curso=ic.id_curso AND ca.id_alumno=ic.id_alumno)
							   WHERE ic.id_alumno=$id_alumno AND cerrada AND ca.id_alumno IS NULL AND ca.id_curso IS NULL;";
	consulta_dml($SQL_agregar_cursos_ins);
	
	consulta_dml($SQL_upd_carga_academica);

	echo(msje_js("Se ha guardado e informado la Inscripción de Asignaturas"));
	echo(js("window.location='comprobante_inscripcion_asignaturas.php';"));
}

if ($_REQUEST['agregar'] == 'si' && $_REQUEST['id_curso'] <> "") {
	$id_curso = $_REQUEST['id_curso'];
	
	//comprobar topes de horario	
	$SQL_cursos_ins_alu = "SELECT ic.id_curso FROM inscripciones_cursos AS ic WHERE id_alumno=$id_alumno";
	
	$tope = false;
	for($ses1=1;$ses1<=3;$ses1++) {
		for($ses2=1;$ses2<=3;$ses2++) {
			$SQL_comp_tope_hor = "SELECT dia$ses1,horario$ses1 FROM cursos WHERE id iN ($SQL_cursos_ins_alu) AND dia$ses1 IS NOT NULL AND horario$ses1 IS NOT NULL
			                      INTERSECT
			                      SELECT dia$ses2,horario$ses2 FROM cursos WHERE id=$id_curso AND dia$ses2 IS NOT NULL AND horario$ses2 IS NOT NULL;";
			//echo($SQL_comp_tope_hor."<br>");
			if (count(consulta_sql($SQL_comp_tope_hor)) > 0) { $tope = true; /*echo("esta es la que me da verdadero $SQL_comp_tope_hor<br>");*/ }
		}
	}
	if ($tope) {
		echo(msje_js("No puedes tomar esta asignatura debido a que se intersecta el horario de uno o todos los módulos"));
	} else {
		$SQL_disp_curso = "SELECT coalesce(cupo,0) as cupo,cant_alumnos_asist(id) AS ai FROM cursos WHERE id=$id_curso;";
		$disp_curso = consulta_sql($SQL_disp_curso);
		if ($disp_curso[0]['ai']+1 > $disp_curso[0]['cupo']) {
			$cupo = $disp_curso[0]['cupo'];
			echo(msje_js("ERROR: El curso que intentas inscribir no tiene cupo disponible.\\n\\n"
			            ."No se puede inscribir este curso."));
		} else {

			$luzverde = false;
			$SQL_pa_curso = "SELECT id_prog_asig FROM cursos WHERE id=$id_curso";
			$id_pa = consulta_sql($SQL_pa_curso);
			$id_pa = $id_pa[0]['id_prog_asig'];
			$msje_prereq = "";
			$prob_prereq = false;
			
			while (!$luzverde) {
				$SQL_id_dm = "SELECT id FROM detalle_mallas WHERE id_prog_asig=$id_pa AND id_malla=$id_malla_actual";
				$SQL_prereq = "SELECT id_dm_req FROM requisitos_malla WHERE id_dm=($SQL_id_dm)";
				$SQL_pa_req = "SELECT id_prog_asig FROM detalle_mallas WHERE id IN ($SQL_prereq) AND id_malla=$id_malla_actual";
			
				$SQL_pa_aprob = "SELECT CASE id_estado WHEN 1 THEN id_prog_asig WHEN 3 THEN id_pa_homo ELSE id_pa END AS id_pa FROM vista_alumnos_cursos WHERE id_alumno=$id_alumno AND id_estado IN (1,3,4,5)";
			
				$SQL_pa_req_aprob = "SELECT id_prog_asig FROM ($SQL_pa_req) AS pa_req LEFT JOIN ($SQL_pa_aprob) AS pa_aprob ON pa_aprob.id_pa=pa_req.id_prog_asig WHERE id_pa IS NOT NULL";

			
				$pa_req = consulta_sql($SQL_pa_req);
				$pa_req_aprob = consulta_sql($SQL_pa_req_aprob);
				
				if (count($pa_req) == 0) { 
					$luzverde = true; 
				} else {
					if (count($pa_req) <> count($pa_req_aprob)) { 
						$msje_prereq = "ERROR: La asignatura que intentas inscribir tiene prerequisito(s) previos no aprobados. No puedes inscribir este curso.";
						$prob_prereq = true;
						echo("<!-- $SQL_pa_req -->\n");
						echo("<!-- $SQL_pa_req_aprob -->\n");
					}
					$id_pa = $pa_req[0]['id_prog_asig'];
				}
			}
			
			if ($prob_prereq) {
				echo(msje_js($msje_prereq));
			} else {
				$SQLinsert_inscripciones_cursos = "INSERT INTO inscripciones_cursos (id_curso,id_alumno) VALUES ($id_curso,$id_alumno);";
				consulta_dml($SQLinsert_inscripciones_cursos);
			}			
		}
	}
}

if ($_REQUEST['eliminar'] == 'si' && $_REQUEST['id_curso'] <> "") {
	$id_curso = $_REQUEST['id_curso'];
	$SQLdelete_cursos_inscritos = "DELETE FROM inscripciones_cursos WHERE id_alumno=$id_alumno AND id_curso=$id_curso AND NOT cerrada;";
	consulta_dml($SQLdelete_cursos_inscritos);
}

$SQL_cursos_ins_alu = "SELECT c.id_prog_asig,ic.id_curso 
                       FROM inscripciones_cursos AS ic
                       LEFT JOIN cursos AS c ON c.id=ic.id_curso
                       WHERE id_alumno=$id_alumno";

$SQL_cursos_inscritos = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,vc.profesor,
                                vc.semestre||'-'||vc.ano AS periodo,
                                coalesce(vc.sesion1,'')||coalesce(vc.sesion2,'')||coalesce(vc.sesion3,'') AS horario,
                                to_char(ic.fecha,'DD/MM/YYYY HH24:MI') AS fecha_ins,
                                CASE ic.alza_prereq WHEN true THEN 'Si' ELSE 'No' END AS alza_prereq,
                                CASE ic.cerrada WHEN true THEN 'Si' ELSE 'No' END AS informada,
								ic.asistencia,c.tipo_clase
                         FROM inscripciones_cursos AS ic
                         LEFT JOIN vista_cursos AS vc ON vc.id=ic.id_curso
						 LEFT JOIN cursos AS c ON c.id=ic.id_curso
                         WHERE id_alumno=$id_alumno";
$cursos_inscritos = consulta_sql($SQL_cursos_inscritos);

$SQL_cursos_ins_alu = "SELECT ic.id_curso FROM inscripciones_cursos AS ic WHERE id_alumno=$id_alumno";
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

//	$horario = chr($mod_horario);
			
	$HTML_mis_horarios .= "<tr class='textoTabla'><td class='filatituloTabla' valign='middle' align='center'><b>$horario</b></td>";
	
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
			$HTML_mis_horarios .= "<td class='celdaramomalla' width='120'>&nbsp;</td>";
		} else {
			$HTML_mis_horarios .= "<td class='celdaramomalla' width='120' align='center' valign='middle'>$celda_horario</td>";
		}
		
	}
		
	$HTML_mis_horarios .= "</tr>\n";

}

$TIPO_ASISTENCIA = consulta_sql("SELECT * FROM vista_tipo_asistencia");

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  Toma de Ramos
</div>
<br>
<form name="formulario" method="post" action="principal.php" onSubmit="return confirm('¿Estás seguro de guardar esta Inscripción de Asignaturas?\nRecuerda que una vez guardada e infomada la Inscripción no podrás eliminar asignaturas de tu Toma de Ramos');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<div class="texto">
  El SGU, en base a las asignaturas que tienes aprobadas, ha procesado y calculado estimando que del listado siguiente, debes inscribir algunos o todos los cursos
  en tu carga académica semestral (si aún no conoces la nueva nomenclatura de los horarios, mira la <a href='principal.php?modulo=tabla_horarios'>Tabla de Horarios</a>).
  Si falta alguna asignatura, es quizás por que aún no tiene un horario definido, por lo que si tienes dudas, comunicate con tu escuela:
  <br><br>
  <table bgcolor="#ffffff" cellspacing="1" cellpadding="3" class="tabla" align="center">
    <tr class='filaTituloTabla'>
      <td class='tituloTabla' colspan="6">Cursos propuestos</td>
    </tr>
    <tr class='filaTituloTabla'>
      <td class='tituloTabla'>Ins.</td>
      <td class='tituloTabla'>ID</td>
      <td class='tituloTabla'>Asignatura</td>
      <td class='tituloTabla'>Periodo</td>
      <td class='tituloTabla'>Profesor cátedra</td>
      <td class='tituloTabla'>Horario {sala}</td>
    </tr>
<?php
	if (count($cursos_propuestos) > 0) {

		for ($x=0; $x<count($cursos_propuestos); $x++) {
			extract($cursos_propuestos[$x]);

			$modalidad = "";
			if ($tipo_clase <> "") { $modalidad = "modadlidad $tipo_clase"; }

			echo("  <tr class='filaTabla'>\n");
			echo("    <td class='textoTabla'><a href='principal.php?modulo=$modulo&agregar=si&id_curso=$id' class='boton'>Agregar</a></td>");
			echo("    <td class='textoTabla'>$id</td>");
			echo("    <td class='textoTabla'><label for='id_curso_$id'>$asignatura</label></td>");
			echo("    <td class='textoTabla'>$periodo</td>");
			echo("    <td class='textoTabla'>$profesor</td>");
			echo("    <td class='textoTabla'>$horario<br>$tipo_clase</td>");
			echo("  </tr>");
		}

	} else {
		echo("<td class='textoTabla' colspan='6' align='center'>"
                    ."  No hay registros para los criterios de selección.<br>"
                    ."  Esto puede ser debido a que las notas finales aún no se han calculado en tu carrera"
                    ."</td>\n");
	}
?>
  </table>
  <br>
  Para inscribir tus cursos o asignaturas, debes pinchar en el botón <span class="boton">Agregar</span> correspondiente a cada asignatura. Luego la asignatura escogida
  desaparecerá del listado "Cursos propuestos" y aparecerá en el listado "Cursos Inscritos". Si te has equivocado en la elección, debes pinchar en el listado de abajo,
  en el boton <span class="boton">Eliminar</span>.<br>
  <br>
  <table bgcolor="#ffffff" cellspacing="1" cellpadding="3" class="tabla" align="center">
    <tr class='filaTituloTabla'>
      <td class='tituloTabla' colspan="9">Cursos inscritos</td>
    </tr>
    <tr class='filaTituloTabla'>
      <td class='tituloTabla'>&nbsp;</td>
      <td class='tituloTabla'>ID</td>
      <td class='tituloTabla'>Asignatura</td>
      <td class='tituloTabla'>Periodo</td>
      <td class='tituloTabla'>Profesor cátedra</td>
      <td class='tituloTabla'>Horario {sala}</td>
      <td class='tituloTabla'>Fecha Inscripción</td>
      <td class='tituloTabla'>Alza. Pre-req</td>
      <td class='tituloTabla'>Informada</td>
    </tr>
<?php
	if (count($cursos_inscritos) > 0) {

		$hibridas = false;

		for ($x=0; $x<count($cursos_inscritos); $x++) {
			extract($cursos_inscritos[$x]);

			$modalidad = "";
			if ($tipo_clase <> "") { $modalidad = "modadlidad $tipo_clase"; }

			$tipo_asistencia = $hibrida = "";
			if ($tipo_clase == "Híbrida") {
				$hibrida = "*";
				$hibridas = true;
				$readonly = "";
				if ($asistencia == "Presencial") { $readonly = "readonly"; }
				$tipo_asistencia = "<select name='tipo_asistencia_curso[$id]' class='filtro' $readonly required>"
								 . "  <option value=''>-- ¿Cómo asistirás? --</option>"
								 . select($TIPO_ASISTENCIA,$asistencia)
								 . "</select>";
			}

			echo("  <tr class='filaTabla'>\n");
			echo("    <td class='textoTabla'><a href='principal.php?modulo=$modulo&id_curso=$id&eliminar=si' class='boton'>Eliminar</a></td>");
			echo("    <td class='textoTabla'>$id</td>");
			echo("    <td class='textoTabla'><label for='id_curso_$id'>$asignatura</label> $hibrida<br>$tipo_asistencia</td>");
			echo("    <td class='textoTabla'>$periodo</td>");
			echo("    <td class='textoTabla'>$profesor</td>");
			echo("    <td class='textoTabla'>$horario<br>$modalidad</td>");
			echo("    <td class='textoTabla'>$fecha_ins</td>");
			echo("    <td class='textoTabla' align='center'>$alza_prereq</td>");
			echo("    <td class='textoTabla' align='center'>$informada</td>");
			echo("  </tr>");
		}
	} else {
		echo("<td class='textoTabla' colspan='9' align='center'>"
          ."  *** Aún no tienes cursos inscritos ***"
          ."</td>\n");
	}
?>
  </table><br>
  Cuando hayas terminado de agregar las asignaturas o cursos que deseas inscribir, para guardar e informar tu inscripción debes pinchar en el boton "Terminar
  Inscripción y Obtener Comprobante". 
  <br><br>
  NOTA: Una vez que terminas la inscripción, las asignaturas que tomas ya no las podrás borrar.
  <br><br>
<?php if ($hibridas) { ?>
  <b>* Esta asignatura se impartirá en modalidad Híbrida. Es necesario que nos indiques la forma en que asistirás, vale decir, en forma "Presencial" o "a Distancia".<br>
  <br>
  NOTA: Los cursos que se dictan en modalidad Híbrida tendrán un aforo máximo en la sala de clases, en concordancia con la normativa dictada por la autoridad Sanitaria y de Educación.</b>
  <br><br>
<?php } ?>
  <center><input type="submit" name="toma_ramos" value="Terminar Inscripción y Obtener Comprobante"></center>
  <br>
  <table cellpadding="2" cellspacing="1" border="0" class="tabla" bgcolor="#ffffff" align="center">
    <tr class='filaTituloTabla'>
      <td class='tituloTabla' colspan="7">Horario</td>
    </tr>
    <tr class='filaTituloTabla'>
      <td class='tituloTabla'><small>Módulo</small></td>
      <td class='tituloTabla'>Lunes</td>
      <td class='tituloTabla'>Martes</td>
      <td class='tituloTabla'>Miércoles</td>
      <td class='tituloTabla'>Jueves</td>
      <td class='tituloTabla'>Viernes</td>
      <td class='tituloTabla'>Sábado</td>
    </tr>
    <?php echo($HTML_mis_horarios); ?>
  </table>
</div>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

<?php

//$SQL_comprobacion = "SELECT ins_alu.id_curso
//                     FROM ($SQL_cursos_ins_alu) AS ins_alu
//                     LEFT JOIN ($SQL_pa_cursables) AS pa_cursables ON pa_cursables.id_prog_asig=ins_alu.id_prog_asig
//                     WHERE pa_cursables.id_prog_asig IS NULL";
//$comprobacion_prereq = consulta_sql($SQL_comprobacion);
//if (count($comprobacion_prereq) > 0 ) {
//	echo(msje_js("Tu inscripción de asignatura tiene problemas.\\n"
//                    ."Para corregirla, SGU eliminará asignaturas sin los prerequisitos cumplidos"));
//	$SQLdelete = "DELETE FROM inscripciones_cursos WHERE id_alumno=$id_alumno AND id_curso IN ($SQL_comprobacion);";
//	consulta_dml($SQLdelete);
//	$SQLdelete = "";
//}

?>
