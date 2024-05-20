<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_curso = $_REQUEST['id_curso'];
if (!is_numeric($id_curso)) {
	echo(js("location.href='principal.php?modulo=gestion_cursos';"));
	exit;
}

$SQL_curso = "SELECT vc.id,vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura AS asignatura,vc.semestre as sem_num,
                     CASE vc.semestre
                          WHEN 0 THEN 'Verano'
                          WHEN 1 THEN 'Primero'
                          WHEN 2 THEN 'Segundo'
                     END AS sem,vc.semestre,vc.ano,vc.profesor,vc.carrera,vc.id_profesor,
                     coalesce(vc.sesion1,'')||' '||coalesce(vc.sesion2,'')||' '||coalesce(vc.sesion3,'') as horario,
                     vc.id_prog_asig,cantidad_alumnos(vc.id) AS cant_alumnos,cant_alumnos_asist(vc.id) AS cant_alumnos_asist,
                     pa.ano AS ano_prog_asig,m.ano AS ano_malla,c.cerrado,vc.dia1,vc.dia2,vc.dia3,vc.seccion
              FROM vista_cursos AS vc
              LEFT JOIN prog_asig AS pa ON pa.id=vc.id_prog_asig
              LEFT JOIN detalle_mallas AS dm ON dm.id_prog_asig=vc.id_prog_asig
              LEFT JOIN mallas AS m ON m.id=dm.id_malla
              LEFT JOIN cursos AS c ON c.id=vc.id 
              WHERE vc.id=$id_curso;";
$curso = consulta_sql($SQL_curso);
           
if (count($curso) == 0) {
	echo(js("location.href='principal.php?modulo=gestion_cursos';"));
	exit;
}

extract($curso[0]);

$prog_asig = "<a class='enlaces' href='$enlbase=ver_prog_asig&id_prog_asig=$id_prog_asig'><small>Ver programa</small></a>";
$ficha_prof = "<a class='enlaces' href='$enlbase=ver_profesor&id_profesor=$id_profesor'><small>Ver ficha</small></a>";

if ($_REQUEST['guardar'] == "Guardar" || $_REQUEST['guardar_cerrar'] == "Guardar y cerrar") {
	$aId_cal       = $_REQUEST['id_cal'];
	$aMateria      = $_REQUEST['materia'];
	$aMetodologias = $_REQUEST['metodologias'];
	$aBibliografia = $_REQUEST['bibliografia'];
	
	$cal = array();
	
	for($x=0;$x<count($aId_cal);$x++) {
		$id_cal = $aId_cal[$x];
		$cal_aux = array(array("id_cal"       => $id_cal,
		                       "materia"      => pg_escape_string($aMateria[$id_cal]),
		                       "metodologias" => pg_escape_string($aMetodologias[$id_cal]),
		                       "bibliografia" => pg_escape_string($aBibliografia[$id_cal])
		                      )
		                 );
		$cal = array_merge($cal,$cal_aux);
	}
	
	$SQLUPD_cal = "";
	//echo($SQLUPD_cal);
	for ($x=0;$x<count($aId_cal);$x++) {
		$SQLUPD_cal .= "UPDATE calendarizaciones SET " . arr2sqlupd($cal[$x]) . " WHERE id={$cal[$x]['id_cal']};";
	}
	if (consulta_dml($SQLUPD_cal) > 0) {
		echo(msje_js("Se han guardado los cambios exitósamente"));
	} else {
		echo(msje_js("Ha ocurrido un problema. Por favor intente nuevamente guardar los cambios"));
	}
}

if ($_REQUEST['guardar_cerrar'] == "Guardar y cerrar") {
	echo(js("location.href='$enlbase_sm=cursos_calendarizacion&id_curso=$id_curso';"));
	exit;		
}

$SQL_calendarizacion = "SELECT id,sesion,fecha,materia,metodologias,bibliografia,susp FROM calendarizaciones WHERE id_curso=$id_curso ORDER BY sesion";
$calendarizacion = consulta_sql($SQL_calendarizacion);

if (count($calendarizacion) == 0) {
	$SQL_prog_asig = "SELECT horas_semanal FROM prog_asig WHERE id=$id_prog_asig";
	$prog_asig = consulta_sql($SQL_prog_asig);
	extract($prog_asig[0]);
	$sesiones = (18 * $horas_semanal)/2;
	
	if ($semestre == 1 ) { $fecha = $Fec_Ini_Sem1; } elseif ($semestre == 2) { $fecha = $Fec_Ini_Sem2; }
	
	$SQLINS_cal = "";
	$sesion = 1;
	$ANO_cal = $ANO - 1;
	$SQL_cal_ant = "SELECT sesion,materia,bibliografia FROM calendarizaciones WHERE not susp AND id_curso=(SELECT id FROM cursos WHERE id_prog_asig=$id_prog_asig AND seccion=$seccion and semestre=$SEMESTRE and ano=$ANO_cal)";
	while ($sesion <= $sesiones) {
		$fec = strftime("%Y-%m-%d",$fecha);
		if (($dia1>0 || $dia2>0 || $dia3>0) && strftime("%u",$fecha) <> 7) {
			if (strftime("%u",$fecha) == $dia1) {
				$SQLINS_cal .= "INSERT INTO calendarizaciones (id_curso,sesion,fecha) VALUES ($id_curso,$sesion,'$fec'::date);";
				$sesion++;
			}
			if (strftime("%u",$fecha) == $dia2) {
				$SQLINS_cal .= "INSERT INTO calendarizaciones (id_curso,sesion,fecha) VALUES ($id_curso,$sesion,'$fec'::date);";
				$sesion++;
			}
			if (strftime("%u",$fecha) == $dia3) {
				$SQLINS_cal .= "INSERT INTO calendarizaciones (id_curso,sesion,fecha) VALUES ($id_curso,$sesion,'$fec'::date);";
				$sesion++;
			}
		} else {
			
		}
		$fecha = strtotime("$fec + 1 days");		
	}
	if (consulta_dml($SQLINS_cal) > 0) {
		$SQLUPD_cal = "UPDATE calendarizaciones 
					   SET materia='** '||susp_clases.motivo,susp=true
					   FROM susp_clases 
					   WHERE id_curso=$id_curso AND calendarizaciones.fecha=susp_clases.fecha AND semestre=$SEMESTRE AND ano=$ANO";
		consulta_dml($SQLUPD_cal);
		echo(msje_js("Se ha creado la plantilla de calendarización y se han marcado los días feriados correspondientes"));
	}
	if ($_REQUEST['copiar_ant'] == "si") {
		$SQL_copiar = "UPDATE calendarizaciones SET materia=cal.materia,bibliografia=cal.bibliografia FROM ($SQL_cal_ant) AS cal WHERE id_curso=$id_curso AND calendarizaciones.sesion=cal.sesion AND not susp";
		//echo($SQL_copiar);
		if (consulta_dml($SQL_copiar) > 0) {
			echo(msje_js("Se ha copiado la calendarización anterior satisfactoriamente"));
		}
	}
	$calendarizacion = consulta_sql($SQL_calendarizacion);
}

$HTML_calendarizacion = "";	
for ($x=0;$x<count($calendarizacion);$x++) {
	extract($calendarizacion[$x]);
	$fecha = ucfirst(strftime("%A %d<br>de %B",strtotime($fecha)));
	$readonly = "";
	if ($susp == "t") { $readonly = "readonly"; }
	$HTML_calendarizacion .= "<tr class='filaTabla'>"
	                      .  "<input type='hidden' name='id_cal[]' value='$id'>"
						  .  "  <td class='textoTabla' align='center'>".$sesion."ª</td>"
						  //.  "  <td class='textoTabla'>$fecha</td>"
						  .  "  <td class='textoTabla'><textarea class='general' cols='50' rows='3' name='materia[$id]' $readonly>$materia</textarea></td>"
						  .  "  <td class='textoTabla'><textarea class='general' cols='50' rows='3' name='metodologias[$id]' $readonly>$metodologias</textarea></td>"
						  .  "  <td class='textoTabla'><textarea class='general' cols='30' rows='3' name='bibliografia[$id]' $readonly>$bibliografia</textarea></td>"
						  .  "</tr>";
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<table width='100%'>
  <tr>
    <td width='60%'><div style='overflow: auto; height: 550px'>
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal_sm.php" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_curso" value="<?php echo($id_curso); ?>">
<div style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Guardar">
  <input type="submit" name="guardar_cerrar" value="Guardar y cerrar">
  <input type="button" onClick="history.back();" value="Volver">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr'>Nº Acta:</td>
    <td class='celdaValorAttr'><?php echo($id); ?></td>
    <td class='celdaNombreAttr'>Periodo:</td>
    <td class='celdaValorAttr'><?php echo($sem_num."-".$ano); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Asignatura:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($asignatura); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($carrera); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Profesor:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($profesor); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Horario:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($horario); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Inscrito(a)s:</td>
    <td class='celdaValorAttr'><?php echo($cant_alumnos); ?> alumno(a)s</td>
    <td class='celdaNombreAttr'>Asistentes:</td>
    <td class='celdaValorAttr'><?php echo($cant_alumnos_asist); ?> alumno(a)s</td>
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Sesión</td>
    <!-- <td class='tituloTabla'>Fecha</td> -->
    <td class='tituloTabla'>Contenidos</td>
    <td class='tituloTabla'>Metodología(s)</td>
    <td class='tituloTabla'>Bibliografía</td>    
  </tr>
  <?php echo($HTML_calendarizacion); ?>
</table>
</form>
      </div>
    </td>
    <td width='25%' valign='top'><div style='overflow: auto; height: 550px'><?php include("ver_prog_asig_contenido.php"); ?></div></td>
  </tr>
</table>

<!-- Fin: <?php echo($modulo); ?> -->

<?php
function arr2sqlupd($aTabla) {
	$arr2sqlupd = "";
	$aCampos = array_keys($aTabla);
	for($x=1;$x<count($aCampos);$x++) {
		if ($aTabla[$aCampos[$x]] == "") {
			$aTabla[$aCampos[$x]] = "null";
		} else {
			$aTabla[$aCampos[$x]] = "'{$aTabla[$aCampos[$x]]}'";
		}
		$arr2sqlupd .= "{$aCampos[$x]}={$aTabla[$aCampos[$x]]},";
	}
	return substr($arr2sqlupd,0,-1);
}
?>
