<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");

$SQL_puntajes_beca_umc = "SELECT puntaje_inferior,puntaje_superior,porcentaje*100 AS porcentaje FROM dae.puntajes_becas_umc";
$puntajes_beca_umc = consulta_sql($SQL_puntajes_beca_umc);

$HTML_becaumc = "";

for ($x=0;$x<count($puntajes_beca_umc);$x++) {
	extract($puntajes_beca_umc[$x]);
	
	$HTML_becaumc .= "<tr class='filaTabla'>\n"
		          .  "  <td class='textoTabla' style='text-align: center'>$puntaje_inferior</td>"
		          .  "  <td class='textoTabla' style='text-align: center'>$puntaje_superior</td>"
		          .  "  <td class='textoTabla' style='text-align: center'>$porcentaje%</td>"
		          .  "</tr>";
}

?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  Asignación de Porcentajes Beca UMC
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="2">Puntaje Acumulativo</td>
    <td class='tituloTabla' rowspan="2">Porcentaje<br>Beca UMC</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Inferior</td>
    <td class='tituloTabla'>Superior</td>
  </tr>
  <?php echo($HTML_becaumc); ?>
</table>
