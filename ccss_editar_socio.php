<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

if (!is_numeric($_REQUEST['id_socio'])) {
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$id_socio = $_REQUEST['id_socio'];

if ($_REQUEST['Guardar'] == "Guardar") {
	$aCampos = array("nombres","apellidos","rut","email","telefono","fecha_reg","activo");
	$SQLupdate = "UPDATE finanzas.ccss_socios SET ".arr2sqlupdate($_REQUEST,$aCampos). " WHERE id=$id_socio";
	if (consulta_dml($SQLupdate) > 0) {
		echo(msje_js("Se han guardado exitosamente los datos."));
		echo(js("location.href='principal_sm.php?modulo=ccss_ver_socio&id_socio=$id_socio';"));
		exit;
	} else {
		echo(msje_js("ATENCIÓN: Ha ocurrido un error y NO se han guardado los datos."));
	}
}
	
$SQL_sociedades = "SELECT char_comma_sum(s.razon_social) 
                   FROM finanzas.ccss_sociedades AS s
                   LEFT JOIN finanzas.ccss_sociedades_socios AS ss ON ss.id_sociedad=s.id
                   WHERE ss.id_socio=soc.id";

$SQL_socio = "SELECT id,nombres,apellidos,rut,email,telefono,to_char(fecha_reg,'DD-MM-YYYY') AS fecha_reg,activo,
                     ($SQL_sociedades) AS sociedades
              FROM finanzas.ccss_socios AS soc
              WHERE id=$id_socio";
$socio     = consulta_sql($SQL_socio);

if (count($socio) > 0) {
	extract($socio[0]);
	$sociedades = str_replace(",","<br>",$sociedades);
} else {
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal_sm.php" method="post">
<input type='hidden' name='modulo' value='<?php echo($modulo); ?>'>
<input type='hidden' name='id_socio' value='<?php echo($id_socio); ?>'>
<div style='margin-top: 5px'>
  <input type='submit' name='Guardar' value='Guardar'>
  <input type='button' name='Cancelar' value='Cancelar' onClick='parent.jQuery.fancybox.close();'>
</div>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='1' class='tabla' style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style='text-align: center'>Antecedentes Personales</td>
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
    <td class='celdaNombreAttr'>Nombres:</td>
    <td class='celdaValorAttr'><input class='boton' type='text' name='nombres' value='<?php echo($nombres); ?>'></td>
    <td class='celdaNombreAttr'>Apellidos:</td>
    <td class='celdaValorAttr'><input class='boton' type='text' name='apellidos' value='<?php echo($apellidos); ?>'></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style='text-align: center'>Antecedentes de Contacto</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>e-Mail:</td>
    <td class='celdaValorAttr' colspan='3'><input class='boton' type='text' name='email' value='<?php echo($email); ?>'></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Telefono:</td>
    <td class='celdaValorAttr' colspan='3'><input class='boton' type='text' name='telefono' value='<?php echo($telefono); ?>'></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha de Registro:</td>
    <td class='celdaValorAttr'><input class='boton' type='text' size='10' name='fecha_reg' value='<?php echo($fecha_reg); ?>'></td>
    <td class='celdaNombreAttr'>Activo:</td>
    <td class='celdaValorAttr'>
      <select name='activo' class='filtro'>
        <?php echo(select($sino,$activo)); ?>
      </select>
    </td>
  </tr>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
