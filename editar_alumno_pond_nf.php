<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_alumno  = $_REQUEST['id_alumno'];


$SQL_alumno = "SELECT va.id,va.nombre,va.rut,trim(va.carrera) AS alias_carrera,va.estado,
                      CASE a.jornada WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada,
                      c.nombre AS carrera,c.regimen,a.cohorte,a.mes_cohorte,a.semestre_cohorte,pond_s1,pond_s2,pond_nc
               FROM vista_alumnos AS va
               LEFT JOIN alumnos AS a USING (id)
               LEFT JOIN carreras AS c ON c.id=a.carrera_actual
               WHERE va.id=$id_alumno;";
$alumno     = consulta_sql($SQL_alumno);

extract($alumno[0]);

switch ($estado) {
	case "Vigente" || "Egresado" || "Titulado" || "Licenciado" || "Graduado" || "Post-Titulado":
		break;
	default:
		echo(msje_js("ERROR: Este alumno NO tiene estado de «Egresado». «Titulado», «Graduado», «Post-Titulado»."."\\n\\n"."No puede continuar"));
		echo(js("window.location='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
		exit;
}

if ($_REQUEST['guardar'] == "Guardar") {
	$pond_s1 = $_REQUEST['pond_s1'];
	$pond_s2 = $_REQUEST['pond_s2'];
	$pond_nc = $_REQUEST['pond_nc'];
	
	if ($pond_s1<>"" && $pond_s2<>"" && $pond_nc<>"") {
		if ($pond_s1+$pond_nc+$pond_s2 <> 100) {
			echo(msje_js("ERROR: La suma de las ponderaciones no es igual a 100%.\\n No se han guardado los cambios."));
		} else {
			$SQL_update = "UPDATE alumnos
						   SET pond_s1=$pond_s1,pond_s2=$pond_s2,pond_nc=$pond_nc
						   WHERE id=$id_alumno";
			$alumno = consulta_dml($SQL_update);
			if ($alumno == 1) {
				echo(msje_js("Se han guardado los cambios. A partir de este momento al calcular notas finales de este alumnos se usará la nueva ponderación."));
				echo(js("window.location='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
			}
		}
	} else {
		echo(msje_js("ERROR: No se han guardado los cambios."."\\n\\n"."Por favor revise los formatos y valores de los datos ingresados."));
	}
}


?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_alumno" value="<?php echo($id_alumno); ?>">
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Guardar">
  <input type="button" name="cancelar" value="Cancelar" onClick="history.back();">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class="celdaNombreAttr" style="text-align: center" colspan="4">Antecedentes del Alumno</td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">ID:</td>
    <td class="celdaValorAttr"><?php echo($id); ?></td>
    <td class="celdaNombreAttr">RUT:</td>
    <td class="celdaValorAttr"><?php echo($rut); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nombre:</td>
    <td class="celdaValorAttr" colspan="3"><?php echo($nombre); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Carrera:</td>
    <td class="celdaValorAttr" colspan="3"><?php echo($carrera); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Jornada:</td>
    <td class="celdaValorAttr"><?php echo($jornada); ?></td>
    <td class="celdaNombreAttr">Cohorte:</td>
    <td class="celdaValorAttr"><?php echo("$semestre_cohorte-$cohorte $mes_cohorte"); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr" style="text-align: center" colspan="4">Antecedentes de la Ponderación de Nota Final</td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Solemne I (S1):</td>
    <td class="celdaValorAttr" colspan="3"><input type='text' size='2' name='pond_s1' value='<?php echo($pond_s1); ?>'>%<br><sup>Valor de 0 a 100</sup></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Solemne II (S2):</td>
    <td class="celdaValorAttr" colspan="3"><input type='text' size='2' name='pond_s2' value='<?php echo($pond_s2); ?>'>%<br><sup>Valor de 0 a 100</sup></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nota de Cátedra (NC):</td>
    <td class="celdaValorAttr" colspan="3"><input type='text' size='2' name='pond_nc' value='<?php echo($pond_nc); ?>'>%<br><sup>Valor de 0 a 100</sup></td>
  </tr>
</table>
<div class="texto" style='margin-top: 5px'>
  <b>ATENCIÓN:</b> La ponderación debe sumar 100%. Puede dejar al menos una nota o calificación con ponderación 0%.<br><br>
  La ponderación por defecto es S1: 30% NC: 30% S2: 40%, si deja en blanco estos campos.
</div>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

