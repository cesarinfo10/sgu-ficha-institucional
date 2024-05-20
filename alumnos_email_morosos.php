<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$SQL_alumnos = "SELECT va.email AS email_umc, pap.email AS email_ext
                FROM vista_alumnos AS va
                LEFT JOIN alumnos AS a USING (id)
                LEFT JOIN pap ON pap.rut=a.rut
                WHERE a.id IN (SELECT id_alumno FROM matriculas WHERE semestre=$SEMESTRE AND ano=$ANO)
                  AND a.moroso_financiero AND a.regimen='PRE'";
$alumnos = consulta_sql($SQL_alumnos);
$cant_al = count($alumnos);
if ($cant_al>0) {
	$HTML_email_alumnos = "<tr><td class='celdaValorAttr'>";
	for ($x=0; $x<count($alumnos); $x++) {
		$HTML_email_alumnos .= $alumnos[$x]['email_umc'];
		if (!empty($alumnos[$x]['email_ext'])) {
			$HTML_email_alumnos .= ", ".$alumnos[$x]['email_ext'];
		}
		if ($x+1<count($alumnos)) {$HTML_email_alumnos .= ",<br>";} 
	}
	$HTML_email_alumnos .= "</td></tr>";
}	
?>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  Direcciones electrónicas de alumnos morosos  
</div>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Direcciones electrónicas (<?php echo($cant_al); ?> alumnos)</td>
  </tr>
  <?php echo($HTML_email_alumnos); ?>
</table><br>
<div class='texto'>
  Seleccione con el mouse las direcciones electrónicas que se muestran para luego copiarlas 
  (presionando el botón derecho del mouse sobre el texto seleccionado, y opción "Copiar").
  A continuación puede pegarlas directamente en el campo "Para:" en un mensaje nuevo de su sistema
  de correo.
</div>
<!-- Fin: <?php echo($modulo); ?> -->
