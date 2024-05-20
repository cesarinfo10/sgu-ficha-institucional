<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");
//include("libchart/classes/libchart.php");

$ano             = $_REQUEST['ano'];
$divisor_valores = $_REQUEST['divisor_valores'];
$id_carrera      = $_REQUEST['id_carrera'];
$jornada         = $_REQUEST['jornada'];
$id_regimen      = $_REQUEST['id_regimen'];
$dia_corte       = $_REQUEST['dia_corte'];
$mes_corte       = $_REQUEST['mes_corte'];
$dia_tope        = $_REQUEST['dia_tope'];
$ccss            = $_REQUEST['ccss'];
$nncc            = $_REQUEST['nncc'];
$ing_noop        = $_REQUEST['ing_noop'];
$id_tipo         = $_REQUEST['id_tipo'];
$id_tipo_excluir = $_REQUEST['id_tipo_excluir'];

if ($_SESSION['tipo'] > 1 && $id_tipo == "") {
	echo(msje_js("Este módulo no puede ejecutarse sin un tipo específico de Ingresos. Esto ocurre debido a que fue abierto desde el menú lateral"));
	header("Location: index.php");
	exit;	
}

if ($divisor_valores == "") { $divisor_valores = 1000; }
if ($ano == "") { $ano = date("Y"); }
if (empty($_REQUEST['id_regimen'])) { $id_regimen = "PRE,PRE-D,POST-G"; }
if (empty($_REQUEST['dia_corte'])) { $dia_corte = date("j"); }
if (empty($_REQUEST['mes_corte'])) { $mes_corte = date("n"); }
if (empty($_REQUEST['ccss'])) { $ccss = 'f'; }
if (empty($_REQUEST['nncc'])) { $nncc = 'f'; }
if (empty($_REQUEST['ing_noop'])) { $ing_noop = 'f'; }
if ($ano < date("Y") && $_REQUEST['ano'] == "") { $dia_corte=31; $mes_corte=12; }

$enl_nav = "ano=$ano&divisor_valores=$divisor_valores&id_carrera=$id_carrera&jornada=$jornada&id_regimen=$id_regimen&dia_corte=$dia_corte&mes_corte=$mes_corte&ccss=$ccss&ing_noop=$ing_noop&nncc=$nncc&id_tipo=$id_tipo&id_tipo_excluir=$id_tipo_excluir";

$cond_ccss = $cond_otros = $cond_nc = $cond_flujo = "";
if ($id_carrera <> "")  { $cond_flujo .= " AND (con.id_carrera=$id_carrera OR al.carrera_actual=$id_carrera)"; }
if ($jornada <> "")     { $cond_flujo .= " AND (con.jornada='$jornada' OR al.jornada='$jornada')"; }

if ($id_regimen <> "t") { $cond_flujo .= " AND (r.id IN ('".str_replace(",","','",$id_regimen)."'))"; }

if ($dia_corte > 0 && $mes_corte > 0) { 
	$cond_nc = $cond_flujo . " AND nc.fecha <= '$dia_corte-$mes_corte-$ano'::date"; 
	$cond_flujo .= $cond_ccss = $cond_otros = " AND p.fecha <= '$dia_corte-$mes_corte-$ano'::date"; 
}

if (!empty($dia_tope)) { $cond_flujo .= " AND date_part('day',p.fecha) BETWEEN split_part('$dia_tope','-',1)::int2 AND split_part('$dia_tope','-',2)::int2"; }

$SQL_inoop_desg = "SELECT 'PRE' AS regimen,'5 '||p.glosa AS tipo,date_part('year',p.fecha) AS ano_origen,
                          date_part('year',p.fecha) as ano_venc,
                          date_part('month',p.fecha) as mes_pago,
                          sum(coalesce(efectivo,0)+coalesce(cheque,0)+coalesce(transferencia,0)) AS monto
                   FROM finanzas.otros_pagos AS p
                   WHERE true $cond_otros AND date_part('year',p.fecha) = $ano
                   GROUP BY tipo,regimen,ano_origen,ano_venc,mes_pago
                   ORDER BY regimen DESC,tipo,ano_origen,ano_venc,mes_pago";
$inoop_desg = consulta_sql($SQL_inoop_desg);

$SQL_nc = "SELECT r.nombre AS regimen,'6 Devoluciones' AS tipo,
                  date_part('year',nc.fecha) AS ano_origen,
                  date_part('year',nc.fecha) as ano_venc,
                  date_part('month',nc.fecha) as mes_pago,
                  sum(ncd.monto)*-1 AS monto
           FROM finanzas.notas_credito nc 
           LEFT JOIN finanzas.notas_credito_detalle AS ncd  ON ncd.nro_nc_docto=nc.nro_docto 
           LEFT JOIN finanzas.cobros        AS c   ON c.id=ncd.id_cobro 
           LEFT JOIN finanzas.glosas        AS g   ON g.id=c.id_glosa
           LEFT JOIN finanzas.contratos     AS con ON con.id=id_contrato 
           LEFT JOIN finanzas.convenios_ci  AS cci ON cci.id=id_convenio_ci 
           LEFT JOIN carreras               AS car ON car.id=con.id_carrera 
           LEFT JOIN alumnos                AS al  ON al.id=c.id_alumno
           LEFT JOIN alumnos                AS al3 ON al3.id=cci.id_alumno
           LEFT JOIN carreras               AS ca2 ON ca2.id=al.carrera_actual
           LEFT JOIN carreras               AS ca3 ON ca3.id=al3.carrera_actual
           LEFT JOIN regimenes              AS r   ON r.id=coalesce(car.regimen,ca2.regimen,ca3.regimen)
           WHERE c.id IS NOT NULL $cond_nc AND date_part('year',nc.fecha) = $ano  
           GROUP BY g.tipo,r.nombre,ano_origen,ano_venc,mes_pago
           ORDER BY r.nombre DESC,tipo,ano_origen,ano_venc,mes_pago";
$nc = consulta_sql($SQL_nc);

$SQL_ccss_desg = "SELECT 'PRE' AS regimen,'4 Cuotas Sociales' AS tipo,c.ano AS ano_origen,
                         date_part('year',fecha_venc) as ano_venc,
                         date_part('month',p.fecha) as mes_pago,
                         sum(monto_pagado) AS monto
                  FROM finanzas.ccss_pagos AS p
                  LEFT JOIN finanzas.ccss_pagos_detalle AS pd  ON pd.id_pago=p.id
                  LEFT JOIN finanzas.ccss_cobros        AS cob ON cob.id=pd.id_cobro
                  LEFT JOIN finanzas.ccss_compromisos   AS c   ON c.id=cob.id_compromiso
                  WHERE true $cond_ccss AND date_part('year',p.fecha) = $ano  
                  GROUP BY tipo,regimen,ano_origen,ano_venc,mes_pago
                  ORDER BY regimen DESC,tipo,ano_origen,ano_venc,mes_pago";
$ccss_desg      = consulta_sql($SQL_ccss_desg);

$SQL_ingresos_desg = "SELECT r.nombre AS regimen,g.tipo,
                             coalesce(con.ano,date_part('year',p.fecha)) AS ano_origen,
                             date_part('year',fecha_venc) as ano_venc,
                             date_part('month',p.fecha) as mes_pago,
                             sum(monto_pagado) AS monto
                      FROM finanzas.pagos p 
                      LEFT JOIN finanzas.pagos_detalle AS pd  ON id_pago=p.id 
                      LEFT JOIN finanzas.cobros        AS c   ON c.id=pd.id_cobro 
                      LEFT JOIN finanzas.glosas        AS g   ON g.id=id_glosa
                      LEFT JOIN finanzas.contratos     AS con ON con.id=id_contrato 
                      LEFT JOIN finanzas.convenios_ci  AS cci ON cci.id=id_convenio_ci 
                      LEFT JOIN carreras               AS car ON car.id=con.id_carrera 
                      LEFT JOIN alumnos                AS al  ON al.id=c.id_alumno
                      LEFT JOIN alumnos                AS al3 ON al3.id=cci.id_alumno
                      LEFT JOIN carreras               AS ca2 ON ca2.id=al.carrera_actual
                      LEFT JOIN carreras               AS ca3 ON ca3.id=al3.carrera_actual
                      LEFT JOIN regimenes              AS r   ON r.id=coalesce(car.regimen,ca2.regimen,ca3.regimen)
                      WHERE NOT p.nulo AND pd.monto_pagado <> 0 AND c.id IS NOT NULL $cond_flujo AND date_part('year',p.fecha) = $ano  
                      GROUP BY g.tipo,r.nombre,ano_origen,ano_venc,mes_pago
                      ORDER BY r.nombre DESC,g.tipo,ano_origen,ano_venc,mes_pago";
$ingresos_desg = consulta_sql($SQL_ingresos_desg);

echo("<!-- $SQL_ingresos_desg -->");
//$ingresos_desg = array_merge($ingresos_desg,$otros_desg);
if (count($ccss_desg) > 0 && $ccss == "t") { $ingresos_desg = array_merge($ingresos_desg,$ccss_desg); }
if (count($inoop_desg) > 0 && $ing_noop == "t") { $ingresos_desg = array_merge($ingresos_desg,$inoop_desg); }
if (count($nc) > 0 && $nncc == "t" ) { $ingresos_desg = array_merge($ingresos_desg,$nc); }

$y = 0;
for ($x=0;$x<count($ingresos_desg);$x++) {
	$tipo       = $ingresos_desg[$x]['tipo'];
	$regimen    = $ingresos_desg[$x]['regimen'];
	$ano_origen = $ingresos_desg[$x]['ano_origen'];
	$ano_venc   = $ingresos_desg[$x]['ano_venc'];
	
	$flujo_det_ingresos[$y]['id']          = 0;
	$flujo_det_ingresos[$y]['categoria']   = substr($tipo,2)." $regimen $ano_origen con vcto en $ano_venc";
	$flujo_det_ingresos[$y]['totalizador'] = "Caja $regimen";
	$flujo_det_ingresos[$y]['tipo']        = "I";
	$flujo_det_ingresos[$y]['ing_tipo']       = substr($tipo,2);
	$flujo_det_ingresos[$y]['ing_regimen']    = $regimen;
	$flujo_det_ingresos[$y]['ing_ano_origen'] = $ano_origen;
	$flujo_det_ingresos[$y]['ing_ano_venc']   = $ano_venc;
	$montos_ingresos = array();
	for ($mes=1;$mes<=12;$mes++) {
		//var_dump($montos_ingresos);
		$montos_ingresos[$mes] = 0;
		
		if ($mes == $ingresos_desg[$x]['mes_pago'] && 
		    $ingresos_desg[$x]['monto'] <> 0 && 
			$ingresos_desg[$x]['regimen'] == $regimen && 
			$ingresos_desg[$x]['tipo'] == $tipo && 
			$ingresos_desg[$x]['ano_origen'] == $ano_origen && 
			$ingresos_desg[$x]['ano_venc'] == $ano_venc)
		{			
			$montos_ingresos[$mes] = $ingresos_desg[$x]['monto'];
			$x++;
		}
	}
	//var_dump($flujo_det_ingresos);
	$flujo_det_ingresos[$y]['montos'] = $montos_ingresos;
	$y++;
	$x--;
}
$flujo_detalle = $flujo_det_ingresos;
//print_r($flujo_detalle);

$Categorias = CategoriasFlujosIngresos($ano,$id_tipo,$id_tipo_excluir);

//print_r($Categorias);
$flujo_resumen = array();
$z = 0;
for ($x=0;$x<count($Categorias);$x++) {
	extract($Categorias[$x]);
	$flujo_resumen[$z]['categoria'] = "$tipo $nombre";
	$flujo_resumen[$z]['tipo'] = "I";
	$flujo_resumen[$z]['totalizador'] = $totalizador;
	$flujo_resumen[$z]['montos'] = array();

	for ($y=0;$y<count($flujo_detalle);$y++) {
		$regimen = $flujo_detalle[$y]['ing_regimen'];

		if ($tipo == $flujo_detalle[$y]['ing_tipo'] 
		 && $ano_origen_min <= $flujo_detalle[$y]['ing_ano_origen'] && $ano_origen_max >= $flujo_detalle[$y]['ing_ano_origen'] 
		 && $ano_venc_min <= $flujo_detalle[$y]['ing_ano_venc'] && $ano_venc_max >= $flujo_detalle[$y]['ing_ano_venc']) {
			 
			$flujo_resumen[$z]['montos'] = array_sum_values($flujo_detalle[$y]['montos'],$flujo_resumen[$z]['montos']);
			
		}
	}
	$z++;
}

$flujo_detalle = $flujo_resumen;

$HTML = "";
$tipo_flujo = "I";
$tot_ingresos = $tot_gastos = $total = array();
$_totalizador = $flujo_detalle[0]['totalizador'];
$subtotal = array();
for ($x=0;$x<count($flujo_detalle);$x++) {
	extract($flujo_detalle[$x]);
	
	$bgcolor = "";
	if ($ccss == "t" && is_numeric(strpos($categoria,"Cuotas Sociales"))) { $bgcolor = "bgcolor='#FFFF00'"; }
	//if ($ing_noop == "t" && is_numeric(strpos($categoria,"Cuotas Sociales"))) { $bgcolor = "bgcolor='#FFFF00'"; }
	
	if ($_totalizador <> $flujo_detalle[$x]['totalizador']) {		
		$HTML .= "<tr>"
		      .  "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><b><i>SubTotal $_totalizador</i></b></td>";
		$tot_cat = 0;
		for ($mes=1;$mes<=12;$mes++) { 
			$HTML .=  "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><b><i>".number_format(round($subtotal[$mes]/$divisor_valores,0),0,",",".")."</i></b></td>";
			$tot_cat += $subtotal[$mes];
		}
		
		$HTML .= "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><b><i>".number_format(round($tot_cat/$divisor_valores,0),0,",",".")."</i></b></td>"
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
	
	$HTML .= "<tr $bgcolor class='filaTabla' onMouseOver=\"document.getElementById('bo$x').style.visibility='visible'\" onMouseOut=\"document.getElementById('bo$x').style.visibility='hidden'\">\n"
	      .  "  <td class='textoTabla' style='text-align: right; vertical-align: middle'>$categoria</td>\n";
	$montos_mesuales = $montos;
	$comentarios_mesuales = explode(",",str_replace(array("{","}"),"",str_replace("\"","",$comentarios)));
	$subtotal['ppto'] += $monto_presupuesto;

	$tot_categoria = 0;
	for ($mes=1;$mes<=12;$mes++) {
		$valor_mes = number_format(round($montos_mesuales[$mes]/$divisor_valores,0),0,",",".");
		if (!empty($comentarios_mesuales[$mes])) {
			$valor_mes = "<div title='header=[Comentarios] fade=[on] body=[{$comentarios_mesuales[$mes]}]' style='background: #BFE4BF; border-radius: 25px; padding: 0px 2px 0px 2px'>$valor_mes</div>";
		}
		$mes_nombre = substr($meses_palabra[$mes]['nombre'],0,3);
		$HTML .= "  <td class='textoTabla' id='$mes_nombre' style='text-align: right; vertical-align: middle'>$valor_mes</td>\n";
		$tot_categoria += $montos_mesuales[$mes];
		if ($tipo == "I") { $tot_ingresos[$mes] += $montos_mesuales[$mes]; }
		else { $tot_gastos[$mes] += $montos_mesuales[$mes]; $montos_mesuales[$mes] *= -1; }
		$total[$mes] += $montos_mesuales[$mes];
		$subtotal[$mes] += $montos_mesuales[$mes];		
	}
	
	$HTML .= "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><b>".number_format(round($tot_categoria/$divisor_valores,0),0,",",".")."</b></td>"
	      .  "</tr>\n";
	
	if ($tipo == "I") { $tot_ingresos[13] += $tot_categoria; }
	else { $tot_gastos[13] += $tot_categoria; $tot_categoria *= -1; }
	$total[13] += $tot_categoria;
}

$HTML .= "<tr>"
	  .  "  <td class='textoTabla' style='text-align: right;  vertical-align: middle'><b><i>SubTotal $_totalizador</i></b></td>";
$tot_cat = 0;
for ($mes=1;$mes<=12;$mes++) { 
	$HTML .=  "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><b><i>".number_format(round($subtotal[$mes]/$divisor_valores,0),0,",",".")."</i></b></td>";
	$tot_cat += $subtotal[$mes];
}
$HTML .= "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><b><i>".number_format(round($tot_cat/$divisor_valores,0),0,",",".")."</i></b></td>"
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
	for ($mes=1;$mes<=13;$mes++) {
		$valor_mes = number_format(round($meses[$mes]/$divisor_valores,0),0,",",".");
		$mes_nombre = substr($meses_palabra[$mes]['nombre'],0,3);

		if ($tipo <> "Saldo") { $valor_mes = "$valor_mes"; }

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

$DIAS_TOPE = array(array('id'=>"1-5",   'nombre'=>"1 al 5"),
                   array('id'=>"1-15",  'nombre'=>"1 al 15"),
                   array('id'=>"1-31",  'nombre'=>"1 al 31"),
                   array('id'=>"6-15",  'nombre'=>"6 al 15"),
                   array('id'=>"16-31", 'nombre'=>"16 al 31"));
                   
$TIPOS_EXCLUIR = array(array('id' => "Matrículas",        'nombre' => "Matrículas"),
                       array('id' => "Aranceles",         'nombre' => "Aranceles"),
                       array('id' => "Cuotas Sociales",   'nombre' => "Cuotas Sociales"),
                       array('id' => "Créditos Internos", 'nombre' => "Créditos Internos"),
                       array('id' => "InoOP",             'nombre' => "Ingresos No Operacionales"),
                       array('id' => "Otros Ingresos",    'nombre' => "Otros Ingresos"));
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<form name="formulario" action="<?php echo($_SERVER['script_name']); ?>" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_tipo" value="<?php echo($id_tipo); ?>">
<input type="hidden" name="id_tipo_excluir" value="<?php echo($id_tipo_excluir); ?>">
<!-- <input type="hidden" name="ano" value="<?php echo($ano); ?>"> -->

<table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
  <tr>
	<td class="celdaFiltro">
      Año Flujo:<br>
      <select name='ano' onChange='formulario.mes_corte.value=12;formulario.dia_corte.value=daysInMonth(12);submitform()' class='filtro'>
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
      CC.SS.:<br>
      <select name="ccss" onChange="submitform();" class="filtro">
        <?php echo(select($sino,$ccss)); ?>
      </select>
    </td>
	<td class="celdaFiltro">
      Ing. No Op.:<br>
      <select name="ing_noop" onChange="submitform();" class="filtro">
        <?php echo(select($sino,$ing_noop)); ?>
      </select>
    </td>
	<td class="celdaFiltro">
      N.C.:<br>
      <select name="nncc" onChange="submitform();" class="filtro">
        <?php echo(select($sino,$nncc)); ?>
      </select>
    </td>
	<td class="celdaFiltro">
      Rango de días:<br>
      <select name="dia_tope" onChange="submitform();" class="filtro">
        <option value="">Todos</option>
        <?php echo(select($DIAS_TOPE,$dia_tope)); ?>
      </select>
    </td>
  </tr>
</table>
<table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
  <tr>
  	<td class="celdaFiltro">
      Acciones:<br>
      <a href='<?php echo("$enlbase=flujos_generales_ingresos&$enl_nav"); ?>' class='boton'>Ver desglosado</a>
      <a id="sgu_fancybox" href='<?php echo("$enlbase_sm=flujos_generales_ingresos_comparativo_anual&$enl_nav"); ?>' class='boton'>Ver comparativo anual</a>
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
    <td class='tituloTabla' colspan="13">
      Flujo Año <?php echo($ano); ?> al 
      <select class="filtro" name="dia_corte" onChange="submitform();" style='text-align: right' onClick="if (formulario.mes_corte.value=='') { var f=new Date(); formulario.mes_corte.value=f.getMonth()+1; }">
		<option value="">Día</option>
		<?php echo(select($dias_fn,$dia_corte)); ?>
	  </select> de 	
      <select class="filtro" name="mes_corte" onChange="formulario.dia_corte.value=daysInMonth(this.value); submitform();">
		<option value="" style='text-align: center'>-- Mes --</option>
		<?php echo(select($meses_palabra,$mes_corte)); ?>
	  </select>
	</td>
    <td class='tituloTabla' rowspan="2">Total<br>Item</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Categoría</td>
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
		'width'				: 1300,
		'height'			: 700,
		'maxHeight'			: 700,
		'afterClose'		: function () { location.reload(true); },
		'scrolling'         : 'yes',
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

<?php

function sumar_arr($a, $b) {
	for ($x=0;$x<count($a);$x++) { $sum[$x] = $a[$x] + $b[$x]; }
    return $sum;
}

?>
