<?php
session_start();
include("funciones.php");

//$nombre_sec_general = "Mercedes Aubá Asvisio";
//$nombre_regacad     = "Andrea Aranela Suazo";

$nombre_sec_general = $SECRETARIA_GENERAL_nombre;
$nombre_regacad     = $JEFE_REGACAD_nombre;

$id_alumno = $_REQUEST['id_alumno'];

if (!$_SESSION['autentificado'] || !is_numeric($id_alumno)) {
	header("Location: index.php");
	exit;
}

$modulo = "alumno_conc_notas_POST-T";
include("validar_modulo.php");

$SQL_alumno = "SELECT rut,upper(nombres||' '||apellidos) AS nombre,cohorte,mes_cohorte,c.nombre AS carrera,rpnp,
                      to_char(fecha_titulacion,'DD \"de\" tmMonth \"de\" YYYY') AS fecha_titulacion,malla_actual                      
               FROM alumnos AS a
               LEFT JOIN carreras AS c ON c.id=carrera_actual
               WHERE a.id=$id_alumno";
$alumno = consulta_sql($SQL_alumno);
if (count($alumno) == 0) {
}
extract($alumno[0],EXTR_PREFIX_ALL,'al');

$al_cohorte = $meses_palabra[$al_mes_cohorte-1]['nombre'].'-'.$al_cohorte;

$SQL_asig_alumno = "SELECT vac.asignatura,nivel,s1,nc,s2,nf 
                    FROM vista_alumnos_cursos AS vac
                    LEFT JOIN vista_detalle_malla AS vdm ON (vdm.id_prog_asig=vac.id_prog_asig AND vdm.id_malla=$al_malla_actual)
					WHERE id_alumno=$id_alumno AND id_estado=1
					ORDER BY nivel,asignatura";
$asig_alumno = consulta_sql($SQL_asig_alumno);
if (count($asig_alumno) == 0) {
}
extract($asig_alumno[0]);

$nota_final = round($s1 * 0.3 + $nc * 0.3 + $s2 * 0.4,1);

$HTML = "<table border='1' cellspacing='0' cellpadding='4' width='100%'>"
      . "  <tr>"
      . "    <td align='center'></td>"
      . "    <td align='center'><b>Notas</b></td>"
      . "    <td align='center'><b>Ponderación</b></td>"
      . "  </tr>"
      . "  <tr>"
      . "    <td align='center'>Actividades en Plataforma</td>"
      . "    <td align='center'>".number_format($s1,1,",",".")."</td>"
      . "    <td align='center'>30%</td>"
      . "  </tr>"
      . "  <tr>"
      . "    <td align='center'>Pruebas en línea</td>"
      . "    <td align='center'>".number_format($nc,1,",",".")."</td>"
      . "    <td align='center'>30%</td>"
      . "  </tr>"
      . "  <tr>"
      . "    <td align='center'>Examen Final</td>"
      . "    <td align='center'>".number_format($s2,1,",",".")."</td>"
      . "    <td align='center'>40%</td>"
      . "  </tr>"
      . "  <tr>"
      . "    <td align='center'><b>Promedio Final de Post-Título</b></td>"
      . "    <td align='center'></td>"
      . "    <td align='center'>".number_format($nota_final,1,",",".")."</td>"
      . "  </tr>"
      . "</table>";
$ASIGNATURAS_NOTAS = $HTML;

$TITULO = "<b>CONCENTRACIÓN DE NOTAS</b><br><br>";

$TEXTO = "La Secretaria General que suscribe certifica que don(ña) <b>$al_nombre</b>, R.U.T. Nº <b>$al_rut</b> "
       . "de la cohorte <b>$al_cohorte</b> cumplió con todos los requisitos académicos del Programa de <b>$al_carrera</b>, "
       . "RPNP Nº <b>$al_rpnp</b>, de esta Casa de Estudios Superiores y obtuvo las calificaciones que se indican:<br><br>"
       . $ASIGNATURAS_NOTAS
       . "<br><br>"
       . "Se deja constancia que el Post-Título mencionado tiene una duración de 1 año con un total de "
       . "800 horas pedagógicas, modalidad a distancia.<br><br>"
       . "NOTA: Escala de calificaciones de 1,0 a 7,0 con nota mínima de aprobación de 4.0<br><br>"
       . "Santiago, a $al_fecha_titulacion";
       
$FIRMAS = "<table width='100%'>".$LF
		. "  <tr>".$LF
		. "    <td align='center' valign='bottom' width='45%'><img width='200' src='../img/firma_aaranela.png'><hr noshade size='2'><b>$nombre_regacad</b><br>Jefa de Registro Académico, Títulos y Grados</td>".$LF
//		. "    <td align='center' valign='bottom' width='45%'><hr noshade size='2'><b>$nombre_regacad</b><br>REGISTRO ACADÉMICO</td>".$LF
		. "    <td align='center' valign='top' width='10%'></td>".$LF
//		. "    <td align='center' valign='bottom' width='45%'><img width='250' src='img/firma_mauba.png'><hr noshade size='2'><b>$nombre_sec_general</b><br>Secretaría General</td>".$LF
		. "    <td align='center' valign='bottom' width='45%'><img width='250' src='../img/firma-tiembre-secgen-vpenaloza.jpg'></td>".$LF
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
      . "  <body><br><br><br><br>".$LF
      . "    <table width='100%'>".$LF
      . "      <tr>".$LF
      . "        <td>".$LF
      . "          <table width='100%'><tr><td align='center'>$TITULO</td></tr></table><br>".$LF
      . "          <table width='100%'><tr><td valign='top' align='justify'>$TEXTO</td></tr>".$LF
      . "          <table width='100%'><tr><td valign='top' align='center'><br><br><br><br><br><br>$FIRMAS</td></tr>".$LF
      . "          </table>".$LF
      . "        </td>".$LF
      . "      </tr>".$LF
      . "    </table>".$LF
      . "  </body>".$LF
      . "</html>".$LF;

$archivo = "tmp/concentracion_notas_POST-T_".$id_alumno;
$HTML = iconv("UTF-8","ISO-8859-1",$HTML);
$hand=fopen($archivo,"w");
fwrite($hand,$HTML);
fclose($hand);
$html2pdf = "htmldoc -t pdf --fontsize 11 --fontspacing 1 --no-strict --size 21.5x27cm --bodyfont helvetica "
		  . "--left 1cm --top 4cm --right 1cm --bottom 1cm --footer '  /' --header '   ' --no-embedfonts "
		  . "--compression=9 --encryption --permissions print,no-copy,no-annotate,no-modify "
		  . "--webpage $archivo ";
header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=$archivo.pdf");
passthru($html2pdf);
unlink($archivo);

//echo(js("window.close();"));
		

?>
