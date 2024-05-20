<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_curso    = $_REQUEST['id_curso'];
$fecha       = $_REQUEST['fecha'];
$hora_inicio = $_REQUEST['hora_inicio'];
$volver      = $_REQUEST['volver'];

if ($fecha == "") { $fecha = date("Y-m-d"); }
if ($hora_inicio == "") { $hora_inicio = date("H:i"); }

if ($id_curso == "") {
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$SQL_curso = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,vc.semestre,
                     vc.ano,vc.profesor,vc.id_profesor,vc.carrera,cant_alumnos_asist(vc.id),
                     coalesce(vc.sesion1,'')||' '||coalesce(vc.sesion2,'')||' '||coalesce(vc.sesion3,'') as horario,
                     c.dia1,c.dia2,c.dia3,
                     vc.id_prog_asig,cantidad_alumnos(vc.id) AS cant_alumnos,c.cupo,c.cant_notas_parciales,token AS cod,
                     CASE WHEN c.cerrado THEN 'Cerrado' ELSE 'Abierto' END AS estado,coalesce(c.cupo,0) AS cupo,
                     coalesce(to_char(c.fec_ini,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_ini,coalesce(to_char(c.fec_fin,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_fin,
                     coalesce(to_char(c.fec_sol1,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_sol1,coalesce(to_char(c.fec_sol2,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_sol2,
                     coalesce(to_char(c.fec_sol_recup,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_sol_recup
              FROM vista_cursos AS vc
              LEFT JOIN cursos AS c USING(id)
              LEFT JOIN vista_cursos_cod_barras AS vccb USING(id)
              WHERE vc.id=$id_curso;";
$curso = consulta_sql($SQL_curso);

if (count($curso) > 0) {
	
	extract($curso[0]);

	$SQL_cursos_fusion = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,c.id_prog_asig 
	                      FROM vista_cursos AS vc
	                      LEFT JOIN cursos AS c USING (id)
	                      WHERE id_fusion = $id_curso";
	$cursos_fusion     = consulta_sql($SQL_cursos_fusion);
	$HTML_fusionadas = "";
	$ids_cursos = $ids_pa = "";
	for ($x=0;$x<count($cursos_fusion);$x++) {
		$HTML_fusionadas .= "<small><br>&nbsp;<big><b>↳</b></big>{$cursos_fusion[$x]['asignatura']}</small>";
		$ids_cursos      .= "{$cursos_fusion[$x]['id']},";
		$ids_pa          .= "{$cursos_fusion[$x]['id_prog_asig']},";
	}
	
	$ids_cursos .= $id_curso;
	$ids_pa     .= $id_prog_asig;
	
	$SQL_mallas = "SELECT char_comma_sum(alias_carrera||ano::text) AS anos FROM vista_mallas WHERE id IN (SELECT id_malla FROM detalle_mallas WHERE id_prog_asig IN ($ids_pa))";
	$mallas = consulta_sql($SQL_mallas);
	$mallas = $mallas[0]['anos'];
}

$botonCancelar = "location.href='$enlbase_sm=cursos_libro_clases&id_curso=$id_curso';";

if ($_REQUEST['guardar'] == "Guardar" && $fecha <> "" && $hora_inicio <> "") {
    
    $SQL_ins_curso_sesion = "INSERT INTO cursos_sesiones (id_curso,fecha,hora_inicio,id_usuario_reg,ip_reg) "
                          . "     VALUES ($id_curso,'$fecha'::date,'$hora_inicio'::time,{$_SESSION['id_usuario']},'{$_SERVER['REMOTE_ADDR']}');"
                          . "INSERT INTO ca_asistencia (id_ca,id_sesion) SELECT id_ca,currval('cursos_sesiones_id_seq') AS id_sesion FROM vista_cursos_alumnos WHERE id_curso IN ($ids_cursos) ORDER BY nombre_alumno;";

	if (consulta_dml($SQL_ins_curso_sesion) > 0) {
		$sesion = consulta_sql("SELECT id FROM cursos_sesiones WHERE id_curso=$id_curso AND fecha='$fecha'::date");
		consulta_dml("INSERT INTO ca_temp_asist (id_ca_temporal,id_sesion) SELECT id,{$sesion[0]['id']} AS id_sesion FROM vista_ca_temporal WHERE id_curso IN ($ids_cursos);");
        echo(msje_js("Se ha creado la sesión correctamente. Ahora puede comenzar su cátedra."));
        echo(js("location.href='$enlbase_sm=cursos_libro_clases&id_curso=$id_curso';"));
        exit;
    } else {
        echo(msje_js("ERROR: No ha sido posible crear la sesión.\\n\\n"
                    ."Es posible que la sesión ya exista para el día indicado.\\n\\n"
                    ."Verifique la fecha."));
    }
}

if (($dia1>0 && $dia1<>date("N")) || ($dia2>0 && $dia2<>date("N")) || ($dia3>0 && $dia3<>date("N"))) {
    echo(msje_js("ATENCIÓN: Hoy no corresponde dictar este curso. Se procede de todas maneras."));
}	
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_curso" value="<?php echo($id_curso); ?>">

<div style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Guardar">
  <input type="button" value="Cancelar" onClick="<?php echo($botonCancelar); ?>">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: center'>Antecedentes del Curso</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Nº Acta:</td>
    <td class='celdaValorAttr'><?php echo($id); ?></td>
    <td class='celdaNombreAttr'>Periodo:</td>
    <td class='celdaValorAttr'><?php echo($semestre."-".$ano); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Asignatura:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($asignatura . " " . $prog_asig . $HTML_fusionadas); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($carrera); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Profesor:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($profesor); ?> <?php echo($ficha_prof); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Inscrito(a)s:</td>
    <td class='celdaValorAttr'><?php echo($cant_alumnos); ?> alumno(a)s</td>
    <td class='celdaNombreAttr'>Asistentes:</td>
    <td class='celdaValorAttr'><?php echo($cant_alumnos_asist); ?> alumno(a)s</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Horario:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($horario); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: center'>Antecedentes de la sesión o clase</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><input name="fecha" max="<?php echo(date("Y-m-d")); ?>" type="date" class="botoncito" value="<?php echo($fecha); ?>" required></td>
    <td class='celdaNombreAttr'>Hora de Inicio:</td>
    <td class='celdaValorAttr'><input name="hora_inicio" type="time" value="<?php echo($hora_inicio); ?>" class="botoncito" readonly></td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->
