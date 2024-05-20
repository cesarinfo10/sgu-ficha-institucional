<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

if (!is_numeric($_REQUEST['id_prog_curso'])) {
	echo(js("window.location='$enlbase=';"));
	exit;
} else {
	$id_prog_curso = $_REQUEST['id_prog_curso'];
}

$SQL_prog_curso = "SELECT vpc.*,pc.fecha AS fecha_creacion
                   FROM vista_prog_cursos AS vpc
                   LEFT JOIN prog_cursos AS pc ON pc.id=vpc.id
                   WHERE vpc.id=$id_prog_curso";
$prog_curso = consulta_sql($SQL_prog_curso);
if (count($prog_curso) == 0) {
	echo(js("window.location='$enlbase=';"));
	exit;
}
extract($prog_curso[0],EXTR_PREFIX_ALL,"pc");

$SQL_prog_asig = "SELECT dm.id_prog_asig,pa.horas_semanal AS hrs_sem,char_comma_sum(c.alias||m.ano::text) AS mallas
                  FROM detalle_mallas AS dm
                  LEFT JOIN mallas AS m ON m.id=dm.id_malla
                  LEFT JOIN prog_asig AS pa ON pa.id=dm.id_prog_asig
                  LEFT JOIN carreras AS c ON c.id=m.id_carrera
                  WHERE c.id_escuela=$pc_id_escuela
                  GROUP BY id_prog_asig,horas_semanal";

$SQL_pc_det = "SELECT pcd.id,pcd.id_prog_asig,vpa.cod_asignatura,vpa.asignatura,seccion,vp.nombre AS profesor,
                      vp.grado_academico,al_proyectado,mpa.mallas,pcd.id_pa_fusion,pcd.id_profesor,
                      coalesce(u.categorizacion,'** Sin categorización **') AS categorización,
                      vpa2.ano||'/'||vpa2.cod_asignatura||' '||vpa2.asignatura AS asignatura_fusion,pcd.modalidad,
                      horas_semestrales||'/'||(hrs_sem*18) AS hrs_semestral,comentarios,fecha_ingreso,vobo_vra,vobo_vraf,
                      CASE pcd.tipo WHEN 'r' THEN 'Regular' WHEN 't' THEN 'Tutorial' WHEN 'm' THEN 'Modular' END AS tipo_curso,
                      CASE pcd.tipo_vra WHEN 'r' THEN 'Regular' WHEN 't' THEN 'Tutorial' WHEN 'm' THEN 'Modular' END AS tipo_curso_vra,
                      CASE WHEN vp.grado_academico IN ('Magister','Doctor') THEN 7200 ELSE 6000 END*horas_semestrales AS rem_sem                      
               FROM prog_cursos_detalle AS pcd
               LEFT JOIN vista_prog_asig AS vpa ON vpa.id=pcd.id_prog_asig
               LEFT JOIN vista_prog_asig AS vpa2 ON vpa2.id=pcd.id_pa_fusion
               LEFT JOIN vista_profesores AS vp ON vp.id=pcd.id_profesor
               LEFT JOIN usuarios AS u ON u.id=pcd.id_profesor
               LEFT JOIN ($SQL_prog_asig) AS mpa ON mpa.id_prog_asig=pcd.id_prog_asig
               WHERE id_prog_curso=$id_prog_curso
               ORDER BY seccion,cod_asignatura";
$pc_det = consulta_sql($SQL_pc_det);

$SQL_genero_docentes_prop = "SELECT CASE sexo WHEN 'f' THEN 'Mujeres' ELSE 'Hombres' END AS sexo,count(id) AS cantidad_docentes
                             FROM usuarios
                             WHERE id IN (SELECT id_profesor FROM prog_cursos_detalle WHERE id_profesor IS NOT NULL AND id_prog_curso=$id_prog_curso)
                             GROUP BY sexo
                             ORDER BY sexo DESC";
$genero_docentes_prop     = consulta_sql($SQL_genero_docentes_prop);
//var_dump($genero_docentes_prop);

$SQL_genero_docentes_vobo_vra = "SELECT CASE sexo WHEN 'f' THEN 'Mujeres' ELSE 'Hombres' END AS sexo,count(id) AS cantidad_docentes
                                 FROM usuarios
                                 WHERE id IN (SELECT id_profesor FROM prog_cursos_detalle WHERE id_profesor IS NOT NULL AND id_prog_curso=$id_prog_curso AND vobo_vra)
                                 GROUP BY sexo
                                 ORDER BY sexo DESC";
$genero_docentes_vobo_vra     = consulta_sql($SQL_genero_docentes_vobo_vra);

if (count($genero_docentes_prop) > 0) {
  $tot_docentes_prop = array_sum(array_column($genero_docentes_prop,"cantidad_docentes"));
  $genero_docentes_prop = "{$genero_docentes_prop[0]['sexo']}: ".round($genero_docentes_prop[0]['cantidad_docentes']*100/$tot_docentes_prop)."%, "
                        . "{$genero_docentes_prop[1]['sexo']}: ".round($genero_docentes_prop[1]['cantidad_docentes']*100/$tot_docentes_prop)."%";
} else { $genero_docentes_prop = ""; }

if (count($genero_docentes_vobo_vra) > 0) {
  $tot_docentes_vobo_vra = array_sum(array_column($genero_docentes_vobo_vra,"cantidad_docentes"));
  $genero_docentes_vobo_vra = "{$genero_docentes_vobo_vra[0]['sexo']}: ".round($genero_docentes_vobo_vra[0]['cantidad_docentes']*100/$tot_docentes_vobo_vra)."%, "
                            . "{$genero_docentes_vobo_vra[1]['sexo']}: ".round($genero_docentes_vobo_vra[1]['cantidad_docentes']*100/$tot_docentes_vobo_vra)."%";
} else { $genero_docentes_vobo_vra = ""; }

$HTML_pc_det = $HTML_pc_det2 = $HTML_pc_det3 = "";
if (count($pc_det) > 0) {	
	for ($x=0;$x<count($pc_det);$x++) {
		extract($pc_det[$x]);
		
		$HTML = "";

		$asignatura_fusion = "<sup><br>".str_repeat("&nbsp;",8)."Fusionada con: $asignatura_fusion</sup>";
		
		$title = "header=[Comentarios] fade=[on] body=[$comentarios]";
		
		$asignatura  = trim($cod_asignatura)."-".$seccion." ".$asignatura;
		$asignatura  = !empty($comentarios)  ? "<span style='color: #ff0000' title='$title'>$asignatura</span>" : $asignatura;
		$asignatura .= !empty($id_pa_fusion) ? $asignatura_fusion : "";

		if ($vobo_vra == "f" && $vobo_vraf == "f") {
			
			$enl = "$enlbase_sm=prog_cursos_editar_curso&id_pc_det=$id&id_prog_curso=$id_prog_curso&mod_anterior=$modulo";
      $asignatura = "<a id='sgu_fancybox' href='$enl' class='enlaces'>$asignatura</a>";

      $boton_eliminar = "<a href='$enlbase=prog_cursos_eliminar_curso&id_pc_det=$id&id_prog_curso=$id_prog_curso' class='enlaces'><big style='color: red'> ✘ </big></a>";
			$asignatura = "$boton_eliminar $asignatura";

    }
    			 
		$rem_sem    = "$".number_format($rem_sem,0,',','.');

		$profesor        = empty($profesor) ? "<span style='color: #ff0000'>*** No definido ***</span>" : $profesor;
		$grado_academico = empty($grado_academico) ? "<span style='color: #ff0000'>---</span>" : $grado_academico;
		
		if (strtotime(substr($fecha_ingreso,0,10)) >= strtotime(substr($pc_fecha_creacion,0,10))) {
			$profesor = "<span style='color: #ff0000'>$profesor</span>";
		}

    if ($id_profesor > 0) {
		  $enl_profe = "$enlbase_sm=ver_profesor&id_profesor=$id_profesor";
		  $profesor = "<a id='sgu_fancybox' href='$enl_profe' class='enlaces'>$profesor</a>";
    }

		if (!empty($tipo_curso_vra) && $tipo_curso <> $tipo_curso_vra) {
			$tipo_curso = "<span style='text-decoration: line-through'>$tipo_curso</span>";
		}

		$tipo_curso     = "$tipo_curso $tipo_curso_vra";				
				
		$HTML .= "<tr class='filaTabla'>"
		      .  "  <td class='textoTabla' nowrap>$asignatura</td>"
		      .  "  <td class='textoTabla' align='center'>$mallas</td>"
		      .  "  <td class='textoTabla' nowrap>$profesor</td>"
		      .  "  <td class='textoTabla' nowrap>$categorización</td>"
		      .  "  <td class='textoTabla' align='center' nowrap>$al_proyectado</td>"
		      .  "  <td class='textoTabla' nowrap>$tipo_curso</td>"
		      .  "  <td class='textoTabla' nowrap>$modalidad</td>"
		      .  "  <td class='textoTabla' align='center' nowrap>$hrs_semestral</td>"
//		      .  "  <td class='textoTabla' align='right' nowrap>$rem_sem</td>"
		      .  "</tr>";

		if ($vobo_vra == "f" && $vobo_vraf == "f") {
			$HTML_pc_det  .= $HTML;
		} elseif ($vobo_vra == "t" && $vobo_vraf == "f") {
			$HTML_pc_det2 .= $HTML;
		} elseif ($vobo_vra == "t" && $vobo_vraf == "t") {
			$HTML_pc_det3 .= $HTML;
		}

	}
}

$HTML_cabecera = " <tr class='filaTituloTabla'>
                     <td class='tituloTabla'>Asignatura</td>
                     <td class='tituloTabla'>Malla(s)</td>
                     <td class='tituloTabla'>Docente</td>
                     <td class='tituloTabla'>Categorización</td>
                     <td class='tituloTabla'>Al.<br>Proy.</td>
                     <td class='tituloTabla'>Tipo</td>
                     <td class='tituloTabla'>Modalidad</td>
                     <td class='tituloTabla'>Hrs.<br>Sem.</td>
                     <!-- <td class='tituloTabla'>Rem.<br>Sem.</td> -->
                   </tr>";
                   
$HTML = "<tr class='filaTabla'>"
      . "  <td class='textoTabla' colspan='9' align='center'><br>***<br><br></td>"
      . "</tr>";

$HTML_pc_det  = empty($HTML_pc_det)  ? $HTML : $HTML_pc_det;
$HTML_pc_det2 = empty($HTML_pc_det2) ? $HTML : $HTML_pc_det2;
$HTML_pc_det3 = empty($HTML_pc_det3) ? $HTML : $HTML_pc_det3;
?>

<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>

<div class="texto" style="margin-top: 5px">
  <a href='<?php echo("$enlbase=prog_cursos_informar&id_prog_curso=$pc_id"); ?>' class='boton'>Informar</a>
  <a href='<?php echo($mod_ant); ?>' class='boton'>Volver</a>
</div>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr>
    <td class="celdaNombreAttr" colspan="4"><center>Antecedentes de la Prog. de Cursos</center></td>  
  </tr>
  <tr>
    <td class="celdaNombreAttr">Escuela:</td>
    <td class="celdaValorAttr"><?php echo($pc_escuela); ?></td>
    <td class="celdaNombreAttr">Periodo:</td>
    <td class="celdaValorAttr"><?php echo($pc_periodo); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Creador:</td>
    <td class="celdaValorAttr" colspan="3"><?php echo($pc_creador); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Fec. Creación:</td>
    <td class="celdaValorAttr"><?php echo($pc_fecha); ?></td>
    <td class="celdaNombreAttr">Fec. Informa:</td>
    <td class="celdaValorAttr"><?php echo($pc_fecha_mod); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">
    <td class="celdaNombreAttr"><center>Cantidad de Cursos</center></td>
    <!-- <td class="celdaNombreAttr" colspan="2"><center>Costo Semestral</center></td>  -->
    <td class="celdaNombreAttr" colspan="2"><center>Proporción de Género</center></td>  
  </tr>
  <tr>
    <td class="celdaNombreAttr">Propuesto:</td>
    <td class="celdaValorAttr"><?php echo($pc_cant_cursos); ?></td>
    <!-- <td class="celdaValorAttr" colspan="2">$<?php echo(number_format($pc_costo_semestral,0,',','.')); ?></td> -->
    <td class="celdaValorAttr" colspan="2"><?php echo($genero_docentes_prop); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Visado VRA:</td>
    <td class="celdaValorAttr"><?php echo($pc_cant_cursos_vra); ?></td>    
    <!-- <td class="celdaValorAttr" colspan="2">$<?php echo(number_format($pc_costo_semestral_vra,0,',','.')); ?></td> -->
    <td class="celdaValorAttr" colspan="2"><?php echo($genero_docentes_vobo_vra); ?></td>
  </tr>
<!--
  <tr>
    <td class="celdaNombreAttr">Ofertado:</td>
    <td class="celdaValorAttr"><?php echo($pc_cant_cursos_ofertado); ?></td>    
    <td class="celdaValorAttr" colspan="2">$<?php echo(number_format($pc_costo_semestral_ofertado,0,',','.')); ?></td>
  </tr>
-->
</table>

<div class="texto"  style="margin-top: 5px">
  <a id="sgu_fancybox" href='<?php echo("$enlbase_sm=prog_cursos_agregar_curso&id_prog_curso=$pc_id"); ?>' class='boton'>Agregar un curso</a>
  <a id="sgu_fancybox" href='<?php echo("$enlbase_sm=prog_cursos_agregar_cursos&id_prog_curso=$pc_id"); ?>' class='boton'>Agregar grupo de cursos</a>
  <a id="sgu_fancybox_big" href='<?php echo("$enlbase_sm=prog_cursos_editar&id_prog_curso=$pc_id"); ?>' class='boton'>Editar cursos</a>
  <a id="sgu_fancybox" href='<?php echo("$enlbase_sm=crear_profesor&id_prog_curso=$id"); ?>' class='boton'>Agregar un Profesor(a)</a>
  <a id="sgu_fancybox_big" href='<?php echo("$enlbase_sm=prog_cursos_horarios&id_prog_curso=$pc_id"); ?>' class='boton'>Horarios</a>
</div>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="8">Cursos Propuestos</td>
  </tr>
  <?php echo($HTML_cabecera); ?>
  <?php echo($HTML_pc_det); ?>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="8">Cursos Visados VRA</td>
  </tr>
  <?php echo($HTML_cabecera); ?>
  <?php echo($HTML_pc_det2); ?>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="8">Cursos Ofertados</td>
  </tr>
  <?php echo($HTML_cabecera); ?>
  <?php echo($HTML_pc_det3); ?>
</table>

<!-- Fin: <?php echo($modulo); ?> -->
<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'		: false,
		'autoDimensions': false,
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic',
		'width'			: 1200,
		'maxHeight'		: 9999,
		'afterClose'	: function () { location.reload(true); },
		'type'			: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_big").fancybox({
		'autoScale'		: false,
		'autoDimensions': false,
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic',
		'width'			: 1300,
		'maxHeight'		: 9999,
		'afterClose'	: function () { location.reload(true); },
		'type'			: 'iframe'
	});
});
</script>