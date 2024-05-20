<?php

$ids_carreras      = $_SESSION['ids_carreras'];
$cond_ids_carreras = "true";
if (!empty($ids_carreras)) {
	$cond_ids_carreras = "carrera_actual IN ($ids_carreras)";
}

$SQL_estados = "SELECT id,nombre FROM al_estados WHERE id NOT IN (0,2,53) ORDER BY id;";
$estados     = consulta_sql($SQL_estados);

$SQL_av_est_nuevos = "SELECT va.estado,count(va.id) as cantidad
                      FROM vista_alumnos AS va
                      LEFT JOIN alumnos AS a ON a.id=va.id
                      LEFT JOIN carreras AS c ON c.id=a.carrera_actual
                      WHERE va.id IN (SELECT id_alumno FROM matriculas WHERE semestre=$SEMESTRE AND ano=$ANO)
                        AND va.cohorte=$ANO AND c.regimen='PRE' AND a.estado NOT IN (0,2,53) AND $cond_ids_carreras
                      GROUP BY va.estado,a.estado ORDER BY a.estado;";                       
$av_est_nuevos = consulta_sql($SQL_av_est_nuevos);
$tot_als_nuevos = 0;
for ($x=0;$x<count($av_est_nuevos);$x++) { $tot_als_nuevos += $av_est_nuevos[$x]['cantidad']; }

$SQL_av_est_antiguos = "SELECT va.estado,count(va.id) as cantidad
                        FROM vista_alumnos AS va
                        LEFT JOIN alumnos AS a ON a.id=va.id
                        LEFT JOIN carreras AS c ON c.id=a.carrera_actual
                        WHERE va.id IN (SELECT id_alumno FROM matriculas WHERE semestre=$SEMESTRE AND ano=$ANO)
                          AND va.cohorte<$ANO AND c.regimen='PRE' AND a.estado NOT IN (0,2,53) AND $cond_ids_carreras
                        GROUP BY va.estado,a.estado ORDER BY a.estado;";
$av_est_antiguos = consulta_sql($SQL_av_est_antiguos);
$tot_als_antiguos = 0;
for ($x=0;$x<count($av_est_antiguos);$x++) { $tot_als_antiguos += $av_est_antiguos[$x]['cantidad']; }
  
$SQL_av_est_morosos = "SELECT va.estado,count(va.id) as cantidad
                       FROM vista_alumnos AS va
                       LEFT JOIN alumnos AS a ON a.id=va.id
                       LEFT JOIN carreras AS c ON c.id=a.carrera_actual
                       WHERE va.id IN (SELECT id_alumno FROM matriculas WHERE semestre=$SEMESTRE AND ano=$ANO)
                         AND c.regimen='PRE' AND moroso_financiero AND a.estado NOT IN (0,2,53) AND $cond_ids_carreras
                       GROUP BY va.estado,a.estado ORDER BY a.estado;";
$av_est_morosos = consulta_sql($SQL_av_est_morosos);
$tot_als_morosos = 0;
for ($x=0;$x<count($av_est_morosos);$x++) { $tot_als_morosos += $av_est_morosos[$x]['cantidad']; }
  
$SQL_av_est_insc_ramos = "SELECT va.estado,count(va.id) as cantidad
                          FROM vista_alumnos AS va
                          LEFT JOIN alumnos AS a ON a.id=va.id
                          LEFT JOIN carreras AS c ON c.id=a.carrera_actual
                          WHERE va.id IN (SELECT id_alumno FROM inscripciones_cursos)
                            AND va.id IN (SELECT id_alumno FROM matriculas WHERE semestre=$SEMESTRE AND ano=$ANO)
                            AND c.regimen='PRE'  AND a.estado NOT IN (0,2,53) AND $cond_ids_carreras
                          GROUP BY va.estado,a.estado ORDER BY a.estado;";
$av_est_insc_ramos = consulta_sql($SQL_av_est_insc_ramos);
$tot_als_insc_ramos = 0;
for ($x=0;$x<count($av_est_insc_ramos);$x++) { $tot_als_insc_ramos += $av_est_insc_ramos[$x]['cantidad']; }
  
$tot_alumnos = $tot_als_nuevos + $tot_als_antiguos;
  
$HTML = "<table bgcolor='#ffffff' border='0' class='tabla' cellspacing='1' cellpadding='2'>"
      . "  <tr class='filaTituloTabla'>"
      . "    <td class='tituloTabla' align='center' rowspan='2'><b>Estado</b></td>"
      . "    <td class='tituloTabla' colspan='8' align='center'><b>Cantidad de Alumnos</b></td>"
      . "  </tr>"
      . "  <tr class='filaTituloTabla'>"
      . "    <td class='tituloTabla' align='center'>Nuevos</td>"
      . "    <td class='tituloTabla' align='center'>Antiguos</td>"
      . "    <td class='tituloTabla' align='center' colspan='2'><b>TOTAL</td>"
      . "    <td class='tituloTabla' align='center' colspan='2'>Morosos</td>"
      . "    <td class='tituloTabla' align='center' colspan='2'>C/Ramos Insc.</td>";

$y = $z = $i = $h = 0;            
for ($x=0;$x<count($estados);$x++) {
	$estado          = $estados[$x]['nombre'];
	$estado_cantidad = 0;
	
	$HTML .= "  <tr class='filaTabla' onClick=\"window.location='$enlbase=gestion_alumnos&estado={$estados[$x]['id']}';\">"
	      .  "    <td class='textoTabla'>{$estados[$x]['nombre']}</td>";
	if ($av_est_nuevos[$y]['estado'] == $estado && $y < count($av_est_nuevos)) {
		$HTML .= "<td class='textoTabla' align='right'>{$av_est_nuevos[$y]['cantidad']}</td>";
		$estado_cantidad += $av_est_nuevos[$y]['cantidad'];
		$y++;
	} else {
		$HTML .= "<td class='textoTabla' align='right'>0</td>";
	}
	
	if ($av_est_antiguos[$z]['estado'] == $estado && $z < count($av_est_antiguos)) {
		$HTML .= "<td class='textoTabla' align='right'>".$av_est_antiguos[$z]['cantidad']."</td>";
		$estado_cantidad += $av_est_antiguos[$z]['cantidad'];
		$z++;
	} else {
		$HTML .= "<td class='textoTabla' align='right'>0</td>";
	}
	
	$porcentaje = ($estado_cantidad/$tot_alumnos)*100;
	$porcentaje = number_format($porcentaje,1,",",".");
	
	$HTML .= "<td class='textoTabla' align='right'>$estado_cantidad</td>"
	      .  "<td class='textoTabla' align='center'>$porcentaje%</td>";
	
	if ($av_est_morosos[$i]['estado'] == $estado && $i < count($av_est_morosos)) {
		$porc_morosos = ($av_est_morosos[$i]['cantidad']/$estado_cantidad)*100;
		$HTML .= "<td class='textoTabla' align='right'>{$av_est_morosos[$i]['cantidad']}</td>"
		      .  "<td class='textoTabla' align='right'>".number_format($porc_morosos,1,",",".")."%</td>";
		$cantidad_morosos += $av_est_morosos[$i]['cantidad'];
		$i++;
	} else {
		$HTML .= "<td class='textoTabla' align='right'>0</td><td class='textoTabla' align='right'>0,0%</td>";
	}

	if ($av_est_insc_ramos[$h]['estado'] == $estado && $h < count($av_est_insc_ramos)) {
		$porc_insc_ramos = ($av_est_insc_ramos[$h]['cantidad']/$estado_cantidad)*100;
		$HTML .= "<td class='textoTabla' align='right'>{$av_est_insc_ramos[$h]['cantidad']}</td>"
		      .  "<td class='textoTabla' align='right'>".number_format($porc_insc_ramos,1,",",".")."%</td>";
		$cantidad_insc_ramos += $av_est_insc_ramos[$h]['cantidad'];
		$h++;
	} else {
		$HTML .= "<td class='textoTabla' align='right'>0</td><td class='textoTabla' align='right'>0,0%</td>";
	}
	
	$HTML .= "</tr>";
}

$porc_insc_ramos = ($cantidad_insc_ramos/$tot_alumnos)*100;
$porc_morosos = ($cantidad_morosos/$tot_alumnos)*100;

$HTML .= "    <tr>"
      .  "      <td class='celdaNombreAttr' align='right'>Total Alumnos:</td>"
      .  "      <td class='celdaNombreAttr' align='right'><b>$tot_als_nuevos</b></td>"
      .  "      <td class='celdaNombreAttr' align='right'><b>$tot_als_antiguos</b></td>"
      .  "      <td class='celdaNombreAttr' style='text-align: center'><b>$tot_alumnos</b></td>"
      .  "      <td class='celdaNombreAttr' style='text-align: center'</td>"
      .  "      <td class='celdaNombreAttr' style='text-align: center'><b>$cantidad_morosos</b></td>"
      .  "      <td class='celdaNombreAttr' style='text-align: center'><b>".number_format($porc_morosos,1,",",".")."%</b></td>"
      .  "      <td class='celdaNombreAttr' style='text-align: center'><b>$cantidad_insc_ramos</b></td>"
      .  "      <td class='celdaNombreAttr' style='text-align: center'><b>".number_format($porc_insc_ramos,1,",",".")."%</b></td>"
      .  "    </tr>"
      .  "  </table>";

echo($HTML);
?>
