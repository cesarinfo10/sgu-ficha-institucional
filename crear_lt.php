<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

if ($_REQUEST['crear'] <> "") {
	$aCampos = array("nombre","id_escuela");
	$SQLinsert = "INSERT INTO lineas_tematicas " . arr2sqlinsert($_REQUEST,$aCampos);
	$bdcon = pg_connect("dbname=regacad" . $authbd);
	$resultado = pg_query($bdcon, $SQLinsert);
	if (!$resultado) {
		echo(msje(pg_last_error()));
	} else {
		$filas = pg_affected_rows($resultado);
	};
	if ($filas > 0) {
		$mensaje = "Se ha creado una nueva Línea Temática con los datos ingresados\\n"
		         . "Desea crear otra?";
		$url_si = "$enlbase=$modulo";
		$url_no = "$enlbase=gestion_lt";
		echo(confirma_js($mensaje,$url_si,$url_no));		
		exit;		
	};
};
	
$bdcon = pg_connect("dbname=regacad" . $authbd);

$escuelas = consulta_sql("SELECT * FROM escuelas ORDER BY nombre;");

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="post" onSubmit="return enblanco2('nombre','id_escuela');">
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
    <td class="celdaNombreAttr">Nombre:</td>
    <td class="celdaValorAttr">
      <input type="text" name="nombre" size="40" value="<?php echo($_REQUEST['nombre']); ?>">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Escuela:</td>
    <td class="celdaValorAttr">
      <select name="id_escuela">
        <option value="">-- Seleccione --</option>
        <?php echo(select($escuelas,$_REQUEST['id_escuela'])); ?>
      </select>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->
