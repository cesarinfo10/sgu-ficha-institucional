<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$id_alumno    = $_REQUEST['id_alumno'];
$id_convalida = $_REQUEST['id_convalida'];
$id_pa        = $_REQUEST['id_pa'];
$id_pa_homo   = $_REQUEST['id_pa_homo'];
$id_malla     = $_REQUEST['id_malla'];

if ($_REQUEST['homologar'] == "Guardar Homologación") {
        if (!checkdate($_REQUEST['fec_mod_mes'],$_REQUEST['fec_mod_dia'],$_REQUEST['fec_mod_ano'])) {
                echo(msje_js(""
                            ."Al parecer hay un problema con la fecha seleccionada.\\n"
                            ."Lo más seguro es que seleccionó una fecha imposible\\n"
                            ."(como un 29 de febrero con un año no biciesto o un 31 de mayo)\\n"
                            ."o bien no ha ingresado ninguna."
                            .""));
                $_REQUEST['homologar'] = "";
        } else {
                $fecha_mod = mktime(0,0,0,$_REQUEST['fec_mod_mes'],$_REQUEST['fec_mod_dia'],$_REQUEST['fec_mod_ano']);
                $_REQUEST['fecha_mod'] = strftime("%Y-%m-%d",$fecha_mod);
        }
}

if ($_REQUEST['homologar'] == "Guardar Homologación") {
	$filas = 0;
	$aCampos = array("id_alumno","homologada","id_estado","valida","id_pa","id_pa_homo","comentarios","fecha_mod");
	$SQLinsert = "INSERT INTO cargas_academicas " . arr2sqlinsert($_REQUEST,$aCampos);
	echo($SQLinsert);
	$filas = consulta_dml($SQLinsert);
	if ($filas == 0) {
		echo(msje_js("ERROR: No se ha podido ingresar este registro.\\n
                              Es muy posible que esté intentando realizar una Homologación dos veces."));
	} else {
		$mensaje = "Se ha creado la homologación.\\n"
		         . "Desea crea otra?";
		$url_si = "$enlbase=$modulo&id_alumno=$id_alumno&id_malla=$id_malla";
		$url_no = "$enlbase=ver_alumno&id_alumno=$id_alumno";
		echo(confirma_js($mensaje,$url_si,$url_no));
	};
};

$SQL_alumno = "SELECT id,nombre,rut,carrera,malla_actual,id_malla_actual,id_carrera
               FROM vista_alumnos
               WHERE id=$id_alumno;";
$alumno = consulta_sql($SQL_alumno);
if (count($alumno) > 0) {
	$id_malla_actual = $alumno[0]['id_malla_actual'];
	$id_carrera      = $alumno[0]['id_carrera'];
	
//	$SQL_mallas = "SELECT id,ano as nombre FROM mallas WHERE id <> $id_malla_actual AND id_carrera = $id_carrera;";
	$SQL_mallas = "SELECT id,carrera||' '||ano as nombre FROM vista_mallas WHERE id <> $id_malla_actual;";
	$mallas     = consulta_sql($SQL_mallas);
	
	if (count($mallas) == 0) {
		echo(msje_js("La carrera en la que está este(a) alumno(a) tiene sólo una malla"));
		echo(js("window.location='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
		exit;
	}

	$SQL_cursos_aprobados = "SELECT id_curso 
 	                     FROM cargas_academicas
 	                     WHERE id_alumno='$id_alumno' AND id_curso IS NOT NULL AND id_estado=1";

	$SQL_prog_asig_aprobados = "SELECT id_prog_asig FROM cursos WHERE id IN ($SQL_cursos_aprobados)";
	
	$SQL_pa_malla_actual = "SELECT id_prog_asig AS id,cod_asignatura||' '||asignatura AS nombre 
		                     FROM vista_detalle_malla
		                     WHERE id_malla=$id_malla_actual AND id_prog_asig IN ($SQL_prog_asig_aprobados)
		                     ORDER BY nombre;";
	$prog_asig_malla_actual = consulta_sql($SQL_pa_malla_actual); 
	
	if ($id_malla<>"") {
		$SQL_pa_malla_nueva = "SELECT id_prog_asig AS id,cod_asignatura||' '||asignatura AS nombre 
		                       FROM vista_detalle_malla
		                       WHERE id_malla=$id_malla AND id_prog_asig NOT IN ($SQL_prog_asig_aprobados)
		                       ORDER BY nombre;";
		$prog_asig_malla_nueva = consulta_sql($SQL_pa_malla_nueva);
	}	
}

if (empty($ca[0]['fec_mod_dia'])) { $ca[0]['fec_mod_dia'] = date("d"); }
if (empty($ca[0]['fec_mod_mes'])) { $ca[0]['fec_mod_mes'] = date("m"); }
if (empty($ca[0]['fec_mod_ano'])) { $ca[0]['fec_mod_ano'] = date("Y"); }

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="get" onSubmit="return enblanco2('id_pa','id_pa_homo');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_alumno" value="<?php echo($id_alumno); ?>">
<input type="hidden" name="homologada" value="t">
<input type="hidden" name="id_estado" value="3">
<input type="hidden" name="valida" value="t">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type="submit" name="homologar" value="Guardar Homologación"></td>
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
    <td class="celdaNombreAttr">Carrrera:</td>
    <td class="celdaValorAttr"><?php echo($alumno[0]['carrera']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Año de malla actual:</td>
    <td class="celdaValorAttr"><?php echo($alumno[0]['malla_actual']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Año de malla a homologar:</td>
    <td class="celdaValorAttr">
      <select name="id_malla" onChange="submitform()">
        <option value="">-- Seleccione --</option>
        <?php echo(select($mallas,$id_malla)); ?>
      </select><br>
      <sup>Es decir, la nueva malla que seguirá el alumno.</sup>
    </td>
  </tr>
<?php if ($id_malla<>"") {?>  
  <tr>
    <td class="celdaNombreAttr">Asignaturas de la malla actual:</td>
    <td class="celdaValorAttr">
      <select name="id_pa">
        <option value="">-- Seleccione --</option>
        <?php echo(select($prog_asig_malla_actual,$id_pa)); ?>
      </select><br>
      <sup>Se muestran sólo las actualmente cursadas en la Universidad y Aprobadas.</sup>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Homologar por:</td>
    <td class="celdaValorAttr">
      <select name="id_pa_homo">
        <option value="">-- Seleccione --</option>
        <?php echo(select($prog_asig_malla_nueva,$id_pa_homo)); ?>
      </select><br>
      <sub>
		Se muestran sólo las asignaturas que pueden ser homologables.<br>
        Las asignaturas que no se muestras es por que no necesitan ser homologadas
      </sub>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr"><u>Fecha:</u></td>
    <td class="celdaValorAttr">
      <select name="fec_mod_dia">
        <option value="" style="text-align: center; ">- D&iacute;a -</option>
        <?php echo(select($dias_fn,$ca[0]['fec_mod_dia'])); ?>
      </select>/
      <select name="fec_mod_mes">
        <option value="" style="text-align: center; ">- Mes -</option>
        <?php echo(select($meses_fn,$ca[0]['fec_mod_mes'])); ?>
      </select>/
      <select name="fec_mod_ano">
        <option value="" style="text-align: center; ">- Año -</option>
        <?php echo(select($anos,$ca[0]['fec_mod_ano'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">
      Comentarios:
    </td>
    <td class="celdaValorAttr">
      <textarea name="comentarios"></textarea>
      <br>
      <sup>
        Use este espacio para indicar, por ejemplo, cuando homologa 2 programas<br>
        de asignaturas de la malla antigua del alumno, por una de la nueva. Especifique<br>
        aquí el código y nombre de la segunda asignatura,<br>
        mientras que del listado 'Asignaturas de la malla actual:'<br>
        elija el primero 
      </sup>
    </td>
  </tr>
<?php } ?>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

