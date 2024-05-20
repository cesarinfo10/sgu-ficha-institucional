<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$cod_sala = $_REQUEST['cod_sala'];

if (empty($cod_sala)) {
	echo(js("location.href='principal.php?modulo=gestion_salas';"));
	exit;
}

$SQL_sala = "SELECT *,CASE WHEN activa THEN 'Si' ELSE 'No' END AS activa FROM salas WHERE codigo='$cod_sala'";
$sala = consulta_sql($SQL_sala);
           
if (count($sala) > 0) {

}

extract($sala[0]);
	
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<input type="button" value="Editar" onClick="window.location='<?php echo("$enlbase=sala_editar&cod_sala=$codigo"); ?>';">
<input type="button" value="Volver" onClick="history.back();"><br>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class='celdaNombreAttr'>Código:</td>
    <td class='celdaValorAttr'><?php echo($codigo); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr'><?php echo($nombre); ?> <?php echo($prog_asig); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Capacidad:</td>
    <td class='celdaValorAttr'><?php echo($capacidad); ?> silla(s)</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Piso:</td>
    <td class='celdaValorAttr'><?php echo($piso); ?>º</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Comentarios:</td>
    <td class='celdaValorAttr'><?php echo(nl2br($comentarios)); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Activa</td>
    <td class='celdaValorAttr'><?php echo($activa); ?></td>
  </tr>
</table>

<!-- Fin: <?php echo($modulo); ?> -->
