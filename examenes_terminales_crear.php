<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$aCampos = array('estado','tema','tipo','virtual','fecha_examen','sala','id_escuela',
                 'id_ministro_de_fe','ministro_de_fe_modalidad','observaciones','id_creador'
                );

if ($_REQUEST['guardar'] == "💾 Guardar") {

	$_REQUEST['fecha_examen'] = $_REQUEST['fecha'] . " " . $_REQUEST['hora'];
	$_REQUEST['id_creador'] = $_SESSION['id_usuario'];

	if (!empty($_REQUEST['sala'])) {
		$SQL_examen = "SELECT 1 FROM examenes_terminales WHERE fecha_examen='{$_REQUEST['fecha_examen']}' AND sala='{$_REQUEST['sala']}'";
		$examen     = consulta_sql($SQL_examen);
		if (count($examen) > 0) { 
			$_REQUEST['sala'] = "";
			echo(msje_js("ERROR: Actualmente ya existe otro examen programado en la sala, fecha y hora indicados.\n\n"
			            ."Se guardará este examen de todas maneras, pero sin la sala seleccionada (quedará en blanco dicho campo)."));
		}
	}

	$SQL_ins = "INSERT INTO examenes_terminales ".arr2sqlinsert($_REQUEST,$aCampos);
	if (consulta_dml($SQL_ins) > 0) {

		$exam_term    = consulta_sql("SELECT id FROM examenes_terminales WHERE id_creador={$_SESSION['id_usuario']} ORDER BY id DESC LIMIT 1");
		$id_exam_term = $exam_term[0]['id'];

		$SQL_ins = "";
		$x = 1;
		foreach($_REQUEST AS $campo => $valor) {
			if ($campo == "id_alumno$x" && is_numeric($valor)) { 
				$SQL_ins .= "INSERT INTO examenes_terminales_estudiantes (id_exam_term,id_alumno) VALUES ($id_exam_term,$valor); ";
				$x++;
			}
		}
		consulta_dml($SQL_ins);

		$SQL_ins = "";
		$x = 1;
		foreach($_REQUEST AS $campo => $valor) {
			if ($campo == "docente$x" && is_numeric($valor)) { 
				$funcion   = ($_REQUEST['funcion'.$x] == "") ? "null" : $_REQUEST['funcion'.$x];
				$area      = ($_REQUEST['area'.$x] == "") ? "null" : $_REQUEST['area'.$x];
				$modalidad = ($_REQUEST['modalidad'.$x] == "") ? "null" : $_REQUEST['modalidad'.$x] ;
				$SQL_ins .= "INSERT INTO examenes_terminales_docentes (id_examen,id_profesor,funcion,area,modalidad) "
				         .  "VALUES ($id_exam_term,$valor,'$funcion',$area,'$modalidad'); ";
				$x++;
			}
		}
		consulta_dml($SQL_ins);

		echo(msje_js("Se han guardado exitosamente los datos."));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	} else {
		echo(msje_js("ERROR: Ha ocurrido un error y NO se han guardado los datos."));
	}
}

$_REQUEST['fec_inicio'] = date("Y-m-d");
include("examenes_terminales_formulario.php");
?>


<!-- Fin: <?php echo($modulo); ?> -->
