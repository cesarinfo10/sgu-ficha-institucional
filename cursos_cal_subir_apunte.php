<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_curso = $_REQUEST['id_curso'];
$id_cal   = $_REQUEST['id_cal'];
if (!is_numeric($id_curso) || !is_numeric($id_cal)) {
	echo(js("location.href='principal.php?modulo=gestion_cursos';"));
	exit;
}

$cal_apuntes = consulta_sql("SELECT count(id) AS cant_apuntes FROM cal_apuntes WHERE id_sesion_cal=$id_cal");
if ($cal_apuntes[0]['cant_apuntes'] == 2) {
	echo(msje_js("Actualmente esta sesión ya cuenta con dos apuntes. No es posible subir más apunte a esta sesión."));
	echo(js("window.location='$enlbase=cursos_calendarizacion&id_curso=$id_curso';"));
	exit;
}
	
if ($_REQUEST['guardar'] == "Subir") {
		
	$arch_apunte_nombre     = $_FILES['arch_apunte']['name'];
	$arch_apunte_tmp_nombre = $_FILES['arch_apunte']['tmp_name'];
	$arch_apunte_tipo_mime  = $_FILES['arch_apunte']['type'];
	$arch_apunte_longitud   = $_FILES['arch_apunte']['size'];

	if ($arch_apunte_longitud > 2097152) {
		echo(msje_js("ATENCIÓN: El archivo que está intentando subir tiene un tamaño que sobrepasa 2MB.\\n"
		            ."Lo sentimos, pero no están permitidos archivos sobre este tamaño. "
		            ."Así mismo 2MB es más que suficiente para almacenar un "
		            ."documento de varias decenas de páginas."
		            ."Le sugerimos transformar a formato PDF usando cualquier aplicación que lo "
		            ."permita, como por ejemplo OpenOffice"));
	} else {		
		$arch_apunte_data = pg_escape_bytea(file_get_contents($arch_apunte_tmp_nombre));
		$SQLINS_apunte = "INSERT INTO cal_apuntes (id_sesion_cal,nombre_archivo,tipo_mime,archivo) 
		                         VALUES ($id_cal,'$arch_apunte_nombre','$arch_apunte_tipo_mime','{$arch_apunte_data}');";
		if (consulta_dml($SQLINS_apunte) > 0) {
			echo(msje_js("Se ha recibido y guardado satisfactoriamente el archivo del apunte."));
			echo(js("window.location='$enlbase_sm=cursos_calendarizacion&id_curso=$id_curso';"));
			exit;
		}
	}
}
			
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

$SQL_cal = "SELECT id,sesion,fecha,materia,bibliografia FROM calendarizaciones WHERE id=$id_cal";
$cal     = consulta_sql($SQL_cal);
$fecha = ucfirst(strftime("%A %d de %B de %Y",strtotime($cal[0]['fecha'])));
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal_sm.php" method="post" enctype="multipart/form-data" onSubmit="enblanco2('arch_apunte');">
<input type="hidden" name="modulo"   value="<?php echo($modulo); ?>">
<input type="hidden" name="id_curso" value="<?php echo($id_curso); ?>">
<input type="hidden" name="id_cal"   value="<?php echo($id_cal); ?>">
<div style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Subir">
  <input type="button" onClick="location.href='<?php echo("$enlbase_sm=cursos_calendarizacion&id_curso=$id_curso"); ?>';" value="Volver">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="4"><center>Antedecentes del Curso</center></td></tr>
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
  <tr>
    <td class='celdaNombreAttr'>Profesor:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($profesor); ?> <?php echo($ficha_prof); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Horario:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($horario); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Inscrito(a)s:</td>
    <td class='celdaValorAttr'><?php echo($cant_alumnos); ?> alumno(a)s</td>
    <td class='celdaNombreAttr'>Asistentes:</td>
    <td class='celdaValorAttr'><?php echo($cant_alumnos_asist); ?> alumno(a)s</td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4"><center>Antedecentes de la Calendarización</center></td></tr>
  <tr>
    <td class='celdaNombreAttr'>Sesión:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($cal[0]['sesion']); ?>ª</td>
    <!-- <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><?php echo($fecha); ?></td> -->
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Contenidos:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo(nl2br($cal[0]['materia'])); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Bibliografía:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo(nl2br($cal[0]['bibliografia'])); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Archivo apunte:</u></td>
    <td class='celdaValorAttr' colspan="3">
      <input type="file" name="arch_apunte"><br>
      <sup>Le sugerimos usar archivos en formato PDF (Adobe Acrobat)</sup><br><br>
      Las opiniones, expresiones o juicios, así como los contenidos en los<br>
      materiales aquí presentes, son de exclusiva responsabilidad de quienes<br>
      los han puesto a disposición de los alumnos o terceros.<br>
      La Universidad Miguel de Cervantes, no asume autoría o propiedad sobre<br>
      ninguno de los materiales aquí presentes.
    </td>
  </tr>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
