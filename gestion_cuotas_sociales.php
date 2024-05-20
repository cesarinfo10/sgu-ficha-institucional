<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");
include("validar_modulo.php");

$ano             = $_REQUEST['ano'];
$divisor_valores = $_REQUEST['divisor_valores'];
$dia_corte       = $_REQUEST['dia_corte'];
$mes_corte       = $_REQUEST['mes_corte'];

if ($divisor_valores == "") { $divisor_valores = 1000; }
if ($ano == "") { $ano = date("Y"); }
//if ($ano == "") { $ano = 2016; }
if (empty($_REQUEST['dia_corte'])) { $dia_corte = date("j"); }
if (empty($_REQUEST['mes_corte'])) { $mes_corte = date("n"); }

$enl_nav = "ano=$ano&divisor_valores=$divisor_valores&dia_corte=$dia_corte&mes_corte=$mes_corte";

$cond_ccss = "true";
//if ($dia_corte > 0 && $mes_corte > 0) { $cond_flujo .= " AND p.fecha <= '$dia_corte-$mes_corte-$ano'::date"; }

$SQL_ccss_socio       = "SELECT char_comma_sum(soc.id||'#'||rut||'#'||nombres||' '||apellidos) 
                         FROM finanzas.ccss_socios AS soc
                         LEFT JOIN finanzas.ccss_sociedades_socios AS ss ON ss.id_socio=soc.id
                         WHERE ss.id_sociedad=s.id";

$SQL_ccss_pagados     = "SELECT sum(coalesce(monto_abonado,monto))
                         FROM finanzas.ccss_cobros 
                         WHERE id_compromiso=c.id AND (pagado OR abonado)";

$SQL_ccss_adeudados   = "SELECT sum(coalesce(monto-monto_abonado,monto))
                         FROM finanzas.ccss_cobros 
                         WHERE id_compromiso=c.id AND (NOT pagado OR abonado) AND fecha_venc<=now()";

$SQL_fec_ult_pago     = "SELECT max(p.fecha)
                         FROM finanzas.ccss_pagos p
                         LEFT JOIN finanzas.ccss_pagos_detalle AS pd ON pd.id_pago=p.id
                         LEFT JOIN finanzas.ccss_cobros AS cob ON cob.id=pd.id_cobro
                         WHERE cob.id_compromiso=c.id";

$SQL_ccss_compromisos = "SELECT c.id,c.id_sociedad,s.rut,s.razon_social,($SQL_ccss_socio) AS socio,
                                c.monto,c.monto_adicional,c.monto_descuento,
                                c.monto+coalesce(monto_adicional,0)-coalesce(monto_descuento,0) AS monto_total,
                                ($SQL_ccss_pagados) AS monto_pagado,($SQL_ccss_adeudados) AS monto_adeudado,
                                to_char(($SQL_fec_ult_pago),'DD-tmMon-YYYY') AS fec_ult_pago
                         FROM finanzas.ccss_compromisos AS c
                         LEFT JOIN finanzas.ccss_sociedades AS s ON s.id=c.id_sociedad
                         WHERE $cond_ccss AND c.ano=$ano
                         ORDER BY s.razon_social";

$compromisos = consulta_sql($SQL_ccss_compromisos);
//var_dump($compromisos);
$HTML = "";
$subtotal = array();
for ($x=0;$x<count($compromisos);$x++) {
	extract($compromisos[$x]);
	
	$HTML_socios = "";
	if ($socio <> "") {	
		$socios = explode(",",$socio);
		$aSocio=array();
		for($y=0;$y<count($socios);$y++) { $aSocio[$y] = explode("#",$socios[$y]); }
		for($y=0;$y<count($aSocio);$y++) { 
			$HTML_socios .= "<a href='$enlbase_sm=ccss_ver_socio&id_socio={$aSocio[$y][0]}' title='Ver detalles del Representante'id='sgu_fancybox_small' class='enlaces'>{$aSocio[$y][1]}</a><br>{$aSocio[$y][2]}<br>";
		}
	}	

	$acciones = "<span id='bo$x' style='visibility: hidden'>"
			  . "  <a href='$enlbase_sm=ccss_pagar&rut=$rut&id_sociedad=$id_sociedad&ano=$ano' title='Registrar pago de cuota social' class='boton' id='sgu_fancybox'><small>Pago</small></a> "
			  . "  <!-- <a href='$enlbase_sm=ccss_condonar&id_compromiso=$id' title='Descontar el monto del Compromiso' class='boton' id='sgu_fancybox_small'><small>Dscto</small></a> --> "
			  . "</span>";

	$monto_pendiente = $monto_total - $monto_pagado;

	$subtotal['total']     += $monto_total;
	$subtotal['pagado']    += $monto_pagado;
	$subtotal['adeudado']  += $monto_adeudado;
	$subtotal['pendiente'] += $monto_pendiente;

	$m_total     = money_format("%(#7.0n",round($monto_total/$divisor_valores,0));
	$m_pagado    = money_format("%(#7.0n",round($monto_pagado/$divisor_valores,0));
	$m_adeudado  = money_format("%(#7.0n",round($monto_adeudado/$divisor_valores,0));
	$m_pendiente = money_format("%(#7.0n",round($monto_pendiente/$divisor_valores,0));
	
	if ($monto_adeudado > 0) { $m_adeudado = "<span class='no'>$m_adeudado</span>"; }
	
	$id  = "<a href='$enlbase_sm=ccss_ver_compromiso&id_compromiso=$id' title='Ver detalles del compromiso' id='sgu_fancybox' class='enlaces'> $id</a>";
	$rut = "<a href='$enlbase_sm=ccss_ver_sociedad&id_sociedad=$id_sociedad' title='Ver detalle e información histórica de la sociedad' id='sgu_fancybox_small' class='enlaces'>$rut</a>";
	$razon_social = wordwrap($razon_social,40,"<br>");
	
	$HTML .= "<tr class='filaTabla' onMouseOver=\"document.getElementById('bo$x').style.visibility='visible'\" onMouseOut=\"document.getElementById('bo$x').style.visibility='hidden'\">\n"
		  .  "  <td class='textoTabla' align='right' nowrap>$id<br>$acciones</td>\n"
		  .  "  <td class='textoTabla'>$rut<br>$razon_social</td>\n"
		  .  "  <td class='textoTabla'>$HTML_socios</td>\n"
		  .  "  <td class='textoTabla' align='right' nowrap>$m_total</td>\n"
		  .  "  <td class='textoTabla' align='right' nowrap><span class='si'>$m_pagado</span></td>\n"
		  .  "  <td class='textoTabla' align='right' nowrap>$m_adeudado</td>\n"
		  .  "  <td class='textoTabla' align='right' nowrap>$m_pendiente</td>\n"
		  .  "  <td class='textoTabla' align='right' nowrap>$fec_ult_pago</td>\n"
		  .  "</tr>\n";
}


$DIVISORES = array(array('id'=>1      ,'nombre'=>"en pesos"),
                   array('id'=>1000   ,'nombre'=>"en miles de pesos"),
                   array('id'=>1000000,'nombre'=>"en millones de pesos"));

$ANOS_compromisos = consulta_sql("SELECT ano AS id,ano AS nombre FROM finanzas.ccss_compromisos GROUP BY ano ORDER BY ano DESC");

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<!-- <input type="hidden" name="ano" value="<?php echo($ano); ?>"> -->

<table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
  <tr>
	<td class="celdaFiltro">
      Año Compromiso:<br>
      <select name='ano' onChange='submitform()' class='filtro'>
        <?php echo(select($ANOS_compromisos,$ano)); ?>
      </select>
    </td>
	<td class="celdaFiltro">
      Mostrar valores:<br>
      <select name="divisor_valores" onChange="submitform();" class="filtro">
        <?php echo(select($DIVISORES,$divisor_valores)); ?>
      </select>
    </td>
<!--  	<td class="celdaFiltro">
      Agregar:<br>
      <a id="sgu_fancybox" href='<?php echo("$enlbase_sm=ccss_nueva_sociedad"); ?>' class='boton'>Sociedad</a>
      <a id="sgu_fancybox" href='<?php echo("$enlbase_sm=ccss_nuevo_socio"); ?>' class='boton'>Socio</a>
      <a id="sgu_fancybox" href='<?php echo("$enlbase_sm=ccss_agregar_compromiso"); ?>' class='boton'>Compromiso</a>
      <a id="sgu_fancybox" href='<?php echo("$enlbase_sm=ccss_agregar_cuota_extraordinaria"); ?>' class='boton'>Cuota Extraordinaria</a>
    </td> -->
  	<td class="celdaFiltro">
      Acciones:<br>
<!--      <a id="sgu_fancybox" href='<?php echo("$enlbase_sm=ccss_crear_compromisos"); ?>' class='boton'>Crear nuevo Año</a> -->
      <a id="sgu_fancybox" href='<?php echo("$enlbase_sm=flujos_generales_ingresos_resumen&ano=$ano&ccss=t"); ?>' class='boton'>Ver Flujo de Pagos</a>
    </td>
  </tr>
</table>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='1' class='tabla' style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr' colspan="2" style='text-align: center'>Resumen de Compromisos para el Año <?php echo($ano); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' width='40%'>Monto Comprometido:</td>
    <td class='celdaValorAttr' width='60%' align='right'><?php echo(money_format("%(#7.0n",round($subtotal['total']/$divisor_valores,0))); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Monto Pagado:</td>
    <td class='celdaValorAttr' align='right'><span class='si'><?php echo(money_format("%(#7.0n",round($subtotal['pagado']/$divisor_valores,0))); ?></span></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Monto Adeudado:</td>
    <td class='celdaValorAttr' align='right'><span class='no'><?php echo(money_format("%(#7.0n",round($subtotal['adeudado']/$divisor_valores,0))); ?></span></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Monto Pendiente:</td>
    <td class='celdaValorAttr' align='right'><?php echo(money_format("%(#7.0n",round($subtotal['pendiente']/$divisor_valores,0))); ?></td>
  </tr>
</table>

<table bgcolor='#ffffff' cellspacing='1' cellpadding='1' class='tabla' style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="8">Detalle de Compromisos para el Año <?php echo($ano); ?></td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' rowspan="2">N°</td>
    <td class='tituloTabla' rowspan="2">Sociedad</td>
    <td class='tituloTabla' rowspan="2">Representante(s)</td>
    <td class='tituloTabla' colspan="4">Montos</td>
    <td class='tituloTabla' rowspan="2">Fecha del<br>último pago</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' nowrap>Comprometido</td>
    <td class='tituloTabla' nowrap>Pagado</td>
    <td class='tituloTabla' nowrap>Adeudado</td>
    <td class='tituloTabla' nowrap>Pendiente</td>
  </tr>
  <?php echo($HTML); ?>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: true,
		'titleShow'         : false,
		'titlePosition'     : 'inside',
		'transitionIn'		: 'elastic',
		'transitionOut'		: 'elastic',
		'width'				: 1000,
		'height'			: 550,
		'maxHeight'			: 600,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: true,
		'titleShow'         : false,
		'titlePosition'     : 'inside',
		'transitionIn'		: 'elastic',
		'transitionOut'		: 'elastic',
		'width'				: 600,
		'height'			: 550,
		'maxHeight'			: 550,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});
</script>

<?php

function sumar_arr($a, $b) {
	for ($x=0;$x<count($a);$x++) { $sum[$x] = $a[$x] + $b[$x]; }
    return $sum;
}

?>
