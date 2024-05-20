<?php

if ($tipo == "art21") {
	switch ($regimen_carrera) {
		case "Pregrado":
			$art21 = " en virtud del Artículo 21 del Reglamento de Estudios de Pregrado,";
			break;
		case "Post-Grado a Distancia":
		case "Post-Título a Distancia":
			$art21 = " en virtud del Artículo 46 del Reglamento de Programas de Magister Profesional y Postitulo en Educación Modalidad a Distancia,";
			break;
	}		
}

$docto = "<center><h2>Acta de Homologación N° $id_prehomo</h2></center>
          <center><h4>Escuela de $escuela_actual</h4></center>
          <p align='right'>Santiago, $fecha</p>
          <table border='0' cellpadding='2' cellspacing='0'>
            <tr>
              <td align='right'>Nombre del Alumno:</td>
              <td colspan='3' nowrap>$nombre_alumno</td>
            </tr>
            <tr>
              <td align='right'>RUT:</td>
              <td colspan='3'>$rut_alumno</td>
            </tr>
            <tr>
              <td align='right'>Cohorte:</td>
              <td>$cohorte_alumno</td>
              <td align='right'>Régimen:</td>
              <td>$regimen_carrera</td>
            </tr>
          </table>
          <p align='justify'>
            <b>RESUELVO:</b> <br>
            Homologase las asignaturas cursadas y aprobadas en el Plan de Estudios de $carrera_antigua año $ano_malla_antigua por las 
            correspondientes del nuevo Plan de Estudios de $carrera_nueva año $ano_malla_nueva,$art21 según el siguiente detalle:  
          </p>
          <table border='1' cellpadding='2' cellspacing='0' width='100%'>
            <tr>
              <td colspan='2' align='center'><b>Plan Nuevo (Malla $alias_carrera_nueva $ano_malla_nueva)</b></td>
              <td colspan='3' align='center'><b>Plan Anterior (Malla $alias_carrera_antigua $ano_malla_antigua)</b></td>
            </tr>
            <tr>
              <td align='center'><b>Nivel</b></td>
              <td align='center'><b>Asignatura Homologada</b></td>
              <td align='center'><b>Asignatura Aprobada</b></td>
              <td align='center'><b>Nota</b></td>
              <td align='center'><b>Periodo</b></td>
            </tr>
            $HTML_asignaturas_homologadas
          </table>
          Se homologan $cant_asig_prehomo asignatura(s), equivalentes al $porc_prehomo% del nuevo plan de estudios.
          <br><br><br><br>
          <center>
          $nombre_director_escuela<br>
          $vocativo_director de Escuela<br>
          $escuela_actual
          </center>";

?>
