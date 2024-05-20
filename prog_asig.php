<?php

session_start();
include("funciones.php");

$id_prog_asig = $_REQUEST['id_prog_asig'];
$id_malla     = $_REQUEST['id_malla'];

if (!is_numeric($id_prog_asig)) {
	echo(js("location.href='$enlbase=gestion_asignaturas';"));
	exit;
}

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

$modulo = "prog_asig";
//include("validar_modulo.php");

$SQL_prog_asig = "SELECT pa.*,vdm.id AS id_dm,vdm.cod_asignatura||' '||vdm.asignatura AS asignatura,a.escuela,vdm.caracter,vdm.nivel
                  FROM prog_asig AS pa
                  LEFT JOIN vista_detalle_malla AS vdm ON vdm.id_prog_asig=pa.id
                  LEFT JOIN vista_asignaturas AS a ON a.codigo=pa.cod_asignatura
                  WHERE pa.id='$id_prog_asig' AND vdm.id_malla='$id_malla';";
$prog_asig     = consulta_sql($SQL_prog_asig);

if (count($prog_asig) == 1) {
	extract($prog_asig[0]);
	
	$SQL_prereq = "SELECT cod_asignatura_req||' '||asignatura_req AS asig_prereq FROM vista_requisitos_malla WHERE id_dm='$id_dm'";
	$prereq = consulta_sql($SQL_prereq);
	if (count($prereq) > 0) {
		$prerequisitos = "";
		for ($x=0;$x<count($prereq);$x++) { $prerequisitos .= $prereq[$x]['asig_prereq']."\n"; }
	} else {
		$prerequisitos = "Admisión";
	}
	
	$HTML_descripcion = $HTML_aporte_perfil_egreso = "";
	if ($descripcion <> "") { 
		$HTML_descripcion = "<!-- PAGE BREAK -->".$LF
		                  . "<img src='img/logo_umc_apaisado.jpg'><br><br>".$LF
		                  . "<b>Descripción de la Asignatura</b><br><br>".$LF
		                  . "<table width='100%' border='1' cellpadding='2' cellspacing='1'><tr><td>".nl2br(trim($descripcion))."<br><br></td></tr></table><br>".$LF;
	}
	
	if ($aporte_perfil_egreso <> "") {
		$HTML_aporte_perfil_egreso = "<b>Aporte al Perfil de Egreso</b><br><br>".$LF
		                           . "<table width='100%' border='1' cellpadding='2' cellspacing='1'><tr><td>".nl2br(trim($aporte_perfil_egreso))."<br><br></td></tr></table><br>".$LF;
	}
	$av = consulta_sql("SELECT id,referencia,visitas FROM prog_asig_audiovisuales WHERE id_prog_asig=$id_prog_asig");
	$HTML_av = "";
	if (count($av) > 0) {
		$HTML_av = "<!-- PAGE BREAK -->"
		         . "<img src='../img/logoumc_apaisado.jpg'><br><br>"
		         . "<b>Audiovisuales</b><br><br>"
		         . "<table width='100%' border='1' cellpadding='2' cellspacing='1'><tr><td><ul>";
		for($x=0;$x<count($av);$x++) {
			$referencia = preg_replace("/((http|https|www)[^\s]+)/", '<a href="$1" target="_blank">$0</a>', $av[$x]['referencia']);
			$referencia = preg_replace("/href=\"www/", 'href="http://www', $referencia);
			$HTML_av .= "<li style='margin-top: 5px'>$referencia</li>";
		}
		$HTML_av .= "</ul><br></td></tr></table><br>".$LF;
	}	
	
//	$bib_obligatoria = preg_replace("/((http|https|www)[^\s]+)/", "<a href='$1' target='_blank'>$0</a>", $bib_obligatoria);
//	$bib_obligatoria = preg_replace("/href=\"www/", "href='http://www", $bib_obligatoria);

//	$bib_complement = preg_replace("/((http|https|www)[^\s]+)/", "<a href='$1' target='_blank'>$0</a>", $bib_complement);
//	$bib_complement = preg_replace("/href=\"www/", "href='http://www", $bib_complement);
	
	$HTML = ""; 
	include("fmt/prog_asig_formato.php");
	
	$HTML = iconv("UTF-8","ISO-8859-1//TRANSLIT",$HTML);
	//echo($HTML);
	$archivo = "prog_asig_".$id_prog_asig;
	$hand=fopen("tmp/$archivo","w");
	fwrite($hand,$HTML);
	fclose($hand);	
	$html2pdf = "htmldoc -t pdf --fontsize 10 --fontspacing 1 --no-strict --size 21.59x27.94cm --bodyfont helvetica "
	          . "--left 2cm --top 1cm --right 1cm --bottom 1cm --footer '  /' --header '   ' --no-embedfonts "
	          . "--compression=9 --encryption --permissions print,no-copy,no-annotate,no-modify "
	          . "--webpage tmp/$archivo ";
	header("Content-Type: application/pdf");
	header("Content-Disposition: attachment; filename=$archivo.pdf");
	passthru($html2pdf);
	unlink("tmp/$archivo");
	echo(js("window.close();"));	
}
?>
