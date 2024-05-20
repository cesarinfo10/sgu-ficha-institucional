<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

$id_alumno = $_REQUEST['id_alumno'];

$abierto_escuelas = true;

/*
if ($_SESSION['tipo'] > 0) {
	echo(msje_js("Módulo cerrado por término de proceso. Debe realizar trámite de inscripción fuera de plazo"));
	$abierto_escuelas = false;
}
*/

include("validar_modulo.php");
include("validar_modulo_uid_no_cero.php");



if ($_REQUEST['id_alumno'] == "") {
	header("Location: principal.php?modulo=gestion_alumnos");
	exit;
}

$id_malla       = $_REQUEST['id_malla'];
$jornada_cursos = $_REQUEST['jornada'];

$SQL_alumno = "SELECT va.id,va.rut,va.nombre,va.estado,va.malla_actual,va.cohorte,va.carrera,va.id_malla_actual,va.id_carrera,a.jornada,a.moroso_financiero
               FROM vista_alumnos AS va 
               LEFT JOIN alumnos AS a USING (id)
               WHERE va.id=$id_alumno;";
$alumno = consulta_sql($SQL_alumno);

if (count($alumno) == 0) {
	header("Location: principal.php?modulo=gestion_alumnos");
	exit;
}

$SQL_comp_matric = "SELECT id FROM matriculas WHERE id_alumno=$id_alumno AND ano=$ANO_InsAsig AND semestre=$SEMESTRE_InsAsig";
$comp_matric = consulta_sql($SQL_comp_matric);
if (count($comp_matric) == 0) {
	$msje = "Estudiante no matriculado, no puede inscribir asignaturas";
	echo(msje_js($msje));
	echo(js("window.location='principal.php?modulo=portada';"));
}

//$toma_condicional = 'f';

//chequeo de vigencia...
//  && $alumno[0]['estado'] <> "Condicional"
if (($alumno[0]['estado'] <> "Vigente" || $alumno[0]['moroso_financiero'] == "t") && $alumno[0]['estado'] <> "Condicional") {
	$estado = $alumno[0]['estado'];
	if ($alumno[0]['moroso_financiero'] == "t") { $estado .= " (M)"; }
	echo(msje_js("Actualmente este alumno no puede realizar inscripción de asignaturas, debido a que su estado es: $estado."));
	//echo(js("window.location='$enlbase=toma_ramos';"));
}


//if ($alumno[0]['estado'] == "Moroso") {
//	echo(msje_js("Actualmente este alumno se encuentra Moroso. Se procede de todas formas con una inscripción condicionada"));
//	$toma_condicional = 't';
//}

//chequeo de documentacion adeudada...
$indocumentados = consulta_sql("SELECT doc_adeudado FROM alumnos_indocumentados WHERE id_alumno=$id_alumno;");
if (count($indocumentados) > 0) {

	$doc_adeudado = $indocumentados[0]['doc_adeudado'];
	echo(msje_js("Actualmente este alumno no puede realizar inscripción de asignaturas, debido a que faltan documentos en su carpeta. Estos son: $doc_adeudado"));
	echo(js("window.location='$enlbase=toma_ramos';"));

//	echo(msje_js("Actualmente este alumno adeuda documentación ($doc_adeudado). Se procede de todas formas con una inscripción condicionada."));
//	$toma_condicional = 't';
}

extract($alumno[0]);

if (empty($jornada_cursos)) { $jornada_cursos = $jornada; }
if (empty($id_malla))      { $id_malla = $id_malla_actual; }

$id = "<a class='enlaces' href='$enlbase=ver_alumno&id_alumno=$id' title='Ver ficha'>$id</a>";
$malla = "<a class='enlaces' href='$enlbase=ver_malla&id_malla=$id_malla_actual'>$malla_actual</a>";

$aAlumno = array("ID"                => $id,
                 "Nombre"            => $nombre,
                 "RUT"               => $rut,
                 "Carrera/Año malla" => trim($carrera)."-".$jornada."/ ".$malla,
                 "Cohorte"           => $cohorte);

$HTML_enc_alumno = "<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla'>"
                 . tabla_encabezado($aAlumno)
                 . "</table>";

//$SQL_pa_cursos_ins = "SELECT c.id_prog_asig FROM inscripciones_cursos AS ic LEFT JOIN cursos AS c ON c.id=ic.id_curso WHERE id_alumno=$id_alumno";
$SQL_pa_cursos_ins = "SELECT id_curso FROM inscripciones_cursos WHERE id_alumno=$id_alumno";

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
                     WHERE ano=$ANO_InsAsig AND semestre=$SEMESTRE_InsAsig
                       AND carrera='Electivos de Formación General'";

$SQL_pa_cursables = "(SELECT id_prog_asig
                      FROM ($SQL_pa_rest_conprereq) AS pa_con_prereq
                      WHERE id_prog_asig NOT IN ($SQL_pa_incursables)
                     ) UNION (
                      $SQL_pa_rest_sinprereq
                     ) UNION (
                      $SQL_pa_electivos
                     )";
//echo($SQL_pa_cursables);

$cond_jornada = " true ";

if ($jornada_cursos == "D") { $cond_jornada = " vc.seccion BETWEEN 1 and 4 "; }
elseif ($jornada_cursos == "V") { $cond_jornada = " vc.seccion BETWEEN 5 and 9 "; }
elseif ($jornada_cursos == "O") { $cond_jornada = " vc.seccion BETWEEN 10 and 99 "; }

if ($alumno[0]['jornada'] == "D" && $_SESSION['tipo'] > 0) {
	$cond_jornada = " vc.seccion BETWEEN 1 and 4 ";
}

$SQL_cursos_propuestos = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,vc.profesor,
                                 vc.semestre||'-'||vc.ano AS periodo,
                                 coalesce(vc.sesion1,'')||coalesce(vc.sesion2,'')||coalesce(vc.sesion3,'') AS horario,
                                 CASE WHEN tomados.id_curso IS NOT NULL THEN 'Si' ELSE 'No' END AS inscrita
                          FROM vista_cursos AS vc
                          LEFT JOIN ($SQL_pa_cursos_ins) AS tomados ON tomados.id_curso=vc.id
                          WHERE vc.ano=$ANO_InsAsig AND vc.semestre=$SEMESTRE_InsAsig                            
                            AND vc.id_prog_asig IN ($SQL_pa_cursables)
                            AND $cond_jornada
                          ORDER BY cod_asignatura;";
//echo($SQL_cursos_propuestos);

/*$SQL_cursos_ins_alu = "SELECT c.id_prog_asig,ic.id_curso 
                       FROM inscripciones_cursos AS ic
                       LEFT JOIN cursos AS c ON c.id=ic.id_curso
                       WHERE id_alumno=$id_alumno AND NOT alza_prereq";
$SQL_comprobacion = "SELECT ins_alu.id_curso
                     FROM ($SQL_cursos_ins_alu) AS ins_alu
                     LEFT JOIN ($SQL_pa_cursables) AS pa_cursables ON pa_cursables.id_prog_asig=ins_alu.id_prog_asig
                     WHERE pa_cursables.id_prog_asig IS NULL";
$comprobacion_prereq = consulta_sql($SQL_comprobacion);
if (count($comprobacion_prereq) > 0 ) {
	echo(msje_js("Se ha detectado incoherencias en la Inscripcion de Cursos de este alumno(a).\\n"
               ."Para corregirla, SGU eliminará asignaturas sin los prerequisitos cumplidos"));
	$SQLdelete = "DELETE FROM inscripciones_cursos WHERE id_alumno=$id_alumno AND id_curso IN ($SQL_comprobacion);";
	consulta_dml($SQLdelete);
	$SQLdelete = "";
}*/

if ($_REQUEST['guardar'] == "Guardar") {
	$SQL_cerrar_inscripcion = "UPDATE inscripciones_cursos SET cerrada='t' WHERE id_alumno=$id_alumno";
	consulta_dml($SQL_cerrar_inscripcion);

	$tipo_asistencia_curso = $_REQUEST['tipo_asistencia_curso'];

	$SQL_upd_insc_cursos = $SQL_upd_carga_academica = "";
	foreach ($tipo_asistencia_curso  as $id_curso_ins => $asist) {
		$SQL_upd_insc_cursos .= "UPDATE inscripciones_cursos SET asistencia='$asist' WHERE id_alumno=$id_alumno AND id_curso=$id_curso_ins;";
		$SQL_upd_carga_academica .= "UPDATE cargas_academicas SET asistencia='$asist' WHERE id_alumno=$id_alumno AND id_curso=$id_curso_ins;";
	}
	consulta_dml($SQL_upd_insc_cursos);
	consulta_dml($SQL_upd_carga_academica);

	$SQL_cursos_semestre = "SELECT id FROM cursos WHERE semestre=$SEMESTRE_InsAsig AND ano=$ANO_InsAsig";

	$SQL_comp_existencia_cursos = "SELECT ca.id
	                               FROM cargas_academicas AS ca
                                  LEFT JOIN inscripciones_cursos AS ci ON (ci.id_curso=ca.id_curso AND ci.id_alumno=ca.id_alumno)
                                  WHERE ca.id_alumno=$id_alumno AND ca.id_curso IN ($SQL_cursos_semestre) AND ci.id_curso IS NULL";

	$SQL_susp_cursos = "UPDATE cargas_academicas SET id_estado=6 WHERE id IN ($SQL_comp_existencia_cursos);";
	consulta_dml($SQL_susp_cursos);

	$SQL_agregar_cursos_ins = "INSERT INTO cargas_academicas (id_curso,id_alumno,valida,id_estado)
                                        SELECT ic.id_curso,ic.id_alumno,ic.condicional,CASE WHEN ic.condicional THEN 22 ELSE NULL END
                                        FROM inscripciones_cursos AS ic
                                        LEFT JOIN cargas_academicas AS ca ON (ca.id_curso=ic.id_curso AND ca.id_alumno=ic.id_alumno)
                                        WHERE ic.id_alumno=$id_alumno AND cerrada AND ca.id_alumno IS NULL AND ca.id_curso IS NULL;";
	consulta_dml($SQL_agregar_cursos_ins);
	
	$SQL_act_est_cursos_ins = "UPDATE cargas_academicas
	                           SET id_estado=null
	                           WHERE id_alumno=$id_alumno 
	                             AND id_curso IN (SELECT id_curso FROM inscripciones_cursos WHERE id_alumno=$id_alumno)
	                             AND id_estado NOT IN (1,2,22)";	                                    
	consulta_dml($SQL_act_est_cursos_ins);
	
	echo(msje_js("Se ha guardado la Inscripción de Asignaturas"));
	echo(js("window.open('comprobante_inscripcion_asignaturas.php?id_alumno=$id_alumno');"));
}

if ($_REQUEST['agregar'] == 'si' && $_REQUEST['id_curso'] <> "") {
	$id_curso = $_REQUEST['id_curso'];
	
	$SQL_id_pa = "SELECT id_prog_asig FROM cursos WHERE id=$id_curso";
	
	$SQL_cumple_req = "SELECT 1 FROM ($SQL_pa_cursables) AS pa_cursables WHERE id_prog_asig=($SQL_id_pa)";
	
	$alza_prereq = "f";
	if (count(consulta_sql($SQL_cumple_req)) == 0) { $alza_prereq = "t"; }
	
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
		echo(msje_js("ATENCIÓN: El horario de este curso se intersecta con el horario de uno o todos los módulos de las asignaturas ya inscritas, la asignatura se inscribe de todas maneras"));
	}
	
	$SQL_disp_curso = "SELECT coalesce(cupo,0) as cupo,cant_alumnos_asist(id) AS ai FROM cursos WHERE id=$id_curso;";
	$disp_curso = consulta_sql($SQL_disp_curso);
	if ($disp_curso[0]['ai']+1 > $disp_curso[0]['cupo'] && $disp_curso[0]['cupo']<>0) {
		$cupo = $disp_curso[0]['cupo'];
		echo(msje_js("ATENCIÓN: El curso que intenta inscribir tiene un cupo de $cupo estudiantes. No se puede realizar esta inscripción"));
	} else {
		$prob = true;

		if ($alza_prereq == "f") { $prob = false; }

		if ($alza_prereq == "t" && $_SESSION['tipo'] == 0) {
			echo(msje_js("ATENCIÓN: Esta asignatura no tiene cumplidos los pre-requisitos. "
			            ."La inscripción se realiza de todas formas debido a que es usuario de Registro Académico"));
			$prob = false;
		}
		
		if ($alza_prereq == "t" && $_SESSION['tipo'] > 0) {
			echo(msje_js("ATENCIÓN: Esta asignatura no tiene cumplidos los pre-requisitos. No se puede inscribir el curso"));
		}
		
		if (!$prob) {
			$SQLinsert_inscripciones_cursos = "INSERT INTO inscripciones_cursos (id_curso,id_alumno,alza_prereq,cerrada)
												VALUES ($id_curso,$id_alumno,'$alza_prereq',true);";
			consulta_dml($SQLinsert_inscripciones_cursos);
		}
	}
}

if ($_REQUEST['eliminar'] == 'si' && $_REQUEST['id_curso'] <> "") {
	$id_curso = $_REQUEST['id_curso'];
	$SQLdelete_cursos_inscritos = "DELETE FROM inscripciones_cursos WHERE id_alumno=$id_alumno AND id_curso=$id_curso;";
	consulta_dml($SQLdelete_cursos_inscritos);
}                          
                          
$cursos_propuestos = consulta_sql($SQL_cursos_propuestos);

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
                         WHERE id_alumno=$id_alumno
                         ORDER BY asignatura";
$cursos_inscritos = consulta_sql($SQL_cursos_inscritos);

$id_carrera_alumno = $alumno[0]['id_carrera'];
                                                                     
/*$SQL_cursos_carrera = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,vc.profesor,
                              vc.semestre||'-'||vc.ano AS periodo,
                              coalesce(vc.sesion1,'')||coalesce(vc.sesion2,'')||coalesce(vc.sesion3,'') AS horario
                       FROM vista_cursos AS vc
                       WHERE ano=$ANO_InsAsig AND semestre=$SEMESTRE_InsAsig AND id_prog_asig NOT IN ($SQL_pa_cursos_ins)
                         AND (id_carrera = $id_carrera_alumno
                          OR carrera='Electivos de Formación General')
                       ORDER BY asignatura;";
$cursos_escuela = consulta_sql($SQL_cursos_carrera);*/

$SQL_carreras_escuela = "SELECT id FROM carreras WHERE id_escuela = (SELECT id_escuela FROM carreras
                                                                     WHERE id=$id_carrera_alumno)";

$SQL_cursos_escuela = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,vc.profesor,
                              vc.semestre||'-'||vc.ano AS periodo,
                              coalesce(vc.sesion1,'')||coalesce(vc.sesion2,'')||coalesce(vc.sesion3,'') AS horario
                       FROM vista_cursos AS vc
                       WHERE ano=$ANO_InsAsig AND semestre=$SEMESTRE_InsAsig AND id_prog_asig NOT IN ($SQL_pa_cursos_ins)
                         AND (vc.id_prog_asig IN (SELECT id_prog_asig FROM detalle_mallas WHERE id_malla=$id_malla)
                          OR cod_asignatura ~ 'SELLO')
                         AND $cond_jornada
                       ORDER BY asignatura;";
$cursos_escuela = consulta_sql($SQL_cursos_escuela);

$JORNADAS[] = array('id' => "O", 'nombre' => "Otra");

$mallas = consulta_sql("SELECT id,ano AS nombre FROM mallas WHERE id_carrera=$id_carrera");
$HTML_selec_malla = "<select name='id_malla' disabled>".select($mallas,$id_malla)."</select>";
$HTML_selec_jornada = "<select name='jornada' onChange='submitform()'>".select($JORNADAS,$jornada_cursos)."</select>";

$TIPO_ASISTENCIA = consulta_sql("SELECT * FROM vista_tipo_asistencia");

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div><br>
<form name="formulario" method="post" action="principal.php">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_alumno" value="<?php echo($id_alumno); ?>">
<input type="submit" name="guardar" value="Guardar">
<br>
<br>
<?php echo($HTML_enc_alumno); ?>
<br>
<div class="texto">
  <table bgcolor="#ffffff" cellspacing="1" cellpadding="3" class="tabla">
    <tr class='filaTituloTabla'>
      <td class='tituloTabla' colspan="6">Cursos propuestos</td>
    </tr>
    <tr class='filaTituloTabla'>
      <td class='tituloTabla'>ID</td>
      <td class='tituloTabla'>Ins.</td>
      <td class='tituloTabla'>Asignatura</td>
      <td class='tituloTabla'>Periodo</td>
      <td class='tituloTabla'>Profesor cátedra</td>
      <td class='tituloTabla'>Horario {sala}</td>
    </tr>
<?php
	if (count($cursos_propuestos) > 0) {

		for ($x=0; $x<count($cursos_propuestos); $x++) {
			extract($cursos_propuestos[$x]);
			echo("  <tr class='filaTabla'>\n");
			echo("    <td class='textoTabla'>$id</td>");
			echo("    <td class='textoTabla'align='center'>$inscrita</td>");
			echo("    <td class='textoTabla'>$asignatura</td>");
			echo("    <td class='textoTabla'>$periodo</td>");
			echo("    <td class='textoTabla'>$profesor</td>");
			echo("    <td class='textoTabla'>$horario</td>");
			echo("  </tr>");
		}

	} else {
		echo("<td class='textoTabla' colspan='5' align='center'>"
          ."  No hay registros para los criterios de selección.<br>"
          ."  Esto puede ser debido a que las notas finales aún no se han calculado."
          ."</td>\n");
	}
?>
  </table>
  <br>
<?php if ($_SESSION['tipo'] == 0 || $abierto_escuelas) { ?>  
  <table bgcolor="#ffffff" cellspacing="0" cellpadding="0" class="tabla">
    <tr class='filaTituloTabla'>
      <td class='tituloTabla'>Cursos de la escuela: [Malla: <?php echo($HTML_selec_malla); ?>] [Jornada: <?php echo($HTML_selec_jornada); ?>]</td>
    </tr>
    <tr>
      <td>
        <div style="height: 150px; overflow: auto;">
        <table bgcolor="#ffffff" cellspacing="1" cellpadding="3" class="tabla">
          <tr class='filaTituloTabla'>
            <td class='tituloTabla'>&nbsp;</td>
            <td class='tituloTabla'>ID</td>
            <td class='tituloTabla'>Asignatura</td>
            <td class='tituloTabla'>Periodo</td>
            <td class='tituloTabla'>Profesor cátedra</td>
            <td class='tituloTabla'>Horario {sala}</td>
          </tr>
<?php
	if (count($cursos_escuela) > 0) {


	for ($x=0; $x<count($cursos_escuela); $x++) {
		extract($cursos_escuela[$x]);

    $boton_insc = "<a href='principal.php?modulo=$modulo&id_curso=$id&agregar=si&id_alumno=$id_alumno' class='boton'> + </a>";
    if ($alumno[0]['moroso_financiero'] == "t") { 
      $boton_insc = "<a href='#' class='boton' disabled> + </a>";
    }

    	echo("  <tr class='filaTabla'>\n"
		    ."    <td class='textoTabla'>$boton_insc</td>"
		    ."    <td class='textoTabla'>$id</td>"
		    ."    <td class='textoTabla'>$asignatura</td>"
		    ."    <td class='textoTabla'>$periodo</td>"
		    ."    <td class='textoTabla'>$profesor</td>"
		    ."    <td class='textoTabla'>$horario</td>"
		    ."  </tr>");
		}

	}
?>
        </table>
        </div>
      </td>
    </tr>
  </table>
  <br>
<?php } ?>
  <table bgcolor="#ffffff" cellspacing="1" cellpadding="3" class="tabla">
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

			$tipo_asistencia = $hibrida = "";
			if ($tipo_clase == "Híbrida") {
				$hibrida = "*";
				$hibridas = true;
				$readonly = "";
				if ($asistencia == "Presencial") { $readonly = "onClick='this.focus();'"; }
				$tipo_asistencia = "<select name='tipo_asistencia_curso[$id]' class='filtro' $readonly required>"
								 . "  <option value=''>-- Modalidad asistencia --</option>"
								 . select($TIPO_ASISTENCIA,$asistencia)
								 . "</select>";
			}

			echo("  <tr class='filaTabla'>\n"
			    ."    <td class='textoTabla'><a href='principal.php?modulo=$modulo&id_curso=$id&eliminar=si&id_alumno=$id_alumno' class='boton'> - </a></td>"
			    ."    <td class='textoTabla'>$id</td>"
			    ."    <td class='textoTabla'><label for='id_curso_$id'>$asignatura</label> $hibrida<br>$tipo_asistencia</td>"
			    ."    <td class='textoTabla'>$periodo</td>"
			    ."    <td class='textoTabla'>$profesor</td>"
			    ."    <td class='textoTabla'>$horario</td>"
			    ."    <td class='textoTabla'>$fecha_ins</td>"
			    ."    <td class='textoTabla' align='center'>$alza_prereq</td>"
			    ."    <td class='textoTabla' align='center'>$informada</td>"
			    ."  </tr>");
		}
	} else {
		echo("<td class='textoTabla' colspan='9' align='center'>"
          ."  Aún no tienes cursos inscritos."
          ."</td>\n");
	}
?>
  </table>  

<?php if ($hibridas) { ?>
  <b>* Esta asignatura se impartirá en modalidad Híbrida. Es necesario que se indique la forma en que asistirá el/la estudiante, vale decir, en forma "Presencial" o "a Distancia".<br>
  <br>
  NOTA: Los cursos que se dictan en modalidad Híbrida tendrán un aforo máximo en la sala de clases, en concordancia con la normativa dictada por la autoridad Sanitaria y de Educación.</b>
  <br><br>
<?php } ?>

</div>

</form>
<!-- Fin: <?php echo($modulo); ?> -->

<?php
/*
$SQL_carreras_escuela = "SELECT id FROM carreras WHERE id_escuela = (SELECT id_escuela FROM carreras
                                                                     WHERE id=$id_carrera_alumno)";
                                                                     
$SQL_cursos_escuela = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,vc.profesor,
                              vc.semestre||'-'||vc.ano AS periodo,
                              coalesce(vc.sesion1,'')||coalesce(vc.sesion2,'')||coalesce(vc.sesion3,'') AS horario
                       FROM vista_cursos AS vc
                       WHERE ano=$ANO_InsAsig AND semestre=$SEMESTRE_InsAsig AND id_prog_asig NOT IN ($SQL_pa_cursos_ins)
                         AND (id_carrera IN ($SQL_carreras_escuela)
                          OR carrera='Electivos de Formación General')
                       ORDER BY asignatura;";
$cursos_escuela = consulta_sql($SQL_cursos_escuela);
*/

?>
