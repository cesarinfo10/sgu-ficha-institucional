<?php

if ($titulo_profesional <> "") { $texto_titulo = "y el Título Profesional de <b>$titulo_profesional</b>"; }

$docto = "<center><h2>Acta de Examen de $tipo N° $id_examen</h2></center>
          <p align='justify'>
            En Santiago, a $fecha_examen a las $hora_examen horas, se procedió a tomar examen para obtener el Grado Académico de <b>$grado_academico</b> $texto_titulo
            de la UNIVERSIDAD MIGUEL DE CERVANTES, a $vocativo_alumno <b>$nombre_alumno</b>, RUT $rut_alumno,
            de la cohorte $cohorte_alumno, plan de estudios año $ano_malla, de la carrera de <b>$carrera_alumno</b>.<br>
            <br>
            Tema: <b>\"$tema\"</b><br>
            <br>
            El examen fue tomado por todos los miembros de la comisión examinadora, quienes calificaron al egresado (a) de común acuerdo con la nota:<br>
          </p>
          <table border='0.5' cellpadding='2' cellspacing='0' width='100%'>
            <tr>
              <td width='30%' align='center'>Calificación en números</td>
              <td width='70%' align='center'>Calificación en palabras</td>
            </tr>
            <tr>
              <td>&nbsp;<br>&nbsp;</td>
              <td>&nbsp;<br>&nbsp;</td>
            </tr>
          </table>
          <br>
          <table border='0.5' cellpadding='5' cellspacing='0' width='100%'>
            <tr>
              <td width='45%' align='center'>Integrantes de la Comisión Examinadora</td>
              <td width='15%' align='center'>Nota</td>
              <td width='40%' align='center'>Firma</td>
            </tr>
            $HTML_docentes
          </table>
          <p align='justify'>
            Para constancia firman los Docentes integrantes de la Comisión Examinadora.<br>
            <br>
            En consecuencia, el presente examen se encuentra: ( &nbsp;&nbsp; ) Aprobado  ( &nbsp;&nbsp; ) Reprobado
            <br>
          </p>
          <table border='0.5' cellpadding='4' cellspacing='0' width='100%'>
            <tr height='20'><td valign='top'>Observaciones:</td></tr>
            <tr height='20'><td>&nbsp;</td></tr>
            <tr height='20'><td>&nbsp;</td></tr>
          </table>
          <br><br>
          <center>
          <b>$nombre_ministro_de_fe</b><br>
          Ministro (a) de Fé - $cargo_ministro_de_fe Escuela de $nombre_escuela<br>
          Presidente (a) de la Comisión
          </center>";

?>
