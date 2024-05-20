	<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$puedeEliminar = $_REQUEST['puedeEliminar'];


$id_curso    = $_REQUEST['id_curso']; 
$ordenar_por = $_REQUEST['ordenar_por'];

if (empty($ordenar_por)) { $ordenar_por = "nombre_alumno"; }

if (!is_numeric($id_curso)) {
	echo(js("location.href='principal.php?modulo=gestion_cursos';"));
	exit;
}

$mod_ant = $_SESSION['enlace_volver'];

if ($puedeEliminar <> "" ) {
	//echo("DEBE ELIMINAR CURSO id_curso=".$id_curso);
	$SQL_borrar = "
	delete from cursos where id = $id_curso";

	//$SQL_borrar = "delete from cursos where id = $id_curso";
	if (consulta_dml($SQL_borrar) == 1) {
		echo(msje_js("Curso eliminado."));
		echo(js("location='$mod_ant';"));
	} else {
		echo(msje_js("Curso no se encuentra completamente vacío para eliminar."));
	}      

}


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
	
//$prog_asig = "<a class='boton' id='sgu_fancybox_medium' href='$enlbase_sm=ver_prog_asig&id_prog_asig=$id_prog_asig'><small>Ver programa</small></a>";
//$ficha_prof = "<a class='boton' id='sgu_fancybox_medium' href='$enlbase_sm=ver_profesor&id_profesor=$id_profesor'><small>Ver ficha</small></a>";

$ORDENAR_POR = array(array('id' => "nombre_alumno", 'nombre' => "Nombre Estudiante"),
                     array('id' => "vca.fecha_mod", 'nombre' => "Fecha de Inscripción"));
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
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
  </tr>  
  
</table>
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_curso" value="<?php echo($id_curso); ?>">
<table cellpadding="4" cellspacing="0" border="0" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class="celdaFiltro" style="vertical-align: middle;">
      <?php
			echo("<a href='javascript:fEliminarCurso($id_curso)' id='eliminarCurso' class='boton'>Eliminar este curso</a> ");
			echo("<a href='$mod_ant' class='boton'>Volver</a> ");
      ?>
    </td>
</form>


<script type="text/javascript">
function fEliminarCurso(id_curso) {
//	alert("id_curso = " + id_curso);
	var txt;
	var r = confirm("Seguro(a) de eliminar el curso?");
	if (r == true) {
		  var pSaltar = "/sgu/principal.php?modulo=eliminar_curso&id_curso="+id_curso+"&puedeEliminar=SI";
          pSaltar = "http://" + window.location.hostname + ":" + window.location.port + pSaltar;
          window.location.href = pSaltar;
	} else {
//	txt = "You pressed Cancel!";
	}	
}
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
