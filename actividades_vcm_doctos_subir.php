<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

//include("validar_modulo.php");

$id_actividad  = $_REQUEST['id_actividad'];
$id_tipo_docto = $_REQUEST['id_tipo_docto'];

$max_tam      = in_bytes(ini_get("upload_max_filesize"));
$max_tam_inMB = $max_tam/1024/1024;

if ($_REQUEST["guardar"] == "💾 Guardar") {
	
	$archivo_nombre     = $_FILES['archivo']['name'];
	$archivo_tmp_nombre = $_FILES['archivo']['tmp_name'];
	$archivo_tipo_mime  = $_FILES['archivo']['type'];
	$archivo_longitud   = $_FILES['archivo']['size'];
	$archivo_ext        = substr($archivo_nombre,strpos($archivo_nombre,'.')+1);

	if ($archivo_longitud < $max_tam) { 
		
		$archivo_data       = pg_escape_bytea(file_get_contents($archivo_tmp_nombre));

		$SQL_ins = "INSERT INTO vcm.documentos_act (id_actividad,id_tipo,archivo,archivo_nombre,archivo_mime,id_usuario_reg) 
		             VALUES ($id_actividad,$id_tipo_docto,'{$archivo_data}','$archivo_nombre','$archivo_tipo_mime',{$_SESSION['id_usuario']})";

		if (consulta_dml($SQL_ins) == 1) {
			echo(msje_js("Se ha subido el documento de la actividad exitósamente"));
		}

	} else {
		echo(msje_js("ATENCIÓN: El archivo que está intentando subir no parece estar en alguno de los formatos permitidos "
		            ."o bien el tamaño sobrepasa los $max_tam_inMB MB.\\n"
					."Lo sentimos, pero no están permitidos otros formatos por motivos de "
					."compatibilidad. Así mismo 6MB es más que suficiente para almacenar un "
					."documento de varias decenas de páginas."
					."Puede transformar a formato PDF usando cualquier aplicación que lo "
					."permita, como por ejemplo LibreOffice. Si su documento contiene imágenes, "
					."considere exportar a PDF activando la compresión."));		
	}
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$SQL_act = "SELECT *,
                   to_char(fecha_inicio,'DD-tmMonth-YYYY HH24:MI') AS fec_inicio,
                   to_char(fecha_termino,'DD-tmMonth-YYYY HH24:MI') AS fec_termino,
                   dimension,array_to_string(difusion,',') AS difusion,
                   coalesce(nombre_unidad1,'')||','||coalesce(nombre_unidad2,'')||','||coalesce(nombre_unidad3,'') AS unidades
            FROM vista_vcm_actividades AS act
            WHERE id=$id_actividad";
$act = consulta_sql($SQL_act);

if (count($act) == 1) {
    
    $TIPOS_DOCTOS = consulta_sql("SELECT id,nombre FROM vcm.documentos_act_tipo ORDER BY nombre");

    $SQL_doctos = "SELECT doctos.id,to_char(doctos.fecha_reg,'DD-tmMon-YYYY HH24:MI') AS fecha_reg,u.nombre_usuario,dt.nombre,id_tipo
                   FROM vcm.documentos_act AS doctos 
                   LEFT JOIN vcm.documentos_act_tipo AS dt ON dt.id=doctos.id_tipo 
                   LEFT JOIN usuarios AS u ON u.id=doctos.id_usuario_reg
                   WHERE id_actividad=$id_actividad
				   ORDER BY dt.nombre";
    $documentos = consulta_sql($SQL_doctos);

    if (count($documentos) > 0) { 
        $ids_doctos = implode(",",array_column($documentos,"id_tipo"));
        $TIPOS_DOCTOS = consulta_sql("SELECT id,nombre FROM vcm.documentos_act_tipo WHERE id NOT IN ($ids_doctos) ORDER BY nombre");
    }

    $_REQUEST = array_merge($act[0],$_REQUEST);
}

$extenciones = array("pdf","odp","odt","ods","xls","xlsx","doc","docx","ppt","pptx","csv","zip");

?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  Actividades VCM: Subir documento
</div>
<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_actividad" value="<?php echo($id_actividad); ?>">
<div style='margin-top: 5px'>
  <input type="submit" name='guardar' value="💾 Guardar">
  <input type="button" name='cancelar' value="❌ Cancelar" onClick="parent.jQuery.fancybox.close();">
</div>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Actividad</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['nombre']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Objetivo:</label></td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['objetivo']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Dimensión/Tipo:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['dimension']." / ".$_REQUEST['nombre_tipo_act']); ?></td>
    <td class='celdaNombreAttr'>Alcance:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['alcance']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Año:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['ano']); ?></td>
    <td class='celdaNombreAttr'>Modalidad:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['modalidad']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha y hora de Inicio:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['fec_inicio']); ?></td>
    <td class='celdaNombreAttr'>Fecha y hora de Término:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['fec_termino']); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Documento</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Tipo:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name='id_tipo_docto' class='filtro' style='max-width: none' required>
        <?php echo(select($TIPOS_DOCTOS,$id_tipo_docto)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Archivo:</td>
    <td class='celdaValorAttr' colspan="3">
	  <input type="file" name="archivo" class="boton" accept=".pdf,.odp,.odt,.ods,.xls,.xlsx,.doc,.docx,.ppt,.pptx,.csv,.zip,.jpeg,.jpg,.png" onChange="valida_tamano(this);" required><br>
	  <small>Tamaño máximo del archivo: <?php echo($max_tam_inMB); ?> MB</small>
	</td>
  </tr>

</table>
</form>

<script>

function valida_tamano(archivo) {
	var max_tam = <?php echo($max_tam); ?>,
		tamano = archivo.files[0].size;
	if (tamano > max_tam) {
		alert("ERROR: El archivo que ha seleccionado sobrepasa el tamaño permitido.");
		archivo.value = "";
		return false;
	} else {
		return true;
	}
}
</script>


<?php

function in_bytes($val) {
  $val = trim($val);
  $last = strtolower($val[strlen($val)-1]);
  switch($last) {
      // El modificador 'G' está disponble desde PHP 5.1.0
      case 'g':
          $val *= 1024;
      case 'm':
          $val *= 1024;
      case 'k':
          $val *= 1024;
  }

  return $val;
}

?>