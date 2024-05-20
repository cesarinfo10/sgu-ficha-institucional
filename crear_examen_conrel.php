<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

if ($_REQUEST['id_alumno'] == "") {
	echo(js("window.location='$enlbase=gestion_alumnos"));
	exit;
}

$id_alumno    = $_REQUEST['id_alumno'];
$id_pa        = $_REQUEST['id_pa'];

if ($_REQUEST['registrar'] == "Registrar Examen") {
	$aCampos = array("id_alumno","examen_con_rel","id_estado","valida","id_pa","comentarios");
	$SQLinsert = "INSERT INTO cargas_academicas " . arr2sqlinsert($_REQUEST,$aCampos);
	$examen_conrel = consulta_dml($SQLinsert);
	if ($examen_conrel == 0) {
		echo(msje(pg_last_error()));
	} else {
		$mensaje = "Se ha registrado el Examen de Conocimietos Relevantes.\\n"
		         . "Desea registrar otro Examen?";
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
	
	$SQL_pa_aprobados = "(SELECT c.id_prog_asig
	                      FROM cargas_academicas AS ca LEFT JOIN cursos AS c ON c.id=ca.id_curso
	                      WHERE ca.id_alumno='$id_alumno' AND ca.id_estado=1 AND ca.id_curso IS NOT NULL)
	                     UNION
	                     (SELECT id_pa AS id_prog_asig FROM cargas_academicas WHERE id_alumno='$id_alumno' AND convalidado)
	                     UNION
	                     (SELECT id_pa_homo AS id_prog_asig FROM cargas_academicas WHERE id_alumno='$id_alumno' AND homologada)
	                     UNION
	                     (SELECT id_pa AS id_prog_asig FROM cargas_academicas WHERE id_alumno='$id_alumno' AND examen_con_rel)";

	$id_malla_alumno = $alumno[0]['id_malla_actual'];
	
	$SQL_prog_asig = "SELECT id_prog_asig AS id,cod_asignatura||' '||asignatura AS nombre 
		               FROM vista_detalle_malla
		               WHERE id_malla=$id_malla_alumno AND id_prog_asig NOT IN ($SQL_pa_aprobados)
		               ORDER BY nombre;";
	$prog_asig = consulta_sql($SQL_prog_asig);

}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="get" onSubmit="return enblanco2('id_convalidacion','id_prog_asig');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_alumno" value="<?php echo($id_alumno); ?>">
<input type="hidden" name="examen_con_rel" value="t">
<input type="hidden" name="id_estado" value="5">
<input type="hidden" name="valida" value="t">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla"><input type="submit" name="registrar" value="Registrar Examen"></td>
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
    <td class="celdaNombreAttr">Carrrera/Año Malla:</td>
    <td class="celdaValorAttr">
      <a href="<?php echo('$enlbase=ver_malla&id_malla=$id_malla'); ?>">      
        <?php echo($alumno[0]['carrera']."/".$alumno[0]['malla_actual']); ?>
      </a>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Asignatura:</td>
    <td class="celdaValorAttr">
      <select name="id_pa">
        <option value="">-- Seleccione --</option>
        <?php echo(select($prog_asig,$id_pa)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Comentarios:</td>
    <td class="celdaValorAttr">
      <textarea name="comentarios"></textarea>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

