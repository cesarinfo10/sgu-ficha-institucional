	<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_curso    = $_REQUEST['id_curso'];
$ordenar_por = $_REQUEST['ordenar_por'];

if (empty($ordenar_por)) { $ordenar_por = "nombre_alumno"; }

if (!is_numeric($id_curso)) {
	echo(js("location.href='principal.php?modulo=gestion_cursos';"));
	exit;
}

$mod_ant = $_SESSION['enlace_volver'];

$SQL_curso_finalizado = "SELECT CASE WHEN count(id) = count(id_estado) THEN true ELSE false END AS calc_acta
	                     FROM cargas_academicas
	                     WHERE id_curso=$id_curso";	                     
	                     
$SQL_curso = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,vc.semestre as sem_num,
                     CASE vc.semestre
                          WHEN 0 THEN 'Verano'
                          WHEN 1 THEN 'Primero'
                          WHEN 2 THEN 'Segundo'
                     END AS sem,vc.semestre,vc.ano,vc.profesor,vc.carrera,vc.id_profesor,
                     CASE WHEN ayudantia THEN 'Si' ELSE 'No' END AS ayudantia,
                     coalesce(vc.sesion1,'')||' '||coalesce(vc.sesion2,'')||' '||coalesce(vc.sesion3,'') as horario,
                     vc.id_prog_asig,cantidad_alumnos(vc.id) AS cant_alumnos,cant_alumnos_asist(vc.id) AS cant_alumnos_asist,
                     pa.ano AS ano_prog_asig,m.ano AS ano_malla,c.cerrado,to_char(fecha_acta,'DD-MM-YYYY') AS fecha_acta,
                     to_char(fecha_acta_comp,'DD-MM-YYYY') AS fecha_acta_comp,vu.nombre AS usuario_emisor,recep_acta,recep_acta_comp,
                     CASE WHEN c.cerrado THEN 'Cerrado' ELSE 'Abierto' END AS estado,coalesce(c.cupo,0) AS cupo,
                     coalesce(to_char(c.fec_ini,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_ini,coalesce(to_char(c.fec_fin,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_fin,
                     coalesce(to_char(c.fec_sol1,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_sol1,coalesce(to_char(c.fec_sol2,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_sol2,
                     coalesce(to_char(c.fec_sol_recup,'tmDy FMDD-tmMon-YY'),'#N/D') AS fec_sol_recup,
                     md5(vc.id::text||vc.id_profesor::text) AS cod,car.regimen,c.cod_google_classroom, c.course_id_moodle as cod_moodle,
					 c.diferencias_sgu_moodle as diferencias_moodle
              FROM vista_cursos        AS vc
              LEFT JOIN prog_asig      AS pa ON pa.id=vc.id_prog_asig
              LEFT JOIN detalle_mallas AS dm ON dm.id_prog_asig=vc.id_prog_asig
              LEFT JOIN mallas         AS m ON m.id=dm.id_malla
              LEFT JOIN cursos         AS c ON c.id=vc.id
              LEFT JOIN carreras       AS car ON car.id=vc.id_carrera
              LEFT JOIN vista_usuarios AS vu ON vu.id=id_usuario_emisor_acta
              WHERE vc.id=$id_curso";
$curso = consulta_sql($SQL_curso);
extract($curso[0]);
           
if (count($curso) > 0) {
	$fec_ini_fin = " <b>F. Inicio:</b> $fec_ini <b>F. Término:</b> $fec_fin";
	
	$SQL_cursos_fusion = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,c.id_prog_asig 
	                      FROM vista_cursos AS vc
	                      LEFT JOIN cursos AS c USING (id)
	                      WHERE id_fusion = $id_curso";
	$cursos_fusion     = consulta_sql($SQL_cursos_fusion);
	$HTML_fusionadas = "";
	$ids_cursos = $ids_pa = "";
	for ($x=0;$x<count($cursos_fusion);$x++) {
		$HTML_fusionadas .= "<small><br>&nbsp;<big><b>↳</b></big><a title='ID: {$cursos_fusion[$x]['id']}'>{$cursos_fusion[$x]['asignatura']}</a></small>";
		$ids_cursos      .= "{$cursos_fusion[$x]['id']},";
		$ids_pa          .= "{$cursos_fusion[$x]['id_prog_asig']},";
	}
	
	$ids_cursos .= $id_curso;
	$ids_pa     .= $id_prog_asig;
	
	$SQL_mallas = "SELECT char_comma_sum(alias_carrera||ano::text) AS anos FROM vista_mallas WHERE id IN (SELECT id_malla FROM detalle_mallas WHERE id_prog_asig IN ($ids_pa))";
	$mallas = consulta_sql($SQL_mallas);
	$mallas = $mallas[0]['anos'];

	$SQL_curso_alumnos = "SELECT vca.id_alumno,a.rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre_alumno,
	                             c.alias||'-'||a.jornada AS carrera,a.cohorte,a.semestre_cohorte,a.estado,
	                             vca.s1,vca.nc,vca.s2,vca.recup AS rec,vca.nf,vca.situacion,ca.asistencia,
								 to_char(vca.fecha_mod,'DD-tmMon-YYYY') AS fecha_inscripcion
	                      FROM vista_cursos_alumnos AS vca
	                      LEFT JOIN alumnos AS a ON a.id=vca.id_alumno
	                      LEFT JOIN carreras AS c ON c.id=a.carrera_actual
						  LEFT JOIN cargas_academicas AS ca ON ca.id=vca.id_ca
	                      WHERE vca.id_curso IN ($ids_cursos)
	                      ORDER BY $ordenar_por;";
	$curso_alumnos = consulta_sql($SQL_curso_alumnos);

	$SQL_calc_acta = "SELECT CASE WHEN count(id) = count(id_estado) THEN true ELSE false END AS calc_acta
	                  FROM cargas_academicas
	                  WHERE id_curso IN ($ids_cursos)";
	$calc_acta = consulta_sql($SQL_calc_acta);
	$acta_imprimible = $calc_acta[0]['calc_acta'];

	$SQL_curso_prom_nf = "SELECT avg(nota_final)::numeric(2,1) AS prom_nf
	                      FROM cargas_academicas
	                      WHERE id_curso IN ($ids_cursos) AND id_estado IN (1,2);";
	$curso_prom_nf = consulta_sql($SQL_curso_prom_nf);
	$promedio_nf = $curso_prom_nf[0]['prom_nf'];
	
}

extract($curso[0]);

$cod_google_classroom .= " <a class='boton' href='$enlbase_sm=editar_curso_classroom&id_curso=$id_curso&token={$curso[0]['cod']}' id='sgu_fancybox_small'><small>Editar</small></a>";

$cod_moodle .= " <a class='boton' href='$enlbase_sm=editar_curso_moodle&pantalla=1&id_curso=$id_curso&nombre_asignatura=$asignatura&nombre_malla=$mallas&nombre_carrera=$carrera&token={$curso[0]['cod']}' id='sgu_fancybox_small'><small>Editar</small></a>";
	
$prog_asig = "<a class='boton' id='sgu_fancybox_medium' href='$enlbase_sm=ver_prog_asig&id_prog_asig=$id_prog_asig'><small>Ver programa</small></a>";
$ficha_prof = "<a class='boton' id='sgu_fancybox_medium' href='$enlbase_sm=ver_profesor&id_profesor=$id_profesor'><small>Ver ficha</small></a>";

$ORDENAR_POR = array(array('id' => "nombre_alumno", 'nombre' => "Nombre Estudiante"),
                     array('id' => "vca.fecha_mod", 'nombre' => "Fecha de Inscripción"));
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<table cellpadding="4" cellspacing="0" border="0" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class="celdaFiltro" style="vertical-align: middle;">
      Acciones:<br>
      <?php
			if ($_SESSION['tipo'] <= 1) {
				echo("<a href='$enlbase_sm=editar_curso&id_curso=$id_curso' id='sgu_fancybox_small2' class='boton'>Editar</a> ");
			} else {
				echo("<a href='$enlbase_sm=curso_editar_horarios_salas&id_curso=$id_curso' id='sgu_fancybox_small2' class='boton'>Editar Salas y Horarios</a> ");
			}
			echo("<a href='$enlbase_sm=curso_crear_fusion&id_curso=$id_curso' id='sgu_fancybox_medium' class='boton'>Crear fusión</a> ");
			echo("<a href='$mod_ant' class='boton'>Volver</a> ");
      ?>
    </td>
    <td class="celdaFiltro" style="vertical-align: middle;">
      Tareas Académicas:<br>
      <?php
			if ($_SESSION['tipo'] == 0 || $curso[0]['cerrado'] == "f") {
				echo("<a href='$enlbase=calificaciones_ver_curso&id_curso=$id_curso' class='boton'>Calificar</a> ");
			}
			echo("<a href='$enlbase_sm=cursos_calendarizacion&id_curso=$id_curso' class='boton' id='sgu_fancybox'>Calendarización</a> ");
			echo("<a href='$enlbase_sm=cursos_libro_clases&id_curso=$id_curso' class='boton' id='sgu_fancybox'>Libro de Clases</a> ");
	  ?>
    </td>
    <td class="celdaFiltro" style="vertical-align: middle;">
      Imprimir:<br>
      <?php
			$enl_acta = "";
			if ($acta_imprimible == "t") { $enl_acta = "acta.php?id_curso=$id_curso"; }			
			
			if ($enl_acta <> "") {
				if ($curso[0]['cerrado'] == "t") {
					$msje = "Este curso ya tiene un acta oficial emitida, por lo que ahora se debería "
					      . "emitir un acta complementaria. No obstante puede volver a emitir un acta oficial.\\n\\n"
					      . "- Si desea un acta oficial nuevamente, pinche en [Aceptar]\\n\\n"
					      . "- Si desea un acta complementaria, pinche en [Cancelar]";
					$url_si       = $enl_acta;
					$url_no       = "$enl_acta&tipo=complementaria";
					$onClick = "if (confirm('$msje')) { location.href='$url_si'; } else { location.href='$url_no'; }";
					echo("<a href='#' target='_blank' onClick=\"$onClick\" class='boton'>Acta de Curso</a> ");
				} else {
					echo("<a href='$enl_acta' target='_blank' onClick=\"$onClick\" class='boton'>Acta de Curso</a> ");
				}
			} else {
				$msje = "No puede imprimir el Acta de éste curso. Aún no se calculan las notas finales y/o las situaciones finales de todos los alumnos del curso";
				echo("<a href='#' onClick=\"alert('$msje');\" class='boton'>Acta de Curso</a> ");
			}

			//actas postgrados
			if ($_SESSION['tipo'] == 0 && $regimen <> "PRE") {
				echo("<a href='acta_postgrado.php?id_curso=$id_curso' target='_blank' class='boton'>Acta de Curso Post-Grado/Título</a> ");
			}
		?>
    </td>
  </tr>
</table>
<table cellpadding="2" cellspacing="0" border="0" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class="celdaFiltro" style="vertical-align: middle;">
      Tareas Administrativas:<br>
      <?php			
			if ($_SESSION['tipo'] == 0 || $curso[0]['cerrado'] == "f") {
				echo("<a href='libro_de_clases.php?id_curso=$id_curso' target='_blank' class='boton'>Imprimir Libro de Clases</a> ");
			} else {
				$msje = "Este curso está cerrado, por lo tanto ya no puede obtener una Lista de Curso para el Libro de Clases";
				echo("<a href='#' onClick=\"alert('$msje');\" class='boton'>Imprimir Libro de Clases</a> ");
			}
			echo("<a href='$enlbase_sm=cursos_control_asistencia&id_curso=$id_curso' class='boton' id='sgu_fancybox_small'>Asistencia Docente</a> ");
		?>
    </td>
    <td class="celdaFiltro">
      <b>Imprimir Pruebas Solemnes:</b><br>
      <?php echo("<a href='pruebas_solemnes_tapa.php?id_curso=$id_curso&prueba=s1&token=$cod' class='boton'>Primera</a> "); ?>
      <?php echo("<a href='pruebas_solemnes_tapa.php?id_curso=$id_curso&prueba=s2&token=$cod' class='boton'>Segunda</a> "); ?>
      <?php echo("<a href='pruebas_solemnes_tapa.php?id_curso=$id_curso&prueba=rec&token=$cod' class='boton'>Recuperativa</a> "); ?>
    </td>
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr'>Nº Acta:</td>
    <td class='celdaValorAttr'><?php echo($id); ?></td>
    <td class='celdaNombreAttr'>Periodo:</td>
    <td class='celdaValorAttr'><?php echo($sem_num."-".$ano); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Asignatura:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($asignatura . " " . $prog_asig . $HTML_fusionadas); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Mallas:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($mallas); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($carrera); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Docente:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($profesor); ?> <?php echo($ficha_prof); ?></td>
  </tr>  <tr>
    <td class='celdaNombreAttr'>Codocente:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($codocente); ?> <?php echo($ficha_codocente); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Ayudantia:</td>
    <td class='celdaValorAttr'><?php echo($ayudantia); ?></td>
    <td class='celdaNombreAttr'>Tipo Clase:</td>
    <td class='celdaValorAttr'><?php echo($tipo_clase); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Classroom:</td>
    <td class='celdaValorAttr' ><?php echo($cod_google_classroom); ?></td>

	<td class='celdaNombreAttr' id="mostrar_moodle2" name="mostrar_moodle2">Moodle:</td>
    <td class='celdaValorAttr' id="mostrar_moodle3" name="mostrar_moodle3"><?php echo($cod_moodle); ?></td>

  </tr>
  <tr>
    <td class='celdaNombreAttr'>Horario {sala}:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($horario . $fec_ini_fin); ?></td>
  </tr>
<!--  <tr>
    <td class='celdaNombreAttr'>Horas programadas:</td>
    <td class='celdaValorAttr'><?php echo($feriados.$horas_programadas); ?></td>
    <td class='celdaNombreAttr'>Horas realizadas:</td>
    <td class='celdaValorAttr'><?php echo(total_horas_control_asist_2011($id)); ?></td>
  </tr> -->
  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: center'>Fechas de Pruebas:</td></tr>
  <tr>
    <td class='celdaValorAttr' colspan="4" style='text-align: center'><?php echo("<b>Solemne I:</b> $fec_sol1 <b>Solemne II:</b> $fec_sol2 <b>Recuperativa:</b> $fec_sol_recup"); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cupo:</td>
    <td class='celdaValorAttr'><?php echo($cupo); ?> alumno(a)s</td>
    <td class='celdaNombreAttr'>Estado:</td>
    <td class='celdaValorAttr'>
    <?php
      echo($estado."<br>");
      if ($estado == "Cerrado") {
		$recep_acta      = ($recep_acta == "t") ? "Recepcionada" : "<b>No recepcionada</b>";
		$recep_acta_comp = ($fecha_acta_comp <> "" && $recep_acta_comp == "t") ? "Recepcionada" : ($recep_acta_comp == "f") ? "<b>No recepcionada</b>" : "";
		echo("<sub>"
		    ."Fec. últ. acta oficial: $fecha_acta $recep_acta<br>"
		    ."Fec. últ. acta compl.: $fecha_acta_comp $recep_acta_comp<br>"
		    ."Emisor(a) última acta: $usuario_emisor"
		    ."</sub>");
	  }
    ?>
    </td>
  </tr>
  
  <tr>
    <td class='celdaNombreAttr'>Inscrito(a)s:</td>
    <td class='celdaValorAttr'><?php echo($cant_alumnos); ?> alumno(a)s</td>
    <td class='celdaNombreAttr'>Asistentes:</td>
    <td class='celdaValorAttr'><?php echo($cant_alumnos_asist); ?> alumno(a)s</td>
  </tr>
  <tr id="mostrar_moodle" name="mostrar_moodle">
		<td class='celdaNombreAttr'>Sincronización Moodle:</td>
		<td class='celdaValorAttr' colspan=3><?php echo($diferencias_moodle); ?></td>
  </tr>

</table>
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_curso" value="<?php echo($id_curso); ?>">
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="11" align="right">Ordenar por: <select name="ordenar_por" onChange="submitform();" class="filtro"><?php echo(select($ORDENAR_POR,$ordenar_por)); ?></select></td>
  </tr>

  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="3">
      Alumnos
      <?php echo("<a id='sgu_fancybox_small' href='$enlbase_sm=cursos_email_al&id_curso=$id_curso' class='botoncito'>Email personales</small></a> "); ?>
      <?php echo("<a id='sgu_fancybox_small' href='$enlbase_sm=cursos_email_al&id_curso=$id_curso&gsuite=Si' class='botoncito'>Email @alumni.umc.cl</small></a> "); ?>
    </td>
	<?php if ($_SESSION['tipo_usuario'] <= 2) { ?>
    <td class='tituloTabla' colspan="6">Calificaciones</td>
	<?php } ?>
    <td class='tituloTabla' rowspan="2">Fecha de<br>Inscripción</td>
    <td class='tituloTabla' rowspan="2">Asistencia</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>Nombre</td>
    <td class='tituloTabla'>Carrera</td>
	<?php if ($_SESSION['tipo_usuario'] <= 2) { ?>
    <td class='tituloTabla'>S1</td>
    <td class='tituloTabla'>NC</td>
    <td class='tituloTabla'>S2</td>
    <td class='tituloTabla'>Rec.</td>
    <td class='tituloTabla'>NF</td>
    <td class='tituloTabla'>Situación</td>	
	<?php } ?>
  </tr>
<?php
	$HTML_curso_alumnos = "";
	for ($x=0; $x<count($curso_alumnos); $x++) {
		extract($curso_alumnos[$x]);
		
		$enl = "$enlbase=ver_alumno&id_alumno=$id_alumno";
		$enlace = "<a class='enlitem' href='$enl'>";
		$js_onClick = "\"window.location='$enl';\"";
		
		$_azul = "color: #000099";
		$_rojo = "color: #ff0000";
		
		$estilo_s1 = $estilo_nc = $estilo_s2 = $estilo_rec = $estilo_nf = $estilo_sit = "color: #000000";

		if ($estado == "Moroso" && $semestre == $SEMESTRE && $ano == $ANO && $_SESSION['tipo'] > 0) {
			$s1 = $nc = $s2 = $rec = $nf = $situacion = "N/D";
		}
		
		if ($s1>=1 && $s1<4) { $estilo_s1 = $_rojo; } elseif ($s1>=4) { $estilo_s1 = $_azul; }   

		if ($nc>=1 && $nc<4) { $estilo_nc = $_rojo; } elseif ($nc>=4) { $estilo_nc = $_azul; }   

		if ($s2>=1 && $s2<4) { $estilo_s2 = $_rojo; } elseif ($s2>=4) { $estilo_s2 = $_azul; }   

		if ($rec>=1 && $rec<4) { $estilo_rec = $_rojo; } elseif ($rec>=4) { $estilo_rec = $_azul; }   

		if ($nf>=1 && $nf<4) { $estilo_nf = $_rojo; } elseif ($nf>=4) { $estilo_nf = $_azul; }   

		if ($situacion == "Reprobado") { $estilo_sit = $_rojo; } elseif ($situacion == "Aprobado") { $estilo_sit = $_azul; }

		if ($estado == "Moroso") { $situacion = "(M) ".$situacion; }
		
		$HTML_curso_alumno .= "<tr class='filaTabla' onClick=$js_onClick>\n"
		                   .  "  <td class='textoTabla'> $id_alumno</td>\n"
		                   .  "  <td class='textoTabla'><a class='enlitem' href='$enl'>$nombre_alumno</a></td>\n"
		                   .  "  <td class='textoTabla' align='center'> $carrera ($semestre_cohorte-$cohorte)</td>\n";

		if ($_SESSION['tipo_usuario'] <= 2) {
			$HTML_curso_alumno .= "  <td class='textoTabla' style='$estilo_s1'> $s1</td>\n"
			                   .  "  <td class='textoTabla' style='$estilo_nc'> $nc</td>\n"
			                   .  "  <td class='textoTabla' style='$estilo_s2'> $s2</td>\n"
			                   .  "  <td class='textoTabla' style='$estilo_rec'> $rec</td>\n"
			                   .  "  <td class='textoTabla' style='$estilo_nf'> $nf</td>\n"
			                   .  "  <td class='textoTabla' style='$estilo_sit'> $situacion</td>\n";
		}

		$HTML_curso_alumno .= "  <td class='textoTabla' align='right'> $fecha_inscripcion</td>\n"
		                   .  "  <td class='textoTabla' align='center'> $asistencia</td>\n"
		                   .  "</tr>\n";
		                   
	};
	
	$HTML_curso_alumno .= "  <tr>\n"
	                   .  "    <td class='textoTabla' align='right' colspan='7'>Promedio Notas Finales:</td>\n"
	                   .  "    <td class='textoTabla' colspan='4'>&nbsp;$promedio_nf</td>\n"
	                   .  "  </tr>\n";
	echo($HTML_curso_alumno);
?>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->

<script type="text/javascript">
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
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 800,
		'height'			: 480,
		'maxHeight'			: 480,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small2").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 800,
		'height'			: 700,
		'maxHeight'			: 700,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoSize'			: false,
		'fitToView'			: true,
		'autoDimensions'	: false,
		'closeBtn'	        : true,
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
	const queryString = window.location.search;
	const urlParams = new URLSearchParams(queryString);
	console.log(queryString);
	var moodleActivado = urlParams.getAll('moodleActivado');
	if (moodleActivado == 'SI') {
		$("#mostrar_moodle").show();	
		$("#mostrar_moodle2").show();	
		$("#mostrar_moodle3").show();	
		//alert("debe mostrar");
	} else {
		$("#mostrar_moodle").hide();
		$("#mostrar_moodle2").hide();	
		$("#mostrar_moodle3").hide();	
		//alert("debe esconder");
	}
});


</script>
