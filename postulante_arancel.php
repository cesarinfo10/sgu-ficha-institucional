<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_pap = $_REQUEST['id_pap'];
if (!is_numeric($id_pap)) {
	echo(js("location.href='principal.php?modulo=gestion_postulantes';"));
	exit;
}

$SQL_postulante = "SELECT vp.id,vp.nombre,vp.rut,vp.admision,pap.arancel_promo FROM vista_pap AS vp LEFT JOIN pap USING (id) WHERE id=$id_pap;";
$postulante     = consulta_sql($SQL_postulante);

if ($_REQUEST['guardar'] == "Guardar") {
	$arancel_promo = $_REQUEST['arancel_promo'];
	$SQL_update_pap = "UPDATE pap SET arancel_promo='$arancel_promo' WHERE id='$id_pap'";
	if (consulta_dml($SQL_update_pap) == 1) {
		echo(msje_js("Se han guardado los cambios exitosamente"));
	} else {
		echo(msje_js("ERROR: Ha ocurrido un inconveniente y no se han guardado los datos"));
	}
	echo(js("location.href='$enlbase=ver_postulante&id_pap=$id_pap';"));
	exit;
}
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<form name="formulario" action="principal.php" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_pap" value="<?php echo($id_pap); ?>">

<div class="texto">
  <input type="submit" name="guardar" value="Guardar" tabindex="99">
  <input type='button' name='volver' onClick='javascript:history.back();' value="Volver">
</div>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Postulante</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['rut']); ?></td>
    <td class='celdaNombreAttr'>ID Postulante:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['id']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($postulante[0]['nombre']); ?></td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'>Arancel Promocional:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name="arancel_promo">
        <?php echo(select($sino,$postulante[0]['arancel_promo'])); ?>
      </select>
  </tr>  
</table>
<br>
</form>
<!-- Fin: <?php echo($modulo); ?> -->
