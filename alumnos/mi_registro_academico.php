<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

$id_alumno = $_SESSION['id'];
if (!is_numeric($id_alumno)) {
	echo(js("location.href='principal.php?modulo=gestion_alumnos';"));
	exit;
}

$mod_ant = $_SERVER['HTTP_REFERER'];
if ($mod_ant == "") { $mod_ant = "$enlbase=gestion_alumnos"; }

$ficha = $_REQUEST['ficha'];
if ($ficha == "") { $ficha = "alumno_ficha_datos_personales"; }

$fichas[0]['nombre'] = "Datos<br>Personales";
$fichas[0]['enlace'] = "alumno_ficha_datos_personales";
//$fichas[1]['nombre'] = "Antecedentes<br>Escolares/Universitarios";
//$fichas[1]['enlace'] = "alumno_ficha_antecedentes_esc_univ";
//$fichas[1]['enlace'] = "alumno_ficha_datos_personales";
//$fichas[2]['nombre'] = "Control<br>Interno";
//$fichas[2]['enlace'] = "alumno_ficha_control_interno";
//$fichas[2]['enlace'] = "alumno_ficha_datos_personales";

$HTML_botones_ficha = "";
for($x=0;$x<3;$x++) {
	$boton_ficha = $fichas[$x]['nombre'];
	$estilo_boton = "background: #DEF1FF";
	if ($fichas[$x]['enlace'] <> $ficha) {
		$enlace_ficha = "$enlbase=$modulo&id_alumno=$id_alumno&ficha=".$fichas[$x]['enlace'];
		$boton_ficha  = "<a class='enlaces' href='$enlace_ficha'>$boton_ficha</a>";
		$estilo_boton = "";
	}
	$HTML_botones_ficha .= "<td width='33%' class='tituloTabla' style='$estilo_boton'>$boton_ficha</td>";
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  Mi Registro Académico
</div><br>

<table cellpadding="0" cellspacing="0" border="0" class="tabla">
  <tr style="padding: 5px">
    <?php echo($HTML_botones_ficha); ?>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="3">
      <?php include($ficha.".php"); ?>
    </td>
  </tr>
</table>
