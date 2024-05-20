<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_curso = $_REQUEST["id_curso"];
$fecha    = $_REQUEST["fecha"];
$hora     = $_REQUEST["hora"];

if (empty($fecha)) { $fecha = strftime("%d-%m-%Y",time()-86400); }
if (empty($hora))  { $hora = strftime("%R"); }

if ($id_curso <> "") {
	$SQL_curso = "SELECT id,cod_asignatura||'-'||seccion||' '||asignatura AS asignatura,profesor,sesion1,sesion2,sesion3 
	              FROM vista_cursos 
	              WHERE id=$id_curso AND ano=$ANO AND semestre=$SEMESTRE";
	$curso = consulta_sql($SQL_curso);
	if (count($curso) == 1) {
		extract($curso[0]);
	} else {
		echo(msje_js("Este curso ya no se está dictando. No se puede registrar asistencia tardía"));
		echo(js("location.href='$enlbase=cursos_control_asistencia&id_curso=$id_curso';"));
		exit;
	}
}

if ($_REQUEST['guardar'] == "Guardar") {
	$problemas = false;
	if (!strtotime($fecha)) { echo(msje_js("La fecha ingresada es inválida. Debe corregir la información.")); $problemas = true; }
	if (!strtotime($hora))  { echo(msje_js("La hora ingresada es inválida. Debe corregir la información.")); $problemas = true; }
	if (strtotime($hora)<(time()-86400)) { echo(msje_js("La fecha ingresada es anterior a 24 horas. Debe corregir la información.")); $problemas = true; }

	if (!$problemas) {
		$id_operador = $_SESSION['id_usuario'];
		/*
		$SQL_control = "SELECT 1 FROM asist_cursos 
		                WHERE id_curso=$id_curso 
		                  AND fecha_hora-'$fecha $hora'::timestamp <= '30 minutes'::interval 
		                  CASE WHEN fecha_hora::time-'$hora'::time<=' THEN  FROM id_curso=$id_curso AND fecha_hora::date='$fecha'::date";
		$control = consulta_sql($SQL_control);
		*/
		consulta_dml("INSERT INTO asist_cursos (id_curso,fecha_hora,id_operador) VALUES ($id_curso,'$fecha $hora'::timestamp,$id_operador)");
		echo(js("location.href='$enlbase=cursos_control_asistencia&id_curso=$id_curso';"));
		exit;
	}
}
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<br>

<form name="formulario" action="principal_sm.php" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="submit" name="guardar" value="Guardar">
<input type="button" name="cancelar" value="Cancelar" onClick="history.back();">
<br><br>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Datos del resgistro tardío</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nº de Acta:</td>
    <td class='celdaValorAttr' colspan="3">
      <input type="text" name="id_curso" size="5" value="<?php echo($_REQUEST['id_curso']); ?>">
      <?php if (count($curso) == 0) { ?>
        <input type="submit" name="continuar" value="Continuar">
        <script>formulario.id_curso.focus();</script>
      <?php } ?>
    </td>
  </tr>
  <?php if (count($curso) == 1) { ?>
  <tr>
    <td class='celdaNombreAttr'>Curso:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($asignatura); ?></td></tr>
  <tr>
    <td class='celdaNombreAttr'>Profesor:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($profesor); ?></td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><input type="text" name="fecha" size="10" value="<?php echo($fecha); ?>"></td>
    <td class='celdaNombreAttr'>Hora:</td>
    <td class='celdaValorAttr'><input type="text" name="hora" size="5" value="<?php echo($hora); ?>"></td>
  </tr>
  <script>formulario.fecha.focus();</script>
  <?php } ?>
</table>

</form>

<!-- Fin: <?php echo($modulo); ?> -->
