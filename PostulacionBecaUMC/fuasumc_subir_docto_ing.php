<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");
if (isset($_SESSION['tipo'])) { 
	include("validar_modulo.php"); 
} else { 
	$datos_modulo = modulos($modulo); 
	$nombre_modulo = $datos_modulo['nombre'];
}

$id_fuas    = $_REQUEST['id_fuas'];
$id_gf      = $_REQUEST['id_gf'];
$id_alumno  = $_REQUEST['id_alumno'];
$forma      = $_REQUEST['forma'];

if ($_REQUEST['guardar'] == "Guardar") {

	$max_tam = 6*1024*1024; // 6MB

	$extenciones = array("pdf");

	$mimes = array("application/pdf");

	$arch_docto_nombre     = $_FILES['docto']['name'];
	$arch_docto_tmp_nombre = $_FILES['docto']['tmp_name'];
	$arch_docto_tipo_mime  = $_FILES['docto']['type'];
	$arch_docto_longitud   = $_FILES['docto']['size'];
	$arch_docto_ext        = substr($arch_docto_nombre,-3);
	
	if (!in_array($arch_docto_tipo_mime,$mimes) && !in_array($arch_docto_ext,$extenciones) || $arch_docto_longitud > $max_tam) { 
		echo(msje_js("ERROR: El archivo que está intentando subir no parece estar en formato PDF "
					."o bien el tamaño sobrepasa los 6MB.\\n"
					."Lo sentimos, pero no están permitidos otros formatos por motivos de "
					."compatibilidad. Así mismo 6MB es más que suficiente para almacenar un "
					."documento de varias decenas de páginas."));		
	} else {
		$arch_docto_data = pg_escape_bytea(file_get_contents($arch_docto_tmp_nombre));
		$tipo_doc = $_REQUEST['tipo_doc'];
		
		if (!empty($id_fuas)) {
			$SQLinsert = "INSERT INTO dae.fuas_doctos_ing (id_fuas,tipo_docto,docto) VALUES ($id_fuas,'$tipo_doc','{$arch_docto_data}')";
		}
		
		if (!empty($id_gf)) {		
			$SQLinsert = "INSERT INTO dae.fuas_doctos_ing (id_fuas_grupo_familiar,tipo_docto,docto) VALUES ($id_gf,'$tipo_doc','{$arch_docto_data}')";
		}
		
		if (consulta_dml($SQLinsert) == 1) {
			echo(msje_js("Se ha subido y guardado con éxito el documento."));
		} else {
			echo(msje_js("ERROR: El documento no ha sido posible guardarlo. Por favor intente nuevamente.\\n\\n"
			            ."Es posible que esté subiendo un documento ya previamente subido."));
		}
		echo(js("parent.jQuery.fancybox.close()"));
		exit;
	}
}

if (!empty($id_gf)) {
	$fuas_gf = consulta_sql("SELECT rut,apellidos,nombres FROM dae.fuas_grupo_familiar WHERE id=$id_gf");
	$_REQUEST = array_merge($_REQUEST,$fuas_gf[0]);
}

if (!empty($id_fuas)) {
	$fuas = consulta_sql("SELECT a.rut,a.nombres,a.apellidos FROM dae.fuas LEFT JOIN alumnos AS a ON a.id=fuas.id_alumno WHERE fuas.id=$id_fuas");
	$_REQUEST = array_merge($_REQUEST,$fuas[0]);	
}

$TIPOS_DOC_ING  = consulta_sql("SELECT * FROM vista_fuas_tipos_doctos_ing");
?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  Postulación a Beca UMC: Subir documento de Acreditacion de Renta
</div>

<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_fuas" value="<?php echo($id_fuas); ?>">
<input type="hidden" name="id_gf" value="<?php echo($id_gf); ?>">

<div style='margin-top: 5px'>
  <input type='submit' name='guardar' value='Guardar' tabindex='99'>
  <input type="button" name="cancelar" value="Cancelar" onclick="history.back();">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales</td></tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($_REQUEST['rut']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Apellidos:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['apellidos']); ?></td>
    <td class='celdaNombreAttr'>Nombres:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['nombres']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Tipo de Documento:</u></td>
    <td class='celdaValorAttr' colspan='3'><select name="tipo_doc" class='filtro' required><option value=''>-- Seleccione --</option><?php echo(select($TIPOS_DOC_ING,$_REQUEST['tipo_doc'])); ?></select></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='vertical-align: top'><u>Archivo:</u></td>
    <td class='celdaValorAttr' colspan='3'>
      <input type='file' name='docto' class="boton" accept=".pdf" required><br>
      El documento debe estar en formato PDF y con un tamaño no superior a 6MB. <br><br>
      Normalmente los archivos que se han obtenido desde los portales del SII, AFP, IPS o AFC,<br>
      ya cumplen con los requisitos aquí exigidos de formato y tamaño.      
    </td>
  </tr>
</table>
</form>

