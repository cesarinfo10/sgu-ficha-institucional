<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$bdcon = pg_connect("dbname=regacad" . $authbd);

$id_lt = $_REQUEST['id_lt'];

if (!is_numeric($id_lt)) {
	echo(js("location.href='principal.php?modulo=gestion_lt';"));
	exit;
};

if ($_REQUEST['guardar'] <> "") {
	$aCampos = array("nombre","id_escuela");
	$SQLupdate = "UPDATE lineas_tematicas SET " . arr2sqlupdate($_REQUEST,$aCampos) . " WHERE id=$id_lt;";
	$resultado = pg_query($bdcon, $SQLupdate);
	if (!$resultado) {
		echo(msje(pg_last_error()));
	} else {
		$filas = pg_affected_rows($resultado);
	};
	if ($filas > 0) {
		echo(msje_js("Se han guardado los cambios"));
		echo(js("location.href='principal.php?modulo=ver_lt&id_lt=$id_lt';"));
		exit;
	};
};

$SQL_lt = "SELECT * FROM lineas_tematicas WHERE id=$id_lt;";
$lt     = consulta_sql($SQL_lt);

$SQL_escuelas = "SELECT * FROM escuelas ORDER BY nombre;";
$escuelas = consulta_sql($SQL_escuelas);

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="post" onSubmit="return enblanco2('nombre','id_escuela');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_lt" value="<?php echo($id_lt); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>: <?php echo($lt[0]['nombre']); ?>  
</div><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type="submit" name="guardar" value="Guardar" onClick="return confirmar_guardar();"></td>
    <td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar"  onClick="cancelar_guardar();"></td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="celdaNombreAttr">Id:</td>
    <td class="celdaValorAttr"><?php echo($lt[0]['id']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nombre:</td>
    <td class="celdaValorAttr">
      <input type="text" name="nombre" value="<?php echo($lt[0]['nombre']); ?>" size="40" onChange="cambiado();">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Escuela:</td>
    <td class="celdaValorAttr">
      <select name="id_escuela" onChange="cambiado();">
        <?php echo(select($escuelas,$lt[0]['id_escuela'])); ?>
      </select>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

