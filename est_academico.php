<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");
include("validar_modulo_uid_no_cero.php");

$id_alumno = $_REQUEST['id_alumno'];
if (!is_numeric($id_alumno)) {
	echo(js("location.href='principal.php?modulo=gestion_alumnos';"));
	exit;
}

$SQL_alumno = "SELECT va.id,va.nombre,va.rut,va.carrera||'-'||a.jornada AS carrera,va.semestre_cohorte,va.cohorte,va.estado,
                      a.estado AS id_estado,va.id_carrera,a.admision,
                      CASE WHEN m.id_alumno IS NOT NULL THEN 'Si' END AS matriculado
               FROM vista_alumnos AS va
               LEFT JOIN alumnos AS a USING (id)
               LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND semestre=$SEMESTRE AND ano=$ANO)
               WHERE va.id=$id_alumno;";
$alumno = consulta_sql($SQL_alumno);
extract($alumno[0]);
if (count($alumno) == 0) {
	echo(msje_js("Este alumno no se encuentra Vigente ni Moroso. No se puede cambiar su actual estado."));
	echo(js("window.location='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
}

$id_estado_nuevo = $_REQUEST['id_estado_nuevo'];
$estado_fecha    = $_REQUEST['estado_fecha'];
$admision_nuevo  = $_REQUEST['admision_nuevo'];
if ($_REQUEST['guardar'] == "Guardar" && is_numeric($id_estado) && $estado_fecha <> "") {
	
	if ($id_estado_nuevo <> $id_estado) {
		$SQLupd_alumno = "UPDATE alumnos 
		                  SET estado=$id_estado_nuevo,
		                      estado_fecha='$estado_fecha'::date,
		                      estado_id_usuario={$_SESSION['id_usuario']} 
		                  WHERE id=$id_alumno";
		if (consulta_dml($SQLupd_alumno) > 0) {
			consulta_dml("INSERT INTO alumnos_estados (id_alumno,estado,estado_fecha) VALUES ($id_alumno,$id_estado_nuevo,'$estado_fecha'::date)");			
			echo(msje_js("Se ha cambiado exitosamente el estado de este alumno"));
			$estado_nuevo = consulta_sql("SELECT nombre FROM al_estados WHERE id='$id_estado_nuevo';");
			$estado_nuevo = $estado_nuevo[0]['nombre'];			
			$SQL_emails = "SELECT email FROM usuarios
			               WHERE tipo in (1,2,10) AND activo
			                 AND id_escuela IN (SELECT id_escuela FROM carreras WHERE id=$id_carrera)";
			$emails = consulta_sql($SQL_emails);	
			$asunto = "SGU: Alumno cambia de estado";
			$cuerpo = "El alumno $nombre de la carrera $carrera, ahora tiene definitivamente el estado de $estado_nuevo.";
			$cabeceras = "From: SGU" . "\r\n"
			           . "Content-Type: text/plain;charset=utf-8" . "\r\n";
			for ($x=0;$x<count($emails);$x++) {
				$email = $emails[$x]['email'];
				mail($email,$asunto,$cuerpo,$cabeceras);
			}
		}
	}
	if ($admision <> $admision_nuevo) {
		$SQLupd_alumno = "UPDATE alumnos SET admision=$admision WHERE id=$id_alumno";
		if (consulta_dml($SQLupd_alumno) > 0) {
			echo(msje_js("Se ha cambiado exitosamente el estado de este alumno"));
		}
	}
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$id_estados = "";
if ($alumno[0]['matriculado'] == "Si") {
	$id_estados = "1,3,4,5,6,7,8,9,20,51,52,53";
} else {
	$id_estados = "0,1,3,4,5,6,7,8,9,20,51,52,53";
}	

$estados = consulta_sql("SELECT id,nombre FROM al_estados WHERE id IN ($id_estados) ORDER BY id;");

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal_sm.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_alumno" value="<?php echo($id_alumno); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>
<div style="margin-top: 5px">
  <input type="submit" name="guardar" value="Guardar">
  <input type="button" name="cancelar" value="Cancelar" onClick="history.back();">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Alumno</td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Código Interno:</td>
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
    <td class="celdaValorAttr"><?php echo($alumno[0]['semestre_cohorte'].'-'.$alumno[0]['cohorte']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Estado Actual:</td>
    <td class="celdaValorAttr"><?php echo($alumno[0]['estado']); ?></td>
    <td class="celdaNombreAttr">Estado nuevo:<br><br>Fecha:</td>
    <td class="celdaValorAttr">
      <select name="id_estado_nuevo" class='filtro'>
        <?php echo(select($estados,$alumno[0]['id_estado']));?>
      </select><br><br>
      <input type="date" name="estado_fecha" value="<?php echo(date('Y-m-d')); ?>" class='boton'>
    </td>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->
