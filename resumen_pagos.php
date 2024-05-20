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

$texto_buscar = $_REQUEST['texto_buscar'];
$buscar       = $_REQUEST['buscar'];
$agrupador    = $_REQUEST['agrupador'];
$id_cajero    = $_REQUEST['id_cajero'];
$rendidas     = $_REQUEST['rendidas'];
$regimen      = $_REQUEST['regimen'];
$fec_ini      = $_REQUEST['fec_ini'];
$fec_fin      = $_REQUEST['fec_fin'];

$script_name  = $_SERVER['SCRIPT_NAME'];
$enlbase = $script_name."?modulo";

if (empty($tiempo)) { $tiempo=1; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }
if (empty($agrupador)) { $agrupador = "A-M-D"; }
if (empty($fec_ini)) { $fec_ini = date("Y-m-d"); }
if (empty($fec_fin)) { $fec_fin = date("Y-m-d"); }


$condicion = "WHERE NOT p.nulo AND (nro_boleta IS NOT NULL OR nro_boleta_e IS NOT NULL OR nro_factura IS NOT NULL) ";

$HTML_cab = "";

switch ($agrupador) {
	case "A-M-D":
		$SQL_agrupador = "date_part('year',p.fecha) as ano,date_part('month',p.fecha) as mes,date_part('day',p.fecha) as dia";
		$SQL_groupby   = "date_part('year',p.fecha),date_part('month',p.fecha),date_part('day',p.fecha)";
		$HTML_cab = "    <td class='tituloTabla'>Año</td>\n"
				  . "    <td class='tituloTabla'>Mes</td>\n"
				  . "    <td class='tituloTabla'>Día</td>\n";
		$HTML_tot = "    <td class='celdaNombreAttr' colspan='3'>Total:</td>";

		break;
	case "A-M":
		$SQL_agrupador = "date_part('year',p.fecha) as ano,date_part('month',p.fecha) as mes";
		$SQL_groupby   = "date_part('year',p.fecha),date_part('month',p.fecha)";
		$HTML_cab  = "    <td class='tituloTabla'>Año</td>\n"
				   . "    <td class='tituloTabla'>Mes</td>\n";
		$HTML_tot = "    <td class='celdaNombreAttr' colspan='2'>Total:</td>";
		break;
	case "A-S":
		$SQL_agrupador = "date_part('year',p.fecha) as ano,date_part('week',p.fecha) as semana";
		$SQL_groupby   = "date_part('year',p.fecha),date_part('week',p.fecha)";
		$HTML_cab  = "    <td class='tituloTabla'>Año</td>\n"
				   . "    <td class='tituloTabla'>Semana</td>\n"
				   . "    <td class='tituloTabla'>Inicio</td>\n"
				   . "    <td class='tituloTabla'>Fin</td>\n";
		$HTML_tot = "    <td class='celdaNombreAttr' colspan='4'>Total:</td>";
		break;
}
	
if ($rendidas == "t") {
	$condicion .= "AND id_arqueo IS NOT NULL ";
} elseif ($rendidas == "f") {
	$condicion .= "AND id_arqueo IS NULL ";
}

if ($fec_ini <> "" && $fec_fin <> "") {
	if (strtotime($fec_ini) == -1 || strtotime($fec_fin) == -1) {
		echo(msje_js("Las fechas de búsqueda están mal ingresadas. Por favor use el formato DD-MM-AAAA"));
	} else {
		$condicion .= "AND p.fecha BETWEEN '$fec_ini'::date AND '$fec_fin'::date ";
	}
}

if ($id_cajero > 0) {
	$condicion .= "AND p.id_cajero=$id_cajero ";
}

if ($regimen <> "t") { $condicion .= "AND ('$regimen' IN (car.regimen,car2.regimen,car3.regimen) OR p.nulo) "; }	
	
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
                       sum(efectivo) AS efectivo,
                       sum(deposito) AS deposito,
                       sum(cheque) AS cheque,
                       sum(cheque_afecha) AS cheque_afecha,
                       sum(transferencia) AS transferencia,
                       sum(tarj_credito) AS tarj_credito,
                       sum(tarj_debito) AS tarj_debito
                FROM ($SQL_pagos) AS p
                GROUP BY $SQL_groupby
                ORDER BY $SQL_groupby";
$resumen_pagos = consulta_sql($SQL_resumen);
//print_r($resumen_pagos);
$HTML_resumen = "";
$totales = array();
if (count($resumen_pagos) > 0) {
	for ($x=0;$x<count($resumen_pagos);$x++) {
		extract($resumen_pagos[$x]);
		
		$disponible = $efectivo + $deposito + $transferencia;
		$total      = $disponible + $tarj_credito + $tarj_debito + $cheque + $cheque_afecha;
		$totales['disponible'] += $disponible;
		$totales['total']      += $total;
		$disponible = number_format($disponible,0,',','.');
		$total      = number_format($total,0,',','.');
		
		$totales['cant_pagos']    += $cant_pagos;
		$totales['efectivo']      += $efectivo;
		$totales['deposito']      += $deposito;
		$totales['cheque']        += $cheque;
		$totales['cheque_afecha'] += $cheque_afecha;
		$totales['transferencia'] += $transferencia;
		$totales['tarj_credito']  += $tarj_credito;
		$totales['tarj_debito']   += $tarj_debito;
		
		$efectivo      = number_format($efectivo,0,',','.');
		$deposito      = number_format($deposito,0,',','.');
		$cheque        = number_format($cheque,0,',','.');
		$cheque_afecha = number_format($cheque_afecha,0,',','.');
		$transferencia = number_format($transferencia,0,',','.');
		$tarj_credito  = number_format($tarj_credito,0,',','.');
		$tarj_debito   = number_format($tarj_debito,0,',','.');
		
		switch ($agrupador) {
			case "A-M-D":
				$fecha = strtotime("$dia-$mes-$ano");
//				$f_ini = $f_fin = "$dia-$mes-$ano";
				$mes_palabra = ucfirst(strftime("%B",$fecha));
				$dia_nombre  = ucfirst(strftime("%A %e",$fecha));
				$HTML_fila = "    <td class='textoTabla'>$ano</td>\n"
			               . "    <td class='textoTabla'>$mes_palabra</td>\n"
			               . "    <td class='textoTabla'>$dia_nombre</td>\n";
				break;
			case "A-M":
				$mes_palabra = $meses_palabra[$mes-1]['nombre'];
//				$f_ini = "01-$mes-$ano";
//				$f_fin = date("d",(mktime(0,0,0,$mes+1,1,$ano)-1))."-$mes-$ano";				
				$HTML_fila = "    <td class='textoTabla'>$ano</td>\n"
						   . "    <td class='textoTabla'>$mes_palabra</td>\n";
				break;
			case "A-S":
				$HTML_fila = "    <td class='textoTabla'>$ano</td>\n"
						   . "    <td class='textoTabla'>$semana</td>\n"
						   . "    <td class='textoTabla'>$fec_ini_sem</td>\n"
						   . "    <td class='textoTabla'>$fec_fin_sem</td>\n";
				break;
		}
		
		//$enl    = "$enlbase=gestion_pagos&tiempo=7&fec_ini=$f_ini&fec_fin=$f_fin&id_cajero=$id_cajero&regimen=$regimen";
		$enl    = "$enlbase=gestion_pagos&tiempo=7&fec_ini=$fec_ini&fec_fin=$fec_fin&id_cajero=$id_cajero&regimen=$regimen";
		$enlace = "a class='enlitem' href='$enl'";
		
		
		$HTML_resumen .= "  <tr class='filaTabla' onClick=\"location.href='$enl';\">\n"
		              .  $HTML_fila
					  .  "    <td class='textoTabla' align='right'>$cant_pagos</td>\n"
					  .  "    <td class='textoTabla' align='right'>$efectivo</td>\n"
					  .  "    <td class='textoTabla' align='right'>$deposito</td>\n"
					  .  "    <td class='textoTabla' align='right'>$transferencia</td>\n"
					  .  "    <td class='textoTabla' align='right'>$tarj_debito</td>\n"
					  .  "    <td class='textoTabla' align='right'>$tarj_credito</td>\n"
					  .  "    <td class='textoTabla' align='right'>$cheque</td>\n"
					  .  "    <td class='textoTabla' align='right'>$cheque_afecha</td>\n"
					  .  "    <td class='textoTabla' align='right'><b><i>$disponible</i></b></td>\n"
					  .  "    <td class='textoTabla' align='right'><b>$total</b></td>\n"
					  .  "  </tr>\n";
	}
	
	foreach ($totales AS $var => $valor) { $totales[$var] = number_format($valor,0,',','.'); }
	$HTML_resumen .= "  <tr class='filaTabla'>\n"
		          .  $HTML_tot
	              .  "    <td class='celdaNombreAttr' align='right'>".$totales['cant_pagos']."</td>\n"
	              .  "    <td class='celdaNombreAttr' align='right'>".$totales['efectivo']."</td>\n"
	              .  "    <td class='celdaNombreAttr' align='right'>".$totales['deposito']."</td>\n"
	              .  "    <td class='celdaNombreAttr' align='right'>".$totales['transferencia']."</td>\n"
	              .  "    <td class='celdaNombreAttr' align='right'>".$totales['tarj_debito']."</td>\n"
	              .  "    <td class='celdaNombreAttr' align='right'>".$totales['tarj_credito']."</td>\n"
	              .  "    <td class='celdaNombreAttr' align='right'>".$totales['cheque']."</td>\n"
	              .  "    <td class='celdaNombreAttr' align='right'>".$totales['cheque_afecha']."</td>\n"
	              .  "    <td class='celdaNombreAttr' align='right'><b><i>".$totales['disponible']."</i></b></td>\n"
	              .  "    <td class='celdaNombreAttr' align='right'><b>".$totales['total']."</b></td>\n"
	              .  "  </tr>\n";
} else {
	$HTML_resumen = "  <tr>"
				  . "    <td class='textoTabla' colspan='13'>"
				  . "      No hay registros para los criterios de búsqueda/selección"
				  . "    </td>\n"
				  . "  </tr>";
}

$SQL_cajeros = "SELECT id,apellido||' '||nombre||' ('||nombre_usuario||')' AS nombre 
                FROM usuarios WHERE id IN (SELECT DISTINCT ON (id_cajero) id_cajero FROM finanzas.pagos) ORDER BY apellido,nombre";
$cajeros     = consulta_sql($SQL_cajeros);                

$AGRUPADOR = array(array('id'=>"A-M-D",'nombre'=>"Año, mes y día"),
                   array('id'=>"A-M" ,'nombre'=>"Año y mes"),
                   array('id'=>"A-S" ,'nombre'=>"Año y semana"));

$REGIMENES = consulta_sql("SELECT * FROM regimenes");


?>

<!-- Inicio: <?php echo($modulo); ?> -->
<script src="js/Kalendae/kalendae.standalone.js" type="text/javascript" charset="utf-8"></script>
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<div class="texto">
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
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
    <table cellpadding="2" border="0" cellspacing="1" width="auto" style='margin-top: 5px'>
      <tr>
        <td class="celdaFiltro" colspan="4">
          Periodo a resumir:<br>
          <input type="date" name="fec_ini" value="<?php echo($fec_ini); ?>" size="10" id="fec_ini" class="boton">
          <input type="date" name="fec_fin" value="<?php echo($fec_fin); ?>" size="10" id="fec_fin" class="boton">
          <!-- <script>document.getElementById("fec_ini").focus();</script> -->
          <input type='submit' name='buscar' value='Buscar'> 
        </td>
      </tr>
    </table>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <?php echo($HTML_cab); ?>
    <td class='tituloTabla'><small>Cantidad<br>de Pagos</small></td>
    <td class='tituloTabla'>Efectivo<br><small>(1)</small></td>
    <td class='tituloTabla'>Depósitos<br><small>(2)</small></td>
    <td class='tituloTabla'>Transferecias<br><small>(3)</small></td>
    <td class='tituloTabla'>T. Débito<br><small>(4)</small></td>
    <td class='tituloTabla'>T. Crédito<br><small>(5)</small></td>
    <td class='tituloTabla'>Cheques al Día<br><small>(6)</small></td>
    <td class='tituloTabla'>Cheques a Fecha<br><small>(7)</small></td>
    <td class='tituloTabla'><small>Disponible Inmediato<br>(1) + (2) + (3)</small></td>
    <td class='tituloTabla'>Total</td>
  </tr>
  <?php echo($HTML_resumen); ?>
</table>
</form>

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
