<?php 

$TEXTO_OTROS = "    <table width='100%'>".$LF
             . "      <tr>".$LF
			 . "        <td align='left'><img src='../img/logoumc_apaisado.jpg' height='60'></td>".$LF
			 . "        <td align='right'>Folio: <b>$carrera_alias_alumno - $folio / $ano_cert</b></td></tr>".$LF
             . "    </table>".$LF
             . "<div align='justify'>Asimismo, se enumeran las asignaturas aprobadas, que a partir de la página 3 se anexan los programas de estudio respectivos:</div><ol>".$LF;

$ids_prog_asig = $otros;

$id_malla_electivos = consulta_sql("SELECT id_malla_actual FROM carreras WHERE alias='SELLO'"); // malla electivos
$id_malla_electivos = $id_malla_electivos[0]['id_malla_actual'];

$SQL_prog_asig = "SELECT pa.*,vdm.id AS id_dm,vdm.cod_asignatura||' '||vdm.asignatura AS asignatura,a.escuela,vdm.caracter,vdm.nivel
                  FROM prog_asig AS pa
                  LEFT JOIN vista_detalle_malla AS vdm ON vdm.id_prog_asig=pa.id
                  LEFT JOIN vista_asignaturas AS a ON a.codigo=pa.cod_asignatura
                  WHERE pa.id IN ($ids_prog_asig) AND vdm.id_malla IN ('$al_malla_actual','$id_malla_electivos')
				  ORDER BY vdm.nivel,vdm.cod_asignatura";
$prog_asig     = consulta_sql($SQL_prog_asig);


for ($j=0;$j<count($prog_asig);$j++) {
	$TEXTO_OTROS .= "<li>{$prog_asig[$j]['asignatura']}</li>".$LF;
}

$TEXTO_OTROS .= "</ol><!-- PAGE BREAK -->".$LF;

for ($j=0;$j<count($prog_asig);$j++) {
	extract($prog_asig[$j]);
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
		                  . "<img src='../img/logoumc_apaisado.jpg'><br><br>".$LF
		                  . "<b>Descripción de la Asignatura</b><br><br>".$LF
		                  . "<table width='100%' border='1' cellpadding='2' cellspacing='1'><tr><td>".nl2br(trim($descripcion))."<br><br></td></tr></table><br>".$LF;
	}
	
	if ($aporte_perfil_egreso <> "") {
		$HTML_aporte_perfil_egreso = "<b>Aporte al Perfil de Egreso</b><br><br>".$LF
		                           . "<table width='100%' border='1' cellpadding='2' cellspacing='1'><tr><td>".nl2br(trim($aporte_perfil_egreso))."<br><br></td></tr></table><br>".$LF;
	}
	$av = consulta_sql("SELECT id,referencia,visitas FROM prog_asig_audiovisuales WHERE id_prog_asig=$id");
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

	
	$HTML2 = ""; 
	include("fmt/prog_asig_formato.php");
	$TEXTO_OTROS .= $HTML2 . "<!-- PAGE BREAK -->".$LF;
}


?>
