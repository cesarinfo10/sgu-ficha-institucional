<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$divisor_valores = $_REQUEST['divisor_valores'];
$id_carrera      = $_REQUEST['id_carrera'];
$jornada         = $_REQUEST['jornada'];
$id_regimen      = $_REQUEST['id_regimen'];
$dia_corte       = $_REQUEST['dia_corte'];
$mes_corte       = $_REQUEST['mes_corte'];
$dia_inicio      = $_REQUEST['dia_inicio'];
$mes_inicio      = $_REQUEST['mes_inicio'];
$ccss            = $_REQUEST['ccss'];
$nncc            = $_REQUEST['nncc'];
$ing_noop        = $_REQUEST['ing_noop'];
$id_tipo         = $_REQUEST['id_tipo'];
$id_tipo_excluir = $_REQUEST['id_tipo_excluir'];
$anos_flujo      = implode(",",$_REQUEST['anos_flujo']);

if ($divisor_valores == "") { $divisor_valores = 1000; }
if (empty($_REQUEST['id_regimen'])) { $id_regimen = "PRE,POST-G"; }
if (empty($_REQUEST['dia_corte'])) { $dia_corte = date("j"); }
if (empty($_REQUEST['mes_corte'])) { $mes_corte = date("n"); }
if (empty($_REQUEST['dia_inicio'])) { $dia_inicio = 1; }
if (empty($_REQUEST['mes_inicio'])) { $mes_inicio = 1; }
if (empty($_REQUEST['ccss'])) { $ccss = 'f'; }
if (empty($_REQUEST['nncc'])) { $nncc = 'f'; }
if (empty($_REQUEST['ing_noop'])) { $ing_noop = 'f'; }
if (empty($anos_flujo)) { $anos_flujo = date("Y")-1 . "," . date("Y"); }

$enl_nav = "ano=$ano&divisor_valores=$divisor_valores&id_carrera=$id_carrera&jornada=$jornada&id_regimen=$id_regimen&ccss=$ccss";

$cond_ccss = $cond_flujo = $cond_otros = $cond_nc = "";
if ($id_carrera <> "")  { $cond_flujo .= "AND (con.id_carrera=$id_carrera OR al.carrera_actual=$id_carrera)"; }
if ($jornada <> "")     { $cond_flujo .= "AND (con.jornada='$jornada' OR al.jornada='$jornada')"; }

if ($id_regimen <> "t") { $cond_flujo .= "AND (r.id IN ('".str_replace(",","','",$id_regimen)."'))"; }

$SQL_anos_pagos = "SELECT DISTINCT ON (date_part('year',fecha)) date_part('year',fecha) AS ano_pago 
                   FROM finanzas.pagos 
                   WHERE date_part('year',fecha) IN ($anos_flujo)
                   ORDER BY ano_pago";
$anos_pagos     = consulta_sql($SQL_anos_pagos);

$min_ano = min(array_column($anos_pagos,'ano_pago'));
$max_ano = max(array_column($anos_pagos,'ano_pago'));

if ($dia_corte > 0 && $mes_corte > 0) { 
	$cond_flujos = $cond_nc = " AND (";
	for ($x=0;$x<count($anos_pagos);$x++) {
		$ano_pago = $anos_pagos[$x]['ano_pago'];
		$fec_inicio = "$dia_inicio-$mes_inicio-$ano_pago";
		$fec_termino = "$dia_corte-$mes_corte-$ano_pago";

		if ($ano_pago%4 == 0 && $ano_pago%100 <> 0 || $ano_pago%400 == 0) {
			if ($mes_corte == 2 && $dia_corte > 29) {
				//si el año es biciesto
				$fec_termino = "29-2-$ano_pago";
			}
		} elseif ($mes_corte == 2 && $dia_corte > 28) {
			$fec_termino = "28-2-$ano_pago";
		}
		
		//if ($mes_corte == 2 && !((( $ano_pago%4 == 0 && $ano_pago%100 <> 0) || $ano_pago%400 == 0))) { $fec_termino = "28-2-$ano_pago"; }
		$cond_flujos .= "(p.fecha between '$fec_inicio'::date AND '$fec_termino'::date) OR ";
		$cond_nc     .= "(nc.fecha between '$fec_inicio'::date AND '$fec_termino'::date) OR ";
	}
	$cond_nc = $cond_flujo . substr($cond_nc,0,-4).")";
	$cond_flujo .= $cond_ccss = $cond_otros = substr($cond_flujos,0,-4).")";
}

$SQL_inoop_desg = "SELECT 'PRE' AS regimen,'5 '||p.glosa AS tipo,date_part('year',p.fecha) AS ano_origen,
                          date_part('year',p.fecha) as ano_venc,
                          date_part('year',p.fecha) as ano_pago,
                          sum(coalesce(efectivo,0)+coalesce(cheque,0)+coalesce(transferencia,0)) AS monto
                   FROM finanzas.otros_pagos AS p
                   WHERE true $cond_otros AND date_part('year',p.fecha) IN ($SQL_anos_pagos)
                   GROUP BY tipo,regimen,ano_origen,ano_venc,ano_pago
                   ORDER BY regimen DESC,tipo,ano_origen,ano_venc";
$inoop_desg = consulta_sql($SQL_inoop_desg);

$SQL_nc = "SELECT r.nombre AS regimen,'6 Devoluciones' AS tipo,
                  date_part('year',nc.fecha) AS ano_origen,
                  date_part('year',nc.fecha) as ano_venc,
                  date_part('year',nc.fecha) as ano_pago,
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
           WHERE c.id IS NOT NULL $cond_nc AND date_part('year',nc.fecha) IN ($SQL_anos_pagos) 
           GROUP BY g.tipo,r.nombre,ano_origen,ano_venc
           ORDER BY r.nombre DESC,tipo,ano_origen,ano_venc";
$nc = consulta_sql($SQL_nc);

$SQL_ccss_desg = "SELECT 'PRE' AS regimen,'4 Cuotas Sociales' AS tipo,c.ano AS ano_origen,
                         date_part('year',fecha_venc) as ano_venc,
                         date_part('year',p.fecha) as ano_pago,
                         sum(monto_pagado) AS monto
                  FROM finanzas.ccss_pagos AS p
                  LEFT JOIN finanzas.ccss_pagos_detalle AS pd  ON pd.id_pago=p.id
                  LEFT JOIN finanzas.ccss_cobros        AS cob ON cob.id=pd.id_cobro
                  LEFT JOIN finanzas.ccss_compromisos   AS c   ON c.id=cob.id_compromiso
                  WHERE true $cond_ccss AND date_part('year',p.fecha) IN ($SQL_anos_pagos)
                  GROUP BY tipo,regimen,ano_origen,ano_venc,ano_pago
                  ORDER BY regimen DESC,tipo,ano_origen,ano_venc";
$ccss_desg      = consulta_sql($SQL_ccss_desg);
//echo($SQL_ccss_desg);
//var_dump($ccss_desg);

$SQL_ingresos_desg = "SELECT r.nombre AS regimen,g.tipo,
                             coalesce(con.ano,date_part('year',p.fecha)) AS ano_origen,
                             date_part('year',fecha_venc) as ano_venc,
                             date_part('year',p.fecha) as ano_pago,
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
                      WHERE NOT p.nulo AND c.id IS NOT NULL $cond_flujo AND date_part('year',p.fecha) IN ($SQL_anos_pagos)
                      GROUP BY g.tipo,r.nombre,ano_origen,ano_venc,ano_pago
                      ORDER BY ano_pago,r.nombre DESC,g.tipo,ano_origen,ano_venc";
$ingresos_desg = consulta_sql($SQL_ingresos_desg);

//$ingresos_desg = array_merge($ingresos_desg,$otros_desg);
if (count($ccss_desg) > 0 && $ccss == "t") { $ingresos_desg = array_merge($ingresos_desg,$ccss_desg); }
if (count($inoop_desg) > 0 && $ing_noop == "t") { $ingresos_desg = array_merge($ingresos_desg,$inoop_desg); }
if (count($nc) > 0 && $nncc == "t" ) { $ingresos_desg = array_merge($ingresos_desg,$nc); }

//echo("<!-- $SQL_ingresos_desg -->!");
$flujo_detalle = $ingresos_desg;
//print_r($flujo_detalle);


$flujo_resumen = array();
$y=0;
for ($z=0;$z<count($anos_pagos);$z++) {
	$ano = $anos_pagos[$z]['ano_pago'];
	unset($Categorias);
	//$Categorias = categorias($ano);
	$Categorias = CategoriasFlujosIngresos($ano,$id_tipo,$id_tipo_excluir);
	//print_r($Categorias);
	//print_r($flujo_resumen);
	for ($x=0;$x<count($Categorias);$x++) {
		extract($Categorias[$x]);
		$flujo_resumen[$y]['ano'] = $ano;
		$flujo_resumen[$y]['categoria'] = "$tipo $nombre";
		$flujo_resumen[$y]['totalizador'] = $totalizador;
		$flujo_resumen[$y]['monto'] = 0;
		for ($j=0;$j<count($flujo_detalle);$j++) {
			if ($ano == $flujo_detalle[$j]['ano_pago'] && $tipo == substr($flujo_detalle[$j]['tipo'],2)
			 && $ano_origen_min <= $flujo_detalle[$j]['ano_origen'] && $ano_origen_max >= $flujo_detalle[$j]['ano_origen'] 
			 && $ano_venc_min <= $flujo_detalle[$j]['ano_venc'] && $ano_venc_max >= $flujo_detalle[$j]['ano_venc']) {

				$flujo_resumen[$y]['monto'] += $flujo_detalle[$j]['monto'];

			}
		}
		$y++;
	}
}

//print_r($Categorias);
$j=0;
$flujo_comparativo = array();
for ($y=0;$y<count($Categorias);$y++) {
	extract($Categorias[$y]);
	$flujo_comparativo[$j]['categoria']   = "$tipo $nombre";
	$flujo_comparativo[$j]['totalizador'] = $totalizador;
	$flujo_comparativo[$j]['montos']      = array();	
	for ($z=0;$z<count($anos_pagos);$z++) {
		$ano = $anos_pagos[$z]['ano_pago'];
		for ($x=0;$x<count($flujo_resumen);$x++) {
			if ($flujo_comparativo[$j]['categoria'] == $flujo_resumen[$x]['categoria'] && $ano == $flujo_resumen[$x]['ano']) {
				$flujo_comparativo[$j]['montos'] = array_merge($flujo_comparativo[$j]['montos'],array($ano => $flujo_resumen[$x]['monto']));	
			}
		}
	}
	$j++;
}


//$flujo_resumen = flipDiagonally($flujo_resumen);

//print_r($flujo_comparativo);

$HTML = "";
$tipo_flujo = "I";
$tot_ingresos = $total = array();
$_totalizador = $flujo_comparativo[0]['totalizador'];
$subtotal = array();
for ($x=0;$x<count($flujo_comparativo);$x++) {
	extract($flujo_comparativo[$x]);
	
	$bgcolor = "";
	if ($ccss == "t" && is_numeric(strpos($categoria,"Cuotas Sociales"))) { $bgcolor = "bgcolor='#FFFF00'"; }
	
	if ($_totalizador <> $flujo_comparativo[$x]['totalizador']) {		
		$HTML .= "<tr>"
		      .  "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><b><i>SubTotal $_totalizador</i></b></td>";
		for ($z=0;$z<count($anos_pagos);$z++) {
			$ano = $anos_pagos[$z]['ano_pago'];
			$HTML .=  "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><b><i>".number_format(round($subtotal[$ano]/$divisor_valores,0),0,",",".")."</i></b></td>";
			//$tot_cat += $subtotal[$mes];
		}
		
//		$HTML .= "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><b><i>".number_format(round($tot_cat/$divisor_valores,0),0,",",".")."</i></b></td>";
		$HTML .= "</tr>";
		
		$subtotal = array();
	}
	$_totalizador = $totalizador;
	
	$HTML .= "<tr $bgcolor class='filaTabla' >\n"
	      .  "  <td class='textoTabla' style='text-align: right; vertical-align: middle'>$categoria</td>\n";
	$montos_anuales = $montos;

	$tot_categoria = 0;
	for ($z=0;$z<count($anos_pagos);$z++) {
		$ano = $anos_pagos[$z]['ano_pago'];
		$valor_ano = number_format(round($montos_anuales[$z]/$divisor_valores,0),0,",",".");
		$HTML .= "  <td class='textoTabla' style='text-align: right;; vertical-align: middle'>$valor_ano</td>\n";
		$tot_ingresos[$ano] += $montos_anuales[$z];
		$total[$ano] += $montos_anuales[$z];
		$subtotal[$ano] += $montos_anuales[$z];
	}
	
//	$HTML .= "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><b>".number_format(round($tot_categoria/$divisor_valores,0),0,",",".")."</b></td>"
	$HTML .=  "</tr>\n";
	
}

$HTML .= "<tr>"
	  .  "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><b><i>SubTotal $_totalizador</i></b></td>";
for ($z=0;$z<count($anos_pagos);$z++) {
	$ano = $anos_pagos[$z]['ano_pago'];
	$HTML .=  "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><b><i>".number_format(round($subtotal[$ano]/$divisor_valores,0),0,",",".")."</i></b></td>";
}
//$HTML .= "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><b><i>".number_format(round($tot_cat/$divisor_valores,0),0,",",".")."</i></b></td>"
$HTML .=  "</tr>";

$totales['Total Ingresos'] = $tot_ingresos;

foreach ($totales AS $tipo => $anos) {

	if ($tipo == "Total Ingresos")   { $estilo = "color: #000099"; } 
	elseif ($tipo == "Total Gastos") { $estilo = "color: #990000"; }
	else { $estilo = ""; }
	
	$HTML .= "<tr><td class='textoTabla' colspan='16'>&nbsp;</td></tr>\n"
		  .  "<tr>\n"
		  .  "  <td class='celdaNombreAttr' style='$estilo'>$tipo:</td>\n";
	for ($z=0;$z<count($anos_pagos);$z++) {
		$ano = $anos_pagos[$z]['ano_pago'];
		$valor_ano = number_format(round($anos[$ano]/$divisor_valores,0),0,",",".");

		if ($tipo <> "Saldo") { $valor_ano = "$valor_ano"; }

		$HTML .= "<td class='textoTabla' align='right' style='$estilo'><b>$valor_ano</b></td>\n";
	}
	
	$HTML .= "</tr>\n";

}


$DIVISORES = array(array('id'=>1      ,'nombre'=>"en pesos"),
                   array('id'=>1000   ,'nombre'=>"en miles de pesos"),
                   array('id'=>1000000,'nombre'=>"en millones de pesos"));

//$ANOS_flujos = consulta_sql("SELECT ano AS id,ano||CASE WHEN activo THEN ' *' ELSE '' END AS nombre FROM finanzas.flujos ORDER BY ano DESC");

$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($id_regimen <> "t")      { $cond_carreras .= "AND regimen IN ('".str_replace(",","','",$id_regimen)."') "; }
$carreras = consulta_sql("SELECT id,nombre FROM carreras $cond_carreras ORDER BY nombre;");

$REGIMENES = consulta_sql("SELECT * FROM regimenes");
$REGIMENES = array_merge($REGIMENES,array(array('id'=>"PRE,PRE-D,POST-G",'nombre'=>"Pregrado (Pres. y Dist.) y Postgrado (Pres.)")),
                                    array(array('id'=>"SEM,DIP",'nombre'=>"Diplomados y Seminarios (Presencial)")),
                                    array(array('id'=>"POST-GD,POST-TD,DIP-D",'nombre'=>"MyP (a Distancia)"))
                        );

$anos_flujo = explode(",",$anos_flujo);

$ANOS_flujos = consulta_sql("SELECT ano AS id,ano||CASE WHEN activo THEN ' *' ELSE '' END AS nombre FROM finanzas.flujos ORDER BY ano");
$HTML_anos = "";
for ($x=0;$x<count($ANOS_flujos);$x++) {
	$checked = "";
	$ano = $ANOS_flujos[$x]['id'];
	if (in_array($ano,$anos_flujo)) { $checked = "checked='checked'"; }
	$HTML_anos .= "<input style='vertical-align: bottom;' type='checkbox' name='anos_flujo[]' value='$ano' id='$ano' onChange='submitform();' $checked> <label for='$ano'>$ano</label>&nbsp;&nbsp;";
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<form name="formulario" action="principal_sm.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="ano" value="<?php echo($ano); ?>">
<input type="hidden" name="id_tipo" value="<?php echo($id_tipo); ?>">
<input type="hidden" name="id_tipo_excluir" value="<?php echo($id_tipo_excluir); ?>">

<table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
	<td class="celdaFiltro" colspan="10">
      Años:<br>
      <div style='vertical-align: top'><?php echo($HTML_anos); ?></div>
    </td>
  </tr>
  <tr>
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
      Mostrar valores:<br>
      <select name="divisor_valores" onChange="submitform();" class="filtro">
        <?php echo(select($DIVISORES,$divisor_valores)); ?>
      </select>
    </td>
  </tr>
</table>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='1' class='tabla' style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="<?php echo(count($anos_pagos)+1); ?>">
      Flujos de Ingreso de los Años <?php echo($min_ano.' ~ '.$max_ano); ?>      
      <div style='font-weight: normal'>(desde
      <select class="filtro" name="dia_inicio" onChange="submitform();" style='text-align: right' onClick="if (formulario.mes_inicio.value=='') { var f=new Date(); formulario.mes_inicio.value=f.getMonth()+1; }">
		<option value="">Día</option>
		<?php echo(select($dias_fn,$dia_inicio)); ?>
	  </select> de 	
      <select class="filtro" name="mes_inicio" onChange="submitform();">
		<option value="" style='text-align: center'>-- Mes --</option>
		<?php echo(select($meses_palabra,$mes_inicio)); ?>
	  </select>
      al 
      <select class="filtro" name="dia_corte" onChange="submitform();" style='text-align: right' onClick="if (formulario.mes_corte.value=='') { var f=new Date(); formulario.mes_corte.value=f.getMonth()+1; }">
		<option value="">Día</option>
		<?php echo(select($dias_fn,$dia_corte)); ?>
	  </select> de 	
      <select class="filtro" name="mes_corte" onChange="formulario.dia_corte.value=daysInMonth(this.value); submitform();">
		<option value="" style='text-align: center'>-- Mes --</option>
		<?php echo(select($meses_palabra,$mes_corte)); ?>
	  </select> de cada año)</div> 
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Categoría</td>
    <?php for($x=0;$x<count($anos_pagos);$x++) { echo("<td class='tituloTabla'>{$anos_pagos[$x]['ano_pago']}</td>\n"); } ?>
  </tr>
  <?php echo($HTML); ?>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

<?php

?>
