<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$ano                = $_REQUEST['ano'];
$col_ctas_contables = $_REQUEST['col_ctas_contables'];
$col_montos         = $_REQUEST['col_montos'];
$mes_balance        = $_REQUEST['mes_balance'];
$texto_balance      = $_REQUEST['texto_balance'];

if (empty($col_ctas_contables)) { $col_ctas_contables = 0; }
if (empty($col_montos)) { $col_montos = 7; }
if (empty($mes_balance)) { $mes_balance = date("m") - 1; }
if ($_REQUEST['texto_balance'] <> "") {
	$SQL_ctas_contables = "SELECT fcc.id,fcc.nombre 
	                       FROM finanzas.flujos_ctas_contables AS fcc
	                       WHERE fcc.ano=$ano
	                       ORDER BY fcc.id";
	$ctas_contables = consulta_sql($SQL_ctas_contables);
	$ctas_contables_ids = array_column($ctas_contables,"id");
	$ctas_contables_nombres = array_column($ctas_contables,"nombre");
	$ctas_ctbles = array();
	for($x=0;$x<count($ctas_contables);$x++) { $ctas_ctbles[$ctas_contables_ids[$x]] = $ctas_contables_nombres[$x]; }
	ini_set("auto_detect_line_endings", true);
	$texto = str_getcsv($_REQUEST['texto_balance'],"\n");
	$data = array();
	$HTML_balance = "";
	$total_montos = 0;
	$problemas = false;
	$disabled = "";
	for($x=0;$x<count($texto);$x++) {
		$data[$x] = str_getcsv($texto[$x],"\t");
		$balance[$x]['cta_contable']    = $data[$x][$col_ctas_contables];
		$balance[$x]['id_cta_contable'] = array_search($data[$x][$col_ctas_contables],$ctas_ctbles);
		$balance[$x]['monto']           = str_replace(" ","",str_replace(".","",$data[$x][$col_montos]));
		$total_montos += $balance[$x]['monto'];
		if (in_array($balance[$x]['cta_contable'],$ctas_contables_nombres) && $balance[$x]['monto'] <> 0) { $cta_valida = "<big style='color: green'>☑</big>"; } else { $cta_valida = "<big style='color: red'>☒</big>"; $problemas=true; }
		$HTML_balance .= "<tr class='filaTabla'>\n"
		              .  "  <td class='textoTabla'>$cta_valida {$balance[$x]['cta_contable']}</td>\n"
		              .  "  <td class='textoTabla' style='text-align: right'>".number_format($balance[$x]['monto'],0,",",".")."</td>\n"
		              .  "</tr>";
	}
	if ($problemas) { $disabled = "disabled"; }
	$HTML_balance .= "<tr class='filaTabla'>\n"
	              .  "  <td class='celdaNombreAttr'>Total:</td>\n"
	              .  "  <td class='textoTabla' style='text-align: right'><b>".number_format($total_montos,0,",",".")."</b></td>\n"
	              .  "</tr>";

}

if ($_REQUEST['subir'] == "Subir Balance") {
	$SQL_ins_baldet = $SQL_upd_flujodet = "";
	$id_usuario = $_SESSION['id_usuario'];
	consulta_dml("INSERT INTO finanzas.flujos_balances (mes,ano,id_usuario) VALUES ($mes_balance,$ano,$id_usuario)");
	$bal = consulta_sql("SELECT max(id) AS id FROM finanzas.flujos_balances WHERE id_usuario=$id_usuario AND mes=$mes_balance AND ano=$ano");
	$id_balance = $bal[0]['id'];
	for($x=0;$x<count($balance);$x++) {
		$baldet_id_cta_ctble = $balance[$x]['id_cta_contable'];
		$baldet_monto        = $balance[$x]['monto'];
		$SQL_ins_baldet .= "INSERT INTO finanzas.flujos_balances_detalle (id_balance,id_cta_contable,monto) VALUES ($id_balance,$baldet_id_cta_ctble,$baldet_monto);";
	}
	$SQL_upd_flujodet = "UPDATE finanzas.flujos_detalle SET montos[$mes_balance]=0 WHERE ano_flujo=$ano;";
	$SQL_upd_flujodet .= "UPDATE finanzas.flujos_detalle AS fd 
	                      SET montos[$mes_balance]=monto 
	                      FROM finanzas.vista_flujos_balances_detalle_sum AS vfbd 
	                      WHERE vfbd.id_balance=$id_balance AND vfbd.id_categoria=fd.id_cat_flujo AND fd.ano_flujo=$ano";
	if (consulta_dml($SQL_ins_baldet) > 0) {
		if (consulta_dml($SQL_upd_flujodet) > 0) {
			echo(msje_js("Se ha subido exitosamente el balance."));
		} else {
			echo(msje_js("ERROR: No ha sido posible realizar las imputaciones desde las cuentas contables hacia las asignaciones. informe este error al Departamento de Informática."));
		}
	} else {
		echo(msje_js("ERROR: No ha sido posible subir el balance. informe este error al Departamento de Informática."));
	}
	echo(js("parent.jQuery.fancybox.close();"));
	exit;		
}

$COLUMNAS = array(array('id'=>"0",'nombre'=>"Primera columna"),
                  array('id'=>"1",'nombre'=>"Segunda columna"),
                  array('id'=>"2",'nombre'=>"Tercera columna"),
                  array('id'=>"3",'nombre'=>"Cuarta columna"),
                  array('id'=>"4",'nombre'=>"Quinta columna"),
                  array('id'=>"5",'nombre'=>"Sexta columna"),
                  array('id'=>"6",'nombre'=>"Séptima columna"),
                  array('id'=>"7",'nombre'=>"Octava columna"));
?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo("$nombre_modulo $ano"); ?>
</div>
<form name="formulario" action="principal_sm.php" method="post" onSubmit="if (formulario.id_cat_flujo.value=='') { alert('Debe seleccionar la categoría para este item'); return false; } else { return true; }">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="ano" value="<?php echo($ano); ?>">
<div style='margin-top: 5px'>
  <input type="submit" name="reconocer" value="Reconocer Texto del Balance">
</div>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr'>Año Flujo/Balance:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($ano); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Mes Balance:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name='mes_balance' class="filtro" onChange="submitform();">
        <?php echo(select($meses_palabra,$mes_balance)); ?>
      </select>
    </td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center">Balance (pegue aquí desde MS-Excel/LO-Calc):</td></tr>
  <tr><td class='celdaValorAttr' colspan="4" style="text-align: center"><textarea onChange="submitform();" class='general' name="texto_balance" rows="15" cols="120"><?php echo($texto_balance); ?></textarea></td></tr>
    <tr>
    <td class='celdaNombreAttr'>Cuentas contables en:</td>
    <td class='celdaValorAttr'>
      <select name='col_ctas_contables' class="filtro" onChange="submitform();">
        <?php echo(select($COLUMNAS,$col_ctas_contables)); ?>
      </select>
    </td>
    <td class='celdaNombreAttr'>Montos en columna:</td>
    <td class='celdaValorAttr'>
      <select name='col_montos' class="filtro" onChange="submitform();">
        <?php echo(select($COLUMNAS,$col_montos)); ?>
      </select>
    </td>
  </tr>
</table>
<?php if (count($balance) > 0) { ?>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
  <tr class='filaTituloTabla'><td class='tituloTabla' colspan="2">Balance reconocido desde el texto</td></tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Cta. Contable</td>
    <td class='tituloTabla'>Monto</td>
  </tr>
  <?php echo($HTML_balance); ?>		
  <tr><td colspan="2" class='celdaNombreAttr' style='text-align: center'><input type="submit" name="subir" value="Subir Balance" <?php echo($disabled); ?>></td></tr>
</td></tr>
<?php } ?>
	
</form>
<!-- Fin: <?php echo($modulo); ?> -->
