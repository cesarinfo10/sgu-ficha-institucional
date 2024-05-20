<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");
include("validar_modulo.php");

$id_fuas = $_REQUEST['id_fuas'];
$forma   = $_REQUEST['forma'];
$id_alumno = $_REQUEST['id_alumno'];
$id_egreso = $_REQUEST['id_egreso'];

if ($id_egreso <> "") { $glosa_egreso = consulta_sql("SELECT descripcion FROM dae.glosas_egresos WHERE id=$id_egreso"); }

$GLOSAS_EGRESOS      = consulta_sql("SELECT id,nombre FROM dae.glosas_egresos");
?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<form name="formulario" action="principal_sm.php" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_fuas" value="<?php echo($id_fuas); ?>">
<input type="hidden" name="forma" value="<?php echo($forma); ?>">

<div style='margin-top: 5px'>
<?php

if ($forma == 'editar') {
	echo("  <input type='submit' name='editar' value='Guardar' tabindex='99'>\n");
} else {
	echo("  <input type='submit' name='crear' value='Guardar' tabindex='99'>\n");
}

?>  
  <input type="button" name="cancelar" value="Cancelar" onclick="history.back();">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="2" style="text-align: center; ">Antecedentes del Egreso del Grupo Familiar</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Glosa:</td>
    <td class='celdaValorAttr'><select name="id_egreso" class='filtro' onChange="submitform();" required><option value="">-- Seleccione --</option><?php echo(select($GLOSAS_EGRESOS,$_REQUEST['id_egreso'])); ?></select><br><?php echo($glosa_egreso[0]['descripcion']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Monto:</u></td>
    <td class='celdaValorAttr'>
      $<input type='text' size="9" name='monto' value="<?php echo($_REQUEST['ing_liq_mensual_prom']); ?>" class='montos' onKeyUp="puntitos(this,this.value.charAt(this.value.length-1),this.name);">
<?php
if ($forma <> 'editar') { 
	echo("<small><br>Una vez que complete este formulario,<br>podrá adjuntar los documentos que respalden este Egreso del Grupo Familiar</small>");
}
?>
    </td>
  </tr>
</table>
</form>
