<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_curso = $_REQUEST['id_curso'];
if ($id_curso == "") {
	echo(js("location.href='principal.php?modulo=calificaciones';"));
	exit;
}

$SQL_curso = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,vc.semestre,
                     vc.ano,vc.profesor,vc.id_profesor,vc.carrera,cant_alumnos_asist(vc.id),
                     coalesce(vc.sesion1,'')||' '||coalesce(vc.sesion2,'')||' '||coalesce(vc.sesion3,'') as horario,
                     vc.id_prog_asig,cantidad_alumnos(vc.id) AS cant_alumnos,c.cupo,c.cant_notas_parciales,
                     md5(vc.id::text||vc.id_profesor::text) AS cod,
                     CASE WHEN ayudantia THEN 'Si' ELSE 'No' END AS ayudantia,
                     CASE WHEN c.cerrado THEN 'Cerrado' ELSE 'Abierto' END AS estado,coalesce(c.cupo,0) AS cupo,
                     coalesce(to_char(c.fec_ini,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_ini,coalesce(to_char(c.fec_fin,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_fin,
                     coalesce(to_char(c.fec_sol1,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_sol1,coalesce(to_char(c.fec_sol2,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_sol2,
                     coalesce(to_char(c.fec_sol_recup,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_sol_recup,c.cod_google_classroom,c.tipo_clase
              FROM vista_cursos AS vc
              LEFT JOIN cursos AS c USING(id)
              WHERE vc.id=$id_curso;";
$curso = consulta_sql($SQL_curso);

if (count($curso) > 0) {

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

	$SQL_alumnos_curso = "SELECT id_alumno,va.rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre_alumno,
	                             va.carrera||'-'||a.jornada AS carrera,
	                             va.cohorte,va.semestre_cohorte,va.estado,s1,nc,s2,prom,der_recup,
	                             recup,nf,situacion
	                      FROM vista_cursos_alumnos
	                      LEFT JOIN vista_alumnos AS va ON va.id=id_alumno
	                      LEFT JOIN alumnos AS a ON a.id=id_alumno
	                      WHERE id_curso IN ($ids_cursos)
	                      ORDER BY nombre_alumno;";
	$alumnos_curso     = consulta_sql($SQL_alumnos_curso);
	
	$SQL_alumnos_curso2 = "SELECT id_alumno 
	                       FROM vista_cursos_alumnos
	                       WHERE id_curso IN ($ids_cursos) AND s1 IS NOT NULL AND nc IS NOT NULL AND s2 IS NOT NULL;";
	$alumnos_curso2 = consulta_sql($SQL_alumnos_curso2);

	$SQL_prom_nf = "SELECT round(avg(nota_final),1)::numeric(3,1) AS prom_nf
	                FROM cargas_academicas
	                WHERE id_curso = '$id_curso' AND id_estado IN (1,2);";
	$curso_prom_nf = consulta_sql($SQL_prom_nf);
	$prom_nf       = $curso_prom_nf[0]['prom_nf']; 
}

$SQL_tiempo_calificaciones = "SELECT * FROM tiempo_calificaciones WHERE semestre=$SEMESTRE AND ano=$ANO;";
$tiempo_calificaciones = consulta_sql($SQL_tiempo_calificaciones);

if (count($tiempo_calificaciones) > 0) {
	if ($tiempo_calificaciones[0]['solemne1'] == "t") {
		$enlS1   = "$enlbase_sm=calificaciones_ingresar&id_curso=$id_curso&calificacion=solemne1&token={$curso[0]['cod']}";
		$botonS1 = "<a href='$enlS1' class='boton' id='sgu_fancybox_calif'>Solemne I</a> ";
	}
	if ($tiempo_calificaciones[0]['nota_catedra'] == "t") {
		$enlNC   = "$enlbase_sm=calificaciones_ingresar&id_curso=$id_curso&calificacion=nota_catedra&token={$curso[0]['cod']}";
		$botonNC = "<a href='$enlNC' class='boton' id='sgu_fancybox_calif'>Nota(s) de Cátedra</a> ";
	}
	if ($tiempo_calificaciones[0]['solemne2'] == "t") {
		$enlS2   = "$enlbase_sm=calificaciones_ingresar&id_curso=$id_curso&calificacion=solemne2&token={$curso[0]['cod']}";
		$botonS2 = "<a href='$enlS2' class='boton' id='sgu_fancybox_calif'>Solemne II</a> ";
	}

	if (count($alumnos_curso) == count($alumnos_curso2)) {
		//$enlCalcRec   = "$enlbase=calificaciones_calcular_recup&id_curso=$id_curso";
		//$botonCalcRec = "<input type='button' value='Alumnos con\nderecho a Recuperativa' onClick=\"window.location='$enlCalcRec';\"> ";
		$enlCalcNF   = "$enlbase=calificaciones_calcular_nf&id_curso=$id_curso&token={$curso[0]['cod']}";
		$botonCalcNF = "<a href='$enlCalcNF' class='boton'>Calcular Nota Final</a>";
	} else {
		$botonCalcRec = "<input type='button' value='Alumnos con\nderecho a Recuperativa'
		                onClick=\"alert('Debe estar registradas S1, NC y S2 para calcular el derecho a Recuperativa');\"> ";
		$botonCalcNF = "<input type='button' value='Calcular Nota Final'
		                onClick=\"alert('Debe estar registradas S1, NC, S2 y Recup. para calcular la Nota Final');\"> ";
	}

	if ($tiempo_calificaciones[0]['recuperativa'] == "t") {
		$enlRECUP   = "$enlbase_sm=calificaciones_ingresar&id_curso=$id_curso&calificacion=recuperativa&token={$curso[0]['cod']}";
		$botonRECUP = "<a href='$enlRECUP' class='boton' id='sgu_fancybox_calif'>Recuperativa</a> ";
	}

	$enlCalifPar   = "$enlbase_sm=calificaciones_ver_curso_califpar&id_curso=$id_curso&token={$curso[0]['cod']}";
	$botonCalifPar = "<a href='$enlCalifPar' class='boton' id='sgu_fancybox_calif'>Nota(s) de Cátedra</a> ";

}

extract($curso[0]);

$fec_ini_fin = " <b>F. Inicio:</b> $fec_ini <b>F. Término:</b> $fec_fin";
	
$cant_notas_parciales .= " <a class='boton' href='$enlbase_sm=calificaciones_def_cant_notas_parciales&id_curso=$id_curso&token={$curso[0]['cod']}' id='sgu_fancybox_small'><small>Cambiar</small></a>";
$cod_google_classroom .= " <a class='boton' href='$enlbase_sm=editar_curso_classroom&id_curso=$id_curso&token={$curso[0]['cod']}' id='sgu_fancybox_small'><small>Editar</small></a>";
$prog_asig = "<a class='boton' href='$enlbase_sm=ver_prog_asig&id_prog_asig=$id_prog_asig' id='sgu_fancybox_medium'><small>Ver programa</small></a>";

//$botones = $botonS1 . $botonCalifPar . $botonNC . $botonS2 . $botonCalcRec . $botonRECUP . $botonCalcNF;
$botones = $botonS1 . $botonCalifPar . $botonNC . $botonS2 . $botonRECUP . $botonCalcNF;

if ($botones == "") { $botones = "Nada que calificar aún"; }
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<table cellpadding="4" cellspacing="0" border="0" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class="celdaFiltro" style="vertical-align: top;">
      Acciones:<br>
      <a href='<?php echo("$enlbase_sm=cursos_calendarizacion&id_curso=$id_curso"); ?>' class='boton' id='sgu_fancybox'>Calendarizar</a>
      <a href='<?php echo("$enlbase_sm=cursos_libro_clases&id_curso=$id_curso"); ?>' class='boton' id='sgu_fancybox'>Libro de Clases</a>
<!--      <a href='<?php echo("$enlbase_sm=cursos_subir_pruebas&id_curso=$id_curso"); ?>' class='boton' id='sgu_fancybox_medium'>Subir Pruebas</a>
      <a href='<?php echo("$enlbase_sm=cursos_control_asistencia&id_curso=$id_curso"); ?>' class='boton' id='sgu_fancybox_small'>Revisar Asistencia</a> -->
      <a href='#' class='boton' onClick='history.back();'>Volver</a>
    </td>
  </tr>
</table>
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
    <td class='celdaNombreAttr'>Ayudantia:</td>
    <td class='celdaValorAttr'><?php echo($ayudantia); ?></td>
    <td class='celdaNombreAttr'>Classroom:</td>
    <td class='celdaValorAttr'><?php echo($cod_google_classroom); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Modalidad:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($tipo_clase); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Horario {sala}:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($horario . $fec_ini_fin); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Estado:</td>
    <td class='celdaValorAttr'><?php echo($estado); ?></td>
    <td class='celdaNombreAttr'>Cant. Notas Parciales:</td>
    <td class='celdaValorAttr'><?php echo($cant_notas_parciales); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: center'>Fechas de Pruebas:</td></tr>
  <tr>
    <td class='celdaValorAttr' colspan="4" style='text-align: center'><?php echo("<b>Solemne I:</b> $fec_sol1 <b>Solemne II:</b> $fec_sol2 <b>Recuperativa:</b> $fec_sol_recup"); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Inscrito(a)s:</td>
    <td class='celdaValorAttr'><?php echo($cant_alumnos); ?> alumno(a)s</td>
    <td class='celdaNombreAttr'>Asistentes:</td>
    <td class='celdaValorAttr'><?php echo($cant_alumnos_asist); ?> alumno(a)s</td>
  </tr>
</table>
<table cellpadding="4" cellspacing="0" border="0" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class="celdaFiltro" style="vertical-align: middle;" >
      Calificar:<br>
      <?php echo($botones); ?>
    </td>
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="3">
      Alumnos
      <?php echo("<a id='sgu_fancybox_small' href='$enlbase_sm=cursos_email_al&id_curso=$id_curso' class='botoncito'>Email's personales</small></a> "); ?>
      <?php echo("<a id='sgu_fancybox_small' href='$enlbase_sm=cursos_email_al&id_curso=$id_curso&gsuite=Si' class='botoncito'>Email's @alumni.umc.cl</small></a> "); ?>
    </td>
    <td class='tituloTabla' colspan="8">Calificaciones</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>Nombre</td>
    <td class='tituloTabla'>Carrera <small>(cohorte)</small></td>
    <td class='tituloTabla'>S1</td>
    <td class='tituloTabla'>NC</td>
    <td class='tituloTabla'>S2</td>
    <td class='tituloTabla'>NP</td>
<!--    <td class='tituloTabla'>D.R.</td> -->
    <td class='tituloTabla'>Rec</td>
    <td class='tituloTabla'>NF</td>
    <td class='tituloTabla'>Situación</td>
  </tr>
<?php
	$HTML_alumnos_curso = "";
	for ($x=0; $x<count($alumnos_curso); $x++) {
		extract($alumnos_curso[$x]);

		$_azul  = "color: #000099; text-align: center";
		$_rojo  = "color: #ff0000; text-align: center";
		$_verde = "color: #009900; text-align: center";

		$estilo_s1 = $estilo_nc = $estilo_s2 = $estilo_prom = $estilo_der_recup = $estilo_recup = $estilo_nf = $estilo_sit = "color: #000000;text-align: center";

		if ($s1>=1 && $s1<4) { $estilo_s1 = $_rojo; } elseif ($s1>=4) { $estilo_s1 = $_azul; }

		if ($nc>=1 && $nc<4) { $estilo_nc = $_rojo; } elseif ($nc>=4) { $estilo_nc = $_azul; }

		if ($s2>=1 && $s2<4) { $estilo_s2 = $_rojo; } elseif ($s2>=4) { $estilo_s2 = $_azul; }

		if ($prom>=1 && $prom<4) { $estilo_prom = $_rojo; } elseif ($prom>=4) { $estilo_prom = $_azul; }

		if ($der_recup == "Si") { $estilo_der_recup = $_verde; } elseif ($der_recup == "No") { $estilo_der_recup = $_rojo; }

		if ($recup>=1 && $recup<4) { $estilo_recup = $_rojo; } elseif ($recup>=4) { $estilo_recup = $_azul; }

		if ($nf>=1 && $nf<4) { $estilo_nf = $_rojo; } elseif ($nf>=4) { $estilo_nf = $_azul; }

		if ($situacion == "Reprobado") { $estilo_sit = $_rojo; } elseif ($situacion == "Aprobado") { $estilo_sit = $_verde; }

		$HTML_alumnos_curso .= "<tr class='filaTabla'>\n"
		                    . "  <td class='textoTabla' align='right'>$id_alumno</td>\n"
		                    . "  <td class='textoTabla'>$nombre_alumno</td>\n"
		                    . "  <td class='textoTabla'>$carrera ($semestre_cohorte-$cohorte)</td>\n"
		                    . "  <td class='textoTabla' style='$estilo_s1'>$s1</td>\n"
		                    . "  <td class='textoTabla' style='$estilo_nc'>$nc</td>\n"
		                    . "  <td class='textoTabla' style='$estilo_s2'>$s2</td>\n"
		                    . "  <td class='textoTabla' bgcolor='#E6E6FA' style='$estilo_prom'>$prom</td>\n"
		                    . "<!--  <td class='textoTabla' style='$estilo_der_recup'>$der_recup</td>\n -->"
		                    . "  <td class='textoTabla' style='$estilo_recup'>$recup</td>\n"
		                    . "  <td class='textoTabla' style='$estilo_nf'><b>$nf</b></td>\n"
		                    . "  <td class='textoTabla' style='$estilo_sit'>$situacion</td>\n"
		                    . "</tr>\n";
	}
	$HTML_alumnos_curso .= "<tr>\n"
	                     . "  <td class='celdaNombreAttr' align='right' colspan='8'>"
	                     . "    Promedio Notas Finales:"
	                     . "  </td>\n"
	                     . "  <td class='celdaNombreAttr' colspan='2' style='text-align: left'>&nbsp;$prom_nf</td>\n"
	                     . "</tr>\n";
	echo($HTML_alumnos_curso);
?>
</table>
<div class="texto">
  <b>NP:</b> Nota de Presentación<br>
  <b>Rec:</b> Nota de Recuperativa<br>
  <b>NF:</b> Nota Final
</div>
<!-- Fin: <?php echo($modulo); ?> -->

<?php
function nombre_dia($numero_dia_semana) {
	$dias = array(1 => "Lun","Mar","Mie","Jue","Vie","Sab","Dom");
	return $dias[$numero_dia_semana];
}
?>

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoSize'			: false,
		'fitToView'			: true,
		'autoDimensions'	: false,
		'closeBtn'	        : false,
		'closeClick'	    : false,
		'modal'      	    : true,
		'width'				: 9999,
		'height'			: 9999,
		'maxHeight'			: 9999,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
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
		'width'				: 800,
		'maxHeight'			: 700,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 520,
		'height'			: 480,
		'maxHeight'			: 480,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_calif").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'closeBtn'	        : false,
		'closeClick'	    : false,
		'modal'      	    : true,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 650,
		'height'			: 9999,
		'maxHeight'			: 9999,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});
</script>

<!-- EVITAR CLICK DERECHO-->
<script language="Javascript">
<!-- Begin
document.oncontextmenu = function(){ alert('ATENCIÓN: No está permitido informar las calificaciones a los estudiantes por una vía distinta al SGU\n\nPor ese motivo se encuentran desactivadas las opciones de Copiar/Pegar en el navegador.'); return false}
// End -->
function click() {
if (event.button==2) {
alert('Aquí el mensaje');
}
}
function keypresed() {
alert('Teclado Desabilitado');
}

document.onkeydown=keypresed;
document.onmousedown=click;
</script>

