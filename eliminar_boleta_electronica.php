<?php
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$nro_docto        = $_REQUEST['nro_docto'];
$tipo_docto       = $_REQUEST['tipo_docto'];
$validar          = $_REQUEST['validar'];
$tipo             = $_REQUEST['tipo'];
$nulo_motivo      = $_REQUEST['nulo_motivo'];
$nulo_tipo_motivo = $_REQUEST['nulo_tipo_motivo'];
$nulo_fecha       = $_REQUEST['nulo_fecha'];
$anular           = $_REQUEST['anular'];

if (empty($tipo_docto)) { $tipo_docto = "boleta"; }

if (!empty($nro_docto) && !empty($validar) && !empty($tipo_docto)) {
	$docto = "nro_$tipo_docto";
	$SQL_pago = "SELECT id,coalesce(nro_boleta,nro_factura,nro_boleta_e) AS nro_docto, 
	                    CASE WHEN nro_boleta   IS NOT NULL THEN 'boleta'
						     WHEN nro_boleta_e IS NOT NULL THEN 'boleta_e'
							 WHEN nro_factura  IS NOT NULL THEN 'factura'
						END AS tipo_docto,nulo,bol_e_cod_erp,
						CASE WHEN now()::date-fecha::date<100 THEN 1 ELSE 0 END AS eliminable
				 FROM finanzas.pagos WHERE $docto=$nro_docto";
	$pago     = consulta_sql($SQL_pago);
	if (count($pago) == 1) {
		if ($pago[0]['nulo'] == "t") { 
			echo(msje_js("ERROR: Este documento ya se encuentra NULO")); 
			echo(js("parent.jQuery.fancybox.close();"));
			exit;
	   } elseif ($pago[0]['eliminable'] == 0 && $pago[0]['bol_e_cod_erp'] <> "") {
			 echo(msje_js("ERROR: Este documento ya se encuentra informado al SII. No se puede eliminar.")); 
			 echo(js("parent.jQuery.fancybox.close();"));
			 exit;
		} else {
			if ($pago[0]['eliminable'] == 1 && $pago[0]['bol_e_cod_erp'] <> "") {
				echo(msje_js("ATENCIÓN: Este documento ya se encuentra informado al ERP.\\m\\n"
				            ."Primero debe eliminarlo del ERP y luego eliminarlo acá.")); 
			}
			$id_pago  = $pago[0]['id'];
			if ($pago[0]['tipo_docto'] == "boleta")   { $nro_boleta   = $nro_docto; }
			if ($pago[0]['tipo_docto'] == "boleta_e") { $nro_boleta_e = $nro_docto; }
			if ($pago[0]['tipo_docto'] == "factura")  { $nro_factura  = $nro_docto; }
		}
	} else {
		echo(msje_js("ERROR: El número de documento que ha ingresado no se encuentra registrado.\\n\\n"
					."No es posible continuar."));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}
}

if ($tipo==md5("reg_nrodocto_inexistente") && !empty($nro_docto) && !empty($nulo_fecha) && !empty($nulo_tipo_motivo) && !empty($anular)) {
	$SQL_inspago = "INSERT INTO finanzas.pagos (nro_boleta,fecha,nulo,nulo_motivo,nulo_fecha,id_cajero) 
											 VALUES ($nro_docto,'$nulo_fecha'::date,true,'$nulo_motivo','$nulo_fecha'::date,{$_SESSION['id_usuario']})";
	if (consulta_dml($SQL_inspago)==1) {
		echo(msje_js("Se ha registrado correctamente el Documento de Pago Nulo"));
		$tipo = $nro_docto = $nulo_fecha = $nulo_motivo = $anular = null;
	}
}

if (($tipo==md5("anulacion") || $tipo==md5("eliminacion")) && !empty($nro_docto) && !empty($nulo_fecha) && !empty($nulo_tipo_motivo) && !empty($anular)) { 
	$pago_detalle_resp = "INSERT INTO finanzas.pagos_eliminados_detalle SELECT * FROM finanzas.pagos_detalle WHERE id_pago=$id_pago";
	$cobros_pago_detalle = consulta_sql("SELECT * FROM finanzas.cobros c LEFT JOIN finanzas.pagos_detalle pd ON pd.id_cobro=c.id WHERE id_pago=$id_pago");
	$SQL_cobros_upd = "";
	for ($x=0;$x<count($cobros_pago_detalle);$x++) {
		extract($cobros_pago_detalle[$x]);
		if ($pagado == "t" && $monto == $monto_pagado && $monto_pagado>0) {
			$SQL_cobros_upd .= "UPDATE finanzas.cobros SET pagado=false WHERE id=$id_cobro;";
		}
		if ($pagado == "t" && $monto > $monto_pagado && $monto_pagado>0) {
			$SQL_cobros_upd .= "UPDATE finanzas.cobros SET pagado=false,abonado=true,monto_abonado=monto-$monto_pagado WHERE id=$id_cobro;";
		}			
		if ($pagado == "f" && $abonado == "t" && $monto_abonado == $monto_pagado && $monto_pagado>0) {
			$SQL_cobros_upd .= "UPDATE finanzas.cobros SET pagado=false,abonado=false,monto_abonado=null WHERE id=$id_cobro;";
		}
		if ($pagado == "f" && $abonado == "t" && $monto_abonado > $monto_pagado && $monto_pagado>0) {
			$SQL_cobros_upd .= "UPDATE finanzas.cobros SET pagado=false,abonado=true,monto_abonado=monto_abonado-$monto_pagado WHERE id=$id_cobro;";
		}
	}
	
	if ($nulo_tipo_motivo <> "Otros") { $nulo_motivo = $nulo_tipo_motivo; }
	
	$SQL_pagos_detalle_del = "DELETE FROM finanzas.pagos_detalle WHERE id_pago=$id_pago";
	
	$SQL_pagos = "UPDATE finanzas.pagos 
				  SET nulo=true,nulo_fecha='$nulo_fecha'::date,nulo_motivo='$nulo_motivo',
					  efectivo=null,cheque=null,cant_cheques=null,transferencia=null,tarj_credito=null,deposito=null,cant_cuotas_tarj_credito=null,tarj_debito=null,cheque_afecha=null,cod_operacion=null
				  WHERE id=$id_pago";
	if ($tipo==md5("eliminacion")) {
		$SQL_pagos = "INSERT INTO finanzas.pagos_eliminados SELECT * FROM finanzas.pagos WHERE id=$id_pago;
		              UPDATE finanzas.pagos_eliminados SET nulo_motivo='$nulo_motivo',nulo_fecha='$nulo_fecha'::date WHERE id=$id_pago;
					  UPDATE finanzas.pagos SET efectivo=null,
					                            transferencia=null,
												cheque=null,
												cant_cheques=null,
												tarj_credito=null,
												tarj_debito=null,
												deposito=null,
												cant_cuotas_tarj_credito=null,
												cheque_afecha=null,
												bol_e_respuesta_api=null,
												bol_e_cod_erp=null,
												bol_e_api_json=null,
												cod_operacion=null
					  WHERE id=$id_pago;";
	}
	
	if (consulta_dml($pago_detalle_resp) > 0) {

		if (consulta_dml($SQL_cobros_upd) > 0) {

			if (consulta_dml($SQL_pagos_detalle_del) > 0) {

				if (consulta_dml($SQL_pagos) > 0) {
					if ($tipo==md5("anulacion")) { echo(msje_js("Se ha registrado la anulación, reactivando la deuda de los cobros respectivos.")); }
					if ($tipo==md5("eliminacion")) { echo(msje_js("Se ha eliminado el pago, reactivando la deuda de los cobros respectivos.")); }
					$tipo = $nro_docto = $nulo_fecha = $nulo_motivo = $anular = null;

				} else { echo(msje_js("ERROR: No se ha podido modificar el pago.")); }

			} else { echo(msje_js("ERROR: No se ha podido modificar el detalle del pago.")); }

		} else { echo(msje_js("ERROR: No se ha podido modificar el estado de los cobros.")); }

	} else { echo(msje_js("ERROR: No se ha podido respaldar el detalle del pago."));}
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

?>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<?php if (!empty($nro_docto) && !empty($validar) && empty($pago[0]['id'])) { ?>
	<form name="formulario" method="post" onSubmit="if (!enblanco2('nro_docto','nulo_fecha','nulo_tipo_motivo','tipo') || (formulario.nulo_tipo_motivo.value=='Otro' && !enblanco2('nulo_motivo'))) { return false; }">
	<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
	<input type="hidden" name="tipo" value="<?php echo(md5("reg_nrodocto_inexistente")); ?>">
	<div style='margin-top: 5px'>
		<input type="submit" name="anular" value="Registrar Anulación" onClick="return confirm('Está seguro de registrar este documento como NULO?');">
		<input type="button" name="Cancelar" value="Cancelar" onClick="">
	</div>
	<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
		<tr>
			<td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes del Registro de Documento Nulo</td>
		</tr>
		<tr>
			<td class='celdaNombreAttr'>Nº Docto:</td>
			<td class='celdaValorAttr'><input type='text' size="12" name='nro_docto' value="<?php echo($nro_docto); ?>" class='filtro' readonly></td>
			<td class='celdaNombreAttr'>Fecha:</td>
			<td class='celdaValorAttr'>
				<input type='date' size='10' name='nulo_fecha' value='<?php echo(date("d-m-Y")); ?>' id='fecha'>
				<script type="text/javascript" charset="utf-8">
					var k4 = new Kalendae.Input('fecha', { format: 'DD-MM-YYYY', weekStart: 1 } );
				</script>
			</td>
		</tr>
		<tr>
			<td class='celdaNombreAttr'><u>Motivo:</u><br><br><u id='nulo_motivo' style='display: none'>Otro:</u></td>
			<td class='celdaValorAttr' colspan='3'>
				<select name='nulo_tipo_motivo' class='filtro' onChange="if (this.value=='Otro') { activar_otro_motivo(); } else { desactivar_otro_motivo(); }" required>
					<option value=''>-- Seleccione --</option>
					<option>Documento corresponde a otro RUT (de estudiante)</option>
					<option>Monto o forma de pago incorrecto</option>
					<option>Error en el número del documento</option>
					<option>Otro</option>
				</select><br><br>
				<textarea name='nulo_motivo' style='display: none' disabled></textarea>
			</td>
		</tr>
	</table>
<?php } elseif (!empty($nro_docto) && !empty($validar) && !empty($pago[0]['id'])) { ?>
	<table width='100%' height='100%'>
	<tr>
		<td valign='top'>
			<form name="formulario" method="post">
			<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
			<input type="hidden" name="validar" value="Validar">
			<div style='margin-top: 5px'>
				<input type="submit" name="anular" value="Aplicar Eliminación" onClick="return confirm('Está seguro de llevar a cabo la eliminación de este documento');">
				<input type="button" name="Cancelar" value="Cancelar" onClick="">
			</div>
			<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
				<tr>
					<td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Eliminación</td>
				</tr>
				<tr>
					<td class='celdaNombreAttr' nowrap>Nº Docto:</td>
					<td class='celdaValorAttr'><input type='text' size="8" name='nro_docto' value="<?php echo($nro_docto); ?>" class='boton' readonly></td>
					<td class='celdaNombreAttr'><u>Fecha:</u></td>
					<td class='celdaValorAttr'>
						<input type='date' size='10' name='nulo_fecha' value='<?php echo(date("Y-m-d")); ?>' class='boton' readonly required>
					</td>
				</tr>
				<tr>
					<td class='celdaNombreAttr'><u>Tipo:</u></td>
					<td class='celdaValorAttr' colspan='3'>
						<input type='radio' name='tipo' value='<?php echo(md5("eliminacion")); ?>' id='eliminacion' required>
						<label for='eliminacion'>
							Eliminar pago<blockquote>Elimina el detalle del pago y deja en 0 el monto de la boleta, reactivando la deuda.</blockquote>
						</label>      
					</td>
				</tr>
				<tr>
					<td class='celdaNombreAttr'><u>Motivo:</u><br><br><u id='nulo_motivo' style='display: none'>Otro:</u></td>
					<td class='celdaValorAttr' colspan='3'>
						<select name='nulo_tipo_motivo' class='filtro' onChange="if (this.value=='Otro') { activar_otro_motivo(); } else { desactivar_otro_motivo(); }" required>
							<option value=''>-- Seleccione --</option>
							<option>RUT (de estudiante) erróneo</option>
							<option>Monto o forma de pago incorrecto</option>
							<option>Otro</option>
						</select><br><br>
						<textarea name='nulo_motivo' style='display: none' disabled></textarea>
					</td>
				</tr>
			</table>
			</form>
		</td>
		<td width='100%' height='100%'>
			<iframe  style='margin-top: 5px' width='100%' height='100%' src='<?php echo("$enlbase_sm=ver_pago&id_pago=$id_pago&impresion=si"); ?>'></iframe>      
		</td>
	</tr>
	</table>
<?php } else { ?>
	<form name="formulario" method="post">
	<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
	<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
		<tr>
			<td class='celdaNombreAttr'>N° Docto:</td>
			<td class='celdaValorAttr'>
				<input type='text' size="12" id='nro_docto' name='nro_docto' value="<?php echo($nro_docto); ?>" class='boton'>
				<script>document.getElementById("nro_docto").focus();</script>
				<input type="submit" name="validar" value="Validar" tabindex="2">
			</td>
		</tr>
	</table>
	</form>
<?php } ?>
<!-- Fin: <?php echo($modulo); ?> -->

<script>
function activar_otro_motivo() {
	formulario.nulo_motivo.disabled=false;
	formulario.nulo_motivo.style.display='inline'; 
	document.getElementById('nulo_motivo').style.display='inline'; 
	formulario.nulo_motivo.focus();
}

function desactivar_otro_motivo() {
	formulario.nulo_motivo.disabled=true; 
	formulario.nulo_motivo.style.display='none'; 
	document.getElementById('nulo_motivo').style.display='none';
}


</script>
