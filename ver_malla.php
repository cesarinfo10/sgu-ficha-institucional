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

$SQL_malla = "SELECT m.id,m.ano,carrera,m.niveles,m.cant_asig_oblig,m.cant_asig_elect,m.cant_asig_efp,m.comentarios,
                     id_escuela,m.requisitos_titulacion,
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
              WHERE id=$id_malla";
$malla     = consulta_sql($SQL_malla);
if (count($malla) > 0) {
	extract($malla[0]);

	$SQL_detalle_malla = "SELECT * FROM vista_detalle_malla WHERE id_malla=$id_malla";
	$detalle_malla     = consulta_sql($SQL_detalle_malla);

	$SQL_lineas_tematicas = "SELECT id,nombre FROM lineas_tematicas WHERE id_escuela=$id_escuela";
	$lineas_tematicas     = consulta_sql($SQL_lineas_tematicas);

	$SQL_requisitos = "SELECT *
	                   FROM vista_requisitos_malla
	                   WHERE id_dm IN (SELECT id FROM detalle_mallas WHERE id_malla=$id_malla)";
	$requisitos     = consulta_sql($SQL_requisitos); 
	
	$HTML_otorga_titulos_grados = tabla_malla_otorga_titulos_grados($malla[0]);

} else {
	echo(js("location.href='principal.php?modulo=gestion_carreras';"));
	exit;
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>: <?php echo($carrera); ?> - <?php echo($ano); ?>
</div>
<table class="tabla" style="margin-top: 5px">
  <tr>
    <td class="celdaFiltro" style="vertical-align: middle;">
      <input type="button" name="ver_plan_de_estudios" value="Plan de Estudios" onClick="window.location='<?php echo("$enlbase=ver_plan_de_estudios&id_malla=$id_malla"); ?>'">
  	  <?php echo("<a href='$enlbase_sm=editar_malla&id_malla=$id_malla' id='sgu_fancybox_small' class='boton'>Editar Antecedentes de la Malla</a>"); ?>
      <input type="button" name="editar" value="Agregar o quitar Asignaturas" onClick="window.location='<?php echo("$enlbase=editar_detalle_malla&id_malla=$id_malla"); ?>'">
      <input type="button" name="volver" value="Volver" onClick="<?php echo($_REQUEST['enl_volver']); ?>">
    </td>
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center">Antecedentes de la Malla</td></tr>
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
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center">Requisitos Generales y Finales de Graduación y/o Titulación</td></tr>
  <tr><td class='celdaValorAttr' colspan="4" align="justify"><?php echo(nl2br($requisitos_titulacion)); ?></td></tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center">Comentarios</td></tr>
  <tr><td class='celdaValorAttr' colspan="4" align="justify"><?php echo(nl2br($comentarios)); ?></td></tr>
</table>
<br>
<table cellpadding="4" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla">
  <tr class='filaTituloTabla'>
    <td rowspan="2" class="tituloTabla" width="80">L&iacute;neas<br>Tem&aacute;ticas</td>
    <td colspan="<?php echo($niveles); ?>" class='tituloTabla'>Semestres</td>
  </tr>
  <tr class='filaTituloTabla'>
<?php
	for($nivel=1;$nivel<=$niveles;$nivel++) {	
		echo("    <td class='tituloTabla'>$nivel</td>\n");
	}
?>
  </tr>
<?php
	$enlace = "$enlbase=ver_prog_asig&id_prog_asig";	
	for($x=0;$x<count($lineas_tematicas);$x++) {
		$id_lt = $lineas_tematicas[$x]['id'];
		$nombre_lt = $lineas_tematicas[$x]['nombre'];
		$tieneAsig = false;
		$filaTabla = "";
		$filaTabla  = "  <tr>\n";
		$filaTabla .= "    <td class='tituloTabla' width='80'>$nombre_lt</td>\n";		
		for($nivel=1;$nivel<=$niveles;$nivel++) {
			$asignatura = "";
			for($y=0;$y<count($detalle_malla);$y++) {
				if ($nivel==$detalle_malla[$y]['nivel'] && $id_lt==$detalle_malla[$y]['id_linea_tematica']) {
					$id_prog_asig      = $detalle_malla[$y]['id_prog_asig'];
					$cod_asignatura    = trim($detalle_malla[$y]['cod_asignatura']);
					$ano_asignatura    = $detalle_malla[$y]['ano'];
					$nombre_asignatura = $detalle_malla[$y]['asignatura'];
					$id_dm             = $detalle_malla[$y]['id'];
					$caracter          = $detalle_malla[$y]['caracter'];
					
					$requisitos_asig = "";
					for($i=0;$i<count($requisitos);$i++) {
						if ($requisitos[$i]['id_dm'] == $id_dm && $requisitos[$i]['tipo'] == 1) {
							$requisitos_asig .= $requisitos[$i]['asignatura_req'] . "<br>";
						};
					};
											
					$title = "header=[Propiedades] fade=[on]"
					       . "body=[Año Programa: $ano_asignatura<br>"
					       . "      Carácter: $caracter<br>"
 					       . "      <b>Pre-requisitos:</b><br>$requisitos_asig]";
					$cont_asig = "<div class='ramoMalla' title='$title'>
					                <a class='enlaces' href='$enlace=$id_prog_asig&id_malla=$id_malla'>
					                  <b>$cod_asignatura</b><br>$nombre_asignatura
					                </a>
					              </div>";					              
					if ($asignatura <> "") {
						$cont_asig .= "<br>$asignatura";
					};
					$asignatura = $cont_asig;
					$tieneAsig = true;
//					break;
				};
			};
			$filaTabla .= "    <td valign='top' class='celdaramoMalla'>$asignatura</td>\n";
		};
		$filaTabla .= "  </tr>\n";
		if ($tieneAsig) {
			echo($filaTabla);
		};
	};
?>
</table>
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
		'height'			: 9999,
		'maxHeight'			: 9999,
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

