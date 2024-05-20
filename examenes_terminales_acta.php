<?php
session_start();
include("funciones.php");
//include("conversor_num2palabras.php");

$modulo = "examenes_terminales_acta";
include("validar_modulo.php");

$id_examen = $_REQUEST['id_examen'];

if (!is_numeric($id_examen)) {
	echo(js("window.close();"));
	exit;
}

$SQL_examen = "SELECT et.id,et.tipo,tema,vu.nombre AS nombre_ministro_de_fe,
                      CASE vu.id_tipo WHEN 1 THEN 'Director (a)' WHEN 2 THEN 'Coordinador (a)' END AS cargo_ministro_de_fe,
                      e.nombre AS nombre_escuela,
                      fecha_examen::date AS fecha_examen,
                      to_char(fecha_examen,'HH24:MI') AS hora_examen                      
               FROM examenes_terminales AS et
               LEFT JOIN vista_usuarios AS vu ON vu.id=et.id_ministro_de_fe
               LEFT JOIN escuelas       AS e  ON e.id=et.id_escuela
			   WHERE et.id=$id_examen";
$examen = consulta_sql($SQL_examen);

if (count($examen) == 1) {
    extract($examen[0]);

    $fecha_examen = strftime("%e de %B de %Y",strtotime($fecha_examen));

	$SQL_alumnos = "SELECT va.id,va.rut AS rut_alumno,upper(a.nombres||' '||a.apellidos) AS nombre_alumno,va.malla_actual AS ano_malla,
                           a.semestre_cohorte||'-'||a.cohorte AS cohorte_alumno,c.nombre AS carrera_alumno,
                           m.ga_nombre AS grado_academico,m.tp_nombre AS titulo_profesional,
	                       CASE va.genero WHEN 'Masculino' THEN 'don' WHEN 'Femenino' THEN 'doña' END AS vocativo_alumno
                    FROM examenes_terminales_estudiantes AS ete
                    LEFT JOIN vista_alumnos AS va ON va.id=ete.id_alumno
                    LEFT JOIN alumnos AS a ON a.id=ete.id_alumno
                    LEFT JOIN mallas AS m ON m.id=va.id_malla_actual
                    LEFT JOIN carreras AS c ON c.id=va.id_carrera
                    WHERE id_exam_term=$id_examen";
    $alumnos = consulta_sql($SQL_alumnos);

	$SQL_docentes = "SELECT etd.id,vp.nombre AS docente,etd.funcion,etd.area
                     FROM examenes_terminales_docentes AS etd
                     LEFT JOIN vista_profesores AS vp ON vp.id=etd.id_profesor
                     WHERE id_examen=$id_examen";
    $docentes = consulta_sql($SQL_docentes);

    if (count($docentes) == 0) {
        echo(msje_js("ERROR: No es posible obtener el acta, debido a que el examen no tiene docentes que integren la comisión."));
        echo(js("parent.jQuery.fancybox.close()"));
        echo(js("window.close();"));                  
        exit;
    }

    $HTML_docentes = "";
    for ($x=0;$x<count($docentes);$x++) {
        $HTML_docentes .= "<tr>"
                       .  "  <td><b>{$docentes[$x]['docente']}</b><br>{$docentes[$x]['funcion']} {$docentes[$x]['area']}</td>"
                       .  "  <td>&nbsp;<br>&nbsp;</td>"
                       .  "  <td>&nbsp;<br>&nbsp;</td>"
                       .  "</tr>";
    }

    $nombre_archivo = "tmp/acta_examen_terminal_$id_examen";
    $HTML_acta = "";
    for ($x=0;$x<count($alumnos);$x++) {
        extract($alumnos[$x]);
        list($rut,$dv) = explode("-",$rut_alumno);
        $rut = number_format(intval($rut),0,",",".");
        $rut_alumno = "$rut-$dv";
        include("fmt/acta_examen_terminal.php");
        $HTML_acta .= $docto . "<!-- PAGE BREAK -->";
    }
		
    $HTML = "<html>".$LF
            . "  <head>".$LF
            . "    <title>UMC - SGU - Acta de Examen de $tipo N° $id_examen</title>".$LF
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
            . "      <tr><td>$HTML_acta</td></tr>".$LF
            . "    </table>".$LF
            . "  </body>".$LF
            . "</html>".$LF;
    $HTML = iconv("UTF-8","ISO-8859-1",$HTML);
				
    $archivo = $nombre_archivo;
    file_put_contents($archivo,$HTML);
    $html2pdf = "htmldoc -t pdf --fontsize 11 --fontspacing 1.25 --no-strict --size 21.5x27.94cm --bodyfont helvetica "
                . "--left 2cm --top 3cm --right 1cm --bottom 1cm --footer '   ' --header '   ' --no-embedfonts "
                . "--compression=9 --encryption --permissions print,no-copy,no-annotate,no-modify "
                . "--webpage $archivo ";
    header("Content-Type: application/pdf");
    header("Content-Disposition: attachment; filename=$archivo.pdf");
    passthru($html2pdf);
    consulta_dml("UPDATE examenes_terminales SET acta_fecha_descarga=now(),acta_id_usuario_descarga={$_SESSION['id']} WHERE id=$id_examen");
    unlink($archivo);
    //echo(js("window.close()"));
    echo(js("parent.jQuery.fancybox.close()"));                  
	
}

?>