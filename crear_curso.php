<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$ids_carreras = $_SESSION['ids_carreras'];


$bdcon = pg_connect("dbname=regacad" . $authbd);

$id_carrera   = $_REQUEST['id_carrera'];
$id_malla     = $_REQUEST['id_malla'];
$id_prog_asig = $_REQUEST['id_prog_asig'];
$id_profesor  = $_REQUEST['id_profesor'];
$ano          = $_REQUEST['ano'];
$semestre     = $_REQUEST['semestre'];
$seccion      = $_REQUEST['seccion'];
$dia1         = $_REQUEST['dia1'];
$horario1     = $_REQUEST['horario1'];
$dia2         = $_REQUEST['dia2'];
$horario2     = $_REQUEST['horario2'];
$dia3         = $_REQUEST['dia3'];
$horario3     = $_REQUEST['horario3'];

if ($_REQUEST['crear'] <> "") {
	if ( !empty($dia1) || !empty($horario1) ) { $sesion1 = $dia1 . $horario1; } else { $sesion1 = NULL; }
	if ( !empty($dia2) || !empty($horario2) ) { $sesion2 = $dia2 . $horario2; } else { $sesion2 = NULL; }
	if ( !empty($dia3) || !empty($horario3) ) { $sesion3 = $dia3 . $horario3; } else { $sesion3 = NULL; }
	if ( ($sesion1 == $sesion2 && (!is_null($sesion1) && !is_null($sesion2))) ||	
	     ($sesion1 == $sesion3 && (!is_null($sesion1) && !is_null($sesion3))) ||
	     ($sesion2 == $sesion3 && (!is_null($sesion2) && !is_null($sesion3)))
	   ) {
		$mensaje_error = "Se han definido 2 o 3 horarios en el mismo día o módulo.\\n"
		               . "Por favor corrija el(los) horarios repetidos.";
		echo(msje_js($mensaje_error));		
	} else {   
		$filas = 0;
		$aCampos = array("id_prog_asig","seccion","semestre","ano","id_profesor","dia1","horario1","dia2","horario2",
		                 "dia3","horario3");
		$SQLinsert = "INSERT INTO cursos " . arr2sqlinsert($_REQUEST,$aCampos);
		$resultado = pg_query($bdcon, $SQLinsert);
		if (!$resultado) {
			echo(msje(pg_last_error()));
		} else {
			$filas = pg_affected_rows($resultado);
		};
		if ($filas > 0) {
			$id_curso_nuevo = consulta_sql("SELECT currval('cursos_id_seq') AS id_curso_nuevo;");
			$id_curso_nuevo = $id_curso_nuevo[0]['id_curso_nuevo'];
			$mensaje = "Se ha creado el nuevo Curso con los datos ingresados.\\n"
			         . "Desea crear otro?";
			$url_si = "$enlbase=$modulo";
			$url_no = "$enlbase=ver_curso&id_curso=$id_curso_nuevo";
			echo(confirma_js($mensaje,$url_si,$url_no));
		}
	}
}

if ($ids_carreras <> "") {
  $ids_carreras .= ",12";
	$condicion_carreras = "WHERE id IN ($ids_carreras)";
}

$carreras = consulta_sql("SELECT id,nombre FROM carreras $condicion_carreras ORDER BY nombre;");

if ($id_carrera <> "") {
	$mallas = consulta_sql("SELECT id,ano AS nombre FROM mallas WHERE id_carrera='$id_carrera';");	
	if ($id_malla <> "") {
		$SQL_prog_asigs = "SELECT id_prog_asig AS id,cod_asignatura||' '||asignatura AS nombre
		                   FROM vista_detalle_malla
		                   WHERE id_malla='$id_malla'
		                   ORDER BY cod_asignatura;";
		$prog_asigs = consulta_sql($SQL_prog_asigs);
		if ($id_prog_asig <> "") {
			$id_profesor = consulta_sql("SELECT id_profesor FROM vista_detalle_malla WHERE id_prog_asig='$id_prog_asig';");
			$id_profesor = $id_profesor[0]['id_profesor'];
			$profesores = consulta_sql("SELECT id,nombre FROM profesores ORDER BY nombre;");
			$horarios = consulta_sql("SELECT id,id||'=> '||intervalo AS nombre FROM vista_horarios ORDER BY id;");
		};
	};
};

if ($semestre == "") { $semestre = $SEMESTRE; }
if ($ano == "") { $ano = $ANO; }	
if ($seccion == "") { $seccion = 1; }	


?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type="submit" name="crear" value="Crear"></td>
    <td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar" onClick="history.back();"></td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="celdaNombreAttr">Carrera:</td>
    <td class="celdaValorAttr">
      <select name="id_carrera" onChange="submitform();">
        <option value="">-- Seleccione --</option>
        <?php echo(select($carreras,$id_carrera)); ?>
      </select>
    </td>
  </tr>
<?php if ($id_carrera <> "") { ?>
  <tr>
    <td class="celdaNombreAttr">Malla:</td>
    <td class="celdaValorAttr">
      <select name="id_malla" onChange="submitform();">
        <option value="">-- Seleccione --</option>
        <?php echo(select($mallas,$id_malla)); ?>
      </select>
    </td>
  </tr>
	<?php if ($id_malla <> "") { ?>
  <tr>
    <td class="celdaNombreAttr">Asignatura:</td>
    <td class="celdaValorAttr">
      <select name="id_prog_asig" onChange="submitform();">
        <option value="">-- Seleccione --</option>
        <?php echo(select($prog_asigs,$id_prog_asig)); ?>
      </select>
    </td>
  </tr>
		<?php if ($id_prog_asig <> "") { ?>
  <tr>
    <td class="celdaNombreAttr">Secci&oacute;n:</td>
    <td class="celdaValorAttr">
      <input type="text" size="1" maxlength="4" name="seccion" value="<?php echo($seccion); ?>">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Semestre:</td>
    <td class="celdaValorAttr">
      <select name="semestre">
        <?php echo(select($semestres,$semestre)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">A&ntilde;o:</td>
    <td class="celdaValorAttr">
      <input type="text" size="4" maxlength="4" name="ano" value="<?php echo($ano); ?>">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Profesor:</td>
    <td class="celdaValorAttr">
      <select name="id_profesor">
        <option value="">-- Seleccione --</option>
        <?php echo(select($profesores,$id_profesor)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr" colspan="2" style="text-align: center;">
      Programación Horaria Semanal del curso
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">1ª Sesión:</td>
    <td class="celdaValorAttr">
      <select name="dia1">
        <option value="">-- Día --</option>
        <?php echo(select($dias_palabra,$dia1)); ?>
      </select>
      <select name="horario1">
        <option value="">-- Módulo (horario) --</option>
        <?php echo(select($horarios,$horario1)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">2ª Sesión:</td>
    <td class="celdaValorAttr">
      <select name="dia2">
        <option value="">-- Día --</option>
        <?php echo(select($dias_palabra,$dia2)); ?>
      </select>
      <select name="horario2">
        <option value="">-- Módulo (horario) --</option>
        <?php echo(select($horarios,$horario2)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">3ª Sesión:</td>
    <td class="celdaValorAttr">
      <select name="dia3">
        <option value="">-- Día --</option>
        <?php echo(select($dias_palabra,$dia3)); ?>
      </select>
      <select name="horario3">
        <option value="">-- Módulo (horario) --</option>
        <?php echo(select($horarios,$horario3)); ?>
      </select>
    </td>
  </tr>
		<?php };?>
	<?php };?>
<?php };?>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

