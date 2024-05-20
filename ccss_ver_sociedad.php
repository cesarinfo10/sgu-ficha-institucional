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

$SQL_socios = "SELECT char_comma_sum(rut||' '||nombres||' '||apellidos) 
               FROM finanzas.ccss_socios 
               WHERE id IN (SELECT id_socio FROM finanzas.ccss_sociedades_socios WHERE id_sociedad=s.id)";

$SQL_sociedad = "SELECT s.id,razon_social,rut,direccion,c.nombre AS comuna,r.nombre AS region,
                        email,telefono,to_char(fecha_reg,'DD-Mon-YYYY') AS fecha_reg,
                        CASE WHEN activa THEN 'Si' ELSE 'No' END AS activo,
                        ($SQL_socios) AS socios
                  FROM finanzas.ccss_sociedades AS s
                  LEFT JOIN comunas AS c ON c.id=s.id_comuna
                  LEFT JOIN regiones AS r ON r.id=s.id_region
                  WHERE s.id=$id_sociedad";
$sociedad     = consulta_sql($SQL_sociedad);

if (count($sociedad) > 0) {
	extract($sociedad[0]);
	$socios = str_replace(",","<br>",$socios);
} else {
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div style='margin-top: 5px'>
  <a href='<?php echo("$enlbase_sm=ccss_editar_sociedad&id_sociedad=$id_sociedad"); ?>' class='boton'>Editar</a>
  <a href='#' onClick='parent.jQuery.fancybox.close();' class='boton'>Cerrar</a>
</div>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='1' class='tabla' style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style='text-align: center'>Antecedentes Identificatorios</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($rut); ?>
    <td class='celdaNombreAttr'>Id:</td>
    <td class='celdaValorAttr'><?php echo($id); ?>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Razón Social:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($razon_social); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style='text-align: center'>Antecedentes de Contacto</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Dirección:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($direccion); ?>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Comuna:</td>
    <td class='celdaValorAttr'><?php echo($comuna); ?>
    <td class='celdaNombreAttr'>Región:</td>
    <td class='celdaValorAttr'><?php echo($region); ?>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>e-Mail:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($email); ?>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Telefono:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($telefono); ?>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha de Registro:</td>
    <td class='celdaValorAttr'><?php echo($fecha_reg); ?>
    <td class='celdaNombreAttr'>Activo:</td>
    <td class='celdaValorAttr'><?php echo($activo); ?>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Socio(s):</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($socios); ?>
  </tr>
</table>

<!-- Fin: <?php echo($modulo); ?> -->
