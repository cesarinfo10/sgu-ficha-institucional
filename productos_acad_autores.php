<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_prod_acad  = $_REQUEST['id_prod_acad'];
$id_autor_prod = $_REQUEST['id_autor_prod'];
$elim_id       = $_REQUEST['elim_id'];

if ($id_tipo_ind > 0) {
    consulta_dml("INSERT INTO vcm.indicadores_act VALUES (default,$id_actividad,$id_tipo_ind,null)");
    consulta_dml("INSERT INTO vcm.actividades_audit VALUES ($id_actividad,{$_SESSION['id_usuario']},'Agrega indicador de Actividad',default)");
}

if ($elim_id > 0) {
    consulta_dml("DELETE FROM vcm.indicadores_act WHERE id=$elim_id");
    consulta_dml("INSERT INTO vcm.actividades_audit VALUES ($id_actividad,{$_SESSION['id_usuario']},'Elimina indicador de Actividad',default)");
}

if ($_REQUEST["guardar"] == "💾 Guardar") {
	$nombres     = $_REQUEST['nombres'];
	$apellidos   = $_REQUEST['apellidos'];
	$genero      = $_REQUEST['genero'];
	$tipo        = $_REQUEST['tipo'];
	$id_profesor = $_REQUEST['id_profesor'];

	if ($nombres <> "" && $apellidos <> "" && $genero <> "" && $tipo <> "") {
		$SQL_ins = "INSERT INTO dpii.autores_prod (id_prod_acad,apellidos,nombres,genero,id_profesor,tipo) "
		         . "VALUES ('$id_prod_acad','$apellidos','$nombres','$genero','$id_profesor','$tipo')";
		if (consulta_dml($SQL_ins) == 1) {
			consulta_dml("INSERT INTO dpii.productos_acad_audit VALUES ($id_prod_acad,{$_SESSION['id_usuario']},'Ingresó un autor(a)',default)");
			echo(msje_js("Se han guardado exitosamente los datos."));
			//echo(js("parent.jQuery.fancybox.close();"));	
		}
	}

    $indicadores = $_REQUEST['indicadores'];
    $SQL_upd = "";
    foreach($indicadores AS $id_tipo_ind => $valor) {
        $SQL_upd .= "UPDATE vcm.indicadores_act SET valor=$valor WHERE id=$id_tipo_ind;";
    }
    if (consulta_dml($SQL_upd) > 0) {
        consulta_dml("INSERT INTO vcm.actividades_audit VALUES ($id_actividad,{$_SESSION['id_usuario']},'Ingreso/modificación de los indicadores',default)");
		echo(msje_js("Se han guardado exitosamente los datos."));
		echo(js("parent.jQuery.fancybox.close();"));
    }
}

$SQL_prod = "SELECT *
            FROM vista_dpii_productos_acad AS vpa
            WHERE id=$id_prod_acad";
$prod = consulta_sql($SQL_prod);

if (count($prod) == 1) {
    
	$USUARIOS   = consulta_sql("SELECT id,nombre||' - '||sexo AS nombre,tipo AS grupo FROM vista_usuarios WHERE activo='Si' ORDER BY tipo,nombre");
	$GENEROS    = consulta_sql("SELECT id,nombre FROM vista_generos");
	$TIPOS_AUTOR = consulta_sql("SELECT id,nombre FROM vista_dpii_tipo_autor_prod");

	$autores = consulta_sql("SELECT id,nombres,apellidos,genero,tipo,id_profesor FROM dpii.autores_prod WHERE id_prod_acad = $id_prod_acad ORDER BY apellidos,nombres");

    $HTML = $HTML_autores = "";
	if (count($autores) == 0) { $HTML = "<tr class='filaTabla'><td class='textoTabla' colspan='4' style='text-align: center'><br>*** Sin autores ingresados ***<br><br></td></tr>"; }    
    for($x=0;$x<count($autores);$x++) {
        $enl_elim = "$enlbase_sm=$modulo&id_prod_acad=$id_prod_acad&elim_id={$autores[$x]['id']}";
        $elim = "<a class='enlaces' href='#' onClick=\"if (confirm('Desea eliminar este autor(a) ({$autores[$x]['nombres']} {$autores[$x]['apellidos']}) del producto?')) { location.href='$enl_elim'; } \"><big style='color: red'>✗</big></a>";
        
        $profesor = ($autores[$x]['id_profesor'] > 0) ? "<span class='Interno'>Interno</span>" : "<span class='Externo'>Externo</span>";

        $HTML .= "<tr class='filaTabla'>\n"
              .  "  <td class='textoTabla' colspan='2'>$elim <label for='autores[{$autores[$x]['id']}]'>{$autores[$x]['apellidos']} {$autores[$x]['nombres']}</label> $profesor</td>\n"
              .  "  <td class='textoTabla'>{$autores[$x]['genero']}</td>\n"
              .  "  <td class='textoTabla'>{$autores[$x]['tipo']}</td>\n"
              .  "</tr>\n";
    }
    $HTML_autores = $HTML;

    $HTML = $HTML_agregar_autor = "";
    $HTML .= "<tr class='filaTabla'>\n"
          .  "  <td class='textoTabla' colspan='4'>\n"
          .  "    <select name='id_profesor' id='id_profesor' class='filtro' onChange='rellenar_nombres_apellidos(this);'>\n"
          .  "      <option value=''>-- Buscar --</option>\n"
          .         select($USUARIOS,"")
          .  "    </select>\n"
          .  "  </td>\n"
          .  "</tr>\n"
          .  "<tr class='filaTabla'>\n"
          .  "  <td class='textoTabla'>\n"
          .  "    <input type='text' size='15' name='nombres' value='' class='boton' placeholder='Primer nombre' required>\n"
          .  "  </td>\n"
          .  "  <td class='textoTabla'>\n"
          .  "    <input type='text' size='15' name='apellidos' value='' class='boton' placeholder='Primer apellido' required>\n"
          .  "  </td>\n"
          .  "  <td class='textoTabla'>\n"
		  .  "    <select name='genero' id='genero' class='filtro' required>\n"
		  .  "      <option value=''>-- Género --</option>\n"
		  .         select($GENEROS,"")
		  .  "    </select>\n"
          .  "  </td>\n"
          .  "  <td class='textoTabla'>\n"
		  .  "    <select name='tipo' id='tipo' class='filtro' required>\n"
		  .  "      <option value=''>-- Tipo de autor(a)--</option>\n"
		  .         select($TIPOS_AUTOR,"")
		  .  "    </select>\n"
          .  "  </td>\n"
          .  "</tr>\n";
    $HTML_agregar_autor = $HTML;

    $_REQUEST = array_merge($prod[0],$_REQUEST);
	$estado = "<span class='".str_replace(" ","",$_REQUEST['estado'])."'>&nbsp;{$_REQUEST['estado']}&nbsp;</span>";

}

?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_prod_acad" value="<?php echo($id_prod_acad); ?>">
<div style='margin-top: 5px'>
  <input type="submit" name='guardar' value="💾 Guardar">
  <input type="button" name='cerrar' value="❌ Cerrar" onClick="parent.jQuery.fancybox.close();">
</div>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes del Producto</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Dimensión/Tipo:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($_REQUEST['dimension']." / ".$_REQUEST['nombre_tipo_prod']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['nombre']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Palabras clave:</label></td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['palabras_clave']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Año / Estado:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['ano']." / ".$estado); ?></td>
    <td class='celdaNombreAttr'>Alcance:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['alcance']); ?></td>
  </tr>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Autores</td></tr>
  <tr>
    <td class='celdaNombreAttr' colspan="2" style="text-align: center; ">Nombre</td>
    <td class='celdaNombreAttr' style="text-align: center; ">Género</td>
    <td class='celdaNombreAttr' style="text-align: center; ">Tipo</td>
  </tr>
  <?php echo($HTML_autores); ?>

  <tr class='filaTituloTabla'>
    <td colspan="4" class='tituloTabla'>Agregar un Autor(a)</td>
  </tr>
  <?php echo($HTML_agregar_autor); ?>

</table>
</form>

<script>

function rellenar_nombres_apellidos(campo) {
    var id_profesor = campo.selectedIndex;
	var aProfesores = campo.options;
	var docente = aProfesores[id_profesor].text.split(' - ');
	var nombre_completo = docente[0];
	var genero = docente[1];
	var aNombre = nombre_completo.split(' ');
	formulario.genero.value = genero;

	if (aNombre.length == 2) {
		formulario.nombres.value = aNombre[0];
		formulario.apellidos.value = aNombre[1];
	} 
	if (aNombre.length == 4) {
		formulario.nombres.value = aNombre[0];
		formulario.apellidos.value = aNombre[2];
	}
}

$(document).ready(function () {
	$('#id_profesor').selectize({
		sortField: 'text'
	});
});

</script>