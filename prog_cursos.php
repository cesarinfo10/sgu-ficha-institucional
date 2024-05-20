<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

if ($_SESSION['tipo'] == 0) {
	//$condiciones = "AND informada=true";
} elseif ($_SESSION['tipo'] == 1 || $_SESSION['tipo'] == 2) {
	$id_escuela_usuario = $_SESSION['id_escuela'];
	if ($id_escuela_usuario > 0) {
		$condiciones = "AND id_escuela=$id_escuela_usuario";
	}
}


$SQL_prog_cursos = "SELECT id,periodo,escuela,creador,fecha,fecha_mod,cant_cursos,costo_semestral,informadas
                    FROM vista_prog_cursos AS vpc
                    WHERE true $condiciones";
$prog_cursos = consulta_sql($SQL_prog_cursos);

$HTML_prog_cursos = "";
if (count($prog_cursos) > 0) {	
	for ($x=0;$x<count($prog_cursos);$x++) {
		extract($prog_cursos[$x]);
		$costo_semestral = "$".number_format($costo_semestral,0,',','.');
		$enl = "$enlbase=prog_cursos_ver&id_prog_curso=$id";
		$HTML_prog_cursos .= "<tr class='filaTabla' onClick=\"window.location='$enl';\">"
		                   . "  <td class='textoTabla'>$periodo</td>"
		                   . "  <td class='textoTabla'>$escuela</td>"
		                   . "  <td class='textoTabla'>$creador</td>"
		                   . "  <td class='textoTabla' align='center'>$fecha</td>"
		                   . "  <td class='textoTabla' align='center'>$fecha_mod</td>"
		                   . "  <td class='textoTabla' align='center'>$cant_cursos</td>"
		                   . "  <td class='textoTabla' align='center'>$costo_semestral</td>"
		                   . "  <td class='textoTabla' align='center'>$informadas</td>"
		                   . "</tr>";
	}
} else {
	$HTML_prog_cursos = "<tr class='filaTabla'>"
	                  . "  <td class='textoTabla' colspan='8' align='center'>*** No hay programaciones para esta escuela ***</td>"
	                  . "</tr>";
}
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div><br>

<table cellpadding="4" cellspacing="0" border="0" class="tabla">
  <tr>
    <td align="center" class="textoTabla" style="vertical-align: middle;">
      <a href='<?php echo($enlbase); ?>=prog_cursos_crear' class='boton'>Crear nueva</a>
      <a href='<?php echo($mod_ant); ?>' class='boton'>Volver</a>
    </td>
  </tr>
</table><br>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Periodo</td>
    <td class='tituloTabla'>Escuela</td>
    <td class='tituloTabla'>Creador</td>
    <td class='tituloTabla'>Fec. Creación</td>
    <td class='tituloTabla'>Fec. Informa</td>
    <td class='tituloTabla'>Cant. cursos</td>
    <td class='tituloTabla'>Costo Semestral</td>
    <td class='tituloTabla'>Informada</td>
  </tr>
  <?php echo($HTML_prog_cursos); ?>
</table>

<!-- Fin: <?php echo($modulo); ?> -->
