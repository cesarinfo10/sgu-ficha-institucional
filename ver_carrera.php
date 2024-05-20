<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_carrera = $_REQUEST['id_carrera'];
if (!is_numeric($id_carrera)) {
	echo("<script language='JavaScript1.2'>location.href='principal.php?modulo=gestion_carreras';</script>");
	exit;
}

$SQL_carrera = "SELECT vc.id,vc.nombre,vc.alias,vc.coordinador,vc.escuela,vc.malla,c.id_malla_actual,
                       CASE c.jornada 
                         WHEN 'D' THEN 'Diurna'
                         WHEN 'V' THEN 'Vespertina'
                         WHEN 'a' THEN 'Ambas'
                       END AS jornada,r.nombre AS regimen,
                       CASE WHEN c.activa THEN 'Si' ELSE 'No' END AS activa,
                       CASE WHEN c.admision THEN 'Si' ELSE 'No' END AS admision,nombre_titulo,nombre_grado
                FROM vista_carreras AS vc
                LEFT JOIN carreras AS c USING (id)
                LEFT JOIN regimenes AS r ON r.id=c.regimen
                WHERE c.id=$id_carrera;"; 
$carrera = consulta_sql($SQL_carrera);
extract($carrera[0]);

$malla = "<a href='$enlbase=ver_malla&id_malla=$id_malla_actual' class='enlaces'>$malla</a>";
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div style='margin-top: 5px'>
  <input type="button" onClick="window.location='<?php echo("$enlbase=editar_carrera&id_carrera=$id"); ?>';" value="Editar">
  <input type="button" name="volver" value="Volver" onClick="<?php echo($_REQUEST['enl_volver']); ?>">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class="celdaNombreAttr">ID:</td>
    <td class="celdaValorAttr"><?php echo($id); ?></td>
    <td class="celdaNombreAttr">Alias:</td>
    <td class="celdaValorAttr"><?php echo($alias); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nombre:</td>
    <td class="celdaValorAttr" colspan='3'><?php echo($nombre); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Jornada:</td>
    <td class="celdaValorAttr"><?php echo($jornada); ?></td>
    <td class="celdaNombreAttr">Regimen:</td>
    <td class="celdaValorAttr"><?php echo($regimen); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Escuela:</td>
    <td class="celdaValorAttr" colspan='3'><?php echo($escuela); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Coordinador(a):</td>
    <td class="celdaValorAttr" colspan='3'><?php echo($coordinador); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nombre Título:</td>
    <td class="celdaValorAttr" colspan='3'><?php echo($nombre_titulo); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nombre Grado:</td>
    <td class="celdaValorAttr" colspan='3'><?php echo($nombre_grado); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Admisión:</td>
    <td class="celdaValorAttr"><?php echo($admision); ?></td>
    <td class="celdaNombreAttr">Activa:</td>
    <td class="celdaValorAttr"><?php echo($activa); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Malla Actual:</td>
    <td class="celdaValorAttr" colspan='3'><?php echo($malla); ?></td>
  </tr>
</table>
<!-- Fin: <?php echo($modulo); ?> -->

