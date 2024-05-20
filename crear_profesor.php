<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_prog_curso = $_REQUEST['id_prog_curso'];
$rut           = $_REQUEST['rut'];

$rut_valido = false;

if ($_REQUEST['validar'] == "Validar" && $rut <> "") {
	$profe = consulta_sql("SELECT id FROM usuarios WHERE rut='$rut' AND tipo=3");
	if (count($profe) == 0) {
		$rut_valido = true;
		$solo_leer = "readonly";
	} else {
		$id_prog_curso = empty($id_prog_curso) ? "" : "&id_prog_curso=$id_prog_curso";
		
		$msje = "El RUT ingresado se encuentra presente en nuestra base datos. Desea ver los datos del profesor?";
		$url_si = "$enlbase=ver_profesor&id_profesor={$profe[0]['id']}";
		$url_no = "$enlbase=crear_profesor$id_prog_curso";
		echo(confirma_js($msje,$url_si,$url_no));
		exit;
	}
}

if ($_REQUEST['guardar'] == "Guardar y continuar" && $_REQUEST['rut_valido'] == "1") {
	
	$arch_cv_nombre     = $_FILES['arch_cv']['name'];
	$arch_cv_tmp_nombre = $_FILES['arch_cv']['tmp_name'];
	$arch_cv_tipo_mime  = $_FILES['arch_cv']['type'];
	$arch_cv_longitud   = $_FILES['arch_cv']['size'];
	//var_dump(substr($arch_cv_nombre,-3));		
	if (($arch_cv_tipo_mime <> "application/pdf" && substr($arch_cv_nombre,-3) <> "pdf") || $arch_cv_longitud > 1048576) {
		echo(msje_js("ATENCIÓN: El archivo que está intentando subir no parece estar en formato PDF "
		            ."o bien el tamaño sobrepasa 1MB.\\n"
		            ."Lo sentimos, pero no están permitidos otros formatos por motivos de "
		            ."compatibilidad. Así mismo 1MB es más que suficiente para almacenar un "
		            ."documento de varias decenas de páginas."
		            ."Puede transformar a formato PDF usando cualquier aplicación que lo "
		            ."permita, como por ejemplo OpenOffice"));
	} else {
		$activo = "f";
		//if ($_SESSION['tipo'] == 0) { $activo = "t"; } else { $activo = "f"; }

		$id_prog_curso = empty($id_prog_curso) ? "" : "&id_prog_curso=$id_prog_curso";
		
		$arch_cv_data     = pg_escape_bytea(file_get_contents($arch_cv_tmp_nombre));
		$SQLinsert_profe = "INSERT INTO usuarios (rut,tipo,nombre,apellido,activo,arch_cv,doc_curriculum_vitae) 
		                         VALUES ('$rut',3,'','','$activo','{$arch_cv_data}','t')";
		if(consulta_dml($SQLinsert_profe) > 0) {
			echo(msje_js("Se ha recibido y guardado satisfactoriamente el Currículum Vítae del profesor(a).\\n"
			            ."A continuación complete el formulario con el resto de los datos necesarios del profesor(a)"));
			$profe = consulta_sql("SELECT currval('usuarios_id_seq') AS id;");
			echo(js("window.location='$enlbase=editar_profesor&id_profesor={$profe[0]['id']}$id_prog_curso&mod_ant=crear_profesor';"));
			exit;
		} else {		
			echo(msje_js("ATENCIÓN: Ha ocurrido un error, no se han guardado los datos. Por favor reintente"));
		}
	}
}	
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div><br>

<form name="formulario" action="principal.php" method="post" enctype="multipart/form-data" onSubmit="return valida_rut(formulario.rut);">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_prog_curso" value="<?php echo($id_prog_curso); ?>">
<input type="hidden" name="rut_valido" value="<?php echo($rut_valido); ?>">
<input type="hidden" name="MAX_FILE_SIZE" value="1048576">

<?php if ($rut_valido) {?>
<table cellpadding="4" cellspacing="0" border="0" class="tabla">
  <tr>
    <td align="center" class="textoTabla" style="vertical-align: middle;">
      <input type="submit" name="guardar" value="Guardar y continuar">
      <input type="button" onClick="history.back();" name="cancelar" value="Cancelar">
    </td>
  </tr>
</table><br>
<?php } ?>

<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla'>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'>
      <input type="text" size="12" name="rut" value="<?php echo($rut); ?>"
             onChange="var valor=this.value;this.value=valor.toUpperCase();" 
             <?php echo($solo_leer); ?>>
      <script>formulario.rut.focus();</script>
      <?php if (!$rut_valido) {?>
      <input type="submit" name="validar" value="Validar">
      <br>
      <sup>Ej: 73124400-6</sup>
      <?php } ?>
    </td>
  </tr>
  <?php if ($rut_valido) {?>
  <tr>
    <td class='celdaNombreAttr'>Currículum Vítae:<br><sup style="font-weight: normal">(En archivo digital)</sup></td>
    <td class='celdaValorAttr'><input type="file" name="arch_cv"><br><sup>Sólo Archivos PDF (Adobe Acrobat)</sup></td>
  </tr>
  <?php } ?>
</table>

</form>
<!-- Fin: <?php echo($modulo); ?> -->
