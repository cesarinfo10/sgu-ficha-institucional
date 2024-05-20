<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$id_alumno = $_REQUEST['id_alumno'];

if (!is_numeric($id_alumno)) {
	echo(js("location.href='principal.php?modulo=gestion_alumnos';"));
	exit;
}

$SQL_alumno = "SELECT va.id,va.nombre,va.rut,va.carrera,va.semestre_cohorte||'-'||va.cohorte AS cohorte,va.estado,
                      a.estado AS id_estado,a.estado_tramite,
                      CASE WHEN m.id_alumno IS NOT NULL THEN 'Si' END AS matriculado
               FROM vista_alumnos AS va
               LEFT JOIN alumnos AS a USING (id)
               LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND ((semestre=$SEMESTRE AND ano=$ANO) OR ano=$ANO-1))
               WHERE va.id=$id_alumno AND a.estado IN (0,1,2);";
$alumno = consulta_sql($SQL_alumno);
if (count($alumno) == 0) {
	echo(msje_js("Este alumno no se encuentra Vigente ni Moroso. No se puede cambiar su actual estado."));
	echo(js("window.location='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
}
extract($alumno[0]);

$id_estado = $_REQUEST['id_estado'];
if ($_REQUEST['guardar'] == "Guardar" && is_numeric($id_estado)) {

	if ($alumno[0]['estado_tramite'] == "") {
		$SQLupdate_alumno = "UPDATE alumnos SET estado=$id_estado WHERE id=$id_alumno";
	} else {
		$SQLupdate_alumno = "UPDATE alumnos SET estado=$id_estado,estado_tramite=null WHERE id=$id_alumno";
	}
	
	if (consulta_dml($SQLupdate_alumno) > 0) {

		if ($alumno[0]['estado_tramite'] <> "") {
			$estado_nuevo = consulta_sql("SELECT nombre FROM al_estados WHERE id='$id_estado';");
			$estado_nuevo = $estado_nuevo[0]['nombre'];		
			
			$emails = consulta_sql("SELECT email FROM usuarios WHERE tipo=0 AND activo;");
			$asunto = "SGU: Alumno regulariza situación (Estado en trámite)";
			$cuerpo = "El alumno $nombre de la carrera $carrera, ahora tiene definitivamente el estado de $estado_nuevo.";
			$cabeceras = "From: SGU" . "\r\n"
			           . "Content-Type: text/plain;charset=utf-8" . "\r\n";
	
			for ($x=0;$x<count($emails);$x++) {
				$email = $emails[$x]['email'];
				mail($email,$asunto,$cuerpo,$cabeceras);
			}
		}
		echo(msje_js("Se ha cambiado exitosamente el estado de este alumno"));
	} else {
		echo(msje_js("Ocurrió un problema mientras se intentaba cambiar el estado de este alumno.\\n"
		            ."Por favor, inténtelo más tarde"));
	}
	//echo(js("window.location='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
	echo(js("parent.jQuery.fancybox.close();"));
}

$id_estados = "";
if ($alumno[0]['estado_tramite'] <> "") {
	$id_estados = $alumno[0]['estado_tramite'].',2';
	echo(msje_js("Este alumno tiene un estado en trámite que ha sido asignado por Registro Académico. "
	            ."Una vez que usted cambie el estado, el SGU informará al Registro Académico automáticamente."));
} else {
	if ($alumno[0]['matriculado'] == "Si") {
		$id_estados = "1,2";
	} else {
		$id_estados = "0,2";
	}
}	
$estados = consulta_sql("SELECT id,nombre FROM al_estados WHERE id IN ($id_estados);");
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal2.php" method="get" onSubmit="return enblanco2('id_pa','id_pa_homo');">
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
    <td class="celdaNombreAttr">Estado Actual:</td>
    <td class="celdaValorAttr" colspan="3">
      <select name="id_estado">
        <?php echo(select($estados,$alumno[0]['id_estado']));?>
      </select>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

