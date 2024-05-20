<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");
include("libchart/classes/libchart.php");

$ano             = $_REQUEST['ano'];
$divisor_valores = $_REQUEST['divisor_valores'];
$id_carrera      = $_REQUEST['id_carrera'];
$jornada         = $_REQUEST['jornada'];
$id_regimen      = $_REQUEST['id_regimen'];
$dia_corte       = $_REQUEST['dia_corte'];
$mes_corte       = $_REQUEST['mes_corte'];

if ($divisor_valores == "") { $divisor_valores = 1000; }
if ($ano == "") { $ano = $ANO; }
if (empty($_REQUEST['id_regimen'])) { $id_regimen = "PRE,PRE-D,POST-G"; }

$enl_nav = "ano=$ano&divisor_valores=$divisor_valores&id_carrera=$id_carrera&jornada=$jornada&id_regimen=$id_regimen&dia_corte=$dia_corte&mes_corte=$mes_corte";

$cond_flujo = "";
if ($id_carrera <> "")  { $cond_flujo .= "AND (con.id_carrera=$id_carrera OR al.carrera_actual=$id_carrera)"; }
if ($jornada <> "")     { $cond_flujo .= "AND (con.jornada='$jornada' OR al.jornada='$jornada')"; }

if ($id_regimen <> "t") { $cond_flujo .= "AND (r.id IN ('".str_replace(",","','",$id_regimen)."'))"; }

if ($dia_corte > 0 && $mes_corte > 0) { $cond_flujo .= "AND p.fecha <= '$dia_corte-$mes_corte-$ano'::date"; }

$SQL_ingresos_desg = "SELECT r.nombre AS regimen,g.tipo,
                             coalesce(con.ano,date_part('year',cci.fecha),date_part('year',p.fecha)) AS ano_origen,
                             date_part('year',fecha_venc) as ano_venc,
                             date_part('month',p.fecha) as mes_pago,
                             sum(monto_pagado) AS monto
                      FROM finanzas.pagos p 
                      LEFT JOIN finanzas.pagos_detalle AS pd  ON id_pago=p.id 
                      LEFT JOIN finanzas.cobros        AS c   ON c.id=pd.id_cobro 
                      LEFT JOIN finanzas.glosas        AS g   ON g.id=id_glosa
                      LEFT JOIN finanzas.contratos     AS con ON con.id=id_contrato 
                      LEFT JOIN finanzas.convenios_ci  AS cci ON cci.id=c.id_convenio_ci
                      LEFT JOIN carreras               AS car ON car.id=con.id_carrera 
                      LEFT JOIN alumnos                AS al  ON al.id=c.id_alumno
                      LEFT JOIN alumnos                AS al2 ON al2.id=cci.id_alumno
                      LEFT JOIN carreras               AS ca2 ON ca2.id=al.carrera_actual 
                      LEFT JOIN carreras               AS ca3 ON ca3.id=al2.carrera_actual 
                      LEFT JOIN regimenes              AS r   ON r.id=coalesce(car.regimen,ca2.regimen,ca3.regimen)
                      WHERE NOT p.nulo $cond_flujo AND date_part('year',p.fecha) = $ano 
                      GROUP BY g.tipo,r.nombre,ano_origen,ano_venc,mes_pago
                      ORDER BY r.nombre DESC,g.tipo,ano_origen,ano_venc,mes_pago";
//echo($SQL_ingresos_desg);
$ingresos_desg = consulta_sql($SQL_ingresos_desg);
$y = 0;
for ($x=0;$x<count($ingresos_desg);$x++) {
	$tipo       = $ingresos_desg[$x]['tipo'];
	$regimen    = $ingresos_desg[$x]['regimen'];
	$ano_origen = $ingresos_desg[$x]['ano_origen'];
	$ano_venc   = $ingresos_desg[$x]['ano_venc'];
	
	$flujo_det_ingresos[$y]['id']          = 0;
	$flujo_det_ingresos[$y]['categoria']   = substr($tipo,1)." | $regimen | $ano_origen | con vcto en | $ano_venc";
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
$flujo_detalle = $flujo_det_ingresos;

$HTML = "";
$tipo_flujo = "I";
$tot_ingresos = $tot_gastos = $total = array();
$_totalizador = $flujo_detalle[0]['totalizador'];
$subtotal = array();
for ($x=0;$x<count($flujo_detalle);$x++) {
	extract($flujo_detalle[$x]);
	
	if ($_totalizador <> $flujo_detalle[$x]['totalizador']) {		
		$HTML .= "<tr>"
		      .  "  <td class='textoTabla' style='text-align: right; font-size: 8pt; vertical-align: middle'><b><i>SubTotal $_totalizador</i></b></td>";
		$tot_cat = 0;
		for ($mes=0;$mes<12;$mes++) { 
			$HTML .=  "  <td class='textoTabla' style='text-align: right; font-size: 8pt; vertical-align: middle'><b><i>".number_format(round($subtotal[$mes]/$divisor_valores,0),0,",",".")."</i></b></td>";
			$tot_cat += $subtotal[$mes];
		}
		
		$HTML .= "  <td class='textoTabla' style='text-align: right; font-size: 8pt; vertical-align: middle'><b><i>".number_format(round($tot_cat/$divisor_valores,0),0,",",".")."</i></b></td>"
		      .  "</tr>";
		
		$subtotal = array();
	}
	$_totalizador = $totalizador;
	
	if ($id > 0) {
		$categoria = "<span id='bo$x' style='visibility: hidden'>"
		           . "  <a href='$enlbase_sm=flujos_generales_editar_cat_flujo&id_cat_flujo=$id&ano=$ano' title='Editar Item' class='boton' id='sgu_fancybox_small'><small>✍</small></a> "
		           . "  <a href='#' onClick=\"if (confirm('Está seguro de eliminar el item $categoria?')) { window.location='$enlbase=flujos_generales&eliminar=Si&id_cat_flujo=$id&ano=$ano'; }\" title='Eliminar Item' class='boton'><small>✕</small></a>"
		           . "</span> $categoria";
	}
	
	$HTML .= "<tr class='filaTabla' onMouseOver=\"document.getElementById('bo$x').style.visibility='visible'\" onMouseOut=\"document.getElementById('bo$x').style.visibility='hidden'\">\n"
	      .  "  <td class='textoTabla' style='text-align: right; font-size: 8pt; vertical-align: middle'>$categoria</td>\n";
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
	      .  "</tr>\n";
	
	if ($tipo == "I") { $tot_ingresos[12] += $tot_categoria; $tot_ingresos[13] += $monto_presupuesto; }
	else { $tot_gastos[12] += $tot_categoria; $tot_categoria *= -1; $tot_gastos[13] += $monto_presupuesto; $monto_presupuesto *= -1;}
	$total[12] += $tot_categoria;
	$total[13] += $monto_presupuesto;	
}

$HTML .= "<tr>"
	  .  "  <td class='textoTabla' style='text-align: right; font-size: 8pt; vertical-align: middle'><b><i>SubTotal $_totalizador</i></b></td>";
$tot_cat = 0;
for ($mes=0;$mes<12;$mes++) { 
	$HTML .=  "  <td class='textoTabla' style='text-align: right; font-size: 8pt; vertical-align: middle'><b><i>".number_format(round($subtotal[$mes]/$divisor_valores,0),0,",",".")."</i></b></td>";
	$tot_cat += $subtotal[$mes];
}
$HTML .= "  <td class='textoTabla' style='text-align: right; font-size: 8pt; vertical-align: middle'><b><i>".number_format(round($tot_cat/$divisor_valores,0),0,",",".")."</i></b></td>"
	  .  "</tr>";

$totales['Total Ingresos'] = $tot_ingresos;

foreach ($totales AS $tipo => $meses) {

	if ($tipo == "Total Ingresos")   { $estilo = "color: #000099"; } 
	elseif ($tipo == "Total Gastos") { $estilo = "color: #990000"; }
	else { $estilo = ""; }
	
	$monto_presupuesto = number_format(round($meses[13]/$divisor_valores,0),0,",",".");
	if ($tipo <> "Saldo") { $monto_presupuesto = "<small>$monto_presupuesto</small>"; }
	$HTML .= "<tr><td class='textoTabla' colspan='16'>&nbsp;</td></tr>\n"
		  .  "<tr>\n"
		  .  "  <td class='celdaNombreAttr' style='$estilo'>$tipo:</td>\n";
	for ($mes=0;$mes<13;$mes++) {
		$valor_mes = number_format(round($meses[$mes]/$divisor_valores,0),0,",",".");
		$mes_nombre = substr($meses_palabra[$mes]['nombre'],0,3);

		if ($tipo <> "Saldo") { $valor_mes = "<small>$valor_mes</small>"; }

		$HTML .= "<td class='textoTabla' id='$mes_nombre' align='right' style='$estilo'><b>$valor_mes</b></td>\n";
	}
	
	$HTML .= "</tr>\n";

}

$DIVISORES = array(array('id'=>1      ,'nombre'=>"en pesos"),
                   array('id'=>1000   ,'nombre'=>"en miles de pesos"),
                   array('id'=>1000000,'nombre'=>"en millones de pesos"));

$ANOS_flujos = consulta_sql("SELECT ano AS id,ano||CASE WHEN activo THEN ' *' ELSE '' END AS nombre FROM finanzas.flujos ORDER BY ano DESC");

$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($id_regimen <> "t")      { $cond_carreras .= "AND regimen IN ('".str_replace(",","','",$id_regimen)."') "; }
$carreras = consulta_sql("SELECT id,nombre FROM carreras $cond_carreras ORDER BY nombre;");

$REGIMENES = consulta_sql("SELECT * FROM regimenes");
$REGIMENES = array_merge($REGIMENES,array(array('id'=>"PRE,PRE-D,POST-G",'nombre'=>"Pregrado (Pres. y Dist.) y Postgrado (Pres.)")),
                                    array(array('id'=>"SEM,DIP",'nombre'=>"Diplomados y Seminarios (Presencial)")),
                                    array(array('id'=>"POST-GD,POST-TD,DIP-D",'nombre'=>"MyP (a Distancia)"))
                        );

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
      Año Flujo:<br>
      <select name='ano' onChange='submitform()' class='filtro'>
        <?php echo(select($ANOS_flujos,$ano)); ?>
      </select>
    </td>
	<td class="celdaFiltro">
	  <div align='left'>Carrera/Programa:</div>
	  <select class="filtro" name="id_carrera" onChange="submitform();">
		<option value="">Todas</option>
		<?php echo(select($carreras,$id_carrera)); ?>    
	  </select>
	</td>
	<td class="celdaFiltro">
	  <div align='left'>Jornada:</div>
	  <select class="filtro" name="jornada" onChange="submitform();">
		<option value="">Ambas</option>
		<?php echo(select($JORNADAS,$jornada)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
	  <div align='left'>Régimen:</div>
	  <select class="filtro" name="id_regimen" onChange="submitform();">
		<option value="t">Todos</option>
		<?php echo(select($REGIMENES,$id_regimen)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
      Mostrar valores:<br>
      <select name="divisor_valores" onChange="submitform();" class="filtro">
        <?php echo(select($DIVISORES,$divisor_valores)); ?>
      </select>
    </td>
	<td class="celdaFiltro">
      Acciones:<br>
      <a href='<?php echo("$enlbase=flujos_generales_ingresos_resumen&$enl_nav"); ?>' class='boton'>Ver resumido</a>
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
    <td class='tituloTabla' colspan="12">
      Flujo Año <?php echo($ano); ?> al 
      <select class="filtro" name="dia_corte" onChange="submitform();" style='text-align: right' onClick="if (formulario.mes_corte.value=='') { var f=new Date(); formulario.mes_corte.value=f.getMonth()+1; }">
		<option value="">- Día -</option>
		<?php echo(select($dias_fn,$dia_corte)); ?>
	  </select> de 
      <select class="filtro" name="mes_corte" onChange="submitform();">
		<option value="" style='text-align: center'>- Mes -</option>
		<?php echo(select($meses_palabra,$mes_corte)); ?>
	  </select>
	</td>
    <td class='tituloTabla' rowspan="2">Total<br>Item</td>
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
