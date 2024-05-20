<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$bdcon = pg_connect("dbname=regacad" . $authbd);

$id_alumno    = $_REQUEST['id_alumno'];
$id_convalida = $_REQUEST['id_convalida'];
$id_pa        = $_REQUEST['id_pa'];

if ($_REQUEST['convalidar'] == "Convalidar") {
	$filas = 0;
	$aCampos = array("id_alumno","convalidado","id_estado","valida","id_convalida","id_pa","comentarios");
	$SQLinsert = "INSERT INTO cargas_academicas " . arr2sqlinsert($_REQUEST,$aCampos);
	$resultado = pg_query($bdcon, $SQLinsert);
	if (!$resultado) {
		echo(msje(pg_last_error()));
	} else {
		$filas = pg_affected_rows($resultado);
	};
	if ($filas > 0) {
		$mensaje = "Se ha creado la convalidación.\\n"
		         . "Desea crea otra convalidacion?";
		$url_si = "$enlbase=$modulo&id_alumno=$id_alumno";
		$url_no = "$enlbase=ver_alumno&id_alumno=$id_alumno";
		echo(confirma_js($mensaje,$url_si,$url_no));
	};
};

$SQL_alumno = "SELECT id,nombre,rut,carrera,malla_actual,id_malla_actual,id_pap
               FROM vista_alumnos
               WHERE id=$id_alumno;";
$alumno = consulta_sql($SQL_alumno);
if (count($alumno) > 0) {
	$id_pap = $alumno[0]['id_pap'];
	$id_malla = $alumno[0]['id_malla_actual'];
	
	$SQL_convalidaciones = "SELECT id,alias||'/'||asignatura||'/'||ano AS nombre 
	                        FROM vista_convalidaciones 
	                        WHERE id_pap=$id_pap 
	                        ORDER BY nombre;";
	$convalidaciones = consulta_sql($SQL_convalidaciones);

	$SQL_prog_asig = "SELECT id_prog_asig AS id,cod_asignatura||' '||asignatura AS nombre 
		              FROM vista_detalle_malla
		              WHERE id_malla=$id_malla
		              ORDER BY nombre;";
	$prog_asig = consulta_sql($SQL_prog_asig);
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="get" onSubmit="return enblanco2('id_convalidacion','id_prog_asig');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_alumno" value="<?php echo($id_alumno); ?>">
<input type="hidden" name="convalidado" value="t">
<input type="hidden" name="id_estado" value="4">
<input type="hidden" name="valida" value="t">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type="submit" name="convalidar" value="Convalidar"></td>
    <td class="tituloTabla"><input type="button" name="cancelar" value="Cancelar" onClick="history.back();"></td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="celdaNombreAttr">ID alumno:</td>
    <td class="celdaValorAttr"><?php echo($alumno[0]['id']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nombre:</td>
    <td class="celdaValorAttr"><?php echo($alumno[0]['nombre']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">RUT:</td>
    <td class="celdaValorAttr"><?php echo($alumno[0]['rut']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Carrrera/A&ntilde;o:</td>
    <td class="celdaValorAttr">
      <a href="<?php echo("$enlbase=ver_malla&id_malla=$id_malla"); ?>">      
        <?php echo($alumno[0]['carrera']."/".$alumno[0]['malla_actual']); ?>
      </a>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Programa de asignatura externo:</td>
    <td class="celdaValorAttr">
      <select name="id_convalida">
        <option value="">-- Seleccione --</option>
        <?php echo(select($convalidaciones,$id_convalida)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Convalidar por:</td>
    <td class="celdaValorAttr">
      <select name="id_pa">
        <option value="">-- Seleccione --</option>
        <?php echo(select($prog_asig,$id_pa)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">
      Comentarios:
    </td>
    <td class="celdaValorAttr">
      <textarea name="comentarios"></textarea>
      <div class="texto">
        Use este espacio para indicar, por ejemplo, cuando convalida 2 programas<br>
        de asignaturas externos por uno interno, especif&iacute;que aqu&iacute; el segundo<br>
        externo mientras que del listado 'Programa de asignatura externo:'<br>
        elija el primero 
      </div>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

