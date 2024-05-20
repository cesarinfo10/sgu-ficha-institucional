<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");
include("libchart/classes/libchart.php");

$ano             = $_REQUEST['ano'];
$divisor_valores = $_REQUEST['divisor_valores'];

if ($ano == "") {
	$SQL_flujo_activo = "SELECT ano FROM finanzas.flujos WHERE activo";
	$flujo_activo = consulta_sql($SQL_flujo_activo);
	
	if (count($flujo_activo) > 1) {
		echo(msje_js("ERROR: Existe más de un flujo activo. Informe al Departamento de Informática, "
		            ."indicando que año de flujo es el activo"));
		exit;
	} elseif (count($flujo_activo) == 1) {
		$ano = $flujo_activo[0]['ano'];
		echo(js("window.location='$enlbase=$modulo&ano=$ano';"));
		exit;
	} elseif (count($flujo_activo) == 0) {
		echo(msje_js("ERROR: No existen flujos creados. Podrá crear uno luego pinchar en «Aceptar»"));
		echo(js("window.location='$enlbase=flujos_generales_crear'"));
		exit;
	}
}

if ($divisor_valores == "") { $divisor_valores = 1000; }

$id_cat_flujo = $_REQUEST['id_cat_flujo'];
if ($_REQUEST['eliminar'] == "Si" && $id_cat_flujo > 0) {
	if ($_REQUEST['conf'] <> "Eliminar") {
		$SQL_flujo_detalle = "SELECT fc.nombre AS categoria,montos 
		                      FROM finanzas.flujos_detalle AS fd 
		                      LEFT JOIN finanzas.flujos_categorias AS fc ON fc.id=fd.id_cat_flujo
		                      WHERE fd.id=$id_cat_flujo";
		$flujo_detalle = consulta_sql($SQL_flujo_detalle);
		if ($flujo_detalle[0]['montos'] <> "{0,0,0,0,0,0,0,0,0,0,0,0}") {
			$msje = "El item {$flujo_detalle[0]['categoria']} que está intentando eliminar tiene montos ingresados.\\n\\n"
				  . "Está seguro de eliminarlo definitivamente (esto no es posible deshacerlo)?";
			$url_si = "$enlbase=flujos_generales&eliminar=Si&id_cat_flujo=$id_cat_flujo&ano=$ano&conf=Eliminar";
			echo(confirma_js($msje,$url_si,"#"));
		} else {
			$_REQUEST['conf'] = "Eliminar";
		}
	}
	if ($_REQUEST['conf'] == "Eliminar") {
		consulta_dml("DELETE FROM finanzas.flujos_detalle WHERE id=$id_cat_flujo");
		echo(js("window.location='$enlbase=flujos_generales&ano=$ano';"));
	}
}

$SQL_flujo = "SELECT ano,vu.nombre AS creador,to_char(fecha_creacion,'DD-tmMon-YYYY') AS fec_creacion,
                     to_char(fecha_modificacion,'DD-tmMon-YYYY HH24:MI') AS fec_mod,comentarios,
                     CASE WHEN f.activo THEN 'Activo' ELSE 'No activo' END AS estado
              FROM finanzas.flujos AS f
              LEFT JOIN vista_usuarios AS vu ON vu.id=f.id_creador
              WHERE ano=$ano";
$flujo = consulta_sql($SQL_flujo);
if (count($flujo) == 0) {

}

$SQL_flujo_detalle = "SELECT fd.id,fc.nombre AS categoria,fcg.nombre AS totalizador,fc.tipo,monto_presupuesto,montos,comentarios 
                      FROM finanzas.flujos_detalle AS fd 
                      LEFT JOIN finanzas.flujos_categorias AS fc  ON fc.id=fd.id_cat_flujo
                      LEFT JOIN finanzas.flujos_cat_grupos AS fcg ON fcg.id=fc.id_cat_grupo
                      WHERE ano_flujo=$ano 
                      ORDER BY fc.tipo DESC,fcg.nombre,fc.nombre";
$flujo_detalle = consulta_sql($SQL_flujo_detalle);
if (count($flujo_detalle) == 0) {

}

$SQL_ingresos_desg = "SELECT CASE coalesce(car.regimen,al.regimen) WHEN 'PRE' THEN 'Pregrado' WHEN 'POST' THEN 'Postgrado' ELSE '' END AS regimen,g.tipo,
                             coalesce(con.ano,date_part('year',p.fecha)) AS ano_origen,
                             date_part('year',fecha_venc) as ano_venc,
                             date_part('month',p.fecha) as mes_pago,
                             sum(monto_pagado) AS monto
                      FROM finanzas.pagos p 
                      LEFT JOIN finanzas.pagos_detalle AS pd  ON id_pago=p.id 
                      LEFT JOIN finanzas.cobros        AS c   ON c.id=pd.id_cobro 
                      LEFT JOIN finanzas.glosas        AS g   ON g.id=id_glosa
                      LEFT JOIN finanzas.contratos     AS con ON con.id=id_contrato 
                      LEFT JOIN carreras               AS car ON car.id=con.id_carrera 
                      LEFT JOIN alumnos                AS al  ON al.id=c.id_alumno 
                      WHERE NOT p.nulo AND date_part('year',p.fecha) = $ano AND con.id_carrera IN (17) AND con.jornada='D'
                      GROUP BY g.tipo,car.regimen,al.regimen,ano_origen,ano_venc,mes_pago
                      ORDER BY coalesce(car.regimen,al.regimen) DESC,g.tipo,ano_origen,ano_venc,mes_pago";
$ingresos_desg = consulta_sql($SQL_ingresos_desg);
$y = 0;
for ($x=0;$x<count($ingresos_desg);$x++) {
	$tipo       = $ingresos_desg[$x]['tipo'];
	$regimen    = $ingresos_desg[$x]['regimen'];
	$ano_origen = $ingresos_desg[$x]['ano_origen'];
	$ano_venc   = $ingresos_desg[$x]['ano_venc'];
	
	$flujo_det_ingresos[$y]['id']          = 0;
	$flujo_det_ingresos[$y]['categoria']   = substr($tipo,1)." $regimen $ano_origen con vcto en $ano_venc";
	$flujo_det_ingresos[$y]['totalizador'] = "Caja $regimen";
	$flujo_det_ingresos[$y]['tipo']        = "I";
	$montos_ingresos = array();
	for ($mes=1;$mes<=12;$mes++) {
		$montos_ingresos[$mes] = 0;
		if ($mes == $ingresos_desg[$x]['mes_pago'] && $ingresos_desg[$x]['monto'] > 0 && $regimen == $ingresos_desg[$x]['regimen']
		 && $ingresos_desg[$x]['tipo'] == $tipo && $ingresos_desg[$x]['ano_origen'] == $ano_origen && $ingresos_desg[$x]['ano_venc'] == $ano_venc) {
			$montos_ingresos[$mes] = $ingresos_desg[$x]['monto'];
			$x++;
		}
	}
	$flujo_det_ingresos[$y]['montos'] = "{".implode(",",$montos_ingresos)."}";
	$y++;
	$x--;
}
$flujo_detalle = array_merge($flujo_det_ingresos,$flujo_detalle);

/*
$SQL_ingresos = "SELECT date_part('month',fecha) AS mes,
                        sum(coalesce(efectivo,0)+coalesce(cheque,0)+coalesce(transferencia,0)+coalesce(tarj_credito,0)+coalesce(tarj_debito,0)) AS monto
                 FROM finanzas.pagos
                 WHERE date_part('year',fecha) = $ano
                 GROUP BY date_part('month',fecha) 
                 ORDER BY date_part('month',fecha)";
$ingresos = consulta_sql($SQL_ingresos);
if (count($ingresos) > 0) {
	$flujo_det_ingresos[0]['id'] = 0;
	$flujo_det_ingresos[0]['categoria'] = "Pagos";
	$flujo_det_ingresos[0]['totalizador'] = "Caja";
	$flujo_det_ingresos[0]['tipo'] = "I";
	$flujo_det_ingresos[0]['montos'] = "{";
	$i = 0;
	for ($mes=1;$mes<=12;$mes++) {
		if($mes == $ingresos[$i]['mes'] && $ingresos[$i]['monto'] > 0) {
			$flujo_det_ingresos[0]['montos'] .= $ingresos[$i]['monto'].",";
		} else {
			$flujo_det_ingresos[0]['montos'] .= "0,";
		}
		$i++;
	}
	$flujo_det_ingresos[0]['montos'] = substr($flujo_det_ingresos[0]['montos'],0,-1)."}";
	$flujo_detalle = array_merge($flujo_det_ingresos,$flujo_detalle);
}
*/

$HTML = "<tr><td class='textoTabla' colspan='16'><b><i>INGRESOS</i></b></td></tr>";
$tipo_flujo = "I";
$tot_ingresos = $tot_gastos = $total = array();
$_totalizador = $flujo_detalle[0]['totalizador'];
$subtotal = array();
for ($x=0;$x<count($flujo_detalle);$x++) {
	extract($flujo_detalle[$x]);
	
	if ($_totalizador <> $flujo_detalle[$x]['totalizador']) {		
		$HTML .= "<tr>"
		      .  "  <td class='textoTabla' style='text-align: right; font-size: 8pt; vertical-align: middle'><b><i>SubTotal $_totalizador</i></b></td>"
		      .  "  <td class='textoTabla' style='text-align: right; font-size: 8pt; vertical-align: middle'><b><i>".number_format(round($subtotal['ppto']/$divisor_valores,0),0,",",".")."</i></b></td>";
		$tot_cat = 0;
		for ($mes=0;$mes<12;$mes++) { 
			$HTML .=  "  <td class='textoTabla' style='text-align: right; font-size: 8pt; vertical-align: middle'><b><i>".number_format(round($subtotal[$mes]/$divisor_valores,0),0,",",".")."</i></b></td>";
			$tot_cat += $subtotal[$mes];
		}
		
		$HTML .= "  <td class='textoTabla' style='text-align: right; font-size: 8pt; vertical-align: middle'><b><i>".number_format(round($tot_cat/$divisor_valores,0),0,",",".")."</i></b></td>"
		      .  "  <td class='textoTabla'>&nbsp;</td>"
		      .  "</tr>";
		
		$subtotal = array();
	}
	$_totalizador = $totalizador;
	
	if ($tipo == "G" && $tipo_flujo == "I") {
		$HTML .= "<tr><td class='textoTabla' colspan='16'><b><i>GASTOS</i></b></td></tr>";
		$tipo_flujo = "";
	}
	
	if ($id > 0) {
		$categoria = "<span id='bo$x' style='visibility: hidden'>"
		           . "  <a href='$enlbase_sm=flujos_generales_editar_cat_flujo&id_cat_flujo=$id&ano=$ano' title='Editar Item' class='boton' id='sgu_fancybox_small'><small>✍</small></a> "
		           . "  <a href='#' onClick=\"if (confirm('Está seguro de eliminar el item $categoria?')) { window.location='$enlbase=flujos_generales&eliminar=Si&id_cat_flujo=$id&ano=$ano'; }\" title='Eliminar Item' class='boton'><small>✕</small></a>"
		           . "</span> $categoria";
	}
	
	$HTML .= "<tr class='filaTabla' onMouseOver=\"document.getElementById('bo$x').style.visibility='visible'\" onMouseOut=\"document.getElementById('bo$x').style.visibility='hidden'\">\n"
	      .  "  <td class='textoTabla' style='text-align: right; font-size: 8pt; vertical-align: middle'>$categoria</td>\n"
	      .  "  <td class='textoTabla' style='text-align: right; font-size: 8pt;; vertical-align: middle'>".number_format(round($monto_presupuesto/$divisor_valores,0),0,",",".")."</td>\n";
	$montos_mesuales = explode(",",str_replace(array("{","}"),"",$montos));
	$comentarios_mesuales = explode(",",str_replace(array("{","}"),"",str_replace("\"","",$comentarios)));
	$subtotal['ppto'] += $monto_presupuesto;

	$tot_categoria = 0;
	for ($mes=0;$mes<12;$mes++) {
		$valor_mes = number_format(round($montos_mesuales[$mes]/$divisor_valores,0),0,",",".");
		if (!empty($comentarios_mesuales[$mes])) {
			$valor_mes = "<div title='header=[Comentarios] fade=[on] body=[{$comentarios_mesuales[$mes]}]' style='background: #BFE4BF; border-radius: 25px; padding: 0px 2px 0px 2px'>$valor_mes</div>";
		}
		$mes_nombre = substr($meses_palabra[$mes]['nombre'],0,3);
		$HTML .= "  <td class='textoTabla' id='$mes_nombre' style='text-align: right; font-size: 8pt;; vertical-align: middle'>$valor_mes</td>\n";
		$tot_categoria += $montos_mesuales[$mes];
		if ($tipo == "I") { $tot_ingresos[$mes] += $montos_mesuales[$mes]; }
		else { $tot_gastos[$mes] += $montos_mesuales[$mes]; $montos_mesuales[$mes] *= -1; }
		$total[$mes] += $montos_mesuales[$mes];
		$subtotal[$mes] += $montos_mesuales[$mes];		
	}
	
	$HTML .= "  <td class='textoTabla' style='text-align: right; font-size: 8pt; vertical-align: middle'><b>".number_format(round($tot_categoria/$divisor_valores,0),0,",",".")."</b></td>"
	      .  "  <td class='textoTabla' style='text-align: right; font-size: 8pt; vertical-align: middle'>".number_format(round(($tot_categoria/$monto_presupuesto)*100,2),2,",",".")."%</td>"
	      .  "</tr>\n";
	
	if ($tipo == "I") { $tot_ingresos[12] += $tot_categoria; $tot_ingresos[13] += $monto_presupuesto; }
	else { $tot_gastos[12] += $tot_categoria; $tot_categoria *= -1; $tot_gastos[13] += $monto_presupuesto; $monto_presupuesto *= -1;}
	$total[12] += $tot_categoria;
	$total[13] += $monto_presupuesto;	
}

$HTML .= "<tr>"
	  .  "  <td class='textoTabla' style='text-align: right; font-size: 8pt; vertical-align: middle'><b><i>SubTotal $_totalizador</i></b></td>"
	  .  "  <td class='textoTabla' style='text-align: right; font-size: 8pt; vertical-align: middle'><b><i>".number_format(round($subtotal['ppto']/$divisor_valores,0),0,",",".")."</i></b></td>";
$tot_cat = 0;
for ($mes=0;$mes<12;$mes++) { 
	$HTML .=  "  <td class='textoTabla' style='text-align: right; font-size: 8pt; vertical-align: middle'><b><i>".number_format(round($subtotal[$mes]/$divisor_valores,0),0,",",".")."</i></b></td>";
	$tot_cat += $subtotal[$mes];
}
$HTML .= "  <td class='textoTabla' style='text-align: right; font-size: 8pt; vertical-align: middle'><b><i>".number_format(round($tot_cat/$divisor_valores,0),0,",",".")."</i></b></td>"
	  .  "  <td class='textoTabla'>&nbsp;</td>"
	  .  "</tr>";

$totales['Total Ingresos'] = $tot_ingresos;
$totales['Total Gastos']   = $tot_gastos;
$totales['Saldo']          = $total;

$grafico = new LineChart(720,320);
$grafico->getPlot()->getPalette()->setLineColor(array(new Color(  0, 0, 255),new Color(255, 0,   0)));
$grafico_ingresos = new XYDataSet();
$grafico_gastos = new XYDataSet();

foreach ($totales AS $tipo => $meses) {

	if ($tipo == "Total Ingresos")   { $estilo = "color: #000099"; } 
	elseif ($tipo == "Total Gastos") { $estilo = "color: #990000"; }
	else { $estilo = ""; }
	
	$monto_presupuesto = number_format(round($meses[13]/$divisor_valores,0),0,",",".");
	if ($tipo <> "Saldo") { $monto_presupuesto = "<small>$monto_presupuesto</small>"; }
	$HTML .= "<tr><td class='textoTabla' colspan='16'>&nbsp;</td></tr>\n"
		  .  "<tr>\n"
		  .  "  <td class='celdaNombreAttr' style='$estilo'>$tipo:</td>\n"
		  .  "  <td class='textoTabla' align='right' style='$estilo'><b>$monto_presupuesto</b></td>\n";
	for ($mes=0;$mes<13;$mes++) {
		$valor_mes = number_format(round($meses[$mes]/$divisor_valores,0),0,",",".");
		$mes_nombre = substr($meses_palabra[$mes]['nombre'],0,3);

		if ($tipo == "Total Ingresos" && $mes<12) { $grafico_ingresos -> addPoint(new Point($mes_nombre, round($meses[$mes]/$divisor_valores,0))); }
		if ($tipo == "Total Gastos" && $mes<12) { $grafico_gastos -> addPoint(new Point($mes_nombre, round($meses[$mes]/$divisor_valores,0))); }
		
		if ($tipo <> "Saldo") { $valor_mes = "<small>$valor_mes</small>"; }

		$HTML .= "<td class='textoTabla' id='$mes_nombre' align='right' style='$estilo'><b>$valor_mes</b></td>\n";
	}
	
	$HTML .= "  <td class='textoTabla' style='text-align: right; font-size: 8pt;; vertical-align: middle'>".number_format(round(($meses[12]/$meses[13])*100,2),2,",",".")."%</td>";

	
	$HTML .= "</tr>\n";

}


$dataSet = new XYSeriesDataSet();
$dataSet->addSerie("Total Ingresos", $grafico_ingresos);
$dataSet->addSerie("Total Gastos", $grafico_gastos);
$grafico->setDataSet($dataSet);
$grafico->setTitle("Flujo Anual $ano");
$grafico->render("graficos/flujo_$ano.png");

$DIVISORES = array(array('id'=>1      ,'nombre'=>"en pesos"),
                   array('id'=>1000   ,'nombre'=>"en miles de pesos"),
                   array('id'=>1000000,'nombre'=>"en millones de pesos"));

$ANOS_flujos = consulta_sql("SELECT ano AS id,ano||CASE WHEN activo THEN ' *' ELSE '' END AS nombre FROM finanzas.flujos ORDER BY ano DESC");
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="ano" value="<?php echo($ano); ?>">

<table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
  <tr>
	<td class="celdaFiltro">
      Acciones generales<br>
      <a href="<?php echo("$enlbase_sm=flujos_generales_crear"); ?>" id='sgu_fancybox_small' class='boton'>Crear Flujo Anual</a>
      <a href="<?php echo("$enlbase_sm=flujos_generales_categorias"); ?>" id='sgu_fancybox' class='boton'>Categorias</a>
      <a href="<?php echo("$enlbase_sm=flujos_generales_cat_grupos"); ?>" id='sgu_fancybox' class='boton'>Grupos <small>(SubTotales)</small></a>
    </td>
	<td class="celdaFiltro">
      Cambiar Año Flujo:<br>
      <select name='ano' onChange='submitform()' class='filtro'>
        <?php echo(select($ANOS_flujos,$ano)); ?>
      </select>
    </td>
  </tr>
</table>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr'>Año:</td>
    <td class='celdaValorAttr'><b><?php echo($flujo[0]['ano']." ".$flujo[0]['estado']); ?></b></td>
    <td class='celdaNombreAttr'>Creador:</td>
    <td class='celdaValorAttr'><?php echo($flujo[0]['creador']); ?></td>
    <td class='celdaNombreAttr'>Fec. Creación:</td>
    <td class='celdaValorAttr'><?php echo($flujo[0]['fec_creacion']); ?></td>
    <td class='celdaNombreAttr'>Fec. última mod.:</td>
    <td class='celdaValorAttr'><?php echo($flujo[0]['fec_mod']); ?></td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="8" style="text-align: justify">
      <div class='celdaNombreAttr' style='text-align: left; padding: 2px'>Comentarios:</div>
      <div style='text-align: justify; padding: 2px'><?php echo(nl2br($flujo[0]['comentarios'])); ?></div>
      <div style='text-align:right;'>
        <a href="<?php echo("$enlbase_sm=flujos_generales_editar&ano=$ano"); ?>" id='sgu_fancybox_small' class='boton'>Editar</a><br><br>
      </div>
    </td>
  </tr>
</table>
<table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
  <tr>
	<td class="celdaFiltro">
      <div style='margin-bottom: 3px'>Acciones del flujo:</div>
      <a href="<?php echo("$enlbase_sm=flujos_generales_agregar_cat_flujo&ano=$ano"); ?>" class="boton" id="sgu_fancybox_small">Añadir Item</a>
    </td>
	<td class="celdaFiltro">
      Mostrar valores:<br>
      <select name="divisor_valores" onChange="submitform();" class="filtro">
        <?php echo(select($DIVISORES,$divisor_valores)); ?>
      </select>
    </td>
  </tr>
</table>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='1' class='tabla' style='margin-top: 5px'>
  <colgroup>
    <col>
    <col>
	<?php for($x=0;$x<12;$x++) { $mes = substr($meses_palabra[$x]['nombre'],0,3);  echo("<col style='' id='$mes'>"); } ?>
	<col>
  </colgroup>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' rowspan="2">Categoría</td>
    <td class='tituloTabla' rowspan="2"><small>Monto<br>Presupuesto</small></td>
    <td class='tituloTabla' colspan="12" onClick="location.reload(true);">Flujo Año <?php echo($ano); ?></td>
    <td class='tituloTabla' rowspan="2">Total<br>Item</td>
    <td class='tituloTabla' rowspan="2">Presup.<br>Gastado</td>
  </tr>
  <tr class='filaTituloTabla'>
<?php

	for($x=0;$x<12;$x++) { 
		$mes = substr($meses_palabra[$x]['nombre'],0,3); 
		echo("<td class='tituloTabla' onClick=\"document.getElementById('$mes').style.background='linear-gradient(#FFFFDE,#ffff00)';\">$mes</td>");
	}

?>
  </tr>
  <?php echo($HTML); ?>
</table>
</form>
<img src="graficos/flujo_<?php echo($ano); ?>.png" style='border: 1px groove #4c8260'>
<!-- Fin: <?php echo($modulo); ?> -->

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 1000,
		'height'			: 400,
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
		'width'				: 600,
		'height'			: 550,
		'maxHeight'			: 550,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});
</script>
