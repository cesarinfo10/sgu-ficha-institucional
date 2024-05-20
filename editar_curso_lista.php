<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$id_carrera = $_REQUEST['id_carrera'];
$regimen    = $_REQUEST['regimen'];
$id_curso   = $_REQUEST['id_curso'];
$accion     = $_REQUEST['accion'];
$id_alumno  = $_REQUEST['id_alumno'];
$semestre   = $_REQUEST['semestre'];
$ano        = $_REQUEST['ano'];

$ids_carreras = $_SESSION['ids_carreras'];

if ($_REQUEST['guardar'] == 'Guardar' && ($accion == "a" || $accion == "q") && $id_alumno <> "" && $id_curso <> "" ) {
	switch ($accion) {
		case "a":
			$SQL_carga_academica = "INSERT INTO cargas_academicas (id_curso,id_alumno) VALUES ($id_curso,$id_alumno);";
			break;
		case "q":
			$SQL_carga_academica = "DELETE FROM cargas_academicas WHERE id_curso='$id_curso' AND id_alumno='$id_alumno';";
			break;
	}
	$carga_academica = consulta_dml($SQL_carga_academica);
	if (count($carga_academica) > 0) {
		echo(msje_js("Se ha actualizado correctamente la lista de este curso"));
		echo(js("window.location='$enlbase=ver_curso&id_curso=$id_curso';"));
		exit;
	}
}

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

if ($regimen <> "") {
	$SQL_carreras = "SELECT id,nombre,id_escuela FROM carreras WHERE true $SQL_carreras_cond AND regimen='$regimen' ORDER BY nombre";
	$carreras = consulta_sql($SQL_carreras);

	if ($id_carrera <> "" && $ano > 0 && $semestre >= 0) {

		$SQL_cursos = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura||' ('||coalesce(vc.profesor,'[Sin profesor definido]')||')' AS nombre
		               FROM vista_cursos AS vc
                   LEFT JOIN detalle_mallas AS dm ON dm.id_prog_asig=vc.id_prog_asig
                   LEFT JOIN mallas AS m ON m.id=dm.id_malla
		               LEFT JOIN carreras AS c ON c.id=vc.id_carrera
		               WHERE (vc.ano=$ano AND vc.semestre=$semestre)
		                 AND '$id_carrera' IN (vc.id_carrera,m.id_carrera)
		               ORDER BY nombre";
		$cursos = consulta_sql($SQL_cursos);

		if ($id_curso <> "") {
			$acciones = array(array('id' => "a", 'nombre' => "Agregar"),
			                  array('id' => "q", 'nombre' => "Quitar"));

			if ($accion == "a") {
				$SQL_malla = "SELECT id_malla FROM detalle_mallas WHERE id_prog_asig=(SELECT id_prog_asig FROM cursos WHERE id=$id_curso)";
				$SQL_alumnos = "SELECT va.id,va.nombre||' ('||va.rut||') ['||va.id||']' AS nombre
				                FROM vista_alumnos AS va
				                LEFT JOIN carreras AS c ON c.id=va.id_carrera
				                WHERE c.id_escuela=$id_escuela AND va.estado='Vigente' AND va.malla_actual IN ($SQL_malla)
				                  AND va.id NOT IN (SELECT id_alumno FROM cargas_academicas WHERE id_curso='$id_curso');";
			}
			
			if ($accion == "q") {
				$SQL_alumnos = "SELECT id_alumno AS id,nombre_alumno||' ('||rut||') ['||id_alumno||']' AS nombre
				                FROM vista_cursos_alumnos
				                WHERE id_curso=$id_curso;";
			}
			$alumnos = consulta_sql($SQL_alumnos);

			if (count($alumnos) == 0 && $accion == "a") {
				$SQL_alumnos = "SELECT id,nombre||' ('||rut||') ['||id||']' AS nombre
				                FROM vista_alumnos WHERE estado='Vigente'";
				$alumnos = consulta_sql($SQL_alumnos);
			} elseif (count($alumnos) == 0 && $accion == "q") {
				echo(msje_js("Este curso no tiene alumnos para eliminar (actualmente está vacío)"));
			}
				
		}
	}
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>: <?php echo($curso[0]['asignatura']); ?>  
</div><br>
<form name="formulario" action="principal.php" method="get" onSubmit="return enblanco2('id_carrera','semestre','ano');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type="submit" name="guardar" value="Guardar"></td>
    <td class="tituloTabla">
      <input type="button" name="cancelar" value="Cancelar" onClick="window.location='<?php echo("$enlbase=ver_curso&id_curso=$id_curso"); ?>';">
    </td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="celdaNombreAttr">Regimen:</td>
    <td class="celdaValorAttr">
      <select name="regimen" onChange="submitform();">
        <option value="">-- Seleccione --</option>
        <?php echo(select($REGIMENES,$regimen)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Carrera:</td>
    <td class="celdaValorAttr">
      <select name="id_carrera" onChange="submitform();">
        <option value="">-- Seleccione --</option>
        <?php echo(select($carreras,$id_carrera)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Periodo:</td>
    <td class="celdaValorAttr">
      <select name="semestre" onChange="submitform();">
        <option value="">-- Semestre --</option>
        <?php echo(select($semestres,$semestre)); ?>
      </select>
      <select name="ano" onChange="submitform();">
        <option value="">-- Año --</option>
        <?php echo(select($anos,$ano)); ?>
      </select>
    </td>
  </tr>
<?php if ($id_carrera <> "") { ?>
  <tr>
    <td class="celdaNombreAttr">Curso:</td>
    <td class="celdaValorAttr">
<?php if ($id_curso == "") { ?>
<!--	  <input name="id_curso" list="id_curso" class="filtro">
	  <datalist name="id_curso" id="id_curso" class="filtro">
        <option value="">-- Seleccione --</option>
        <?php //echo(select($cursos,$id_curso)); ?>
	  </datalist> -->
    <select name="id_curso"  id="asignaturas">
        <option value="">-- Seleccione --</option>
        <?php echo(select($cursos,$id_curso)); ?>
      </select>
	  <input type="submit" name="Siguiente" value="Siguiente">
<?php } else { ?>	  
      <select name="id_curso" onChange="submitform();">
        <option value="">-- Seleccione --</option>
        <?php echo(select($cursos,$id_curso)); ?>
      </select>
<?php } ?>
    </td>
  </tr>
	<?php if ($id_curso <> "") { ?>
  <tr>
    <td class="celdaNombreAttr">Acción:</td>
    <td class="celdaValorAttr">
      <select name="accion" onChange="submitform();">
        <option value="">-- Seleccione --</option>
        <?php echo(select($acciones,$accion)); ?>
      </select>
    </td>
  </tr>
		<?php if ($accion == "a" || $accion == "q") { ?>  
  <tr>
    <td class="celdaNombreAttr">Alumno(a):</td>
    <td class="celdaValorAttr">
		<?php if ($id_alumno == "") { ?>
          <!-- <input name="id_alumno" list="id_alumno" class="filtro">
	      <datalist name="id_alumno" id="id_alumno" class="filtro">
            <option value="">-- Seleccione --</option>
            <?php //echo(select($alumnos,$id_alumno)); ?>
	      </datalist> -->
		  <select name="id_alumno" id="alumnos">
            <option value="">-- Seleccione --</option>
            <?php echo(select($alumnos,$id_alumno)); ?>
          </select>
		<?php } else { ?>
          <select name="id_alumno" id="alumnos">
            <option value="">-- Seleccione --</option>
            <?php echo(select($alumnos,$id_alumno)); ?>
          </select>
		<?php } ?>
      <input type="submit" name="guardar" value="Guardar">
    </td>
  </tr>
		<?php } ?>
	<?php } ?>
<?php } ?>
</table>
</form>

<script>
  $(document).ready(function () {
      $('#asignaturas').selectize({
          sortField: 'text'
      });
  });
  
  $(document).ready(function () {
      $('#alumnos').selectize({
          sortField: 'text'
      });
  });
</script>
<!-- Fin: <?php echo($modulo); ?> -->
