<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$bdcon = pg_connect("dbname=regacad" . $authbd);

$id_escuela = $_REQUEST['id_escuela'];
if (!is_numeric($id_escuela)) {
	echo("<script language='JavaScript1.2'>location.href='principal.php?modulo=gestion_escuelas';</script>");
	exit;
};

if ($_REQUEST['guardar'] <> "") {
	$aCampos = array("nombre","id_director");
	$SQLupdate = "UPDATE escuelas SET " . arr2sqlupdate($_REQUEST,$aCampos) . " WHERE id=$id_escuela;";
	$resultado = pg_query($bdcon, $SQLupdate);
	if (!$resultado) {
		echo(msje(pg_last_error()));
	} else {
		$filas = pg_affected_rows($resultado);
	};
	if ($filas > 0) {
		echo(msje("Se han guardado los cambios<br>Pinche <a class='enlaces' href='principal.php?modulo=gestion_escuelas'>aqu&iacute;</a>
		           para voler al Gestor de Escuelas"));
		exit;
	};
};
	
$SQLtxt2 = "SELECT id,nombre||' '||apellido as nombre FROM usuarios where tipo=1 and activo ORDER BY nombre;";
$resultado2 = pg_query($bdcon, $SQLtxt2);
$filas2 = pg_numrows($resultado2);
$directores = pg_fetch_all($resultado2);
            
$SQLtxt = "SELECT id,nombre,id_director FROM escuelas WHERE id=$id_escuela;";
$resultado = pg_query($bdcon, $SQLtxt);
$filas = pg_numrows($resultado);
$escuela = pg_fetch_all($resultado);
?>

<!-- Inicio: editar escuela -->
<form name="formulario" action="principal.php" method="post" onSubmit="return enblanco2('nombre');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_escuela" value="<?php echo($id_escuela); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>: <?php echo($escuela[0]['nombre']); ?>  
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
    <td class="celdaNombreAttr">Nombre:</td>
    <td class="celdaValorAttr">
      <input type="text" name="nombre" value="<?php echo($escuela[0]['nombre']); ?>" size="40" onChange="cambiado();">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Director:</td>
    <td class="celdaValorAttr">
      <select name="id_director" onChange="cambiado();">
        <?php echo(select($directores,$escuela[0]['id_director'])); ?>
      </select>
    </td>
  </tr>
</table>
</form>
<!-- Fin: editar escuela -->

