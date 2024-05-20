<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$id_ca = $_REQUEST['id_ca'];

if ($_REQUEST['guardar'] == "Guardar") {
	if (!checkdate($_REQUEST['fec_mod_mes'],$_REQUEST['fec_mod_dia'],$_REQUEST['fec_mod_ano'])) {
		echo(msje_js(""
		            ."Al parecer hay un problema con la fecha seleccionada.\\n"
		            ."Lo más seguro es que seleccionó una fecha imposible\\n"
		            ."(como un 29 de febrero con un año no biciesto o un 31 de mayo)\\n"
		            ."o bien no ha ingresado ninguna."
		            .""));
		$_REQUEST['guardar'] = "";
	} else {
		$fec_mod = mktime(0,0,0,$_REQUEST['fec_mod_mes'],$_REQUEST['fec_mod_dia'],$_REQUEST['fec_mod_ano']);
		$_REQUEST['fec_mod'] = strftime("%Y-%m-%d",$fec_mod);		
	}
}

if ($_REQUEST['guardar'] == "Guardar") {
	$fec_mod     = $_REQUEST['fec_mod'];
	$comentarios = $_REQUEST['comentarios'];
	$SQLupdate = "UPDATE cargas_academicas
	              SET fecha_mod='$fec_mod'::date,comentarios='$comentarios' 
	              WHERE id=$id_ca AND homologada;";
	consulta_dml($SQLupdate);
	$id_alumno=$_REQUEST['id_alumno'];
	echo(js("window.location='$enlbase=ver_alumno&id_alumno=$id_alumno&vista=homologaciones'"));
}

$SQL_ca = "SELECT vac.id,vac.id_alumno,vac.asignatura,vpa.cod_asignatura||' '||vpa.asignatura AS homologada_por,
	               extract(DAY from fec_mod) AS fec_mod_dia,extract(MONTH from fec_mod) AS fec_mod_mes,
	               extract(YEAR from fec_mod) AS fec_mod_ano,comentarios
           FROM vista_alumnos_cursos AS vac
           LEFT JOIN vista_prog_asig AS vpa ON vpa.id=vac.id_pa
           WHERE vac.id=$id_ca AND homologada";
$ca = consulta_sql($SQL_ca);

if (count($ca) > 0) {
	$id_alumno=$ca[0]['id_alumno'];
	$SQL_alumno = "SELECT id,nombre,rut,carrera,malla_actual,id_malla_actual,id_carrera
	               FROM vista_alumnos
	               WHERE id=$id_alumno;";
	$alumno = consulta_sql($SQL_alumno);
}
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="get" onSubmit="return enblanco2('fec_mod_dia','fec_mod_mes','fec_mod_ano');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_ca" value="<?php echo($id_ca); ?>">
<input type="hidden" name="id_alumno" value="<?php echo($alumno[0]['id']); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type="submit" name="guardar" value="Guardar"></td>
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
    <td class="celdaNombreAttr">Año Malla Actual:</td>
    <td class="celdaValorAttr"><?php echo($alumno[0]['malla_actual']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Asignatura:</td>
    <td class="celdaValorAttr"><?php echo($ca[0]['asignatura']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Homologada por:</td>
    <td class="celdaValorAttr"><?php echo($ca[0]['homologada_por']); ?></td>
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
    <td class="celdaNombreAttr">Comentarios:</td>
    <td class="celdaValorAttr">
      <textarea name="comentarios"><?php echo($ca[0]['comentarios']); ?></textarea>
      <br>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

