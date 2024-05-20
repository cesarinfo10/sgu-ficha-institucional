<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

//include("validar_modulo.php");

$id_curso = $_REQUEST['id_curso'];
$cant_np  = $_REQUEST['cant_np'];
$token    = $_REQUEST['token'];
$volver   = $_REQUEST['volver'];

if ($id_curso == "" || $token == "") {
	echo(js("location.href='principal.php?modulo=portada';"));
	exit;
}

$SQL_curso = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,vc.semestre,
                     vc.ano,vc.profesor,vc.id_profesor,vc.carrera,cant_alumnos_asist(vc.id),
                     coalesce(vc.sesion1,'')||' '||coalesce(vc.sesion2,'')||' '||coalesce(vc.sesion3,'') as horario,
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

	if ($token <> md5($id_curso.$id_profesor)) { 
		echo(msje_js("Error de consistencia. No se puede continuar"));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}

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

if ($_REQUEST['guardar'] == "Guardar" && $cant_np <> "" && $token == md5($id_curso.$id_profesor)) {
	$SQL_curso_update = "UPDATE cursos SET cant_notas_parciales='$cant_np' WHERE id=$id_curso OR id_fusion=$id_curso;";
	if (consulta_dml($SQL_curso_update) > 0) {
		echo(msje_js('Se ha guardado la información ingresada.'));
		consulta_dml("UPDATE cargas_academicas SET nota_catedra=null,nota_final=null WHERE id_curso IN (SELECT id FROM cursos WHERE $id_curso IN (id,id_fusion))");
		if ($volver <> "") {
			$volver = base64_decode($volver);
			echo(js("location.href='$volver';"));
		} else {
			echo(js("parent.jQuery.fancybox.close();"));
		}
		exit;
	}
}

if ($volver <> "") { $botonCancelar = "location.href='".base64_decode($volver)."';"; } else { $botonCancelar = "parent.jQuery.fancybox.close();"; }
$CANT_NOTAS_PARCIALES = array();
for ($x=1;$x<=7;$x++) { $CANT_NOTAS_PARCIALES[] = array('id'=>$x,'nombre'=>$x); }
	
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  Definir cantidad de Notas Parciales 
</div>

<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_curso" value="<?php echo($id_curso); ?>">
<input type="hidden" name="token" value="<?php echo($token); ?>">
<input type="hidden" name="volver" value="<?php echo($volver); ?>">

<div style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Guardar">
  <input type="button" value="Cancelar" onClick="<?php echo($botonCancelar); ?>">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
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
    <td class='celdaNombreAttr' colspan="3">Cantidad de Notas Parciales:</td>
    <td class='celdaValorAttr'>
      <select name='cant_np' class='filtro'>
        <?php echo(select($CANT_NOTAS_PARCIALES,$cant_notas_parciales)); ?>
      </select>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->
