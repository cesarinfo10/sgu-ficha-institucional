<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");

$SQL_puntajes_sit_financiera = "SELECT nombre,descripcion,puntaje FROM dae.puntajes_sit_financiera";
$puntajes_sit_financiera = consulta_sql($SQL_puntajes_sit_financiera);

$HTML_sf = "";

for ($x=0;$x<count($puntajes_sit_financiera);$x++) {
	extract($puntajes_sit_financiera[$x]);
	
	$HTML_sf .= "<tr class='filaTabla'>\n"
		          .  "  <td class='textoTabla'>$nombre<br><small>$descripcion</small></td>"
		          .  "  <td class='textoTabla' style='text-align: center'>$puntaje</td>"
		          .  "</tr>";
}

$HTML_sit_financiera = $HTML_sf;
?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  Puntaje Situación Financiera UMC
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Categoría</td>
    <td class='tituloTabla'>Puntaje Beca UMC</td>
  </tr>
  <?php echo($HTML_sit_financiera); ?>
</table>
