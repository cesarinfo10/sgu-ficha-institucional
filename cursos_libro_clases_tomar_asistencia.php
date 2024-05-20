<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_curso     = $_REQUEST['id_curso'];
$id_sesion    = $_REQUEST['id_sesion'];
$hora_fin     = $_REQUEST['hora_fin'];

if ($hora_fin == "") { $hora_fin = date("H:i"); }


if ($id_curso == "" || $id_sesion == "") {
	echo(js("parent.jQuery.fancybox.close();"));
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

	if ($estado == "Cerrado") {
		echo(msje_js("AVISO: Este curso se encuentra CERRADO y no es posible alterar el contenido. Contáctese con su Escuela para resolver."));
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
  
  
  if ($_REQUEST['guardar'] == "Guardar") {

    $ids_ca = $_REQUEST['id_ca'];	
    $SQL_upd_ca = "";
    foreach($ids_ca AS $id_ca => $presente) {
        $SQL_upd_ca .= "UPDATE ca_asistencia SET presente='$presente' WHERE id_ca = $id_ca AND id_sesion=$id_sesion;";
    }

    $ids_cat = $_REQUEST['id_cat'];	
    $SQL_upd_cat = "";
    foreach($ids_cat AS $id_cat => $presente) {
        $SQL_upd_cat .= "UPDATE ca_temp_asist SET presente='$presente' WHERE id_ca_temporal = $id_cat AND id_sesion=$id_sesion;";
    }

    if (consulta_dml($SQL_upd_ca) > 0) {
      consulta_dml($SQL_upd_cat);
      $materia      = $_REQUEST['materia'];
      $metodologias = implode(",",$_REQUEST['metodologias']);
      $av_utiliza   = ($_REQUEST['av_utiliza'] <> "") ? $_REQUEST['av_utiliza'] : "null";
      $av_tipo      = ($_REQUEST['av_tipo'] <> "") ? $_REQUEST['av_tipo'] : "null";
      $av_titulo    = ($_REQUEST['av_titulo'] <> "") ? $_REQUEST['av_titulo'] : "null";
      $av_url       = ($_REQUEST['av_url'] <> "") ? $_REQUEST['av_url'] : "null";

      $av = arr2sqlupdate($_REQUEST,array('av_utiliza','av_tipo','av_titulo','av_url'));

      $SQL_upd_sesion = "UPDATE cursos_sesiones 
                          SET metodologias='{".$metodologias."}',
                              materia='$materia',
                              $av
                          WHERE id=$id_sesion AND id_curso=$id_curso";
      consulta_dml($SQL_upd_sesion);
      echo(msje_js("Se ha guardado la asistencia y los contenidos de la clase"));
    }
  }
   
  if ($_REQUEST['terminar'] == "TERMINAR CLASE") {
    if (consulta_dml("UPDATE cursos_sesiones SET hora_fin=now()::time WHERE id=$id_sesion AND id_curso=$id_curso") > 0) {
      echo(msje_js("Se ha cerrado la sesión de clases"));
      echo(js("location='$enlbase_sm=cursos_libro_clases&id_curso=$id_curso';"));
      exit;
    }
  }

  $SQL_sesion  = "SELECT to_char(fecha,'DD/MM') AS fecha,materia,metodologias,fecha AS fec_sesion,
                         to_char(hora_inicio,'HH24:MI') AS hora_inicio,to_char(hora_fin,'HH24:MI') AS hora_fin,
                         av_utiliza,av_tipo,av_titulo,av_url
                  FROM cursos_sesiones WHERE id=$id_sesion AND id_curso=$id_curso";
  $sesion = consulta_sql($SQL_sesion);

  $METODOLOGIAS = consulta_sql("SELECT id,nombre FROM vista_metod_clases");

  $HTML_metodologias = "";
  $metodologias = explode(",",str_replace("\"","",trim($sesion[0]['metodologias'],"{}")));
  for ($x=0;$x<count($METODOLOGIAS);$x++) {
    $checked = "";
    if (in_array($METODOLOGIAS[$x]['id'],$metodologias)) { $checked = "checked"; }
    $HTML_metodologias .= "<input type='checkbox' name='metodologias[]' value='{$METODOLOGIAS[$x]['id']}' id='{$METODOLOGIAS[$x]['id']}' $checked> "
                       .  "<label for='{$METODOLOGIAS[$x]['id']}'>{$METODOLOGIAS[$x]['nombre']}</label><br>\n";
  }

  $AV_TIPOS = consulta_sql("SELECT id,nombre FROM vista_av_tipo");

	$SQL_alumnos_curso = "SELECT vca.id_ca,id_alumno,va.rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre_alumno,
	                             to_char(vca.fecha_mod,'DD-tmMon-YYYY') AS fecha_mod,va.carrera||'-'||a.jornada AS carrera,
	                             va.cohorte,va.semestre_cohorte,va.estado,s1,nc,s2,prom,der_recup,
	                             recup,nf,situacion,caa.presente
	                      FROM vista_cursos_alumnos AS vca
	                      LEFT JOIN vista_alumnos   AS va  ON va.id=id_alumno
	                      LEFT JOIN alumnos         AS a   ON a.id=id_alumno
                        LEFT JOIN ca_asistencia   AS caa ON (caa.id_ca=vca.id_ca AND caa.id_sesion=$id_sesion)
	                      WHERE id_curso IN ($ids_cursos)
	                      ORDER BY nombre_alumno;";
	$alumnos_curso = consulta_sql($SQL_alumnos_curso);
	
	if (count($alumnos_curso) > 0) {
		$SQL_als_ins_post = "SELECT id AS id_ca FROM cargas_academicas WHERE id_curso IN ($ids_cursos) EXCEPT ALL SELECT id_ca FROM ca_asistencia WHERE id_sesion=$id_sesion";
		//echo($SQL_als_ins_post);
		$als_ins_post = consulta_sql($SQL_als_ins_post);
		if (count($als_ins_post) > 0) {
			echo(msje_js("ATENCIÓN: Luego de esta clase, se ha(n) incorporado ".count($als_ins_post)." estudiante(s), "
			            ."debido a que inscribieron tardíamente esta asignatura.\\n\\n"
			            ."Se procede a normalizar listado para la toma de asistencia de esta sesión. Ahora podrá registrar dicha asistencia."));
			consulta_dml("INSERT INTO ca_asistencia (id_ca,id_sesion) SELECT id_ca,$id_sesion FROM ($SQL_als_ins_post) AS als_ins_post");
			$alumnos_curso = consulta_sql($SQL_alumnos_curso);
		}
		$campos_validar = "";
		for ($x=0;$x<count($alumnos_curso);$x++) {
			$campos_validar[$x] = "'id_ca[{$alumnos_curso[$x]['id_ca']}]'";
		}
		$campos_validar = implode(",",$campos_validar);
	}

	$SQL_alumnos_provisorios = "SELECT vcat.id AS id_ca,vcat.rut,vcat.nombre AS nombre_alumno,caat.presente
                                FROM vista_ca_temporal  AS vcat
                                LEFT JOIN ca_temp_asist AS caat ON (caat.id_ca_temporal=vcat.id AND caat.id_sesion=$id_sesion)
                                WHERE id_curso IN ($ids_cursos)
                                ORDER BY nombre_alumno;";
	$alumnos_provisorios = consulta_sql($SQL_alumnos_provisorios);
	if (count($alumnos_provisorios) > 0) {
		$SQL_alsprov_ins_post = "SELECT id AS id_ca_temporal FROM vista_ca_temporal WHERE id_curso IN ($ids_cursos) EXCEPT ALL SELECT id_ca_temporal FROM ca_temp_asist WHERE id_sesion=$id_sesion";
		//echo($SQL_als_ins_post);
		$alsprov_ins_post = consulta_sql($SQL_alsprov_ins_post);
		if (count($alsprov_ins_post) > 0) {
			echo(msje_js("ATENCIÓN: Luego de esta clase, se ha(n) incorporado ".count($alsprov_ins_post)." estudiante(s) a la lista provisoria.\\n\\n"
			            ."Ahora podrá registrar dicha asistencia."));
			consulta_dml("INSERT INTO ca_temp_asist (id_ca_temporal,id_sesion) SELECT id_ca_temporal,$id_sesion FROM ($SQL_alsprov_ins_post) AS alsprov_ins_post");
			$alumnos_provisorios = consulta_sql($SQL_alumnos_provisorios);
		}
	}

	$asiste = $noasiste = 0;
	$HTML_alumnos_curso = "";
	for ($x=0; $x<count($alumnos_curso); $x++) {
		extract($alumnos_curso[$x]);
		
		$disabled = "";
		if ($situacion == "Suspendido" || $situacion == "Abandono" || $situacion == "Retirado") {
			$disabled = "readonly";
			$valor    = "NSP";
		}

    $asiste_checked = $noasiste_checked = "";

	$fila_destacada = "";
    if ($presente == "t") { $asiste_checked = "checked"; $asiste++; }
    if ($presente == "f") { $noasiste_checked = "checked"; $noasiste++; }
    if ($presente == "")  { $fila_destacada = "bgcolor='#FFFF00'"; }

	$HTML_alumnos_curso .= "<tr class='filaTabla' id='tr_$id_ca' $fila_destacada>\n"
	                    . "  <td class='textoTabla' style='vertical-align: middle; text-align: right'>$id_alumno</td>\n"
	                    . "  <td class='textoTabla' style='vertical-align: middle;'>$nombre_alumno</td>\n"
	                    . "  <td class='textoTabla' align='center'>"
                        . "    <input type='radio' name='id_ca[$id_ca]' onClick=\"fila_destacada('tr_$id_ca');\" id='asiste_id_ca[$id_ca]' style='height: 15px; width: 15px' value='t' required $asiste_checked>  "
                        . "    <label for='asiste_id_ca[$id_ca]'><span style='color: green'><b><big><big> ✓ </big></big></b></span></label> &nbsp;&nbsp;"
                        . "    <input type='radio' name='id_ca[$id_ca]' onClick=\"fila_destacada('tr_$id_ca');\" id='noasiste_id_ca[$id_ca]' style='height: 15px; width: 15px' value='f' required $noasiste_checked>  "
                        . "    <label for='noasiste_id_ca[$id_ca]'><span style='color: red'><b><big><big> ✗ </big></big></b></span></label> "
	                    . "  </td>\n"
	                    . "</tr>\n";
	}
	$asiste_t = $noasiste_t = 0;
	$HTML_alumnos_provisorios = "";
	for ($x=0; $x<count($alumnos_provisorios); $x++) {
		extract($alumnos_provisorios[$x]);
		
		$asiste_checked = $noasiste_checked = "";

		$fila_destacada = "";
		if ($presente == "t") { $asiste_checked = "checked"; $asiste_t++; }
		if ($presente == "f") { $noasiste_checked = "checked"; $noasiste_t++; }
		if ($presente == "")  { $fila_destacada = "bgcolor='#FFFF00'"; }

		$HTML_alumnos_provisorios .= "<tr class='filaTabla' $fila_destacada>\n"
		                          . "  <td class='textoTabla' align='right'></td>\n"
		                          . "  <td class='textoTabla' style='vertical-align: middle;'>$nombre_alumno</td>\n"
		                          . "  <td class='textoTabla' align='center'>"
                              . "    <input type='radio' name='id_cat[$id_ca]' id='asiste_id_cat[$id_ca]' style='height: 15px; width: 15px' value='t' required $asiste_checked>  "
                              . "    <label for='asiste_id_cat[$id_ca]'><span style='color: green'><b><big><big> ✓ </big></big></b></span></label> &nbsp;&nbsp;"
                              . "    <input type='radio' name='id_cat[$id_ca]' id='noasiste_id_cat[$id_ca]' style='height: 15px; width: 15px' value='f' required $noasiste_checked>  "
                              . "    <label for='noasiste_id_cat[$id_ca]'><span style='color: red'><b><big><big> ✗ </big></big></b></span></label> "
		                          . "  </td>\n"
		                          . "</tr>\n";
  }
  if (count($alumnos_provisorios)==0) { 
    $HTML_alumnos_provisorios = "<tr class='filaTabla'><td class='textoTabla' align='center' colspan='3'>** Sin registro **</td></tr>"; 
  }

  $boton_termina = "<input type='submit' name='terminar' value='TERMINAR CLASE' disabled>";
  if ($asiste+$noasiste==$cant_alumnos) { 
    $boton_termina = "<input type='submit' name='terminar' value='TERMINAR CLASE'>";
    if ($sesion[0]['hora_fin'] <> "") {
      $boton_termina = "<input type='submit' name='terminar' value='** Clase terminada **' disabled>";
    }
  }
}

?>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_curso" value="<?php echo($id_curso); ?>">
<input type="hidden" name="id_sesion" value="<?php echo($id_sesion); ?>">

<div class="texto" style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Guardar">  
  <input type="button" value="Cerrar" onClick="location='<?php echo("$enlbase_sm=cursos_libro_clases&id_curso=$id_curso"); ?>';">
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <?php if ($_REQUEST['guardar'] == "Guardar") { ?>
  <a href="<?php echo("$enlbase_sm=cursos_libro_clases_agregar_provisorio&id_curso=$id_curso&id_sesion=$id_sesion"); ?>" class="boton" id='sgu_fancybox_small'>+ Añadir estudiante provisorio</a>
  <?php } ?>
</div>
<table>
<tr>
<td>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px' width="auto">
  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: center'>Antecedentes del Curso</td></tr>
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
    <td class='celdaNombreAttr'>Horario:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($horario); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: center'>Antecedentes de la sesión o clase</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><input name="fecha" type="date" class="botoncito" value="<?php echo($sesion[0]['fec_sesion']); ?>" readonly></td>
    <td class='celdaNombreAttr' nowrap>Hora de Inicio:</td>
    <td class='celdaValorAttr'><input name="hora_inicio" type="time" value="<?php echo($sesion[0]['hora_inicio']); ?>" class="botoncito" readonly></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="3">Hora de Término:</td>
    <td class='celdaValorAttr' nowrap>
      <input name="hora_fin" type="time" value="<?php echo($sesion[0]['hora_fin']); ?>" class="botoncito" readonly>
      <?php echo($boton_termina); ?>
    </td>
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px' width="100%">
  <tr class='filaTituloTabla'><td class='tituloTabla' colspan='3'>Alumno(a)s Inscrito(a)s</td></tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>Nombre alumno(a)</td>
    <td class='tituloTabla'><?php echo($sesion[0]['fecha']); ?></td>
  </tr> 
  <?php echo($HTML_alumnos_curso); ?>
  <tr class='filaTituloTabla'><td class='tituloTabla' colspan='3'>Lista Provisoria</td></tr>
  <?php echo($HTML_alumnos_provisorios); ?>  
  <tr class='filaTabla'>
    <td class='textoTabla' colspan='2'>&nbsp;</td>
    <td class='celdaValorAttr' style='text-align: center'><input type="submit" name="guardar" value="Guardar"></td>
  </tr>
</table>
</td>
<td valign="top">
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: left'>Materia(s):</td></tr>
  <tr><td class='celdaValorAttr' colspan="4"><textarea name="materia" cols="300" rows="5" class="general" required><?php echo($sesion[0]['materia']); ?></textarea></td></tr>
  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: left'>Metodología(s):</td></tr>
  <tr>
    <td class='celdaValorAttr' colspan="4"><div style="column-count: 2"><?php echo($HTML_metodologias); ?></div></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: center'>Audiovisuales</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Utilizó?</td>
    <td class='celdaValorAttr'><select name="av_utiliza"  class="filtro" onChange="actualiza_av(this.value);" required><option value="">-- Seleccione --<?php echo(select($sino,$sesion[0]['av_utiliza'])); ?></select></td>
    <td class='celdaNombreAttr'>Tipo:</td>
    <td class='celdaValorAttr'><select name="av_tipo" class="filtro" disabled><option>-- Seleccione --<?php echo(select($AV_TIPOS,$sesion[0]['av_tipo'])); ?></select></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Título:</td>
    <td class='celdaValorAttr' colspan="3"><input name="av_titulo" size="50" type="text" class="boton" value="<?php echo($sesion[0]['av_titulo']); ?>" disabled></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>URL:</td>
    <td class='celdaValorAttr' colspan="3"><input name="av_url" size="50" type="text" value="<?php echo($sesion[0]['av_url']); ?>" class="boton" disabled></td>
  </tr>
</table>
</td>
</tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

<script>

actualiza_av("<?php echo($sesion[0]['av_utiliza']); ?>");

function actualiza_av(utilizo) {
  if (utilizo == "t") {
    formulario.av_tipo.disabled=false;
    formulario.av_tipo.required=true;
    formulario.av_titulo.disabled=false;
    formulario.av_titulo.required=true;
    formulario.av_url.disabled=false;
    formulario.av_url.required=true;    
  } else {
    formulario.av_tipo.disabled=true;
    formulario.av_tipo.required=false;
    formulario.av_titulo.disabled=true;
    formulario.av_titulo.required=false;
    formulario.av_url.disabled=true;
    formulario.av_url.required=false;
  }
}

function fila_destacada(id_fila) {
	document.getElementById(id_fila).style="background: none";
}

</script>


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
		'height'			: 600,
		'maxHeight'			: 600,
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
		'maxHeight'			: 9999,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});
</script>
