<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

if ($_REQUEST['crear'] == "") {
	$_REQUEST['nro_sesiones_se'] = 2;
	$_REQUEST['horas_semanal'] = 4;
	$_REQUEST['carga_acad_sem'] = 2;
};

include("validar_modulo.php");

$cod_asignatura = $_REQUEST['cod_asignatura'];
if ($cod_asignatura == "") {
	echo("<script language='JavaScript1.2'>location.href='principal.php?modulo=gestion_asignaturas';</script>");
	exit;
};

$bdcon = pg_connect("dbname=regacad" . $authbd);

$SQLtxt = "SELECT codigo AS \"código\",nombre FROM vista_asignaturas WHERE codigo='$cod_asignatura';";
$resultado = pg_query($bdcon, $SQLtxt);
$filas = pg_numrows($resultado);
if ($filas > 0) {
	$asignatura = utf2html(pg_fetch_all($resultado));
};

if ($_REQUEST['crear'] <> "") {
	$ano = $_REQUEST['ano'];
	$SQLtxt = "SELECT id FROM prog_asig WHERE cod_asignatura='$cod_asignatura' AND ano=$ano;";
	$resultado = pg_query($bdcon, $SQLtxt);
	$filas = pg_numrows($resultado);
	if ($filas > 0) {
		$asig = pg_fetch_all($resultado);
		$nombre_asig = $asig[0]['nombre'];
		echo(msje("Intenta crear un Programa de estudio de una asignatura, ya existente en la
		           base de datos.<br><br>A continuaci&oacute;n puede
		           editar nuevamente los datos del ingreso (Preocupese del a&ntilde;o del Programa de estudios)."));
	} else {
		$aCampos = array("cod_asignatura","ano","nro_sesiones_se","horas_semanal","carga_acad_sem","obj_generales","obj_especificos","contenidos","met_instruccion","evaluacion","bib_obligatoria","bib_complement");
		$SQLinsert = "INSERT INTO prog_asig " . arr2sqlinsert($_REQUEST,$aCampos);
		$resultado = pg_query($bdcon, $SQLinsert);
		if (!$resultado) {
			echo(msje(pg_last_error()));
		} else {
			$filas = pg_affected_rows($resultado);
		};
		if ($filas > 0) {
			echo(msje("Se ha creado un nuevo Programa de estudios para la asignatura "
			          . $asignatura[0]['nombre'] . "con los datos ingresados<br>
			           Pinche <a class='enlaces' href='principal.php?modulo=ver_asignatura&cod_asignatura=$cod_asignatura'>aqu&iacute;</a>
			           para voler a ver la Asignatura"));
			exit;
		};
	};
};
	
$validar_js = "'ano','nro_sesiones_se','horas_semanal','carga_acad_sem','obj_generales','obj_especificos','contenidos','met_instruccion','evaluacion','bib_obligatoria','bib_complement'";
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="post" onSubmit="return enblanco2(<?php echo($validar_js); ?>);">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="cod_asignatura" value="<?php echo($cod_asignatura); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type="submit" name="crear" value="Crear"></td>
    <td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar"  onClick="cancelar_guardar();"></td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
<?php
	for($x=0;$x<pg_num_fields($resultado);$x++) {
		$nombre_campo = ucfirst(pg_field_name($resultado,$x));
		$valor_campo = $asignatura[0][pg_field_name($resultado,$x)];
		echo("  <tr>\n");
		echo("    <td class='celdaNombreAttr'>$nombre_campo:</td>\n");
		echo("    <td class='celdaValorAttr'>&nbsp;$valor_campo</td>\n");
		echo("  </tr>\n");
	};
?>
  <tr>
    <td class='celdaNombreAttr'>A&ntilde;o:</td>
    <td class='celdaValorAttr'>
      <input type='text' name='ano' value="<?php echo($_REQUEST['ano']); ?>" maxlenght="4" size="4">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>N&ordm; de sesiones semanales:</td>
    <td class='celdaValorAttr'>
      <input type='text' name='nro_sesiones_se' value="<?php echo($_REQUEST['nro_sesiones_se']); ?>" maxlenght="2" size="2">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Horas por semana:</td>
    <td class='celdaValorAttr'>
      <input type='text' name='horas_semanal' value="<?php echo($_REQUEST['horas_semanal']); ?>" maxlenght="2" size="2">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carga acad&eacute;mica semanal:</td>
    <td class='celdaValorAttr'>
      <input type='text' name='carga_acad_sem' value="<?php echo($_REQUEST['carga_acad_sem']); ?>" maxlenght="2" size="2">
    </td>
  </tr>
  
  <tr>
    <td class='celdaNombreAttr'>Objetivos generales:</td>
    <td class='celdaValorAttr'>
      <textarea name='obj_generales'><?php echo($_REQUEST['obj_generales']); ?></textarea>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Objetivos especificos:</td>
    <td class='celdaValorAttr'>
      <textarea name='obj_especificos'><?php echo($_REQUEST['obj_especificos']); ?></textarea>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Contenidos:</td>
    <td class='celdaValorAttr'>
      <textarea name='contenidos' class="grande"><?php echo($_REQUEST['contenidos']); ?></textarea>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>M&eacute;todo de instrucci&oacute;n:</td>
    <td class='celdaValorAttr'>
      <textarea name='met_instruccion'><?php echo($_REQUEST['met_instruccion']); ?></textarea>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>M&eacute;todo de evaluaci&oacute;n:</td>
    <td class='celdaValorAttr'>
      <textarea name='evaluacion'><?php echo($_REQUEST['evaluacion']); ?></textarea>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Bibliograf&iacute;a obligatoria:</td>
    <td class='celdaValorAttr'>
      <textarea name='bib_obligatoria'><?php echo($_REQUEST['bib_obligatoria']); ?></textarea>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Bibliograf&iacute;a complementaria:</td>
    <td class='celdaValorAttr'>
      <textarea name='bib_complement'><?php echo($_REQUEST['bib_complement']); ?></textarea>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

