<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$condiciones = "AND informada=true";

$SQL_prog_cursos = "SELECT id,periodo,escuela,creador,fecha,fecha_mod,cant_cursos,costo_semestral,informadas
                    FROM vista_prog_cursos AS vpc
                    WHERE true $condiciones";
$prog_cursos = consulta_sql($SQL_prog_cursos);

$HTML_prog_cursos = "";
if (count($prog_cursos) > 0) {	
	for ($x=0;$x<count($prog_cursos);$x++) {
		extract($prog_cursos[$x]);
		$costo_semestral = "$".number_format($costo_semestral,0,',','.');
		$enl = "$enlbase=prog_cursos_vra_ver&id_prog_curso=$id";
		$HTML_prog_cursos .= "<tr class='filaTabla' onClick=\"window.location='$enl';\">"
		                   . "  <td class='textoTabla'>$periodo</td>"
		                   . "  <td class='textoTabla'>$escuela</td>"
		                   . "  <td class='textoTabla'>$creador</td>"
		                   . "  <td class='textoTabla' align='center'>$fecha</td>"
		                   . "  <td class='textoTabla' align='center'>$fecha_mod</td>"
		                   . "  <td class='textoTabla' align='center'>$cant_cursos</td>"
		                   . "  <td class='textoTabla' align='center'>$costo_semestral</td>"
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

<div class="texto">Programaciones de cursos informadas al día de hoy:</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Periodo</td>
    <td class='tituloTabla'>Escuela</td>
    <td class='tituloTabla'>Creador</td>
    <td class='tituloTabla'>Fec. Creación</td>
    <td class='tituloTabla'>Fec. Informa</td>
    <td class='tituloTabla'>Cant. cursos</td>
    <td class='tituloTabla'>Costo Semestral</td>    
  </tr>
  <?php echo($HTML_prog_cursos); ?>
</table>

<!-- Fin: <?php echo($modulo); ?> -->