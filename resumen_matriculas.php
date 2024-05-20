<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");
setlocale(LC_ALL,"es_CL.UTF8");
include("validar_modulo.php");

$ano          = $_REQUEST['ano'];
$semestre     = $_REQUEST['semestre'];
$fecha_corte  = $_REQUEST['fecha_corte'];
$admision     = $_REQUEST['admision'];
$regimen      = $_REQUEST['regimen'];
$meta_estado  = $_REQUEST['meta_estado'];
$meta_regimen = $_REQUEST['meta_regimen'];
$escuela      = $_REQUEST['escuela'];
$jornada      = $_REQUEST['jornada'];

if (empty($ano)) { $ano = $ANO_MATRICULA; }
if (empty($semestre)) { $semestre = $SEMESTRE_MATRICULA; }
if (!empty($ids_carreras) && empty($regimen)) { $regimen = "t"; }
if (empty($fecha_corte)) { $fecha_corte = date("Y-m-d"); }
if (empty($meta_regimen)) { $meta_regimen = "1. Pregrado"; }
if (empty($meta_estado)) { $meta_estado = "Vigente"; }

$cond = $cond_regimen = "";
if ($regimen <> "t" && !empty($regimen)) { $cond .= " AND c.regimen = '$regimen' "; $cond_regimen = "WHERE id='$regimen'"; }

if ($meta_regimen <> "t" && !empty($meta_regimen)) { $cond .= " AND r.agrupador = '$meta_regimen' "; $cond_regimen = "WHERE agrupador='$meta_regimen'"; }

if ($meta_estado <> "t") { $cond .= " AND ae.agrupador = '$meta_estado' "; }

if ($admision <> "") { $cond .= "AND (a.admision IN ('".str_replace(",","','",$admision)."')) ";	}

if ($escuela > 0) { $cond .= " AND c.id_escuela=$escuela "; }

if ($jornada <> "") { $cond .= " AND a.jornada='$jornada' "; }

$aEstados   = array_column(consulta_sql("SELECT DISTINCT ON (agrupador) agrupador AS estado_agrup FROM al_estados"),"estado_agrup");
if (!empty($meta_estado) && $meta_estado <> "t") { $aEstados = array("$meta_estado"); }
$aRegimenes = array_column(consulta_sql("SELECT nombre AS regimen FROM regimenes_ $cond_regimen ORDER BY orden"),"regimen");

$condiciones    = "WHERE m.fecha::date <= '$fecha_corte'::date $cond";
$mat_total      = consulta_sql(sql_matricula($condiciones));
$HTML_mat_total = html_cuadro_mat("Matrícula Total",$mat_total);

$condiciones    = "WHERE cohorte=$ano AND semestre_cohorte=$semestre AND m.fecha::date <= '$fecha_corte'::date $cond";
$mat_nueva      = consulta_sql(sql_matricula($condiciones));
$HTML_mat_nueva = html_cuadro_mat("Matrícula Nueva",$mat_nueva);

$condiciones      = "WHERE cohorte<$ano AND m.fecha::date <= '$fecha_corte'::date $cond";
if ($SEMESTRE_MATRICULA == 2) { 
	$condiciones  = "WHERE (cohorte<$ano OR (cohorte=$ano AND semestre_cohorte=1)) AND m.fecha::date <= '$fecha_corte'::date $cond";
}
$mat_antigua      = consulta_sql(sql_matricula($condiciones));
$HTML_mat_antigua = html_cuadro_mat("Matrícula Antigua",$mat_antigua);

$SEMESTRES = array(array("id"=>1,"nombre"=>1),
                   array("id"=>2,"nombre"=>2));

$REGIMENES    = consulta_sql("SELECT id,nombre,agrupador AS grupo FROM regimenes_ $cond_regimen ORDER BY agrupador,orden");

$META_REGIMENES = consulta_sql("SELECT DISTINCT ON (agrupador) agrupador AS id,agrupador AS nombre FROM regimenes_ ORDER BY id");

if (!empty($ids_carreras) && empty($_REQUEST['regimen'])) { $regimen = "t"; }

$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "" && $regimen <> "t") { $cond_carreras .= "AND regimen='$regimen' "; }

$SQL_carreras = "SELECT id,nombre||' ('||alias||')' AS nombre FROM carreras $cond_carreras AND activa ORDER BY nombre;";
$carreras = consulta_sql($SQL_carreras);

$SQL_carreras_novig = "SELECT id,nombre||' ('||alias||')' AS nombre FROM carreras $cond_carreras AND NOT activa ORDER BY nombre;";
$carreras_novig = consulta_sql($SQL_carreras_novig);

$ESCUELAS = consulta_sql("SELECT id,nombre,CASE WHEN activa THEN ' Vigentes' ELSE 'Cerradas' END AS grupo FROM escuelas ORDER BY grupo,nombre");

$ADMISION = array_merge(array(array('id' => "1,3", 'nombre' => "1er año (Regular + Especial)")),$ADMISION) ;  

$ESTADOS = consulta_sql("SELECT DISTINCT ON (agrupador) agrupador AS id,agrupador AS nombre FROM al_estados");

?>

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
  <tr>
    <td class="celdaFiltro">
      <div align='left'>Periodo:</div>
      <select class="filtro" name="semestre" onChange="submitform();">
        <?php echo(select($SEMESTRES,$semestre)); ?>    
      </select>
      - 
      <select class="filtro" name="ano" onChange="formulario.fecha_corte.value=null;submitform();">
        <?php echo(select($anos,$ano)); ?>
      </select>
    </td>
    <td class="celdaFiltro">
      <div align='left'>Fecha de corte:</div>
      <input type='date' name='fecha_corte' value="<?php echo($fecha_corte); ?>" max="<?php echo(date("Y-m-d")); ?>" class="boton" style="font-size: 8pt" onChange="submitform();">
    </td>
	<td class="celdaFiltro">
	  Escuela/Unidad Academica:<br>
	  <select class="filtro" name="escuela" onChange="submitform();">
		<option value="">Todas</option>
        <?php echo(select_group($ESCUELAS,$escuela)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
	  Jornada:<br>
	  <select class="filtro" name="jornada" onChange="submitform();">
	    <option value="">Ambas</option>
		<?php echo(select($JORNADAS,$jornada)); ?>
	  </select>
	</td>
  </tr>
</table>
<table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
  <tr>
  <td class="celdaFiltro">
	  Admisión: <br>
	  <select class="filtro" name="admision" onChange="submitform();">
		<option value="">Todos</option>
		<?php echo(select($ADMISION,$admision)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
	  Estado: <br>
	  <select class="filtro" name="meta_estado" onChange="submitform();">
		<option value="t">Todos</option>
		<?php echo(select($ESTADOS,$meta_estado)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
	  Meta-régimen: <br>
	  <select class="filtro" name="meta_regimen" onChange="submitform();">
		<option value="t">Todos</option>
		<?php echo(select($META_REGIMENES,$meta_regimen)); ?>
	  </select>
	</td>
	<td class="celdaFiltro">
	  Régimen: <br>
	  <select class="filtro" name="regimen" onChange="submitform();">
		<option value="t">Todos</option>
		<?php echo(select_group($REGIMENES,$regimen)); ?>
	  </select>
	</td>
  </tr>
</table>
</form>
<?php echo($HTML_mat_total); ?>
<?php echo($HTML_mat_nueva); ?>
<?php echo($HTML_mat_antigua); ?>

<?php

function sql_matricula($condiciones) {
    global $ano, $semestre, $fecha_corte;

    $SQL = "SELECT r.orden,r.nombre AS regimen,ae.agrupador AS estado_agrup,count(a.id) AS cant_alumnos
            FROM alumnos AS a 
            LEFT JOIN al_estados AS ae ON ae.id=(CASE WHEN a.estado_fecha IS NULL OR a.estado_fecha::date>'$fecha_corte'::date THEN 1 ELSE a.estado END) 
            LEFT JOIN carreras   AS c  ON c.id=a.carrera_actual 
            LEFT JOIN regimenes_ AS r  ON r.id=c.regimen 
            LEFT JOIN matriculas AS m  ON (m.id_alumno=a.id AND m.ano=$ano AND m.semestre=$semestre)
            $condiciones
            GROUP BY r.orden,r.nombre,ae.agrupador 
            ORDER BY r.orden,ae.agrupador";
    return $SQL;
}

function html_cuadro_mat($titulo,$aMatriculas) {
	global $aEstados, $aRegimenes, $semestre, $ano, $fecha_corte, $meta_estado;

	$fecha_corte_f = strftime("%A %e de %B de %Y",strtotime($fecha_corte));

	$cant_estados = count($aEstados) + 2;

	$tot_regimen = $tot_mat = array();

	$HTML = "<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>"
		. "  <tr class='filaTituloTabla'><td class='tituloTabla' colspan='$cant_estados'>$titulo <i>Proceso $semestre-$ano</i><br><small>al $fecha_corte_f</small></td></tr>"
		. "  <tr class='filaTituloTabla'><td class='tituloTabla'>Regimen</td>";
	foreach($aEstados AS $estado_agrup) { $HTML .= "<td class='tituloTabla'>$estado_agrup</td>"; }
	if ($meta_estado == "t") {
		$HTML .= "<td class='tituloTabla'>Total</td>";
	}
	$HTML .= "</tr>";
	foreach($aRegimenes AS $regimen_nombre) {
		$HTML .= "<tr class='filaTabla'><td class='tituloTabla' style='text-align: left;'>$regimen_nombre</td>";
		if (!isset($tot_regimen[$regimen_nombre])) { $tot_regimen[$regimen_nombre] = 0; }
		foreach($aEstados AS $estado_agrup) {
			$valor = "-";
			if (!isset($tot_mat[$estado_agrup])) { $tot_mat[$estado_agrup] = 0; }
			for ($x=0;$x<count($aMatriculas);$x++) {
				if ($aMatriculas[$x]['regimen'] == $regimen_nombre && $aMatriculas[$x]['estado_agrup'] == $estado_agrup) {
					$valor = $aMatriculas[$x]['cant_alumnos'];
					$tot_mat[$estado_agrup] += $valor;
					$tot_regimen[$regimen_nombre] += $valor;
				}
			}
			$HTML .= "<td class='textoTabla' style='text-align: right'>".number_format($valor,0,",",".")."</td>";
		}
		if ($meta_estado == "t") {   
			$HTML .= "<td class='textoTabla' style='text-align: right'><b>".number_format($tot_regimen[$regimen_nombre],0,",",".")."</b></td>";
			$tot_mat["Total"] += $tot_regimen[$regimen_nombre];
		}
		$HTML .= "</tr>";
	}
	$HTML .= "<tr class='filaTabla'><td class='celdaNombreAttr'>Total:</td>";
	foreach($tot_mat AS $valor) { $HTML .= "<td class='celdaNombreAttr' style='text-align: right'>".number_format($valor,0,",",".")."</td>"; }
	$HTML .= "</table><br>";

	return $HTML;
}

?>