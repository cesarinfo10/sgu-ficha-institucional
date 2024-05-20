<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

$id_curso = $_REQUEST['id_curso'];
if (!is_numeric($id_curso)) {
	echo(js("location.href='principal.php?modulo=portada';"));
	exit;
}

$id_curso_orig = $id_curso;
$curso_fusion = consulta_sql("SELECT id_fusion FROM cursos WHERE id=$id_curso AND id_fusion IS NOT NULL");
if (count($curso_fusion) > 0) { $id_curso = $curso_fusion[0]['id_fusion']; }

$SQL_curso = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,vc.semestre as sem_num,
                     CASE vc.semestre
                          WHEN 0 THEN 'Verano'
                          WHEN 1 THEN 'Primero'
                          WHEN 2 THEN 'Segundo'
                     END AS sem,vc.semestre,vc.ano,vc.profesor,vc.carrera,vc.id_profesor,
                     coalesce(vc.sesion1,'')||' '||coalesce(vc.sesion2,'')||' '||coalesce(vc.sesion3,'') as horario,
                     vc.id_prog_asig,cantidad_alumnos(vc.id) AS cant_alumnos,cant_alumnos_asist(vc.id) AS cant_alumnos_asist,
                     pa.ano AS ano_prog_asig,m.ano AS ano_malla,c.cerrado
              FROM vista_cursos AS vc
              LEFT JOIN prog_asig AS pa ON pa.id=vc.id_prog_asig
              LEFT JOIN detalle_mallas AS dm ON dm.id_prog_asig=vc.id_prog_asig
              LEFT JOIN mallas AS m ON m.id=dm.id_malla
              LEFT JOIN cursos AS c ON c.id=vc.id 
              WHERE vc.id=$id_curso;";
$curso = consulta_sql($SQL_curso);
           
if (count($curso) == 0) {
	echo(js("location.href='principal.php?modulo=gestion_cursos';"));
	exit;
}

extract($curso[0]);

$SQL_cursos_fusion = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,c.id_prog_asig 
					  FROM vista_cursos AS vc
					  LEFT JOIN cursos AS c USING (id)
					  WHERE id = $id_curso_orig";
$cursos_fusion     = consulta_sql($SQL_cursos_fusion);
$HTML_fusionadas = "";
$ids_cursos = $ids_pa = "";
for ($x=0;$x<count($cursos_fusion);$x++) {
	$HTML_fusionadas .= "<small><br>&nbsp;<big><b>↳</b></big><a title='ID: {$cursos_fusion[$x]['id']}'>{$cursos_fusion[$x]['asignatura']}</a></small>";
	$ids_cursos      .= "{$cursos_fusion[$x]['id']},";
	$ids_pa          .= "{$cursos_fusion[$x]['id_prog_asig']},";
}
$prog_asig = "<a class='enlaces' href='$enlbase=ver_prog_asig&id_prog_asig=$id_prog_asig'><small>Ver programa</small></a>";

$SQL_calendarizacion = "SELECT id,sesion,fecha,materia,bibliografia FROM calendarizaciones WHERE id_curso=$id_curso ORDER BY sesion";
$calendarizacion     = consulta_sql($SQL_calendarizacion);

$SQL_cal_apuntes = "SELECT id,id_sesion_cal,nombre_archivo FROM cal_apuntes
                    WHERE id_sesion_cal IN (SELECT id FROM calendarizaciones WHERE id_curso=$id_curso) ORDER BY id_sesion_cal ";
$cal_apuntes     = consulta_sql($SQL_cal_apuntes);
//echo($SQL_cal_apuntes);

$HTML_calendarizacion = "";
if (count($calendarizacion) > 0) {
	$y=0;
	for ($x=0;$x<count($calendarizacion);$x++) {
		extract($calendarizacion[$x]);
		
		$apuntes = "";
		while ($cal_apuntes[$y]['id_sesion_cal'] == $id && $y < count($cal_apuntes)) {
			$apuntes .= "<a href='../ver_cal_apunte.php?id={$cal_apuntes[$y]['id']}' target='_blank'>"
			         .  "  {$cal_apuntes[$y]['nombre_archivo']}"
			         .  "</a><sup><hr size='1' noshade></sup>";
			$y++;
		}
		
		$materia      = nl2br($materia);
		$bibliografia = nl2br($bibliografia);
		
		$fecha = ucfirst(strftime("%A %d<br>de %B",strtotime($fecha)));
		$HTML_calendarizacion .= "<tr class='filaTabla'>"
		                      .  "  <td class='textoTabla'><center>".$sesion."ª</center></td>"
		                      //.  "  <td class='textoTabla'>$fecha</td>"
		                      .  "  <td class='textoTabla'>$materia</td>"
		                      .  "  <td class='textoTabla'>$bibliografia <br> $apuntes</td>"
		                      //.  "  <td class='textoTabla'>$apuntes</td>"
		                      .  "</tr>";
	}
} else {
	$enl_js = "\"window.location='$enlbase=cursos_calendarizacion_editar&id_curso=$id_curso';\"";
	$boton = "<input type='button' onClick=$enl_js value='Crear'>";

	$HTML_calendarizacion = "<tr class='filaTabla'>
	                           <td class='textoTabla' colspan='5'>
	                             <center>
	                               <br><b>*** Este curso no tiene una calendarización ***</b><br><br>
	                               Debes consultar a tú profesor sobre la misma.<br><br>
	                             </center>
	                           </td>
	                         </tr>";
}
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	Calendarización del curso: <?php echo($asignatura); ?>  
</div>
<br>
<input type="button" onClick="history.back();" value="Volver">
<br><br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class='celdaNombreAttr'>Nº Acta:</td>
    <td class='celdaValorAttr'><?php echo($curso[0]['id']); ?></td>
    <td class='celdaNombreAttr'>Periodo:</td>
    <td class='celdaValorAttr'><?php echo($sem_num."-".$ano); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Asignatura:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($asignatura); ?> <?php echo($prog_asig . $HTML_fusionadas); ?></td>
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
    <td class='celdaNombreAttr'>Horario:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($horario); ?></td>
  </tr>
</table>
<br>
<div class="texto">
  <b>NOTA SOBRE LOS APUNTES:</b> Las opiniones, expresiones o juicios, así como los contenidos en los 
  materiales aquí presentes, son de exclusiva responsabilidad de quienes los han puesto a disposición 
  de los alumnos o terceros.
  La Universidad Miguel de Cervantes, no asume autoría o propiedad sobre ninguno de los materiales aquí presentes.
</div><br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" width="100%">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' width="5%">Sesión</td>
    <td class='tituloTabla' width="40%">Materia</td>
    <td class='tituloTabla' width="25%">Bibliografía y/o Apuntes</td>    
    <!-- <td class='tituloTabla' width="20%">Apuntes</td> -->
  </tr>
  <?php echo($HTML_calendarizacion); ?>
</table>
<br>
<div class="texto">
  <b>NOTA SOBRE LOS APUNTES:</b> Las opiniones, expresiones o juicios, así como los contenidos en los 
  materiales aquí presentes, son de exclusiva responsabilidad de quienes los han puesto a disposición 
  de los alumnos o terceros.
  La Universidad Miguel de Cervantes, no asume autoría o propiedad sobre ninguno de los materiales aquí presentes.
</div>
<!-- Fin: <?php echo($modulo); ?> -->
