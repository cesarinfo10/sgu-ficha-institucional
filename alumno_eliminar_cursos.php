<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

if ($_REQUEST['id_alumno'] == "") {
	header("Location: principal.php?modulo=gestion_alumnos");
	exit;
}

$id_alumno = $_REQUEST['id_alumno'];

$SQL_alumno = "SELECT va.id,va.rut,va.nombre,va.genero,va.fec_nac,
                      va.nacionalidad,coalesce(va.pasaporte,'**No corresponde**') AS pasaporte,va.direccion,va.comuna,va.region,
                      va.telefono,coalesce(va.tel_movil,'** No se registra **') AS tel_movil,va.email,
                      coalesce(va.semestre_cohorte,0) AS semestre_cohorte,va.cohorte,pap.email AS email_externo,
                      trim(va.carrera) AS carrera_alias,va.malla_actual,va.estado,va.id_malla_actual,c.nombre AS carrera,
                      ae.nombre AS estado_tramite,CASE a.jornada WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada,
                      CASE a.admision WHEN 1 THEN 'Regular' WHEN 2 THEN 'Extraordinaria' WHEN 10 THEN 'Modular' WHEN 20 THEN 'Modular (Extr.)' END AS admision
               FROM vista_alumnos AS va
               LEFT JOIN carreras AS c ON c.id=id_carrera
               LEFT JOIN alumnos AS a ON a.id=va.id
               LEFT JOIN al_estados AS ae ON ae.id=a.estado_tramite
               LEFT JOIN pap ON pap.id=a.id_pap
               WHERE va.id=$id_alumno;";
$alumno = consulta_sql($SQL_alumno);
if (count($alumno) == 0) {
	header("Location: principal.php?modulo=gestion_alumnos");
	exit;
}


extract($alumno[0]);

$id = "<a class='enlaces' href='$enlbase=ver_alumno&id_alumno=$id' title='Ver ficha'>$id</a>";
$malla = "<a class='enlaces' href='$enlbase=ver_malla&id_malla=$id_malla_actual'>$malla_actual</a>";

$aAlumno = array("ID"                => $id,
                 "Nombre"            => $nombre,
                 "RUT"               => $rut,
                 "Carrera/Año malla" => $carrera."/ ".$malla,
                 "Cohorte"           => $cohorte);

$HTML_enc_alumno = "<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla'>"
                 . tabla_encabezado($aAlumno)
                 . "</table>";


$SQL_pa_cursos_ins = "SELECT id_curso FROM inscripciones_cursos WHERE id_alumno=$id_alumno";


if ($_REQUEST['guardar'] == "Guardar") {
	$SQL_cerrar_inscripcion = "UPDATE inscripciones_cursos SET cerrada='t' WHERE id_alumno=$id_alumno";
	consulta_dml($SQL_cerrar_inscripcion);

	$SQL_cursos_semestre = "SELECT id FROM cursos WHERE semestre=$SEMESTRE AND ano=$ANO";

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
	
	echo(msje_js("Se ha guardado los cambios, aplicando la suspensión de Asignaturas"));
	echo(js("window.location='principal.php?modulo=ver_alumno&id_alumno=$id_alumno';"));		
}


if ($_REQUEST['eliminar'] == 'si' && $_REQUEST['id_curso'] <> "") {
	$id_curso = $_REQUEST['id_curso'];
	$SQLdelete_cursos_inscritos = "DELETE FROM inscripciones_cursos WHERE id_alumno=$id_alumno AND id_curso=$id_curso;";
	consulta_dml($SQLdelete_cursos_inscritos);
}                          
                          
$SQL_cursos_inscritos = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,vc.profesor,
                                vc.semestre||'-'||vc.ano AS periodo,
                                coalesce(vc.sesion1,'')||coalesce(vc.sesion2,'')||coalesce(vc.sesion3,'') AS horario,
                                to_char(ic.fecha,'DD/MM/YYYY HH24:MI') AS fecha_ins,
                                CASE ic.alza_prereq WHEN true THEN 'Si' ELSE 'No' END AS alza_prereq,
                                CASE ic.cerrada WHEN true THEN 'Si' ELSE 'No' END AS informada
                         FROM inscripciones_cursos AS ic
                         LEFT JOIN vista_cursos AS vc ON vc.id=ic.id_curso
                         WHERE id_alumno=$id_alumno
                         ORDER BY asignatura";
$cursos_inscritos = consulta_sql($SQL_cursos_inscritos);

$id_carrera_alumno = $alumno[0]['id_carrera'];
                                                                     

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
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Alumno</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Código Interno:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['id']); ?></td>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['rut']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($alumno[0]['nombre']); ?></td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Curriculares</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Admisión:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['admision']); ?></td>
    <td class='celdaNombreAttr'>Cohorte:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['semestre_cohorte'].'-'.$alumno[0]['cohorte']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera Actual:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['carrera'].' ('.$alumno[0]['carrera_alias'].')'); ?></td>
    <td class='celdaNombreAttr'>Jornada:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['jornada']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Año Malla Actual:</td>
    <td class='celdaValorAttr'>
      <a class='enlaces' href="<?php echo($enlbase.'=ver_malla&id_malla='.$alumno[0]['id_malla_actual']); ?>">
        <?php echo($alumno[0]['malla_actual']); ?>
      </a>
    </td>
    <td class='celdaNombreAttr'>Estado:</td>
    <td class='celdaValorAttr'><?php echo($alumno[0]['estado'].$estado_tramite); ?></td>
  </tr>
</table>
<br>
<div class="texto">
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

		for ($x=0; $x<count($cursos_inscritos); $x++) {
			extract($cursos_inscritos[$x]);
			echo("  <tr class='filaTabla'>\n"
			    ."    <td class='textoTabla'><a href='principal.php?modulo=$modulo&id_curso=$id&eliminar=si&id_alumno=$id_alumno' class='boton'> - </a></td>"
			    ."    <td class='textoTabla'>$id</td>"
			    ."    <td class='textoTabla'><label for='id_curso_$id'>$asignatura</label></td>"
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
</div>
</form>
<!-- Fin: <?php echo($modulo); ?> -->
