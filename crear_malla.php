<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");
$bdcon = pg_connect("dbname=regacad" . $authbd);

if ($_REQUEST['ano'] == "") {
	$_REQUEST['ano'] = strftime("%Y") + 1;
};	

if ($_REQUEST['crear'] <> "") {
	$ano = $_REQUEST['ano'];
	$id_carrera = $_REQUEST['id_carrera'];	
	$SQLtxt = "SELECT ano FROM mallas WHERE ano=$ano AND id_carrera=$id_carrera;";
	$resultado = pg_query($bdcon, $SQLtxt);
	$filas = pg_numrows($resultado);
	if ($filas > 0) {
		$mensaje  = "Intenta crear una Malla para un año y carrera ya existente en la base de datos.\\n";
		$mensaje .= "A continuación puede editar nuevamente los datos del ingreso.";
		echo(msje_js($mensaje));
	} else {
		$aCampos = array("ano","id_carrera","niveles","cant_asig_oblig","cant_asig_elect","cant_asig_efp","comentarios");
		$SQLinsert = "INSERT INTO mallas " . arr2sqlinsert($_REQUEST,$aCampos);
		$resultado = pg_query($bdcon, $SQLinsert);
		if (!$resultado) {
			echo(msje(pg_last_error()));
		} else {
			$filas = pg_affected_rows($resultado);
		};
		if ($filas > 0) {
			$mensaje  = "Se ha creado una nueva Malla con los datos ingresados\\n";
			$mensaje .= "Desea crear otra?";
			$url_si = "$enlbase=$modulo";
			$url_no = "$enlbase=gestion_mallas";
			echo(confirma_js($mensaje,$url_si,$url_no));			
			exit;
		};
	};
};
	
$asignatura = pg_fetch_all($resultado);
$SQLtxt2 = "SELECT id,nombre FROM carreras ORDER BY nombre;";
$resultado2 = pg_query($bdcon, $SQLtxt2);
$filas2 = pg_numrows($resultado2);
if ($filas2 > 0) {
	$carreras = pg_fetch_all($resultado2);
	$SQLtxt3 = "SELECT * FROM profesores ORDER BY nombre;";
	$resultado3 = pg_query($bdcon, $SQLtxt3);
	$filas3 = pg_numrows($resultado3);
	if ($filas3 > 0) {
		$profesores = pg_fetch_all($resultado3);
	};
};

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="post" onSubmit="return enblanco2('ano','id_carrera','niveles','cant_asig_oblig','cant_asig_elect','cant_asig_efp');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
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
  <tr>
    <td class="celdaNombreAttr">A&ntilde;o:</td>
    <td class="celdaValorAttr">
      <input type="text" name="ano" value="<?php echo($_REQUEST['ano']); ?>" maxlength="4" size="4" onChange="cambiado();">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Carrera:</td>
    <td class="celdaValorAttr">
      <select name="id_carrera" onChange="cambiado();">
        <option value="">-- Seleccione --</option>
        <?php echo(select($carreras,$_REQUEST['id_carrera'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Niveles (semestres):</td>
    <td class="celdaValorAttr">
      <input type="text" name="niveles" value="<?php echo($_REQUEST['niveles']); ?>" size="2" maxlength="2" onChange="cambiado();">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Cantidad Asignaturas Obligatorias:</td>
    <td class="celdaValorAttr">
      <input type="text" name="cant_asig_oblig" value="<?php echo($_REQUEST['cant_asig_oblig']); ?>" size="2" maxlength="2" onChange="cambiado();">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Cantidad Asignaturas Electivas:</td>
    <td class="celdaValorAttr">
      <input type="text" name="cant_asig_elect" value="<?php echo($_REQUEST['cant_asig_elect']); ?>" size="2" maxlength="2" onChange="cambiado();">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Cantidad Asignaturas EFP:</td>
    <td class="celdaValorAttr">
      <input type="text" name="cant_asig_efp" value="<?php echo($_REQUEST['cant_asig_efp']); ?>" size="2" maxlength="2" onChange="cambiado();">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Comentarios:</td>
    <td class="celdaValorAttr">
      <textarea name='comentarios'><?php echo($_REQUEST['comentarios']); ?></textarea>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

