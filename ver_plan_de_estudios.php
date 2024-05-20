<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_malla = $_REQUEST['id_malla'];
if (!is_numeric($id_malla)) {
	echo(js("location.href='principal.php?modulo=gestion_mallas';"));
	exit;
}

$SQL_malla = "SELECT m.id,m.ano,carrera,m.niveles,m.requisitos_titulacion,id_escuela,m.comentarios,m.cant_asig_oblig,m.cant_asig_elect,m.cant_asig_efp,
                     coalesce(tns_nombre,'No aplica') AS tns_nombre,coalesce(tns_sem_req,0) AS tns_sem_req,tns_actividad_nombre,tns_actividad_pond,
                     coalesce(tns_promgen_pond,0)*100 AS tns_promgen_pond,
                     coalesce(ga_nombre,'No aplica') AS ga_nombre,coalesce(ga_sem_req,0) AS ga_sem_req,ga_actividad_nombre,ga_actividad_pond,
                     coalesce(ga_promgen_pond,0)*100 AS ga_promgen_pond,
                     coalesce(tp_nombre,'No aplica') AS tp_nombre,coalesce(tp_sem_req,0) AS tp_sem_req,tp_actividad_nombre,tp_actividad_pond,
                     coalesce(tp_promgen_pond,0)*100 AS tp_promgen_pond,
                     coalesce(otros_nombre,'No aplica') AS otros_nombre,coalesce(otros_sem_req,0) AS otros_sem_req,otros_actividad_nombre,otros_actividad_pond,
                     coalesce(otros_promgen_pond,0)*100 AS otros_promgen_pond
              FROM vista_mallas vm
              LEFT JOIN mallas m USING (id)
              WHERE m.id=$id_malla";
$malla     = consulta_sql($SQL_malla);
if (count($malla) > 0) {
	extract($malla[0]);

	$SQL_detalle_malla = "SELECT vdm.id AS id_dm,vdm.id_prog_asig,vdm.cod_asignatura,vdm.asignatura,vdm.caracter,pa.horas_semanal,vdm.id_prog_asig,vdm.nivel,
	                             dm.pond_tns,dm.pond_tp,dm.pond_ga,dm.pond_otros,pa.nro_semanas_semestrales,dm.linea_formacion,pa.creditos,pa.horas_autonomas_semanales
	                      FROM vista_detalle_malla vdm
	                      LEFT JOIN detalle_mallas AS dm USING (id)
	                      LEFT JOIN prog_asig pa ON pa.id=dm.id_prog_asig
	                      WHERE vdm.id_malla=$id_malla
	                      ORDER BY dm.nivel,vdm.cod_asignatura";
	$detalle_malla     = consulta_sql($SQL_detalle_malla);

	$SQL_requisitos = "SELECT *
	                   FROM vista_requisitos_malla
	                   WHERE id_dm IN (SELECT id FROM detalle_mallas WHERE id_malla=$id_malla)
	                   ORDER BY cod_asignatura,cod_asignatura_req";
	$requisitos     = consulta_sql($SQL_requisitos); 
	
	$HTML_otorga_titulos_grados = tabla_malla_otorga_titulos_grados($malla[0]);

} else {
	echo(msje_js("Se está intentando acceder a una malla inexistente. No es posible continuar"));
	echo(js("location.href='principal.php?modulo=gestion_carreras';"));
	exit;
}

$HTML = "";
$tot_creditos_plan = $tot_creditos_sem = $tot_horas_plan = $tot_horas_sem = $nivel_aux = 0;
$enlace = "$enlbase_sm=ver_prog_asig&id_malla=$id_malla&id_prog_asig";	

for ($x=0;$x<count($detalle_malla);$x++) {
	
	foreach($detalle_malla[$x] AS $campo => $valor) { 
		if (substr($campo,0,5)=="pond_") { 
			if (!is_null($valor) && $valor == 0) { $valor = "PG"; }
			if ($valor > 0 && $valor <= 1) { $valor = $valor*100 . "%"; }
			if (is_null($valor)) { $valor = "N/A"; }
			$detalle_malla[$x][$campo] = $valor;
		}
		
	}
	extract($detalle_malla[$x]);

	if ($nivel <> $nivel_aux) {
		if ($nivel > 1) {
			$HTML .= "<tr>"
			      .  "  <td class='celdaNombreAttr' colspan='5' align='right'>Total Créditos y Horas Lectivas semanales del {$NIVELES[$nivel-2]['nombre']} semestre:</td>"
			      .  "  <td class='textoTabla' align='center'><b>$tot_creditos_sem créditos</b></td>"
			      .  "  <td class='textoTabla' align='center'><b>$tot_horas_sem hrs.</b></td>"
			      .  "  <td class='textoTabla' colspan='6'></td>"
			      .  "</tr>";
			$tot_creditos_plan += $tot_creditos_sem;
			$tot_horas_plan += $tot_horas_sem;
			$tot_horas_sem = $tot_creditos_sem = 0;
		}
		$HTML .= "<tr><td class='textoTabla' colspan='11'><i>{$NIVELES[$nivel-1]['nombre']} Semestre</i></td></tr>";
	}

	$requisitos_asig = "";
	for($i=0;$i<count($requisitos);$i++) {
		if ($requisitos[$i]['id_dm'] == $id_dm && $requisitos[$i]['tipo'] == 1) {
			$requisitos_asig .= "<a href='$enlace={$requisitos[$i]['id_prog_asig']}' class='enlaces' id='sgu_fancybox_medium'>"
			                 .  "  <small>{$requisitos[$i]['cod_asignatura_req']} {$requisitos[$i]['asignatura_req']}</small>"
			                 .  "</a><br>";
		}
	}

	$asignatura = "<a id='sgu_fancybox_medium' href='$enlace=$id_prog_asig' class='enlaces'>$asignatura</a>";
	
	$HTML_pond = "";
	$enl_editar_pond = "$enlbase_sm=editar_malla_asignatura&id_malla=$id_malla&id_dm=$id_dm";
	if ($tns_nombre <> "No aplica") { $HTML_pond .= "<td class='textoTabla' align='center'><a href='$enl_editar_pond' id='sgu_fancybox_small' class='enlaces'>$pond_tns</a></td>\n"; }
	if ($ga_nombre <> "No aplica") { $HTML_pond .= "<td class='textoTabla' align='center'><a href='$enl_editar_pond' id='sgu_fancybox_small' class='enlaces'>$pond_ga</a></td>\n"; }
	if ($tp_nombre <> "No aplica") { $HTML_pond .= "<td class='textoTabla' align='center'><a href='$enl_editar_pond' id='sgu_fancybox_small' class='enlaces'>$pond_tp</a></td>\n"; }
	if ($otros_nombre <> "No aplica") { $HTML_pond .= "<td class='textoTabla' align='center'><a href='$enl_editar_pond' id='sgu_fancybox_small' class='enlaces'>$pond_otros</a></td>\n"; }
	
	
	$nro_asig = $x + 1;
	$HTML .= "<tr class='filaTabla'>\n"
		  .  "  <td class='textoTabla'>$nro_asig</td>\n"
		  .  "  <td class='textoTabla' nowrap>$cod_asignatura</td>\n"
		  .  "  <td class='textoTabla' nowrap>$asignatura</td>\n"
		  .  "  <td class='textoTabla'>$caracter</td>\n"
		  .  "  <td class='textoTabla' align='center'>$linea_formacion</td>\n"
		  .  "  <td class='textoTabla' align='center'>$creditos</td>\n"
		  .  "  <td class='textoTabla' align='center'>$horas_semanal</td>\n"
		  .  "  <td class='textoTabla' align='center'>$horas_autonomas_semanales</td>\n"
		  .  "  <td class='textoTabla' align='center'>$nro_semanas_semestrales</td>\n"
		  .  "  <td class='textoTabla'>$requisitos_asig</td>\n"
		  . $HTML_pond
		  .  "</tr>\n";
		  
	$tot_horas_sem += $horas_semanal * $nro_semanas_semestrales;
	$tot_creditos_sem += $creditos;

	$nivel_aux = $nivel;
}
$tot_horas_plan += $tot_horas_sem;

$HTML .= "<tr>"
	  .  "  <td class='celdaNombreAttr' colspan='5' align='right'>Total Créditos y Horas Lectivas semanales del {$NIVELES[$nivel_aux-1]['nombre']} semestre:</td>"
	  .  "  <td class='textoTabla' align='center'><b>$tot_creditos_sem créditos</b></td>"
	  .  "  <td class='textoTabla' align='center'><b>$tot_horas_sem hrs.</b></td>"
	  .  "  <td class='textoTabla' colspan='7'></td>"
	  .  "</tr>"
	  .  "<tr><td class='textoTabla' colspan='12'>&nbsp</td></tr>"
	  .  "<tr>"
	  .  "  <td class='celdaNombreAttr' colspan='5' align='right'>Total Cŕeditos y Horas Lectivas del Plan:</td>"
	  .  "  <td class='textoTabla' align='center'><b>$tot_creditos_plan créditos</b></td>"
	  .  "  <td class='textoTabla' align='center'><b>$tot_horas_plan hrs.</b></td>"
	  .  "  <td class='textoTabla' colspan='7'></td>"
	  .  "</tr>";
	  
$HTML_tit_pond = ""; $tit_pond = 0;
if ($tns_nombre <> "No aplica") { $HTML_tit_pond .= "<td class='tituloTabla'><a title='Técnico de Nivel Superior'><small>T.N.S.</small></a></td>\n"; $tit_pond++; }
if ($ga_nombre <> "No aplica") { $HTML_tit_pond .= "<td class='tituloTabla'><a title='Grado Académico'><small>G.A.</small></a></td>\n"; $tit_pond++; }
if ($tp_nombre <> "No aplica") { $HTML_tit_pond .= "<td class='tituloTabla'><a title='Título Profesional'><small>T.P.</small></a></td>\n"; $tit_pond++; }
if ($otros_nombre <> "No aplica") { $HTML_tit_pond .= "<td class='tituloTabla'>Otros</td>\n"; $tit_pond++; }

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>: <?php echo($carrera); ?> - <?php echo($ano); ?>
</div>
<?php if ($_REQUEST['imprimir'] <> "si") { ?>
<table class="tabla" style='margin-top: 5px'>
  <tr>
    <td>
      <input type="button" name="imprimir" value="Imprimir Plan de Estudios" onClick="window.open('<?php echo("$enlbase_sm=$modulo&id_malla=$id_malla&imprimir=si"); ?>')">
      <input type="button" name="editar" value="Editar Antecedentes del Plan de Estudios" onClick="window.location='<?php echo("$enlbase=editar_malla&id_malla=$id_malla"); ?>'">
      <input type="button" name="mediateca" value="Mediateca" onClick="window.location='<?php echo("$enlbase=mediateca_plan_de_estudios&id_malla=$id_malla"); ?>'">
      <input type="button" name="volver" value="Volver" onClick="window.location='<?php echo("$enlbase=ver_malla&id_malla=$id_malla"); ?>'">
    </td>
  </tr>
</table>
<?php } else { echo(js("window.print();window.close();")); } ?>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center">Antecedentes del Plan de Estudios</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Id:</td>
    <td class='celdaValorAttr'><?php echo($id); ?></td>
    <td class='celdaNombreAttr'>Año:</td>
    <td class='celdaValorAttr'><?php echo($ano); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($carrera); ?></td>
  </tr>
  <?php echo($HTML_otorga_titulos_grados); ?>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center">Cantidad de Asignaturas</td></tr>
  <tr>
    <td class='celdaValorAttr' colspan="4" style='text-align: center'>
      <b>Obligatorias:</b> <?php echo($cant_asig_oblig); ?>
      <b><a title='Electivas de Formación General'>E.F.G.</a>:</b> <?php echo($cant_asig_elect); ?>
      <b><a title='Electivas de Formación Profesional'>E.F.P.</a>:</b> <?php echo($cant_asig_efp); ?>
    </td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center">Comentarios</td></tr>
  <tr><td class='celdaValorAttr' colspan="4" align="justify"><?php echo(nl2br($comentarios)); ?></td></tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td rowspan='2' class='tituloTabla'>Nº</td>
    <td rowspan='2' class='tituloTabla'>Código</td>
    <td rowspan='2' class='tituloTabla'>Asignatura</td>
    <td rowspan='2' class='tituloTabla'>Carácter</td>
    <td rowspan='2' class='tituloTabla'><small>Línea de<br>Formación</small></td>
	<td rowspan='2' class='tituloTabla'>Créditos</td>
    <td colspan='2' class='tituloTabla'><small>Horas Semanales</small></td>
    <td rowspan='2' class='tituloTabla'><small>Sem.<br>Semes.</small></td>
    <td rowspan='2' class='tituloTabla'>Prerequisitos</td>
	<td class='tituloTabla' colspan='<?php echo($tit_pond); ?>'><small>Ponderación Concentración de Notas</small></td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'><small>Lectivas</small></td>
    <td class='tituloTabla'><small>Autónomas</small></td>
    <?php echo($HTML_tit_pond); ?>
  </tr>
  <?php echo($HTML); ?>  
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" width="75%" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='text-align: left;'>Requisitos de Titulación y/o Graduación</td>
  </tr>
  <tr class='filaTabla'>
    <td class='textoTabla'><?php echo(nl2br($requisitos_titulacion)); ?></td>
  </tr>
</table>
<!-- Fin: <?php echo($modulo); ?> -->
<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox_medium").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 1000,
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
		'width'				: 500,
		'height'			: 600,
		'maxHeight'			: 600,
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
		'width'				: 520,
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
		'closeClick'	    : true,
		'modal'      	    : true,
		'width'				: 800,
		'maxHeight'			: 9999,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});
</script>
