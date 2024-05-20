<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_pago          = $_REQUEST['id_pago'];

$script_name      = $_SERVER['SCRIPT_NAME'];
$enlbase          = $script_name."?modulo";

$nro_docto         = $_REQUEST['nro_docto'];
$fecha             = $_REQUEST['fecha'];
$observacion       = $_REQUEST['observacion'];
$monto_devolucion  = $_REQUEST['monto_devolucion'];
$monto_nueva_deuda = $_REQUEST['monto_nueva_deuda'];
$reactiva_deuda    = $_REQUEST['reactiva_deuda'];
$total_devolucion  = $_REQUEST['total_devolucion'];
$nro_boleta        = $_REQUEST['nro_boleta'];

if ($_REQUEST['guardar'] == "💾 Guardar") {

	$nc = consulta_sql("SELECT 1 FROM finanzas.notas_credito WHERE nro_docto=$nro_docto");

	if (count($nc) == 0) {
		$id_cajero = $_SESSION['id_usuario'];
		$total_devolucion = intval(str_replace(".","",$total_devolucion));

		// insertar cabecera nc
		$SQL_ins_nc = "INSERT INTO finanzas.notas_credito (nro_docto,fecha,monto,nro_boleta,observacion,id_pago,id_cajero) "
                    . "VALUES ($nro_docto,'$fecha',$total_devolucion,$nro_boleta,'$observacion',$id_pago,$id_cajero)";
		if (consulta_dml($SQL_ins_nc) == 1) {
			$SQL_ins_ns_detalle = "";
			foreach($monto_devolucion AS $id_cobro => $monto_dev) {
				$monto_dev = intval(str_replace(".","",$monto_dev));
				if ($monto_dev > 0) {
					$SQL_ins_ns_detalle .= "INSERT INTO finanzas.notas_credito_detalle (nro_nc_docto,id_cobro,monto) VALUES ($nro_docto,$id_cobro,$monto_dev);";
				}
			}
			echo(msje_js("Se ha registrado correctamente la Nota de Crédito N° $nro_docto."));
			if (consulta_dml($SQL_ins_ns_detalle) > 0) {
				consulta_dml("UPDATE finanzas.pagos SET cod_operacion = cod_operacion || ' NCE/$nro_docto' WHERE id=$id_pago");
				$SQL_ins_cobro = "";
				foreach($reactiva_deuda AS $id_cobro => $valor) {
					$monto_deuda_nueva = 0;
					if ($valor == "on") {
						$monto_deuda_nueva = intval(str_replace(".","",$monto_nueva_deuda[$id_cobro]));
						$SQL_ins_cobro .= "INSERT INTO finanzas.cobros (id_contrato,id_alumno,id_convenio_ci,id_glosa,fecha_venc,nro_cuota,monto)"
						               .  "SELECT id_contrato,id_alumno,id_convenio_ci,id_glosa,fecha_venc,nro_cuota,$monto_deuda_nueva AS monto "
									   .  "FROM finanzas.cobros "
									   .  "WHERE id=$id_cobro;";
					}					
				}
				if (count($reactiva_deuda) > 0 && consulta_dml($SQL_ins_cobro) > 0) {
					echo(msje_js("Se han reactivado la deuda definida en la Nota de Crédito"));
				} else {
					echo(msje_js("ERROR: No se ha podido guardar la reactivación de las deudas."));
				}
			} else {
				echo(msje_js("ERROR: No ha sido posible guardar el detalle de la Nota de Crédito."));
			}
    	} else {
			echo(msje_js("ERROR: No ha sido posible guardar la cabecera de la Nota de Crédito."));
		}
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	} else {
		echo(msje_js("ERROR: El número de documento ingresado ya se encuentra registrado. Debe cambiar el número de Nota de Crédito."));
		$_REQUEST['nro_docto'] = "";
	}	
}

$nc = consulta_sql("SELECT 1 FROM finanzas.notas_credito WHERE id_pago=$id_pago");
if (count($nc) > 0) {
	echo(msje_js("ERROR: Este pago ya tiene una Nota de Crédito asociada.\\n\\n No es posible continuar."));
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$SQL_pago = "SELECT p.id,coalesce(p.nro_boleta,p.nro_boleta_e,nro_factura) AS nro_boleta,
                    coalesce('B/'||p.nro_boleta,'BE/'||p.nro_boleta_e,'F/'||nro_factura) AS nro_docto,
                    to_char(p.fecha,'DD \"de\" tmMonth \"de\" YYYY') AS fecha,u.nombre_usuario AS cajero,cod_operacion,
                    efectivo,deposito,cheque,transferencia,tarj_credito,tarj_debito,cheque_afecha,p.nro_boleta_e,
                    CASE WHEN p.nro_boleta IS NOT NULL THEN 'boleta'
                         WHEN p.nro_boleta_e IS NOT NULL THEN 'boleta_e'
                         WHEN p.nro_factura IS NOT NULL THEN 'factura'
                    END AS tipo_docto,
                    CASE WHEN p.nro_boleta_e IS NOT NULL THEN 1 ELSE 0 END AS es_electronica,
                    CASE WHEN p.nro_boleta_e IS NOT NULL THEN 'Bol-E'
                         WHEN p.nro_boleta IS NOT NULL THEN 'Bol'
                         WHEN p.nro_factura IS NOT NULL THEN 'Fac'
                         ELSE '' 
                    END AS tipo_boleta,
                    CASE WHEN id_arqueo IS NULL THEN 'No' ELSE 'Si' END AS rendida,p.nulo,p.nulo_motivo,to_char(p.nulo_fecha,'DD tmMonth YYYY') AS nulo_fecha,
                    CASE
                      WHEN cob.id_contrato    IS NOT NULL THEN trim(coalesce(a.rut,pap.rut))
                      WHEN cob.id_convenio_ci IS NOT NULL THEN trim(a3.rut)
                      WHEN cob.id_alumno      IS NOT NULL THEN trim(a2.rut)
                    END AS rut_alumno,
                    CASE
                      WHEN cob.id_contrato    IS NOT NULL THEN coalesce(a.apellidos||' '||a.nombres,pap.apellidos||' '||pap.nombres) 
                      WHEN cob.id_convenio_ci IS NOT NULL THEN a3.apellidos||' '||a3.nombres
                      WHEN cob.id_alumno      IS NOT NULL THEN a2.apellidos||' '||a2.nombres  
                    END AS nombre_alumno,
                    CASE
                      WHEN cob.id_contrato    IS NOT NULL THEN car.nombre 
                      WHEN cob.id_convenio_ci IS NOT NULL THEN car3.nombre 
                      WHEN cob.id_alumno      IS NOT NULL THEN car2.nombre  
                    END AS carrera_alumno,
                    CASE
                      WHEN cob.id_contrato    IS NOT NULL THEN c.jornada 
                      WHEN cob.id_convenio_ci IS NOT NULL THEN a3.jornada 
                      WHEN cob.id_alumno      IS NOT NULL THEN a2.jornada  
                    END AS jornada_alumno,to_char(p.fecha_reg,'DD-tmMon-YYYY HH24:MI') as fecha_reg,
                    p.bol_e_respuesta_api,p.bol_e_cod_erp
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
             LEFT JOIN carreras AS car			    ON car.id=c.id_carrera
             LEFT JOIN carreras AS car2			    ON car2.id=a2.carrera_actual
             LEFT JOIN carreras AS car3			    ON car3.id=a3.carrera_actual
             WHERE p.id=$id_pago";
$pago     = consulta_sql($SQL_pago);

if (count($pago) > 0) {

	$efectivo      = number_format($pago[0]['efectivo'],0,",",".");
	$deposito      = number_format($pago[0]['deposito'],0,",",".");
	$cheque        = number_format($pago[0]['cheque'],0,",",".");
	$cheque_afecha = number_format($pago[0]['cheque_afecha'],0,",",".");
	$transferencia = number_format($pago[0]['transferencia'],0,",",".");
	$tarj_credito  = number_format($pago[0]['tarj_credito'],0,",",".");
	$tarj_debito   = number_format($pago[0]['tarj_debito'],0,",",".");
	
	$SQL_pago_detalle = "SELECT c.id AS id_cobro,g.nombre AS glosa,pd.monto_pagado,c.monto,
	                            to_char(c.fecha_venc,'DD-tmMon-YYYY') AS fecha_venc,nro_cuota,g.tipo,
	                            coalesce(id_contrato,id_convenio_ci) AS nro_docto,
	                            coalesce(con.ano,date_part('year',cci.fecha)) AS ano_docto
	                     FROM finanzas.pagos_detalle     AS pd
	                     LEFT JOIN finanzas.cobros       AS c   ON c.id=pd.id_cobro
	                     LEFT JOIN finanzas.glosas       AS g   ON g.id=c.id_glosa
	                     LEFT JOIN finanzas.contratos    AS con ON con.id=c.id_contrato
	                     LEFT JOIN finanzas.convenios_ci AS cci ON cci.id=c.id_convenio_ci
	                     WHERE pd.id_pago=$id_pago
	                     ORDER BY c.fecha_venc";
	$pago_detalle     = consulta_sql($SQL_pago_detalle);
	$monto_total = 0;
	$HTML = "";
	for ($x=0;$x<count($pago_detalle);$x++) {
		extract($pago_detalle[$x]);
		$accion = "Pagó";
		if ($monto_pagado < $monto) { $accion = "Abonó"; } 
		$monto_total += $monto_pagado;
		$fmonto_pagado = number_format($monto_pagado,0,",",".");
		$id_contrato =  number_format($id_contrato,0,",",".");
        
        $monto_devolucion = "<input type='text' size='4' name='monto_devolucion[$id_cobro]' min='0' max='$monto_pagado' value='{$monto_devolucion[$id_cobro]}' tabindex='10$x' onClick=\"this.value='$monto_pagado';\" class='montos monto_devolucion' onBlur=\"js_number_format(this); calc_nueva_deuda($id_cobro,$monto_pagado,this); calc_devolucion();\" required>";
        $monto_nueva_deuda = "<input type='text' size='4' name='monto_nueva_deuda[$id_cobro]' value='' tabindex='20$x' class='montos' readonly>";
        
        $condicion = "";
        if ($tipo <> "3 Otros Ingresos") { 
            $condicion = "checked onClick=\"alert('ERROR: Esta glosa no permite desactivar la reactivación de la deuda.');this.checked=!this.checked; \""; 
        } else {
			$condicion = "checked onClick=\"if (!this.checked) { formulario.elements.namedItem('monto_nueva_deuda[$id_cobro]').value=0; } else { formulario.elements.namedItem('monto_nueva_deuda[$id_cobro]').value=formulario.elements.namedItem('monto_devolucion[$id_cobro]').value; } \""; 
		}
        $reactiva_deuda = "<input type='checkbox' name='reactiva_deuda[$id_cobro]' value='on' tabindex='30$x' onBlur=\"calc_devolucion(); \" $condicion>";

		$HTML .= "<tr>\n"
		      .  "  <td class='textoTabla' style='vertical-align: middle; color: #7F7F7F'>$id_cobro</td>\n"
		      .  "  <td class='textoTabla' style='vertical-align: middle'>$glosa</td>\n"
		      .  "  <td class='textoTabla' style='vertical-align: middle'>$fecha_venc</td>\n"
		      .  "  <td class='textoTabla' style='vertical-align: middle; text-align: center'>$nro_cuota</td>\n"
		      .  "  <td class='textoTabla' style='vertical-align: middle; text-align: center'>$nro_docto</td>\n"
		      .  "  <td class='textoTabla' style='vertical-align: middle; text-align: center'>$ano_docto</td>\n"
		      .  "  <td class='textoTabla' style='vertical-align: middle; text-align: center'>$accion</td>\n"
		      .  "  <td class='textoTabla' style='vertical-align: middle; text-align: right'>$$fmonto_pagado</td>\n"
		      .  "  <td class='textoTabla' style='vertical-align: middle; text-align: right'>$$monto_devolucion</td>\n"
		      .  "  <td class='textoTabla' style='vertical-align: middle; text-align: right'>$$monto_nueva_deuda</td>\n"
		      .  "  <td class='textoTabla' style='vertical-align: middle; text-align: center'>$reactiva_deuda</td>\n"
		      .  "</tr>\n";
	}
	$monto_total = number_format($monto_total,0,",",".");
	
	$boton_registro_cheques = "";
	if ($cheque <> "" || $cheque_afecha <> "") {
		$boton_registro_cheques = "<a href='$enlbase_sm=pago_registrar_cheques&id_pago={$pago[0]['id']}' class='boton'>Registrar Cheque(s)</a>";
	}
} else {
	exit;
}

if ($pago[0]['nulo'] == "t") { $nulo = " <b style='color: red'>NULO</b>"; }
$jornada_alumno = "";
if ($pago[0]['jornada_alumno'] == "D") { $jornada_alumno = "Diurna"; } else { $jornada_alumno = "Vespertina"; }

$fec_min = date("Y-01-01");
$fec_max = date("Y-m-d");

if ($_REQUEST['fecha'] == "") { $fec_act = date("Y-m-d"); } else { $fec_act = $_REQUEST['fecha']; }
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" id="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" metohd="post">
<input type="hidden" name="modulo"     value="<?php echo($modulo); ?>">
<input type="hidden" name="id_pago"    value="<?php echo($id_pago); ?>">
<input type="hidden" name="nro_boleta" value="<?php echo($pago[0]['nro_boleta']); ?>">
<?php if ($id_pago > 0) { ?>
<div style='margin-top: 5px'>
  <input type="submit" name='guardar' value="💾 Guardar">
  <input type="button" name='cancelar' value="❌ Cancelar" onClick="parent.jQuery.fancybox.close();">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes del Documento de Pago</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='color: #7F7F7F'>ID:</td>
    <td class='celdaValorAttr' style='color: #7F7F7F'><?php echo($pago[0]['id']); ?></td>
    <td class='celdaNombreAttr'>Nº <?php echo($pago[0]['tipo_boleta']); ?>:</td>
    <td class='celdaValorAttr'><b><?php echo(number_format($pago[0]['nro_boleta'],0,",",".").$nulo); ?></b></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cajero:</td>
    <td class='celdaValorAttr'><?php echo($pago[0]['cajero']); ?></td>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><a title="Registrada: <?php echo($pago[0]['fecha_reg']); ?>"><?php echo($pago[0]['fecha']); ?></a></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes del Alumno</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr'><?php echo($pago[0]['nombre_alumno']); ?></td>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($pago[0]['rut_alumno']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr'><?php echo($pago[0]['carrera_alumno']); ?></td>
    <td class='celdaNombreAttr'>Jornada:</td>
    <td class='celdaValorAttr'><?php echo($jornada_alumno); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Nota de Crédito</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>N° Docto:</td>
    <td class='celdaValorAttr'><input type="number" name='nro_docto' value='<?php echo($_REQUEST['nro_docto']); ?>' class='boton' required></td>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><input type="date" name='fecha' min='<?php echo($fec_min); ?>' max='<?php echo($fec_max); ?>' value='<?php echo($fec_act); ?>' class='boton' required></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Observación:</td>
    <td class='celdaValorAttr' colspan='3'><textarea name='observacion' class='general' required><?php echo($_REQUEST['observacion']); ?></textarea></td>
  </tr>

<?php if ($pago[0]['nro_boleta_e'] <> "") { ?>  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Mensaje de la API</td></tr>
  <tr><td class='celdaValorAttr' colspan="4"><?php echo($pago[0]['bol_e_respuesta_api']); ?></td></tr>
<?php } ?>
<?php if ($pago[0]['nulo'] == "t") {?>  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Anulación</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($pago[0]['nulo_fecha']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Motivo:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($pago[0]['nulo_motivo']); ?></td>
  </tr>
<?php } ?>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' rowspan="2" style='color: #7F7F7F'>ID<br>Cobro</td>
    <td class='tituloTabla' rowspan="2">Glosa</td>
    <td class='tituloTabla' rowspan="2">Vencimiento</td>
    <td class='tituloTabla' rowspan="2">Nº<br> Cuota</td>
    <td class='tituloTabla' colspan="2">Documento</td>
    <td class='tituloTabla' rowspan="2">Acción</td>
    <td class='tituloTabla' colspan="3">Monto</td>
    <td class='tituloTabla' rowspan="2">Reactivar<br>Deuda</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>N°</td>
    <td class='tituloTabla'>Año</td>
    <td class='tituloTabla'>Pagado</td>
    <td class='tituloTabla'>Devolución</td>
    <td class='tituloTabla'>Nueva deuda</td>
  </tr>
  <?php echo($HTML); ?>
  <tr>
    <td colspan="8" class='celdaNombreAttr' style='text-align: right;''>Total Devolución:</td>
    <td class='celdaValorAttr'>$<input type='text' size='6' name='total_devolucion' class='montos' readonly></td>
  </tr>
  <tr>
    <td colspan="5">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="2" style="text-align: right; ">Monto Boleta</td>
    <td class='celdaValorAttr' style='text-align: right;'><b>$<?php echo($monto_total); ?></b></td>
  </tr>
  <tr>
    <td colspan="5">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="2" style="text-align: right; ">Código Operación</td>
    <td class='celdaValorAttr' style='text-align: right;'><?php echo($pago[0]['cod_operacion']); ?></td>
  </tr>
  <tr>
    <td colspan="5">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="3" style="text-align: center;">Forma de Pago</td>
  </tr>
  <tr>
    <td colspan="5">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="2">Efectivo</td>
    <td class='celdaValorAttr' style='text-align: right;'>$<?php echo($efectivo); ?></td>
  </tr>
  <tr>
    <td colspan="5">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="2">Depósito</td>
    <td class='celdaValorAttr' style='text-align: right;'>$<?php echo($deposito); ?></td>
  </tr>
  <tr>
    <td  colspan="5">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="2">Cheque al día</td>
    <td class='celdaValorAttr' style='text-align: right;'>$<?php echo($cheque); ?></td>
  </tr>
  <tr>
    <td  colspan="5">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="2">Cheque a fecha</td>
    <td class='celdaValorAttr' style='text-align: right;'>$<?php echo($cheque_afecha); ?></td>
  </tr>
  <tr>
    <td  colspan="5">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="2">Transferencia</td>
    <td class='celdaValorAttr' style='text-align: right;'>$<?php echo($transferencia); ?></td>
  </tr>
  <tr>
    <td  colspan="5">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="2">Tarjeta de Crédito</td>
    <td class='celdaValorAttr' style='text-align: right;'>$<?php echo($tarj_credito); ?></td>
  </tr>
  <tr>
    <td colspan="5">&nbsp;</td>
    <td class='celdaNombreAttr' colspan="2">Tarjeta de Débito</td>
    <td class='celdaValorAttr' style='text-align: right;'>$<?php echo($tarj_debito); ?></td>
  </tr>  
</table>
<?php } else { ?>

<?php } ?>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

<script>

function calc_devolucion(id_cobro) {
	var montos_devolucion = new Array();
	var monto_devolucion = document.getElementsByClassName('monto_devolucion'),
	    aMonto_devolucion = [].map.call(monto_devolucion,function(dataInput){montos_devolucion.push(parseInt(dataInput.value.replace(".","").replace(".","").replace(".","")));});
	var x,total_devolucion=0;
    
	for (x=0;x<montos_devolucion.length;x++) {
		if (!isNaN(montos_devolucion[x])) {
			total_devolucion += montos_devolucion[x];
		}
	}    

	var numero_formateado = new Intl.NumberFormat('es-CL');
	formulario.total_devolucion.value=numero_formateado.format(total_devolucion);
}

function calc_nueva_deuda(id_cobro,monto_pagado,monto_devolucion) {
	var monto_devolucion_valor = parseInt(monto_devolucion.value.replace(".","").replace(".","").replace(".","")),
	    monto_nueva_deuda=0,
		monto_nueva_deuda_campo = "monto_nueva_deuda["+id_cobro+"]",
		reactiva_deuda = "reactiva_deuda["+id_cobro+"]";
	var numero_formateado = new Intl.NumberFormat('es-CL');

	if (isNaN(monto_devolucion_valor)) { monto_devolucion_valor = 0; }
	
	if (monto_devolucion_valor > monto_pagado || monto_devolucion_valor < 0) {
		alert("ERROR: Monto de devolución ingresado está fuera del rango. Éste no puede ser inferior a 0 ni superior al monto pagado.");
		monto_devolucion.value="";
	} else {
		monto_nueva_deuda = monto_devolucion_valor;
		if (monto_nueva_deuda == 0) { 
			formulario.elements.namedItem(reactiva_deuda).checked=false;
		} else {
			formulario.elements.namedItem(reactiva_deuda).checked=true;
		}

		formulario.elements.namedItem(monto_nueva_deuda_campo).value = numero_formateado.format(monto_nueva_deuda);
		monto_devolucion.value=numero_formateado.format(monto_devolucion_valor);
	}
}

function js_number_format(campo) {
	var numero_formateado = new Intl.NumberFormat('es-CL');
	campo = parseInt(campo.value.replace(".","").replace(".","").replace(".",""));
	return campo.value=numero_formateado.format(campo.value);
}

</script>