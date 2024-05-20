<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

if ($_SESSION['id_escuela'] > 0) { $_REQUEST['id_escuela'] = $_SESSION['id_escuela']; }

$HORAS = array();
for($hora=8;$hora<=20;$hora++) { 
    for($min=0;$min<=45;$min=$min+15) {
        if ($hora<13) { $grupo = "Mañana"; }
        elseif ($hora<19) { $grupo = "Tarde"; }
        else { $grupo = "Vespertina"; }
        $horaf = sprintf("%'.02d",$hora).":".sprintf("%'.02d",$min); 
        $HORAS[] = array("id" => $horaf, "nombre" => $horaf, "grupo" => $grupo);
    }
}

$fecha_min = date("Y-m-d",strtotime($_REQUEST['fec_inicio']));
$ano_min = date("Y",strtotime($_REQUEST['ano']));

$salas = consulta_sql("SELECT codigo AS id,nombre||' ('||coalesce(capacidad,0)||' sillas)' AS nombre,'Piso '||piso||'°' AS grupo FROM salas WHERE activa ORDER BY piso,nombre;");

$ESCUELAS = consulta_sql("SELECT id,nombre FROM escuelas WHERE activa ORDER BY nombre");

$TIPOS = consulta_sql("SELECT id,nombre FROM vista_exam_terminales_tipos ORDER BY nombre");

$SQL_ministros = "SELECT id,nombre||' ['||tipo||']' AS nombre,escuela AS grupo 
                  FROM vista_usuarios 
				  WHERE tipo IN ('Dirección','Coordinación') AND activo='Si' AND escuela IS NOT NULL 
				  UNION ALL
				  SELECT u.id,u.nombre||' '||u.apellido||' [Docente de Planta]' AS nombre,e.nombre AS grupo
				  FROM usuarios AS u
				  LEFT JOIN escuelas AS e ON e.id=u.id_escuela
				  WHERE activo AND tipo=3 AND horas_planta IS NOT NULL
				  ORDER BY grupo,nombre";
$MINISTROS = consulta_sql($SQL_ministros);


$DOCENTES = consulta_sql("SELECT id,nombre FROM vista_profesores WHERE activo ORDER BY nombre");

$DOCENTES_FUNCION = consulta_sql("SELECT id,nombre FROM vista_exam_term_profes_funciones");

$cond_lt = ($_SESSION['id_escuela'] > 0) ? "WHERE id_escuela={$_SESSION['id_escuela']}" : "";
$DOCENTES_LT = consulta_sql("SELECT id,nombre,escuela AS grupo FROM vista_lineas_tematicas $cond_lt ORDER BY grupo,nombre");

$TIPO_CLASE = consulta_sql("SELECT id,nombre FROM vista_tipo_clase WHERE nombre <> 'Híbrida'");

$cond_alumnos = ($_SESSION['id_escuela'] > 0) ? "AND c.id_escuela={$_SESSION['id_escuela']}" : "";
$SQL_alumnos = "SELECT a.id,va.nombre||' '||va.rut||' '||carrera||'-'||a.jornada AS nombre,c.nombre as grupo
                FROM vista_alumnos AS va
				LEFT JOIN alumnos AS a USING (id)
				LEFT JOIN carreras AS c ON c.id=a.carrera_actual
				LEFT JOIN regimenes_ AS r ON r.id=c.regimen
				WHERE a.estado IN (1,4) AND c.regimen iN ('PRE','PRE-D','POST-G','POST-GD') $cond_alumnos
				ORDER BY r.orden,carrera,va.nombre";
$ESTUDIANTES = consulta_sql($SQL_alumnos);

$ESTADOS = consulta_sql("SELECT * FROM vista_exam_terminales_estados");

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="estado" value="Programado">
<input type="hidden" name="id_examen" value="<?php echo($_REQUEST['id_examen']); ?>">
<input type="hidden" name="estado_original" value="<?php echo($_REQUEST['estado']); ?>">
<div style='margin-top: 5px'>
  <input type="submit" name='guardar' value="💾 Guardar">
  <input type="button" name='cancelar' onClick='history.back();' value="❌ Cancelar">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes del Examen</td></tr>
<?php if ($modulo == "examenes_terminales_editar") { ?>
  <tr>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['id']); ?></td>
    <td class='celdaNombreAttr'>Estado:</td>
    <td class='celdaValorAttr'>
      <select name="estado" class='filtro' required>
		<?php echo(select($ESTADOS,$_REQUEST['estado'])); ?>
      </select>
	  <i>desde el <?php echo($_REQUEST['estado_fecha']) ?></i>
	</td>
  </tr>
<?php } ?>
  <tr>
    <td class='celdaNombreAttr'>Tema:</td>
    <td class='celdaValorAttr' colspan="3"><input type="text" size='70' name="tema" value="<?php echo($_REQUEST['tema']); ?>" <?php echo($readonly); ?> class='boton' required></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tipo:</td>
    <td class='celdaValorAttr'>
      <select name="tipo" class='filtro' required>
        <option value=''>--</option>
        <?php echo(select($TIPOS,$_REQUEST['tipo'])); ?>    
      </select>
    </td>
    <td class='celdaNombreAttr'>Virtual:</td>
    <td class='celdaValorAttr'>
      <select name="virtual" class='filtro' onChange="validar_virtual(this.value);" required>
        <option value=''>--</option>
        <?php echo(select($sino,$_REQUEST['virtual'])); ?>    
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha y hora:</td>
    <td class='celdaValorAttr'>      
      <input type="date" id="fecha" name="fecha" min="<?php echo($fecha_min); ?>" class="boton" value="<?php echo($_REQUEST['fecha']); ?>" required>
      <select name="hora" class="filtro" style="height: 25px" required>
        <option value="">-- HH:MM --</option>
        <?php echo(select_group($HORAS,$_REQUEST['hora'])); ?>
      </select>
	  <select name="sala" style="height: 25px" class='filtro'>
        <option value="">-- Sala --</option>
        <?php echo(select_group($salas,$_REQUEST['sala'])); ?>        
      </select>
    </td>
    <td class='celdaNombreAttr'>Escuela:</td>
    <td class='celdaValorAttr'>
	  <select name="id_escuela" style="height: 25px" class='filtro' required>
        <option value="">--</option>
        <?php echo(select_group($ESCUELAS,$_REQUEST['id_escuela'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Estudiante(s):</td>
    <td class='celdaValorAttr' colspan="3">
<?php if ($modulo == "examenes_terminales_crear") { ?>
	  <select name="id_alumno1" id="id_alumno1" class='filtro' required> 
		<option value=''>-- Escriba para buscar --</option>
		<?php echo(select_group($ESTUDIANTES,$_REQUEST['id_alumno1'])); ?>
      </select>
	  <select name="id_alumno2" id="id_alumno2" class='filtro'> 
		<option value=''>-- Escriba para buscar --</option>
		<?php echo(select_group($ESTUDIANTES,$_REQUEST['id_alumno2'])); ?>
      </select>
	  <select name="id_alumno3" id="id_alumno3" class='filtro'> 
		<option value=''>-- Escriba para buscar --</option>
		<?php echo(select_group($ESTUDIANTES,$_REQUEST['id_alumno3'])); ?>
      </select>
	  <select name="id_alumno4" id="id_alumno4" class='filtro'> 
		<option value=''>-- Escriba para buscar --</option>
		<?php echo(select_group($ESTUDIANTES,$_REQUEST['id_alumno4'])); ?>
      </select>
	  <select name="id_alumno5" id="id_alumno5" class='filtro'> 
		<option value=''>-- Escriba para buscar --</option>
		<?php echo(select_group($ESTUDIANTES,$_REQUEST['id_alumno5'])); ?>
      </select>
      <br>
	  NOTA: La información asociada al o los estudiantes que registre aquí no es editable a futuro.
<?php } else { echo("<li>".str_replace(",","<li>",$_REQUEST['estudiantes'])); } ?>
    </td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Comisión</td></tr>

  <tr>
    <td class='celdaNombreAttr'><label for="id_ministro_de_fe">Ministro (a) de Fé:</label></td>
    <td class='celdaValorAttr' colspan="2">
	  <select name="id_ministro_de_fe" id="id_ministro_de_fe" class='filtro' required>
		<option value=''>-- Escriba para buscar --</option>
		<?php echo(select_group($MINISTROS,$_REQUEST['id_ministro_de_fe'])); ?>
      </select>
	</td>
	<td class='celdaValorAttr'>
	  <select name="ministro_de_fe_modalidad" id="ministro_de_fe_modalidad" class='filtro' required>
		<option value=''>-- Modalidad --</option>
		<?php echo(select_group($TIPO_CLASE,$_REQUEST['ministro_de_fe_modalidad'])); ?>
      </select>     
    </td>
  </tr>
  <tr>    
    <td class='celdaValorAttr' colspan="4">
      <table bgcolor="#ffffff" cellspacing="1" cellpadding="2" width='100%' class="tabla">
	    <tr class='filaTituloTabla'>
		  <td class='tituloTabla'>Docente</td>
		  <td class='tituloTabla'>Función</td>
		  <td class='tituloTabla'>Área</td>
		  <td class='tituloTabla'>Modalidad</td>
		</tr>
<?php 
	if ($modulo == "examenes_terminales_crear") {

		$HTML = "";
		$cant_docentes = 3;

		for ($x=1;$x<=$cant_docentes;$x++) {

			$required = ($x==0) ? "required" : "";

			$select_docente = "    <select name='docente$x' id='docente$x' class='filtro' $required>"
							. "      <option value=''>-- Escriba para buscar --</option>"
							.        select_group($DOCENTES,$_REQUEST['docente'.$x])
							. "    </select>";
			$select_funcion = "    <select name='funcion$x' id='funcion$x' class='filtro' $required>"
							. "      <option value=''>--</option>"
							.        select($DOCENTES_FUNCION,$_REQUEST['funcion'.$x])
							. "    </select>";
			$select_area    = "    <input type='text' name='area$x' id='area$x' style='width: 200px; height: 35px' class='boton' value='{$_REQUEST['area'.$x]}'>";
			$select_modalidad = "    <select name='modalidad$x' id='modalidad$x' class='filtro' $required>"
							. "      <option value=''>--</option>"
							.        select($TIPO_CLASE,$_REQUEST['modalidad'.$x])
							. "    </select>";

			$HTML .= "<tr class='filaTabla'>"
				.  "  <td class='textoTabla'>$select_docente</td>"
				.  "  <td class='textoTabla'>$select_funcion</td>"
				.  "  <td class='textoTabla'>$select_area</td>"
				.  "  <td class='textoTabla'>$select_modalidad</td>"
				.  "</tr>";
		}

		echo($HTML);

		$JS = "";
		for ($x=1;$x<=$cant_docentes;$x++) { 
			$JS .= "$(document).ready(function () { $('#docente$x').selectize({ sortField: 'text' }); });\n\n"
				.  "$(document).ready(function () { $('#funcion$x').selectize({ sortField: 'text' }); });\n\n "
				.  "$(document).ready(function () { $('#linea_tematica$x').selectize({ sortField: 'text' }); });\n\n "
				.  "$(document).ready(function () { $('#modalidad$x').selectize({ sortField: 'text' }); });\n\n "; 
		}

		echo(js($JS));

	} else {
		echo($HTML_docentes);
	}

?>
      </table>
	</td>
  </tr>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Observaciones</td></tr>
  <tr>
    <td class='celdaValorAttr' colspan="4"><textarea name="observaciones" rows='5' class="general"><?php echo($_REQUEST['observaciones']); ?></textarea></td>
  </tr>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
<script>
var estudiantes = 1,
    docentes = 1;

/*
var $ESTUDIANTES = $("#id_alumno1 > option").clone();
$("#id_alumno2").append($ESTUDIANTES);
$("#id_alumno3").append($ESTUDIANTES);
$("#id_alumno4").append($ESTUDIANTES);
$("#id_alumno5").append($ESTUDIANTES);
$("#id_alumno2").append($ESTUDIANTES);
*/

function validar_virtual(valor) {
	var $select_ministro_de_fe_modalidad = $("#ministro_de_fe_modalidad").selectize();
	var selectize_ministro_de_fe_modalidad = $select_ministro_de_fe_modalidad[0].selectize;

	var $select_modalidad1 = $("#modalidad1").selectize();
	var selectize_modalidad1 = $select_modalidad1[0].selectize;



	if (valor == "t") {
		selectize_ministro_de_fe_modalidad.setValue("a Distancia",false);
		selectize_modalidad1.setValue("a Distancia",false);
	}
	if (valor == "f") {
		selectize_ministro_de_fe_modalidad.setValue("Presencial",false);
		selectize_modalidad1.setValue("Presencial",false);
	}
}

//function agregar_item_docente() {
//	var bloque_docentes = document.getElementById("docente");
//	docentes++;
//	var id = "docente"+docentes;
//
//	bloque_docentes.innerHTML += "<select name='docente[]' id='"+id+"' class='filtro'>"
//	                          +  "  <option value=''>-- Seleccione --</option>"
//				              +  "  <?php //echo(select_group($DOCENTES,$_REQUEST['docente'])); ?>"
//				              +  "</select> "
//				              +  "<a href='#' onClick='agregar_item_docente();' style='color: red' class='boton'> <big><b> + </b></big> </a><br>";
//	$(document).ready(function () {
//		$('#'+id).selectize({
//			sortField: 'text'
//		});
//	});
//}

/* function agregar_item_estudiante() {
	var bloque_estudiante = document.getElementById("estudiante");
	estudiantes++;
	var id = "docente"+estudiantes;


	bloque_estudiante.innerHTML += "<select name='estudiante[]' id='"+id+"' class='filtro'>"
	                          +  "  <option value=''>-- Seleccione --</option>"
				              +  "  <?php //echo(select_group($ESTUDIANTES,$_REQUEST['estudiante'])); ?>"
				              +  "</select> "
				              +  "<a href='#' onClick='agregar_item_estudiante();' style='color: red' class='boton'> <big><b> + </b></big> </a><br>";
							  $(document).ready(function () {
	$('#'+id).selectize({
			sortField: 'text'
		});
	});
} */

$(document).ready(function () {
	$('#id_ministro_de_fe').selectize({
		sortField: 'text'
	});
});

$(document).ready(function () {
	$('#ministro_de_fe_modalidad').selectize({
		sortField: 'text'
	});
});


$(document).ready(function () {
	$('#id_alumno1').selectize({
		sortField: 'text'
	});
});
$(document).ready(function () {
	$('#id_alumno2').selectize({
		sortField: 'text'
	});
});
$(document).ready(function () {
	$('#id_alumno3').selectize({
		sortField: 'text'
	});
});
$(document).ready(function () {
	$('#id_alumno4').selectize({
		sortField: 'text'
	});
});
$(document).ready(function () {
	$('#id_alumno5').selectize({
		sortField: 'text'
	});
});
</script>