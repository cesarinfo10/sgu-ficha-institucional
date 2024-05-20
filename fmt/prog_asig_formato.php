<?php

$titulo = "<img src='../img/logoumc_apaisado.jpg'><br><br>"
        . "<b>VICERRECTORÍA ACADÉMICA</b><br>"
        . "<b>Escuela de $escuela</b><br><br>"
        . "<center>PROGRAMA DE ASIGNATURA O ACTIVIDAD CURRICULAR (Prácticas)<br>"
        . "<sup>Sistema de Gestión Universitaria (SGU)<br></sup></center>";

$PROG_ASIG = "<br>".$LF
           . "Nombre de la asignatura o actividad curricular<br><br>".$LF
		   . "<table width='100%' border='1' cellpadding='2' cellspacing='1'><tr><td>$asignatura</td></tr></table><br>".$LF
           . "Carácter de la asignatura (Obligatoria/Electiva)<br><br>".$LF
		   . "<table width='100%' border='1' cellpadding='2' cellspacing='1'><tr><td>$caracter</td></tr></table><br>".$LF
           . "Prerrequisitos<br><br>".$LF
		   . "<table width='100%' border='1' cellpadding='2' cellspacing='1'><tr><td>".nl2br($prerequisitos)."</td></tr></table><br><br>".$LF
		   . "<table width='100%' border='2' cellpadding='2' cellspacing='1'>"
		   . "  <tr><td>Ubicación dentro del plan de estudio (Semestre)</td><td>$nivel</td></tr>"
		   . "  <tr><td>Nº de horas pedagógicas (45 minutos) semanales</td><td>$horas_semanal</td></tr>"
		   . "  <tr><td>Número de semanas de clases por semestre</td><td>$nro_semanas_semestrales</td></tr>"
//		   . "  <tr><td>Carga académica semanal estimada de estudio o trabajo autónomo del alumno(a)</td><td>$carga_acad_sem</td></tr>"
		   . "</table><br>".$LF
//           . "<img src='img/logo_umc_apaisado.jpg'><br><br>"
           . $HTML_descripcion
           . $HTML_aporte_perfil_egreso
           . "<b>Objetivos Generales y Específicos de la Asignatura o Actividad Curricular</b>"
           . "<p align='justify'><small>"
           . "Describir el o los objetivos de la asignatura que indique su propósito general en el contexto de "
           . "formación que entrega la carrera , así como los objetivos específicos orientados al logro de conocimientos, "
           . "habilidades, destrezas o competencias que se pretende alcanzar con los alumno durante el desarrollo de la "
           . "asignatura o actividad curricular<br><br></small></p>".$LF
		   . "<table width='100%' border='1' cellpadding='2' cellspacing='1'>"
		   . "  <tr><td>"
		   . "    <b>Objetivos Generales</b><br><br>".nl2br(trim($obj_generales))."<br><br>"
		   . "    <b>Objetivos Específicos</b><br><br>".nl2br(trim($obj_especificos))."<br><br>"
		   . "  </td></tr>"
		   . "</table><br>".$LF

           . "<!-- PAGE BREAK -->"
           
           . "<img src='../img/logoumc_apaisado.jpg'><br><br>"
           . "<b>Contenidos de la asignatura o actividad curricular</b><br>"
           . "Indicar los contenidos, organizados por unidades, que serán desarrollados durante el curso de acuerdo "
           . "con los objetivos generales y específicos definidos para la asignatura o actividad curricular.<br><br>".$LF
		   . "<table width='100%' border='1' cellpadding='2' cellspacing='1'><tr><td>".nl2br(trim($contenidos))."<br><br></td></tr></table><br>".$LF
		   
           . "<!-- PAGE BREAK -->"

           . "<img src='../img/logoumc_apaisado.jpg'><br><br>"
           . "<b>Metodología docente que se utilizará en la asignatura o actividad curricular</b><br>"
           . "Se deberán describir los métodos de enseñanza que se emplearán a lo largo del curso, los cuales deben "
           . "ser consistentes con sus objetivos y contenidos.<br><br>".$LF
		   . "<table width='100%' border='1' cellpadding='2' cellspacing='1'><tr><td>".nl2br(trim($met_instruccion))."<br><br></td></tr></table><br>".$LF
		   
           . "<!-- PAGE BREAK -->"
           
           . "<img src='../img/logoumc_apaisado.jpg'><br><br>"
           . "<b>Evaluación de la asignatura</b><br>"
           . "Se deberá describir los métodos de evaluación para medir los objetivos propuestos en la asignatura, los que "
           . "deben mostrar coherencia con los métodos de enseñanza definidos. Se deberá señalar, a lo menos, los tipos de "
           . "evaluaciones, la cantidad de evaluaciones en el período académico, las ponderaciones respectivas y los criterios "
           . "de aprobación de las evaluaciones y del curso.<br><br>".$LF
		   . "<table width='100%' border='1' cellpadding='2' cellspacing='1'><tr><td>".nl2br(trim($evaluacion))."<br><br></td></tr></table><br>".$LF
		   
           . "<!-- PAGE BREAK -->"

           . "<img src='../img/logoumc_apaisado.jpg'><br><br>"
           . "<b>Bibliografía mínima obligatoria y bibliografía complementaria</b><br>"
           . "Se deberá indicar las referencias separadamente, incluyendo, a lo menos, autor, título, año de publicación, "
           . "lugar de publicación, número de edición si corresponde, y editorial.<br><br>".$LF
		   . "<table style='table-layout:fixed' width='100%' border='1' cellpadding='2' cellspacing='1'>"
		   . "  <tr>"
		   . "    <td bgcolor='#e5e5e5' width='75%'><b>Bibliografía mínima obligatoria y complementaria</b><br>(señalar autor, título, editorial y año)</td>"
		   . "    <td bgcolor='#e5e5e5' width='25%'><b>Otras asignaturas que requerirán este título*</b></td>"
		   . "  </tr>"
		   . "  <tr>"
		   . "    <td width='75%'>"
		   . "      <b>Bibliografía Obligatoria</b><br><br>".nl2br(wordwrap($bib_obligatoria,80,"\n",true))."<br><br>"
		   . "      <b>Bibliografía Complementaria</b><br><br>".nl2br(wordwrap($bib_complement,80,"\n",true))."<br><br>"
		   . "    </td width='25%'>"
		   . "    <td><br><br><br>".nl2br($bib_oblig_otras_asig)."<br><br><br><br>".nl2br($bib_compl_otras_asig)."<br><br></td>"
		   . "  </tr>"
		   . "</table><br>".$LF
		   
           . $HTML_av;
	
        
$HTML = "<html>".$LF
      . "  <head>".$LF
      . "    <title>UMC - SGU - Programa de Asignatura: $asignatura</title>".$LF
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
      . "        <td>$titulo</td>".$LF
      . "      </tr>".$LF
      . "      <tr>".$LF
      . "        <td>$PROG_ASIG</td>".$LF
      . "      </tr>".$LF
      . "    </table>".$LF
      . "  </body>".$LF
      . "</html>".$LF;

$HTML2 = "    <table width='100%'>".$LF
       . "      <tr>".$LF
       . "        <td>$titulo</td>".$LF
       . "      </tr>".$LF
       . "      <tr>".$LF
       . "        <td>$PROG_ASIG</td>".$LF
       . "      </tr>".$LF
       . "    </table>".$LF

?>
