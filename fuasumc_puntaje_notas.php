<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");

$SQL_puntajes_notas = "SELECT tramo,nota_inferior::numeric(3,1),nota_superior::numeric(3,1),puntaje FROM dae.puntajes_notas";
$puntajes_notas = consulta_sql($SQL_puntajes_notas);

$HTML_pn = "";

for ($x=0;$x<count($puntajes_notas);$x++) {
	extract($puntajes_notas[$x]);
	
	$HTML_pn .= "<tr class='filaTabla'>\n"
		     .  "  <td class='textoTabla' style='text-align: center'>$tramo</td>"
		     .  "  <td class='textoTabla' style='text-align: center'>$nota_inferior</td>"
		     .  "  <td class='textoTabla' style='text-align: center'>$nota_superior</td>"
		     .  "  <td class='textoTabla' style='text-align: center'>$puntaje</td>"
		     .  "</tr>";
}

$HTML_puntajes_notas = $HTML_pn;


?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  Puntaje de Notas: Promedio Anual de asignaturas aprobadas y reprobadas
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Tramo</td>
    <td class='tituloTabla'>Nota Inferior</td>
    <td class='tituloTabla'>Nota Superior</td>
    <td class='tituloTabla'>Puntaje Beca UMC</td>
  </tr>
  <?php echo($HTML_puntajes_notas); ?>
</table>
