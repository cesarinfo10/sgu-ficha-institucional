<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$SQL_informe = "SELECT cuotas_morosas,count(id_contrato) as cant_contratos,sum(monto_moroso) AS monto_moroso
                FROM (SELECT id_contrato,count(id) AS cuotas_morosas,sum(monto) AS monto_moroso
                      FROM finanzas.cobros 
                      WHERE fecha_venc < '2012-09-30' AND NOT pagado 
                      GROUP BY id_contrato) AS foo
                GROUP BY cuotas_morosas
                ORDER BY cuotas_morosas";
$informe     = consulta_sql($SQL_informe);

$HTML = "";
$total = $cant = 0;
for ($x=0;$x<count($informe);$x++) {
	$total += $informe[$x]['monto_moroso'];
	$cant += $informe[$x]['cant_contratos'];
	$monto = number_format($informe[$x]['monto_moroso'],0,',','.');
	$HTML .= "<tr class='filaTabla'>
	           <td class='textoTabla' align='right'>{$informe[$x]['cuotas_morosas']}</td>
	           <td class='textoTabla' align='right'>{$informe[$x]['cant_contratos']}</td>
	           <td class='textoTabla' align='right'>$$monto</td>
	          </tr>";
}
$total = number_format($total,0,',','.');
$HTML .= "<tr class='filaTabla'>
            <td class='textoTabla' colspan='2' align='right'><b>TOTAL: $cant</b></td>
            <td class='textoTabla' align='right'><b>$$total</b></td>
          </tr>";
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla'>
<tr class='filaTituloTabla'>
    <td class='tituloTabla'>Cuotas<br>Morosas</td>
    <td class='tituloTabla'>Contratos<br>con Morosidad</td>
    <td class='tituloTabla'>Monto<br>Moroso</td>
  </tr>
  <?php echo($HTML); ?>
</table>
