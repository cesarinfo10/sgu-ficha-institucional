<?php
setlocale(LC_ALL,"es_ES.UTF8");
setlocale(LC_ALL,"es_ES@euro");
include("funciones.php");
$jornada      = $_REQUEST['jornada'];
$id_escuela_u = $_REQUEST['id_escuela_u'];
$fecha        = $_REQUEST['fecha'];

if ($jornada <> "D" && $jornada <> "V") { $jornada = ""; }

if ($fecha == "" || !is_numeric($id_escuela_u)) {
	echo(js("window.location='$enlbase=profesores_control_asistencia_fecha_jornada';"));
	exit;
} else {
	$fecha = strftime("%Y-%m-%d",strtotime($fecha));
}

$dia_asist = strftime("%u",strtotime($fecha));
if ($dia_asist == 7) {
	echo(msje_js("El domingo no es un día válido. A continuación seleccione otra fecha."));
	echo(js("window.location='$enlbase=profesores_control_asistencia_fecha_jornada';"));
	exit;
}

$fecha_asistencia = strftime("%A %e de %B de %Y", strtotime($fecha));

$ids_carreras = consulta_sql("SELECT char_comma_sum(id::text) AS ids_carreras FROM carreras WHERE id_escuela=$id_escuela_u");
$ids_carreras = $ids_carreras[0]['ids_carreras']; 
$condicion_carreras = "AND vc.id_carrera IN ($ids_carreras)";
$escuela = consulta_sql("SELECT nombre FROM escuelas WHERE id=$id_escuela_u");
if (count($escuela) == 1) { $escuelas = $escuela[0]['nombre']; }

switch($jornada) {
	case "D":
		$condicion_jornada = "AND modulo BETWEEN 'A' AND 'F'";
		$jornadas = "Diurna";
		break;
	case "V":
		$condicion_jornada = "AND modulo BETWEEN 'G' AND 'H'";
		$jornadas = "Vespertina";
		break;
	default:
		$jornadas = "Ambas";
}

$SQL_asist_profe = "SELECT ap.id AS id_asist,ap.id_curso,vc.profesor,
                           vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,ap.modulo AS mod,
                           vh.intervalo AS mod_intervalo
                    FROM asist_profesores AS ap 
                    LEFT JOIN vista_cursos AS vc ON vc.id=ap.id_curso
                    LEFT JOIN vista_horarios AS vh ON vh.id=ap.modulo
                    WHERE fecha='$fecha'::date
                          $condicion_carreras $condicion_jornada
                    UNION
                    SELECT ap.id AS id_asist,ap.id_curso,vc.profesor,
                           vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,ap.modulo AS mod,
                           vh.intervalo AS mod_intervalo
                    FROM asist_profesores AS ap 
                    LEFT JOIN vista_cursos AS vc ON vc.id=ap.id_curso
                    LEFT JOIN vista_horarios AS vh ON vh.id=ap.modulo
                    WHERE asiste='r' AND fecha_recup IS NOT NULL AND fecha_recup='$fecha'::date
                          $condicion_carreras $condicion_jornada
                    ORDER BY mod,profesor,asignatura;";
$asist_profe     = consulta_sql($SQL_asist_profe);
//echo($SQL_asist_profe);
$Fecha = strftime("%d-%m-%Y",strtotime($fecha));
$fecha = strftime("%Y%m%d",strtotime($fecha));

$HTML = "";
for ($x=0; $x<count($asist_profe); $x++) {
	if ($mod_intervalo <> $asist_profe[$x]['mod_intervalo']) {
		$HTML .= "<tr class='filaTabla'>
		            <td class='textoTabla' colspan='7' align='center'>
		              Módulo {$asist_profe[$x]['mod']} ({$asist_profe[$x]['mod_intervalo']})
		            </td>
		          </tr>";
	}

	extract($asist_profe[$x]);
	
	$HTML .= "  <tr class='filaTabla'>
		           <td class='textoTabla' height='40' align='right'>$id_curso</td>
		           <td class='textoTabla'>$asignatura</td>
		           <td class='textoTabla'>$profesor</td>
		           <td class='textoTabla'>&nbsp</td>
		           <td class='textoTabla'>&nbsp</td>
		           <td class='textoTabla'>&nbsp</td>
		           <td class='textoTabla'>&nbsp</td>
		         </tr>";
}

$HTML .= "<tr class='filaTabla'>
            <td class='textoTabla' colspan='7' align='center'>
              <b>Recuperaciones</b>
            </td>
          </tr>";

for($x=0;$x<5;$x++) {
	$HTML .= "  <tr class='filaTabla'>
			   <td class='textoTabla' height='40' align='right'>&nbsp;</td>
			   <td class='textoTabla'>&nbsp;</td>
			   <td class='textoTabla'>&nbsp;</td>
			   <td class='textoTabla'>&nbsp</td>
			   <td class='textoTabla'>&nbsp</td>
			   <td class='textoTabla'>&nbsp</td>
			   <td class='textoTabla'>&nbsp</td>
			 </tr>";
}

?>

<html>
  <head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type">
    <title>UMC - SGU - Registro Académico</title>
    <link href="sgu.css" rel="stylesheet" type="text/css">
  </head>
</head>
<body topmargin="0" leftmargin="0" rightmargin="0" bottommargin="0">

<table width="100%" cellpadding="0" cellspacing="0" bgcolor="#B4DFFF">
  <tr bgcolor="#F1F9FF">
    <td><img src="img/logoumc.jpg" alt="Universidad Miguel de Cervantes"></td>
    <td align="center" class='texto' nowrap>
      <b>Universidad Miguel de Cervantes</b>
      <hr width="200" color="#ff0000">
      Sistema de Gestión Universitaria
    </td>
    <td align="center" valign="middle" width="100%">
      <b>Registro de Asistencia de Profesores</b>
    </td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" width="100%">
  <tr>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><?php echo($fecha_asistencia); ?></td>
    <td class='celdaNombreAttr'>Escuela:</td>
    <td class='celdaValorAttr'><?php echo($escuelas); ?></td>
    <td class='celdaNombreAttr'>Jornada:</td>
    <td class='celdaValorAttr'><?php echo($jornadas); ?></td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" width="100%">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>Asignatura</td>
    <td class='tituloTabla'>Profesor</td>    
    <td class='tituloTabla' width="60"><u>Hora de Entrada</u></td>
    <td class='tituloTabla' width="60"><u>Hora de Salida</u></td>
    <td class='tituloTabla' width="100"><u>Firma</u></td>
    <td class='tituloTabla' width="200"><u>Observaciones</u></td>
  </tr>
  <?php echo($HTML); ?>
</table>

<script>
 window.print();
 history.back();
</script>
</body>
</html>
