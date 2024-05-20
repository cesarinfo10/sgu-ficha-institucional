<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_malla = $_REQUEST['id_malla'];
if (!is_numeric($id_malla)) {
	echo(js("location.href='principal.php?modulo=gestion_mallas';"));
	exit;
}

$SQL_malla = "SELECT id,ano,carrera,niveles,requisitos_titulacion,id_escuela,comentarios
              FROM vista_mallas
              WHERE id=$id_malla";
$malla     = consulta_sql($SQL_malla);
if (count($malla) > 0) {
	extract($malla[0]);
	
	$SQL_arch_malla = "SELECT id,nombre,descripcion,to_char('DD-MM-YYYY HH24:MI',arch_fecha) AS arch_fecha,
	                          to_char('DD-MM-YYYY HH24:MI',arch_fec_mod) AS arch_fec_mod
	                   FROM mallas_archivos
	                   WHERE id_malla=$id_malla
	                   ORDER BY arch_fec_mod;";
	$arch_malla = consulta_sql($SQL_arch_malla);	
} else {
	echo(msje_js("Se está intentando acceder a una malla inexistente. No es posible continuar"));
	echo(js("location.href='principal.php?modulo=gestion_carreras';"));
	exit;
}

if ($_REQUEST['guardar'] == "Guardar") {
		
	$arch_nombre     = $_FILES['arch']['name'];
	$arch_tmp_nombre = $_FILES['arch']['tmp_name'];
	$arch_tipo_mime  = $_FILES['arch']['type'];
	$arch_longitud   = $_FILES['arch']['size'];

	if ($arch_tipo_mime <> "application/pdf" || $arch_longitud > 1048576*2) {
		echo(msje_js("ATENCIÓN: El archivo que está intentando subir no está en formato PDF "
		            ."o bien el tamaño sobrepasa 2MB.\\n"
		            ."Lo sentimos, pero no están permitidos otros formatos por motivos de "
		            ."compatibilidad. Así mismo 1MB es más que suficiente para almacenar un "
		            ."documento de varias decenas de páginas."
		            ."Puede transformar a formato PDF usando cualquier aplicación que lo "
		            ."permita, como por ejemplo OpenOffice/LibreOffice"));
	} else {
		$nombre      = $_REQUEST['nombre'];
		$descripcion = $_REQUEST['descripcion'];
		$arch_data   = pg_escape_bytea(file_get_contents($arch_tmp_nombre));
		$id_usuario  = $_SESSION['id_usuario'];
		$SQLINS_arch_malla = "INSERT INTO mallas_archivos (nombre,descripcion,arch_nombre,arch_data,id_malla,id_usuario) 
		                         VALUES ('$nombre','$descripcion','$arch_nombre','{$arch_data}',$id_malla,$id_usuario);";
		if (consulta_dml($SQLINS_arch_malla) > 0) {
			echo(msje_js("Se ha recibido y guardado satisfactoriamente el archivo para la Mediateca de Plan de Estudios."));
			echo(js("window.location='$enlbase=mediateca_plan_de_estudios&id_malla=$id_malla';"));
			exit;
		}
	}
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>: <?php echo($carrera); ?> - <?php echo($ano); ?>
</div><br>
<form name="formulario" action="principal.php" method="post" enctype="multipart/form-data" onSubmit="return enblanco2('nombre','descripcion','arch')">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_malla" value="<?php echo($id_malla); ?>">
<table class="tabla">
  <tr>
    <td class="tituloTabla">
      <input type="submit" name="guardar" value="Guardar">
      <input type="button" name="volver" value="Volver" onClick="history.back();">
    </td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class='celdaNombreAttr'>Id:</td>
    <td class='celdaValorAttr'><?php echo($id); ?></td>
    <td class='celdaNombreAttr'>Año:</td>
    <td class='celdaValorAttr'><?php echo($ano); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($carrera); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Nombre:</u></td>
    <td class='celdaValorAttr' colspan="3"><input type="text" size='50' name="nombre"></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Descripción:</u></td>
    <td class='celdaValorAttr' colspan="3"><textarea name='descripcion' rows='10' cols="50"></textarea></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Archivo:</u></td>
    <td class='celdaValorAttr' colspan="3"><input type='file' name='arch'></td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

