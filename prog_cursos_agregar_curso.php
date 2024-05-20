<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

if (!is_numeric($_REQUEST['id_prog_curso'])) {
	echo(js("window.location='$enlbase=';"));
	exit;
} else {
	$id_prog_curso = $_REQUEST['id_prog_curso'];
}

$seccion      = $_REQUEST['seccion'];
$id_prog_asig = $_REQUEST['id_prog_asig'];

if ($_REQUEST['guardar'] == "Guardar") {
	if ($seccion < 1 || $seccion > 9) {
		echo(msje_js("No ha definido la sección o bien ingreso un valor ilógico.\\n"
		            ."Las secciones entán en un rango de 1 a 9"));		
	} else {
		$SQLinsert_pcd .= "INSERT INTO prog_cursos_detalle (id_prog_curso,id_prog_asig,seccion)
				                  VALUES ($id_prog_curso,$id_prog_asig,$seccion);";
		if (consulta_dml($SQLinsert_pcd) > 0) {
			echo(msje_js("Se ha creado el curso exitosamente.\\n"
			            ."Ahora se procede a la edición del curso para completar los datos restantes"));
			$pc_det = consulta_sql("SELECT currval('prog_cursos_detalle_id_seq') AS id;");
			echo(js("window.location='$enlbase=prog_cursos_editar_curso&id_prog_curso=$id_prog_curso&mod_anterior=$modulo&id_pc_det={$pc_det[0]['id']}';"));
			exit;
		} else {
			echo(msje_js("ATENCIÓN: Ha ocurrido un error..\\n"
			            ."Lo más probable es que esté intentando agregar un curso que ya existe, usando la misma sección."));
		}
	}
}

$prog_curso = consulta_sql("SELECT * FROM vista_prog_cursos WHERE id=$id_prog_curso");
if (count($prog_curso) == 0) {
	echo(js("window.location='$enlbase=';"));
	exit;
}

extract($prog_curso[0]);

$carreras = consulta_sql("SELECT id,nombre FROM carreras WHERE id_escuela=$id_escuela ORDER BY nombre");
if (is_numeric($_REQUEST['id_carrera'])) {
	$id_carrera=$_REQUEST['id_carrera'];
	$mallas = consulta_sql("SELECT id,ano AS nombre FROM mallas WHERE id_carrera=$id_carrera ORDER BY ano");
	
	if (is_numeric($_REQUEST['id_malla'])) {
		$id_malla = $_REQUEST['id_malla'];
		if ($_REQUEST['niveles'] == 1) {
			$condiciones = "AND nivel in (1,3,5,7,9,11)";
		} elseif ($_REQUEST['niveles'] == 2) {
			$condiciones = "AND nivel in (2,4,6,8,10,12)";
		}
		
		$SQL_asignaturas = "SELECT vdm.id_prog_asig AS id,vdm.cod_asignatura||' '||vdm.asignatura AS nombre
		                    FROM vista_detalle_malla AS vdm		                    
		                    WHERE id_malla=$id_malla $condiciones ORDER BY nivel,cod_asignatura";
		$asignaturas = consulta_sql($SQL_asignaturas);
		
	}
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div><br>

<form name="formulario" action="principal_sm.php" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_prog_curso" value="<?php echo($id_prog_curso); ?>">

<?php if (count($asignaturas) > 0) { ?>
<table cellpadding="4" cellspacing="0" border="0" class="tabla">
  <tr>
    <td align="center" class="textoTabla" style="vertical-align: middle;">
      <input type="submit" name="guardar" value="Guardar">
      <input type="button" name="cancelar" value="Cancelar">
    </td>
  </tr>
</table><br>
<?php } ?>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="celdaNombreAttr">Escuela:</td>
    <td class="celdaValorAttr"><?php echo($escuela); ?></td>
    <td class="celdaNombreAttr">Periodo:</td>
    <td class="celdaValorAttr"><?php echo($periodo); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Creador:</td>
    <td class="celdaValorAttr" colspan="3"><?php echo($creador); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Fec. Creación:</td>
    <td class="celdaValorAttr"><?php echo($fecha); ?></td>
    <td class="celdaNombreAttr">Cant. cursos:</td>
    <td class="celdaValorAttr"><?php echo($cant_profes); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Carrera:</td>
    <td class="celdaValorAttr" colspan="3">
      <select name="id_carrera" onChange="submitform();">
        <option value="">-- Seleccione --</option>
        <?php echo(select($carreras,$id_carrera)); ?>
      </select>
    </td>
  </tr>
<?php if (count($mallas) > 0) { ?>
  <tr>
    <td class="celdaNombreAttr">Malla:</td>
    <td class="celdaValorAttr">
      <select name="id_malla" onChange="submitform();">
        <option value="">-- Seleccione --</option>
        <?php echo(select($mallas,$id_malla)); ?>
      </select>
    </td>
    <td class="celdaNombreAttr">Sección:</td>
    <td class="celdaValorAttr"><input type="text" name="seccion" size="1" value="<?php echo($seccion); ?>"></td>
  </tr>
<?php } ?>
<?php if (count($asignaturas) > 0) { ?>
  <tr>
    <td class="celdaNombreAttr">Asignatura:</td>
    <td class="celdaValorAttr" colspan="3">
      <select name="id_prog_asig">
        <option value="">-- Seleccione --</option>
        <?php echo(select($asignaturas,$id_prog_asig)); ?>
      </select>
    </td>
  </tr>
</table><br>

<?php } ?>

</form>

<!-- Fin: <?php echo($modulo); ?> -->
