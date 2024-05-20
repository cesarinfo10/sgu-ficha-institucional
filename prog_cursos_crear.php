<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$nombre_real_usuario = nombre_real_usuario($_SESSION['usuario'],$_SESSION['tipo']);
$id_usuario          = $_SESSION['id_usuario'];

$semestre = $_REQUEST['semestre'];
$ano      = $_REQUEST['ano'];

if ($_SESSION['id_escuela'] <> "") {
	$id_escuela    = $_SESSION['id_escuela'];
	$id_escuela_ro = "readonly";
} else {
	$id_escuela = $_REQUEST['id_escuela'];
}

if ($_REQUEST['crear'] == 'Crear') {
	unset($_REQUEST);
	if (count(consulta_sql("SELECT 1 FROM prog_cursos WHERE id_escuela=$id_escuela AND ano=$ano AND semestre=$semestre")) > 0) {
		echo(msje_js("Ya existe creada una programación para esta escuela en el periodo señalado.\\n"
		            ."En el módulo Programación de Cursos, pinchela para verla y luego podrá editarla"));
		echo(js("window.location='$enlbase=prog_cursos';"));
		exit;
	}
	
	$SQLinsert_prog_cursos = "INSERT INTO prog_cursos (id_escuela,id_creador,semestre,ano) 
	                               VALUES ($id_escuela,$id_usuario,$semestre,$ano)";
	if (consulta_dml($SQLinsert_prog_cursos) == 1) {
		$prog_curso = consulta_sql("SELECT currval('prog_cursos_id_seq') AS id;");
		echo(msje_js("Se ha creado exitósamente una nueva programación.\\n"
		            ."Ahora SGU lo llevará al módulo de edición para completar con los cursos que desea programar"));
		echo(js("window.location='$enlbase=prog_cursos_ver&id_prog_curso={$prog_curso[0]['id']}';"));
		exit;
	}
}

$escuelas = consulta_sql("SELECT id,nombre FROM escuelas ORDER BY nombre");

switch ($SEMESTRE) {
	case 0:
		$semestre_prog = 1;
		$ano_prog = $ANO;
		break;
	case 1:
		$semestre_prog = 2;
		$ano_prog = $ANO;
		break;
	case 2:
		//$semestre_prog = 0; Se debe regresar a este valor . El cambio es temporal para el inicio de año 2014
		$semestre_prog = 1;
		$ano_prog = $ANO+1;
		break;
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div><br>

<form name="formulario" action="principal.php" method="post" onSubmit="return enblanco2('id_escuela','semestre','ano');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">

<table cellpadding="4" cellspacing="0" border="0" class="tabla">
  <tr>
    <td align="center" class="textoTabla" style="vertical-align: middle;">
      <input type="submit" name="crear" value="Crear">
      <input type="button" name="cancelar" value="Cancelar">
    </td>
  </tr>
</table><br>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="celdaNombreAttr">Escuela:</td>
    <td class="celdaValorAttr" colspan="3">
      <select name="id_escuela" <?php echo($id_escuela_ro); ?>>
        <option value="">-- Seleccione --</option>
        <?php echo(select($escuelas,$id_escuela)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Creador:</td>
    <td class="celdaValorAttr" colspan="3"><?php echo($nombre_real_usuario); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Semestre:</td>
    <td class="celdaValorAttr"><input type="text" size="1" name="semestre" value="<?php echo($semestre_prog); ?>"></td>
    <td class="celdaNombreAttr">Año:</td>
    <td class="celdaValorAttr"><input type="text" size="4" name="ano" value="<?php echo($ano_prog); ?>"></td>
  </tr>
</table>

</form>

<!-- Fin: <?php echo($modulo); ?> -->
