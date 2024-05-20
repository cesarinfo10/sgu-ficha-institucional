<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_examen = $_REQUEST['id_examen'];

$aCampos = array('tema','tipo','virtual','fecha_examen','sala','id_escuela',
                 'id_ministro_de_fe','ministro_de_fe_modalidad','observaciones'
                );

if ($_REQUEST['guardar'] == "💾 Guardar") {

	$_REQUEST['fecha_examen'] = $_REQUEST['fecha'] . " " . $_REQUEST['hora'];

	$problema_sala = false;
	if (!empty($_REQUEST['sala'])) {
		$SQL_examen = "SELECT 1 FROM examenes_terminales WHERE id<>$id_examen AND fecha_examen='{$_REQUEST['fecha_examen']}' AND sala='{$_REQUEST['sala']}'";
		$examen     = consulta_sql($SQL_examen);
		if (count($examen) > 0) { 
			$problema_sala = true;
			echo(msje_js("ERROR: Actualmente ya existe otro examen programado en la sala, fecha y hora indicados.\n\n"
			            ."No es posible guardar los cambios."));
		}
	}

	if (!$problema_sala) {
		$SQL_upd = "UPDATE examenes_terminales SET ".arr2sqlupdate($_REQUEST,$aCampos)." WHERE id=$id_examen";
		if (consulta_dml($SQL_upd) > 0) {
	
			$SQL_docentes = "SELECT etd.id,etd.id_profesor,etd.funcion,etd.area,etd.modalidad
							 FROM examenes_terminales_docentes AS etd
							 WHERE id_examen=$id_examen
							 ORDER BY etd.id";
			$docentes = consulta_sql($SQL_docentes);
	
			$SQL_upd = "";
			$x = 0;
			foreach($_REQUEST AS $campo => $valor) {
				
				$id_doc = ($docentes[$x]['id'] > 0) ? $docentes[$x]['id'] : "0$x";
	
				if ($campo == "docente$id_doc" && is_numeric($valor)) {
					$id_profesor = $valor; 
					$funcion     = ($_REQUEST['funcion'.$id_doc] == "") ? "null" : "'{$_REQUEST['funcion'.$id_doc]}'";
					$area        = ($_REQUEST['area'.$id_doc] == "") ? "null" : "'{$_REQUEST['area'.$id_doc]}'";
					$modalidad   = ($_REQUEST['modalidad'.$id_doc] == "") ? "null" : "'{$_REQUEST['modalidad'.$id_doc]}'";
					if ($docentes[$x]['id'] > 0) {
						$SQL_upd .= "UPDATE examenes_terminales_docentes 
									 SET id_profesor=$id_profesor,
										 funcion=$funcion,
										 area=$area,
										 modalidad=$modalidad 
									 WHERE id=$id_doc AND id_examen=$id_examen; ";
					} else {
						$SQL_upd .= "INSERT INTO examenes_terminales_docentes (id_examen,id_profesor,funcion,area,modalidad) "
								 .  "VALUES ($id_examen,$id_profesor,$funcion,$area,$modalidad); ";
					}
					$x++;
				}
			}
			consulta_dml($SQL_upd);
			if ($_REQUEST['estado'] <> $_REQUEST['estado_original']) {
				consulta_dml("UPDATE examenes_terminales SET estado='{$_REQUEST['estado']}', estado_fecha=now() WHERE id=$id_examen");
			}
			echo(msje_js("Se han guardado exitosamente los datos."));
			echo(js("location.href = '$enlbase_sm=examenes_terminales_ver&id_examen=$id_examen';"));
			exit;
		} else {
			echo(msje_js("ERROR: Ha ocurrido un error y NO se han guardado los datos."));
		}
	}
}

$SQL_grupo_estud = "SELECT char_comma_sum(va2.nombre) 
                    FROM examenes_terminales_estudiantes AS ete 
					LEFT JOIN vista_alumnos AS va2 ON va2.id=ete.id_alumno 
					LEFT JOIN alumnos AS a2 ON a2.id=va2.id
					LEFT JOIN carreras AS c2 ON c2.id=a2.carrera_actual
					WHERE ete.id_exam_term=et.id $cond_estudiantes";

$SQL_exam_term = "SELECT *,to_char(fecha_examen,'YYYY-MM-DD') AS fecha,to_char(fecha_examen,'HH24:MI') AS hora,
                         to_char(estado_fecha,'dd-tmMon-YYYY') AS estado_fecha,($SQL_grupo_estud) AS estudiantes
                  FROM examenes_terminales AS et
				  WHERE id=$id_examen";	
$exam_term = consulta_sql($SQL_exam_term);

if (count($exam_term) == 1) {
	$_REQUEST = array_merge($_REQUEST,$exam_term[0]);

	$SQL_docentes = "SELECT etd.id,etd.id_profesor,etd.funcion,etd.area,etd.modalidad
	                 FROM examenes_terminales_docentes AS etd
					 WHERE id_examen=$id_examen
					 ORDER BY etd.id";
	$docentes = consulta_sql($SQL_docentes);

	$DOCENTES = consulta_sql("SELECT id,nombre FROM vista_profesores WHERE activo ORDER BY nombre");

	$DOCENTES_FUNCION = consulta_sql("SELECT id,nombre FROM vista_exam_term_profes_funciones");

	$TIPO_CLASE = consulta_sql("SELECT id,nombre FROM vista_tipo_clase WHERE nombre <> 'Híbrida'");

	$HTML = "";
	$cant_docentes = 3;
	for ($x=0;$x<$cant_docentes;$x++) {

		$required = ($x==0) ? "required" : "";
		$id_doc = ($docentes[$x]['id'] > 0) ? $docentes[$x]['id'] : "0$x";

		$select_docente = "    <select name='docente$id_doc' id='docente$x' class='filtro' $required>"
						. "      <option value=''>-- Escriba para buscar --</option>"
						.        select_group($DOCENTES,$docentes[$x]['id_profesor'])
						. "    </select>";
		$select_funcion = "    <select name='funcion$id_doc' id='funcion$x' class='filtro' $required>"
						. "      <option value=''>--</option>"
						.        select($DOCENTES_FUNCION,$docentes[$x]['funcion'])
						. "    </select>";
		$input_area     = "    <input type='text' name='area$id_doc' id='area$x' style='width: 200px; height: 35px' class='boton' value='{$docentes[$x]['area']}'>";
		$select_modalidad = "    <select name='modalidad$id_doc' id='modalidad$x' class='filtro' $required>"
						. "      <option value=''>--</option>"
						.        select($TIPO_CLASE,$docentes[$x]['modalidad'])
						. "    </select>";

		$HTML .= "<tr class='filaTabla'>"
			  .  "  <td class='textoTabla'>$select_docente</td>"
			  .  "  <td class='textoTabla'>$select_funcion</td>"
			  .  "  <td class='textoTabla'>$input_area</td>"
			  .  "  <td class='textoTabla'>$select_modalidad</td>"
			  .  "</tr>";
	}

	$JS = "";
	for ($x=0;$x<$cant_docentes;$x++) { 

		$JS .= "$(document).ready(function () { $('#docente$x').selectize({ sortField: 'text' }); });\n\n"
			.  "$(document).ready(function () { $('#funcion$x').selectize({ sortField: 'text' }); });\n\n "
			.  "$(document).ready(function () { $('#modalidad$x').selectize({ sortField: 'text' }); });\n\n "; 
	}

	$HTML .= js($JS);

	$HTML_docentes = $HTML;

	/*
	$docentes = consulta_sql("SELECT id_profesor,funcion,id_linea_tematica,modalidad FROM examenes_terminales_docentes WHERE id_examen=$id_examen");
	$y = 1;
	for ($x=0;$x<count($docentes);$x++) {
		$doc = array("docente$y"        => $docentes[$x]['id_profesor'],
					 "funcion$y"        => $docentes[$x]['funcion'], 
					 "linea_tematica$y" => $docentes[$x]['id_linea_tematica'],
					 "modalidad$y"      => $docentes[$x]['modalidad']);
		$_REQUEST = array_merge($_REQUEST,$doc);
		$y++;
	}
	*/
	
	include("examenes_terminales_formulario.php");	
}

?>


<!-- Fin: <?php echo($modulo); ?> -->
