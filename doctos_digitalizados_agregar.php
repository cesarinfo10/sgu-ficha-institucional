<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$rut       = $_REQUEST['rut'];

$alumno = consulta_sql("SELECT id,trim(rut) AS rut,nombre FROM vista_alumnos WHERE rut='$rut'");
if (count($alumno) == 0) {
	$postulante = consulta_sql("SELECT id,trim(rut) AS rut,nombre FROM vista_pap WHERE rut='$rut'");
	extract($postulante[0]);
} else {
	extract($alumno[0]);
}

if ($_REQUEST['guardar'] == "Guardar") {
		
	$arch_nombre     = $_FILES['arch']['name'];
	$arch_tmp_nombre = $_FILES['arch']['tmp_name'];
	$arch_tipo_mime  = $_FILES['arch']['type'];
	$arch_longitud   = $_FILES['arch']['size'];

	if (($arch_tipo_mime <> "application/pdf" && $arch_tipo_mime <> "image/jpeg") || $arch_longitud > 1048576) {
		echo(msje_js("ATENCIÓN: El archivo que está intentando subir no está en formato PDF o JPEG"
		            ."o bien el tamaño sobrepasa 1MB.\\n"
		            ."Lo sentimos, pero no están permitidos otros formatos por motivos de "
		            ."compatibilidad. Así mismo 1MB es suficiente para almacenar un "
		            ."documento de varias decenas de páginas."
		            ."Puede transformar a formato PDF usando cualquier aplicación que lo "
		            ."permita, como por ejemplo OpenOffice/LibreOffice"));
	} else {
		$id_tipo    = $_REQUEST['id_tipo'];
		$arch_data  = pg_escape_bytea(file_get_contents($arch_tmp_nombre));
		$id_usuario = $_SESSION['id_usuario'];
		$comp_docto = consulta_sql("SELECT 1 FROM doctos_digitalizados WHERE id_tipo=$id_tipo AND rut='$rut' AND NOT eliminado");
		if (count($comp_docto) == 0) {
			$SQLINS_docto = "INSERT INTO doctos_digitalizados (rut,id_tipo,nombre_archivo,mime,id_usuario,archivo) 
			                      VALUES ('$rut',$id_tipo,'$arch_nombre','$arch_tipo_mime',$id_usuario,'{$arch_data}');";
			if (consulta_dml($SQLINS_docto) > 0) {
				echo(msje_js("Se ha recibido y guardado satisfactoriamente el documento."));
				echo(js("window.location='$enlbase_sm=doctos_digitalizados&rut=$rut';"));
				exit;
			}
		} else {
			$tipo_docto  = consulta_sql("SELECT nombre FROM doctos_digital_tipos WHERE id=$id_tipo");
			$nombre_tipo = $tipo_docto[0]['nombre'];
			echo(msje_js("ERROR: Ya existe un documento de tipo $nombre_tipo registrado para este postulante/alumno.\\n\\n"
			            ."Si está realizando un reemplazo, elimine el documento existente en primer lugar y luego repita esta operación."));
			echo(js("window.location='$enlbase_sm=doctos_digitalizados&rut=$rut';"));
			exit;
		}
	}
}

$TIPOS_DOCTOS = consulta_sql("SELECT id,nombre||' ('||mime||')' AS nombre FROM doctos_digital_tipos ORDER BY nombre");
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name='formulario' action='principal_sm.php' method='post' enctype="multipart/form-data">
<input type='hidden' name='modulo' value='<?php echo($modulo); ?>'>
<input type='hidden' name='rut' value='<?php echo($rut); ?>'>

<div  style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Guardar">
  <input type="button" name="volver" value="Volver" onClick="history.back();">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="6" style="text-align: center; ">Antecedentes Personales del (la) Postulante/Alumno(a)</td></tr>
  <tr>
    <td class='celdaNombreAttr'><u>RUT:</u></td>
    <td class='celdaValorAttr'><?php echo($rut); ?></td>
    <td class='celdaNombreAttr'><u>Nombre:</u></td>
    <td class='celdaValorAttr'><?php echo($nombre); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Contenido</u></td>
    <td class='celdaValorAttr' colspan='3'>
      <select class="filtro" name="id_tipo" style='max-width: none'>
        <option value="">-- Seleccione --</option>
        <?php echo(select($TIPOS_DOCTOS,$tipo)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Archivo:</u></td>
    <td class='celdaValorAttr' colspan="3">
      <input type='file' name='arch'><br>
        ATENCIÓN: Sólo se aceptan archivos en formato PDF o JPG y con una longitud de hasta 1 MB.<br>
                  Las imagenes serán redimencionadas a 900x600 pixeles, si la resolución está excedida.
    </td>
  </tr>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
