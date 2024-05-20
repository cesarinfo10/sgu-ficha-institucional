<?php

session_start();
include("funciones.php");

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

if (!is_numeric($_REQUEST['semestre']) || !is_numeric($_REQUEST['ano'])) {
	echo(js("window.close();"));
	exit;
} else {
	$semestre   = $_REQUEST['semestre'];
	$ano        = $_REQUEST['ano'];
	$id_carrera = $_REQUEST['id_carrera'];
	$jornada    = $_REQUEST['jornada'];
	$sala       = $_REQUEST['sala'];
}

$ids_carreras = $_SESSION['ids_carreras'];

$condiciones = " AND c.ano=$ano AND c.semestre=$semestre AND tipo IN ('r','t') ";

$nombre_horario = " del Periodo $semestre-$ano ";

if ($id_carrera > 0) {
	$condiciones .= " AND vc.id_carrera=$id_carrera ";
	
	$carrera = consulta_sql("SELECT upper(nombre) AS nombre FROM carreras WHERE id=$id_carrera");
	$nombre_horario .= " de la Carrera de {$carrera[0]['nombre']} ";
} elseif (!empty($ids_carreras)) {
	$condiciones .= " AND vc.id_carrera IN ($ids_carreras) ";
}

if ($jornada == 'D') {
	$condiciones .= " AND c.seccion BETWEEN 1 AND 4 ";
	
	$nombre_horario .= " de la jornada DIURNA ";
} elseif ($jornada == 'V') {
	$condiciones .= " AND c.seccion BETWEEN 5 AND 9 ";
	
	$nombre_horario .= " de la jornada VESPERTINA ";
}

if(!empty($sala)) {
	$condiciones .= " AND (c.sala1='$sala' OR c.sala2='$sala' OR c.sala3='$sala') ";
	
	$nombre_horario .= " de la sala $sala ";
}

$SQL_cursos = "SELECT c.id,vc.cod_asignatura,vc.asignatura,c.seccion,vc.profesor,c.horario1 AS horario,c.dia1 AS dia,c.sala1 AS sala
               FROM vista_cursos AS vc LEFT JOIN cursos AS c USING (id)
               WHERE true $condiciones
               UNION
               SELECT c.id,vc.cod_asignatura,vc.asignatura,c.seccion,vc.profesor,c.horario2 AS horario,c.dia2 AS dia,c.sala2 AS sala
               FROM vista_cursos AS vc LEFT JOIN cursos AS c USING (id)
               WHERE true $condiciones
               UNION
               SELECT c.id,vc.cod_asignatura,vc.asignatura,c.seccion,vc.profesor,c.horario3 AS horario,c.dia3 AS dia,c.sala3 AS sala
               FROM vista_cursos AS vc LEFT JOIN cursos AS c USING (id)
               WHERE true $condiciones
               ORDER BY horario,dia,seccion,cod_asignatura";
if (!empty($sala)) { $SQL_cursos = "SELECT * FROM ($SQL_cursos) AS foo WHERE sala='$sala'"; }
$cursos = consulta_sql($SQL_cursos);

$horarios = consulta_sql("SELECT id,intervalo FROM vista_horarios ORDER BY id");
$y=0;	
$HTML_horarios = "";
for ($x=0;$x<count($horarios);$x++) {
	$HTML_horarios .= "<tr>
	                   <td class='tituloTabla' align='center' valign='middle'>
	                     {$horarios[$x]['id']}<br>{$horarios[$x]['intervalo']}
	                   </td>";
	$id_horario = $horarios[$x]['id'];
	for($d=1;$d<7;$d++) {
		$HTML_cursos = "";
		while ($id_horario == $cursos[$y]['horario'] && $d == $cursos[$y]['dia']) {
			$asignatura = trim($cursos[$y]['cod_asignatura'])."-".$cursos[$y]['seccion']."<br><b>".$cursos[$y]['asignatura']."</b>";
			$HTML_cursos .= "<div class='horarioCurso' style='width: 120px'>$asignatura<br><u>{$cursos[$y]['profesor']}</u><br>Sala: {$cursos[$y]['sala']}</div>";
			
			if ($y < count($cursos)) { $y++; } else { break; }				
		}
		$HTML_horarios .= "<td class='celdaHorarios' valign='top'>$HTML_cursos</td>\n";
	}
	$HTML_horarios . "</tr>\n";
}

$fecha_hora = strftime("%x %X");

$titulo = "<u>UNIVERSIDAD MIGUEL DE CERVANTES</u><br>"
        . "<sup>Vicerrectoría Académica<br>"
        . "Unidad de Registro Académico<br>"
        . "Sistema de Gestión Universitaria (SGU)<br></sup>"
        . "<b>Horarios de Clases</b>";
        
?>

<html>
  <head>
    <title>UMC - SGU - Horarios</title>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type">
    <link href="sgu.css" rel="stylesheet" type="text/css">

    <style>
      td { font-size: 10pt; font-family: sans,arial,helvetica; }
      @media print {
        @page {page-break-after: always; size: 21.5cm 25cm; }
        td { font-size: 12px; font-family: sans,arial,helvetica; }
      }
    </style>
  </head>
  <body topmargin="5" leftmargin="5" rightmargin="5" bottommargin="5">
    <table width='100%'>
      <tr>
        <td>
          <table width='100%'>
            <tr>
              <td width="33%" align="left"><img src='img/logo_umc_apaisado.jpg' width="200"></td>
              <td width="34%" align='center'><?php echo($titulo); ?></td>
              <td width="33%" align='right' valign='top'><?php echo($fecha_hora); ?></td>
            </tr>
          </table><br>
          <table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
            <tr class='filaTituloTabla'>
              <td class='tituloTabla' colspan="8"><h2>Horario Semanal <?php echo($nombre_horario); ?></h2></td>
            </tr>
            <tr class='filaTituloTabla'>
              <td class='tituloTabla'>&nbsp;</td>
              <td class='tituloTabla' width="125">Lunes</td>
              <td class='tituloTabla' width="125">Martes</td>
              <td class='tituloTabla' width="125">Miércoles</td>
              <td class='tituloTabla' width="125">Jueves</td>
              <td class='tituloTabla' width="125">Viernes</td>
              <td class='tituloTabla' width="125">Sábado</td>
            </tr>
            <?php echo($HTML_horarios); ?>            
          </table>
        </td>
      </tr>
    </table>
    <div class="texto" style="color: #DF0000">
      NOTA: Se excluyen de esta tabla los cursos de tipo Modular, ya que estos tienen un horario predefinido.
    </div><br>
  </body>
</html>

<?php
	echo(js("window.print()"));
//	echo(js("window.close()"));
?>
