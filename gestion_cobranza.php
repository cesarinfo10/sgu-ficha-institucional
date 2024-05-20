<?php


if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) {	$cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if (empty($reg_inicio)) { $reg_inicio = 0; }

$mes              = $_REQUEST['mes'];
$semestre         = $_REQUEST['semestre'];
$ano              = $_REQUEST['ano'];
$estado           = $_REQUEST['estado'];
$tipo             = $_REQUEST['tipo'];
$mes_desglosado   = $_REQUEST['mes_desglosado'];
$tipo_alumno      = $_REQUEST['tipo_alumno'];
$regimen          = $_REQUEST['regimen'];
$id_carrera       = $_REQUEST['id_carrera'];
$jornada          = $_REQUEST['jornada'];

if ($estado == "")    { $estado = '1'; }
if (empty($ano))      { $ano = $ANO; }
if (empty($semestre)) { $semestre = null; }
if (empty($mes))      { $mes = null; }

if (empty($mes_desglosado)) { $mes_desglosado = date("m"); }

if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }


$condicion = "WHERE true ";

if ($id_carrera <> "") { $condicion .= "AND c.id_carrera=$id_carrera "; }

if ($jornada <> "") { $condicion .= "AND c.jornada='$jornada' "; }

if ($mes > 0) { $condicion .= "AND c.mes=$mes "; }

if ($ano > 0) { $condicion .= "AND c.ano=$ano "; }

if ($estado <> "") {
	if ($estado == "N")  { $condicion .= "AND c.estado IS NULL "; } 
	elseif ($estado == "1") { $condicion .= "AND c.estado IS NOT NULL "; }
	elseif ($estado == "D") { $condicion .= "AND c.estado IN ('S','R','A') "; }
	elseif ($estado != "0") { $condicion .= "AND c.estado='$estado' "; }
}

if ($tipo == "Anual" || $tipo == "Modular") { $semestre = null; }

if (!is_null($semestre)) { $condicion .= "AND c.semestre=$semestre "; }

if ($tipo <> "" && $tipo <> "0") { $condicion .= "AND c.tipo='$tipo' "; }

if ($regimen <> "" && $regimen <> "t") { $condicion .= "AND (car.regimen = '$regimen') "; }

if ($tipo_alumno == "N") { $condicion .= "AND c.id_pap IS NOT NULL "; }
if ($tipo_alumno == "A") { $condicion .= "AND c.id_alumno IS NOT NULL "; }

$ano_ant = $ano-2;
//$cond_ant = str_replace("c.ano=$ano","c.ano=$ano_ant AND c.ano<$ano",$condicion);
$cond_ant = str_replace("c.ano=","c.ano<",$condicion);

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$IDS_GLOSAS = "2,3,20,21,22,31,10003";

$SQL_contratos = "SELECT c.id FROM finanzas.contratos AS c LEFT JOIN carreras AS car ON car.id=c.id_carrera $condicion";

$SQL_contratos_rut = "SELECT rut FROM vista_contratos_rut WHERE id IN ($SQL_contratos)";

$SQL_contratos_ant = "SELECT c.id 
                      FROM finanzas.contratos AS c 
                      LEFT JOIN vista_contratos_rut AS vcr USING (id)
                      LEFT JOIN carreras AS car ON car.id=c.id_carrera
                      $cond_ant AND vcr.rut IN ($SQL_contratos_rut)";

$SQL_pactados_mat = "SELECT date_part('year',fecha_venc) as ano,date_part('month',fecha_venc) as mes,
                            sum(monto) AS monto
                     FROM finanzas.cobros
                     WHERE id_contrato IN ($SQL_contratos) AND id_glosa IN (1,10001)
                     GROUP BY ano,mes
                     ORDER BY ano,mes;"; //montos pactados de matriculas

$SQL_pagados_mat  = "SELECT date_part('year',fecha_venc) as ano,date_part('month',fecha_venc) as mes,
                            sum(coalesce(monto_abonado,monto)) AS monto
                     FROM finanzas.cobros
                     WHERE id_contrato IN ($SQL_contratos) AND id_glosa IN (1,10001) AND (pagado OR abonado)
                     GROUP BY ano,mes
                     ORDER BY ano,mes;"; //montos pagados de matriculas

$SQL_pact_desg  = "SELECT date_part('year',fecha_venc) as ano,date_part('month',fecha_venc) as mes,date_part('day',fecha_venc) as dia,
						  sum(monto) AS monto
				   FROM finanzas.cobros 
				   WHERE id_contrato IN ($SQL_contratos) AND id_glosa IN ($IDS_GLOSAS)
						 AND date_part('month',fecha_venc) = $mes_desglosado AND date_part('year',fecha_venc) = date_part('year',now())
				   GROUP BY ano,mes,dia"; //montos pactados de aranceles completos, mensualidades de cheque y pagaré
                  
$SQL_pag_desg  = "SELECT date_part('year',fecha_venc) as ano,date_part('month',fecha_venc) as mes,date_part('day',fecha_venc) as dia,
						 sum(coalesce(monto_abonado,monto)) AS monto
				  FROM finanzas.cobros 
				  WHERE id_contrato IN ($SQL_contratos) AND id_glosa IN ($IDS_GLOSAS) AND (pagado OR abonado)
					AND date_part('month',fecha_venc) = $mes_desglosado AND date_part('year',fecha_venc) = date_part('year',now())
				  GROUP BY ano,mes,dia"; //montos pagados de aranceles completos, mensualidades de cheque y pagaré

$SQL_pact_agrup = "SELECT date_part('year',fecha_venc) as ano,date_part('month',fecha_venc) as mes, null AS dia,
                          sum(monto) AS monto
                   FROM finanzas.cobros
                   WHERE id_contrato IN ($SQL_contratos) AND id_glosa IN ($IDS_GLOSAS)
                     AND NOT (date_part('month',fecha_venc) = $mes_desglosado AND date_part('year',fecha_venc) = date_part('year',now()))
                   GROUP BY ano,mes"; //montos pactados de aranceles completos, mensualidades de cheque y pagaré

$SQL_pag_agrup  = "SELECT date_part('year',fecha_venc) as ano,date_part('month',fecha_venc) as mes, null AS dia,
                          sum(coalesce(monto_abonado,monto)) AS monto
                   FROM finanzas.cobros
                   WHERE id_contrato IN ($SQL_contratos) AND id_glosa IN ($IDS_GLOSAS) AND (pagado OR abonado)
                     AND NOT (date_part('month',fecha_venc) = $mes_desglosado AND date_part('year',fecha_venc) = date_part('year',now()))
                   GROUP BY ano,mes"; //montos pagados de aranceles completos, mensualidades de cheque y pagaré

$SQL_pact_desg_ant  = "SELECT date_part('year',fecha_venc) as ano,date_part('month',fecha_venc) as mes,date_part('day',fecha_venc) as dia,
						  sum(monto) AS monto
				   FROM finanzas.cobros 
				   WHERE id_contrato IN ($SQL_contratos_ant) AND id_glosa IN ($IDS_GLOSAS)
						 AND date_part('month',fecha_venc) = $mes_desglosado AND date_part('year',fecha_venc) = date_part('year',now())
				   GROUP BY ano,mes,dia"; //montos pactados de aranceles completos, mensualidades de cheque y pagaré
                  
$SQL_pag_desg_ant  = "SELECT date_part('year',fecha_venc) as ano,date_part('month',fecha_venc) as mes,date_part('day',fecha_venc) as dia,
						 sum(coalesce(monto_abonado,monto)) AS monto
				  FROM finanzas.cobros 
				  WHERE id_contrato IN ($SQL_contratos_ant) AND id_glosa IN ($IDS_GLOSAS) AND (pagado OR abonado)
					AND date_part('month',fecha_venc) = $mes_desglosado AND date_part('year',fecha_venc) = date_part('year',now())
				  GROUP BY ano,mes,dia"; //montos pagados de aranceles completos, mensualidades de cheque y pagaré

$SQL_pact_agrup_ant = "SELECT date_part('year',fecha_venc) as ano,date_part('month',fecha_venc) as mes, null AS dia,
                          sum(monto) AS monto
                   FROM finanzas.cobros
                   WHERE id_contrato IN ($SQL_contratos_ant) AND id_glosa IN ($IDS_GLOSAS)
                     AND NOT (date_part('month',fecha_venc) = $mes_desglosado AND date_part('year',fecha_venc) = date_part('year',now()))
                   GROUP BY ano,mes"; //montos pactados de aranceles completos, mensualidades de cheque y pagaré

$SQL_pag_agrup_ant  = "SELECT date_part('year',fecha_venc) as ano,date_part('month',fecha_venc) as mes, null AS dia,
                          sum(coalesce(monto_abonado,monto)) AS monto
                   FROM finanzas.cobros
                   WHERE id_contrato IN ($SQL_contratos_ant) AND id_glosa IN ($IDS_GLOSAS) AND (pagado OR abonado)
                     AND NOT (date_part('month',fecha_venc) = $mes_desglosado AND date_part('year',fecha_venc) = date_part('year',now()))
                   GROUP BY ano,mes"; //montos pagados de aranceles completos, mensualidades de cheque y pagaré

$SQL_pactados = "($SQL_pact_agrup) UNION ($SQL_pact_desg) ORDER BY ano,mes,dia";
$SQL_pagados  = "($SQL_pag_agrup) UNION ($SQL_pag_desg) ORDER BY ano,mes,dia";

$SQL_pactados_ant = "($SQL_pact_agrup_ant) UNION ($SQL_pact_desg_ant) ORDER BY ano,mes,dia";
$SQL_pagados_ant  = "($SQL_pag_agrup_ant) UNION ($SQL_pag_desg_ant) ORDER BY ano,mes,dia";

$pactados      = consulta_sql($SQL_pactados);
$pagados       = consulta_sql($SQL_pagados);
$pactados_mat  = consulta_sql($SQL_pactados_mat);
$pagados_mat   = consulta_sql($SQL_pagados_mat);

$pactados_ant  = consulta_sql($SQL_pactados_ant);
$pagados_ant   = consulta_sql($SQL_pagados_ant);

// Generar Flujo y HTML del mismo de la Matricula
$flujo_mat = $flujo_ano_meses_mat = array();
genera_flujo($pactados_mat, $pagados_mat, $flujo_mat, $flujo_ano_meses_mat);
$HTML_mat = genera_html_flujo_horizontal($flujo_mat, $flujo_ano_meses_mat,"Matrículas $ano");

// Generar Flujo y HTML del mismo de los aranceles
$flujo = $flujo_ano_meses = array();
genera_flujo($pactados, $pagados, $flujo, $flujo_ano_meses);
$HTML = genera_html_flujo_horizontal($flujo, $flujo_ano_meses,"Aranceles");

$titulo = "Aranceles $ano (Contado, Cheques, Pagarés de Colegiatura y Tarjetas de Crédito)";
$HTML2 = genera_html_flujo_vertical($flujo,$flujo_ano_meses,$titulo);

$flujo_ant = $flujo_ano_meses_ant = array();
genera_flujo($pactados_ant, $pagados_ant, $flujo_ant, $flujo_ano_meses_ant);

$ano_ini = $ano-2;
$ano_fin = $ano-1;
//$titulo = "Aranceles Anteriores [$ano_ini-$ano_fin] respecto a matriculados en $ano (Contado, Cheques, Pagarés de Colegiatura y Tarjetas de Crédito)";
$titulo = "Aranceles Anteriores respecto a matriculados en $ano (Contado, Cheques, Pagarés de Colegiatura y Tarjetas de Crédito)";
$HTML3 = genera_html_flujo_vertical($flujo_ant, $flujo_ano_meses_ant,$titulo);

$carreras = consulta_sql("SELECT id,nombre FROM carreras WHERE regimen='$regimen' ORDER BY nombre;");

$cohortes = $anos;

$SEMESTRES = array(array("id"=>0,"nombre"=>0),
                   array("id"=>1,"nombre"=>1),
                   array("id"=>2,"nombre"=>2));

$_SESSION['enlace_volver'] = "$enlace_nav=$reg_inicio";

$REGIMENES = consulta_sql("SELECT * FROM regimenes");


?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<div class="texto">
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
      <tr>
        <td class="celdaFiltro">
          Periodo/Ejercicio:<br>
          <div style='text-align: right'>
          <select name="mes" onChange="submitform();" class='filtro'>
            <option value="">-- Mes --</option>
            <?php echo(select($meses_fn,$mes)); ?>    
          </select>
          /
          <select name="semestre" onChange="submitform();" class='filtro'>
            <option value="">-- Semestre --</option>
            <?php echo(select($SEMESTRES,$semestre)); ?>    
          </select>
          - 
          <select name="ano" onChange="submitform();" class='filtro'>
            <option value="-1">-- Años --</option>
            <?php echo(select($anos_contratos,$ano)); ?>    
          </select>
          </div>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Estado:</div>
          <select class="filtro" name="estado" onChange="submitform();">
            <option value="0">Todos</option>
            <?php echo(select($estados_contratos,$estado)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">        
          Tipo:<br>
          <select name="tipo" onChange="submitform();" class='filtro'>
            <option value="0">Todos</option>
            <?php echo(select($tipos_contratos,$tipo)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">        
          Alumnos:<br>
          <select name="tipo_alumno" onChange="submitform();" class='filtro'>
            <option value="">Todos</option>
            <?php echo(select($tipos_alumnos,$tipo_alumno)); ?>    
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
            <?php echo(select($meses_palabra,$mes_desglosado)); ?>    
          </select>
        </td>
      </tr>
    </table>  
</div>
</form>
<?php echo($HTML_mat); ?>
<br>
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
