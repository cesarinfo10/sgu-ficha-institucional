<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$rut        = $_REQUEST['rut'];
$validar    = $_REQUEST['validar'];
$rut_valido = false;

if (empty($_REQUEST["EF"])) { $_REQUEST["EF"] = 0; }
if (empty($_REQUEST["CH"])) { $_REQUEST["CH"] = 0; }
if (empty($_REQUEST["TR"])) { $_REQUEST["TR"] = 0; }
if (empty($_REQUEST["TC"])) { $_REQUEST["TC"] = 0; }
if (empty($_REQUEST["TD"])) { $_REQUEST["TD"] = 0; }

if ($_REQUEST['validar'] == "Validar" && $rut <> "") {
	
	$SQL_id_alumno = "SELECT id FROM vista_alumnos WHERE trim(rut)='$rut'";

	$SQL_alumno = "SELECT id,nombre FROM vista_alumnos WHERE trim(rut)='$rut'";
	$alumno     = consulta_sql($SQL_alumno);

	$SQL_pap = "SELECT id,nombre FROM vista_pap WHERE rut='$rut'";
	$pap     = consulta_sql($SQL_pap);
	
	$id_alumno = $id_pap = 0;
	
	if (count($alumno) == 0 && count($pap) == 0) {
		echo(msje_js("El RUT ingresado no corresponde a un alumno o postulante. Intente nuevamente por favor."));
		echo(js("window.location='$enlbase=$modulo';"));
		exit;
	} 
	if (count($pap) == 1) {
		$id_pap = $pap[0]['id'];
		$nombre = $pap[0]['nombre'];
	} 
	if (count($alumno) == 1) {
		$id_alumno = $alumno[0]['id'];
		$nombre    = $alumno[0]['nombre'];
	}
	
	$rut_valido = true;
		
	$SQL_contratos = "SELECT c.id AS id_contrato,c.tipo,car.nombre AS carrera,
	                         CASE c.jornada WHEN 'D' THEN 'Diurna' ELSE 'Vespertina' END AS jornada,
							 CASE WHEN c.tipo='Semestral' THEN text(c.semestre)||'-'||text(c.ano) ELSE text(c.ano) END AS periodo
					   FROM finanzas.contratos AS c
					   LEFT JOIN vista_alumnos AS va  ON va.id=c.id_alumno
					   LEFT JOIN alumnos       AS a   ON a.id=va.id
					   LEFT JOIN vista_pap     AS vp  ON vp.id=c.id_pap
					   LEFT JOIN pap                  ON pap.id=vp.id
					   LEFT JOIN carreras      AS car ON car.id=c.id_carrera      
					   WHERE c.estado IS NOT NULL AND (c.id_alumno IN ($SQL_id_alumno) OR c.id_pap=$id_pap)
					   ORDER BY c.fecha DESC";
	$contratos  = consulta_sql($SQL_contratos);
	if (count($contratos) == 0) {
		echo(msje_js("El RUT ingresado no tiene contratos asociados. No se puede continuar."));
		echo(js("window.location='$enlbase=$modulo';"));
		exit;
	}		

	$SQL_cobros = "SELECT to_char(fecha_venc,'DD-tmMon-YYYY') as fec_venc,c.fecha_venc,g.nombre AS glosa,monto,nro_cuota,id_contrato AS id_contrato_c,
						  CASE WHEN pagado THEN 'Si' ELSE 'No' END AS pagado,
						  CASE WHEN abonado THEN 'Si' ELSE 'No' END AS abonado,
						  monto_abonado
				   FROM finanzas.cobros c
				   LEFT JOIN finanzas.glosas g ON g.id=c.id_glosa
				   WHERE c.id_contrato IN (SELECT id_contrato FROM ($SQL_contratos) AS foo) AND c.id_glosa<>22 AND NOT pagado
				   ORDER BY c.id_contrato,c.fecha_venc";
	$cobros     = consulta_sql($SQL_cobros);
	if (count($cobros) == 0) {
		echo(msje_js("El RUT ingresado tiene contratos emitidos, pero no tiene cobros asociados. "
		            ."Por favor informe esta situación al Departamento de Informática para corregir la información"));
		echo(js("window.location='$enlbase=$modulo';"));
		exit;
	}		

	$HTML_cobros = $HTML_contratos = "";
	for ($x=0;$x<count($contratos);$x++) {
		extract($contratos[$x]);
		
		$HTML_cobros_vencidos = $HTML_cobros = "";
		$deuda_total = $deuda_vencida = 0;
		for ($y=0;$y<count($cobros);$y++) {			
			if ($id_contrato == $cobros[$y]['id_contrato_c']) {
				$HTML = "";
				extract($cobros[$y]);

				if ($pagado == "No") { $deuda_total += $monto; }

				$monto_f = number_format($monto,0,',','.');
				
				$saldo = "";
				if ($abonado == "Si") { $saldo = "($".number_format($monto-$monto_abonado,0,',','.').")"; }
				
				if ($pagado == "Si") { $abonado = ""; }

				$HTML =  "<tr class='filaTabla'>\n"
				      .  "  <td class='textoTabla' align='right'>$fec_venc</td>\n"
				      .  "  <td class='textoTabla'>$glosa</td>\n"
				      .  "  <td class='textoTabla' align='right'>$nro_cuota</td>\n"
				      .  "  <td class='textoTabla' align='right'>$$monto_f</td>\n"
				      .  "  <td class='textoTabla' align='center'><span class='$pagado'>$pagado</span></td>\n"
				      .  "  <td class='textoTabla' align='center'><span class='$abonado'>$abonado <small>$saldo</small></span></td>\n"
				      .  "</tr>\n";
				
				if (strtotime($fecha_venc) <= time()) { 
					$HTML_cobros_vencidos .= $HTML;
					if ($pagado == "No") { $deuda_vencida += $monto; }
				} else {					
					$HTML_cobros .= $HTML;
				}
			}
		}
		if ($HTML_cobros <> "" || $HTML_cobros_vencidos <> "") {
			$_REQUEST['EF'] = $deuda_vencida;
			$deuda_vencida = number_format($deuda_vencida,0,',','.');
			$HTML_cobros_vencidos .= "<tr class='filaTabla'><td class='textoTabla' align='right' colspan='4'>"
								  .  "  <b><span class='No'>Deuda Vencida: $$deuda_vencida</span></b>"
								  .  "</td><td class='textoTabla' colspan='2'> </td></tr>\n";

			$deuda_total = number_format($deuda_total,0,',','.');                      
			$HTML_cobros .= "<tr class='filaTabla'><td class='textoTabla' align='right' colspan='4'>"
						 .  "  <b>Deuda Total <small>(incluye Deuda Vencida)</small>: $$deuda_total</b>"
						 . "</td><td class='textoTabla' colspan='2'> </td></tr>\n";
			
			$datos_contrato = "Nº $id_contrato $id_pagare<br> del Periodo $periodo de Tipo $tipo";
			
			$HTML_contratos .= tabla_contrato_cobros($datos_contrato)
							.  $HTML_cobros_vencidos
							.  $HTML_cobros
							.  "</table><br>\n";
		}
	}
	
	$SQL_boleta = "SELECT nro_boleta FROM finanzas.pagos WHERE id_cajero = {$_SESSION['id_usuario']} ORDER BY fecha DESC LIMIT 1";
	$boleta     = consulta_sql($SQL_boleta);
	$ultima_nro_boleta = "***";
	if (count($boleta) > 0) { $ultima_nro_boleta = "Última boleta: " . $boleta[0]['nro_boleta']; }
	
}



if ($_REQUEST['pagar'] == "Pagar") {
	$id_alumno      = $_REQUEST['id_alumno'];
	$id_pap         = $_REQUEST['id_pap'];
	$nro_boleta     = $_REQUEST['nro_boleta'];
	$EF             = str_replace(".","",$_REQUEST['EF']);
	$CH             = str_replace(".","",$_REQUEST['CH']);
	$cant_cheques   = $_REQUEST['cant_cheques'];
	$TR             = str_replace(".","",$_REQUEST['TR']);
	$TC             = str_replace(".","",$_REQUEST['TC']);
	$cant_cuotas_TC = $_REQUEST['cant_cuotas_TC'];
	$TD             = str_replace(".","",$_REQUEST['TD']);
	
	$boleta_valida = consulta_sqL("SELECT nro_boleta FROM finanzas.pagos WHERE nro_boleta = $nro_boleta AND $nro_boleta<30000");
	if (count($boleta_valida) > 0) {
		echo(msje_js("Número de Boleta ya utilizado. Debe usar otro"));
		exit;
	}
	
	$total_pago = $EF + $CH + $TR + $TC + $TD;
	
	$SQL_id_alumno = "SELECT id FROM vista_alumnos WHERE trim(rut)='$rut'";
	
	$SQL_contratos = "SELECT c.id 
	                  FROM finanzas.contratos AS c
					  LEFT JOIN vista_alumnos AS va  ON va.id=c.id_alumno
					  LEFT JOIN alumnos       AS a   ON a.id=va.id
					  LEFT JOIN vista_pap     AS vp  ON vp.id=c.id_pap
					  LEFT JOIN pap                  ON pap.id=vp.id
					  LEFT JOIN carreras      AS car ON car.id=c.id_carrera      
					  WHERE c.estado IS NOT NULL AND (c.id_alumno IN ($SQL_id_alumno) OR c.id_pap=$id_pap)
					  ORDER BY c.fecha DESC";

	$SQL_cobros = "SELECT c.id,to_char(fecha_venc,'DD-Mon-YYYY') as fecha_venc,g.nombre AS glosa,monto,nro_cuota,id_contrato AS id_contrato_c,
	                      monto_abonado,abonado
				   FROM finanzas.cobros c
				   LEFT JOIN finanzas.glosas g ON g.id=c.id_glosa
				   WHERE NOT pagado AND c.id_contrato IN ($SQL_contratos) 
				   ORDER BY c.fecha_venc,id_contrato";
	$cobros     = consulta_sql($SQL_cobros);

	$SQL_insPago = "INSERT INTO finanzas.pagos (efectivo,cheque,cant_cheques,transferencia,tarj_credito,cant_cuotas_tarj_credito,tarj_debito,nro_boleta,id_cajero)
	                        VALUES ($EF,$CH,$cant_cheques,$TR,$TC,$cant_cuotas_TC,$TD,$nro_boleta,{$_SESSION['id_usuario']});";
	if (consulta_dml($SQL_insPago) > 0) {
		$pago    = consulta_sql("SELECT last_value AS id FROM finanzas.pagos_id_seq;");
		$id_pago = $pago[0]['id']-1;
	} else {
		echo(msje_js("Ha ocurrido un error, no ha podido guardarse la boleta. "
		            ."Por favor comunique este error al Departamento de Informática."));
		exit;
	}	
	
	$SQL_updCobro = "";
	$ids_cobros = array();
	$tot_pago = $total_pago;
	//var_dump($cobros);

	for ($x=0;$x<count($cobros);$x++) {
		$monto = $cobros[$x]['monto'];
		
		$ids_cobros[$x] = $cobros[$x]['id'];
		
		if ($cobros[$x]['abonado'] == "t") { $monto -= $cobros[$x]['monto_abonado']; }
		
		if ($tot_pago < $monto) {			
			$SQL_updCobro .= "UPDATE finanzas.cobros SET abonado=true,monto_abonado=coalesce(monto_abonado,0)+$tot_pago WHERE id={$cobros[$x]['id']};
			                  INSERT INTO finanzas.pagos_detalle ($id_pago,{$cobros[$x]['id']},$monto)";
			$tot_pago -= $monto;
			break;
		}
		if ($tot_pago == $monto) {
			$SQL_updCobro .= "UPDATE finanzas.cobros SET pagado=true,abonado=false,monto_abonado=null WHERE id={$cobros[$x]['id']};
			                  INSERT INTO finanzas.pagos_detalle ($id_pago,{$cobros[$x]['id']},$monto)";
			$tot_pago -= $monto;
			break;
		}
		if ($tot_pago > $monto) {
			$SQL_updCobro .= "UPDATE finanzas.cobros SET pagado=true,abonado=false,monto_abonado=null WHERE id={$cobros[$x]['id']};
			                  INSERT INTO finanzas.pagos_detalle ($id_pago,{$cobros[$x]['id']},$monto)";
			$tot_pago -= $monto;
		}		
	}
	
	consulta_dml($SQL_updCobro);
	//echo($SQL_updCobro);

	
	echo(msje_js("Pago registrado correctamente. Imprima el comprobante que se emite a continuación"));
	echo(js("window.open('comprobante_pago.php?id_pago=$id_pago');"));
}

$cuotas_TC = array();
for ($x=0;$x<12;$x++) { $cuotas_TC[$x] = array('id'=>$x+1,'nombre'=>$x+1); }
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>

<?php	if ($rut_valido) { ?>
<form name="formulario" action="principal.php" method="get"
      onSubmit="if (!enblanco2(<?php echo($requeridos); ?>) || !val_nota('promedio_col','prom_nt_ies_pro') || !val_psu('puntaje_psu') || !valida_rut(formulario.rut.value)) { return false; }">

<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="rut" value="<?php echo($rut); ?>">
<input type="hidden" name="id_alumno" value="<?php echo($id_alumno); ?>">
<input type="hidden" name="id_pap" value="<?php echo($id_pap); ?>">
<input type="hidden" name="deuda_total" value="<?php echo(str_replace(".","",$deuda_total)); ?>">

<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla">
  <tr><td class='celdaNombreAttr' colspan="6" style="text-align: center; ">Antecedentes Personales del Alumno(a)</td></tr>
  <tr>
    <td class='celdaNombreAttr'><u>RUT:</u></td>
    <td class='celdaValorAttr'><?php echo($rut); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($id_alumno); ?></td>
    <td class='celdaNombreAttr'><u>Nombre:</u></td>
    <td class='celdaValorAttr' colspan="3"><?php echo($nombre); ?></td>
  </tr>
</table>
<br>
<?php echo($HTML_contratos); ?>	
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla">
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Forma de pago</td></tr>
  <tr>
    <td class='celdaNombreAttr' rowspan="2">Número Boleta:</td>
    <td class='celdaValorAttr' rowspan="2">
      <input type='text' size='10' name='nro_boleta' value="<?php echo($_REQUEST['nro_boleta']); ?>"><br>
      <sub><?php echo($ultima_nro_boleta); ?></sub>
    </td>
    <td class='celdaNombreAttr'>Efectivo:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='EF' value="<?php echo($_REQUEST['EF']); ?>"
              onBlur="puntitos(this,this.value.charAt(this.value.length-1),this.name); calc_total(this.value);">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cheque(s):</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='CH' value="<?php echo($_REQUEST['CH']); ?>"
              onBlur="puntitos(this,this.value.charAt(this.value.length-1),this.name); calc_total(this.value);">
      <select name="cant_cheques">
        <option value="">Cant:</option>
        <option>1</option>
        <option>2</option>
        <option>3</option>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="3">Transferencia/Depósito:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='TR' value="<?php echo($_REQUEST['TR']); ?>"
              onBlur="puntitos(this,this.value.charAt(this.value.length-1),this.name); calc_total(this.value);">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="3">Tarjeta de Crédito:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='TC' value="<?php echo($_REQUEST['TC']); ?>"
              onBlur="puntitos(this,this.value.charAt(this.value.length-1),this.name); calc_total(this.value);">
      <select name="cant_cuotas_TC">
        <option value="">Cant:</option>
        <?php echo(select($cuotas_TC,$_REQUEST['cant_cuotas_tarj_credito'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="3">Tarjeta de Débito:</td>
    <td class='celdaValorAttr'>
      $<input type='text' class='montos' size='10' name='TD' value="<?php echo($_REQUEST['TD']); ?>"
              onBlur="puntitos(this,this.value.charAt(this.value.length-1),this.name); calc_total(this.value);">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="3">Total Pago:</td>
    <td class='celdaNombreAttr' style='text-align: left'>
      $<input type='text' class='montos' size='10' name='total_pago' value=""
              onChange="puntitos(this,this.value.charAt(this.value.length-1),this.name)" readonly>
      <input type='submit' name='pagar' value='Pagar' onClick="return valida_pago();">
    </td>
  </tr>
</table>
<?php	} else { ?>
<form name="formulario" action="principal.php" method="get" onSubmit="return valida_rut(formulario.rut);">
  <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
  <table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla'>
    <tr>
      <td class='celdaNombreAttr'>RUT:</td>
      <td class='celdaValorAttr'>
        <input type='text' size="12" name='rut' onChange="var valor=this.value;this.value=valor.toUpperCase();" tabindex="1">
        <script>formulario.rut.focus();</script>
        <input type="submit" name="validar" value="Validar" tabindex="2">
      </td>
    </tr>
  </table>
</form>        <option value="">Cant:</option>

<?php	} ?>

<!-- Fin: <?php echo($modulo); ?> -->

<?
function tabla_contrato_cobros($datos_contrato) {
	$HTML = "<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla'>
	           <tr class='filaTituloTabla'><td colspan='6' class='tituloTabla'>Cobros del Contrato $datos_contrato</td></tr>
	           <tr class='filaTituloTabla'>
	             <td class='tituloTabla'>Fecha<br>Vencimiento</td>
	             <td class='tituloTabla' width='200'>Glosa</td>
	             <td class='tituloTabla'>Nº<br>Cuota</td>
	             <td class='tituloTabla'>Monto</td>
	             <td class='tituloTabla'>Pagado?</td>
	             <td class='tituloTabla'>Abon.? <sub>(saldo)</sub></td>
	           </tr>";
	return $HTML;
}
?>
<script>
function calc_total() {
	var EF = document.formulario.EF.value,
	    CH = document.formulario.CH.value,
	    TR = document.formulario.TR.value,
	    TC = document.formulario.TC.value;
	    TD = document.formulario.TD.value;
	    
	document.formulario.total_pago.value = EF.replace('.','').replace('.','')*1 
	                                     + CH.replace('.','').replace('.','')*1
	                                     + TR.replace('.','').replace('.','')*1
	                                     + TC.replace('.','').replace('.','')*1
	                                     + TD.replace('.','').replace('.','')*1;

	puntitos(document.formulario.total_pago,document.formulario.total_pago.value.charAt(document.formulario.total_pago.value.length-1),document.formulario.total_pago.name);
}

calc_total();
puntitos(document.formulario.total_pago,document.formulario.total_pago.value.charAt(document.formulario.total_pago.value.length-1),document.formulario.total_pago.name);
puntitos(document.formulario.EF,document.formulario.EF.value.charAt(document.formulario.EF.value.length-1),document.formulario.EF.name);
document.getElementById("nro_boleta").focus();

function valida_pago() {
	var valida_pago = false,
	    total_pago = document.formulario.total_pago.value;
	total_pago = total_pago.replace('.','').replace('.','')*1;
	if (document.formulario.nro_boleta.value != "") {
		if (total_pago > document.formulario.deuda_total.value) {
			alert("No puede ingresar un monto de pago superior a la Deuda Total");
		} else {
			valida_pago = confirm('Por favor confirme el pago por $'+document.formulario.total_pago.value+' (pinche en Aceptar). \n\n'
		                         +'Una vez confirmado el pago no podrá deshacer la acción.',true,false);
			valida_pago = confirm('Esta seguro de confirmar el pago por $'+document.formulario.total_pago.value+' (pinche en Aceptar). \n\n\n\n\n\n\n\n\n\n\n\n'
		                         +'Una vez confirmado el pago no podrá deshacer la acción.',true,false);

		}
	} else {
		alert('Debe ingresar el Nº de Boleta');
		formulario.nro_boleta.focus();
	}
	return valida_pago;
}
</script>
