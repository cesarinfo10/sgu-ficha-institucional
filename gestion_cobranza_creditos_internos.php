<?php


if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$ano              = $_REQUEST['ano'];
$estado           = $_REQUEST['estado'];
$mes_desglosado   = $_REQUEST['mes_desglosado'];
$regimen          = $_REQUEST['regimen'];
$id_carrera       = $_REQUEST['id_carrera'];
$jornada          = $_REQUEST['jornada'];

if (empty($estado)) { $estado = 'TodosNoNulo'; }
//if (empty($ano))    { $ano = $ANO; }

if (empty($mes_desglosado)) { $mes_desglosado = date("m"); }

if (empty($regimen)) { $regimen = 'PRE'; }


$condicion = "WHERE true ";

if ($id_carrera <> "") { $condicion .= "AND a.carrera_actual=$id_carrera "; }

if ($jornada <> "") { $condicion .= "AND a.jornada='$jornada' "; }

if ($ano > 0) { $condicion .= "AND date_part('year',c.fecha)=$ano "; }

if ($estado == "TodosNoNulo") { $condicion .= "AND NOT c.nulo "; }
elseif ($estado <> "") { $condicion .= "AND NOT c.nulo AND c.estado='$estado' "; }

if ($regimen <> "" && $regimen <> "t") { $condicion .= "AND (car.regimen = '$regimen') "; }

$IDS_GLOSAS = "300,301,302,303,304";

$SQL_convenios_ci = "SELECT c.id FROM finanzas.convenios_ci AS c LEFT JOIN alumnos AS a ON a.id=c.id_alumno LEFT JOIN carreras AS car ON car.id=a.carrera_actual $condicion";

$SQL_contratos_rut = "SELECT rut FROM vista_contratos_rut WHERE id IN ($SQL_contratos)";

$SQL_pact_desg  = "SELECT date_part('year',fecha_venc) as ano,date_part('month',fecha_venc) as mes,date_part('day',fecha_venc) as dia,
						  sum(monto) AS monto
				   FROM finanzas.cobros 
				   WHERE id_convenio_ci IN ($SQL_convenios_ci) AND id_glosa IN ($IDS_GLOSAS)
						 AND date_part('month',fecha_venc) = $mes_desglosado AND date_part('year',fecha_venc) = date_part('year',now())
				   GROUP BY ano,mes,dia"; //montos pactados de aranceles completos, mensualidades de cheque y pagaré
                  
$SQL_pag_desg  = "SELECT date_part('year',fecha_venc) as ano,date_part('month',fecha_venc) as mes,date_part('day',fecha_venc) as dia,
						 sum(coalesce(monto_abonado,monto)) AS monto
				  FROM finanzas.cobros 
				  WHERE id_convenio_ci IN ($SQL_convenios_ci) AND id_glosa IN ($IDS_GLOSAS) AND (pagado OR abonado)
					AND date_part('month',fecha_venc) = $mes_desglosado AND date_part('year',fecha_venc) = date_part('year',now())
				  GROUP BY ano,mes,dia"; //montos pagados de aranceles completos, mensualidades de cheque y pagaré

$SQL_pact_agrup = "SELECT date_part('year',fecha_venc) as ano,date_part('month',fecha_venc) as mes, null AS dia,
                          sum(monto) AS monto
                   FROM finanzas.cobros
                   WHERE id_convenio_ci IN ($SQL_convenios_ci) AND id_glosa IN ($IDS_GLOSAS)
                     AND NOT (date_part('month',fecha_venc) = $mes_desglosado AND date_part('year',fecha_venc) = date_part('year',now()))
                   GROUP BY ano,mes"; //montos pactados de aranceles completos, mensualidades de cheque y pagaré

$SQL_pag_agrup  = "SELECT date_part('year',fecha_venc) as ano,date_part('month',fecha_venc) as mes, null AS dia,
                          sum(coalesce(monto_abonado,monto)) AS monto
                   FROM finanzas.cobros
                   WHERE id_convenio_ci IN ($SQL_convenios_ci) AND id_glosa IN ($IDS_GLOSAS) AND (pagado OR abonado)
                     AND NOT (date_part('month',fecha_venc) = $mes_desglosado AND date_part('year',fecha_venc) = date_part('year',now()))
                   GROUP BY ano,mes"; //montos pagados de aranceles completos, mensualidades de cheque y pagaré

$SQL_pactados = "($SQL_pact_agrup) UNION ($SQL_pact_desg) ORDER BY ano,mes,dia";
$SQL_pagados  = "($SQL_pag_agrup) UNION ($SQL_pag_desg) ORDER BY ano,mes,dia";

$pactados      = consulta_sql($SQL_pactados);
$pagados       = consulta_sql($SQL_pagados);

// Generar Flujo y HTML del mismo de los aranceles
$flujo = $flujo_ano_meses = array();
genera_flujo($pactados, $pagados, $flujo, $flujo_ano_meses);
$HTML = genera_html_flujo_horizontal($flujo, $flujo_ano_meses,"Aranceles");

$titulo = "Creditos Solidarios $ano <br><small>(Contado, Cheques, Pagarés de Liquidación y Tarjetas de Crédito)</small>";
$HTML2 = genera_html_flujo_vertical($flujo,$flujo_ano_meses,$titulo);

$carreras = consulta_sql("SELECT id,nombre FROM carreras WHERE regimen='$regimen' ORDER BY nombre;");

$cohortes = $anos;

$SEMESTRES = array(array("id"=>0,"nombre"=>0),
                   array("id"=>1,"nombre"=>1),
                   array("id"=>2,"nombre"=>2));

$_SESSION['enlace_volver'] = "$enlace_nav=$reg_inicio";

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

$estados_cci = consulta_sql("SELECT * FROM vista_cci_estados");

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<div class="texto">
  <form name="formulario" action="principal_sm.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
      <tr>
        <td class="celdaFiltro">
          Periodo/Ejercicio:<br>
          <select name="ano" onChange="submitform();" class='filtro'>
            <option value="">Todos</option>
            <?php echo(select($anos_contratos,$ano)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Estado:</div>
          <select class="filtro" name="estado" onChange="submitform();">
            <option value="TodosNoNulo">Todos No Nulo</option>
            <?php echo(select($estados_cci,$estado)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">        
          Carrera/Programa:<br>
          <select name="id_carrera" onChange="submitform();" class='filtro'>
            <option value="">Todas</option>
            <?php echo(select($carreras,$id_carrera)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">        
          Jornada:<br>
          <select name="jornada" onChange="submitform();" class='filtro'>
            <option value="">Ambas</option>
            <?php echo(select($JORNADAS,$jornada)); ?>
          </select>
        </td>
        <td class="celdaFiltro">        
          Régimen:<br>
          <select name="regimen" onChange="submitform();" class='filtro'>
            <option value="t">Todos</option>
            <?php echo(select($REGIMENES,$regimen)); ?>
          </select>
        </td><td></td>
        <td class="celdaFiltro">        
          Mes desglosado:<br>
          <select name="mes_desglosado" onChange="submitform();" class='filtro'>
            <option value="-1">-- Ninguno --</option>
            <?php echo(select($meses_palabra,$mes_desglosado)); ?>    
          </select>
        </td>
      </tr>
    </table>  
</div>
</form>
<?php echo($HTML2); ?>
<br>
<?php echo($HTML3); ?>
<br>
<!-- Fin: <?php echo($modulo); ?> -->
<?php
function genera_html_flujo_horizontal($flujo, $flujo_ano_meses, $titulo) {
	global $meses_palabra;

	$tot_cols = 2;
	$tot_pactado = $tot_pagado = $tot_morosidad = 0;
	
	foreach($flujo_ano_meses as $ano) { foreach($ano as $cant_meses) { $tot_cols += $cant_meses; }}
	
	$HTML = "<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla'>\n"
	      . "<tr class='filaTituloTabla'><td colspan='$tot_cols' class='tituloTabla' style='text-align: left'>$titulo</td></tr>\n"
	      . "<tr class='filaTituloTabla'>\n"
	      . "  <td></td>\n";
	$cant_meses = 0;
	$ano_aux = $flujo[0]['ano'];
	for ($x=0;$x<count($flujo);$x++) {
		if ($ano_aux <> $flujo[$x]['ano'] || $x+1 == count($flujo)) {
			if ($x+1 == count($flujo)) { $cant_meses++; }
			$HTML .= "<td  class='tituloTabla' colspan='$cant_meses'>$ano_aux</td>\n";
			$cant_meses = 0;
			$ano_aux = $flujo[$x]['ano'];
		}
		$cant_meses++;
	}
	$HTML .= "<td  class='tituloTabla'></td></tr>\n<tr class='filaTituloTabla'>\n<td></td>\n";
	for ($z=0;$z<count($flujo);$z++) {
		$mes_palabra = substr($meses_palabra[$flujo[$z]['mes']-1]['nombre'],0,3);
		$HTML .= "<td class='tituloTabla'>$mes_palabra</td>\n";
	}
	$HTML .= "<td class='tituloTabla'>TOTAL</td></tr>\n";
	$HTML .= "<tr>\n<td class='tituloTabla'>Pactados</td>\n";
	for ($x=0;$x<count($flujo);$x++) { 
		$HTML .= "<td class='textoTabla' align='right'><span style='color: #000080'>".number_format($flujo[$x]['pactado'],0,',','.')."</span></td>\n";
		$tot_pactado += $flujo[$x]['pactado'];
	}
	$HTML .= "<td class='textoTabla' align='right'><span style='color: #000080'><b>".number_format($tot_pactado,0,',','.')."</b></span></td></tr>\n";
	$HTML .= "<tr>\n<td class='tituloTabla'>Pagados</td>\n";
	for ($x=0;$x<count($flujo);$x++) { 
		$HTML .= "<td class='textoTabla' align='right'><span style='color: #008000'>".number_format($flujo[$x]['pagado'],0,',','.')."</span></td>\n";
		$tot_pagado += $flujo[$x]['pagado'];
	}
	$HTML .= "<td class='textoTabla' align='right'><span style='color: #008000'><b>".number_format($tot_pagado,0,',','.')."</b></span></td></tr>\n";
	$HTML .= "<tr>\n<td class='tituloTabla'>Morosidad</td>\n";
	for ($x=0;$x<count($flujo);$x++) { 
		$HTML .= "<td class='textoTabla' align='right'><span style='color: #FF0000'>".number_format($flujo[$x]['morosidad'],0,',','.')."</span></td>\n";
		$tot_morosidad += $flujo[$x]['morosidad'];
	}
	$HTML .= "<td class='textoTabla' align='right'><span style='color: #FF0000'><b>".number_format($tot_morosidad,0,',','.')."</b></span></td></tr>\n</table>\n";
	return $HTML;
}

function genera_flujo($pactados,$pagados,&$flujo,&$flujo_ano_meses) {
	$y = $morosidad_acum = $pactado_acum = 0;
	for ($x=0;$x<count($pactados);$x++) {
		$flujo[$x]['ano']     = $pactados[$x]['ano'];
		$flujo[$x]['mes']     = $pactados[$x]['mes'];
		$flujo[$x]['dia']     = $pactados[$x]['dia'];
		$flujo[$x]['pactado'] = $pactados[$x]['monto'];
		$pactado_acum += $flujo[$x]['pactado'];
		$flujo[$x]['pagado']  = 0;
		if ($flujo[$x]['ano'] == $pagados[$y]['ano'] 
		 && $flujo[$x]['mes'] == $pagados[$y]['mes']
		 && $flujo[$x]['dia'] == $pagados[$y]['dia'])
		{
			$flujo[$x]['pagado'] = $pagados[$y]['monto'];
			$y++;
		}
		$flujo[$x]['morosidad']         = $flujo[$x]['pactado'] - $flujo[$x]['pagado'];
		$morosidad_acum                += $flujo[$x]['morosidad'];
		$flujo[$x]['morosidad_mensual'] = $flujo[$x]['morosidad'] / $flujo[$x]['pactado'];
		$flujo[$x]['morosidad_acum']    = $morosidad_acum / $pactado_acum;
		$flujo_ano_meses[$flujo[$x]['ano']]['cant_meses']++;
	}
}

function genera_html_flujo_vertical($flujo, $flujo_ano_meses, $titulo) {
	global $meses_palabra;
	// Generar Flujo y HTML del mismo de los aranceles

	$HTML2 = "<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla'>\n"
		   . "<tr class='filaTituloTabla'><td colspan='7' class='tituloTabla' style='text-align: left'>$titulo</td></tr>\n"
		   . "<tr class='filaTituloTabla'>\n"
		   . "  <td class='tituloTabla' colspan='2' rowspan='2'></td>\n"
		   . "  <td class='tituloTabla' rowspan='2'>Pactado</td>\n"
		   . "  <td class='tituloTabla' rowspan='2'>Pagado</td>\n"
		   . "  <td class='tituloTabla' colspan='3'>Morosidad</td>\n"
		   . "</tr>\n"
		   . "<tr class='filaTituloTabla'>\n"
		   . "  <td class='tituloTabla'>Monto</td>\n"
		   . "  <td class='tituloTabla'>Mensual</td>\n"
		   . "  <td class='tituloTabla'>Acumulada</td>\n"
		   . "</tr>\n";

	$ano_aux = $flujo[0]['ano'];
	$ano_imp = true;
	$y = 0;
	$tot_pactado = $tot_pagado = $tot_morosidad = 0;

	for ($x=0;$x<count($flujo);$x++) {
		$HTML2 .= "<tr class='filaTabla'>\n";
		if ($ano_aux <> $flujo[$x]['ano'] && !$ano_imp) {
			$ano_imp = true;
			$ano_aux = $flujo[$x]['ano'];
			$tot_pactado_anual = $tot_pagado_anual = $tot_morosidad_anual = 0;
		}
		if ($ano_aux == $flujo[$x]['ano'] && $ano_imp) {
			$cant_meses = $flujo_ano_meses[$ano_aux]['cant_meses'];
			$HTML2  .= "  <td rowspan='$cant_meses' class='tituloTabla' style='height: 50px'><div style='-webkit-transform: rotate(-90deg); -moz-transform: rotate(-90deg)'>$ano_aux</div></td>\n";
			$ano_imp = false;
		}
		
		$ano_aux = $flujo[$x]['ano'];
		
		$tot_pactado_anual   += $flujo[$x]['pactado'];
		$tot_pagado_anual    += $flujo[$x]['pagado'];
		$tot_morosidad_anual += $flujo[$x]['morosidad'];
		
		$dia = "";
		if ($flujo[$x]['dia'] <> "") { $dia = $flujo[$x]['dia']."-"; }
		
		$HTML2 .= "  <td class='tituloTabla'>$dia".substr($meses_palabra[$flujo[$x]['mes']-1]['nombre'],0,3)."</td>\n"
			   .  "  <td class='textoTabla' style='vertical-align: middle; text-align: right'><span style='color: #000080'>".number_format($flujo[$x]['pactado'],0,',','.')."</span></td>\n"
			   .  "  <td class='textoTabla' style='vertical-align: middle; text-align: right'><span style='color: #008000'>".number_format($flujo[$x]['pagado'],0,',','.')."</span></td>\n"
			   .  "  <td class='textoTabla' style='vertical-align: middle; text-align: right'><span style='color: #FF0000'>".number_format($flujo[$x]['morosidad'],0,',','.')."</span></td>\n"
			   .  "  <td class='textoTabla' style='vertical-align: middle; text-align: right'><span style='color: #FF0000'>".number_format($flujo[$x]['morosidad_mensual']*100,2,',','.')."%</span></td>\n"
			   .  "  <td class='textoTabla' style='vertical-align: middle; text-align: right'><span style='color: #FF0000'>".number_format($flujo[$x]['morosidad_acum']*100,2,',','.')."%</span></td>\n"
			   .  "</tr>\n";
			   
		if ($ano_aux <> $flujo[$x+1]['ano']) {
			$HTML2 .= "<tr>"
				   .  "  <td colspan='2' class='textoTabla' align='right'><b><i>Total Año $ano_aux:</i></b></td>"
				   .  "  <td class='textoTabla' align='right'><span style='color: #000080'><b><i>".number_format($tot_pactado_anual,0,',','.')."</i></b></span></td>"
				   .  "  <td class='textoTabla' align='right'><span style='color: #008000'><b><i>".number_format($tot_pagado_anual,0,',','.')."</i></b></span></td>"
				   .  "  <td class='textoTabla' align='right'><span style='color: #FF0000'><b><i>".number_format($tot_morosidad_anual,0,',','.')."</i></b></span></td>"
				   .  "  <td colspan='2' class='textoTabla' align='right'></td>"
				   .  "</tr>";
			$tot_pactado   += $tot_pactado_anual;
			$tot_pagado    += $tot_pagado_anual;
			$tot_morosidad += $tot_morosidad_anual;
		}
	}
	$HTML2 .= "<tr><td class='textoTabla' colspan='7'>&nbsp;</td></tr>"
		   .  "<tr>"
		   .  "  <td colspan='2' class='textoTabla' style='text-align: right'><b>TOTAL EJERCICIO $ano:</b></td>"
		   .  "  <td class='textoTabla' align='right'><span style='color: #000080'><b>".number_format($tot_pactado,0,',','.')."</b></span></td>"
		   .  "  <td class='textoTabla' align='right'><span style='color: #008000'><b>".number_format($tot_pagado,0,',','.')."</b></span></td>"
		   .  "  <td class='textoTabla' align='right'><span style='color: #FF0000'><b>".number_format($tot_morosidad,0,',','.')."</b></span></td>"
		   .  "  <td colspan='2' class='textoTabla' align='right'></td>"
		   .  "</tr>"
		   .  "</table>";
	return $HTML2;
}
?>
