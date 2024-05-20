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

$SQL_sociedades = "SELECT char_comma_sum(s.rut||' '||s.razon_social) 
                   FROM finanzas.ccss_sociedades AS s
                   LEFT JOIN finanzas.ccss_sociedades_socios AS ss ON ss.id_sociedad=s.id
                   WHERE ss.id_socio=soc.id";

$SQL_socio = "SELECT id,nombres||' '||apellidos AS nombre,rut,email,telefono,to_char(fecha_reg,'DD-Mon-YYYY') AS fecha_reg,
                     CASE WHEN activo THEN 'Si' ELSE 'No' END AS activo,
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
<div style='margin-top: 5px'>
  <a href='<?php echo("$enlbase_sm=ccss_editar_socio&id_socio=$id_socio"); ?>' class='boton'>Editar</a>
  <a href='#' onClick='parent.jQuery.fancybox.close();' class='boton'>Cerrar</a>
</div>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='1' class='tabla' style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style='text-align: center'>Antecedentes Personales</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($rut); ?></td>
    <td class='celdaNombreAttr'>Id:</td>
    <td class='celdaValorAttr'><?php echo($id); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($nombre); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style='text-align: center'>Antecedentes de Contacto</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>e-Mail:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($email); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Telefono:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($telefono); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha de Registro:</td>
    <td class='celdaValorAttr'><?php echo($fecha_reg); ?></td>
    <td class='celdaNombreAttr'>Activo:</td>
    <td class='celdaValorAttr'><?php echo($activo); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Sociedad(es):</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($sociedades); ?></td>
  </tr>
</table>

<!-- Fin: <?php echo($modulo); ?> -->
