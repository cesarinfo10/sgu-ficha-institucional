<?php
session_start();
include("funciones.php");

$nombre_sec_general = "Mercedes Aubá Asvisio";
$nombre_regacad     = "Andrea Aranela Suazo";

$id_alumno = $_REQUEST['id_alumno'];

if (!$_SESSION['autentificado'] || !is_numeric($id_alumno)) {
	header("Location: index.php");
	exit;
}

$modulo = "alumno_conc_notas_POST-G";
include("validar_modulo.php");

$SQL_alumno = "SELECT rut,upper(nombres||' '||apellidos) AS nombre,cohorte,mes_cohorte,trim(c.nombre) AS carrera,rpnp,
                      to_char(fecha_titulacion,'DD \"de\" tmMonth \"de\" YYYY') AS fecha_titulacion,malla_actual,
                      examen_anual_1,examen_anual_2
               FROM alumnos AS a
               LEFT JOIN carreras AS c ON c.id=carrera_actual
               LEFT JOIN alumnos_examen_final_postgrado AS aefp ON aefp.id_alumno=a.id
               WHERE a.id=$id_alumno";
$alumno = consulta_sql($SQL_alumno);
if (count($alumno) == 0) {
}
extract($alumno[0],EXTR_PREFIX_ALL,'al');

$al_cohorte = $meses_palabra[$al_mes_cohorte-1]['nombre'].'-'.$al_cohorte;

$SQL_asig_alumno = "SELECT vac.asignatura,nivel,nf 
                    FROM vista_alumnos_cursos AS vac
                    LEFT JOIN vista_detalle_malla AS vdm ON (vdm.id_prog_asig=vac.id_prog_asig AND vdm.id_malla=$al_malla_actual)
					WHERE id_alumno=$id_alumno AND vac.id_estado IN (1,3,4)
					ORDER BY nivel,asignatura";
$asig_alumno = consulta_sql($SQL_asig_alumno);
if (count($asig_alumno) == 0) {
}


// Despliegue de asignaturas y notas
$nivel_aux = $sum_notas = $trab_grado = $act_aplic = 0;
$HTML = "<table border='0.5' cellspacing='0' cellpadding='2' width='100%'>"
      . "  <tr>"
      . "    <td align='center'><b>Nombre de la Asignatura</b></td>"
      . "    <td align='center'><b>Nota</b></td>"
      . "  </tr>";
for ($x=0;$x<count($asig_alumno);$x++) {
	extract($asig_alumno[$x]);
	
	if ($nivel_aux <> $nivel) { $HTML .= "<tr><td colspan='2'><i>{$NIVELES[$nivel-1]['nombre']} Semestre</i></td></tr>"; }
	
	$nf = floatval($nf);
	
	$HTML .= "  <tr>"
	      .  "    <td>&nbsp;&nbsp;&nbsp;$asignatura</td>"
	      .  "    <td align='center'>".number_format($nf,1,",",".")."</td>"
	      .  "  </tr>";
	$nivel_aux = $nivel;
	$sum_notas += $nf;
		
	if (stristr($asignatura,"Trabajo de Grado") == "Trabajo de Grado")  { $trab_grado = $nf; $sum_notas -= $nf; }
	if (stristr($asignatura,"Actividad de Aplicación") == "Actividad de Aplicación") { $act_aplic = $nf; $sum_notas -= $nf; }
	if (stristr($asignatura,"Actividad de Aplicación") == "Actividad de Aplicación Práctica (Análisis PEI)") { $act_aplic = $nf; $sum_notas -= $nf; }
}
$HTML .= "</table>";
$ASIGNATURAS_NOTAS = $HTML;


// Tabla de ponderaciones
$HTML = "<table border='0.5' cellspacing='0' cellpadding='4' width='100%'>"
      . "  <tr>"
      . "    <td></td>"
      . "    <td align='center'><b>Notas</b></td>"
      . "    <td align='center'><b>Ponderación</b></td>"
      . "    <td align='center'><b>Calificación Ponderada</b></td>"
      . "  </tr>";
$prom_general      = round($sum_notas/(count($asig_alumno)-2),1);
$pond_prom_general = 0.45;
$prom_general_pond = $prom_general * $pond_prom_general;      
$HTML .= "  <tr>"
      .  "    <td>Promedio General Asignaturas</td>"
      .  "    <td align='center'>".number_format($prom_general,1,",",".")."</td>"
      .  "    <td align='center'>". $pond_prom_general*100 ."%</td>"
      .  "    <td align='center'>".number_format($prom_general_pond,2,",",".")."</td>"
      .  "  </tr>";
$examen_anual_1      = floatval($al_examen_anual_1);
$pond_examen_anual_1 = 0.1;
$examen_anual_1_pond = $examen_anual_1 * $pond_examen_anual_1;
$HTML .= "  <tr>"
      .  "    <td>Examen Anual (1)</td>"
      .  "    <td align='center'>".number_format($examen_anual_1,1,",",".")."</td>"
      .  "    <td align='center'>". $pond_examen_anual_1*100 ."%</td>"
      .  "    <td align='center'>".number_format($examen_anual_1_pond,2,",",".")."</td>"
      .  "  </tr>";      
$examen_anual_2      = floatval($al_examen_anual_2);
$pond_examen_anual_2 = 0.1;
$examen_anual_2_pond = $examen_anual_2 * $pond_examen_anual_2;
$HTML .= "  <tr>"
      .  "    <td>Examen Anual (2)</td>"
      .  "    <td align='center'>".number_format($examen_anual_2,1,",",".")."</td>"
      .  "    <td align='center'>". $pond_examen_anual_2*100 ."%</td>"
      .  "    <td align='center'>".number_format($examen_anual_2_pond,2,",",".")."</td>"
      .  "  </tr>";      
$pond_trab_grado = 0.15;
$trab_grado_pond = $trab_grado * $pond_trab_grado;
$HTML .= "  <tr>"
      .  "    <td>Trabajo de Grado</td>"
      .  "    <td align='center'>".number_format($trab_grado,1,",",".")."</td>"
      .  "    <td align='center'>". $pond_trab_grado*100 ."%</td>"
      .  "    <td align='center'>".number_format($trab_grado_pond,2,",",".")."</td>"
      .  "  </tr>";      
$pond_act_aplic = 0.2;
$act_aplic_pond = $act_aplic * $pond_act_aplic;
$HTML .= "  <tr>"
      .  "    <td>Actividad de Aplicación</td>"
      .  "    <td align='center'>".number_format($act_aplic,1,",",".")."</td>"
      .  "    <td align='center'>". $pond_act_aplic*100 ."%</td>"
      .  "    <td align='center'>".number_format($act_aplic_pond,2,",",".")."</td>"
      .  "  </tr>";
$prom_final = round($prom_general_pond + $examen_anual_1_pond + $examen_anual_2_pond + $trab_grado_pond + $act_aplic_pond,1);
$HTML .= "  <tr>"
      .  "    <td align='right' colspan='3'><b>Promedio Final de Graduación</b></td>"
      .  "    <td align='center'><b>".number_format($prom_final,1,",",".")."</b></td>"
      .  "  </tr>";
$HTML .= "</table>";

$TABLA_POND_NOTA_FINAL = $HTML;      

$TITULO = "<b>CONCENTRACIÓN DE NOTAS</b><br><br>";

if ($al_rpnp = "") { $al_rpnp = " RPNP Nº <b>$al_rpnp</b>, "; }

$TEXTO = "La Secretaria General que suscribe certifica que don(ña) <b>$al_nombre</b>, R.U.T. Nº <b>$al_rut</b> "
       . "de la cohorte <b>$al_cohorte</b> cumplió con todos los requisitos académicos del Programa de <b>$al_carrera</b>, "
       . "$al_rpnp de esta Casa de Estudios Superiores y obtuvo las calificaciones que se indican "
       . "en las asignaturas del Programa mencionado:<br><br>"
       . $ASIGNATURAS_NOTAS
       . "<br>"
       . $TABLA_POND_NOTA_FINAL
       . "<br>"       
       . "Se deja constancia que el Magíster mencionado tiene una duración de 4 semestres con un total de "
       . "900 horas pedagógicas, modalidad a distancia.<br><br>"
       . "NOTA: Escala de calificaciones de 1,0 a 7,0 con nota mínima de aprobación de 4.0<br><br>"
       . "Santiago, a $al_fecha_titulacion";
       
       $FIRMAS = "<table width='100%'>".$LF
       . "  <tr>".$LF
       . "    <td align='center' valign='bottom' width='45%'><img width='200' src='../img/firma_aaranela.png'><hr noshade size='0.5'><b>$nombre_regacad</b><br>Jefa de Registro Académico, Títulos y Grados</td>".$LF
//		. "    <td align='center' valign='bottom' width='45%'><hr noshade size='2'><b>$nombre_regacad</b><br>REGISTRO ACADÉMICO</td>".$LF
       . "    <td align='center' valign='top' width='10%'></td>".$LF
//		. "    <td align='center' valign='bottom' width='45%'><img width='250' src='img/firma_mauba.png'><hr noshade size='2'><b>$nombre_sec_general</b><br>Secretaría General</td>".$LF
       . "    <td align='center' valign='bottom' width='45%'><img width='250' src='../img/firma-tiembre-secgen-vpenaloza.jpg'></td>".$LF
//       . "    <td align='center' valign='bottom' width='45%'><img width='250' src='../img/firma-tiembre-secgen.jpg'></td>".$LF
//		. "    <td align='center' valign='bottom' width='45%'><hr noshade size='2'><b>$nombre_sec_general</b><br>Secretaría General</td>".$LF
       . "  </tr>".$LF
       . "</table>".$LF;


$HTML = "<html>".$LF
      . "  <head>".$LF
      . "    <title>UMC - SGU - Concentración de Notas Post-Grado</title>".$LF
      . "    <style>".$LF
      . "      td { font-size: 12px; font-family: sans,arial,helvetica; }".$LF
      . "      @media print {".$LF
      . "        @page {page-break-after: always; size: 21.5cm 25cm; }".$LF
      . "        td { font-size: 12px; font-family: sans,arial,helvetica; }".$LF
      . "      }".$LF
      . "    </style>".$LF
      . "  </head>".$LF
      . "  <body>".$LF
      . "    <table width='100%'>".$LF
      . "      <tr>".$LF
      . "        <td>".$LF
      . "          <table width='100%'><tr><td align='center'>$TITULO</td></tr></table>".$LF
      . "          <table width='100%'><tr><td valign='top' align='justify'>$TEXTO</td></tr>".$LF
      . "          <table width='100%'><tr><td valign='top' align='center'>$FIRMAS</td></tr>".$LF
      . "          </table>".$LF
      . "        </td>".$LF
      . "      </tr>".$LF
      . "    </table>".$LF
      . "  </body>".$LF
      . "</html>".$LF;

$archivo = "tmp/concentracion_notas_POST-G_".$id_alumno;
$HTML = iconv("UTF-8","ISO-8859-1//TRANSLIT",$HTML);	
$hand=fopen($archivo,"w");
fwrite($hand,$HTML);
fclose($hand);
$html2pdf = "htmldoc -t pdf --fontsize 11 --fontspacing 1 --no-strict --size 21.5x33cm --bodyfont helvetica "
		  . "--left 1cm --top 5cm --right 1cm --bottom 1cm --footer '  /' --header '   ' --no-embedfonts "
		  . "--compression=9 --encryption --permissions print,no-copy,no-annotate,no-modify "
		  . "--webpage $archivo ";
header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=$archivo.pdf");
passthru($html2pdf);
unlink($archivo);

//echo(js("window.close();"));
		

?>
