<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo_uid_no_cero.php");

$id_alumno = $_REQUEST['id_alumno'];

if (!is_numeric($id_alumno)) {
	echo(js("location.href='principal.php?modulo=gestion_alumnos';"));
	exit;
}

$SQL_alumno = "SELECT va.id,va.nombre,va.rut,va.carrera,va.semestre_cohorte||'-'||va.cohorte AS cohorte,va.estado,
                      a.estado AS id_estado,a.estado_tramite,
                      CASE WHEN m.id_alumno IS NOT NULL THEN 'Si' END AS matriculado,moroso_financiero
               FROM vista_alumnos AS va
               LEFT JOIN alumnos AS a USING (id)
               LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND ((semestre=$SEMESTRE AND ano=$ANO) OR ano=$ANO-1))
               WHERE va.id=$id_alumno;";
$alumno = consulta_sql($SQL_alumno);
if (count($alumno) == 0) {
	echo(msje_js("No se puede cambiar su actual estado."));
	echo(js("window.location='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
}
extract($alumno[0]);

$id_estado = $_REQUEST['id_estado'];
if ($_REQUEST['guardar'] == "Guardar" && $id_estado <> "") {

	$SQLupdate_alumno = "UPDATE alumnos SET moroso_financiero='$id_estado' WHERE id=$id_alumno";
	
	if (consulta_dml($SQLupdate_alumno) > 0) {
    consulta_dml("INSERT INTO alumnos_est_financiero_aud (id_alumno,moroso_financiero,id_operador) VALUES ($id_alumno,'$id_estado',{$_SESSION['id_usuario']})");

		echo(msje_js("Se ha marcado exitosamente el estado financiero de este alumno"));
	} else {
		echo(msje_js("Ocurrió un problema mientras se intentaba marcar el estado financiero de este alumno.\\n"
		            ."Por favor, inténtelo más tarde"));
	}
	//echo(js("window.location='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
	echo(js("parent.jQuery.fancybox.close();"));
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal_sm.php" method="get" onSubmit="return enblanco2('id_pa','id_pa_homo');">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_alumno" value="<?php echo($id_alumno); ?>">
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
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Alumno</td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">ID alumno:</td>
    <td class="celdaValorAttr"><?php echo($alumno[0]['id']); ?></td>
    <td class="celdaNombreAttr">RUT:</td>
    <td class="celdaValorAttr"><?php echo($alumno[0]['rut']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nombre:</td>
    <td class="celdaValorAttr" colspan="3"><?php echo($alumno[0]['nombre']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Carrrera:</td>
    <td class="celdaValorAttr"><?php echo($alumno[0]['carrera']); ?></td>
    <td class="celdaNombreAttr">Cohorte:</td>
    <td class="celdaValorAttr"><?php echo($alumno[0]['cohorte']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Moroso Financiero:</td>
    <td class="celdaValorAttr" colspan="3">
      <select name="id_estado">
        <?php echo(select($sino,$alumno[0]['moroso_financiero']));?>
      </select>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

