<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_alumno  = $_REQUEST['id_alumno'];

$SQL_alumno = "SELECT va.id,va.nombre,va.rut,trim(va.carrera) AS alias_carrera,va.estado,
                      CASE a.jornada WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada,
                      c.nombre AS carrera,c.regimen,a.cohorte,a.mes_cohorte,a.semestre_cohorte,
                      a.fecha_inicio_programa,
                      a.fecha_egreso,semestre_egreso,ano_egreso,
                      a.salida_int_fecha,a.salida_int_calif,a.salida_int_nroreg_libro,
                      a.examen_grado_titulo_fecha,a.examen_grado_titulo_calif,examen_grado_titulo_oportunidades,
                      a.examen_titulo_fecha,a.examen_titulo_calif,examen_titulo_oportunidades,
                      a.fecha_titulacion,a.nro_registro_libro_tit,a.nota_titulacion,
                      a.fecha_graduacion,a.nro_registro_libro_grado,a.nota_graduacion,
                      a.rpnp
               FROM vista_alumnos AS va
               LEFT JOIN alumnos AS a USING (id)
               LEFT JOIN carreras AS c ON c.id=a.carrera_actual
               WHERE va.id=$id_alumno;";
$alumno     = consulta_sql($SQL_alumno);

extract($alumno[0]);

if ($estado == "Egresado") {
	if ($ano_egreso == "") { $ano_egreso = $ANO; }
	if ($semestre_egreso == "") { $semestre_egreso = $SEMESTRE; }
}

switch ($estado) {
	case "Vigente" || "Egresado" || "Titulado" || "Licenciado" || "Graduado" || "Post-Titulado":
		break;
	default:
		echo(msje_js("ERROR: Este alumno NO tiene estado de «Egresado». «Titulado», «Graduado», «Post-Titulado»."."\\n\\n"."No puede continuar"));
		echo(js("window.location='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
		exit;
}

if ($regimen == "POST-GD" || $regimen == "POST-TD") {
	$exam_final = consulta_sql("SELECT * FROM alumnos_examen_final_postgrado WHERE id_alumno=$id_alumno");
	extract($exam_final[0]);
	$mes_cohorte = "(".$meses_palabra[$mes_cohorte-1]['nombre'].")";
}

if ($_REQUEST['guardar'] == "Guardar") {
	$fecha_inicio_programa             = $_REQUEST['fecha_inicio_programa'];
	$ano_egreso                        = $_REQUEST['ano_egreso'];
	$semestre_egreso                   = $_REQUEST['semestre_egreso'];
	$fecha_egreso                      = $_REQUEST['fecha_egreso'];
	$salida_int_fecha                  = $_REQUEST['salida_int_fecha'];
	$salida_int_calif                  = $_REQUEST['salida_int_calif'];
	$salida_int_nroreg_libro           = $_REQUEST['salida_int_nroreg_libro'];
	$examen_grado_titulo_fecha         = $_REQUEST['examen_grado_titulo_fecha'];
	$examen_grado_titulo_calif         = $_REQUEST['examen_grado_titulo_calif'];
	$examen_grado_titulo_oportunidades = $_REQUEST['examen_grado_titulo_oportunidades'];
	$examen_titulo_fecha               = $_REQUEST['examen_titulo_fecha'];
	$examen_titulo_calif               = $_REQUEST['examen_titulo_calif'];
	$examen_titulo_oportunidades       = $_REQUEST['examen_titulo_oportunidades'];
	$fecha_titulacion                  = $_REQUEST['fecha_titulacion'];
	$nota_titulacion                   = $_REQUEST['nota_titulacion'];
	$nro_registro_libro_tit            = $_REQUEST['nro_registro_libro_tit'];
	$fecha_graduacion                  = $_REQUEST['fecha_graduacion'];
	$nota_graduacion                   = $_REQUEST['nota_graduacion'];
	$nro_registro_libro_grado          = $_REQUEST['nro_registro_libro_grado'];
	$rpnp                              = $_REQUEST['rpnp'];
	$examen_anual_1                    = $_REQUEST['examen_anual_1'];
	$examen_anual_2                    = $_REQUEST['examen_anual_2'];
	
	$fecha_inicio_programa             = ($fecha_inicio_programa<>"") ? "'$fecha_inicio_programa'::date" : "null";
	$fecha_egreso                      = ($fecha_egreso<>"") ? "'$fecha_egreso'::date" : "null";
	$semestre_egreso                   = ($semestre_egreso<>"") ? "'$semestre_egreso'" : "null";
	$ano_egreso                        = ($ano_egreso<>"") ? "'$ano_egreso'" : "null";
	$salida_int_fecha                  = ($salida_int_fecha<>"") ? "'$salida_int_fecha'::date" : "null";
	$salida_int_calif                  = ($salida_int_calif<>"") ? "'$salida_int_calif'::numeric(3,1)" : "null";
	$salida_int_nroreg_libro           = ($salida_int_nroreg_libro<>"") ? "'$salida_int_nroreg_libro'::int2" : "null";
	$examen_grado_titulo_fecha         = ($examen_grado_titulo_fecha<>"") ? "'$examen_grado_titulo_fecha'::date" : "null";
	$examen_grado_titulo_calif         = ($examen_grado_titulo_calif<>"") ? "'$examen_grado_titulo_calif'::numeric(3,2)" : "null";
	$examen_grado_titulo_oportunidades = ($examen_grado_titulo_oportunidades<>"") ? "'$examen_grado_titulo_oportunidades'::int2" : "null";
	$examen_titulo_fecha               = ($examen_titulo_fecha<>"") ? "'$examen_titulo_fecha'::date" : "null";
	$examen_titulo_calif               = ($examen_titulo_calif<>"") ? "'$examen_titulo_calif'::numeric(3,2)" : "null";
	$examen_titulo_oportunidades       = ($examen_titulo_oportunidades<>"") ? "'$examen_titulo_oportunidades'::int2" : "null";
	$fecha_titulacion                  = ($fecha_titulacion<>"") ? "'$fecha_titulacion'::date" : "null";
	$nota_titulacion                   = ($nota_titulacion<>"") ? "'$nota_titulacion'::numeric(3,1)" : "null";
	$nro_registro_libro_tit            = ($nro_registro_libro_tit<>"") ? "'$nro_registro_libro_tit'::int2" : "null";
	$fecha_graduacion                  = ($fecha_graduacion<>"") ? "'$fecha_graduacion'::date" : "null";
	$nota_graduacion                   = ($nota_graduacion<>"") ? "'$nota_graduacion'::numeric(3,1)" : "null";
	$nro_registro_libro_grado          = ($nro_registro_libro_grado<>"") ? "'$nro_registro_libro_grado'::int2" : "null";
	$SQL_update = "UPDATE alumnos
	               SET fecha_inicio_programa=$fecha_inicio_programa,
	                   fecha_egreso=$fecha_egreso,
	                   ano_egreso=$ano_egreso,
	                   semestre_egreso=$semestre_egreso,
	                   salida_int_fecha=$salida_int_fecha,
	                   salida_int_calif=$salida_int_calif,
	                   salida_int_nroreg_libro=$salida_int_nroreg_libro,
	                   examen_grado_titulo_fecha=$examen_grado_titulo_fecha,
	                   examen_grado_titulo_calif=$examen_grado_titulo_calif,
	                   examen_grado_titulo_oportunidades=$examen_grado_titulo_oportunidades,
	                   examen_titulo_fecha=$examen_titulo_fecha,
	                   examen_titulo_calif=$examen_titulo_calif,
	                   examen_titulo_oportunidades=$examen_titulo_oportunidades,
	                   fecha_titulacion=$fecha_titulacion,
	                   nota_titulacion=$nota_titulacion,
	                   nro_registro_libro_tit=$nro_registro_libro_tit,
	                   fecha_graduacion=$fecha_graduacion,
	                   nota_graduacion=$nota_graduacion,
	                   nro_registro_libro_grado=$nro_registro_libro_grado,
	                   rpnp='$rpnp'
	               WHERE id=$id_alumno";
	$alumno = consulta_dml($SQL_update);
	if ($alumno == 1) {
		if ($regimen == "POST-GD") {
			$examen_anual_1 = ($examen_anual_1<>"") ? $examen_anual_1 : "null";
			$examen_anual_2 = ($examen_anual_2<>"") ? $examen_anual_2 : "null";
			
			$exam_final = consulta_dml("UPDATE alumnos_examen_final_postgrado SET examen_anual_1=$examen_anual_1,examen_anual_2=$examen_anual_2 WHERE id_alumno=$id_alumno");
			if ($exam_final == 0) {
				consulta_dml("INSERT INTO alumnos_examen_final_postgrado VALUES ($id_alumno,$examen_anual_1,$examen_anual_2)");
			}
		}
		$nombre_malla = $malla[0]['nombre_malla'];
		echo(msje_js("Se han guardado los cambios"));
	} else {
		echo(msje_js("ERROR: No se han guardado los cambios."."\\n\\n"."Por favor revise los formatos de los datos ingresados."));
	}
	echo(js("window.location='$enlbase=ver_alumno&id_alumno=$id_alumno';"));
	exit;
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
  <tr><td class="celdaNombreAttr" style="text-align: center" colspan="4">Antecedentes de Egreso/Titulación/Graduación</td></tr>
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
    <td class="celdaNombreAttr">Fecha de Inicio:</td>
    <td class="celdaValorAttr" colspan="3"><input class="boton" type='date' size='10' name='fecha_inicio_programa' value='<?php echo($fecha_inicio_programa); ?>'</td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Fecha de Egreso:</td>
    <td class="celdaValorAttr"><input class="boton" type='date' size='10' name='fecha_egreso' value='<?php echo($fecha_egreso); ?>'></td>
    <td class="celdaNombreAttr">Periodo de Egreso:</td>
    <td class="celdaValorAttr">
      <select name="semestre_egreso" class="filtro">
        <option value="">-- Semestre --</option>
        <?php echo(select($semestres,$semestre_egreso)); ?>
      </select> - 
      <select name="ano_egreso" class="filtro">
        <option value="">-- Año --</option>
        <?php echo(select($anos,$ano_egreso)); ?>
      </select>
    </td>
  </tr>
  <tr><td class="celdaNombreAttr" style="text-align: center" colspan="4">Salida Intermedia</td></tr>
  <tr>
    <td class="celdaValorAttr" colspan="4" style='text-align: center'>
      <b>Fecha:</b> <input class="boton" type='date' size='10' name='salida_int_fecha' value='<?php echo($salida_int_fecha); ?>'>&nbsp;&nbsp;
      <b>Calificación:</b> <input class="boton" type='text' size='3' name='salida_int_calif' value='<?php echo($salida_int_calif); ?>'>&nbsp;&nbsp;
      <b>N° Registro:</b> <input class="boton" type='text' size='1' name='salida_int_nroreg_libro' value='<?php echo($salida_int_nroreg_libro); ?>'><br>
      <sup>Separación decimal: punto,<br>ej.: 6.0 (seis punto cero)</sup><br><br>
    </td>
  </tr>
  <tr><td class="celdaNombreAttr" style="text-align: center" colspan="4">Examen de Grado</td></tr>
  <tr>
    <td class="celdaValorAttr" colspan="4" style='text-align: center'>
      <b>Fecha:</b> <input class="boton" type='date' size='10' name='examen_grado_titulo_fecha' value='<?php echo($examen_grado_titulo_fecha); ?>'>&nbsp;&nbsp;
      <b>Calificación:</b> <input class="boton" type='text' size='3' name='examen_grado_titulo_calif' value='<?php echo($examen_grado_titulo_calif); ?>'>&nbsp;&nbsp;
      <b>Oportunidad(es):</b> <input class="boton" type='text' size='1' name='examen_grado_titulo_oportunidades' value='<?php echo($examen_grado_titulo_oportunidades); ?>'><br>
      <sup>Separación decimal: punto,<br>ej.: 6.0 (seis punto cero)</sup><br><br>
    </td>
  </tr>
  <tr><td class="celdaNombreAttr" style="text-align: center" colspan="4">Examen de Título</td></tr>
  <tr>
    <td class="celdaValorAttr" colspan="4" style='text-align: center'>
      <b>Fecha:</b> <input class="boton" type='date' size='10' name='examen_titulo_fecha' value='<?php echo($examen_titulo_fecha); ?>'>&nbsp;&nbsp;
      <b>Calificación:</b> <input class="boton" type='text' size='3' name='examen_titulo_calif' value='<?php echo($examen_titulo_calif); ?>'>&nbsp;&nbsp;
      <b>Oportunidad(es):</b> <input class="boton" type='text' size='1' name='examen_titulo_oportunidades' value='<?php echo($examen_titulo_oportunidades); ?>'><br>
      <sup>Separación decimal: punto,<br>ej.: 6.0 (seis punto cero)</sup><br><br>
    </td>
  </tr>
  <tr><td class="celdaNombreAttr" style="text-align: center" colspan="4">Titulación</td></tr>
  <tr>
    <td class="celdaValorAttr" colspan="4" style='text-align: center'>
      <b>Fecha:</b> <input class="boton" type='date' size='10' name='fecha_titulacion' value='<?php echo($fecha_titulacion); ?>'>&nbsp;&nbsp;
      <b>Calificación:</b> <input class="boton" type='text' size='3' name='nota_titulacion' value='<?php echo($nota_titulacion); ?>'>&nbsp;&nbsp;&nbsp;
      <b>N° Registro:</b> <input class="boton" type='text' size='3' name='nro_registro_libro_tit' value='<?php echo($nro_registro_libro_tit); ?>'><br>
      <sup>Separación decimal: punto,<br>ej.: 6.0 (seis punto cero)</sup><br><br>
    </td>
  </tr>
  <tr><td class="celdaNombreAttr" style="text-align: center" colspan="4">Graduación</td></tr>
  <tr>
    <td class="celdaValorAttr" colspan="4" style='text-align: center'>
      <b>Fecha:</b> <input class="boton" type='date' size='10' name='fecha_graduacion' value='<?php echo($fecha_graduacion); ?>'>&nbsp;&nbsp;
      <b>Calificación:</b> <input class="boton" type='text' size='3' name='nota_graduacion' value='<?php echo($nota_graduacion); ?>'>&nbsp;&nbsp;&nbsp;
      <b>N° Registro:</b> <input class="boton" type='text' size='3' name='nro_registro_libro_grado' value='<?php echo($nro_registro_libro_grado); ?>'><br>
      <sup>Separación decimal: punto,<br>ej.: 6.0 (seis punto cero)</sup><br><br>
    </td>
  </tr>
<?php if ($regimen == "POST-GD" || $regimen == "POST-TD") { ?>
  <tr><td class="celdaNombreAttr" style="text-align: center" colspan="4">Antecedentes Postgrado a Distancia</td></tr>
  <tr>
    <td class="celdaNombreAttr">Calificación de Examen Anual 1:</td>
    <td class="celdaValorAttr"><input class="boton" type='text' size='3' name='examen_anual_1' value='<?php echo($examen_anual_1); ?>'><br><sup>Separación decimal: punto, ej.: 6.0 (seis punto cero)</sup></td>
    <td class="celdaNombreAttr">Calificación de Examen Anual 2:</td>
    <td class="celdaValorAttr"><input class="boton" type='text' size='3' name='examen_anual_2' value='<?php echo($examen_anual_2); ?>'><br><sup>Separación decimal: punto, ej.: 6.0 (seis punto cero)</sup></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">N° RPNP:</td>
    <td class="celdaValorAttr"><input class="boton" type='text' size='10' name='rpnp' value='<?php echo($rpnp); ?>'><br><sup>Formato: NN-NNNN</sup></td>
    <td class="celdaNombreAttr">Fecha de Inicio:</td>
    <td class="celdaValorAttr"><input class="boton" type='text' size='10' name='fecha_inicio_programa' value='<?php echo($fecha_inicio_programa); ?>'><br><sup>Formato: DD-MM-AAAA</sup></td>
  </tr>
<?php } ?>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

