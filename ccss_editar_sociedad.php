<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

if (!is_numeric($_REQUEST['id_sociedad'])) {
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$id_sociedad = $_REQUEST['id_sociedad'];

if ($_REQUEST['Guardar'] == "Guardar") {
	$aCampos = array("razon_social","rut","direccion","id_comuna","id_region","email","telefono","fecha_reg","activa");
	$SQLupdate = "UPDATE finanzas.ccss_sociedades SET ".arr2sqlupdate($_REQUEST,$aCampos). " WHERE id=$id_sociedad";
	if (consulta_dml($SQLupdate) > 0) {
		echo(msje_js("Se han guardado exitosamente los datos."));
		echo(js("location.href='principal_sm.php?modulo=ccss_ver_sociedad&id_sociedad=$id_sociedad';"));
		exit;
	} else {
		echo(msje_js("ATENCIÓN: Ha ocurrido un error y NO se han guardado los datos."));
	}
}

$SQL_sociedad = "SELECT id,razon_social,rut,direccion,id_comuna,id_region,
                        email,telefono,to_char(fecha_reg,'DD-MM-YYYY') AS fecha_reg,
                        activa
                  FROM finanzas.ccss_sociedades
                  WHERE id=$id_sociedad";
$sociedad     = consulta_sql($SQL_sociedad);

if (count($sociedad) > 0) {
	extract($sociedad[0]);
} else {
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$comunas = consulta_sql("SELECT id,nombre FROM comunas ORDER BY nombre");
$regiones = consulta_sql("SELECT id,nombre FROM regiones ORDER BY nombre");

?>

<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal_sm.php" method="post">
<input type='hidden' name='modulo' value='<?php echo($modulo); ?>'>
<input type='hidden' name='id_sociedad' value='<?php echo($id_sociedad); ?>'>
<div style='margin-top: 5px'>
  <input type='submit' name='Guardar' value='Guardar'>
  <input type='button' name='Cancelar' value='Cancelar' onClick='parent.jQuery.fancybox.close();'>
</div>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='1' class='tabla' style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style='text-align: center'>Antecedentes Identificatorios</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'>
      <input class='boton' type='text' size='12' name='rut' value='<?php echo($rut); ?>'
           onKeyUp="var valor=this.value;this.value=valor.toUpperCase();"
             onBlur="valida_rut(this);">
    </td>
    <td class='celdaNombreAttr'>Id:</td>
    <td class='celdaValorAttr'><?php echo($id); ?>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Razón Social:</td>
    <td class='celdaValorAttr' colspan='3'><input class='boton' size='50' type='text' name='razon_social' value="<?php echo($razon_social); ?>"></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style='text-align: center'>Antecedentes de Contacto</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Dirección:</td>
    <td class='celdaValorAttr' colspan='3'><input class='boton' size='50' type='text' name='direccion' value="<?php echo($direccion); ?>"></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Comuna:</td>
    <td class='celdaValorAttr'>
      <select name='id_comuna' class='filtro'>
		<option value=''>-- Seleccione --</option>
        <?php echo(select($comunas,$id_comuna)); ?>
      </select>
    </td>
    <td class='celdaNombreAttr'>Región:</td>
    <td class='celdaValorAttr'>
      <select name='id_region' class='filtro'>
		<option value=''>-- Seleccione --</option>
        <?php echo(select($regiones,$id_region)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>e-Mail:</td>
    <td class='celdaValorAttr' colspan='3'><input class='boton' size='40' type='text' name='email' value="<?php echo($email); ?>"></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Telefono:</td>
    <td class='celdaValorAttr' colspan='3'><input class='boton' size='20' type='text' name='telefono' value="<?php echo($telefono); ?>"></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha de Registro:</td>
    <td class='celdaValorAttr'><input class='boton' type='text' size='10' name='fecha_reg' value="<?php echo($fecha_reg); ?>"></td>
    <td class='celdaNombreAttr'>Activo:</td>
    <td class='celdaValorAttr'>
      <select name='activa' class='filtro'>
        <?php echo(select($sino,$activa)); ?>
      </select>
    </td>
  </tr>
</table>

<!-- Fin: <?php echo($modulo); ?> -->
