<?php 

$SQL_nf = "SELECT max(nota_final)::numeric(3,1) FROM cargas_academicas WHERE id_estado=1 AND id_alumno=$id_alumno AND id_curso IN (SELECT id FROM cursos WHERE id_prog_asig=vpa.id)";
	
$SQL_homologaciones = "SELECT vac.id,vac.asignatura,vpa.cod_asignatura||' '||vpa.asignatura AS homologada_por,
							  extract(YEAR from fec_mod)||'-'||CASE WHEN extract(MONTH from fec_mod) < 8 THEN '1' ELSE '2' END AS periodo,
							  ($SQL_nf) AS nf
					   FROM vista_alumnos_cursos AS vac
					   LEFT JOIN vista_prog_asig AS vpa ON vpa.id=vac.id_pa
					   WHERE vac.id_alumno=$id_alumno AND homologada
					   ORDER BY vac.asignatura";

$homologaciones = consulta_sql($SQL_homologaciones);
$cantidad = count($homologaciones);

$HTML = "<table border='0.5' cellpadding='2' cellspacing='0' width='100%'>"
	  . "  <tr>"
	  . "    <td align='center'><b>Asignatura homologada</b></td>"
	  . "    <td align='center'><b>Asignatura aprobada</b></td>"
	  . "    <td align='center'><b>Periodo</b></td>"
	  . "    <td align='center'><b>Nota</b></td>"
	  . "  </tr>";

if (count($homologaciones) == 0) {
	$HTML .= "<tr><td colspan='5'>** Es alumno no registra Homologaciones **</td></tr>";
} else {
	$promedio = 0;
	for($x=0;$x<count($homologaciones);$x++) {
		extract($homologaciones[$x]);
	
		$onclick = "onClick=\"window.location='$enlbase=editar_homologacion&id_ca=$id';\"";
		
		$HTML .= "<tr>"
			  .  "  <td> $asignatura</td>"
			  .  "  <td> $homologada_por</td>"
			  .  "  <td align='center'> $periodo</td>"
			  .  "  <td align='center'> $nf</td>"
			  .  "</tr>";
		$promedio += $nf;
	}
	$promedio = round($promedio/$cantidad,1);
	/*
	$HTML .= "<tr>"
		  .  "  <td colspan='4'>Promedio de Notas:</td>"
		  .  "  <td style='text-align: center'><b>$promedio</b></td>"
		  .  "</tr>";
	*/
}

$texto_docto = "<h2 align='center'>CERTIFICADO DE HOMOLOGACIONES</h2><p align='justify'>"
             . "La Secretaria General de la Universidad Miguel de Cervantes que suscribe certifica "
			 . "que $vocativo_alumno <b>$nombre_alumno</b>, Rut Nº <b>$rut_alumno</b>, es alumno(a) "
			 . "$estado_alumno de esta Casa de Estudios Superiores de la Carrera de $carrera_alumno, "
			 . "jornada $jornada_alumno y en virtud del cambio de plan de estudios que solicitó, se ha "
			 . "realizado la siguiente homologación de asignaturas: <br>"

			 . $HTML

			 . "</table><br><p align='justify'>"

             . "Esta Universidad autónoma, otorga grados y títulos oficiales, cumpliendo con las exigencias "
			 . "establecidas en el DFL N.º 2, de 2009, del Ministerio de Educación, y está autorizada por "
			 . "dicho Ministerio de Educación por Decreto exento N°1169, de 27 de noviembre de 1997, "
			 . "publicado en el Diario Oficial de 17 de diciembre del mismo año. "

			 . $texto_adicional

			 . "Santiago, $fecha_cert."
			 . "</p>";

?>
