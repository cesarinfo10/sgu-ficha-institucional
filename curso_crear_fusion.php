<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_curso     = $_REQUEST['id_curso'];

$SQL_curso = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,vc.semestre as sem_num,vc.ano,
              FROM vista_cursos AS vc
              LEFT JOIN detalle_mallas AS dm ON (dm.id_prog_asig=
              WHERE vc.id=$id_curso";

$SQL_curso = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,vc.semestre as sem_num,vc.seccion,
                     CASE vc.semestre
                          WHEN 0 THEN 'Verano'
                          WHEN 1 THEN 'Primero'
                          WHEN 2 THEN 'Segundo'
                     END AS sem,vc.semestre,vc.ano,vc.profesor,vc.carrera,vc.id_profesor,vc.id_carrera,coalesce(ca.regimen,ca2.regimen) AS regimen,
                     c.dia1,c.horario1,c.dia2,c.horario2,c.dia3,c.horario3,vc.sala1,vc.sala2,vc.sala3,
                     vc.id_prog_asig,cantidad_alumnos(vc.id) AS cant_alumnos,cant_alumnos_asist(vc.id) AS cant_alumnos_asist,
                     pa.ano AS ano_prog_asig,m.ano AS ano_malla,c.cerrado,to_char(fecha_acta,'DD-MM-YYYY') AS fecha_acta,
                     to_char(fecha_acta_comp,'DD-MM-YYYY') AS fecha_acta_comp,vu.nombre AS usuario_emisor,recep_acta,recep_acta_comp,c.cerrado,
                     CASE WHEN c.cerrado THEN 'Cerrado' ELSE 'Abierto' END AS estado,coalesce(c.cupo,0) AS cupo
              FROM vista_cursos AS vc
              LEFT JOIN prog_asig AS pa ON pa.id=vc.id_prog_asig
              LEFT JOIN detalle_mallas AS dm ON dm.id_prog_asig=vc.id_prog_asig
              LEFT JOIN mallas AS m ON m.id=dm.id_malla
              LEFT JOIN cursos AS c ON c.id=vc.id
              LEFT JOIN carreras AS ca ON ca.id=vc.id_carrera
              LEFT JOIN carreras AS ca2 ON ca2.id=m.id_carrera
              LEFT JOIN vista_usuarios AS vu ON vu.id=id_usuario_emisor_acta
              WHERE vc.id=$id_curso;";
$curso = consulta_sql($SQL_curso);
extract($curso[0]);
$regimen_ = implode("','",array_column($curso,'regimen'));
//var_dump($regimen_);
//echo($SQL_curso);

if ($_REQUEST['guardar'] == "Guardar" && !empty($_REQUEST['id_prog_asig'])) {
	$id_prog_asig = $_REQUEST['id_prog_asig'];
	$val_curso = consulta_sql("SELECT 1 FROM cursos WHERE id_prog_asig = $id_prog_asig AND semestre = $semestre AND ano = $ano AND seccion = $seccion");
	if (count($val_curso) == 0) {
		$SQL_insert = "INSERT INTO cursos (id_prog_asig,seccion,semestre,ano,id_profesor,id_ayudante,dia1,horario1,dia2,horario2,dia3,horario3,cod_google_classroom,tipo_clase,
		                      sala1,sala2,sala3,cant_notas_parciales,cupo,fec_ini,fec_fin,fec_sol1,fec_sol2,fec_sol_recup,id_prog_curso,id_fusion)
		               SELECT $id_prog_asig AS id_prog_asig,seccion,semestre,ano,id_profesor,id_ayudante,dia1,horario1,dia2,horario2,dia3,horario3,cod_google_classroom,tipo_clase,
		                      sala1,sala2,sala3,cant_notas_parciales,cupo,fec_ini,fec_fin,fec_sol1,fec_sol2,fec_sol_recup,id_prog_curso,$id_curso AS id_fusion
		               FROM cursos
		               WHERE id=$id_curso";		               
		if (consulta_dml($SQL_insert) > 0) {
			$msje = "El curso se ha creado y fusionado exitosamente.";
			echo(msje_js($msje));
			echo(js("parent.jQuery.fancybox.close();"));
			exit;
		}
	} else {
		$msje = "El curso que intenta crear a través de la fusión ya existe.\\n\\n"
		      . "No se puede continuar.";
		echo(msje_js($msje));
	}
}
if ($_SESSION['tipo'] > 0) { $cond_carreras = "AND id IN ({$_SESSION['ids_carreras']})"; }
$CARRERAS = consulta_sql("SELECT id,nombre,CASE WHEN activa THEN 'Vigentes' ELSE 'No Vigentes' END AS grupo FROM carreras WHERE regimen IN ('$regimen_') ORDER BY activa DESC,nombre");

if (!empty($_REQUEST['id_carrera'])) { $id_carrera = $_REQUEST['id_carrera']; }
$SQL_anos_mallas = "SELECT id FROM mallas WHERE id IN (SELECT id_malla FROM detalle_mallas WHERE id_prog_asig=$id_prog_asig)";
$MALLAS = consulta_sql("SELECT id,ano AS nombre FROM mallas WHERE id_carrera='$id_carrera' AND id NOT IN ($SQL_anos_mallas) ORDER BY ano DESC");

if (!empty($_REQUEST['id_malla'])) { $id_malla = $_REQUEST['id_malla']; }
$ASIGNATURAS = consulta_sql("SELECT id_prog_asig AS id,cod_asignatura||' '||asignatura AS nombre FROM vista_detalle_malla WHERE caracter<>'Electiva' AND id_malla=$id_malla ORDER BY nivel,cod_asignatura");

$est_cursos = array(array('id'=>"f",'nombre'=>"Abierto"),
                    array('id'=>"t",'nombre'=>"Cerrado"));
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal_sm.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_curso" value="<?php echo($id_curso); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>
<div style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Guardar">
  <input type="button" name="cancelar" value="Cancelar" onClick="parent.jQuery.fancybox.close();">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan='4' style='text-align:center'>Antecedentes del curso base</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Nº Acta:</td>
    <td class='celdaValorAttr'><?php echo($id); ?></td>
    <td class='celdaNombreAttr'>Periodo:</td>
    <td class='celdaValorAttr'><?php echo($sem_num."-".$ano); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Asignatura:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($asignatura); ?> <?php echo($prog_asig); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($carrera); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan='4' style='text-align:center'>Antecedentes del curso a fusionar</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name='id_carrera' onChange='submitform();'>
        <option>-- Seleccione --</option>
        <?php echo(select_group($CARRERAS,$id_carrera)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Malla:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name='id_malla' onChange='submitform();'>
        <option>-- Seleccione --</option>
        <?php echo(select($MALLAS,$id_malla)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Asignatura:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name='id_prog_asig'>
		<option>-- Seleccione --</option>
        <?php echo(select($ASIGNATURAS,$id_prog_asig)); ?>
      </select>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

