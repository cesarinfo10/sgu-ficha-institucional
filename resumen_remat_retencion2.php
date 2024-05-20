<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");
include("validar_modulo.php");

$ano_remat      = $_REQUEST['ano_remat'];
$semestre_remat = $_REQUEST['semestre_remat'];
//$regimen      = $_REQUEST['regimen'];
$id_carrera     = $_REQUEST['id_carrera'];
$jornada        = $_REQUEST['jornada'];

if (empty($ano_remat)) { $ano_remat = $ANO_MATRICULA; }
if (empty($semestre_remat)) { $semestre_remat = $SEMESTRE_MATRICULA; }
if (!empty($ids_carreras) && empty($_REQUEST['regimen'])) { $regimen = "t"; }

$aAdmision_agrupadores = array_column(consulta_sql("SELECT DISTINCT ON (agrupador::text) agrupador::text FROM admision_tipo ORDER BY agrupador::text"),"agrupador");
//var_dump($aAdmision_agrupadores);
$aClasificadores = array("Matrícula SIES","Desertores","Rematriculados","Contactados Desertores","Contactados Comprometidos",'No contactados','Retención Actual','Retención Esperada','Retención Máxima');

$condiciones = "";
if ($regimen <> "") { $condiciones .= " AND c.regimen='$regimen' "; }
if ($ids_carreras <> "") { $condiciones .= " AND a.carrera_actual IN ($ids_carreras) "; }
if ($id_carrera > 0) { $condiciones .= " AND a.carrera_actual=$id_carrera "; }
if ($jornada <> "") { $condiciones .= " AND a.jornada='$jornada' "; }

$aCuadro_resumen = $aResumen = $aSQL_resumen = $aCohortes = array();
for ($ano_cohorte=$ano_remat;$ano_cohorte>=$ano_remat-4;$ano_cohorte--) { 
	$aCohortes[] = $ano_cohorte;
	$aSQL_resumen[$ano_cohorte] = sql_mat_sies($ano_cohorte,$semestre_remat)
	                            . " UNION ALL "
								. sql_desertores($ano_cohorte,$semestre_remat)
								. " UNION ALL "
								. sql_remat($ano_cohorte,$semestre_remat)
								. " UNION ALL "
								. sql_posible_desert($ano_cohorte,$semestre_remat)
								. " UNION ALL "
								. sql_comprometidos_remat($ano_cohorte,$semestre_remat)
								. " UNION ALL "
								. sql_no_contactados($ano_cohorte,$semestre_remat);
	$aResumen[$ano_cohorte]	= consulta_sql($aSQL_resumen[$ano_cohorte]);
	foreach($aClasificadores AS $Clasificador) {
		foreach($aAdmision_agrupadores AS $Admision_agrupador) {
			for ($x=0;$x<count($aResumen[$ano_cohorte]);$x++) {
				$aCuadro_resumen[$ano_cohorte][$Admision_agrupador][$Clasificador] = 0;
				if ($Clasificador == $aResumen[$ano_cohorte][$x]['clasif'] && $Admision_agrupador == $aResumen[$ano_cohorte][$x]['admision']) {
					$aCuadro_resumen[$ano_cohorte][$Admision_agrupador][$Clasificador] = intval($aResumen[$ano_cohorte][$x]['cant_estudiantes']);
					break;
				}
			}
		}
	}	
}

//var_dump($aCuadro_resumen);

foreach($aCuadro_resumen AS $cohorte => $cuadro_cohorte) {
	foreach($aClasificadores AS $clasificador) {
		$aCuadro_resumen[$cohorte]['Total'][$clasificador] = array_sum(array_column($cuadro_cohorte,$clasificador));		
	}
}

$estilo_rojo     = "background: red; border-radius: 2px; color: white; padding: 0px 2px";
$estilo_amarillo = "background: orange; border-radius: 2px; color: black; padding: 0px 2px";
$estilo_verde    = "background: green; border-radius: 2px; color: white; padding: 0px 2px";

foreach($aCuadro_resumen AS $cohorte => $cuadro_cohorte) {
	foreach($cuadro_cohorte AS $agrupador_admision => $detalle_agrupador) {
		//var_dump($detalle_agrupador);

		$aCuadro_resumen[$cohorte][$agrupador_admision]['Retención Actual'] = "-";
		if ($aCuadro_resumen[$cohorte][$agrupador_admision]['Matrícula SIES'] > 0) {

			$aCuadro_resumen[$cohorte][$agrupador_admision]['Retención Actual'] = $aCuadro_resumen[$cohorte][$agrupador_admision]['Rematriculados'] 
																				/ $aCuadro_resumen[$cohorte][$agrupador_admision]['Matrícula SIES'];

			if ($aCuadro_resumen[$cohorte][$agrupador_admision]['Retención Actual'] < 0.65) { $estilo = $estilo_rojo; }
			if ($aCuadro_resumen[$cohorte][$agrupador_admision]['Retención Actual'] >= 0.65) { $estilo = $estilo_amarillo; }
			if ($aCuadro_resumen[$cohorte][$agrupador_admision]['Retención Actual'] >= 0.75) { $estilo = $estilo_verde; }

			$aCuadro_resumen[$cohorte][$agrupador_admision]['Retención Actual'] = "<span style='$estilo'>"
			                                                                    . number_format($aCuadro_resumen[$cohorte][$agrupador_admision]['Retención Actual']*100,1,",",".")."%"
																				. "</span>";
		}

		$aCuadro_resumen[$cohorte][$agrupador_admision]['Retención Esperada'] = "-";
		if ($aCuadro_resumen[$cohorte][$agrupador_admision]['Matrícula SIES'] > 0) {

			$aCuadro_resumen[$cohorte][$agrupador_admision]['Retención Esperada'] = ($aCuadro_resumen[$cohorte][$agrupador_admision]['Rematriculados'] 
																				+  $aCuadro_resumen[$cohorte][$agrupador_admision]['Contactados Comprometidos']) 
																				/ $aCuadro_resumen[$cohorte][$agrupador_admision]['Matrícula SIES'];

			if ($aCuadro_resumen[$cohorte][$agrupador_admision]['Retención Esperada'] < 0.65) { $estilo = $estilo_rojo; }
			if ($aCuadro_resumen[$cohorte][$agrupador_admision]['Retención Esperada'] >= 0.65) { $estilo = $estilo_amarillo; }
			if ($aCuadro_resumen[$cohorte][$agrupador_admision]['Retención Esperada'] >= 0.75) { $estilo = $estilo_verde; }
																	
			$aCuadro_resumen[$cohorte][$agrupador_admision]['Retención Esperada'] = "<span style='$estilo'>"
			                                                                      . number_format($aCuadro_resumen[$cohorte][$agrupador_admision]['Retención Esperada']*100,1,",",".")."%"
																				  . "</span>";
		}

		$aCuadro_resumen[$cohorte][$agrupador_admision]['Retención Máxima'] = "-";
		if ($aCuadro_resumen[$cohorte][$agrupador_admision]['Matrícula SIES'] > 0) {

			$aCuadro_resumen[$cohorte][$agrupador_admision]['Retención Máxima']   = ($aCuadro_resumen[$cohorte][$agrupador_admision]['Matrícula SIES'] 
																				-  $aCuadro_resumen[$cohorte][$agrupador_admision]['Desertores'] 
																				-  $aCuadro_resumen[$cohorte][$agrupador_admision]['Contactados Desertores']) 
																				/ $aCuadro_resumen[$cohorte][$agrupador_admision]['Matrícula SIES'];

			if ($aCuadro_resumen[$cohorte][$agrupador_admision]['Retención Máxima'] < 0.65) { $estilo = $estilo_rojo; }
			if ($aCuadro_resumen[$cohorte][$agrupador_admision]['Retención Máxima'] >= 0.65) { $estilo = $estilo_amarillo; }
			if ($aCuadro_resumen[$cohorte][$agrupador_admision]['Retención Máxima'] >= 0.75) { $estilo = $estilo_verde; }
																	
			$aCuadro_resumen[$cohorte][$agrupador_admision]['Retención Máxima']   = "<span style='$estilo'>"
			                                                                      . number_format($aCuadro_resumen[$cohorte][$agrupador_admision]['Retención Máxima']*100,1,",",".")."%"
																				  . "</span>";
		}
	}
}


//var_dump($aCuadro_resumen);
$cant_clasif = count($aClasificadores) + 1;

$HTML = "<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>";
foreach($aCuadro_resumen AS $cohorte => $cuadro_cohorte) {
	$HTML .= "<tr class='filaTituloTabla'>"
	      .  "  <td class='tituloTabla' colspan='$cant_clasif'>Cohorte $cohorte (Proceso Rematricula $ano_remat)</td>"
		  .  "</tr>"
		  .  "<tr class='filaTituloTabla'>"
	      .  "  <td class='tituloTabla'>Tipo Admisión</td>";
	foreach($aClasificadores AS $Clasificador) { $HTML .=  "<td class='tituloTabla'>".str_replace(" ","<br>",$Clasificador)."</td>"; }
	$HTML .= "</tr>";
	foreach($cuadro_cohorte AS $agrupador_admision => $detalle_agrupador) {
		$clase1_css = "tituloTabla";
		$clase2_css = "textoTabla";
		$estilo     = "";
		if ($agrupador_admision == "Total") { $clase1_css = $clase2_css = "celdaNombreAttr"; }
		if ($agrupador_admision == "1. Primer año") { $estilo = "background: yellow"; }

		$HTML .= "<tr class='filaTabla'>"
		      .  "  <td class='$clase1_css' style='text-align: left'>$agrupador_admision</td>";
		foreach($detalle_agrupador AS $clasificador => $valor) { $HTML .= "<td class='$clase2_css' style='text-align: center; $estilo'>$valor</td>"; }
		$HTML .= "</tr>";
	}
	$HTML .= "</table><table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>";
}
$HTML .= "</table>";

$SEMESTRES_COHORTES = array(array("id"=>1,"nombre"=>1),
                            array("id"=>2,"nombre"=>2));

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }
if (!empty($ids_carreras) && empty($_REQUEST['regimen'])) { $regimen = "t"; }

$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "" && $regimen <> "t") { $cond_carreras .= "AND regimen='$regimen' "; }

$SQL_carreras = "SELECT id,nombre||' ('||alias||')' AS nombre FROM carreras $cond_carreras AND activa ORDER BY nombre;";
$carreras = consulta_sql($SQL_carreras);

$SQL_carreras_novig = "SELECT id,nombre||' ('||alias||')' AS nombre FROM carreras $cond_carreras AND NOT activa ORDER BY nombre;";
$carreras_novig = consulta_sql($SQL_carreras_novig);

?>

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
  <tr>
    <td class="celdaFiltro">
      <div align='left'>Periodo matrícula:</div>
      <select class="filtro" name="semestre_remat" onChange="submitform();">
        <option value=""></option>
        <?php echo(select($SEMESTRES_COHORTES,$semestre_remat)); ?>    
      </select>
      - 
      <select class="filtro" name="ano_remat" onChange="submitform();">
        <?php echo(select($anos,$ano_remat)); ?>
      </select>
    </td> 
	<td class="celdaFiltro">
	  Carrera/Programa:<br>
	  <select class="filtro" name="id_carrera" onChange="submitform();">
		<option value="">Todas</option>
		<optgroup label='Vigentes'>
		  <?php echo(select($carreras,$id_carrera)); ?>
		</optgroup>
		<optgroup label='No vigentes'>
		  <?php echo(select($carreras_novig,$id_carrera)); ?>
		</optgroup>
	  </select>
	</td>
	<td class="celdaFiltro">
	  Jornada:<br>
	  <select class="filtro" name="jornada" onChange="submitform();">
	    <option value="">Ambas</option>
		<?php echo(select($JORNADAS,$jornada)); ?>
	  </select>
	</td>
<!--	<td class="celdaFiltro">
	  Régimen: <br>
	  <select class="filtro" name="regimen" onChange="submitform();">
		<option value="t">Todos</option>
		<?php echo(select($REGIMENES,$regimen)); ?>
	  </select>
	</td> -->
  </tr>
</table>
</form>
<?php echo($HTML); ?>

<?php

function sql_mat_sies($cohorte,$semestre_remat) {
	global $ano_remat,$condiciones;

	$SQL = "SELECT 'Matrícula SIES' AS clasif,at.agrupador AS admision,count(a.id) AS cant_estudiantes
			FROM alumnos            AS a
			LEFT JOIN carreras      AS c  ON c.id=a.carrera_actual
			LEFT JOIN admision_tipo AS at ON at.id=a.admision
			WHERE cohorte=$cohorte
			  $condiciones
			  AND rut IN (SELECT rut FROM alumnos_sies WHERE regimen=c.regimen AND ano=a.cohorte)
			GROUP BY at.agrupador";
	return $SQL;
}

function sql_desertores($cohorte,$semestre_remat) {
	global $ano_remat,$condiciones;

    $SQL = "SELECT 'Desertores' AS clasif,at.agrupador AS admision,count(a.id) AS cant_estudiantes
		    FROM alumnos            AS a
		    LEFT JOIN carreras      AS c ON c.id=a.carrera_actual
		    LEFT JOIN admision_tipo AS at ON at.id=a.admision
		    LEFT JOIN al_estados    AS ae ON ae.id=a.estado
		    WHERE cohorte=$cohorte  AND ae.nombre IN ('Retirado','Suspendido','Abandono')
			  $condiciones
			  AND rut IN (SELECT rut FROM alumnos_sies WHERE regimen=c.regimen AND ano=a.cohorte)
		    GROUP BY at.agrupador";
	return $SQL;
}

function sql_remat($cohorte,$semestre_remat) {
	global $ano_remat,$condiciones;

    $SQL = "SELECT 'Rematriculados' AS clasif,at.agrupador AS admision,count(a.id) AS cant_estudiantes
			FROM alumnos            AS a
			LEFT JOIN carreras      AS c ON c.id=a.carrera_actual
			LEFT JOIN admision_tipo AS at ON at.id=a.admision
			LEFT JOIN al_estados    AS ae ON ae.id=a.estado
			WHERE cohorte=$cohorte  AND ae.nombre = 'Vigente'
			  $condiciones
			  AND a.rut IN (SELECT rut FROM alumnos_sies WHERE regimen=c.regimen AND ano=a.cohorte)
			  AND a.id IN (SELECT id_alumno FROM matriculas WHERE ano=$ano_remat AND semestre=$semestre_remat)
			GROUP BY at.agrupador";
	return $SQL;
}

function sql_posible_desert($cohorte,$semestre_remat) {
	global $ano_remat,$condiciones;

    $SQL = "SELECT 'Contactados Desertores' AS clasif,at.agrupador AS admision,count(a.id) AS cant_estudiantes
			FROM alumnos            AS a
			LEFT JOIN carreras      AS c ON c.id=a.carrera_actual
			LEFT JOIN admision_tipo AS at ON at.id=a.admision
			LEFT JOIN al_estados    AS ae ON ae.id=a.estado
			WHERE cohorte=$cohorte
			  $condiciones
			  AND a.rut IN (SELECT rut FROM alumnos_sies WHERE regimen=c.regimen AND ano=a.cohorte)
			  AND a.id NOT IN (SELECT id_alumno FROM matriculas WHERE ano=$ano_remat)
			  AND ae.nombre NOT IN ('Suspendido','Retirado','Abandono')
			  AND a.id IN (SELECT id_alumno FROM gestion.atenciones_remat WHERE id_alumno=a.id AND id_motivo_no_remat IS NOT NULL AND ano_mat=$ano_remat ORDER BY fecha DESC LIMIT 1)
			GROUP BY at.agrupador";
	return $SQL;
}

function sql_comprometidos_remat($cohorte,$semestre_remat) {
	global $ano_remat,$condiciones;

	$SQL = "SELECT 'Contactados Comprometidos' AS clasif,at.agrupador AS admision,count(a.id) AS cant_estudiantes
			FROM alumnos            AS a
			LEFT JOIN carreras      AS c ON c.id=a.carrera_actual
			LEFT JOIN admision_tipo AS at ON at.id=a.admision
			WHERE cohorte=$cohorte
			  $condiciones
		      AND a.rut IN (SELECT rut FROM alumnos_sies WHERE regimen=c.regimen AND ano=a.cohorte)
			  AND a.id NOT IN (SELECT id_alumno FROM matriculas WHERE ano=$ano_remat)
			  AND a.id IN (SELECT id_alumno FROM gestion.atenciones_remat WHERE id_alumno=a.id AND id_motivo_no_remat IS NULL AND fecha_compromiso>=now()::date AND ano_mat=$ano_remat ORDER BY fecha DESC LIMIT 1)
			GROUP BY at.agrupador";
	return $SQL;
}

function sql_no_contactados($cohorte,$semestre_remat) {
	global $ano_remat,$condiciones;

	$SQL = "SELECT 'No contactados' AS clasif,at.agrupador AS admision,count(a.id) AS cant_estudiantes
			FROM alumnos            AS a
			LEFT JOIN carreras      AS c ON c.id=a.carrera_actual
			LEFT JOIN admision_tipo AS at ON at.id=a.admision
			LEFT JOIN al_estados    AS ae ON ae.id=a.estado
			WHERE cohorte=$cohorte
			  $condiciones
		      AND a.rut IN (SELECT rut FROM alumnos_sies WHERE regimen=c.regimen AND ano=a.cohorte)
			  AND a.id NOT IN (SELECT id_alumno FROM matriculas WHERE ano=$ano_remat)
			  AND ae.nombre NOT IN ('Suspendido','Retirado','Abandono')
			  AND a.id NOT IN (SELECT id_alumno FROM gestion.atenciones_remat WHERE (fecha_compromiso>=now()::date OR id_motivo_no_remat IS NOT NULL) AND ano_mat=$ano_remat)
			GROUP BY at.agrupador";
	return $SQL;
}

?>