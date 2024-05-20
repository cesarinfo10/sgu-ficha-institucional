<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

$id_alumno = $_SESSION['id'];
if (!is_numeric($id_alumno)) {
	echo(js("location.href='principal.php?modulo=portada';"));
	exit;
}

$SQL_cursos_actuales = "SELECT id FROM cursos WHERE ano=$ANO and semestre=$SEMESTRE";

$SQL_mis_cursos = "SELECT vac.id_curso,vc.cod_asignatura||'-'||vc.seccion||'<br>'||vc.asignatura AS asignatura,
                          pa.ano AS ano_prog_asig,vc.id_prog_asig,u.apellido||'<br>'||u.nombre AS profesor,c.cod_google_classroom,
                          cp.c1,cp.c2,cp.c3,cp.c4,cp.c5,cp.c6,cp.c7,
                          vac.s1,vac.nc,vac.s2,vac.recuperativa AS rec,vac.nf,vac.situacion,vac.id_estado
                   FROM vista_alumnos_cursos AS vac
                   LEFT JOIN vista_cursos AS vc ON vc.id=vac.id_curso
                   LEFT JOIN cursos       AS c ON c.id=vac.id_curso
                   LEFT JOIN usuarios     AS u ON u.id=c.id_profesor
                   LEFT JOIN prog_asig    AS pa ON pa.id=vc.id_prog_asig
                   LEFT JOIN vista_calificaciones_parciales AS cp ON cp.id_ca=vac.id
                   WHERE vac.id_curso in ($SQL_cursos_actuales) AND vac.id_alumno=$id_alumno";
$mis_cursos = consulta_sql($SQL_mis_cursos);

$SQL_mis_horarios = "SELECT id,cod_asignatura||'-'||seccion AS cod_asig,asignatura AS nombre_asig,
                            dia1 AS dia_curso,horario1 AS horario_curso,sala1 AS sala_curso
                     FROM vista_cursos
                     WHERE id IN (SELECT id_curso FROM ($SQL_mis_cursos) AS mis_cursos WHERE id_estado IS NULL)
                     UNION ALL
                     SELECT id,cod_asignatura||'-'||seccion AS cod_asig,asignatura AS nombre_asig,
                            dia2 AS dia_curso,horario2 AS horario_curso,sala2 AS sala_curso
                     FROM vista_cursos
                     WHERE id IN (SELECT id_curso FROM ($SQL_mis_cursos) AS mis_cursos WHERE id_estado IS NULL)
                     UNION ALL
                     SELECT id,cod_asignatura||'-'||seccion AS cod_asig,asignatura AS nombre_asig,
                            dia3 AS dia_curso,horario3 AS horario_curso,sala3 AS sala_curso
                     FROM vista_cursos
                     WHERE id IN (SELECT id_curso FROM ($SQL_mis_cursos) AS mis_cursos WHERE id_estado IS NULL)
                     ORDER BY horario_curso,dia_curso";
$mis_horarios = consulta_sql($SQL_mis_horarios);

$SQL_alumno = "SELECT va.id,va.rut,va.nombre,va.carrera,va.malla_actual,a.nombre_usuario,a.jornada
               FROM vista_alumnos AS va
               LEFT JOIN alumnos AS a USING (id)
               WHERE va.id=$id_alumno";
$alumno = consulta_sql($SQL_alumno);

$HTML_mis_cursos = "";
for ($x=0;$x<count($mis_cursos);$x++) {
	extract($mis_cursos[$x]);

	$enl        = "$enlbase=ver_mi_curso&id_curso=$id_curso";
	//$js_onClick = "\"window.location='$enl';\"";
	//$asignatura = "<a class='enlitem' href='$enl'>$asignatura</a>";
	
	$enl_prog_asig = "$enlbase=ver_prog_asig&id_prog_asig=$id_prog_asig";
	$ano_prog_asig = "<a class='enlitem' href='$enl_prog_asig' title='Pincha aquí para ver el Programa de Asignatura del curso'>$ano_prog_asig</a>";
	
	$enl_cal    = "$enlbase=calendarizacion&id_curso=$id_curso";
	$asignatura = "<a class='enlitem' href='$enl_cal' title='Pincha aquí para ver la Calendarización del curso'>$asignatura</a>";
	
	$HTML_mis_cursos .= "  <tr class='filaTabla' onClick=$js_onClick>\n"
	                 .  "    <td class='textoTabla'>$asignatura</td>\n"
	                 .  "    <td class='textoTabla'>$ano_prog_asig</td>\n"
	                 .  "    <td class='textoTabla'>$profesor</td>\n"
	                 .  "    <td class='textoTabla' style='text-align: center'>$cod_google_classroom</td>\n"
	                 .  "    <td class='celdaramomalla'>$c1</td>\n"
	                 .  "    <td class='celdaramomalla'>$c2</td>\n"
	                 .  "    <td class='celdaramomalla'>$c3</td>\n"
	                 .  "    <td class='celdaramomalla'>$c4</td>\n"
	                 .  "    <td class='celdaramomalla'>$c5</td>\n"
	                 .  "    <td class='celdaramomalla'>$c6</td>\n"
	                 .  "    <td class='celdaramomalla'>$c7</td>\n"
	                 .  "    <td class='textoTabla'>$nc</td>\n"
	                 .  "    <td class='textoTabla'>$s1</td>\n"
	                 .  "    <td class='textoTabla'>$s2</td>\n"
	                 .  "    <td class='textoTabla'>$rec</td>\n"
	                 .  "    <td class='textoTabla'>$nf</td>\n"
	                 .  "    <td class='textoTabla'>$situacion</td>\n"
	                 .  "  </tr>";
}

$HTML_mis_horarios = "";

$HTML_mis_horarios = "";

$horarios = consulta_sql("SELECT trim(id) as id FROM horarios ORDER BY id");

for ($y=0;$y<count($horarios);$y++) {
	$horario = $horarios[$y]['id'];
/*
for ($mod_horario=65;$mod_horario<73;$mod_horario++) {

	$horario = chr($mod_horario);
*/			
	$HTML_mis_horarios .= "<tr class='textoTabla'><td class='filatituloTabla' valign='middle' align='center'><b>$horario</b></td>";
	
	for ($dia=1;$dia<7;$dia++) {

		$encontrado = false;
    $HTML_mis_horarios .= "<td class='celdaramomalla' width='120' align='center'>";
		
		for ($x=0;$x<count($mis_horarios);$x++) {
			extract($mis_horarios[$x]);

			if ($dia_curso == $dia && trim($horario_curso) == $horario) {

		   	$enl         = "$enlbase=ver_mi_curso&id_curso=$id_curso";
				//$nombre_asig = "<a class='enlitem' href='$enl'>$nombre_asig</a>";
 
				$HTML_mis_horarios .= "  <u>$cod_asig</u><br><b>$nombre_asig</b><br>"  
				                   .  "  Sala: $sala_curso<hr>";
				$encontrado = true;
			} else {
        $HTML_mis_horarios .= "&nbsp;";
      }
			
		}
    $HTML_mis_horarios .=  "</td>";
				
	}
		
	$HTML_mis_horarios .= "</tr>\n";

}
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  Mis cursos actuales
</div>
<?php extract($alumno[0]); ?>
<table cellpadding="2" cellspacing="1" border="0" class="tabla" bgcolor="#ffffff" style='margin-top: 5px'>
  
  <tr><td class='celdaNombreAttr' style='text-align:center' colspan="4">Antecedentes Personales</td></tr>  
  <tr>
    <td class='celdaNombreAttr'>Código Interno:</td>
    <td class='celdaValorAttr'><?php echo($id); ?></td>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($rut); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($nombre); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr'><?php echo($carrera."-".$jornada); ?></td>
    <td class='celdaNombreAttr'>Año Malla:</td>
    <td class='celdaValorAttr'><?php echo($malla_actual); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>e-Mail G-Suite:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($nombre_usuario."@alumni.umc.cl"); ?></td>
  </tr>
</table>
<table cellpadding="2" cellspacing="1" border="0" class="tabla" bgcolor="#ffffff" style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="4">Cursos del periodo <?php echo("$SEMESTRE-$ANO");?></td>
    <td class='tituloTabla' colspan="8">Notas Parciales</td>
    <td class='tituloTabla' colspan="3">Solemnes</td>
    <td class='tituloTabla' rowspan="2">Nota<br>Final</td>
    <td class='tituloTabla' rowspan="2">Situación</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Asignatura</td>
    <td class='tituloTabla'>Prog.<br>Asig.</td>
    <td class='tituloTabla'>Profesor</td>
    <td class='tituloTabla'>Classroom</td>
    <td class='tituloTabla'>C1</td>
    <td class='tituloTabla'>C2</td>
    <td class='tituloTabla'>C3</td>
    <td class='tituloTabla'>C4</td>
    <td class='tituloTabla'>C5</td>
    <td class='tituloTabla'>C6</td>
    <td class='tituloTabla'>C7</td>
    <td class='tituloTabla'>NC</td>
    <td class='tituloTabla'>S1</td>
    <td class='tituloTabla'>S2</td>
    <td class='tituloTabla'>Rec</td>
  </tr>
  <?php echo($HTML_mis_cursos); ?>
  <tr>
    <td colspan="20">
      <table cellpadding="2" cellspacing="1" border="0" class="tabla" bgcolor="#ffffff" align="center" style='margin-top: 5px'>
        <tr class='filaTituloTabla'><td class='tituloTabla' colspan="7">Horario</td></tr>
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
    </td>
  </tr>
</table>
