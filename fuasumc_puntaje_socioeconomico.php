<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");

$SQL_puntajes_notas = "SELECT decil,lim_inferior,lim_superior,puntaje FROM dae.puntajes_deciles_casen";
$puntajes_notas = consulta_sql($SQL_puntajes_notas);

$HTML_pn = "";

for ($x=0;$x<count($puntajes_notas);$x++) {
	extract($puntajes_notas[$x]);
	$lim_inferior = number_format($lim_inferior,0,",",".");
	$lim_superior = number_format($lim_superior,0,",",".");
	
	$HTML_pn .= "<tr class='filaTabla'>\n"
		     .  "  <td class='textoTabla' style='text-align: center'>$decil</td>"
		     .  "  <td class='textoTabla' style='text-align: right'>$$lim_inferior</td>"
		     .  "  <td class='textoTabla' style='text-align: right'>$$lim_superior</td>"
		     .  "  <td class='textoTabla' style='text-align: center'>$puntaje</td>"
		     .  "</tr>";
}

$HTML_puntajes_notas = $HTML_pn;


?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  Puntaje Socio-económico: CASEN 2014 (Vigente al 2018)
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' rowspan="2">Decil</td>
    <td class='tituloTabla' colspan="2">Renta Líquida per-cápita</td>
    <td class='tituloTabla' rowspan="2">Puntaje<br>Beca UMC</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Inferior</td>
    <td class='tituloTabla'>Superior</td>
  </tr>
  <?php echo($HTML_puntajes_notas); ?>
</table>
