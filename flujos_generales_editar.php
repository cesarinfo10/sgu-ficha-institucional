<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$ano = $_REQUEST['ano'];

if (empty($ano)) {
	echo(js("parent.jQuery.fancybox.close();"));
}

$SQL_flujo = "SELECT ano,vu.nombre AS creador,to_char(fecha_creacion,'DD-tmMon-YYYY') AS fec_creacion,
                     to_char(fecha_modificacion,'DD-tmMon-YYYY HH24:MI') AS fec_mod,comentarios,f.activo,
                     CASE WHEN f.activo THEN 'Activo' ELSE 'No activo' END AS estado
              FROM finanzas.flujos AS f
              LEFT JOIN vista_usuarios AS vu ON vu.id=f.id_creador
              WHERE ano=$ano";
$flujo = consulta_sql($SQL_flujo);
if (count($flujo) == 0) {
	echo(msje_js("ERROR: No es posible editar el flujo del año $año por que no existe."));
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

if ($_REQUEST['guardar'] == "Guardar") {
	$comentarios = $_REQUEST['comentarios'];
	$activo      = $_REQUEST['activo'];	

	$SQL_flujo_update = "UPDATE finanzas.flujos SET comentarios='$comentarios',activo='$activo' WHERE ano=$ano";
	if (consulta_dml($SQL_flujo_update) > 0) {
		if ($activo == "t") { consulta_dml("UPDATE finanzas.flujos SET activo=false WHERE ano<>$ano"); }
		echo(msje_js("Se han guardado los cambios exitosamente para el flujo del año $año."));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}	

}
	
$activo_disabled = "";
if ($flujo[0]['activo'] == 't') { $activo_disabled = "disabled"; }

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div><br>
<form name="formulario" action="principal_sm.php" method="get" onSubmit="if (confirm('Está seguro de guardar los cambios para el Flujo del año ' + formulario.ano.value + '?')) { return true; } else { return false; }">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="ano" value="<?php echo($ano); ?>">

<input type="submit" name="guardar" value="Guardar">
<br><br>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr'>Año:</td>
    <td class='celdaValorAttr'><?php echo($flujo[0]['ano']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Creador:</td>
    <td class='celdaValorAttr'><?php echo($flujo[0]['creador']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Activo:</td>
    <td class='celdaValorAttr'>
      <select name='activo' class='filtro' <?php echo($activo_disabled); ?>>
        <?php echo(select($sino,$flujo[0]['activo'])); ?>
      </select><br>
      <sup>Si deja activo este flujo, será el que se abrirá al acceder al módulo «Flujos Generales»</sup>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="2" style='text-align: left'>Comentarios:</td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="2">
      <textarea name='comentarios' rows='5' cols='50' class='general'><?php echo($flujo[0]['comentarios']); ?></textarea>
    </td>
  </tr>
</table>
<?php if ($activo_disabled == "disabled") { ?>
<input type="hidden" name="activo" value="<?php echo($flujo[0]['activo']); ?>">
<?php } ?>

</form>
<!-- Fin: <?php echo($modulo); ?> -->
