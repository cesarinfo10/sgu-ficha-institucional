<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_curso     = $_REQUEST['id_curso'];
$dia1         = $_REQUEST['dia1'];
$horario1     = $_REQUEST['horario1'];
$dia2         = $_REQUEST['dia2'];
$horario2     = $_REQUEST['horario2'];
$dia3         = $_REQUEST['dia3'];
$horario3     = $_REQUEST['horario3'];
$sala1        = $_REQUEST['sala1'];
$sala2        = $_REQUEST['sala2'];
$sala3        = $_REQUEST['sala3'];

if ($_REQUEST['guardar'] <> "") {
	if ( !empty($dia1) || !empty($horario1) ) { $sesion1 = $dia1 . $horario1; } else { $sesion1 = NULL; }
	if ( !empty($dia2) || !empty($horario2) ) { $sesion2 = $dia2 . $horario2; } else { $sesion2 = NULL; }
	if ( !empty($dia3) || !empty($horario3) ) { $sesion3 = $dia3 . $horario3; } else { $sesion3 = NULL; }
	if ( ($sesion1 == $sesion2 && (!is_null($sesion1) && !is_null($sesion2))) ||	
	     ($sesion1 == $sesion3 && (!is_null($sesion1) && !is_null($sesion3))) ||
	     ($sesion2 == $sesion3 && (!is_null($sesion2) && !is_null($sesion3)))
	   ) {
		$mensaje_error = "Se han definido 2 o 3 horarios en el mismo día o módulo.\\n"
		               . "Por favor corrija el(los) horarios repetidos.";
		echo(msje_js($mensaje_error));		
	} else {   
		$filas = 0;
		$aCampos = array("dia1","horario1","dia2","horario2","dia3","horario3","sala1","sala2","sala3");
		$SQLupdate = "UPDATE cursos SET " . arr2sqlupdate($_REQUEST,$aCampos) . " WHERE id=$id_curso";
		if (consulta_dml($SQLupdate) > 0) {
			echo(msje_js("Se han guardado los datos"));
			echo(js("window.location='$enlbase=ver_curso&id_curso=$id_curso'"));
			exit;
		}
	}
}

$SQL_cursos = "SELECT id,cod_asignatura||'-'||seccion||' '||asignatura AS asignatura,
                      semestre||'-'||ano AS periodo,profesor,carrera,dia1,horario1,dia2,horario2,dia3,horario3,
                      sala1,sala2,sala3,cantidad_alumnos(id) AS cant_alumnos,cant_alumnos_asist(id) AS cant_alumnos_asist
               FROM vista_cursos
               WHERE id=$id_curso";
$cursos   = consulta_sql($SQL_cursos);
$_REQUEST = array_merge($cursos[0],$_REQUEST);

$horarios = consulta_sql("SELECT id,id||' => '||intervalo AS nombre FROM vista_horarios ORDER BY id;");
$salas    = consulta_sql("SELECT trim(codigo) AS id,nombre||' (cap. '||capacidad||')' AS nombre FROM salas WHERE activa ORDER BY piso,nombre;");

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_curso" value="<?php echo($id_curso); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type="submit" name="guardar" value="Guardar"></td>
    <td class="tituloTabla">
      <input type="button" name="cancelar" value="Cancelar" onClick="window.location='<?php echo("$enlbase=ver_curso&id_curso=$id_curso"); ?>';">
    </td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="celdaNombreAttr">Número Acta:</td>
    <td class="celdaValorAttr"><?php echo($cursos[0]['id']); ?></td>
    <td class="celdaNombreAttr">Periodo:</td>
    <td class="celdaValorAttr"><?php echo($cursos[0]['periodo']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Asignatura:</td>
    <td class="celdaValorAttr" colspan="3"><?php echo($cursos[0]['asignatura']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Carrera:</td>
    <td class="celdaValorAttr" colspan="3"><?php echo($cursos[0]['carrera']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Profesor:</td>
    <td class="celdaValorAttr" colspan="3"><?php echo($cursos[0]['profesor']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Inscrito(a)s:</td>
    <td class='celdaValorAttr'><?php echo($cursos[0]['cant_alumnos']); ?> alumno(a)s</td>
    <td class='celdaNombreAttr'>Asistentes:</td>
    <td class='celdaValorAttr'><?php echo($cursos[0]['cant_alumnos_asist']); ?> alumno(a)s</td>
  </tr>

  <tr><td class="celdaNombreAttr" colspan="4" style="text-align: center;">Pruebas Solemnes</td></tr>

  <tr>
    <td class='celdaNombreAttr'>Primera:</td>
    <td class='celdaValorAttr'><input type='date' name='fec_sol1' value='<?php echo($cursos[0]['fec_sol1']); ?>' class='boton'></td>        
    <td class='celdaValorAttr' colspan='2'>
      <select name="sol1_horario1" class='filtro'>
        <option value="">-- Horario 1 --</option>
        <?php echo(select($horarios,$cursos[0]['sol1_horario1'])); ?>        
      </select>
      <select name="sol1_sala1" class='filtro'>
        <option value="">-- Sala 1 --</option>
        <?php echo(select($salas,$cursos[0]['sol1_sala1'])); ?>        
      </select><hr>        
      <select name="sol1_horario2" class='filtro'>
        <option value="">-- Horario 2 --</option>
        <?php echo(select($horarios,$cursos[0]['sol1_horario2'])); ?>        
      </select>       
      <select name="sol1_sala2" class='filtro'>
        <option value="">-- Sala 2 --</option>
        <?php echo(select($salas,$cursos[0]['sol1_sala2'])); ?>        
      </select><br><br>
    </td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'>Segunda:</td>
    <td class='celdaValorAttr'><input type='date' name='fec_sol2' value='<?php echo($cursos[0]['fec_sol2']); ?>' class='boton'></td>        
    <td class='celdaValorAttr' colspan='2'>
      <select name="sol2_horario1" class='filtro'>
        <option value="">-- Horario 1 --</option>
        <?php echo(select($horarios,$cursos[0]['sol2_horario1'])); ?>        
      </select>
      <select name="sol2_sala1" class='filtro'>
        <option value="">-- Sala 1 --</option>
        <?php echo(select($salas,$cursos[0]['sol2_sala1'])); ?>        
      </select><hr>        
      <select name="sol2_horario2" class='filtro'>
        <option value="">-- Horario 2 --</option>
        <?php echo(select($horarios,$cursos[0]['sol2_horario2'])); ?>        
      </select>       
      <select name="sol2_sala2" class='filtro'>
        <option value="">-- Sala 2 --</option>
        <?php echo(select($salas,$cursos[0]['sol2_sala2'])); ?>        
      </select><br><br>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Recuperativa:</td>
    <td class='celdaValorAttr'><input type='date' name='fec_sol_recup' value='<?php echo($cursos[0]['fec_sol_recup']); ?>' class='boton'></td>        
    <td class='celdaValorAttr' colspan='2'>
      <select name="recup_horario1" class='filtro'>
        <option value="">-- Horario 1 --</option>
        <?php echo(select($horarios,$cursos[0]['recup_horario1'])); ?>        
      </select>
      <select name="recup_sala1" class='filtro'>
        <option value="">-- Sala 1 --</option>
        <?php echo(select($salas,$cursos[0]['recup_sala1'])); ?>        
      </select><hr>        
      <select name="recup_horario2" class='filtro'>
        <option value="">-- Horario 2 --</option>
        <?php echo(select($horarios,$cursos[0]['recup_horario2'])); ?>        
      </select>       
      <select name="recup_sala2" class='filtro'>
        <option value="">-- Sala 2 --</option>
        <?php echo(select($salas,$cursos[0]['recup_sala2'])); ?>        
      </select><br><br>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr" colspan="4" style="text-align: center;">
      Programación Horaria Semanal del curso
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">1ª Sesión:</td>
    <td class="celdaValorAttr" colspan="3">
      <select name="dia1">
        <option value="">-- Día --</option>
        <?php echo(select($dias_palabra,$_REQUEST['dia1'])); ?>
      </select>
      <select name="horario1">
        <option value="">-- Módulo (horario) --</option>
        <?php echo(select($horarios,$_REQUEST['horario1'])); ?>
      </select>
      <select name="sala1">
        <option value="">-- Sala --</option>
        <?php echo(select($salas,$_REQUEST['sala1'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">2ª Sesión:</td>
    <td class="celdaValorAttr" colspan="3">
      <select name="dia2">
        <option value="">-- Día --</option>
        <?php echo(select($dias_palabra,$_REQUEST['dia2'])); ?>
      </select>
      <select name="horario2">
        <option value="">-- Módulo (horario) --</option>
        <?php echo(select($horarios,$_REQUEST['horario2'])); ?>
      </select>
      <select name="sala2">
        <option value="">-- Sala --</option>
        <?php echo(select($salas,$_REQUEST['sala2'])); ?>        
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">3ª Sesión:</td>
    <td class="celdaValorAttr" colspan="3">
      <select name="dia3">
        <option value="">-- Día --</option>
        <?php echo(select($dias_palabra,$_REQUEST['dia3'])); ?>
      </select>
      <select name="horario3">
        <option value="">-- Módulo (horario) --</option>
        <?php echo(select($horarios,$_REQUEST['horario3'])); ?>
      </select>
      <select name="sala3">
        <option value="">-- Sala --</option>
        <?php echo(select($salas,$_REQUEST['sala3'])); ?>        
      </select>
    </td>
  </tr>  
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

