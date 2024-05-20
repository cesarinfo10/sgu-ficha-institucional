<?php
session_start();
include("funciones.php");
//include("conversor_num2palabras.php");

$modulo = "certificado";
include("validar_modulo_uid_no_cero.php");

$folio = $_REQUEST['folio'];

$SQL_total_horas_carrera = "SELECT SUM(horas_semanal*nro_semanas_semestrales) AS total 
							FROM detalle_mallas AS dm 
							LEFT JOIN prog_asig AS pa ON pa.id=dm.id_prog_asig 
							WHERE dm.id_malla=a.malla_actual
							GROUP BY dm.id_malla";
							
$SQL_certificado = "SELECT cert.nombre AS nombre_docto,trim(a.rut) AS rut_alumno,a.id AS id_alumno,
                           coalesce(a.cohorte_reinc,a.cohorte) AS cohorte,
                           coalesce(a.mes_cohorte_reinc,a.mes_cohorte) AS mes_cohorte,a.carrera_actual,                           
                           coalesce(a.semestre_cohorte_reinc,a.semestre_cohorte) AS semestre_cohorte,
                           CASE a.genero WHEN 'm' THEN 'don' WHEN 'f' THEN 'doña' END AS vocativo_alumno,
                           upper(a.nombres||' '||a.apellidos) AS nombre_alumno,c.nombre AS carrera_alumno,
                           CASE a.jornada WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada_alumno,
                           c.alias AS carrera_alias_alumno,texto_adicional,ac.firma1,ac.firma2,ano_academico,semestre_academico,
                           date_part('year',ac.fecha) AS ano_cert,a.malla_actual AS al_malla_actual,ae.nombre AS estado_alumno,
                           ac.fecha::date AS fecha_cert,ac.otros,
                           a.fecha_egreso::date AS fecha_egreso_alumno,
                           a.examen_grado_titulo_fecha::date AS examen_grado_titulo_fecha_alumno,
						   a.fecha_inicio_programa::date AS fecha_inicio_programa,
						   a.salida_int_fecha::date AS salida_int_fecha_alumno,
						   a.salida_int_nroreg_libro||'/'||date_part('year',salida_int_fecha) AS salida_int_nroreg_libro_alumno,
						   to_char(a.salida_int_calif,'9D9') AS salida_int_calif_alumno,
						   m.tns_nombre,
                           m.ano AS ano_malla,
                           a.fecha_titulacion::date AS fecha_titulacion_alumno,
                           a.fecha_graduacion::date AS fecha_graduacion_alumno,
                           aefp.examen_anual_1 AS al_examen_anual_1,aefp.examen_anual_2 AS al_examen_anual_2,
                           to_char(a.examen_grado_titulo_calif,'9D9') AS examen_grado_titulo_calif_alumno,
                           to_char(a.nota_titulacion,'9D9') AS nota_titulacion_alumno,
                           to_char(a.nota_graduacion,'9D9') AS nota_graduacion_alumno,
                           c.nombre_titulo AS nombre_titulo_alumno,c.nombre_grado AS nombre_grado_alumno,
   						   a.nro_registro_libro_tit||'/'||date_part('year',fecha_titulacion) AS nro_registro_libro_tit_alumno,
   						   a.nro_registro_libro_grado||'/'||date_part('year',fecha_graduacion) AS nro_registro_libro_grado_alumno,
                           cert.nombre_archivo,
                           to_char(cert.fecha_vigencia,'YYYYMMDD') AS fecha_vigencia,
                           m.niveles AS duracion_carrera_alumno,($SQL_total_horas_carrera) AS horas_totales_carrera_alumno,
					       a.rpnp AS rpnp_alumno
                    FROM alumnos_certificados AS ac
                    LEFT JOIN certificados    AS cert ON ac.id_certificado=cert.id
                    LEFT JOIN alumnos         AS a    ON a.id=ac.id_alumno
                    LEFT JOIN al_estados      AS ae   ON ae.id=a.estado
                    LEFT JOIN carreras        AS c    ON c.id=a.carrera_actual
					LEFT JOIN mallas          AS m    ON m.id=a.malla_actual
					LEFT JOIN alumnos_examen_final_postgrado AS aefp ON aefp.id_alumno=a.id
                    WHERE folio=$folio";
$certificado = consulta_sql($SQL_certificado);
extract($certificado[0]);

$SQL_curso_dip = "SELECT to_char(nota_final,'9D9') AS nota_final FROM cargas_academicas WHERE id_curso IN (SELECT id FROM vista_cursos WHERE id_carrera=$carrera_actual) AND id_alumno=$id_alumno";
$curso_dip     = consulta_sql($SQL_curso_dip);
if (count($curso_dip) == 0) {
    $SQL_curso_dip = "SELECT to_char(avg(nota_final),'9D9') AS nota_final FROM cargas_academicas WHERE id_alumno=$id_alumno";
    $curso_dip     = consulta_sql($SQL_curso_dip);    
}
$nota_final_dip           = $curso_dip[0]['nota_final'];
$nota_final_dip_palabras  = calificacion_palabras($curso_dip[0]['nota_final']);
$nota_final_dip_apelativo = apelativo_aprobacion($curso_dip[0]['nota_final']);

$semestre_academico = $semestres_academicos[$semestre_academico]['nombre'] . " (" . $semestres[$semestre_academico]['nombre'] . ")";

$fecha_cert                       = strftime("%e de %B de %Y",strtotime($fecha_cert));
$fecha_egreso_alumno              = strftime("%e de %B de %Y",strtotime($fecha_egreso_alumno));
$fecha_inicio_programa            = strftime("%e de %B de %Y",strtotime($fecha_inicio_programa));
$fecha_titulacion_alumno          = strftime("%e de %B de %Y",strtotime($fecha_titulacion_alumno));
$fecha_graduacion_alumno          = strftime("%e de %B de %Y",strtotime($fecha_graduacion_alumno));
$examen_grado_titulo_fecha_alumno = strftime("%e de %B de %Y",strtotime($examen_grado_titulo_fecha_alumno));
$salida_int_fecha_alumno          = strftime("%e de %B de %Y",strtotime($salida_int_fecha_alumno));
$mes_cohorte = $meses_palabra[$mes_cohorte-1]['nombre'];
	
$examen_grado_titulo_calif_palabras = calificacion_palabras($examen_grado_titulo_calif_alumno);
$salida_int_calif_palabras = calificacion_palabras($salida_int_calif_alumno);
$salida_int_calif_apelativo = apelativo_aprobacion($salida_int_calif_alumno);

$nota_titulacion_palabras = calificacion_palabras($nota_titulacion_alumno);
$nota_titulacion_apelativo = apelativo_aprobacion($nota_titulacion_alumno);

// Antiguamente
//$nota_graduacion_palabras = calificacion_palabras($examen_grado_titulo_calif_alumno);
//$nota_graduacion_apelativo = apelativo_aprobacion($examen_grado_titulo_calif_alumno);

// A contar del 3-12-20
$nota_graduacion_palabras = calificacion_palabras($nota_graduacion_alumno);
$nota_graduacion_apelativo = apelativo_aprobacion($nota_graduacion_alumno);

list($rut,$dv) = explode("-",$rut_alumno);
$rut = number_format(intval($rut),0,",",".");
$rut_alumno = "$rut-$dv";

if ($firma1 <> "") { $firma1 = nl2br($firma1); } else { $firma1 = "&nbsp;"; }
if ($firma2 <> "") { $firma2 = nl2br($firma2); } else { $firma2 = "&nbsp;"; }

$FIRMAS = "<table width='100%'>"
        . "  <tr>"
        . "    <td width='45%' align='center' valign='botom'>$firma1</td>"
        . "    <td width='10%'>&nbsp;</td>"
        . "    <td width='45%' align='center' valign='botom'>$firma2</td>"
        . "  </tr>"
        . "</table>";

$TEXTO_OTROS = "";
if (!empty($otros)) { include("fmt/otros_$nombre_archivo.php"); }

$nombre_archivo = $nombre_archivo."_".$fecha_vigencia;
include("fmt/$nombre_archivo.php");
$texto_docto = nl2br($texto_docto);

$folio_md5 = substr(md5($folio),0,16);


$HTML = "<html>".$LF
      . "  <head>".$LF
      . "    <title>UMC - SGU - $nombre_docto</title>".$LF
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
      . "      <tr><td><p align='right'>Folio: <b>$carrera_alias_alumno - $folio / $ano_cert</b></p>$texto_docto</td></tr>".$LF
      . "      <tr><td><br><br>$FIRMAS</td></tr>".$LF
      . "    </table>".$LF
      . "    <div align='center'><img src='http://localhost/sgu/php-barcode/barcode.php?code=$folio_md5&scale=1'><!-- <small><br>$folio_md5</small> --></div>".$LF
      . "    <div><small>Este documento puede ser validado en nuestra web entrando a http://www.umcervantes.cl/ y pinchando en el banner 'Validación de Certificados'</small></div>".$LF
      . "<!-- PAGE BREAK -->".$LF
      . $TEXTO_OTROS
      . "  </body>".$LF
      . "</html>".$LF;

$HTML = iconv("UTF-8","ISO-8859-1//TRANSLIT",$HTML);
$archivo = $nombre_archivo."_".$folio;
file_put_contents("tmp/$archivo",$HTML);
$html2pdf = "htmldoc -t pdf --fontsize 11 --fontspacing 1.5 --no-strict --size 21.5x27.94cm --bodyfont helvetica "
		  . "--left 2cm --top 5cm --right 2cm --bottom 1cm --footer 't /' --header '   ' --no-embedfonts "
		  . "--compression=9 --encryption --permissions print,no-copy,no-annotate,no-modify "
		  . "--webpage tmp/$archivo ";
header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=$archivo.pdf");
passthru($html2pdf);
unlink("tmp/$archivo");
consulta_dml("UPDATE alumnos_certificados SET fec_impresion=now() WHERE folio=$folio AND fec_impresion IS NULL");
certificado_email($folio);
echo(js("window.close()"));
echo(js("parent.jQuery.fancybox.close()"));
?>
