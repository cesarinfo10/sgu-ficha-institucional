<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$fecha   = $_REQUEST['fecha'];
$usuario = $_REQUEST['usuario'];

if (empty($fecha))   { $fecha = strftime("%d-%m-%Y"); }
if (empty($usuario)) { $usuario = "mias"; }

$cond_usuario = "";
if ($usuario == "mias") { $cond_usuario = "id_operador={$_SESSION['id_usuario']}"; }

$SQL_reg_diario = "SELECT vc.id,to_char(fecha_hora,'HH24:MI:SS') as hora,cod_asignatura||'-'||seccion||' '||asignatura as asignatura,profesor,
                          coalesce(sesion1,'')||' '||coalesce(sesion2,'')||' '||coalesce(sesion3,'') as horario,vu.nombre AS operador
                   FROM asist_cursos AS ac
                   LEFT JOIN vista_cursos AS vc ON vc.id=id_curso
                   LEFT JOIN vista_usuarios AS vu ON vu.id=id_operador
                   WHERE fecha_hora::date = '$fecha'::date AND $cond_usuario
                   ORDER BY fecha_hora";
$reg_diario     = consulta_sql($SQL_reg_diario);

if (count($reg_diario) == 0) {
	$usuario = "todos";
	
	$SQL_reg_diario = "SELECT vc.id,to_char(fecha_hora,'HH24:MI:SS') as hora,cod_asignatura||'-'||seccion||' '||asignatura as asignatura,profesor,
	                          coalesce(sesion1,'')||' '||coalesce(sesion2,'')||' '||coalesce(sesion3,'') as horario,vu.nombre AS operador
					   FROM asist_cursos AS ac
					   LEFT JOIN vista_cursos AS vc ON vc.id=id_curso
					   LEFT JOIN vista_usuarios AS vu ON vu.id=id_operador
					   WHERE fecha_hora::date = '$fecha'::date
					   ORDER BY fecha_hora";
	$reg_diario     = consulta_sql($SQL_reg_diario);
	
	if (count($reg_diario) == 0) {
		echo(msje_js("No se han encontrado marcaciones para el día de hoy por ningún operador"));
		//echo(js("window.location='$enlbase=profesores_reg_electronico_asistencia';"));
		//exit;
	}
}

$HTML = "";
for ($x=0;$x<count($reg_diario);$x++) {
	extract($reg_diario[$x]);
	$enl = "$enlbase=ver_curso&id_curso=$id";
	$enlace = "<a class='enlitem' href='$enl'>";
	$HTML .= "  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n"
	      .  "    <td class='textoTabla'>$hora</td>"
	      .  "    <td class='textoTabla'>$asignatura</td>"
	      .  "    <td class='textoTabla'>$profesor</td>"
	      .  "    <td class='textoTabla'>$horario</td>"
	      .  "    <td class='textoTabla'>$operador</td>"
	      .  "  </tr>";
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div><br>
<input type="button" value="Registro electrónico de Asistencia Docente"
       onClick="window.location='<?php echo("$enlbase=profesores_reg_electronico_asistencia"); ?>';">
<br><br>
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<div class="texto">
  Marcaciones para el día <input type="text" size="10" name="fecha" value="<?php echo($fecha); ?>"> 
  <input id="mias" type="radio" name="usuario" value="mias" <?php if ($usuario=="mias") { echo("checked"); } ?>>
  <label for="mias">sólo las mías</label>
  <input id="todas" type="radio" name="usuario" value="todos" <?php if ($usuario=="todos") { echo("checked"); } ?>>
  <label for="todas">todas</label>
  <input type="submit" name="enviar" value="Buscar">
</div>
</form>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Hora</td>
    <td class='tituloTabla'>Asignatura</td>
    <td class='tituloTabla'>Profesor</td>
    <td class='tituloTabla'>Horario {sala}</td>
    <td class='tituloTabla'>Operador</td>
  </tr>
  <?php echo($HTML); ?>
</table>
<br>
<div class="texto">

</div><br>

<!-- Fin: <?php echo($modulo); ?> -->
