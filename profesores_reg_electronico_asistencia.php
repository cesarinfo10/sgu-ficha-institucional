<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$codigo_barras = $_REQUEST["codigo_barras"];

if (!empty($codigo_barras)) {
	$SQL_cod_barra = "SELECT id FROM vista_cursos_cod_barras WHERE cod=lower('$codigo_barras') AND ano=$ANO AND semestre=$SEMESTRE";
	$cod_barra = consulta_sql($SQL_cod_barra);	
	if (count($cod_barra) > 0){
		$id_curso = $cod_barra[0]['id'];
		$id_operador = $_SESSION['id_usuario'];
		$SQL_curso = "SELECT cod_asignatura||'-'||seccion||' '||asignatura AS asignatura,profesor FROM vista_cursos WHERE id=$id_curso";
		$curso = consulta_sql($SQL_curso);
		consulta_dml("INSERT INTO asist_cursos (id_curso,id_operador) VALUES ($id_curso,$id_operador)");
	}
}
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<br>
<input type="button" value="Registro Diario"
       onClick="window.location='<?php echo("$enlbase=profesores_reg_diario_asistencia"); ?>';">

<form name="formulario" action="principal.php" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<br>
<div class="texto">
  Código de Barras de Libro de Clases:
  <input type="text" name="codigo_barras" size="8" value="">
  <script>formulario.codigo_barras.focus();</script>
  <br><br>
<?php

if (count($cod_barra) > 0){
	echo("Registrando asistencia del profesor <b>".$curso[0]['profesor']."</b> del curso <b>".$curso[0]['asignatura']."</b> 
en ".strftime("%c"));
}

?>

</div><br>

</form>

<!-- Fin: <?php echo($modulo); ?> -->
