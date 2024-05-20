<?php
session_start();
include("funciones.php");
//include("conversor_num2palabras.php");

$modulo = "pruebas_solemnes_tapa";
include("validar_modulo.php");

$prueba   = $_REQUEST['prueba'];
$id_curso = $_REQUEST['id_curso'];
$token    = $_REQUEST['token'];

if (!is_numeric($id_curso) || empty($prueba)) { 
	echo(js("location.href='principal.php?modulo=gestion_cursos';"));
	exit;
}


$SQL_curso = "SELECT cod_asignatura||'-'||c.seccion||' '||asignatura AS asignatura,profesor AS nombre_profesor,carrera,c.semestre,c.ano,
                     to_char(c.fec_sol1,'tmDay FMDD-tmMon-YYYY') AS fec_sol1,to_char(c.fec_sol2,'tmDay FMDD-tmMon-YYYY') AS fec_sol2,
                     to_char(c.fec_sol_recup,'tmDay FMDD-tmMon-YYYY') AS fec_sol_recup
              FROM vista_cursos AS vc
              LEFT JOIN cursos AS c USING(id)
              WHERE vc.id=$id_curso AND md5(vc.id::text||vc.id_profesor::text)='$token'";
$curso = consulta_sqL($SQL_curso);

if (count($curso) > 0) {
	extract($curso[0]);

	$SQL_alumnos_curso = "SELECT rut AS rut_alumno,nombre_alumno FROM vista_cursos_alumnos WHERE id_curso IN (SELECT id FROM cursos WHERE id=$id_curso OR id_fusion=$id_curso) AND id_situacion IS NULL";	
	$alumnos_curso = consulta_sql($SQL_alumnos_curso);

	if (count($alumnos_curso) == 0) { 

		echo(msje_js("Este curso no tiene alumnos o bien ya se les ha calculado la nota final.\\n\\n No se puede continuar."));

	} else {

		switch ($prueba) {
			case "s1":
				$nro_prueba = "I"; $fecha = $fec_sol1;
				break;
			case "s2":
				$nro_prueba = "II"; $fecha = $fec_sol2;
				break;
			case "rec":
				$nro_prueba = "RECUPERATIVA"; $fecha = $fec_sol_recup;
				break;
		}

		$periodo = "{$NIVELES[$semestre-1]['nombre']} Semestre $ano";

		$HTML_tapa = "";
		for ($x=0;$x<count($alumnos_curso);$x++) {
			extract($alumnos_curso[$x]);
			include("fmt/prueba_solemne.php");
			$HTML_tapa .= $texto_docto . "<!-- PAGE BREAK -->";
		}


		$HTML = "<html>".$LF
			  . "  <head>".$LF
			  . "    <title>UMC - SGU - PRUEBAS SOLEMNES</title>".$LF
			  . "    <style>".$LF
			  . "      td { font-size: 12px; font-family: sans,arial,helvetica; }".$LF
			  . "      @media print {".$LF
			  . "        @page {page-break-after: always; size: 21.5cm 25cm; }".$LF
			  . "        td { font-size: 11px; font-family: sans,arial,helvetica; }".$LF
			  . "      }".$LF
			  . "    </style>".$LF
			  . "  </head>".$LF
			  . "  <body>".$LF
			  . $HTML_tapa.$LF
			  . "  </body>".$LF
			  . "</html>".$LF;

		$HTML = iconv("UTF-8","ISO-8859-1//TRANSLIT",$HTML);
		$archivo = "prueba_solemne_".$id_curso;
		file_put_contents("tmp/".$archivo,$HTML);
		$html2pdf = "htmldoc -t pdf --fontsize 11 --fontspacing 1.5 --no-strict --size 21.5x27.94cm --bodyfont helvetica "
				  . "--left 1cm --top 1cm --right 1cm --bottom 1cm --footer '   ' --header '   ' --no-embedfonts "
				  . "--compression=9 "
				  . "--webpage tmp/$archivo -f tmp/$archivo.pdf";
		shell_exec($html2pdf);

		header("Content-Type: application/pdf");
		header("Content-Disposition: attachment; filename=\"$archivo.pdf\"");
		
		$SQL_campo = $prueba."_arch";
		$SQL_arch = "SELECT $SQL_campo 
					 FROM cursos_pruebas cp 
					 LEFT JOIN cursos c ON c.id=cp.id_curso 
					 WHERE id_curso='$id_curso' AND md5(id_curso::text||id_profesor::text)='$token'";
		$arch     = consulta_sql($SQL_arch);
		$arch_prueba = pg_unescape_bytea($arch[0][$SQL_campo]);
		if (!empty($arch_prueba)) {
			$nombre_arch_prueba = "prueba_".$SQL_campo."_$id_curso";
			for ($x=0;$x<count($alumnos_curso);$x++) {
				file_put_contents("tmp/$nombre_arch_prueba"."_$x.pdf",$arch_prueba);
			}
			shell_exec("pdftk tmp/$nombre_arch_prueba_*.pdf cat output tmp/comb_$nombre_arch_prueba.pdf");
			array_map('unlink',glob("tmp/$nombre_arch_prueba*.pdf"));

			passthru("pdftk A=tmp/$archivo.pdf B=tmp/comb_$nombre_arch_prueba.pdf shuffle output -");
			unlink("tmp/comb_$nombre_arch_prueba.pdf");
		} else {
			passthru("cat /var/www/sgu/tmp/$archivo.pdf");
		}
		
		unlink($archivo);
		unlink("tmp/$archivo.pdf");
	}
} else {
	echo(msje_js("Error de consistencia. No se puede continuar"));
}
echo(js("window.close()"));
echo(js("history.back()"));
echo(js("parent.jQuery.fancybox.close()"));

?>
