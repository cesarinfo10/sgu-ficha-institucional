<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$anos         = implode(",",$_REQUEST['anos']);
$semestre     = $_REQUEST['semestre'];
$fecha_corte  = $_REQUEST['fecha_corte'];
$meta_estado  = $_REQUEST['meta_estado'];
$meta_regimen = $_REQUEST['meta_regimen'];
$admision     = $_REQUEST['admision'];
$jornada      = $_REQUEST['jornada'];
$dia_corte    = $_REQUEST['dia_corte'];
$mes_corte    = $_REQUEST['mes_corte'];

if (empty($anos)) { $anos = $ANO_MATRICULA-1 . "," . $ANO_MATRICULA; }
if (empty($fecha_corte)) { $fecha_corte = date("Y-m-d"); }
if (empty($semestre)) { $semestre = $SEMESTRE_MATRICULA; }
if (empty($meta_regimen)) { $meta_regimen = "1. Pregrado"; }
if (empty($_REQUEST['dia_corte'])) { $dia_corte = date("j"); }
if (empty($_REQUEST['mes_corte'])) { $mes_corte = date("n"); }

$cond = $cond_regimen = "\n";

if ($meta_regimen <> "t" && !empty($meta_regimen)) { $cond .= " AND r.agrupador = '$meta_regimen' \n"; $cond_regimen = "WHERE agrupador='$meta_regimen' \n"; }

if ($semestre <> "") { $cond .= " AND (c.semestre='$semestre' OR c.semestre IS NULL) \n"; }

if ($admision <> "") { $cond .= " AND (pap.admision IN ('".str_replace(",","','",$admision)."')) \n";	}

if ($jornada <> "") { $cond .= " AND (c.jornada='$jornada') \n"; }

if ($dia_corte > 0 && $mes_corte > 0) { 
	$SQL_pap = "SELECT c.ano,r.nombre AS regimen,r.orden AS orden_regimen,car.nombre AS carrera,pap.id 
	            FROM finanzas.contratos AS c
				LEFT JOIN pap ON pap.id=c.id_pap
				LEFT JOIN carreras AS car ON car.id=c.id_carrera
				LEFT JOIN regimenes_ AS r ON r.id=car.regimen
				WHERE c.estado IS NOT NULL $cond";

	$aSQL_pap = array();
	$aAnos = explode(",",$anos);
	$cond_aux = " AND (";
	for ($x=0;$x<count($aAnos);$x++) {		
		$ano_mat = $ano = $aAnos[$x];
		$ano_ant = $ano - 1;
		if ($ANO_MATRICULA > $ano && $semestre == 2) { $ano = $ano_ant; }
		$fec_termino = "$ano-$mes_corte-$dia_corte";
		$fec_inicio = "$ano_ant-01-01";
		if ($semestre == 2) { $fec_inicio = "$ano-06-01"; }

		if ($ano%4 == 0 && $ano%100 <> 0 || $ano%400 == 0) {
			//si el año es biciesto
			if ($mes_corte == 2 && $dia_corte > 29) { $fec_termino = "$ano-02-29"; }
		} 
		elseif ($mes_corte == 2 && $dia_corte > 28) { $fec_termino = "$ano-02-28"; }

		$cond_aux = " AND c.ano=$ano_mat \n"
		          . " AND c.fecha::date BETWEEN '$fec_inicio'::date AND '$fec_termino'::date \n"
				  . " AND c.id_pap IS NOT NULL \n"
		          . " AND (c.estado IN ('E','Z') OR (c.estado IN ('A','S','R') AND c.estado_fecha::date > '$fec_termino'::date)) \n";

		$aSQL_pap[] = "$SQL_pap $cond_aux";
		
	}
	$cond .= substr($cond_aux,0,-4).")";
	$SQL_pap = "(" . implode(") \n UNION ALL \n (",$aSQL_pap) . ")";
}

$SQL_mat = "SELECT ano,regimen,carrera,count(id) AS cant_postulantes 
            FROM ($SQL_pap) AS foo
            GROUP BY orden_regimen,regimen,carrera,ano
            ORDER BY orden_regimen,regimen,carrera,ano";
$mat = consulta_sql($SQL_mat);
echo("<!-- $SQL_mat -->");
//var_dump($mat);
$carreras_mat = array_unique(array_column($mat,"carrera"));
$regimenes_mat = array_unique(array_column($mat,"regimen"));
//var_dump($carreras_mat);

$anos = explode(",",$anos);
//var_dump($anos);
$HTML_mat = "";
$y = 0;
$cant_anos = count($anos) + 1;
$tot = array();
foreach ($regimenes_mat AS $regimen_mat) {
	$tot_regimen = array();
	$HTML_mat .= "<tr class='filaTabla'><td class='textoTabla' colspan='$cant_anos' style='text-align: center'><b><i>$regimen_mat</i></b></td></tr>\n";
	foreach ($carreras_mat AS $carrera_mat) {
		//echo("$regimen_mat = {$mat[$y]['regimen']} $y<br>");

		if ($regimen_mat == $mat[$y]['regimen'] && $carrera_mat == $mat[$y]['carrera']) {
			$HTML_mat .= "<tr class='filaTabla'>\n<td class='textoTabla'>$carrera_mat</td>\n";
			foreach($anos AS $ano_mat) {
				$tot_regimen[$ano_mat] += 0;
				if ($regimen_mat == $mat[$y]['regimen'] && $carrera_mat == $mat[$y]['carrera'] && $ano_mat == $mat[$y]["ano"]) {
					$HTML_mat .= "<td class='textoTabla' align='right'>{$mat[$y]['cant_postulantes']}</td>\n";
					$tot_regimen[$ano_mat] += $mat[$y]['cant_postulantes'];
					$tot[$ano_mat] += $mat[$y]['cant_postulantes'];
					$y++;
				} else {
					$HTML_mat .= "<td class='textoTabla' align='right'>0</td>\n";
				}
			}
			$HTML_mat .= "</tr>\n";	
		} else { 
			for ($z=0;$z<count($mat);$z++) {
				if ($regimen_mat == $mat[$z]['regimen']) { $y = $z; break; }
			}
		}
	}
	//var_dump($tot);
	$HTML_mat .= "<tr class='filaTabla'><td class='celdaNombreAttr' align='right'><b>SubTotal $regimen_mat:</b></td>";
	foreach($tot_regimen AS $tot_ano) {
		$HTML_mat .= "<td class='celdaNombreAttr' style='text-align: right'><b>$tot_ano</b></td>";
	}
	$HTML_mat .= "</tr>\n";
	$y = 0;
}
$HTML_mat .= "<tr class='filaTabla'><td class='textoTabla' colspan='$cant_anos' style='text-align: center'>&nbsp;</td></tr>\n"
          .  "<tr class='filaTabla'><td class='celdaNombreAttr' align='right'><b>Total:</b></td>";

foreach($anos AS $ano_mat) {
	$HTML_mat .= "<td class='celdaNombreAttr' style='text-align: right'><b>{$tot[$ano_mat]}</b></td>";
}
$HTML_mat .= "</tr>\n";


$ANOS = consulta_sql("SELECT DISTINCT ON (ano) ano AS id,ano AS nombre FROM finanzas.contratos ORDER BY ano");
$HTML_anos = "";
for ($x=0;$x<count($ANOS);$x++) {
	$checked = "";
	$ano = $ANOS[$x]['id'];
	if (in_array($ano,$anos)) { $checked = "checked='checked'"; }
	$HTML_anos .= "<input style='vertical-align: bottom;' type='checkbox' name='anos[]' value='$ano' id='$ano' onChange='submitform();' $checked>"
               .  "<label for='$ano'>$ano</label>&nbsp;&nbsp;";
}

$META_REGIMENES = consulta_sql("SELECT DISTINCT ON (agrupador) agrupador AS id,agrupador AS nombre FROM regimenes_ ORDER BY id");

$ADMISION = array_merge(array(array('id' => "1,3", 'nombre' => "1er año (Regular + Especial)")),$ADMISION) ;  

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">

<table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
	<td class="celdaFiltro" colspan="10">
      Años:<br>
      <div style='vertical-align: top'><?php echo($HTML_anos); ?></div>
    </td>
  </tr>
  <tr>
    <td class="celdaFiltro">
      Semestre:<br>
      <select class='filtro' name="semestre" onChange="submitform();">
        <?php echo(select($semestres,$semestre)); ?>
      </select>
    </td>
    <td class="celdaFiltro">
      Día y mes de corte:<br>
      <select class="filtro" name="dia_corte" onChange="submitform();" style='text-align: right' onClick="if (formulario.mes_corte.value=='') { var f=new Date(); formulario.mes_corte.value=f.getMonth()+1; }">
		<option value="">Día</option>
		<?php echo(select($dias_fn,$dia_corte)); ?>
	  </select> de 	
      <select class="filtro" name="mes_corte" onChange="formulario.dia_corte.value=daysInMonth(this.value); submitform();">
	    <option value="" style='text-align: center'>-- Mes --</option>
	    <?php echo(select($meses_palabra,$mes_corte)); ?>
	  </select>
    </td>
	<td class="celdaFiltro">
	  <div align='left'>Meta Regimen:</div>
  	  <select class="filtro" name="meta_regimen" onChange="submitform();">
	      <option value="t">Todas</option>
        <?php echo(select($META_REGIMENES,$meta_regimen)); ?>    
	    </select>
	  </td>
    <td class="celdaFiltro">
	    <div align='left'>Jornada:</div>
	    <select class="filtro" name="jornada" onChange="submitform();">
		    <option value="">Ambas</option>
		    <?php echo(select($JORNADAS,$jornada)); ?>
	    </select>
	  </td>
    <td class="celdaFiltro">
	    Admisión: <br>
  	  <select class="filtro" name="admision" onChange="submitform();">
	  	  <option value="">Todos</option>
		    <?php echo(select($ADMISION,$admision)); ?>
	    </select>
	  </td>
  </tr>
</table>

<table bgcolor='#ffffff' cellspacing='1' cellpadding='1' class='tabla' style='margin-top: 5px'>
  <colgroup>
    <col>
    <col>
	<?php for($x=0;$x<count($anos);$x++) { echo("<col style='' id='{$anos[$x]}'>"); } ?>
	<col>
  </colgroup>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="<?php echo(count($anos) + 1); ?>">Postulantes Matrículados</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Carrera/Programa</td>
    <?php for($x=0;$x<count($anos);$x++) { echo("<td class='tituloTabla'>{$anos[$x]}</td>\n"); } ?>
  </tr>
  <?php echo($HTML_mat); ?>
</form>