<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

//include("validar_modulo.php");

$id_alumno = $_REQUEST['id_alumno'];
if (!is_numeric($id_alumno)) {
	echo(js("location.href='principal.php?modulo=gestion_alumnos';"));
	exit;
}

$ficha = $_REQUEST['ficha'];
if ($ficha == "") { $ficha = "alumno_ficha_datos_personales"; }

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div><br>

<table cellpadding="4" cellspacing="0" border="0" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Acciones:</td>
    <td class='tituloTabla'>Gestión de Carga Académica:</td>
  </tr>
  <tr>
    <td class='textoTabla'>
      <a href="principal.php?modulo=editar_alumno&id_alumno=<?php echo($id_alumno); ?>" class="boton">Editar</a>
      <a href="principal.php?modulo=gestion_alumnos" class="boton">Volver</a>
    </td>
    <td class='textoTabla'>
<?php if ($_SESSION['tipo'] == 0) {?>    
      <a href="principal.php?modulo=gestion_alumnos" class="boton">Convalidar</a>
      <a href="principal.php?modulo=gestion_alumnos" class="boton">Homologar</a>
      <a href="principal.php?modulo=gestion_alumnos" class="boton">Examen Con. Rel.</a>
<?php } ?>
<?php if ($_SESSION['tipo'] >= 0 || $_SESSION['tipo'] <= 2 ) {?>    
      <a href="principal.php?modulo=gestion_alumnos" class="boton">Cambio de Malla</a>
<?php } ?>
    </td>
  </tr>
</table>
<br>
<table cellpadding="0" cellspacing="0" border="0" class="tabla">
  <tr style="padding: 5px">
<?php

$fichas[0]['nombre'] = "Datos<br>Personales";
$fichas[0]['enlace'] = "alumno_ficha_datos_personales";
$fichas[1]['nombre'] = "Antecedentes<br>Escolares/Universitarios";
$fichas[1]['enlace'] = "alumno_ficha_antecedentes_esc_univ";
$fichas[2]['nombre'] = "Control<br>Interno";
$fichas[2]['enlace'] = "alumno_ficha_control_interno";

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
echo($HTML_botones_ficha);

?>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="3">
      <?php include($ficha.".php"); ?>
    </td>
  </tr>
</table>