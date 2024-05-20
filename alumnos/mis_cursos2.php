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

$SQL_mis_cursos = "SELECT vac.id_curso,vc.cod_asignatura||'-'||vc.seccion AS cod_asignatura,vc.asignatura,
                          pa.ano AS ano_prog_asig,vc.id_prog_asig,initcap(vc.profesor) as profesor,c.cod_google_classroom,
                          cp.c1,cp.c2,cp.c3,cp.c4,cp.c5,cp.c6,cp.c7,
                          vac.s1,vac.nc,vac.s2,vac.recuperativa AS rec,vac.nf,vac.situacion,vac.id_estado,
						  coalesce(to_char(c.fec_sol1,'DD-tmMon-YYYY'),'#N/D') AS fec_sol1,
						  coalesce(to_char(c.fec_sol2,'DD-tmMon-YYYY'),'#N/D') AS fec_sol2,
						  coalesce(to_char(c.fec_sol_recup,'DD-tmMon-YYYY'),'#N/D') AS fec_sol_recup
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
	
	$enl_prog_asig = "$enlbase_sm=ver_prog_asig&id_prog_asig=$id_prog_asig";
	$boton_prog_asig = "<span id='prog_asig_$x' style='visibility: hidden'><a href='$enl_prog_asig' class='botoncito' id='sgu_fancybox'>📜 Ver Prog. de Asig.</a></span>";

	//$ano_prog_asig = "<a class='enlitem' id='sgu_fancybox' href='$enl_prog_asig'>$ano_prog_asig</a>";
	
	$enl_cal    = "$enlbase_sm=calendarizacion&id_curso=$id_curso";
	$boton_cal = "<span id='cal_$x' style='visibility: hidden'><a href='$enl_cal' class='botoncito' id='sgu_fancybox'>📑 Ver Calendarización</a></span>";

	$asignatura = "$cod_asignatura $boton_cal $boton_prog_asig<br>$asignatura";

	$fec_sol1 = "<a href='$enlbase_sm=mis_cursos_pruebas&prueba=sol1&id_curso=$id_curso' class='botoncito' id='sgu_fancybox_small'>📆 $fec_sol1</a>";
	$fec_sol2 = "<a href='$enlbase_sm=mis_cursos_pruebas&prueba=sol2&id_curso=$id_curso' class='botoncito' id='sgu_fancybox_small'>📆 $fec_sol2</a>";
	$fec_sol_recup = "<a href='$enlbase_sm=mis_cursos_pruebas&prueba=sol_recup&id_curso=$id_curso' class='botoncito' id='sgu_fancybox_small'>📆 $fec_sol_recup</a>";
	
	$HTML_mis_cursos .= "  <tr class='filaTabla' onMouseOver=\"elementos=['cal_$x','prog_asig_$x'];elementos.forEach(mostrar_elementos);\" onMouseOut=\"elementos=['cal_$x','prog_asig_$x'];elementos.forEach(ocultar_elementos);\" onClick=$js_onClick>\n"
	                 .  "    <td class='textoTabla'>$asignatura<div align='right'>🎓<i>$profesor</i></div></td>\n"
	                 .  "    <td class='textoTabla' style='text-align: center'>$cod_google_classroom</td>\n"
	                 .  "    <td class='celdaramomalla'>$c1</td>\n"
	                 .  "    <td class='celdaramomalla'>$c2</td>\n"
	                 .  "    <td class='celdaramomalla'>$c3</td>\n"
	                 .  "    <td class='celdaramomalla'>$c4</td>\n"
	                 .  "    <td class='celdaramomalla'>$c5</td>\n"
	                 .  "    <td class='celdaramomalla'>$c6</td>\n"
	                 .  "    <td class='celdaramomalla'>$c7</td>\n"
	                 .  "    <td class='textoTabla'>$nc</td>\n"
	                 .  "    <td class='textoTabla'><small>$fec_sol1</small><br>$s1</td>\n"
	                 .  "    <td class='textoTabla'><small>$fec_sol2</small><br>$s2</td>\n"
	                 .  "    <td class='textoTabla'><small>$fec_sol_recup</small><br>$rec</td>\n"
	                 .  "    <td class='textoTabla'>$nf</td>\n"
	                 .  "    <td class='textoTabla'>$situacion</td>\n"
	                 .  "  </tr>";
}

$HTML_mis_cursos .= "  <tr class='filaTabla' onClick=$js_onClick>\n"
                 .  "    <td class='textoTabla' colspan='10'>&nbsp;</td>\n"
                 .  "    <td class='textoTabla' colspan='5'>Pincha en las fechas para ver los detalles</td>\n"
                 .  "  </tr>\n";

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
		
		for ($x=0;$x<count($mis_horarios);$x++) {
			extract($mis_horarios[$x]);

			if ($dia_curso == $dia && trim($horario_curso) == $horario) {

		   	$enl         = "$enlbase=ver_mi_curso&id_curso=$id_curso";
				//$nombre_asig = "<a class='enlitem' href='$enl'>$nombre_asig</a>";
 
				$HTML_mis_horarios .= "<td class='celdaramomalla' width='120' align='center'>"
				                   .  "  <u>$cod_asig</u><br><b>$nombre_asig</b><br>"  
				                   .  "  Sala: $sala_curso"
				                   .  "</td>";
				$encontrado = true;
			} 
			
		}
		
		if (!$encontrado) {
			$HTML_mis_horarios .= "<td class='celdaramomalla' width='120'>&nbsp;</td>";
		}
		
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
    <td class='tituloTabla' colspan="2">Cursos del periodo <?php echo("$SEMESTRE-$ANO");?></td>
    <td class='tituloTabla' colspan="8">Notas Parciales</td>
    <td class='tituloTabla' colspan="3">Solemnes</td>
    <td class='tituloTabla' rowspan="2">Nota<br>Final</td>
    <td class='tituloTabla' rowspan="2">Situación</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Asignatura</td>
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

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 1200,
		'height'			: 400,
		'maxHeight'			: 800,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_medium").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 1000,
		'maxHeight'			: 600,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 400,
		'height'			: 400,
		'maxHeight'			: 400,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

function mostrar_elementos(id_elem) {
	document.getElementById(id_elem).style.visibility='visible';
}

function ocultar_elementos(id_elem) {
	document.getElementById(id_elem).style.visibility='hidden';
}

</script>