<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$nombre_real_usuario = nombre_real_usuario($_SESSION['usuario'],$_SESSION['tipo']);

if ($_REQUEST['guardar'] == "Guardar") {
	$ano         = $_REQUEST['ano'];
	$comentarios = $_REQUEST['comentarios'];
	$activo      = $_REQUEST['activo'];
	$id_creador  = $_SESSION['id_usuario'];
	
	$SQL_flujo = "SELECT 1 FROM finanzas.flujos WHERE ano=$ano";
	$flujo = consulta_sql($SQL_flujo);
	if (count($flujo) > 0) {
		echo(msje_js("Ya existe un flujo para el año $año. No se puede continuar."));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}
	
	$SQL_flujo_insert = "INSERT INTO finanzas.flujos (ano,id_creador,comentarios,activo) VALUES ($ano,$id_creador,'$comentarios','$activo')";
	if (consulta_dml($SQL_flujo_insert) > 0) {
		if ($activo == "t") { consulta_dml("UPDATE finanzas.flujos SET activo=false WHERE ano<>$ano"); }
		echo(msje_js("Se ha creado exitosamente el flujo para el año $año."));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}	

}
	

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div><br>
<form name="formulario" action="principal_sm.php" method="get" onSubmit="if (confirm('Está seguro de crear el Flujo para el año ' + formulario.ano.value + '?')) { return true; } else { return false; }">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">

<input type="submit" name="guardar" value="Guardar">
<br><br>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr'>Año:</td>
    <td class='celdaValorAttr'>
      <input type="text" name="ano" size="4" id="ano"
           onBlur="var fecha = new Date(); if (this.value < 2010 || this.value > fecha.getFullYear()+1) { alert('Año fuera de rango'); this.value=''; return false; }">
      <script>document.getElementById("ano").focus();</script>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Creador:</td>
    <td class='celdaValorAttr'><?php echo($nombre_real_usuario); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Activo:</td>
    <td class='celdaValorAttr'>
      <select name='activo' class='filtro'>
        <?php echo(select($sino,$activo)); ?>
      </select><br>
      <sup>Si deja activo este flujo, será el que se abrirá al acceder al módulo «Flujos Generales»</sup>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="2" style='text-align: left'>Comentarios:</td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="2">
      <textarea name='comentarios' rows='5' cols='50' class='general'></textarea>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->
