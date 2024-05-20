<?php
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$admin_poa = consulta_sql("SELECT poas_admin FROM usuarios WHERE id={$_SESSION['id_usuario']}");
$admin_poa = ($admin_poa[0]['poas_admin'] == "t") ? true : false;

$id_unidad = $_REQUEST['id_unidad'];

if ($id_unidad > 0) {
	$SQL_cant_conceptos_pol = "SELECT count(id) FROM gestion.segpol_conceptos WHERE id_concepto_padre=spcp.id";
	
	$SQL_segpol = "SELECT sp.id,spc.id AS id_concepto,spcp.id AS id_politica,sp.ano,meta,sp.indicador,
						  spcp.nombre AS nombre_politica,spcp.descripcion AS descripcion_politica,
						  ($SQL_cant_conceptos_pol) AS cant_conceptos,
						  spc.nombre AS nombre_concepto,spc.descripcion AS descripcion_concepto,
						  sp.meta_tipo_valor,sp.meta_decimales,sp.meta_descripcion,
						  CASE WHEN sp.meta=0 AND sp.indicador=0
						         THEN 0
						       WHEN sp.meta_eval
							     THEN round((sp.meta/sp.indicador)*100,1)
							     ELSE round((sp.indicador/sp.meta)*100,1)
						  END AS cumplimiento_ratio,
						  CASE WHEN sp.meta=0 AND sp.indicador=0
						         THEN 'N/A'
						       WHEN sp.meta_eval AND sp.meta > 0 
							     THEN CASE WHEN sp.indicador<=sp.meta THEN 'Cumplida' ELSE 'Incumplida' END
							     ELSE CASE WHEN sp.indicador>=sp.meta THEN 'Cumplida' ELSE 'Incumplida' END
						  END AS cumplimiento 
				   FROM gestion.segpol AS sp
				   LEFT JOIN gestion.segpol_conceptos AS spc  ON spc.id=sp.id_concepto
				   LEFT JOIN gestion.segpol_conceptos AS spcp ON spcp.id=spc.id_concepto_padre
				   WHERE spc.id_unidad=$id_unidad AND spc.id_concepto_padre IS NOT NULL
				   ORDER BY spcp.id,spc.id,sp.ano,spcp.orden,spc.orden";
	$segpol = consulta_sql($SQL_segpol);
	//echo($SQL_segpol);
	if (count($segpol) > 0) {
		$SQL_anos_pols = "SELECT ano 
						  FROM gestion.segpol AS sp 
						  LEFT JOIN gestion.segpol_conceptos AS spc ON spc.id=sp.id_concepto
						  WHERE spc.id_unidad=$id_unidad
						  GROUP BY ano
						  ORDER BY ano";
		$anos_pols = consulta_sql($SQL_anos_pols);
		
		$nombre_unidad = consulta_sql("SELECT nombre FROM gestion.unidades WHERE id=$id_unidad");

		$HTML = $HTML_segpol = "";
		$HTML .= "  <tr>\n";
		for($y=0;$y<count($anos_pols);$y++) { $HTML .= "    <td class='tituloTabla'>{$anos_pols[$y]['ano']}</td>\n"; }
		$HTML .= "  </tr>\n";
		for($x=0;$x<count($segpol);$x++) {
			$nom_pol = $segpol[$x]['nombre_politica'];
			$boton_edpol  = "<span id='edpol_$x' style='visibility: hidden'><a href='$enlbase_sm=editar_politica_segpol&id_politica={$segpol[$x]['id_politica']}&id_unidad=$id_unidad' id='sgu_fancybox_small' class='botoncito'>Editar Política</a></span>";

			$HTML .= "  <tr class='filaTabla' onMouseOver=\"elementos=['edpol_$x'];elementos.forEach(mostrar_elementos);\" onMouseOut=\"elementos=['edpol_$x'];elementos.forEach(ocultar_elementos);\">\n"
				  .  "    <td class='celdaNombreAttr' style='text-align: justify' rowspan='{$segpol[$x]['cant_conceptos']}'>"
				  .  "      {$segpol[$x]['nombre_politica']}<br>\n"
				  .  "      <i style='font-weight: normal'>{$segpol[$x]['descripcion_politica']}</i>\n"
				  .  "      <div style='text-align: right'>$boton_edpol</div>\n"
				  .  "    </td>\n";
			while ($nom_pol == $segpol[$x]['nombre_politica']) {
				$boton_edcon  = "<span id='edcon_$x' style='visibility: hidden'><a href='$enlbase_sm=editar_concepto_segpol&id_concepto={$segpol[$x]['id_concepto']}&id_unidad=$id_unidad' id='sgu_fancybox_small' class='botoncito'>Editar Concepto</a></span>";
				$HTML .= "    <td class='textoTabla' style='text-align: justify' onMouseOver=\"elementos=['edcon_$x'];elementos.forEach(mostrar_elementos);\" onMouseOut=\"elementos=['edcon_$x'];elementos.forEach(ocultar_elementos);\">\n"
				      .  "      <u>{$segpol[$x]['nombre_concepto']}</u><br>\n"
				      .  "      {$segpol[$x]['descripcion_concepto']}\n"
				      .  "      <div style='text-align: right'>$boton_edcon</div>\n"
				      .  "    </td>\n";
				$nom_con = $segpol[$x]['nombre_concepto'];
				while ($nom_con == $segpol[$x]['nombre_concepto']) {
					switch ($segpol[$x]['meta_tipo_valor']) {
						case "Porcentaje":
							$meta = round($segpol[$x]['meta'] * 100,$segpol[$x]['meta_decimales']);
							$indicador = round($segpol[$x]['indicador'] * 100,$segpol[$x]['meta_decimales']);
							$meta = "$meta%";
							$indicador = "$indicador%";
							break;
						case "Número":
							$meta = number_format($segpol[$x]['meta'],$segpol[$x]['meta_decimales'],".",",");
							$indicador = number_format($segpol[$x]['indicador'],$segpol[$x]['meta_decimales'],".",",");
							break;
						case "Pesos":
							$meta = "$ $meta";
							$indicador = "$ $indicador";
					}
					$boton_edmeta_ind  = "<span id='edmeta_$x' style='visibility: hidden'><a href='$enlbase_sm=editar_meta_segpol&id_meta={$segpol[$x]['id']}' id='sgu_fancybox_small' class='botoncito'>Editar Meta/Indicador</a></span>";

					$HTML .= "    <td class='textoTabla' onMouseOver=\"elementos=['edmeta_$x'];elementos.forEach(mostrar_elementos);\" onMouseOut=\"elementos=['edmeta_$x'];elementos.forEach(ocultar_elementos);\">"
					      .  "      Meta: $meta<br>"
					      .  "      Indicador: $indicador<hr>"
					      .  "      Cumplimiento: {$segpol[$x]['cumplimiento_ratio']}%<br><br>"
					      .  "      <div style='text-align: right'>$boton_edmeta_ind</div>\n"
					      .  "    </td>\n";
					$x++;
				}
				$HTML .= "  </tr>\n"
				      .  "  <tr class='filaTabla'>\n";
			}
			$x--;
		}
		$HTML_segpol = $HTML;
	}
}
$cond_unidades = "";
if ($_SESSION['id_unidad'] <> "" && !$admin_poa) { $cond_unidades = "WHERE id = {$_SESSION['id_unidad']}"; $id_unidad = $_SESSION['id_unidad']; }
$UNIDADES = consulta_sql("SELECT id,nombre||' ('||alias||')' AS nombre FROM gestion.unidades $cond_unidades ORDER BY nombre");

?>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<table cellpadding="1" border="0" cellspacing="2" width="auto" style="margin-top: 5px">
  <tr>
	<td class="celdaFiltro">
	  Unidad Administrativa:<br>
	  <select class='filtro' name="id_unidad" style="max-width: none" onChange="submitform();">
		<option value="-1">Todas</option>
		<?php echo(select($UNIDADES,$id_unidad)); ?>
	  </select>
	</td>
  </tr>
</table>
<table cellpadding="1" border="0" cellspacing="2" width="auto">
  <tr>
	<td class="celdaFiltro">
	  Acciones:<br>
	  <a href="<?php echo("$enlbase_sm=crear_politica_segpol&id_unidad=$id_unidad")?>" class="boton" id="sgu_fancybox_small">Nueva Política</a>
	  <a href="<?php echo("$enlbase_sm=crear_concepto_segpol&id_unidad=$id_unidad")?>" class="boton" id="sgu_fancybox_small">Nuevo Concepto</a>
	  <a href="<?php echo("$enlbase_sm=crear_meta-ind_segpol&id_unidad=$id_unidad")?>" class="boton" id="sgu_fancybox_small">Nueva Meta/Indicador</a>
	</td>
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="4" class="tabla" style="margin-top: 5px">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan='<?php echo(3+count($anos_pols)); ?>' width='300'><?php echo($nombre_unidad[0]['nombre']); ?></td>	  
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' rowspan='2' width='300'>Política</td>
    <td class='tituloTabla' rowspan='2' width='300'>Concepto</td>    
    <td class='tituloTabla' colspan='<?php echo(count($anos_pols)); ?>'>Año</td>
  </tr>
  <?php echo($HTML_segpol); ?>
</table>
</form>

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'		: false,
		'autoDimensions': false,
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic',
		'width'			: 700,
		'maxHeight'		: 650,
		'afterClose'	: function () { location.reload(true); },
		'type'			: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'		: false,
		'autoDimensions': false,
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic',
		'width'			: 1000,
		'maxHeight'		: 700,
		'afterClose'	: function () { location.reload(true); },
		'type'			: 'iframe'
	});
});

function mostrar_elementos(id_elem) {
	document.getElementById(id_elem).style.visibility='visible';
}

function ocultar_elementos(id_elem) {
	document.getElementById(id_elem).style.visibility='hidden';
}
</script>
