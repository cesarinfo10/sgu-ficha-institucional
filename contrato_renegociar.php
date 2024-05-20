<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_contrato       = $_REQUEST["id_contrato"];
$fecha_repac       = $_REQUEST["fecha_repac"];
$dia_pago          = $_REQUEST["dia_pago"];
$cuotas_repac      = $_REQUEST["cuotas_repac"];
$meses_gracia      = $_REQUEST["meses_gracia"];
$duracion_contrato = $_REQUEST["duracion_contrato"];
$porc_pie_min      = $_REQUEST["porc_pie_min"];

if ($fecha_repac == "")       { $fecha_repac       = date("d-m-Y"); }
if ($meses_gracia == "")      { $meses_gracia      = 1; }
if ($duracion_contrato == "") { $duracion_contrato = 18; }
if ($porc_pie_min == "")      { $porc_pie_min      = 30; }

if (!is_numeric($id_contrato)) {
	echo(js("location.href='$enlbase=gestion_contratos';"));
	exit;
}

// Glosas que son renegociables
//$Ids_glosas = "2,20,21,22,31";
$Ids_glosas = "2,20";

$SQL_cobros = "SELECT sum(coalesce(monto-monto_abonado,monto)) AS total_deuda,count(id) AS total_cuotas
               FROM finanzas.cobros 
               WHERE id_contrato=$id_contrato and id_glosa IN ($Ids_glosas) AND (NOT pagado OR abonado)";
$cobros     = consulta_sql($SQL_cobros);
if ($cobros[0]['total_deuda'] == 0) {
	echo(msje_js("Este contrato está completamente pagado o bien no tiene deuda.\\n"
	            ."No es posible renegociar este contrato"));
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$SQL_contrato = "SELECT c.*,coalesce(va.id,vp.id) AS id,coalesce(va.rut,vp.rut) AS rut,coalesce(va.nombre,vp.nombre) AS nombre,                        
                        to_char(c.fecha,'DD-MM-YYYY') AS fecha,monto_condonacion,coalesce(arancel_diap_cheque,arancel_diap_pagare_coleg) AS diap
                 FROM finanzas.contratos AS c
                 LEFT JOIN alumnos       AS al  ON al.id=c.id_alumno
                 LEFT JOIN vista_alumnos AS va  ON va.id=c.id_alumno
                 LEFT JOIN vista_pap     AS vp  ON vp.id=c.id_pap
                 LEFT JOIN pap                  ON pap.id=c.id_pap
                 WHERE c.id=$id_contrato";
$contrato     = consulta_sql($SQL_contrato);
if (count($contrato) == 0) {
	echo(js("location.href='$enlbase=gestion_contratos';"));
	exit;
}

if ($_REQUEST['guardar'] == "Guardar") {
	$problemas = false;
	if ($monto_pie>0 && $monto_pie > $cobros[0]['total_deuda']) {
		echo(msje_js("El monto del Pie es mayor que la deuda total. Esto no es posible."));
		$problemas = true;
	}
	
	if ($fecha_repac<>"" && strtotime($fecha_repac) < strtotime($contrato[0]['fecha'])) {
		echo(msje_js("La fecha de la renegociación es anterior a la fecha del contrato. Esto no es posible."));		
		$problemas = true;
	}
	
	$monto_pie   = intval(str_replace(".","",$_REQUEST['monto_pie']));
	$saldo_deuda = intval(str_replace(".","",$_REQUEST['saldo_deuda']));		
	if ($cobros[0]['total_deuda'] <> $monto_pie + $saldo_deuda) {
		echo(msje_js("ERROR: No hay consistencia en los saldos.\n\nNo se puede continuar. Saldo Deuda: $saldo_deuda Monto Pie: $monto_pie. Deuda Total: {$cobros[0]['total_deuda']}")); 
		$problemas = true;
	}
	
	if ($cuotas_repac <= 0) {
		echo(msje_js("ERROR: Debe definir una cantidad de cuotas."));
		$problemas = true;
	}

	if (!$problemas) {
				
		$SQL_cobros_resp = "INSERT INTO finanzas.cobros_resp 
		                    SELECT * FROM finanzas.cobros 
		                    WHERE id_contrato = $id_contrato AND id_glosa IN ($Ids_glosas) AND (NOT pagado OR abonado)";
		if (consulta_dml($SQL_cobros_resp) == 0) {
			echo(msje_js("No fue posible establecer un punto de retorno para la operación, por lo que esta no se ha realizado.\\n\\n"
			            ."Por favor avise este error al Departamento de Informática"));
			echo(js("parent.jQuery.fancybox.close();"));
			exit;
		}
		
		
		// Primero eliminar la deuda existente
		$SQL_cobros_cond = "SELECT id,monto,monto_abonado FROM finanzas.cobros 
		                    WHERE id_contrato = $id_contrato AND id_glosa IN ($Ids_glosas) AND (NOT pagado OR abonado)
		                    ORDER BY fecha_venc DESC";
		$cobros_cond  = consulta_sql($SQL_cobros_cond);
		
		$x = 0;
		$SQL_cond = "";
		$monto_condonacion  = $_REQUEST['deuda_total'];
		while ($monto_condonacion > 0) {
			if ($cobros_cond[$x]['monto_abonado'] > 0 && $monto_condonacion >= $cobros_cond[$x]['monto'] - $cobros_cond[$x]['monto_abonado']) {
				$SQL_cond .= "UPDATE finanzas.cobros 
				              SET monto=monto_abonado,pagado=true,abonado=false,monto_abonado=null
				              WHERE id={$cobros_cond[$x]['id']};";
				$monto_condonacion -= $cobros_cond[$x]['monto'] - $cobros_cond[$x]['monto_abonado'];
				$x++;
			} 
			elseif ($cobros_cond[$x]['monto_abonado'] > 0 && $monto_condonacion < $cobros_cond[$x]['monto'] - $cobros_cond[$x]['monto_abonado']) {
				$SQL_cond .= "UPDATE finanzas.cobros 
				              SET monto=monto-$monto_condonacion
				              WHERE id={$cobros_cond[$x]['id']};";
				$monto_condonacion = 0;
				$x++;
			} 
			elseif ($cobros_cond[$x]['monto_abonado'] == 0 && $monto_condonacion >= $cobros_cond[$x]['monto']) {
				$SQL_cond .= "DELETE FROM finanzas.cobros WHERE id={$cobros_cond[$x]['id']};";
				$monto_condonacion -= $cobros_cond[$x]['monto'];
				$x++;
			}
			elseif ($cobros_cond[$x]['monto_abonado'] == 0 && $monto_condonacion < $cobros_cond[$x]['monto']) {
				$SQL_cond .= "UPDATE finanzas.cobros SET monto=monto-$monto_condonacion WHERE id={$cobros_cond[$x]['id']};";
				$monto_condonacion = 0;
				$x++;
			}
			
		}
		consulta_dml($SQL_cond);

		// Generar nuevos cobros (pie)		
		if ($monto_pie > 0) {
			$id_glosa     = 20; // mensualidad de pagare de colegiatura REPACTADA
			$cant_cuotas  = 1;
			$monto_cuota  = $monto_pie;
			$monto_total  = $monto_cuota;
			$fecha_repac  = strtotime($fecha_repac);
			$diap         = date("d",$fecha_repac);
			$mesp         = date("m",$fecha_repac);
			$anop         = date("Y",$fecha_repac);
			$SQL_cobros   = generar_cobros($id_contrato,$id_glosa,$cant_cuotas,$monto_cuota,$monto_total,$diap,$mesp,$anop);
			consulta_sql($SQL_cobros);
		}

		// Generar nuevos cobros (cuotas)
		if ($saldo_deuda > 0) {
			$id_glosa     = 20; // mensualidad de pagare de colegiatura REPACTADA
			$cant_cuotas  = $cuotas_repac;
			$monto_cuota  = str_replace(".","",$_REQUEST['monto_cuota']);
			$monto_total  = $saldo_deuda;
			$fec_1er_venc = strtotime($_REQUEST['fec_1er_venc']);
			$diap         = $dia_pago;
			$mesp         = date("m",$fec_1er_venc);
			$anop         = date("Y",$fec_1er_venc);
			$SQL_cobros   = generar_cobros($id_contrato,$id_glosa,$cant_cuotas,$monto_cuota,$monto_total,$diap,$mesp,$anop);
			consulta_sql($SQL_cobros);
		}
		
		echo(msje_js("Se ha guardado y aplicado la renegociación."));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}		
}

$monto_total = number_format($cobros[0]['total_deuda'],0,',','.');

$SQL_cobros_morosos = "SELECT sum(coalesce(monto-monto_abonado,monto)) AS total_deuda,count(id) AS total_cuotas
                       FROM finanzas.cobros 
                       WHERE id_contrato=$id_contrato AND id_glosa IN ($Ids_glosas) AND (NOT pagado OR abonado) AND fecha_venc < '$fecha_repac'::date";
$cobros_morosos     = consulta_sql($SQL_cobros_morosos);
$monto_moroso       = number_format($cobros_morosos[0]['total_deuda'],0,',','.');

$SQL_cuotas = "SELECT round(date_part('days',((min(fecha_venc)+'$duracion_contrato month'::interval)-'$fecha_repac'::date))/30) AS max_cuotas
               FROM finanzas.cobros 
               WHERE id_contrato=$id_contrato AND id_glosa IN ($Ids_glosas)";
$cuotas     = consulta_sql($SQL_cuotas);
$max_cuotas = $cuotas[0]['max_cuotas'];

$DIAS_PAGO = array(array("id"=>5 ,"nombre"=>"5"),
                   array("id"=>15,"nombre"=>"15"),
                   array("id"=>30,"nombre"=>"30"));

$MESES_GRACIA = array();
for ($x=0;$x<=6;$x++) { $MESES_GRACIA = array_merge($MESES_GRACIA,array(array("id"=>$x, "nombre" => "0$x"))); }

$DURACION = array();
$max_duracion = 72;
if ($_SESSION['tipo'] == 0) { $max_duracion *= 2; } 
for ($x=18;$x<=$max_duracion;$x++) { $DURACION = array_merge($DURACION,array(array("id"=>$x, "nombre" => $x))); }

$CUOTAS = array();
for ($x=1;$x<=$max_cuotas;$x++) { $CUOTAS = array_merge($CUOTAS,array(array("id"=>$x, "nombre" => $x))); }

if ($dia_pago == "") { $dia_pago = $contrato[0]['diap']; }
if ($dia_pago == "") { $dia_pago = 30; }


if ($_REQUEST['monto_pie'] == "") { $_REQUEST['monto_pie'] = round($cobros_morosos[0]['total_deuda'] * ($porc_pie_min/100),0); }

if ($cuotas_repac == "") { $cuotas_repac = $cobros[0]['total_cuotas'] - $cobros_morosos[0]['total_cuotas'] + 1; }
?>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal_sm.php" method="post" onSubmit="validar_repactacion();">
<input type="hidden" name="modulo"         value="<?php echo($modulo); ?>">
<input type="hidden" name="id_contrato"    value="<?php echo($id_contrato); ?>">
<input type="hidden" name="deuda_total"    value="<?php echo($cobros[0]['total_deuda']); ?>">
<input type="hidden" name="deuda_morosa"   value="<?php echo($cobros_morosos[0]['total_deuda']); ?>">
<input type="hidden" name="fecha_contrato" value="<?php echo($contrato[0]['fecha']); ?>">
<div style="margin-top: 5px">
  <input type="submit" name="guardar" value="Guardar">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style="margin-top: 5px">
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Generales del Contrato</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Número:</td>
    <td class='celdaValorAttr'><?php echo($id_contrato); ?></td>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['fecha']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tipo:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['tipo']); ?></td>
    <td class='celdaNombreAttr'>Periodo:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['semestre'].'-'.$contrato[0]['ano']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['rut']); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['id']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($contrato[0]['nombre']); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Deuda</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Monto Moroso:</td>
    <td class='celdaValorAttr' align='right'>$<?php echo($monto_moroso); ?></td>
    <td class='celdaNombreAttr'>Cuotas:</td>
    <td class='celdaValorAttr'><?php echo($cobros_morosos[0]['total_cuotas']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Monto Total:</td>
    <td class='celdaValorAttr' align='right'>$<?php echo($monto_total); ?></td>
    <td class='celdaNombreAttr'>Cuotas:</td>
    <td class='celdaValorAttr'><?php echo($cobros[0]['total_cuotas']); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Renegociación</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr' align='right'>
      <input type='text' size='8' name='fecha_repac' value="<?php echo($fecha_repac); ?>" onBlur='submitform();'><br>
      <sup>DD-MM-AAAA</sup>
    </td>
    <td class='celdaNombreAttr'>Día de pago:</td>
    <td class='celdaValorAttr'><select name='dia_pago' onChange='submitform();' class='filtro'><?php echo(select($DIAS_PAGO,$dia_pago)); ?></select></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>
	  Meses de Gracia:<br>
	  Duración:
	</td>
    <td class='celdaValorAttr'>
      <select name='meses_gracia' class='filtro' onChange='submitform();'><?php echo(select($MESES_GRACIA,$meses_gracia)); ?></select><br>
      <select name='duracion_contrato' class='filtro' onChange='submitform();'><?php echo(select($DURACION,$duracion_contrato)); ?></select>
    </td>
    <td class='celdaNombreAttr'>Pie Mínimo:</td>
    <td class='celdaValorAttr'>
      <input type='text' size='2' name='porc_pie_min' value="<?php echo($porc_pie_min); ?>" onBlur='formulario.monto_pie.value=Math.round(formulario.deuda_morosa.value*(this.value/100),0); submitform();'>%
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Monto pie:</u></td>
    <td class='celdaValorAttr' colspan='3'>
      <b>$</b><input type='text' class='montos' size='7' name='monto_pie' value="<?php echo($_REQUEST['monto_pie']); ?>" 
              onKeyUp="puntitos(this,this.value.charAt(this.value.length-1),this.name);"
              onBlur="val_pie(); calc_pie();">
      <input type='text' size='3' name='porc_pie' value='' class='celdaValorAttr' tabindex='99'
            style='border: none; background: none; text-align: right; padding: 0px' readonly><b style='vertical-align: top'>% del monto moroso</b><br>
      <small>Este monto debe ser cancelado hoy, de otro modo<br> el contrato continuará en estado de morosidad.</small>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Saldo Deuda:</td>
    <td class='celdaValorAttr'>
      <b>$</b><input type='text' size='7' name='saldo_deuda' value='' class='celdaValorAttr' tabindex='100'
              style='border: none; background: none; text-align: right; padding: 0px; width: auto'
              onChange="puntitos(this,this.value.charAt(this.value.length-1),this.name)" readonly>
    </td>
    <td class='celdaNombreAttr'><u>Cuotas:</u></td>
    <td class='celdaValorAttr'>
      <select name='cuotas_repac' class='filtro' onChange='calc_pie();'><?php echo(select($CUOTAS,$cuotas_repac)); ?></select>
      <span style='vertical-align: top'>
        de 
        <b>$<input type='text' size='7' name='monto_cuota' value='' class='celdaValorAttr' tabindex='100'
                style='border: none; background: none; text-align: left; padding: 0px; width: auto'
                onChange="puntitos(this,this.value.charAt(this.value.length-1),this.name)" readonly></b>
      </span>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Primer Venc.:</td>
    <td class='celdaValorAttr'>
      <input type='text' size='8' name='fec_1er_venc' value='' class='celdaValorAttr' tabindex='100'
             style='border: none; background: none; text-align: left; padding: 0px'
             readonly>
    </td>
    <td class='celdaNombreAttr'>Último Venc.:</td>
    <td class='celdaValorAttr'>
      <input type='text' size='8' name='fec_ult_venc' value='' class='celdaValorAttr' tabindex='100'
             style='border: none; background: none; text-align: left; padding: 0px'
             readonly>
    </td>
  </tr>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->

<script>
calc_pie();
puntitos(formulario.monto_pie,formulario.monto_pie.value.charAt(formulario.monto_pie.value.length-1),formulario.monto_pie.name);

function calc_pie() {
	var deuda_morosa = parseInt(formulario.deuda_morosa.value.replace(".","").replace(".","")),
	    deuda_total  = parseInt(formulario.deuda_total.value.replace(".","").replace(".","")),
		monto_pie    = parseInt(formulario.monto_pie.value.replace(".","").replace(".","").replace(".","")),
		porc_pie_min = parseInt(formulario.porc_pie_min.value.replace(".","").replace(".","")),
		meses_gracia = parseInt(formulario.meses_gracia.value.replace(".","").replace(".","")),
		dia_pago     = parseInt(formulario.dia_pago.value.replace(".","").replace(".",""))*1,
		cuotas_repac = parseInt(formulario.cuotas_repac.value.replace(".","").replace(".","")),
		fecha_repac  = formulario.fecha_repac.value,
		fecha_repac  = fecha_repac.split("-"),
		hoy_dia      = fecha_repac[0],
		hoy_mes      = fecha_repac[1]-1,
		hoy_ano      = fecha_repac[2],
		hoy          = new Date(hoy_ano,hoy_mes,hoy_dia),
		mes          = hoy_mes*1+meses_gracia*1,
		ano          = hoy.getFullYear(),
		fec_1er_venc = new Date(ano,mes,dia_pago),
		cuotas_mm    = ((cuotas_repac*1)-1) * 30 * 24 * 60 * 60 * 1000,
		fec_ult_venc = new Date(fec_1er_venc.getTime() + cuotas_mm);

    if (mes == 1 && dia_pago == 30) { fec_1er_venc = new Date(ano,mes,28); }

	if (fec_ult_venc.getMonth() == 1 && dia_pago == 30) { dia_pago = 28; }
	fec_ult_venc.setDate(dia_pago);
		
	formulario.porc_pie.value     = Math.round(monto_pie/deuda_morosa*100,0);
	formulario.saldo_deuda.value  = deuda_total - monto_pie;
	formulario.monto_cuota.value  = Math.round(formulario.saldo_deuda.value / cuotas_repac,0);
	formulario.fec_1er_venc.value = fec_1er_venc.toLocaleDateString().replace("/","-").replace("/","-");
	formulario.fec_ult_venc.value = fec_ult_venc.toLocaleDateString().replace("/","-").replace("/","-");
	
	if (formulario.porc_pie.value < porc_pie_min || formulario.porc_pie.value > 100) { 
		formulario.porc_pie.style='border: none; background: red; text-align: right; padding: 0px; color: white'; 
	} else {
		formulario.porc_pie.style='border: none; background: none; text-align: right; padding: 0px;'; 
	}
	
	puntitos(formulario.saldo_deuda,formulario.saldo_deuda.value.charAt(formulario.saldo_deuda.value.length-1),formulario.saldo_deuda.name);
	puntitos(formulario.monto_cuota,formulario.monto_cuota.value.charAt(formulario.monto_cuota.value.length-1),formulario.monto_cuota.name);
}

function val_pie() {
	var deuda_morosa  = parseInt(formulario.deuda_morosa.value.replace(".","").replace(".","")),
	    deuda_total   = parseInt(formulario.deuda_total.value.replace(".","").replace(".","")),
	    porc_pie_min  = parseInt(formulario.porc_pie_min.value.replace(".","").replace(".","")),
	    porc_pie      = parseInt(formulario.porc_pie.value.replace(".","").replace(".","")),
	    monto_pie     = parseInt(formulario.monto_pie.value.replace(".","").replace(".","")),
		monto_pie_min = 0;

	if (isNaN(deuda_morosa)) { deuda_morosa = 0; }
	monto_pie_min = Math.round(deuda_morosa * (porc_pie_min/100),0);
	
	if (porc_pie < porc_pie_min) { 
		alert("El monto del pie es insuficiente (el pie mínimo es del "+porc_pie_min+"% equivalente a $"+monto_pie_min+".- )");
		formulario.monto_pie.value = monto_pie_min;
		return false;
	}
	
	if (monto_pie > deuda_total) {
		alert("ERROR: El monto del pie es superior al monto de la deuda total.");
		formulario.monto_pie.value = monto_pie_min;
		return false;
	}

	calc_pie();
}

function validar_repactacion() {
	var saldo_deuda = Math.round(parseInt(formulario.saldo_deuda.value.replace(".","").replace(".",""))*1,0),
	    monto_pie   = Math.round(parseInt(formulario.monto_pie.value.replace(".","").replace(".",""))*1,0);
	    
	if (formulario.cuotas_repac.value <= 0) { 
		alert('ERROR: Debe definir una cantidad de cuotas.'); 
		return false		
	} else { 
		if (formulario.deuda_total.value != monto_pie + saldo_deuda) {
			alert('ERROR: No hay consistencia en los saldos.\n\nNo se puede continuar. Deuda Total: '+formulario.deuda_total.value+' Monto Pie + Saldo Deuda:'+monto_pie+saldo_deuda); 
			return false;
		} else {			
			if (!confirm('Está seguro de guardar esta renegociación?')) { 
				return false;
			}
		}
	}
}

</script>
