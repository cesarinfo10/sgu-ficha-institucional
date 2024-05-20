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

$anos         = implode(",",$_REQUEST['anos']);
$texto_buscar = $_REQUEST['texto_buscar'];
$buscar       = $_REQUEST['buscar'];
$agrupador    = $_REQUEST['agrupador'];
$id_cajero    = $_REQUEST['id_cajero'];
$rendidas     = $_REQUEST['rendidas'];
$regimen      = $_REQUEST['regimen'];


$script_name  = $_SERVER['SCRIPT_NAME'];
$enlbase = $script_name."?modulo";

if ($ano == "") { $ano = date("Y"); }
if (empty($tiempo))    { $tiempo = 1; }
if (empty($regimen))   { $regimen = 'PRE'; }
if (empty($agrupador)) { $agrupador = "M"; }
if (empty($anos)) { $anos = date("Y")-1 . "," . date("Y"); }


//$condicion = "WHERE date_part('year',p.fecha) > 2012 AND (nro_boleta IS NOT NULL OR nro_factura IS NOT NULL) ";
$condicion = "WHERE p.fecha >= '2012-10-1' AND (nro_boleta IS NOT NULL OR nro_boleta_e IS NOT NULL OR nro_factura IS NOT NULL) ";

$HTML_cab = "";

switch ($agrupador) {
	case "M":
		$SQL_agrupador = "date_part('year',p.fecha) as ano,date_part('month',p.fecha) as mes";
		$SQL_groupby   = "date_part('year',p.fecha),date_part('month',p.fecha)";
		$SQL_orderby   = "date_part('month',p.fecha),date_part('year',p.fecha)";
		$HTML_cab = "    <td class='tituloTabla' rowspan='3'>Mes</td>\n";
		break;
	case "M-D":
		$SQL_agrupador = "date_part('year',p.fecha) as ano,date_part('month',p.fecha) as mes,date_part('day',p.fecha) as dia";
		$SQL_groupby   = "date_part('year',p.fecha),date_part('month',p.fecha),date_part('day',p.fecha)";
		$SQL_orderby   = "date_part('month',p.fecha),date_part('day',p.fecha),date_part('year',p.fecha)";
		$HTML_cab = "    <td class='tituloTabla' rowspan='3'>Mes y Día</td>\n";
		break;
	case "S":
		$SQL_agrupador = "date_part('year',p.fecha::date) as ano,date_part('week',p.fecha::date) as semana";
		$SQL_groupby   = "date_part('year',p.fecha::date),date_part('week',p.fecha::date)";
		$SQL_orderby   = "date_part('week',p.fecha::date),date_part('year',p.fecha::date)";
		$HTML_cab = "    <td class='tituloTabla' rowspan='3'>Semana</td>\n";
		break;
}
	
if ($rendidas == "t") {
	$condicion .= "AND id_arqueo IS NOT NULL ";
} elseif ($rendidas == "f") {
	$condicion .= "AND id_arqueo IS NULL ";
}

if ($id_cajero > 0) {
	$condicion .= "AND p.id_cajero=$id_cajero ";
}

if (!empty($anos)) { $condicion .= " AND date_part('year',p.fecha) IN ($anos) "; }

if ($regimen <> "t") { $condicion .= "AND ('$regimen' IN (car.regimen,car2.regimen,car3.regimen) OR p.nulo) "; }	

$anos_pagos = consulta_sql("SELECT date_part('year',fecha) AS ano FROM finanzas.pagos WHERE date_part('year',fecha) IN ($anos) AND (nro_boleta_e IS NOT NULL OR nro_boleta IS NOT NULL OR nro_factura IS NOT NULL) GROUP BY ano ORDER BY ano");

$cant_anos_pagos = count($anos_pagos);
$colspan_anos_pagos = $cant_anos_pagos * 2;
$HTML_cab = "  <tr class='filaTituloTabla'>\n"
          .      $HTML_cab
          . "    <td class='tituloTabla' colspan='$colspan_anos_pagos'>Años</td>\n"
          . "  </tr>\n"
          . "  <tr class='filaTituloTabla'>\n";
for ($x=0;$x<$cant_anos_pagos;$x++) { $HTML_cab .= "  <td class='tituloTabla' colspan='2'>{$anos_pagos[$x]['ano']}</td>\n"; }
$HTML_cab .= "</tr>\n"
          .  "<tr class='filaTituloTabla'>\n";
for ($x=0;$x<$cant_anos_pagos;$x++) { $HTML_cab .= "  <td class='tituloTabla'><small>Cantidad<br>de Pagos</small></td><td class='tituloTabla'>Total</td>\n"; }
$HTML_cab .= "</tr>\n";
	
$SQL_pagos = "SELECT DISTINCT ON (p.id) p.* 
              FROM finanzas.pagos AS p
              LEFT JOIN vista_usuarios AS u          ON u.id=id_cajero
              LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_pago=p.id 
              LEFT JOIN finanzas.cobros AS cob       ON cob.id=id_cobro 
              LEFT JOIN finanzas.contratos AS c      ON c.id=cob.id_contrato 
              LEFT JOIN finanzas.convenios_ci AS cci ON cci.id=cob.id_convenio_ci
              LEFT JOIN alumnos AS a                 ON a.id=c.id_alumno
              LEFT JOIN alumnos AS a2                ON a2.id=cob.id_alumno
              LEFT JOIN alumnos AS a3                ON a3.id=cci.id_alumno
              LEFT JOIN pap                          ON pap.id=c.id_pap
              LEFT JOIN carreras AS car              ON car.id=c.id_carrera
              LEFT JOIN carreras AS car2             ON car2.id=a2.carrera_actual
              LEFT JOIN carreras AS car3             ON car3.id=a3.carrera_actual
              $condicion ";
                
$SQL_resumen = "SELECT $SQL_agrupador,
                       count(p.id) AS cant_pagos,
                       sum(coalesce(efectivo,0)+coalesce(deposito,0)+coalesce(cheque,0)+coalesce(cheque_afecha,0)+coalesce(transferencia,0)+coalesce(tarj_credito,0)+coalesce(tarj_debito,0)) AS monto_total
                FROM ($SQL_pagos) AS p
                GROUP BY $SQL_groupby
                ORDER BY $SQL_orderby";

$resumen_pagos = consulta_sql($SQL_resumen);
//print_r($resumen_pagos);
$HTML_resumen = "";
$totales = array();
if (count($resumen_pagos) > 0) {
	$x = 0;
	while ($x<count($resumen_pagos)) {
		extract($resumen_pagos[$x]);

		switch ($agrupador) {
			case "M":
				if ($dia == "") { $dia = 1; }
				$fecha = strtotime("$dia-$mes-$ano");
				$mes_palabra = ucfirst(strftime("%B",$fecha));
				$HTML_fila = "    <td class='textoTabla'>$mes_palabra</td>\n";
				break;
			case "M-D":
				$fecha = strtotime("$dia-$mes-$ano");
				$mes_palabra = ucfirst(strftime("%B",$fecha));
				$HTML_fila = "    <td class='textoTabla'>$dia de $mes_palabra</td>\n";
				break;
			case "S":
				$HTML_fila = "    <td class='textoTabla'>$semana</td>\n";
				break;
		}

		for ($y=0;$y<count($anos_pagos);$y++) {
			$ano_pagos = $anos_pagos[$y]['ano'];
			if ($ano_pagos == $resumen_pagos[$x]['ano']) { 
				$totales[$ano_pagos]['cant_pagos']  += $resumen_pagos[$x]['cant_pagos'];
				$totales[$ano_pagos]['monto_total'] += $resumen_pagos[$x]['monto_total'];
				
				
				$monto_total = number_format($resumen_pagos[$x]['monto_total'],0,',','.');				
				$tot_acum = number_format($totales[$ano_pagos]['monto_total'],0,',','.');
				
				$cant_pagos  = "<div title='header=[Cantidad de Pagos Acumulados a la fecha del año $ano_pagos] fade=[on] body=[{$totales[$ano_pagos]['cant_pagos']}]'>{$resumen_pagos[$x]['cant_pagos']}</div>";
				$monto_total = "<div title='header=[Pesos Acumulados a la fecha del año $ano_pagos] fade=[on] body=[$$tot_acum]'>$monto_total</div>";

				$HTML_fila .= "    <td class='textoTabla' style='text-align:center; vertical-align: middle'><small>$cant_pagos</small></td>\n"
				           .  "    <td class='textoTabla' align='right'>$monto_total</td>\n";
				$x++;
			} else {
				$HTML_fila .= "    <td class='textoTabla' align='right'>&nbsp;</td>\n"
				           .  "    <td class='textoTabla' align='right'>&nbsp;</td>\n";
			}
		}
		
		$HTML_resumen .= "  <tr class='filaTabla'>\n"
					  .       $HTML_fila
					  .  "  </tr>\n";
	}
	$HTML_tot = "  <tr class='filaTabla'>\n"
	          . "    <td class='celdaNombreAttr'>Total Año</td>\n";
	for ($y=0;$y<count($anos_pagos);$y++) {
		$ano_pagos   = $anos_pagos[$y]['ano'];
		$monto_total = number_format($totales[$ano_pagos]['monto_total'],0,',','.');
		$HTML_tot .= "    <td class='celdaNombreAttr' style='text-align:center; vertical-align: middle'><small>{$totales[$ano_pagos]['cant_pagos']}</small></td>\n"
	              .  "    <td class='celdaNombreAttr' align='right'>$monto_total</td>\n";
	}
	$HTML_tot .= "  </tr>";

	$HTML_resumen .= $HTML_tot;
} else {
	$HTML_resumen = "  <tr>"
				  . "    <td class='textoTabla' colspan='9'>"
				  . "      No hay registros para los criterios de búsqueda/selección"
				  . "    </td>\n"
				  . "  </tr>";
}

$SQL_cajeros = "SELECT id,apellido||' '||nombre||' ('||nombre_usuario||')' AS nombre 
                FROM usuarios WHERE id IN (SELECT DISTINCT ON (id_cajero) id_cajero FROM finanzas.pagos) ORDER BY apellido,nombre";
$cajeros     = consulta_sql($SQL_cajeros);                

$ANOS = consulta_sql("SELECT min(date_part('year',fecha)) AS ano_min,max(date_part('year',fecha)) AS ano_max FROM finanzas.pagos where not nulo");
$min_ano = $ANOS[0]['ano_min'];
$max_ano = $ANOS[0]['ano_max'];

$HTML_anos = "";
for ($ano=$min_ano;$ano<=$max_ano;$ano++) {
	$checked = "";
	if (in_array($ano,explode(",",$anos))) { $checked = "checked='checked'"; }
	$HTML_anos .= "<input style='vertical-align: bottom;' type='checkbox' name='anos[]' value='$ano' id='$ano' onChange='submitform();' $checked> <label for='$ano'>$ano</label>&nbsp;&nbsp;";
}

$AGRUPADOR = array(array('id'=>"M",  'nombre'=>"Meses"),
                   array('id'=>"M-D",'nombre'=>"Meses y Días"),
                   array('id'=>"S",  'nombre'=>"Semanas"));

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<div class="texto">
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <table cellpadding="2" border="0" cellspacing="1" width="auto" style='margin-top: 5px'>
	  <tr>
	    <td class="celdaFiltro" colspan="10">
          Años:<br>
          <div style='vertical-align: top'><?php echo($HTML_anos); ?></div>
        </td>
	  </tr>
	</table>
    <table cellpadding="2" border="0" cellspacing="1" width="auto" style='margin-top: 5px'>
      <tr>
        <td class="celdaFiltro">
          Mostrar boletas del cajero(a):<br>
          <select name="id_cajero" onChange="submitform();" class="filtro">
            <option value="">Todo(a)s</option>
            <?php echo(select($cajeros,$id_cajero)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Agrupador:<br>
          <select name="agrupador" onChange="submitform();" class="filtro">
            <?php echo(select($AGRUPADOR,$agrupador)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Rendidas:<br>
          <select name="rendidas" onChange="submitform();" class="filtro">
			<option value="0">Todas</option>
            <?php echo(select($sino,$rendidas)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Régimen:<br> 
          <select name="regimen" onChange="submitform();" class="filtro">
            <option value="t">Todos</option>
            <?php echo(select($REGIMENES,$regimen)); ?>
          </select>
        </td>
      </tr>
    </table>
  </form>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <?php echo($HTML_cab); ?>
  <?php echo($HTML_resumen); ?>
</table>


<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 1000,
		'height'			: 400,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 500,
		'height'			: 350,
		'maxHeight'			: 350,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});
</script>
<!-- Fin: <?php echo($modulo); ?> -->
