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

if ($_REQUEST["guardar"] == "Guardar") {
	$aCampos = array("codigo","nombre","nombre_largo","capacidad","piso","tipo","activa","lux","tipo_luminaria","tipo_piso",
                   "computador","proyector","parlantes","pizarra_interactiva","webcam",
                   "aire_acond","cortinas","doble_vidrio","tipo_silla","largo","ancho",
                   "tamano","orientacion","comentarios");
	$SQLupdate = "UPDATE salas SET " . arr2sqlupdate($_REQUEST,$aCampos) . " WHERE codigo='$cod_sala'";
	if (consulta_dml($SQLupdate) > 0) {
		echo(msje_js("Se han guardado los datos"));
    echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}
}

$SQL_sala = "SELECT * FROM salas WHERE codigo='$cod_sala'";
$sala = consulta_sql($SQL_sala);
           
if (count($sala) > 0) {

}

extract($sala[0]);

$TIPOS_SALAS      = consulta_sql("SELECT * FROM vista_salas_tipo");
$TIPOS_LUMINARIAS = consulta_sql("SELECT * FROM vista_salas_tipos_luminarias");
$TIPOS_SILLAS     = consulta_sql("SELECT * FROM vista_salas_tipos_sillas");
$TIPOS_PISO       = consulta_sql("SELECT * FROM vista_salas_tipos_piso");
$ORIENTACIONES    = consulta_sql("SELECT * FROM vista_salas_orientacion");
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal.php" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="cod_sala" value="<?php echo($cod_sala); ?>">
<div style="margin-top: 5px">
  <input type="submit" value="Guardar" name="guardar">
  <input type="button" value="Cancelar" onClick="history.back();"><br>
</div>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr><td class='celdaNombreAttr' colspan='4' style="text-align: center">Ficha de la Sala</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Código:</td>
    <td class='celdaValorAttr'><input type="text" name="codigo" class="boton" size="5" value="<?php echo($codigo); ?>" required></td>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr'><input type="text" name="nombre"  class="boton" size="10" value="<?php echo($nombre); ?>" required></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre largo:</td>
    <td class='celdaValorAttr' colspan="3"><input type="text" name="nombre_largo" class="boton" size="30" value="<?php echo($nombre_largo); ?>"></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Capacidad:</td>
    <td class='celdaValorAttr'><input type="text" name="capacidad" class="boton" size="2"  value="<?php echo($capacidad); ?>"> sillas</td>
    <td class='celdaNombreAttr'>Piso:</td>
    <td class='celdaValorAttr'><input type="text" name="piso"  class="boton" size="2" value="<?php echo($piso); ?>"></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tipo:</td>
    <td class='celdaValorAttr'><select name="tipo"><option value="">-- Seleccione --</option><?php echo(select($TIPOS_SALAS,$tipo)); ?></select></td>
    <td class='celdaNombreAttr'>Activa:</td>
    <td class='celdaValorAttr'><select name="activa"><?php echo(select($sino,$activa)); ?></select></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Lux:</td>
    <td class='celdaValorAttr'><input type="text" name="lux"  class="boton" size="2" value="<?php echo($lux); ?>"> lúmenes</td>
    <td class='celdaNombreAttr'>Luminaria:</td>
    <td class='celdaValorAttr'><select name="tipo_luminaria"><option value="">-- Seleccione --</option><?php echo(select($TIPOS_LUMINARIAS,$tipo_luminaria)); ?></select></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' rowspan="7">Implementación:</td>
    <td class='celdaValorAttr' rowspan="7">
      <input type="checkbox" name="computador"   id="computador"   value="t" <?php echo(($computador=="t")?"checked":""); ?>><label for="computador"> Computador</label><br>
      <input type="checkbox" name="proyector"    id="proyector"    value="t" <?php echo(($proyector=="t")?"checked":""); ?>><label for="proyector"> Proyector</label><br>
      <input type="checkbox" name="parlantes"    id="parlantes"    value="t" <?php echo(($parlantes=="t")?"checked":""); ?>><label for="parlantes"> Parlantes</label><br>
      <input type="checkbox" name="pizarra_interactiva" id="pizarra_interactiva" value="t" <?php echo(($pizarra_interactiva=="t")?"checked":""); ?>><label for="pizarra_interactiva"> Pizarra Interactiva</label><br>
      <input type="checkbox" name="webcam"       id="webcam"       value="t" <?php echo(($webcam=="t")?"checked":""); ?>><label for="webcam"> Cámara (clase híbrida)</label><br>
      <input type="checkbox" name="aire_acond"   id="aire_acond"   value="t" <?php echo(($aire_acond=="t")?"checked":""); ?>><label for="aire_acond"> Aire Acondicionado</label><br>
      <input type="checkbox" name="cortinas"     id="cortinas"     value="t" <?php echo(($cortinas=="t")?"checked":""); ?>><label for="cortinas"> Cortinas</label><br>
      <input type="checkbox" name="doble_vidrio" id="doble_vidrio" value="t" <?php echo(($doble_vidrio=="t")?"checked":""); ?>><label for="doble_vidrio"> Doble vidrio</label>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Sillas:</td>
    <td class='celdaValorAttr'><select name="tipo_silla"><option value="">-- Seleccione --</option><?php echo(select($TIPOS_SILLAS,$tipo_silla)); ?></select></td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'>Tipo piso:</td>
    <td class='celdaValorAttr'><select name="tipo_piso"><option value="">-- Seleccione --</option><?php echo(select($TIPOS_PISO,$tipo_piso)); ?></select></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Largo:</td>
    <td class='celdaValorAttr'><input type="text" name="ancho"  class="boton" size="2" value="<?php echo($ancho); ?>" style='text-align: right' onBlur="calc_tamano();"> metros</td>
    </tr>
  <tr>
    <td class='celdaNombreAttr'>Ancho:</td>
    <td class='celdaValorAttr'><input type="text" name="largo"  class="boton" size="2" value="<?php echo($largo); ?>" style='text-align: right' onBlur="calc_tamano();"> metros</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tamaño:</td>
    <td class='celdaValorAttr'><input type="text" name="tamano" class="boton" size="2" value="<?php echo($tamano); ?>" style='text-align: right' onBlur="calc_tamano();" readonly> mt<sup>2</sup></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Orientación:</td>
    <td class='celdaValorAttr'><select name="orientacion"><option value="">-- Seleccione --</option><?php echo(select($ORIENTACIONES,$orientacion)); ?></select></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Comentarios:</td>
    <td class='celdaValorAttr' colspan="3"><textarea name="comentarios" class="grande"><?php echo($comentarios); ?></textarea></td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->
<script>
  function calc_tamano() {
    var largo  = parseFloat(formulario.largo.value),
        ancho  = parseFloat(formulario.ancho.value),
        tamano = parseFloat(formulario.tamano.value);
    
    if (largo>0 && ancho>0) { formulario.tamano.value=Math.round(ancho*largo*100)/100; }
    
  }
</script>
