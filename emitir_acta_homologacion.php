<?php
session_start();
include("funciones.php");
//include("conversor_num2palabras.php");

$modulo = "emitir_acta_homologacion";
include("validar_modulo.php");

$id_alumno = $_REQUEST['id_alumno'];
$id_malla_nueva = $_REQUEST['id_malla_nueva'];
if (!is_numeric($id_alumno) || !is_numeric($id_malla_nueva)) {
	echo(js("window.close();"));
	exit;
}

$SQL_prehomo = "SELECT ph.*,m.ano AS ano_malla_nueva,m.carrera AS carrera_nueva,m.alias_carrera AS alias_carrera_nueva,
                       coalesce(cant_asig_oblig,0)+coalesce(cant_asig_elect,0)+coalesce(cant_asig_efp,0) AS cant_asig_malla_nueva,
                       u2.nombre||' '||u2.apellido AS nombre_director_escuela,
                       CASE u2.sexo WHEN 'f' THEN 'Directora' ELSE 'Director' END AS vocativo_director
                FROM prehomologaciones AS ph
                LEFT JOIN usuarios AS u ON u.id=id_creador
                LEFT JOIN vista_mallas AS m ON m.id=ph.id_malla_nueva
                LEFT JOIN usuarios AS u2 ON u2.id=(SELECT id_director FROM escuelas WHERE id=m.id_escuela)
                WHERE id_alumno = $id_alumno AND id_malla_nueva=$id_malla_nueva";
$prehomo = consulta_sql($SQL_prehomo);
if (count($prehomo) > 0) {
	extract($prehomo[0]);
	$id_prehomo = $prehomo[0]['id'];
	$prehomo_det = consulta_sql("SELECT * FROM prehomologaciones_detalle WHERE id_prehomo = $id_prehomo");
	if (count($prehomo_det) > 0) {
		$SQL_plan_ant = "SELECT malla_actual,count(ca.id) AS cant
		                 FROM prehomologaciones_detalle phd 
		                 LEFT JOIN cargas_academicas AS ca on ca.id=phd.id_ca
		                 LEFT JOIN alumnos AS a ON a.id=ca.id_alumno
		                 WHERE id_prehomo = $id_prehomo
		                 GROUP BY malla_actual";
		                 
		$SQL_malla_ant   = "SELECT malla_actual FROM ($SQL_plan_ant) AS pa ORDER BY cant DESC LIMIT 1";
		
		$SQL_malla_ant = "SELECT vm.carrera AS carrera_antigua,vm.alias_carrera AS alias_carrera_antigua,e.nombre AS escuela_antigua,ano AS ano_malla_antigua
		                  FROM vista_mallas AS vm
		                  LEFT JOIN escuelas AS e ON e.id=vm.id_escuela
		                  WHERE vm.id IN ($SQL_malla_ant)";                  
		$malla_ant = consulta_sql($SQL_malla_ant);
		extract($malla_ant[0]);
		
		$SQL_alumno = "SELECT upper(apellidos)||' '||initcap(nombres) AS nombre_alumno,trim(a.rut) AS rut_alumno,
		                      c.nombre AS carrera_actual,e.nombre AS escuela_actual,m.ano AS ano_malla_antigua,
		                      semestre_cohorte||'-'||cohorte AS cohorte_alumno,r.nombre AS regimen_carrera,carrera_actual
		               FROM alumnos AS a
		               LEFT JOIN carreras  AS c ON c.id=a.carrera_actual
		               LEFT JOIN regimenes AS r ON r.id=c.regimen
		               LEFT JOIN escuelas  AS e ON e.id=c.id_escuela
		               LEFT JOIN mallas    AS m ON m.id=a.malla_actual
		               LEFT JOIN usuarios  AS u ON u.id=e.id_director
		               WHERE a.id=$id_alumno";
		$alumno = consulta_sql($SQL_alumno);
		extract($alumno[0]);
		
		$SQL_prehomo_det = "SELECT nivel,vdm.cod_asignatura||' '||vdm.asignatura AS asignatura_equivalente,vac.asignatura AS asignatura_aprobada,
		                           vac.nf,vac.semestre||'-'||vac.ano AS periodo
		                    FROM prehomologaciones_detalle AS phd
		                    LEFT JOIN vista_alumnos_cursos AS vac ON vac.id=phd.id_ca
		                    LEFT JOIN vista_detalle_malla AS vdm ON (vdm.id_prog_asig=phd.id_prog_asig AND vdm.id_malla=$id_malla_nueva)
		                    WHERE id_prehomo=$id_prehomo
		                    ORDER BY nivel,asignatura_equivalente";
		$prehomo_det = consulta_sql($SQL_prehomo_det);
		
		$cant_asig_prehomo = count($prehomo_det);
		$porc_prehomo = round(($cant_asig_prehomo/$cant_asig_malla_nueva)*100,0);
		$HTML = "";
		for ($x=0;$x<count($prehomo_det);$x++) {
			extract($prehomo_det[$x]);
			$HTML .= "<tr>"
			      .  "  <td align='center'>$nivel</td>"
			      .  "  <td>$asignatura_equivalente</td>"
			      .  "  <td>$asignatura_aprobada</td>"
			      .  "  <td align='center'>$nf</td>"
			      .  "  <td align='center'>$periodo</td>"
			      .  "</tr>";
		}
		$HTML_asignaturas_homologadas = $HTML;
		
		$fecha = strftime("%e de %B de %Y",strtotime($fecha));

		include("fmt/acta_homologacion.php");
		$nombre_archivo = "tmp/acta_homologacion_".date("Ymd");
		
		
		$HTML = "<html>".$LF
              . "  <head>".$LF
              . "    <title>UMC - SGU - Acta de Homologación</title>".$LF
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
              . "      <tr><td>$docto</td></tr>".$LF
              . "    </table>".$LF
              . "  </body>".$LF
              . "</html>".$LF;
		$HTML = iconv("UTF-8","ISO-8859-1",$HTML);
				
		$archivo = $nombre_archivo;
		file_put_contents($archivo,$HTML);
		$html2pdf = "htmldoc -t pdf --fontsize 11 --fontspacing 1.25 --no-strict --size 21.5x33.02cm --bodyfont helvetica "
				  . "--left 2cm --top 5cm --right 1cm --bottom 2cm --footer '   ' --header '   ' --no-embedfonts "
				  . "--compression=9 --encryption --permissions print,no-copy,no-annotate,no-modify "
				  . "--webpage $archivo ";
		header("Content-Type: application/pdf");
		header("Content-Disposition: attachment; filename=$archivo.pdf");
		passthru($html2pdf);
		unlink($archivo);
		echo(js("window.close()"));
		echo(js("parent.jQuery.fancybox.close()"));                  
	}
}
?>
