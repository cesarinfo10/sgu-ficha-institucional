<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$bdcon = pg_connect("dbname=regacad" . $authbd);

if ($_REQUEST['crear'] <> "") {
	$nombre_usuario = $_REQUEST['nombre_usuario'];
	$tipo = $_REQUEST['tipo'];
	$SQLtxt = "SELECT id FROM usuarios WHERE nombre_usuario='$nombre_usuario' AND tipo=$tipo;";
	$resultado1 = pg_query($bdcon, $SQLtxt);
	if (pg_numrows($resultado1) > 0) {
		$mensaje  = "Esta intentando crear un usuario que al parecer ya exite en la base de datos.\\n"
		          . "Esto puede estar ocurriendo debido a que para un tipo o perfil de usuario, "
		          . "ya existe el nombre de usuario que esta intentando crear";
		echo(msje_js($mensaje));
	} else {
		$aCampos = array("nombre_usuario","nombre","apellido","sexo","tipo","grado_academico","id_escuela","activo");
		$SQLinsert = "INSERT INTO usuarios " . arr2sqlinsert($_REQUEST,$aCampos);
		$resultado = pg_query($bdcon, $SQLinsert);
		if (!$resultado) {
			echo(msje(pg_last_error()));
		} else {
			$filas = pg_affected_rows($resultado);
		};
		if ($filas > 0) {
			$tipo_usuario = tipos_usuario($_REQUEST['tipo']);
			$asunto = "Nuevo usuario de SGU";
			$cuerpo = "Debes crear el usuario $nombre_usuario en " . $tipo_usuario['servidor'];
			mail("jeugenio@umcervantes.cl",$asunto,$cuerpo);
			$mensaje  = "Se ha creado un nuevo usuario con los datos ingresados.\\n"
			          . "También se ha enviado una petición de creación de casilla de correo al "
			          . "Administrador de Redes y Sistemas, para que genere la cuenta de este nuevo usuario.\\n"
			          . "ATENCIÓN: Este nuevo usuario no podrá ingresar al sistema sino hasta que tenga "
			          . "su casilla de correo creada.\\n\\n"
			          . "Desea añadir a otro usuario?";
			$url_si = "$enlbase=$modulo";
			$url_no = "$enlbase=gestion_usuarios";
			echo(confirma_js($mensaje,$url_si,$url_no));
			exit;
		};
	};
};

$SQL_escuelas = "SELECT id,nombre FROM escuelas;";
$escuelas = consulta_sql($SQL_escuelas);

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="post" onSubmit="return enblanco2('nombre_usuario','nombre','apellido','grado_academico','tipo');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type="submit" name="crear" value="Crear"></td>
    <td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar" onclick="window.location='principal.php?modulo=gestion_usuarios';"></td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="celdaNombreAttr">Nombre de usuario:</td>
    <td class="celdaValorAttr">
      <input type="text" name="nombre_usuario" value="<?php echo($_REQUEST['nombre_usuario']); ?>" size="20">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nombres:</td>
    <td class="celdaValorAttr">
      <input type="text" name="nombre" value="<?php echo($_REQUEST['nombre']); ?>" size="40">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Apellidos:</td>
    <td class="celdaValorAttr">
      <input type="text" name="apellido" value="<?php echo($_REQUEST['apellido']); ?>" size="40">
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">G&eacute;nero:</td>
    <td class="celdaValorAttr">
      <select name="sexo">
        <option value=''>-- Seleccione --</option>
        <?php echo(select($generos,$_REQUEST['sexo'])); ?>
      </select>
    </td>
  </tr>
  <tr>  
    <td class="celdaNombreAttr">Tipo:</td>
    <td class="celdaValorAttr">
      <select name="tipo">
      <option value=''>-- Seleccione --</option>
      <?php echo(select(tipos_usuario(null),$_REQUEST['tipo'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Grado Acad&eacute;mico:</td>
    <td class="celdaValorAttr">
      <select name="grado_academico">
        <option value="">-- Seleccione --</option>
        <?php echo(select(grados_academicos(null),$_REQUEST['grado_academico'])); ?>
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
    <td class="celdaNombreAttr">Activo?</td>
    <td class="celdaValorAttr">
      <select name="activo">
        <?php echo(select($sino,$_REQUEST['activo'])); ?>
      </select>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

