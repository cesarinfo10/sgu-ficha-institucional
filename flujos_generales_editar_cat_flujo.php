<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$ano   = $_REQUEST['ano'];
$id_fd = $_REQUEST['id_fd'];

if ($_REQUEST['guardar'] == "Guardar") {
	$aComentarios      = $_REQUEST['comentarios'];
	$monto_presupuesto = str_replace(".","",$_REQUEST['monto_presupuesto']);
	$aMontos = array();
	$i = 0;
	foreach ($_REQUEST AS $var => $valor) { if (substr($var,0,7) == "montos_") { $aMontos[$i] = str_replace(".","",$valor); $i++; } }
	for ($x=0;$x<12;$x++) { $aComentarios[$x] = "'$aComentarios[$x]'"; }
	$montos      = implode(",",$aMontos);
	$comentarios = implode(",",$aComentarios);
	$SQL_cat_flujo_update = "UPDATE finanzas.flujos_detalle SET montos=ARRAY[$montos],comentarios=ARRAY[$comentarios],monto_presupuesto=$monto_presupuesto WHERE id=$id_fd";
	if (consulta_dml($SQL_cat_flujo_update) > 0) {
		consulta_dml("UPDATE finanzas.flujos SET fecha_modificacion=now() WHERE ano=$ano");
		echo(msje_js("Se han guardado los cambios con éxito de este item del flujo $ano"));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}
}

$SQL_ctas_contables = "SELECT char_comma_sum('- '||fcc.nombre||'<br>') AS ctas_contables
                       FROM finanzas.flujos_categorias_ctas_contables AS fccc
                       LEFT JOIN finanzas.flujos_ctas_contables AS fcc ON fcc.id=fccc.id_cta_contable
                       WHERE fccc.id_categoria=fd.id_cat_flujo AND fccc.ano_flujo=$ano";

$SQL_cat_flujo = "SELECT fc.nombre AS categoria,fcg.nombre AS totalizador,fcg.acumulador,montos,comentarios,monto_presupuesto,
                         CASE fcg.tipo WHEN 'I' THEN 'Ingreso' WHEN 'E' THEN 'Egreso' END as tipo, ($SQL_ctas_contables) AS ctas_contables
                  FROM finanzas.flujos_detalle AS fd 
                  LEFT JOIN finanzas.flujos_categorias AS fc ON fc.id=fd.id_cat_flujo
                  LEFT JOIN finanzas.flujos_cat_grupos AS fcg ON fcg.id=fc.id_cat_grupo
                  WHERE fd.id=$id_fd";
$cat_flujo = consulta_sql($SQL_cat_flujo);
$monto_presupuesto = number_format($cat_flujo[0]['monto_presupuesto'],0,",",".");
$montos = explode(",",str_replace(array("{","}"),"",$cat_flujo[0]['montos']));
$comentarios = explode(",",str_replace(array("{","}"),"",str_replace("\"","",$cat_flujo[0]['comentarios'])));
$readonly = "";
if ($cat_flujo[0]['ctas_contables'] <> "") { 
	//$readonly = "readonly";
	$cat_flujo[0]['ctas_contables'] = implode("<br>",explode("<br>,",$cat_flujo[0]['ctas_contables']));
} else {
	$cat_flujo[0]['ctas_contables'] = "** Sin cuentas contables asociadas **";
}
$HTML = "";
for ($x=0;$x<12;$x++) {
	$monto = number_format($montos[$x],0,",",".");
	$HTML .= "<tr class='filaTabla'>"
	      .  "  <td class='textoTabla' style='vertical-align: middle; text-align: right'>{$meses_palabra[$x]['nombre']}</td>"
	      .  "  <td class='textoTabla' style='vertical-align: middle; text-align: right'>$<input type='text' name='montos_$x' value='$monto' size='10' class='montos' onBlur=\"if (this.value=='') { this.value=0; }\" onKeyUp=\"puntitos(this,this.value.charAt(this.value.length-1),this.name);\" $readonly></td>"
	      .  "  <td class='textoTabla'><input type='text' name='comentarios[$x]' value='{$comentarios[$x]}' size='30' class='boton'></td>"
	      .  "</tr>";
}
$HTML .= "";

?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo("$nombre_modulo $ano"); ?>
</div>
<form name="formulario" action="principal_sm.php" method="get">
<div style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Guardar">
</div>
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_fd" value="<?php echo($id_fd); ?>">
<input type="hidden" name="ano" value="<?php echo($ano); ?>">
<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="3" style='text-align: center'>Datos de la Asignación</td></tr>	
  <tr>
    <td class='celdaNombreAttr'>Tipo \ Sut-Título \ Ítem \ Asignación:</td>
    <td class='celdaValorAttr' colspan='2'><?php echo($cat_flujo[0]['tipo']." \ ".$cat_flujo[0]['acumulador']." \ ".$cat_flujo[0]['totalizador']." \ ".$cat_flujo[0]['categoria']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cuenta(s) Contable(s) asociada(s):</td>
    <td class='celdaValorAttr' colspan='2'><?php echo($cat_flujo[0]['ctas_contables']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Monto Presupuesto:</td>
    <td class='celdaValorAttr' colspan='2'>$<input type='text' name='monto_presupuesto' value='<?php echo($monto_presupuesto); ?>' size='10' class='montos' onBlur="if (this.value=='') { this.value=0; }" onKeyUp="puntitos(this,this.value.charAt(this.value.length-1),this.name);"></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='text-align: center'>Mes</td>
    <td class='celdaNombreAttr' style='text-align: center'>Monto</td>
    <td class='celdaNombreAttr' style='text-align: center'>Comentarios</td>
  </tr>
  <?php echo($HTML); ?>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->
