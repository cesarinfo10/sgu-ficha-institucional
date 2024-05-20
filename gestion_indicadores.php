<?php
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$admin_poa = consulta_sql("SELECT poas_admin FROM usuarios WHERE id={$_SESSION['id_usuario']}");
$admin_poa = ($admin_poa[0]['poas_admin'] == "t") ? true : false;

$id_unidad         = $_REQUEST['id_unidad'];
$id_agrupador      = $_REQUEST['id_agrupador'];
$id_subagrupador   = $_REQUEST['id_subagrupador'];
$id_pde            = $_REQUEST['id_pde'];
$cod_procedencia   = $_REQUEST['cod_procedencia'];
$id_abierto        = $_REQUEST['id_abierto'];
$id_activo         = $_REQUEST['id_activo'];
$estandarizado     = $_REQUEST['estandarizado'];
$mecanismo_captura = $_REQUEST['mecanismo_captura'];
$mecanismo_cap     = $_REQUEST['mecanismo_cap'];
$anos_ind          = implode(",",$_REQUEST['anos_ind']);

//if (empty($anos_ind)) { $anos_ind = date("Y")-1 . "," . date("Y"); }
if (empty($anos_ind)) { $anos_ind = "2017,2019,2022"; }
if (empty($id_activo)) { $id_activo = "t"; }

$cond_ind = "WHERE true";
if ($id_unidad > 0)         { $cond_ind .= " AND gic.id_unidad=$id_unidad"; }
if ($id_agrupador <> "")    { $cond_ind .= " AND gic.agrupador='$id_agrupador'"; }
if ($id_subagrupador <> "") { $cond_ind .= " AND gic.subagrupador='$id_subagrupador'"; }
if ($cod_procedencia <> "") { $cond_ind .= " AND gic.cod_procedencia='$cod_procedencia'"; }
if ($id_pde <> "")          { $cond_ind .= " AND gic.pde='$id_pde'"; }
if ($id_abierto <> "")      { $cond_ind .= " AND gic.abierto='$id_abierto'"; }
if ($id_activo <> "-1")       { $cond_ind .= " AND gic.activo='$id_activo'"; }
if ($estandarizado == "t")  { $cond_ind .= " AND gic.estandar IS NOT NULL"; }
elseif ($estandarizado == "f")  { $cond_ind .= " AND gic.estandar IS NULL"; }
if ($mecanismo_cap <> "") { $cond_ind .= " AND gic.mecanismo='$mecanismo_cap'"; }
//if ($anos_ind <> "")        { $cond_ind .= " AND ano IN ($anos_ind)"; }

$SQL_indicadores_cat = "SELECT gic.id,gic.nombre,gic.descripcion,gic.cod_procedencia,gic.agrupador,gic.subagrupador,
                               gic.valor_porcentaje,gic.valor_decimales,gic.cod_procedencia,gic.orden,gic.alias,
                               coalesce(gu.nombre,'* Institucional *') AS unidad,gic.estandar,gic.estandar_tipo,
                               CASE WHEN gic.abierto THEN 'Si' ELSE 'No' END AS abierto,
                               CASE WHEN gic.activo THEN 'Si' ELSE 'No' END AS activo,
                               gic.totalizador,gic.subitem,
							   gic.pde,gic.pde_nro_indicador,gic.relevancia,
							   gic.mecanismo,gic.periodicidad,
							   gic.period_anual_mes,gic.period_anual_dia,
							   gic.period_semestral_1ro_dia,gic.period_semestral_1ro_mes,gic.period_semestral_2do_dia,gic.period_semestral_2do_mes,
							   gic.period_mensual_dia,
							   gic.period_semanal_dia_sem,
							   to_char(gic.period_hora,'HH24:MI') AS period_hora
                        FROM gestion.indicadores_categorias AS gic
                        LEFT JOIN gestion.unidades AS gu ON gu.id=gic.id_unidad
                        $cond_ind
                        ORDER BY agrupador,subagrupador,orden,nombre";
$indicadores_cat = consulta_sql($SQL_indicadores_cat);

$SQL_ind_cat = "SELECT id FROM gestion.indicadores_categorias AS gic $cond_ind";

$ind_anos = array_column(consulta_sql("SELECT ano FROM gestion.indicadores WHERE id_cat_indicador IN ($SQL_ind_cat) AND ano IN ($anos_ind) GROUP BY ano ORDER BY ano"),'ano');

$SQL_indicadores = "SELECT i.* 
                    FROM gestion.indicadores AS i
                    LEFT JOIN gestion.indicadores_categorias AS gic ON gic.id=i.id_cat_indicador
                    WHERE id_cat_indicador IN ($SQL_ind_cat) AND i.ano IN ($anos_ind)
                    ORDER BY gic.agrupador,gic.subagrupador,gic.orden,i.ano,gic.nombre";
$indicadores = consulta_sql($SQL_indicadores);
$cols = count($ind_anos)+4;
if ($mecanismo_captura=="si") { $cols++; }
$HTML = "";
$y = 0;
if (count($indicadores_cat) > 0) {
	$agrupador = "";
	for ($x=0;$x<count($indicadores_cat);$x++) {
		$bgcolor = "";
		if ($indicadores_cat[$x]['abierto'] == "Si") { $bgcolor = "#FFFFCC"; }
		if ($indicadores_cat[$x]['abierto'] == "No") { $bgcolor = "#F2FFF2"; }
		
		$HTML .= "<tr class='filaTabla' bgcolor='$bgcolor'>";
		if ($agrupador <> $indicadores_cat[$x]['agrupador']) {
			$HTML .= "<td class='celdaNombreAttr' style='text-align: center' colspan='$cols'>{$indicadores_cat[$x]['agrupador']}</td></tr>"
			      .  "<tr class='filaTabla' bgcolor='$bgcolor'>";
		}
		
		//$indicadores_cat[$x]['nombre'] .= ($indicadores_cat[$x]['abierto'] == "Si") ? "🔓 " : "🔒 ";
		$indicadores_cat[$x]['abierto'] = ($indicadores_cat[$x]['abierto'] == "Si") ? "Si 🔓 " : "No 🔒 ";

		$title = "header=[Descripción] fade=[on]"
			   . "body=[<big>{$indicadores_cat[$x]['descripcion']}</big><br><br>"
			   . "      <b>Alias:</b> {$indicadores_cat[$x]['alias']}<br>"
			   . "      <b>Alcance:</b> {$indicadores_cat[$x]['unidad']}<br>"
			   . "      <b>Ámbito:</b> {$indicadores_cat[$x]['agrupador']}<br>"
			   . "      <b>Clase:</b> {$indicadores_cat[$x]['subagrupador']}<br>"
			   . "      <b>Abierto:</b> {$indicadores_cat[$x]['abierto']}<br>"
			   . "      <b>Activo:</b> {$indicadores_cat[$x]['activo']} ]";			   
		
		$estandar_tipo = "";
		$estandar = "➖";
		if ($indicadores_cat[$x]['estandar'] > 0) {
			$estandar = number_format($indicadores_cat[$x]['estandar'],$indicadores_cat[$x]['valor_decimales'],',','.');
			if ($indicadores_cat[$x]['valor_porcentaje'] == "t") { $estandar .= "%"; }
			if ($indicadores_cat[$x]['estandar_tipo'] <> "") {
				$estandar_tipo = ($indicadores_cat[$x]['estandar_tipo'] == "MIN") ? "🔼" : "🔽";
			}
		}
		
		$pde = "";
		if ($indicadores_cat[$x]['pde'] == "t") { $pde = " <sup class='OK'>&nbsp;PDE 🔗{$indicadores_cat[$x]['pde_nro_indicador']}&nbsp;</sup>"; }

		$nombre_indicador = $indicadores_cat[$x]['nombre'] . $pde;
		$cod_procedencia  = $indicadores_cat[$x]['cod_procedencia'];

		if ($indicadores_cat[$x]['totalizador'] == "t") { 
			$nombre_indicador = "<b>$nombre_indicador</b>";
			$estandar         = "<b>$estandar</b>";
			$cod_procedencia  = "<b>$cod_procedencia</b>";
		}

		if ($indicadores_cat[$x]['subitem'] == "t") { 
			$nombre_indicador = "<div style='font-style: italic;padding: 0px 0px 0px 20px'>$nombre_indicador</div>";
			$estandar         = "<i>$estandar</i>";
			$cod_procedencia  = "<i>$cod_procedencia</i>";
		}



		$nombre_indicador = "<div title='$title'><a href='$enlbase_sm=indicadores_editar&id_indicador_cat={$indicadores_cat[$x]['id']}' style='color: #000000' class='enlaces' id='sgu_fancybox'>$nombre_indicador</a></div>";
				
		$HTML .= "<td class='textoTabla' align='right'><span title='$title' style='color: #BFBFBF'>{$indicadores_cat[$x]['orden']}</span></td>"
		      .  "<td class='textoTabla'>$nombre_indicador</td>"
		      .  "<td class='textoTabla' align='center'><span title='$title'>$cod_procedencia</span></td>"
		      .  "<td class='textoTabla' align='center'><span title='$title'>$estandar $estandar_tipo</span></td>";
		if ($mecanismo_captura=="si") {
			$mecanismo = "<small>".$indicadores_cat[$x]['mecanismo']."</small>";
			switch ($indicadores_cat[$x]['periodicidad']) {
				case "Anual":
					$mes = meses($indicadores_cat[$x]['period_anual_mes']);
					$mecanismo .= "<br><small>{$indicadores_cat[$x]['period_anual_dia']} de $mes de cada año<br>a las {$indicadores_cat[$x]['period_hora']}</small>";
					break;
				case "Mensual":
					$mecanismo .= "<br><small>{$indicadores_cat[$x]['period_mensual_dia']} de cada mes<br>a las {$indicadores_cat[$x]['period_hora']}</small>";
					break;
				case "Semanal":
					$dia_semana = $dias_palabra[$indicadores_cat[$x]['period_semanal_dia_sem']-1]['nombre'];
					$mecanismo .= "<br><small>los $dia_semana de cada semana<br>a las {$indicadores_cat[$x]['period_hora']}</small>";
					break;
			}
			$HTML .= "<td class='textoTabla' align='center'><span title='$title'>$mecanismo</span></td>";
		}
		for($z=0;$z<count($ind_anos);$z++) {
			$valor = "<a href='$enlbase_sm=indicadores_agregar_valor&id_cat_indicador={$indicadores_cat[$x]['id']}&ano={$ind_anos[$z]}' class='enlaces' title='$title' id='sgu_fancybox'>#N/D</a>";				
			if ($indicadores[$y]['ano'] == $ind_anos[$z] && $indicadores_cat[$x]['id'] == $indicadores[$y]['id_cat_indicador']) {
				$valor = floatval($indicadores[$y]['valor']);
				if ($valor < 1 && $indicadores_cat[$x]['valor_porcentaje'] == "t") { $valor = $valor * 100; }
				$color_valor = "#000000";
				if ($indicadores_cat[$x]['estandar'] <> "") {
					if ($indicadores_cat[$x]['estandar_tipo'] == "MIN") {
						if ($valor < $indicadores_cat[$x]['estandar']) { $color_valor = "#FF0000"; }
						if ($valor >= $indicadores_cat[$x]['estandar']) { $color_valor = "#008000"; }
					}
					if ($indicadores_cat[$x]['estandar_tipo'] == "MAX") {
						if ($valor > $indicadores_cat[$x]['estandar']) { $color_valor = "#FF0000"; }
						if ($valor <= $indicadores_cat[$x]['estandar']) { $color_valor = "#008000"; }
					}
				}
				$valor = number_format($valor,$indicadores_cat[$x]['valor_decimales'],',','.');
				if ($indicadores_cat[$x]['valor_porcentaje'] == "t") { $valor .= "%"; }
				$valor = "<a href='$enlbase_sm=indicadores_editar_valor&id_indicador={$indicadores[$y]['id']}' class='enlaces' title='$title' style='color: $color_valor' id='sgu_fancybox'>$valor</a>";
			} else { 
				$y--;
			}
			if ($indicadores_cat[$x]['totalizador'] == "t") { $valor = "<b>$valor</b>"; }
			if ($indicadores_cat[$x]['subitem'] == "t") { $valor = "<i>$valor</i>"; }
			$HTML .= "<td class='textoTabla' align='right'>&nbsp;$valor</td>";
			$y++;
		}		
		$HTML .= "</tr>";
		$agrupador = $indicadores_cat[$x]['agrupador'];
	}
}

$HTML_anos = "";
for ($x=0;$x<count($ind_anos);$x++) { $HTML_anos .= "<td class='tituloTabla'>{$ind_anos[$x]}</td>"; }
	
$nombre_unidad = consulta_sql("SELECT nombre FROM gestion.unidades WHERE id=$id_unidad");
if (count($nombre_unidad) == 0) { $nombre_unidad = "Institucionales"; } else { $nombre_unidad = "de ".$nombre_unidad[0]['nombre']; } 

$cond_unidades = "";
if ($_SESSION['id_unidad'] <> "" && !$admin_poa) { $cond_unidades = "WHERE id = {$_SESSION['id_unidad']}"; $id_unidad = $_SESSION['id_unidad']; }
$UNIDADES = consulta_sql("SELECT id,nombre||' ('||alias||')' AS nombre FROM gestion.unidades $cond_unidades ORDER BY nombre");
$AGRUPADORES = consulta_sql("SELECT id,nombre FROM vista_ind_cat_agrupador");
$SUBAGRUPADORES = consulta_sql("SELECT id,nombre FROM vista_ind_cat_subagrupador");
$PROCEDENCIAS_int = consulta_sql("SELECT codigo AS id,'('||codigo||') '||nombre AS nombre FROM gestion.indicadores_procedencia WHERE interno ORDER BY nombre");
$PROCEDENCIAS_ext = consulta_sql("SELECT codigo AS id,'('||codigo||') '||nombre AS nombre FROM gestion.indicadores_procedencia WHERE NOT interno ORDER BY nombre");
$MECANISMOS       = consulta_sql("SELECT id,nombre FROM vista_ind_cat_mecanismo");

$ABIERTOS = array(array('id'=>"t",'nombre'=>"🔓 Si"),array('id'=>"f",'nombre'=>"🔒 No"));
$anos_ind = explode(",",$anos_ind);
$ANOS_ind = consulta_sql("SELECT ano AS id,ano AS nombre FROM gestion.indicadores GROUP BY ano ORDER BY ano");
$HTML_filtro_anos = "";
for ($x=0;$x<count($ANOS_ind);$x++) {
	$checked = "";
	$ano = $ANOS_ind[$x]['id'];
	if (in_array($ano,$anos_ind)) { $checked = "checked='checked'"; }
	$HTML_filtro_anos .= "<input style='vertical-align: bottom; ' type='checkbox' name='anos_ind[]' value='$ano' id='$ano' onChange='submitform();' $checked> <label for='$ano'>$ano</label>&nbsp;&nbsp;";
}
?>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<table cellpadding="1" border="0" cellspacing="2" width="auto" style="margin-top: 5px">
  <tr class='filaTituloTabla'>
	<td class="celdaFiltro" colspan="1">
      Años:<br>
      <div style='vertical-align: top'><?php echo($HTML_filtro_anos); ?></div>
    </td>
	<td class="celdaFiltro">
	  Abierto:<br>
	  <select class='filtro' name="id_abierto" style="max-width: none" onChange="submitform();">
		<option value="">Todos</option>
		<?php echo(select($ABIERTOS,$id_abierto)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
	  Activo:<br>
	  <select class='filtro' name="id_activo" style="max-width: none" onChange="submitform();">
		<option value="-1">Todos</option>
		<?php echo(select($sino,$id_activo)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
	  Acciones:<br>
	  <a href="<?php echo("$enlbase_sm=indicadores_crear"); ?>" class="boton" id="sgu_fancybox_small">📏 Crear Indicador</a>
	  <!-- <a href="<?php echo("$enlbase_sm=indicadores_crear_procedencia")?>" class="boton" id="sgu_fancybox_small">Crear Indicador</a> -->
	  <a href="<?php echo("$enlbase_sm=indicadores_agregar_valor"); ?>" class="boton" id="sgu_fancybox_small">📶 Agregar Valor</a>
	</td>
  </tr>
</table>
<table cellpadding="1" border="0" cellspacing="2" width="auto">
  <tr>
<!-- 
	<td class="celdaFiltro">
	  Alcance:<br>
	  <select class='filtro' name="id_unidad" style="max-width: none" onChange="submitform();">
		<option value="-1">* Institucional *</option>
		<optgroup label="Unidad Administrativa">
		<?php echo(select($UNIDADES,$id_unidad)); ?>
		</optgroup>
	  </select>
	</td>
--> 
	<td class="celdaFiltro">
	  Ámbito:<br>
	  <select class='filtro' name="id_agrupador" style="max-width: none" onChange="submitform();">
		<option value="">Todos</option>
		<?php echo(select($AGRUPADORES,$id_agrupador)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
	  Clase:<br>
	  <select class='filtro' name="id_subagrupador" style="max-width: none" onChange="submitform();">
		<option value="">Todas</option>
		<?php echo(select($SUBAGRUPADORES,$id_subagrupador)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
	  PDE:<br>
	  <select class='filtro' name="id_pde" style="max-width: none" onChange="submitform();">
		<option value="">Todas</option>
		<?php echo(select($sino,$id_pde)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
	  Fuente:<br>
	  <select class='filtro' name="cod_procedencia" onChange="submitform();">
		<option value="">Todas</option>
        <optgroup label="Internos">
          <?php echo(select($PROCEDENCIAS_int,$_REQUEST['cod_procedencia'])); ?>
        </optgroup>
        <optgroup label="Externos">
          <?php echo(select($PROCEDENCIAS_ext,$_REQUEST['cod_procedencia'])); ?>
        </optgroup>
      </select>
	</td>
	<td class="celdaFiltro">
	  c/Estándar:<br>
	  <select class='filtro' name="estandarizado" onChange="submitform();">
		<option value="">Todos</option>
		<?php echo(select($sino,$estandarizado)); ?>
	  </select>
	</td>
<?php if ($mecanismo_captura == "si") { ?>
	<td class="celdaFiltro">
	  Mecanismo:<br>
	  <select class='filtro' name="mecanismo_cap" onChange="submitform();">
		<option value="">Todos</option>
		<?php echo(select($MECANISMOS,$mecanismo_cap)); ?>
	  </select>
	</td>
<?php } ?>
	<td class="celdaFiltro">
	  Mostrar:<br>
	  <input type="checkbox" name="mecanismo_captura" id="mecanismo_captura" value="si" onClick="submitform();" <?php echo($mecanismo_captura == "si" ? "checked" : ""); ?>>
      <label for="mecanismo_captura">Captura</label>&nbsp;&nbsp;&nbsp;
	  <input type="checkbox" name="relevancia" id="relevancia" value="si" onClick="submitform();" <?php echo($relevancia == "si" ? "checked" : ""); ?>>
      <label for="relevancia">Relevancia</label>&nbsp;
	</td>
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan='<?php echo($cols); ?>'>Indicadores <?php echo($nombre_unidad . " (".count($indicadores_cat).")"); ?></td>	  
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'></td>
    <td class='tituloTabla'>Categoría</td>
    <td class='tituloTabla'>Fuente</td>
    <td class='tituloTabla'>Estándar</td>
<?php if ($mecanismo_captura=="si") { ?>
    <td class='tituloTabla'>Captura</td>
<?php } ?>
    <?php echo($HTML_anos); ?>
  </tr>
  <?php echo($HTML); ?>
</table>
</form>

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'		: false,
		'autoDimensions': false,
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic',
		'width'			: 800,
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
