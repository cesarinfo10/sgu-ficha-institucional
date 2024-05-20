<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$divisor_valores = $_REQUEST['divisor_valores'];
$nivel_info      = $_REQUEST['nivel_info'];
$mes_inicio      = $_REQUEST['mes_inicio'];
$mes_corte       = $_REQUEST['mes_corte'];
$anos_flujo      = implode(",",$_REQUEST['anos_flujo']);
$totz_exc1       = $_REQUEST['totz_exc1'];
$totz_exc2       = $_REQUEST['totz_exc2'];
$totz_exc3       = $_REQUEST['totz_exc3'];
$totz_exc4       = $_REQUEST['totz_exc4'];
$totz_exc5       = $_REQUEST['totz_exc5'];


if ($divisor_valores == "") { $divisor_valores = 1000; }
if ($nivel_info == "") { $nivel_info = 2; }
if (empty($mes_corte)) { $mes_corte = date("n")-1; }
if (empty($mes_inicio)) { $mes_inicio = 1; }
if (empty($anos_flujo)) { $anos_flujo = date("Y")-1 . "," . date("Y"); }


$SQL_flujo = "SELECT ano
              FROM finanzas.flujos AS f
              LEFT JOIN vista_usuarios AS vu ON vu.id=f.id_creador
              WHERE ano IN ($anos_flujo)
              ORDER BY ano";
$flujo = consulta_sql($SQL_flujo);
if (count($flujo) > 0) {
	$HTML_cabecera = "";
	for ($x=0;$x<count($flujo);$x++) {
		$HTML_cabecera .= "<td class='tituloTabla' colspan='3'>{$flujo[$x]['ano']}</td>";
	}
	if (count($flujo) == 2) { $HTML_cabecera .= "<td class='tituloTabla'>Diferencia</td>"; }
}

$SQL_acum = array();
for ($mes=$mes_inicio;$mes<=$mes_corte;$mes++) { $SQL_acum[$mes] = "sum(montos[$mes])"; }
$SQL_acum = implode(",",$SQL_acum);
	                       
$SQL_flujo_detalle = "SELECT ano_flujo,CASE fcg.tipo WHEN 'E' THEN 'Egresos' WHEN 'I' THEN 'Ingresos' END AS tipo,fcg.acumulador,
                             fcg.nombre AS totalizador,fc.nombre AS categoria,ARRAY[$SQL_acum] AS montos,sum(monto_presupuesto) AS monto_presupuesto
                      FROM finanzas.flujos_detalle AS fd 
                      LEFT JOIN finanzas.flujos_categorias AS fc  ON fc.id=fd.id_cat_flujo
                      LEFT JOIN finanzas.flujos_cat_grupos AS fcg ON fcg.id=fc.id_cat_grupo
                      WHERE fcg.tipo='E' AND ano_flujo IN ($anos_flujo) AND fcg.nombre NOT IN ('$totz_exc1','$totz_exc2','$totz_exc3','$totz_exc4','$totz_exc5')
                      GROUP BY fcg.tipo,fcg.acumulador,fcg.nombre,fc.nombre,ano_flujo
                      ORDER BY fcg.tipo DESC,fcg.acumulador,fcg.nombre,fc.nombre,ano_flujo";
$flujo_detalle = consulta_sql($SQL_flujo_detalle);

if (count($flujo_detalle) > 0) {

	$SQL_fd_tipo = "SELECT ano_flujo,CASE fcg.tipo WHEN 'E' THEN 'Egresos' WHEN 'I' THEN 'Ingresos' END AS tipo,
	                       ARRAY[$SQL_acum] AS montos,sum(monto_presupuesto) AS monto_presupuesto
	                FROM finanzas.flujos_detalle AS fd 
	                LEFT JOIN finanzas.flujos_categorias AS fc  ON fc.id=fd.id_cat_flujo
	                LEFT JOIN finanzas.flujos_cat_grupos AS fcg ON fcg.id=fc.id_cat_grupo
	                WHERE fcg.tipo='E' AND ano_flujo IN ($anos_flujo) AND fcg.nombre NOT IN ('$totz_exc1','$totz_exc2','$totz_exc3','$totz_exc4','$totz_exc5')
	                GROUP BY tipo,ano_flujo
	                ORDER BY tipo DESC,ano_flujo";
	$fd_tipo     = consulta_sql($SQL_fd_tipo);

	$SQL_fd_acum = "SELECT ano_flujo,fcg.acumulador,
	                      ARRAY[$SQL_acum] AS montos,sum(monto_presupuesto) AS monto_presupuesto
	                FROM finanzas.flujos_detalle AS fd 
	                LEFT JOIN finanzas.flujos_categorias AS fc  ON fc.id=fd.id_cat_flujo
	                LEFT JOIN finanzas.flujos_cat_grupos AS fcg ON fcg.id=fc.id_cat_grupo
	                WHERE fcg.tipo='E' AND ano_flujo IN ($anos_flujo) AND fcg.nombre NOT IN ('$totz_exc1','$totz_exc2','$totz_exc3','$totz_exc4','$totz_exc5')
	                GROUP BY acumulador,ano_flujo
	                ORDER BY acumulador,ano_flujo";
	$fd_acum     = consulta_sql($SQL_fd_acum);

	$SQL_fd_totz = "SELECT ano_flujo,fcg.nombre AS totalizador,
	                      ARRAY[$SQL_acum] AS montos,sum(monto_presupuesto) AS monto_presupuesto
	                FROM finanzas.flujos_detalle AS fd 
	                LEFT JOIN finanzas.flujos_categorias AS fc  ON fc.id=fd.id_cat_flujo
	                LEFT JOIN finanzas.flujos_cat_grupos AS fcg ON fcg.id=fc.id_cat_grupo
	                WHERE fcg.tipo='E' AND ano_flujo IN ($anos_flujo) AND fcg.nombre NOT IN ('$totz_exc1','$totz_exc2','$totz_exc3','$totz_exc4','$totz_exc5')
	                GROUP BY totalizador,ano_flujo
	                ORDER BY totalizador,ano_flujo";
	$fd_totz     = consulta_sql($SQL_fd_totz);
	
	$SQL_fd_totz_exc = "SELECT DISTINCT ON (fcg.nombre) fcg.nombre AS id,fcg.nombre AS nombre
	                    FROM finanzas.flujos_detalle AS fd 
	                    LEFT JOIN finanzas.flujos_categorias AS fc  ON fc.id=fd.id_cat_flujo
	                    LEFT JOIN finanzas.flujos_cat_grupos AS fcg ON fcg.id=fc.id_cat_grupo
	                    WHERE fcg.tipo='E' AND ano_flujo IN ($anos_flujo)
	                    ORDER BY fcg.nombre";
	$fd_totz_exc = consulta_sql($SQL_fd_totz_exc);
}

$HTML = "";
$tot_ingresos = $tot_gastos = $total = array();
$_tipo        = $_acumulador  = $_totalizador = $_categoria = $ano_flujo = "";
$subtotal = array();
$anos_flujo = explode(",",$anos_flujo);
$fd = array();
$j = 0;

$cabecera = "";
if ($nivel_info >= 1) { $cabecera .= "Tipo<br>"; }
if ($nivel_info >= 2) { $cabecera .= "&nbsp;&nbsp;Sub-Título<br>"; }
if ($nivel_info >= 3) { $cabecera .= "&nbsp;&nbsp;&nbsp;&nbsp;Ítem<br>"; }
if ($nivel_info >= 4) { $cabecera .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Asignación"; }

for ($x=0;$x<count($flujo_detalle);$x++) {
	extract($flujo_detalle[$x]);
	
	if ($_tipo <> $tipo && $nivel_info>=1) {		
		for ($y=0;$y<count($fd_tipo);$y++) {
			$montos_mensuales = explode(",",str_replace(array("{","}"),"",$fd_tipo[$y]['montos']));
			$total_anual = array_sum($montos_mensuales);
			if ($tipo == $fd_tipo[$y]['tipo']) {
				$fd[] = array('ano_flujo'=>$fd_tipo[$y]['ano_flujo'],'nombre'=>$tipo,'nivel'=>1,'monto'=>$total_anual,'presupuesto'=>$fd_tipo[$y]['monto_presupuesto']);
			}
		}
		$_tipo = $tipo;
	}
	
	if ($_acumulador <> $acumulador && $nivel_info>=2) {
		for ($y=0;$y<count($fd_acum);$y++) {
			$montos_mensuales = explode(",",str_replace(array("{","}"),"",$fd_acum[$y]['montos']));
			$total_anual = array_sum($montos_mensuales);
			if ($acumulador == $fd_acum[$y]['acumulador']) {
				$fd[] = array('ano_flujo'=>$fd_acum[$y]['ano_flujo'],'nombre'=>$acumulador,'nivel'=>2,'monto'=>$total_anual,'presupuesto'=>$fd_acum[$y]['monto_presupuesto']);
			}
		}
		$_acumulador = $acumulador;
	}

	if ($_totalizador <> $totalizador && $nivel_info>=3) {
		for ($y=0;$y<count($fd_totz);$y++) {
			$montos_mensuales = explode(",",str_replace(array("{","}"),"",$fd_totz[$y]['montos']));
			$total_anual = array_sum($montos_mensuales);
			if ($totalizador == $fd_totz[$y]['totalizador']) {
				$fd[] = array('ano_flujo'=>$fd_totz[$y]['ano_flujo'],'nombre'=>$totalizador,'nivel'=>3,'monto'=>$total_anual,'presupuesto'=>$fd_totz[$y]['monto_presupuesto']);
			}
		}
		$_totalizador = $totalizador;
	}
	
	
	if ($_categoria <> $categoria && $nivel_info>=4) {
		$_categoria = $categoria;	
		while ($x < count($flujo_detalle) && $categoria == $flujo_detalle[$x]['categoria']) {
			
			$montos_mensuales = explode(",",str_replace(array("{","}"),"",$flujo_detalle[$x]['montos']));
			$tot_categoria = array_sum($montos_mensuales);
			$fd[] = array('ano_flujo'=>$flujo_detalle[$x]['ano_flujo'],'nombre'=>$categoria,'nivel'=>4,'monto'=>$tot_categoria,'presupuesto'=>$flujo_detalle[$x]['monto_presupuesto']);
			$x++;
		}
		$x--;
	}

}


$HTML_fd = "";
$ano_flujo = 0;
$nombre_flujo = "";
$nivel_flujo = 0;
$style_css = array(1=>"font-weight: bold",2=>"font-style: italic",3=>"text-decoration: underline",4=>"font-size: 75%");
$dif=0;
$limite_presup_consumido = (1/12)*(1+$mes_corte-$mes_inicio)+0.01;

for ($x=0;$x<count($fd);$x++) {
	if ($nombre_flujo <> $fd[$x]['nombre']) {
		$HTML_fd .= "</tr>\n"
		         .  "<tr class='filaTabla'>\n"
		         .  "  <td class='textoTabla'>".str_repeat("&nbsp;",$fd[$x]['nivel']*2)."<span style='{$style_css[$fd[$x]['nivel']]}'>{$fd[$x]['nombre']}</span></td>\n";
		$nombre_flujo = $fd[$x]['nombre'];
	}
	
	if ($fd[$x]['nivel'] == 1) { $monto_anual_tipo1[$fd[$x]['ano_flujo']] = $fd[$x]['monto']; }
	
	$presup_consumido = $fd[$x]['monto']/$fd[$x]['presupuesto'];
	
	$importancia_rel = "";
	$importancia_rel = $fd[$x]['monto']/$monto_anual_tipo1[$fd[$x]['ano_flujo']];
	
	$estilo = ($presup_consumido > $limite_presup_consumido) ? "sobreconsumo" : "bajoconsumo";

	$presup = "Año {$fd[$x]['ano_flujo']}: $".number_format($fd[$x]['presupuesto'],0,",",".");
	
	$HTML_fd .= "  <td class='textoTabla' align='right'><span style='{$style_css[$fd[$x]['nivel']]}'>".number_format(round($fd[$x]['monto']/$divisor_valores,0),0,",",".")."</span></td>\n"
	         .  "  <td class='textoTabla' align='right' style='font-size:85%;vertical-align: middle'><span title='header=[Presupuesto {$fd[$x]['nombre']}] fade=[on] body=[$presup]' class='$estilo' style='{$style_css[$fd[$x]['nivel']]}'>".number_format($presup_consumido*100,1,",",".")."%</span></td>\n"
	         .  "  <td class='textoTabla' align='right' style='font-size:70%;vertical-align: middle'><div style='transform: rotate(-15deg)'>".number_format($importancia_rel*100,1,",",".")."%</div></td>\n" ;
	if (count($anos_flujo)==2) {
		if ($nombre_flujo == $fd[$x+1]['nombre']) { $dif = $fd[$x]['monto']; }
		if ($nombre_flujo <> $fd[$x+1]['nombre']) { 
			$dif -= $fd[$x]['monto'];
			$HTML_fd .= "  <td class='textoTabla' align='right'><span style='{$style_css[$fd[$x]['nivel']]}'>".number_format(round($dif*-1/$divisor_valores,0),0,",",".")."</span></td>\n";
		}
	}
}
//var_dump($monto_anual_tipo1);

$DIVISORES = array(array('id'=>1      ,'nombre'=>"en pesos"),
                   array('id'=>1000   ,'nombre'=>"en miles de pesos"),
                   array('id'=>1000000,'nombre'=>"en millones de pesos"));

$ANOS_flujos = consulta_sql("SELECT ano AS id,ano||CASE WHEN activo THEN ' *' ELSE '' END AS nombre FROM finanzas.flujos ORDER BY ano");

$HTML_anos = "";
for ($x=0;$x<count($ANOS_flujos);$x++) {
	$checked = "";
	$ano = $ANOS_flujos[$x]['id'];
	if (in_array($ano,$anos_flujo)) { $checked = "checked='checked'"; }
	$HTML_anos .= "<input style='vertical-align: bottom;' type='checkbox' name='anos_flujo[]' value='$ano' id='$ano' onChange='submitform();' $checked> <label for='$ano'>$ano</label>&nbsp;&nbsp;";
}

$NIVELES_INFO = array(array('id'=>1,'nombre'=>"Tipo"),
                      array('id'=>2,'nombre'=>"&nbsp;&nbsp;Sub-Título"),
                      array('id'=>3,'nombre'=>"&nbsp;&nbsp;&nbsp;&nbsp;Ítem "),
                      array('id'=>4,'nombre'=>"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Asignación (máximo)"));

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<form name="formulario" action="principal_sm.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">

<table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
  <tr>
	<td class="celdaFiltro">
      Años:<br>
      <div style='vertical-align: top'><?php echo($HTML_anos); ?></div>
    </td>
	<td class="celdaFiltro">
      Mostrar valores:<br>
      <select name="divisor_valores" onChange="submitform();" class="filtro">
        <?php echo(select($DIVISORES,$divisor_valores)); ?>
      </select>
    </td>
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
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="<?php echo(count($flujo)*4); ?>">
      <div style='font-weight: normal'>
        (desde
        <select class="filtro" name="mes_inicio" onChange="submitform();">
		  <?php echo(select($meses_palabra,$mes_inicio)); ?>
	    </select>
        hasta
        <select class="filtro" name="mes_corte" onChange="submitform();">
		  <?php echo(select($meses_palabra,$mes_corte)); ?>
	    </select> 
	    de cada año)
	  </div> 
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='text-align: left'><small><?php echo($cabecera); ?></small></td>
    <?php echo($HTML_cabecera); ?>
  </tr>
  <?php echo($HTML_fd); ?>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

