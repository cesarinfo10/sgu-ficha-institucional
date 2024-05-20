<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

$TIPOS           = consulta_sql("SELECT id,nombre,dimension AS grupo FROM dpii.tipos_prod ORDER BY grupo,nombre");
$USUARIOS_REG    = consulta_sql("SELECT id,nombre,tipo AS grupo FROM vista_usuarios WHERE activo='Si' ORDER BY tipo,nombre");
$FORMATOS_PUBLIC = consulta_sql("SELECT id,nombre FROM vista_dpii_formato_public_prod ORDER BY nombre");
$MEDIOS_PUBLIC   = consulta_sql("SELECT id,nombre FROM dpii.medios_publicacion ORDER BY nombre");
$ALCANCES        = consulta_sql("SELECT id,nombre FROM vista_dpii_alcance_prod ORDER BY nombre");
$PAISES          = consulta_sql("SELECT localizacion AS id,nombre FROM pais ORDER BY nombre");
$MODALIDADES     = consulta_sql("SELECT id,nombre FROM vista_tipo_clase");

if ($_REQUEST['id_usuario_reg'] == "") { $_REQUEST['id_usuario_reg'] = $_SESSION['id_usuario']; }
if ($_REQUEST['ano'] == "") { $_REQUEST['ano'] = $ANO; }

if ($_REQUEST['id_tipo'] > 0) {
	$dimension = consulta_sql("SELECT dimension FROM dpii.tipos_prod WHERE id={$_REQUEST['id_tipo']}");
	$dimension = $dimension[0]['dimension'];
	$ESTADOS = consulta_sql("SELECT id,nombre,'Avance' AS grupo FROM dpii.estados_prod WHERE '$dimension' = ANY (dimension) AND NOT termino_exitoso");
	if (perm_ejec_modulo($_SESSION["id_usuario"],'productos_acad_visar_termino')) {	
		$EST_termino = consulta_sql("SELECT id,nombre,'Finalizado exitoso' AS grupo FROM dpii.estados_prod WHERE '$dimension' = ANY (dimension) AND termino_exitoso");
		$ESTADOS = array_merge($ESTADOS,$EST_termino);
	}
}

$fecha_min = date("Y-m-d",strtotime($_REQUEST['fec_inicio']));
$ano_min = (empty($_REQUEST['ano'])) ? date("Y") : $_REQUEST['ano'];
?>

<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_prod_acad" value="<?php echo($id_prod_acad); ?>">
<div style='margin-top: 5px'>
  <input type="submit" name='guardar' value="💾 Guardar">
  <input type="button" name='cancelar' value="❌ Cancelar" onClick="parent.jQuery.fancybox.close();">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Generales del Producto Académico</td></tr>
  <tr>
    <td class='celdaNombreAttr'><label for="id_tipo">Dimensión/Tipo:</label></td>
    <td class='celdaValorAttr' colspan="3">
	  <select id="id_tipo" name="id_tipo" class='filtro' style='max-width: none' onChange='submitform();' required>
		<option value=''>-- Seleccione --</option>
		<?php echo(select_group($TIPOS,$_REQUEST['id_tipo'])); ?>
      </select>
	</td>
  </tr>
<?php if ($_REQUEST['id_tipo'] > 0) { ?>

  <tr>
    <td class='celdaNombreAttr'><label for="nombre">Nombre:</label></td>
    <td class='celdaValorAttr' colspan="3"><input type="text" size='70' id="nombre" name="nombre" value="<?php echo($_REQUEST['nombre']); ?>" <?php echo($readonly); ?> class='boton' required></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><label for="palabras_clave">Palabras Clave:</label></td>
    <td class='celdaValorAttr' colspan="3"><input type="text" size='50' id="palabras_clave" name="palabras_clave" value="<?php echo($_REQUEST['palabras_clave']); ?>" <?php echo($readonly); ?> class='boton' required></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Autor(es):</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['autores']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><label for="ano">Año </label> y <label for="id_estado">Estado</label>:</td>
    <td class='celdaValorAttr'>
	  <input type="number" min="<?php echo($ano_min); ?>" size='4' id="ano" name="ano" class="boton" style='width: 50px' value="<?php echo($_REQUEST['ano']); ?>" required>
	  <select id="id_estado" name="id_estado" class='filtro' style='max-width: none' required>
		<option value=''>-- Seleccione --</option>
		<?php echo(select_group($ESTADOS,$_REQUEST['id_estado'])); ?>
      </select>
    </td>
    <td class='celdaNombreAttr'><label for="alcance">Alcance:</label></td>
    <td class='celdaValorAttr'>
	  <select id="alcance" name="alcance" class='filtro' style='max-width: none' required>
		<option value=''>-- Seleccione --</option>
		<?php echo(select($ALCANCES,$_REQUEST['alcance'])); ?>
      </select>
	</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><label for="public_formato">Formato de Publicación:</td>
    <td class='celdaValorAttr'>
	  <select id="public_formato" name="public_formato" class='filtro' required>
		<option value=''>-- Seleccione --</option>
		<?php echo(select_group($FORMATOS_PUBLIC,$_REQUEST['public_formato'])); ?>
      </select>
    </td>
    <td class='celdaNombreAttr'><label for="id_medio_public">Medio de Publicación:</td>
    <td class='celdaValorAttr'>
	  <select id="id_medio_public" name="id_medio_public" class='filtro' required>
		<option value=''>-- Seleccione --</option>
		<?php echo(select_group($MEDIOS_PUBLIC,$_REQUEST['id_medio_public'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><label for="fecha_inicio">Fecha de Inicio:</label></td>
    <td class='celdaValorAttr'>
      <input type="date" id="fecha_inicio" name="fecha_inicio" min="<?php echo($fecha_min); ?>" class="boton" value="<?php echo($_REQUEST['fecha_inicio']); ?>" onChange="formulario.fecha_termino.value=this.value; formulario.fecha_termino.min=this.value;" required>
    </td>
    <td class='celdaNombreAttr'><label for="fecha_termino">Fecha de Término:</label></td>
    <td class='celdaValorAttr'>
      <input type="date" id="fecha_termino" name="fecha_termino" min="<?php echo($fecha_min); ?>" class="boton" value="<?php echo($_REQUEST['fecha_termino']); ?>" required>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Asignatura(s):</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['asignaturas']); ?></td>
  </tr>

<?php if ($modulo == "productos_acad_editar") { ?>
<?php } ?>

  <tr>
    <td class='celdaNombreAttr'><label for="id_usuario_reg">Registrado por:</label></td>
    <td class='celdaValorAttr' colspan="3">
	    <select name="id_usuario_reg" id="id_usuario_reg" class='filtro' required>
		    <option value=''>-- Buscar --</option>
		    <?php echo(select_group($USUARIOS_REG,$_REQUEST['id_usuario_reg'])); ?>
      </select>
	  </td>
  </tr>

<?php	if ($dimension == "Revistas") { ?>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Revista</td></tr>
  <tr>
    <td class='celdaNombreAttr'><label for="revista_nombre">Nombre Revista:</label></td>
    <td class='celdaValorAttr' colspan="3"><input type="text" size='70' id="revista_nombre" name="revista_nombre" value="<?php echo($_REQUEST['revista_nombre']); ?>" <?php echo($readonly); ?> class='boton' required></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><label for="revista_numero">Número:</label></td>
    <td class='celdaValorAttr' ><input type="text" size='10' id="revista_numero" name="revista_numero" value="<?php echo($_REQUEST['revista_numero']); ?>" <?php echo($readonly); ?> class='boton' required></td>
    <td class='celdaNombreAttr'><label for="revista_editorial">Editorial:</label></td>
    <td class='celdaValorAttr' ><input type="text" size='35' id="revista_editorial" name="revista_editorial" value="<?php echo($_REQUEST['revista_editorial']); ?>" <?php echo($readonly); ?> class='boton' required></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><label for="revista_ciudad">Ciudad:</label></td>
    <td class='celdaValorAttr' ><input type="text" size='15' id="revista_ciudad" name="revista_ciudad" value="<?php echo($_REQUEST['revista_ciudad']); ?>" <?php echo($readonly); ?> class='boton' required></td>
    <td class='celdaNombreAttr'><label for="revista_pais">País:</label></td>
    <td class='celdaValorAttr' >
	  <select id="revista_pais" name="revista_pais" class='filtro' required>
		<option value=''>-- Seleccione --</option>
		<?php echo(select($PAISES,$_REQUEST['revista_pais'])); ?>
	  </select>
	</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><label for="revista_facto_impacto">Factor de Impacto:</label></td>
    <td class='celdaValorAttr' ><input type="text" size='10' id="revista_facto_impacto" name="revista_facto_impacto" value="<?php echo($_REQUEST['revista_facto_impacto']); ?>" <?php echo($readonly); ?> class='boton' required></td>
    <td class='celdaNombreAttr'><label for="revista_enlace">Enlace:</label></td>
    <td class='celdaValorAttr' ><input type="url" size='35' id="revista_enlace" name="revista_enlace" value="<?php echo($_REQUEST['revista_enlace']); ?>" placeholder='https://' <?php echo($readonly); ?> class='boton' required></td>
  </tr>
<?php	} ?>

<?php	if ($dimension == "Libros") { ?>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes del Libro</td></tr>
  <tr>
    <td class='celdaNombreAttr'><label for="libro_nombre">Nombre del Libro:</label></td>
    <td class='celdaValorAttr' colspan="3"><input type="text" size='70' id="libro_nombre" name="libro_nombre" value="<?php echo($_REQUEST['libro_nombre']); ?>" <?php echo($readonly); ?> class='boton' required></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><label for="libro_editorial">Editorial:</label></td>
    <td class='celdaValorAttr' colspan='3'><input type="text" size='50' id="libro_editorial" name="libro_editorial" value="<?php echo($_REQUEST['libro_editorial']); ?>" <?php echo($readonly); ?> class='boton' required></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><label for="libro_ciudad">Ciudad:</label></td>
    <td class='celdaValorAttr' ><input type="text" size='15' id="libro_ciudad" name="libro_ciudad" value="<?php echo($_REQUEST['libro_ciudad']); ?>" <?php echo($readonly); ?> class='boton' required></td>
    <td class='celdaNombreAttr'><label for="libro_pais">País:</label></td>
    <td class='celdaValorAttr' >
	  <select id="libro_pais" name="libro_pais" class='filtro' required>
		<option value=''>-- Seleccione --</option>
		<?php echo(select($PAISES,$_REQUEST['libro_pais'])); ?>
	  </select>
	</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><label for="libro_enlace">Enlace:</label></td>
    <td class='celdaValorAttr' colspan='3'><input type="url" size='35' id="libro_enlace" name="libro_enlace" value="<?php echo($_REQUEST['libro_enlace']); ?>" placeholder='https://' <?php echo($readonly); ?> class='boton' required></td>
  </tr>
<?php	} ?>

<?php	if ($dimension == "Informes") { ?>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes del Informe</td></tr>
  <tr>
    <td class='celdaNombreAttr'><label for="informe_organismo">Organismo:</label></td>
    <td class='celdaValorAttr' colspan="3"><input type="text" size='70' id="informe_organismo" name="informe_organismo" value="<?php echo($_REQUEST['informe_organismo']); ?>" <?php echo($readonly); ?> class='boton' required></td>
  </tr>

<?php 	} ?>

<?php 	if ($dimension == "Proyectos") { ?>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes del Proyecto</td></tr>
  <tr>
    <td class='celdaNombreAttr'><label for="proyecto_organismo">Organismo/Fondo:</label></td>
    <td class='celdaValorAttr' colspan="3"><input type="text" size='70' id="informe_organismo" name="informe_organismo" value="<?php echo($_REQUEST['informe_organismo']); ?>" <?php echo($readonly); ?> class='boton' required></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><label for="proyecto_id_invest_princ">Investigador principal:</label></td>
    <td class='celdaValorAttr' colspan="3">
	  <select name="proyecto_id_invest_princ" id="proyecto_id_invest_princ" class='filtro' required>
		<option value=''>-- Seleccione --</option>
		<?php echo(select_group($USUARIOS_REG,$_REQUEST['proyecto_id_invest_princ'])); ?>
      </select>
	</td>
  </tr>
<?php 	} ?>

<?php   if ($dimension == "Ponencias") { ?>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Ponecia</td></tr>
  <tr>
    <td class='celdaNombreAttr'><label for="ponencia_nombre_congreso">Nombre del Congreso/Seminario:</label></td>
    <td class='celdaValorAttr' colspan='3'><input type="text" size='70' id="ponencia_nombre_congreso" name="ponencia_nombre_congreso" value="<?php echo($_REQUEST['ponencia_nombre_congreso']); ?>" <?php echo($readonly); ?> class='boton' required></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><label for="ponencia_ciudad">Ciudad</label>/<label for="ponencia_pais">País</label>:</td>
    <td class='celdaValorAttr' >
      <input type="text" size='15' id="ponencia_ciudad" name="ponencia_ciudad" value="<?php echo($_REQUEST['ponencia_ciudad']); ?>" <?php echo($readonly); ?> class='boton' required>
      <select id="ponencia_pais" name="ponencia_pais" class='filtro' required>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($PAISES,$_REQUEST['ponencia_pais'])); ?>
      </select>
    </td>
    <td class='celdaNombreAttr'><label for="ponencia_modalidad">Modalidad:</label></td>
    <td class='celdaValorAttr' colspan='3'>
      <select id="ponencia_modalidad" name="ponencia_modalidad" class='filtro' required>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($MODALIDADES,$_REQUEST['ponencia_modalidad'])); ?>
      </select>
    </td>
  </tr>
<?php   } ?>

<?php } ?>
</table>
</form>

<script>

$(document).ready(function () {
	$('#id_usuario_reg').selectize({
		sortField: 'text'
	});
});

$(document).ready(function () {
	$('#proyecto_id_invest_princ').selectize({
		sortField: 'text'
	});
});

$(document).ready(function () {
	$('#id_curso1').selectize({
		sortField: 'text'
	});
});

$(document).ready(function () {
	$('#id_curso2').selectize({
		sortField: 'text'
	});
});

$(document).ready(function () {
	$('#id_curso3').selectize({
		sortField: 'text'
	});
});

</script>
<!-- Fin: <?php echo($modulo); ?> -->
