<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");
include("libchart/classes/libchart.php");

$ano                 = $_REQUEST['ano'];
$divisor_valores     = $_REQUEST['divisor_valores'];
$fec_valor_uf_presup = $_REQUEST['fec_valor_uf_presup'];
$nivel_info          = $_REQUEST['nivel_info'];
$mes_corte           = $_REQUEST['mes_corte'];
$totz_exc1           = $_REQUEST['totz_exc1'];
$totz_exc2           = $_REQUEST['totz_exc2'];
$totz_exc3           = $_REQUEST['totz_exc3'];
$totz_exc4           = $_REQUEST['totz_exc4'];
$totz_exc5           = $_REQUEST['totz_exc5'];

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
		echo(msje_js("ERROR: No existen flujos creados. Podrá crear uno luego de pinchar en «Aceptar»"));
		echo(js("window.location='$enlbase=flujos_generales_crear'"));
		exit;
	}
}

if ($divisor_valores == "") { $divisor_valores = 1000; }
if ($nivel_info == "") { $nivel_info = 2; }
if (empty($fec_valor_uf_presup)) { $fec_valor_uf_presup = date('Y-m-d'); }
if (empty($_REQUEST['mes_corte'])) { $mes_corte = date("n")-1; }

$id_fd = $_REQUEST['id_fd'];
if ($_REQUEST['eliminar'] == "Si" && $id_fd > 0 && $ano > 0) {
	if ($_REQUEST['conf'] <> "Eliminar") {
		$SQL_flujo_detalle = "SELECT fc.nombre AS categoria,montos 
		                      FROM finanzas.flujos_detalle AS fd 
		                      LEFT JOIN finanzas.flujos_categorias AS fc ON fc.id=fd.id_cat_flujo
		                      WHERE fd.id=$id_fd AND fd.ano_flujo=$ano";
		$flujo_detalle = consulta_sql($SQL_flujo_detalle);
		if ($flujo_detalle[0]['montos'] <> "{0,0,0,0,0,0,0,0,0,0,0,0}") {
			$msje = "La asignación «{$flujo_detalle[0]['categoria']}» que está intentando eliminar tiene montos ingresados.\\n\\n"
				  . "Está seguro de quitarla definitivamente (esto no es posible deshacer)?";
			$url_si = "$enlbase=flujos_generales_egresos_resumen&eliminar=Si&id_fd=$id_fd&ano=$ano&conf=Eliminar&divisor_valores=$divisor_valores&nivel_info=$nivel_info";
			echo(confirma_js($msje,$url_si,"#"));
		} else {
			$_REQUEST['conf'] = "Eliminar";
		}
	}
	if ($_REQUEST['conf'] == "Eliminar") {
		consulta_dml("DELETE FROM finanzas.flujos_detalle WHERE id=$id_fd AND ano_flujo=$ano");
		echo(js("window.location='$enlbase=flujos_generales_egresos_resumen&ano=$ano&nivel_info=$nivel_info&divisor_valores=$divisor_valores';"));
		exit;
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

$SQL_valor_uf = "SELECT date_part('month',fecha) AS mes,valor AS monto
                 FROM finanzas.valor_uf 
				 WHERE fecha IN (SELECT (fecha-'1 day'::interval)::date AS fecha 
                                 FROM finanzas.valor_uf 
				                 WHERE date_part('year',fecha)=$ano AND date_part('day',fecha)=1)
				   AND date_part('year',fecha)=$ano
				 ORDER BY fecha";
$valor_uf_mensual = consulta_sql($SQL_valor_uf);

$valor_uf_presup = consulta_sql("SELECT valor FROM finanzas.valor_uf WHERE fecha='$fec_valor_uf_presup'::date");
$valor_uf_presup = $valor_uf_presup[0]['valor'];

$SQL_ctas_contables = "SELECT char_comma_sum('- '||fcc.nombre||'<br>') AS ctas_contables
                       FROM finanzas.flujos_categorias_ctas_contables AS fccc
                       LEFT JOIN finanzas.flujos_ctas_contables AS fcc ON fcc.id=fccc.id_cta_contable
                       WHERE fccc.id_categoria=fd.id_cat_flujo AND fccc.ano_flujo=$ano";
                       
$SQL_flujo_detalle = "SELECT fd.id AS id_fd,CASE fcg.tipo WHEN 'E' THEN 'Egresos' WHEN 'I' THEN 'Ingresos' END AS tipo,fcg.acumulador,
                             fcg.nombre AS totalizador,fc.nombre AS categoria,monto_presupuesto,montos,comentarios,
                             ($SQL_ctas_contables) AS ctas_contables                             
                      FROM finanzas.flujos_detalle AS fd 
                      LEFT JOIN finanzas.flujos_categorias AS fc  ON fc.id=fd.id_cat_flujo
                      LEFT JOIN finanzas.flujos_cat_grupos AS fcg ON fcg.id=fc.id_cat_grupo
                      WHERE ano_flujo=$ano AND fcg.tipo='E' AND fcg.nombre NOT IN ('$totz_exc1','$totz_exc2','$totz_exc3','$totz_exc4','$totz_exc5')
                      ORDER BY fcg.tipo DESC,fcg.acumulador,fcg.nombre,fc.nombre";
$flujo_detalle = consulta_sql($SQL_flujo_detalle);
if (count($flujo_detalle) > 0) {
	$SQL_acum = array();
	for ($mes=1;$mes<=$mes_corte;$mes++) { $SQL_acum[$mes] = "sum(montos[$mes])"; }
	$SQL_acum = implode(",",$SQL_acum);
	
	$SQL_fd_tipo = "SELECT CASE fcg.tipo WHEN 'E' THEN 'Egresos' WHEN 'I' THEN 'Ingresos' END AS tipo,sum(monto_presupuesto) AS monto_presupuesto,
	                      ARRAY[$SQL_acum] AS montos 
	                FROM finanzas.flujos_detalle AS fd 
	                LEFT JOIN finanzas.flujos_categorias AS fc  ON fc.id=fd.id_cat_flujo
	                LEFT JOIN finanzas.flujos_cat_grupos AS fcg ON fcg.id=fc.id_cat_grupo
	                WHERE ano_flujo=$ano AND fcg.tipo='E' AND fcg.nombre NOT IN ('$totz_exc1','$totz_exc2','$totz_exc3','$totz_exc4','$totz_exc5')
	                GROUP BY tipo
	                ORDER BY tipo DESC";
	$fd_tipo     = consulta_sql($SQL_fd_tipo);

	$SQL_fd_acum = "SELECT fcg.acumulador,sum(monto_presupuesto) AS monto_presupuesto,
	                      ARRAY[$SQL_acum] AS montos 
	                FROM finanzas.flujos_detalle AS fd 
	                LEFT JOIN finanzas.flujos_categorias AS fc  ON fc.id=fd.id_cat_flujo
	                LEFT JOIN finanzas.flujos_cat_grupos AS fcg ON fcg.id=fc.id_cat_grupo
	                WHERE ano_flujo=$ano AND fcg.tipo='E' AND fcg.nombre NOT IN ('$totz_exc1','$totz_exc2','$totz_exc3','$totz_exc4','$totz_exc5')
	                GROUP BY acumulador
	                ORDER BY acumulador";
	$fd_acum     = consulta_sql($SQL_fd_acum);

	$SQL_fd_totz = "SELECT fcg.nombre AS totalizador,sum(monto_presupuesto) AS monto_presupuesto,
	                      ARRAY[$SQL_acum] AS montos 
	                FROM finanzas.flujos_detalle AS fd 
	                LEFT JOIN finanzas.flujos_categorias AS fc  ON fc.id=fd.id_cat_flujo
	                LEFT JOIN finanzas.flujos_cat_grupos AS fcg ON fcg.id=fc.id_cat_grupo
	                WHERE ano_flujo=$ano AND fcg.tipo='E' AND fcg.nombre NOT IN ('$totz_exc1','$totz_exc2','$totz_exc3','$totz_exc4','$totz_exc5')
	                GROUP BY totalizador
	                ORDER BY totalizador";
	$fd_totz     = consulta_sql($SQL_fd_totz);
	
	$SQL_fd_totz_exc = "SELECT DISTINCT ON (fcg.nombre) fcg.nombre AS id,fcg.nombre AS nombre
	                    FROM finanzas.flujos_detalle AS fd 
	                    LEFT JOIN finanzas.flujos_categorias AS fc  ON fc.id=fd.id_cat_flujo
	                    LEFT JOIN finanzas.flujos_cat_grupos AS fcg ON fcg.id=fc.id_cat_grupo
	                    WHERE fcg.tipo='E' AND ano_flujo=$ano
	                    ORDER BY fcg.nombre";
	$fd_totz_exc = consulta_sql($SQL_fd_totz_exc);
}

$HTML = "";
$tot_ingresos = $tot_gastos = $total = array();
$_tipo        = $_acumulador  = $_totalizador = "";
$subtotal = array();

$cabecera = "";
if ($nivel_info >= 1) { $cabecera .= "Tipo<br>"; }
if ($nivel_info >= 2) { $cabecera .= "&nbsp;&nbsp;Sub-Título<br>"; }
if ($nivel_info >= 3) { $cabecera .= "&nbsp;&nbsp;&nbsp;&nbsp;Ítem<br>"; }
if ($nivel_info >= 4) { $cabecera .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Asignación"; }

$limite_presup_consumido = ((1/12)*$mes_corte)+0.01;
$limite2_presup_consumido = ((1/12)*$mes_corte)+0.05;

for ($x=0;$x<count($flujo_detalle);$x++) {
	extract($flujo_detalle[$x]);
	
	if ($_tipo <> $tipo && $nivel_info>=1) {		
		$HTML .= "<tr class='filaTabla'>"
		      .  "  <td class='textoTabla'><b><i style='font-variant: small-caps'>$tipo</i></b></td>";
		for ($y=0;$y<count($fd_tipo);$y++) {
			if ($tipo == $fd_tipo[$y]['tipo']) {
				$montos_mensuales = explode(",",str_replace(array("{","}"),"",$fd_tipo[$y]['montos']));
				$monto_presup = number_format(round($fd_tipo[$y]['monto_presupuesto']/$divisor_valores,0),0,",",".");
				if ($divisor_valores == "UF") { $monto_presup = number_format(round($fd_tipo[$y]['monto_presupuesto']/$valor_uf_presup,2),2,",","."); }
				$HTML .= "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><b><i>$monto_presup</i></b></td>\n";
				$monto_gasto_uf = 0;
				for($mes=0;$mes<12;$mes++) {
					$monto_gasto = 0;
					if ($mes<$mes_corte) {
						$monto_gasto = number_format(round($montos_mensuales[$mes]/$divisor_valores,0),0,",",".");
						if ($divisor_valores == 'UF') {
							$valor_uf_mes = 0;
							for($j=0;$j<count($valor_uf_mensual);$j++) { if ($mes == $valor_uf_mensual[$j]['mes']-1) { $valor_uf_mes = $valor_uf_mensual[$j]['monto']; } }
							$monto_gasto = number_format(round($montos_mensuales[$mes]/$valor_uf_mes,2),2,",",".");
							$monto_gasto_uf += round($montos_mensuales[$mes]/$valor_uf_mes,2);
						} 
					}
					$HTML .= "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><b><i>$monto_gasto</i></b></td>\n";
				}
				$total_anual      = array_sum($montos_mensuales);
				$presup_consumido = $total_anual/$fd_tipo[$y]['monto_presupuesto'];
				$total_anual      = number_format(round($total_anual/$divisor_valores,0),0,",",".");
				if ($divisor_valores == "UF") { 
					$total_anual      = $monto_gasto_uf; 
					$presup_consumido = $monto_gasto_uf/round($fd_tipo[$y]['monto_presupuesto']/$valor_uf_presup,2);
					$total_anual      = number_format($total_anual,2,",",".");
				}

				if ($presup_consumido > $limite2_presup_consumido) { $estilo = "sobreconsumo"; } 
				elseif ($presup_consumido > $limite_presup_consumido) { $estilo = "mediosobreconsumo"; } 
				else { $estilo = "bajoconsumo"; }
		
				$HTML .= "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><b><i>$total_anual</i></b></td>\n"
				      .  "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><b><i class='$estilo'>".number_format($presup_consumido*100,1,",",".")."%</i></b></td>\n";
			}
		}
		$HTML .= "</tr>";
		$_tipo = $tipo;
	}
	
	if ($_acumulador <> $acumulador && $nivel_info>=2) {
		$HTML .= "<tr class='filaTabla'>"
		      .  "  <td class='textoTabla'>&nbsp;&nbsp;<b>$acumulador</b></td>";
		for ($y=0;$y<count($fd_acum);$y++) {
			if ($acumulador == $fd_acum[$y]['acumulador']) {
				$montos_mensuales = explode(",",str_replace(array("{","}"),"",$fd_acum[$y]['montos']));

				$monto_presup = number_format(round($fd_acum[$y]['monto_presupuesto']/$divisor_valores,0),0,",",".");
				if ($divisor_valores == "UF") { $monto_presup = number_format(round($fd_acum[$y]['monto_presupuesto']/$valor_uf_presup,2),2,",","."); }
				$HTML .= "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><b>$monto_presup</b></td>\n";
				$monto_gasto_uf = 0;
				for($mes=0;$mes<12;$mes++) {
					$monto_gasto = 0;
					if ($mes<$mes_corte) {
						$monto_gasto = number_format(round($montos_mensuales[$mes]/$divisor_valores,0),0,",",".");
						if ($divisor_valores == 'UF') {
							$valor_uf_mes = 0;
							for($j=0;$j<count($valor_uf_mensual);$j++) { if ($mes == $valor_uf_mensual[$j]['mes']-1) { $valor_uf_mes = $valor_uf_mensual[$j]['monto']; } }
							$monto_gasto = number_format(round($montos_mensuales[$mes]/$valor_uf_mes,2),2,",",".");
							$monto_gasto_uf += round($montos_mensuales[$mes]/$valor_uf_mes,2);
						}
					} 
					$HTML .= "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><b>$monto_gasto</b></td>\n";
				}
				$total_anual      = array_sum($montos_mensuales);
				$presup_consumido = $total_anual/$fd_acum[$y]['monto_presupuesto'];
				$total_anual      = number_format(round($total_anual/$divisor_valores,0),0,",",".");
				if ($divisor_valores == "UF") { 
					$total_anual      = $monto_gasto_uf; 
					$presup_consumido = $monto_gasto_uf/round($fd_acum[$y]['monto_presupuesto']/$valor_uf_presup,2);
					$total_anual      = number_format($total_anual,2,",",".");
				}

				if ($presup_consumido > $limite2_presup_consumido) { $estilo = "sobreconsumo"; } 
				elseif ($presup_consumido > $limite_presup_consumido) { $estilo = "mediosobreconsumo"; } 
				else { $estilo = "bajoconsumo"; }
				$HTML .= "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><b>$total_anual</b></td>\n"
				      .  "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><b class='$estilo'>".number_format($presup_consumido*100,1,",",".")."%</b></td>\n";
			}
		}
		$HTML .= "</tr>";
		$_acumulador = $acumulador;
	}

	if ($_totalizador <> $totalizador && $nivel_info>=3) {
		$HTML .= "<tr class='filaTabla'>"
		      .  "  <td class='textoTabla'>&nbsp;&nbsp;&nbsp;&nbsp;<u>$totalizador</u></td>";
		for ($y=0;$y<count($fd_totz);$y++) {
			if ($totalizador == $fd_totz[$y]['totalizador']) {
				$montos_mensuales = explode(",",str_replace(array("{","}"),"",$fd_totz[$y]['montos']));

				$monto_presup = number_format(round($fd_totz[$y]['monto_presupuesto']/$divisor_valores,0),0,",",".");
				if ($divisor_valores == "UF") { $monto_presup = number_format(round($fd_totz[$y]['monto_presupuesto']/$valor_uf_presup,2),2,",","."); }
				$HTML .= "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><u>$monto_presup</u></td>\n";
				$monto_gasto_uf = 0;
				for($mes=0;$mes<12;$mes++) {
					$monto_gasto = 0;
					if ($mes<$mes_corte) {
						$monto_gasto = number_format(round($montos_mensuales[$mes]/$divisor_valores,0),0,",",".");
						if ($divisor_valores == 'UF') {
							$valor_uf_mes = 0;
							for($j=0;$j<count($valor_uf_mensual);$j++) { if ($mes == $valor_uf_mensual[$j]['mes']-1) { $valor_uf_mes = $valor_uf_mensual[$j]['monto']; } }
							$monto_gasto = number_format(round($montos_mensuales[$mes]/$valor_uf_mes,2),2,",",".");
							$monto_gasto_uf += round($montos_mensuales[$mes]/$valor_uf_mes,2);
						} 
					}
					$HTML .= "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><u>$monto_gasto</u></td>\n";
				}
				$total_anual      = array_sum($montos_mensuales);
				$presup_consumido = $total_anual/$fd_totz[$y]['monto_presupuesto'];
				$total_anual      = number_format(round($total_anual/$divisor_valores,0),0,",",".");
				if ($divisor_valores == "UF") { 
					$total_anual      = $monto_gasto_uf; 
					$presup_consumido = $monto_gasto_uf/round($fd_totz[$y]['monto_presupuesto']/$valor_uf_presup,2);
					$total_anual      = number_format($total_anual,2,",",".");
				}

				if ($presup_consumido > $limite2_presup_consumido) { $estilo = "sobreconsumo"; } 
				elseif ($presup_consumido > $limite_presup_consumido) { $estilo = "mediosobreconsumo"; } 
				else { $estilo = "bajoconsumo"; }

				$HTML .= "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><u>$total_anual</u></td>\n"
				      .  "  <td class='textoTabla' style='text-align: right; vertical-align: middle'><u class='$estilo'>".number_format($presup_consumido*100,1,",",".")."%</u></td>\n";
			}
		}
		$HTML .= "</tr>";
		$_totalizador = $totalizador;
	}
	
	
	if ($id_fd > 0 && $nivel_info>=4) {
		
		$href_editar  = "$enlbase_sm=flujos_generales_editar_cat_flujo&id_fd=$id_fd&ano=$ano";
		$href_elim    = "$enlbase=flujos_generales_egresos_resumen&eliminar=Si&id_fd=$id_fd&ano=$ano&nivel_info=$nivel_info&divisor_valores=$divisor_valores";
		$onclick_elim = "return confirm('Está seguro de quitar la asignación “ $categoria ” de este flujo ($ano)?');";
		$ctas_contables = str_replace(",","",$ctas_contables);
		
		$categoria = "<span id='bo_$x' style='visibility: hidden'>"
		           . "  <a href='$href_editar' title='Editar Asignación' class='boton' id='sgu_fancybox'>✍</a> "
		           . "  <a href='$href_elim' onClick=\"$onclick_elim\" title='Eliminar Asignación' class='boton'>✕</a>"
		           . "</span> "
		           . "<span title='header=[Ctas. Contables] fade=[on] body=[{$ctas_contables}]'>$categoria</span>";
		
		if ($ctas_contables <> "") { 
			$ctas_contables = implode("<br>",explode("<br>,",$ctas_contables)); 
			$categoria .= " <b style='color: green'><big>⮀</big></b>";
		} else {
			$ctas_contables = "** Sin asociar **<br>(Asignación de imputación manual)"; 
			$categoria .= " <span style='color: red'><big>🖎</big></span>"; 
		}
			
		$HTML .= "<tr class='filaTabla' onMouseOver=\"document.getElementById('bo_$x').style.visibility='visible'\" onMouseOut=\"document.getElementById('bo_$x').style.visibility='hidden'\">\n"
			  .  "  <td class='textoTabla' style='text-align: right; font-size: 8pt; vertical-align: middle'>$categoria</td>\n";

		$monto_presup = number_format(round($monto_presupuesto/$divisor_valores,0),0,",",".");
		if ($divisor_valores == "UF") { $monto_presup = number_format(round($monto_presupuesto/$valor_uf_presup,2),2,",","."); }
		$HTML .= "  <td class='textoTabla' style='text-align: right; font-size: 8pt; vertical-align: middle'><b><i>$monto_presup</i></b></td>\n";

		$montos_mensuales = explode(",",str_replace(array("{","}"),"",$montos));
		$comentarios_mensuales = explode(",",str_replace(array("{","}"),"",str_replace("\"","",$comentarios)));
		$subtotal['ppto'] += $monto_presupuesto;
		$monto_gasto_uf = 0;
		$tot_categoria = 0;
		for ($mes=0;$mes<12;$mes++) {
			$monto_gasto = 0;
			if ($mes<$mes_corte) {
				$monto_gasto = number_format(round($montos_mensuales[$mes]/$divisor_valores,0),0,",",".");
				if ($divisor_valores == 'UF') {
					$valor_uf_mes = 0;
					for($j=0;$j<count($valor_uf_mensual);$j++) { if ($mes == $valor_uf_mensual[$j]['mes']-1) { $valor_uf_mes = $valor_uf_mensual[$j]['monto']; } }
					$monto_gasto = number_format(round($montos_mensuales[$mes]/$valor_uf_mes,2),2,",",".");
					$monto_gasto_uf += round($montos_mensuales[$mes]/$valor_uf_mes,2);
				}
				$valor_mes = $monto_gasto; 

				if (!empty($comentarios_mensuales[$mes])) {
					$valor_mes = "<div title='header=[Comentarios] fade=[on] body=[{$comentarios_mensuales[$mes]}]' style='background: #BFE4BF; border-radius: 25px; padding: 0px 2px 0px 2px'>$valor_mes</div>";
				}
				$mes_nombre = substr($meses_palabra[$mes]['nombre'],0,3);
				$HTML .= "  <td class='textoTabla' id='$mes_nombre' style='text-align: right; font-size: 8pt;; vertical-align: middle'>$valor_mes</td>\n";
				$tot_categoria += $montos_mensuales[$mes];
				if ($tipo == "I") { $tot_ingresos[$mes] += $montos_mensuales[$mes]; }
				else { $tot_gastos[$mes] += $montos_mesuales[$mes]; $montos_mensuales[$mes] *= -1; }
				$total[$mes] += $montos_mensuales[$mes];
				$subtotal[$mes] += $montos_mensuales[$mes];
			} else {
				$HTML .= "  <td class='textoTabla' id='$mes_nombre' style='text-align: right; font-size: 8pt;; vertical-align: middle'>0</td>\n";
			}
		}
		$presup_consumido = $tot_categoria/$monto_presupuesto;
		$total_anual      = number_format(round($tot_categoria/$divisor_valores,0),0,",",".");
		if ($divisor_valores == "UF") { 
			$total_anual      = $monto_gasto_uf; 
			$presup_consumido = $monto_gasto_uf/round($monto_presupuesto/$valor_uf_presup,2);
			$total_anual      = number_format($total_anual,2,",",".");
		}

		if ($presup_consumido > $limite2_presup_consumido) { $estilo = "sobreconsumo"; } 
		elseif ($presup_consumido > $limite_presup_consumido) { $estilo = "mediosobreconsumo"; } 
		else { $estilo = "bajoconsumo"; }
				
		$HTML .= "  <td class='textoTabla' style='text-align: right; font-size: 8pt; vertical-align: middle'><b>$total_anual</b></td>"
			  .  "  <td class='textoTabla' style='text-align: right; font-size: 8pt; vertical-align: middle'><span class='$estilo'>".number_format(round(($presup_consumido)*100,2),2,",",".")."%</span></td>"
			  .  "</tr>\n";
	
	}
	
	if ($tipo == "I") { $tot_ingresos[12] += $tot_categoria; $tot_ingresos[13] += $monto_presupuesto; }
	else { $tot_gastos[12] += $tot_categoria; $tot_categoria *= -1; $tot_gastos[13] += $monto_presupuesto; $monto_presupuesto *= -1;}
	$total[12] += $tot_categoria;
	$total[13] += $monto_presupuesto;	
}


/*
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
*/
$DIVISORES = array(array('id'=>'UF'   ,'nombre'=>"en Unidades de Fomento"),
                   array('id'=>1      ,'nombre'=>"en pesos"),
                   array('id'=>1000   ,'nombre'=>"en miles de pesos"),
                   array('id'=>1000000,'nombre'=>"en millones de pesos"));

$ANOS_flujos = consulta_sql("SELECT ano AS id,ano||CASE WHEN activo THEN ' *' ELSE '' END AS nombre FROM finanzas.flujos ORDER BY ano DESC");

$NIVELES_INFO = array(array('id'=>1,'nombre'=>"Tipo"),
                      array('id'=>2,'nombre'=>"&nbsp;&nbsp;Sub-Título"),
                      array('id'=>3,'nombre'=>"&nbsp;&nbsp;&nbsp;&nbsp;Ítem "),
                      array('id'=>4,'nombre'=>"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Asignación (máximo)"));


$ctas_contables_ano = consulta_sql("SELECT * FROM finanzas.flujos_ctas_contables WHERE ano=$ano");
if (count($ctas_contables_ano) > 0) {
	$SQL_ctas_contables_sin_asig = "SELECT id,nombre FROM finanzas.flujos_ctas_contables 
									WHERE ano=$ano AND id NOT IN (SELECT id_cta_contable FROM finanzas.flujos_categorias_ctas_contables WHERE ano_flujo=$ano)
									ORDER BY nombre";
	$ctas_contables_sin_asig = consulta_sql($SQL_ctas_contables_sin_asig);
	$HTML_ccsa = "";
	if (count($ctas_contables_sin_asig) > 0) {
		$HTML_ccsa = "<ul>";
		for ($x=0;$x<count($ctas_contables_sin_asig);$x++) { $HTML_ccsa .= "<li>{$ctas_contables_sin_asig[$x]['nombre']}</li>"; }
		$HTML_ccsa .= "</ul>";
		$HTML_ccsa = "<div class='texto' style='border: 2px red solid; background: #FFE4E9; margin: 5px; padding: 5px;'>"
				   . "  <b>ERROR:</b> Actualmente se encuentran las siguientes Cuentas Contables sin Asignación: "
				   .    $HTML_ccsa
				   . "  Debe realizar las asignaciones correspondientes (no se permiten Cuentas Contables sin Asignación)."
				   . "</div>";
		$HTML_ctas_contables_asig = $HTML_ccsa;
	}
} else {
	$HTML_ccsa = "<div class='texto' style='border: 2px red solid; background: #FFE4E9; margin: 5px; padding: 5px;'>"
			   . "  <b>ERROR:</b> Actualmente NO HAY cuentas contables para el Flujo $ano_flujo. Debe definir el conjunto de cuentas contables antes de proseguir "
			   . "  y luego realizar las asignaciones correspondientes."
			   . "</div>";
	$HTML_ctas_contables_asig = $HTML_ccsa;	
}

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
      Acciones:<br>
      <a href="<?php echo("$enlbase_sm=flujos_generales_crear"); ?>" id='sgu_fancybox_small' class='boton'>Crear Flujo Anual</a>
      <a href="<?php echo("$enlbase_sm=flujos_generales_categorias&ano_flujo=$ano"); ?>" id='sgu_fancybox' class='boton'>Gestionar Asignaciones</a>
      <a href="<?php echo("$enlbase_sm=flujos_generales_ctas_contables&ano_flujo=$ano"); ?>" id='sgu_fancybox' class='boton'>Gestionar Ctas. Contables</a>
      <a href="<?php echo("$enlbase_sm=flujos_generales_subir_balance&ano=$ano"); ?>" id='sgu_fancybox' class='boton'>Subir Balance</a>
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
    <td class='celdaNombreAttr'>Fec. Creación:</td>
    <td class='celdaValorAttr'><?php echo($flujo[0]['fec_creacion']); ?></td>
    <td class='celdaNombreAttr'>Fec. última mod.:</td>
    <td class='celdaValorAttr'><?php echo($flujo[0]['fec_mod']); ?></td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="6" style="text-align: justify">
      <div class='celdaNombreAttr' style='text-align: left; padding: 2px'>Comentarios:</div>
      <div style='text-align: justify; padding: 2px'><?php echo(nl2br($flujo[0]['comentarios'])); ?></div>
      <div style='text-align:right;'>
        <a href="<?php echo("$enlbase_sm=flujos_generales_editar&ano=$ano"); ?>" id='sgu_fancybox_small' class='boton'>Editar</a><br><br>
      </div>
    </td>
  </tr>
</table>
<?php echo($HTML_ctas_contables_asig); ?>
<table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
  <tr>
	<td class="celdaFiltro">
      <div style='margin-bottom: 3px'>Acciones del flujo:</div>
      <a href="<?php echo("$enlbase_sm=flujos_generales_agregar_cat_flujo&ano=$ano"); ?>" class="boton" id="sgu_fancybox">Añadir una Asignación imputable</a>
      <a href="<?php echo("$enlbase_sm=flujos_generales_egresos_comparativo_anual&nivel_info=$nivel_info&divisor_valores=$divisor_valores&mes_corte=$mes_corte"); ?>" class="boton" id="sgu_fancybox">Comparativo Anual</a>
      <!-- <a href="<?php echo("$enlbase_sm=flujos_generales_agregar_todas_cats&ano=$ano"); ?>" class="boton" id="sgu_fancybox">Añadir un grupo de Asignaciones</a> -->
    </td>
	<td class="celdaFiltro">
      Mostrar valores:<br>
      <select name="divisor_valores" onChange="submitform();" class="filtro">
        <?php echo(select($DIVISORES,$divisor_valores)); ?>
      </select>
    </td>
<?php if ($divisor_valores == "UF") { ?>	
	<td class="celdaFiltro">
      Fec. valor UF presup.:<br>
	  <input type='date' name='fec_valor_uf_presup' value='<?php echo($fec_valor_uf_presup); ?>' class='botoncito' onChange="submitform();">
    </td>
<?php } ?>
	<td class="celdaFiltro">
      Nivel de desglose:<br>
      <select name="nivel_info" onChange="submitform();" class="filtro">
        <?php echo(select($NIVELES_INFO,$nivel_info)); ?>
      </select>
    </td>
  </tr>
</table>
<table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
  <tr>
	<td class="celdaFiltro">
      Excluir a:<br>
      <select name="totz_exc1" onChange="submitform();" class="filtro">
		<option value="">-- Seleccione --</option>
        <?php echo(select($fd_totz_exc,$totz_exc1)); ?>
      </select>
    </td>
	<td class="celdaFiltro">
      Excluir a:<br>
      <select name="totz_exc2" onChange="submitform();" class="filtro">
		<option value="">-- Seleccione --</option>
        <?php echo(select($fd_totz_exc,$totz_exc2)); ?>
      </select>
    </td>
	<td class="celdaFiltro">
      Excluir a:<br>
      <select name="totz_exc3" onChange="submitform();" class="filtro">
		<option value="">-- Seleccione --</option>
        <?php echo(select($fd_totz_exc,$totz_exc3)); ?>
      </select>
    </td>
	<td class="celdaFiltro">
      Excluir a:<br>
      <select name="totz_exc4" onChange="submitform();" class="filtro">
		<option value="">-- Seleccione --</option>
        <?php echo(select($fd_totz_exc,$totz_exc4)); ?>
      </select>
    </td>
	<td class="celdaFiltro">
      Excluir a:<br>
      <select name="totz_exc5" onChange="submitform();" class="filtro">
		<option value="">-- Seleccione --</option>
        <?php echo(select($fd_totz_exc,$totz_exc5)); ?>
      </select>
    </td>
  </tr>
</table>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='1' class='tabla' style='margin-top: 5px' id="flujo_egresos">
  <colgroup>
    <col>
    <col>
	<?php for($x=0;$x<12;$x++) { $mes = substr($meses_palabra[$x]['nombre'],0,3);  echo("<col style='' id='$mes'>"); } ?>
	<col>
  </colgroup>
    <tr class='filaTituloTabla'>
      <td class='tituloTabla' rowspan="2" style='text-align: left'><small><?php echo($cabecera); ?></small></td>
      <td class='tituloTabla' rowspan="2"><small>Monto<br>Presupuesto</small></td>
      <td class='tituloTabla' colspan="12">
        Flujo del Año <?php echo($ano); ?> al mes de 
        <select class="filtro" name="mes_corte" onChange="submitform();">
          <?php echo(select($meses_palabra,$mes_corte)); ?>
	    </select>
      </td>
      <td class='tituloTabla' rowspan="2">Total<br>Item</td>
      <td class='tituloTabla' rowspan="2"><small>Presupuesto<br>Consumido</small></td>
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
<!-- <img src="graficos/flujo_<?php echo($ano); ?>.png" style='border: 1px groove #4c8260'> -->
<!-- Fin: <?php echo($modulo); ?> -->

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 1200,
		'height'			: 800,
		'maxHeight'			: 800,
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
