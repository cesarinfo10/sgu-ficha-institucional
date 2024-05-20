<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$id_usuario = $_REQUEST['id_usuario'];
if (!is_numeric($id_usuario)) {
	echo(js("location.href='$enlbase=gestion_usuarios';"));
	exit;
};

$bdcon = pg_connect("dbname=regacad" . $authbd);

if ($_REQUEST['guardar'] <> "") {
	$aCampos = array("nombre","apellido","tipo","grado_academico","activo","email","sexo","id_escuela","id_unidad","id_tipo_ponderaciones","jefe_unidad");
	$SQLupdate = "UPDATE usuarios SET " . arr2sqlupdate($_REQUEST,$aCampos) . " WHERE id=$id_usuario;";
	$resultado = pg_query($bdcon, $SQLupdate);
	if (!$resultado) {
		echo(msje(pg_last_error()));
	} else {
		$filas = pg_affected_rows($resultado);
	};
	if ($filas > 0) {
		echo(msje_js("Se han guardado los cambios"));		
		echo(js("location.href='$enlbase=ver_usuario&id_usuario=$id_usuario';"));		      
	};
};
	
$SQLtxt = "SELECT nombre,apellido,nombre_usuario,email,grado_academico,tipo,activo,sexo,id_escuela,id_unidad,id_tipo_ponderaciones,jefe_unidad
           FROM usuarios
           WHERE id=$id_usuario;";
$resultado = pg_query($bdcon, $SQLtxt);
$filas = pg_numrows($resultado);
if ($filas > 0) {
	$usuario = pg_fetch_all($resultado);
	$SQLtxt0 = "SELECT id,nombre FROM escuelas;";
	$resultado0 = pg_query($bdcon, $SQLtxt0);
	$filas0 = pg_numrows($resultado0);
	if ($filas0 > 0) {
		$escuelas = pg_fetch_all($resultado0);
	};
};

$tipos_usuario = consulta_sql("SELECT id,nombre FROM usuarios_tipo ORDER BY id");

$UNIDADES = consulta_sql("SELECT id,nombre FROM gestion.unidades ORDER BY nombre");

$TIPOS_PONDERACIONES = consulta_sql("SELECT id,glosa_tipo_ponderaciones AS nombre FROM tipo_ponderaciones ORDER BY id");
?>

<!-- Inicio: editar usuario -->
<form name="formulario" action="principal.php" method="post" onSubmit="return enblanco2('nombre_real','email');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_usuario" value="<?php echo($id_usuario); ?>">
<div class="tituloModulo">
  Gesti&oacute;n de Usuarios - Editar usuario: <?php echo($usuario[0]['nombre_usuario']); ?>
</div><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla">
      <input type="submit" name="guardar" value="Guardar" onClick="return confirmar_guardar();">
    </td>
    <td class="tituloTabla">
      <input type="button" name="cancelar" value="Cancelar"  onClick="cancelar_guardar();">
    </td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="celdaNombreAttr">nombre de usuario:</td>
    <td class="celdaValorAttr"><?php echo($usuario[0]['nombre_usuario']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nombres:</td>
    <td class="celdaValorAttr">
      <input type="text" name="nombre" value="<?php echo($usuario[0]['nombre']); ?>" size="40" onChange="cambiado();">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Apellidos:</td>
    <td class="celdaValorAttr">
      <input type="text" name="apellido" value="<?php echo($usuario[0]['apellido']); ?>" size="40" onChange="cambiado();">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">G&eacute;nero:</td>
    <td class="celdaValorAttr">
      <select name="sexo" onChange="cambiado();">
        <?php echo(select(utf2html($generos),$usuario[0]['sexo'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">email:</td>
    <td class="celdaValorAttr">
      <input type="text" name="email" value="<?php echo($usuario[0]['email']); ?>" size="40" onChange="cambiado();">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Tipo:</td>
    <td class="celdaValorAttr">
      <select name="tipo" onChange="cambiado();">
        <?php echo(select($tipos_usuario,$usuario[0]['tipo'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Grado Acad&eacute;mico:</td>
    <td class="celdaValorAttr">
      <select name="grado_academico" onChange="cambiado();">
        <?php echo(select(utf2html(grados_academicos(null)),$usuario[0]['grado_academico'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Escuela:</td>
    <td class="celdaValorAttr">
      <select name="id_escuela" onChange="cambiado();">
        <option value="">Sin escuela</option>
        <?php echo(select($escuelas,$usuario[0]['id_escuela'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Unidad Administrativa:</td>
    <td class="celdaValorAttr">
      <select name="id_unidad" onChange="cambiado();">
        <option value="">-- Sin Unidad --</option>
        <?php echo(select($UNIDADES,$usuario[0]['id_unidad'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Activo?</td>
    <td class="celdaValorAttr">
      <select name="activo" onChange="cambiado();">
        <?php echo(select(utf2html($sino),$usuario[0]['activo'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Grupo:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name='id_tipo_ponderaciones' class='filtros'>
        <option value="">--Seleccione--</option>
        <?php echo(select($TIPOS_PONDERACIONES,$usuario[0]['id_tipo_ponderaciones'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Jefe de Unidad?</td>
    <td class='celdaValorAttr' colspan="3">
      <select name='jefe_unidad' class='filtros'>
        <option value="">--Seleccione--</option>
        <?php echo(select($sino,$usuario[0]['jefe_unidad'])); ?>
      </select>
    </td>
  </tr>
</table>
</form>
<!-- Fin: editar usuario -->

