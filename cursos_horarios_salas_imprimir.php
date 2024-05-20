<?php
session_start();
include("funciones.php");

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

//include("validar_modulo.php");

if (!is_numeric($_REQUEST['semestre']) || !is_numeric($_REQUEST['ano']) || !is_numeric($_REQUEST['dia'])) {
	echo(js("window.close();"));
	exit;
} else {
	$semestre   = $_REQUEST['semestre'];
	$ano        = $_REQUEST['ano'];
	$id_carrera = $_REQUEST['id_carrera'];
	$jornada    = $_REQUEST['jornada'];
	$dia        = $_REQUEST['dia'];
	$vacias     = $_REQUEST['vacias'];
}

$ids_carreras = $_SESSION['ids_carreras'];

if (empty($_REQUEST['ano']))      { $ano = $ANO; }
if (empty($_REQUEST['semestre'])) { $semestre = $SEMESTRE; }
if (empty($vacias))               { $vacias   = "f"; }
if (empty($dia)) { $dia = strftime("%u"); }

$condiciones = " AND c.ano=$ano AND c.semestre=$semestre AND tipo in ('r','t') ";

if ($id_carrera > 0) {
	$condiciones .= " AND vc.id_carrera=$id_carrera ";
	$carrera = consulta_sql("SELECT upper(nombre) AS nombre FROM carreras WHERE id=$id_carrera");
	$nombre_horario .= " de la Carrera de {$carrera[0]['nombre']} ";
}

if ($jornada == 'D') {
	$condiciones .= " AND c.seccion BETWEEN 1 AND 4 ";
	
	$nombre_horario .= " de la jornada DIURNA ";
} elseif ($jornada == 'V') {
	$condiciones .= " AND c.seccion BETWEEN 5 AND 9 ";
	
	$nombre_horario .= " de la jornada VESPERTINA ";
}

if (!empty($ids_carreras)) { $condiciones .= " AND vc.id_carrera IN ($ids_carreras) "; }

$SQL_cursos = "SELECT c.id,vc.cod_asignatura,vc.asignatura,c.seccion,vc.profesor,c.horario1 AS horario,c.dia1 AS dia,trim(c.sala1) AS sala
               FROM vista_cursos AS vc LEFT JOIN cursos AS c USING (id)
               WHERE c.dia1=$dia AND c.sala1 IS NOT NULL $condiciones
               UNION
               SELECT c.id,vc.cod_asignatura,vc.asignatura,c.seccion,vc.profesor,c.horario2 AS horario,c.dia2 AS dia,trim(c.sala2) AS sala
               FROM vista_cursos AS vc LEFT JOIN cursos AS c USING (id)
               WHERE c.dia2=$dia AND c.sala2 IS NOT NULL  $condiciones
               UNION
               SELECT c.id,vc.cod_asignatura,vc.asignatura,c.seccion,vc.profesor,c.horario3 AS horario,c.dia3 AS dia,trim(c.sala3) AS sala
               FROM vista_cursos AS vc LEFT JOIN cursos AS c USING (id)
               WHERE c.dia3=$dia AND c.sala3 IS NOT NULL  $condiciones
               ORDER BY horario,sala,cod_asignatura";
$cursos = consulta_sql($SQL_cursos);
//echo($SQL_cursos);

$salas_utilizadas = "";
if ($vacias == "f") {
	$salas_utilizadas = array();
	for($x=0;$x<count($cursos);$x++) {
		$sala = $cursos[$x]['sala'];
		if (!in_array($sala,$salas_utilizadas)) {
			$salas_utilizadas = array_merge($salas_utilizadas,array($sala));
		}
	}
	$salas_utilizadas = "'".str_replace(",","','",implode("," , $salas_utilizadas))."'";
} else {
	$salas = consulta_sql("SELECT codigo FROM salas WHERE activa ORDER BY codigo");
	for ($x=0;$x<count($salas);$x++) {
		$salas_utilizadas .= "'{$salas[$x]['codigo']}',";
	}
	$salas_utilizadas = substr($salas_utilizadas,0,-1);
}

$salas = consulta_sql("SELECT trim(codigo) AS id,nombre FROM salas WHERE codigo IN ($salas_utilizadas) ORDER BY codigo");
$cant_salas = count($salas);

$HTML_salas = "";
for($x=0;$x<count($salas);$x++) {
	$HTML_salas .= "<td class='tituloTabla' width='100' nowrap>{$salas[$x]['nombre']}</td>";
}

$mods = "'A','B','C','D','E','F','G','H'";
if ($dia == 6) { $mods="'A','B','C','Ds','E'"; }
$horarios = consulta_sql("SELECT id,intervalo FROM vista_horarios WHERE id IN ($mods) ORDER BY id");

$y=0;	
$HTML_horarios = "";
for ($x=0;$x<count($horarios);$x++) {
	$HTML_horarios .= "<tr>
	                   <td class='tituloTabla' align='center' valign='middle'>
	                     {$horarios[$x]['id']}<br>{$horarios[$x]['intervalo']}
	                   </td>";
	$id_horario = $horarios[$x]['id'];
	for($s=0;$s<count($salas);$s++) {
		$HTML_cursos = "";
		while ($id_horario == $cursos[$y]['horario'] && $salas[$s]['id'] == $cursos[$y]['sala']) {
			$asignatura = trim($cursos[$y]['cod_asignatura'])."-".$cursos[$y]['seccion']."<br>"
			            . "<b>{$cursos[$y]['asignatura']}</b>";
			$HTML_cursos .= "$asignatura<br><u>{$cursos[$y]['profesor']}</u><hr>";
			
			if ($y < count($cursos)) { $y++; } else { break; }				
		}
		$HTML_horarios .= "<td class='celdaHorarios' valign='top' bgcolor='#f5f5f5'>$HTML_cursos</td>\n";
	}
	$HTML_horarios . "</tr>\n";
}	



$fecha_hora = strftime("%x %X");

$titulo = "<u>UNIVERSIDAD MIGUEL DE CERVANTES</u><br>"
        . "<sup>Vicerrectoría Académica<br>"
        . "Unidad de Registro Académico<br>"
        . "Sistema de Gestión Universitaria (SGU)<br></sup>";
        
$nombre_horario = $dias_palabra[$dia-1]['nombre']." del Periodo $semestre-$ano ";
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
          </table>
          <table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
            <tr class='filaTituloTabla'>
              <td class='tituloTabla' colspan="<?php echo($cant_salas+1); ?>">
                <h2>Horario Diario del <?php echo($nombre_horario); ?></h2>
              </td>
            </tr>
            <tr class='filaTituloTabla'>
              <td class='tituloTabla'>&nbsp;</td>
              <?php echo($HTML_salas); ?>
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
