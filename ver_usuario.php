<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$id_usuario = $_REQUEST['id_usuario'];
if (!is_numeric($id_usuario)) {
	echo("<script language='JavaScript1.2'>location.href='principal.php?modulo=gestion_usuarios';</script>");
	exit;
};

$SQL_usuario= "SELECT vu.id,vu.nombre_usuario,vu.nombre,vu.sexo,vu.tipo,vu.email,
                      vu.grado_acad,coalesce(vu.escuela,'Sin Escuela') as escuela,
                      gu.nombre AS unidad,vu.activo,u.cargo,u.email_gsuite,u.email_personal,
                      to_char(fec_nac,'DD de tmMonth de YYYY') AS fec_nac,
                      u.telefono,u.tel_movil,tp.glosa_tipo_ponderaciones AS grupo,
                      CASE WHEN jefe_unidad THEN 'Si' ELSE 'No' END AS jefe_unidad
               FROM vista_usuarios AS vu
               LEFT JOIN usuarios AS u USING(id)
               LEFT JOIN gestion.unidades AS gu ON gu.id=u.id_unidad
               LEFT JOIN tipo_ponderaciones AS tp ON tp.id=u.id_tipo_ponderaciones
               WHERE vu.id=$id_usuario;";
$usuario = consulta_sql($SQL_usuario);

if (count($usuario) == 0) { exit; }
extract($usuario[0]);


$enl_volver = $_SESSION['enlace_volver'];
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal.php" method="post">
<input type="hidden" name="modulo" value="editar_usuario">
<input type="hidden" name="id_usuario" value="<?php echo($id_usuario); ?>">
<table class="tabla" style="margin-top: 5px">
  <tr>
<?php
/*	if ($_SESSION['tipo'] == 0) { */
?>
    <td class="tituloTabla"><input type="submit" name="editar" value="Editar"></td>
<?php
/*	}; */ 
?>
    <td class="tituloTabla">
      <input type="button" name="volver" value="Volver" onClick="window.location='<?php echo($_SESSION['enlace_volver']); ?>';">
    </td>
  </tr>
</form>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr><td class='celdaNombreAttr' colspan='4' style='text-align: center'>Antecedentes Personales</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre de usuario:</td>
    <td class='celdaValorAttr'><?php echo($nombre_usuario); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($id); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($nombre); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Género:</td>
    <td class='celdaValorAttr'><?php echo($sexo); ?></td>
    <td class='celdaNombreAttr'>F. de Nacimiento:</td>
    <td class='celdaValorAttr'><?php echo($fec_nac); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Perfil:</td>
    <td class='celdaValorAttr'><?php echo($tipo); ?></td>
    <td class='celdaNombreAttr'>Grado Académico:</td>
    <td class='celdaValorAttr'><?php echo($grado_acad); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Activo:</td>
    <td class='celdaValorAttr'><?php echo("<span class='$activo'>$activo</span>"); ?></td>
    <td class='celdaNombreAttr'>Último acceso:</td>
    <td class='celdaValorAttr'><?php echo($ult_acceso); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan='4' style='text-align: center'>Antecedentes de Contacto</td></tr>
  <tr>
    <td class='celdaNombreAttr'>e-Mail Institucional:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($email); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>e-Mail Personal:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($email_personal); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Google Suite:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($email_gsuite); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tel. Fijo:</td>
    <td class='celdaValorAttr'><?php echo($telefono); ?></td>
    <td class='celdaNombreAttr'>Tel. Móvil:</td>
    <td class='celdaValorAttr'><?php echo($tel_movil); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan='4' style='text-align: center'>Antecedentes Administrativos</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Escuela:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($escuela); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Unidad:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($unidad); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cargo:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($cargo); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Grupo:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($grupo); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Jefe de Unidad?</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($jefe_unidad); ?></td>
  </tr>

<?php
/*
foreach($usuario[0] AS $nombre_campo => $valor_campo) {
	$nombre_campo = ucfirst($nombre_campo);
	echo("  <tr>\n");
	echo("    <td class='celdaNombreAttr'>$nombre_campo:</td>\n");
	echo("    <td class='celdaValorAttr'>&nbsp;$valor_campo</td>\n");
	echo("  </tr>\n");
}
*/
?>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

