<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$id_pago  = $_REQUEST['id_pago'];

$SQL_pago = "SELECT p.id,p.nro_boleta,to_char(p.fecha,'DD tmMonth YYYY') AS fecha,u.nombre_usuario AS cajero,
                    cheque,cheque_afecha,cant_cheques,
                    CASE WHEN id_arqueo IS NULL THEN 'No' ELSE 'Si' END AS rendida,p.nulo,p.nulo_motivo,to_char(p.nulo_fecha,'DD tmMonth YYYY') AS nulo_fecha,
                    CASE
                      WHEN cob.id_contrato    IS NOT NULL THEN coalesce(a.rut,pap.rut)
                      WHEN cob.id_convenio_ci IS NOT NULL THEN a3.rut
                      WHEN cob.id_alumno      IS NOT NULL THEN a2.rut
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
                    END AS jornada_alumno,to_char(p.fecha_reg,'DD-tmMon-YYYY HH24:MI') as fecha_reg
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

$disabled = "";
$SQL_cheques = "SELECT if.nombre AS inst_finan,nro_cuenta,numero,rut_emisor,nombre_emisor,telefono_emisor 
				FROM finanzas.cheques AS ch
				LEFT JOIN finanzas.inst_financieras AS if on if.codigo=ch.cod_inst_finan
				WHERE id_pago = $id_pago
				ORDER BY fecha_venc";
$cheques     = consulta_sql($SQL_cheques);
if (count($cheques) > 0) {
	$SQL_boleta = "SELECT nro_boleta,to_char(fecha,'DD-MM-YYYY') as fecha FROM finanzas.pagos 
				   WHERE id IN (SELECT id_pago FROM finanzas.pagos_detalle
								WHERE id_cobro IN (SELECT id FROM ($SQL_cobros) AS cobros))";
	$boleta     = consulta_sql($SQL_boleta);
	$_REQUEST['nro_boleta'] = $boleta[0]['nro_boleta'];
	$_REQUEST['fecha_pago'] = $boleta[0]['fecha'];
	$disabled   = "disabled";
}

$HTML = "";
$onBlur = "onBlur='copiar_datos_cheque();'";
$monto_total = 0;

if (count($cheques) == 0) {
	for ($y=0;$y<count($cobros);$y++) {			
		extract($cobros[$y]);
		$monto_total += $monto;
		$monto_f = number_format($monto,0,',','.');
		if ($y>0) { $onBlur = ""; }
		
		$HTML .=  "<tr class='filaTabla'>\n"
			  .   "  <td class='textoTabla' align='center'>$fecha_venc</td>\n"
			  .   "  <td class='textoTabla'>$glosa</td>\n"
			  .   "  <td class='textoTabla' align='right'>$nro_cuota</td>\n"
			  .   "  <td class='textoTabla' align='right'>$$monto_f</td>\n"
			  .   "  <td class='textoTabla' align='center'><input class='boton' type='text' name='inst_finan[$y]' size='3' $onBlur></td>\n"
			  .   "  <td class='textoTabla' align='center'><input class='boton' type='text' name='nro_cuenta[$y]' size='10' $onBlur></td>\n"
			  .   "  <td class='textoTabla' align='center'><input class='boton' type='text' name='numero[$y]' size='7' $onBlur></td>\n"
			  .   "  <td class='textoTabla' align='center'><input class='boton' type='text' name='rut_emisor[$y]' size='12' value='$rut_al' $onBlur onChange='var valor=this.value;this.value=valor.toUpperCase();'></td>\n"
			  .   "  <td class='textoTabla' align='center'><input class='boton' type='text' name='nombre_emisor[$y]' size='20' value='$nombre_al' $onBlur onChange='var valor=this.value;this.value=valor.toUpperCase();'></td>\n"
			  .   "  <td class='textoTabla' align='center'><input class='boton' type='text' name='telefono_emisor[$y]' size='10' value='$telefono_al' $onBlur></td>\n"
			  .   "</tr>\n";
	}
} else {
	for ($y=0;$y<count($cheques);$y++) {			
		extract($cobros[$y]);
		extract($cheques[$y]);
		$monto_f = number_format($monto,0,',','.');
		
		$HTML .=  "<tr class='filaTabla'>\n"
			  .   "  <td class='textoTabla' align='center'>$fecha_venc</td>\n"
			  .   "  <td class='textoTabla'>$glosa</td>\n"
			  .   "  <td class='textoTabla' align='right'>$nro_cuota</td>\n"
			  .   "  <td class='textoTabla' align='right'>$$monto_f</td>\n"
			  .   "  <td class='textoTabla'>$inst_finan</td>\n"
			  .   "  <td class='textoTabla'>$nro_cuenta</td>\n"
			  .   "  <td class='textoTabla'>$numero</td>\n"
			  .   "  <td class='textoTabla'>$rut_emisor</td>\n"
			  .   "  <td class='textoTabla'>$nombre_emisor</td>\n"
			  .   "  <td class='textoTabla'>$telefono_emisor</td>\n"
			  .   "</tr>\n";
	}
}

$SQL_boleta = "SELECT nro_boleta FROM finanzas.pagos WHERE id_cajero = {$_SESSION['id_usuario']} ORDER BY fecha DESC LIMIT 1";
$boleta     = consulta_sql($SQL_boleta);
$ultima_nro_boleta = "***";
if (count($boleta) > 0) { $ultima_nro_boleta = "Última boleta: " . $boleta[0]['nro_boleta']; }

if ($_REQUEST['registrar'] == "Registrar") {
	$nro_boleta = $_REQUEST['nro_boleta'];
	$fecha_pago = $_REQUEST['fecha_pago'];
	$boleta_valida = consulta_sqL("SELECT nro_boleta FROM finanzas.pagos WHERE nro_boleta = $nro_boleta");
	if (count($boleta_valida) > 0) {
		echo(msje_js("Número de Boleta ya utilizado. Debe usar otro"));
		exit;
	}
	
	$aInst_finan      = $_REQUEST['inst_finan'];
	$aNro_cuenta      = $_REQUEST['nro_cuenta'];
	$aNro_docto       = $_REQUEST['numero'];
	$aRut_emisor      = $_REQUEST['rut_emisor'];
	$aNombre_emisor   = $_REQUEST['nombre_emisor'];
	$aTelefono_emisor = $_REQUEST['telefono_emisor'];
	
	$cant_cheques = count($cobros);	
	$SQL_insPago = "INSERT INTO finanzas.pagos (cheque,cant_cheques,nro_boleta,id_cajero,fecha)
	                                    VALUES ($monto_total,$cant_cheques,$nro_boleta,{$_SESSION['id_usuario']},'$fecha_pago'::date);";
	if (consulta_dml($SQL_insPago) > 0) {
		$pago    = consulta_sql("SELECT last_value AS id FROM finanzas.pagos_id_seq;");
		$id_pago = $pago[0]['id'];
	} else {
		echo(msje_js("Ha ocurrido un error, no se ha podido guardar la boleta. "
		            ."Por favor comunique este error al Departamento de Informática."));
		exit;
	}	
	
	$SQL_insCheque = "";
	for($x=0;$x<count($cobros);$x++) {
		$SQL_insCheque .= "INSERT INTO finanzas.cheques (id_cobro,id_pago,cod_inst_finan,nro_cuenta,numero,monto,fecha_venc,rut_emisor,nombre_emisor,telefono_emisor)
		                               VALUES ({$cobros[$x]['id']},$id_pago,{$aInst_finan[$x]},'{$aNro_cuenta[$x]}',{$aNro_docto[$x]},{$cobros[$x]['monto']},'{$cobros[$x]['fec_venc']}'::date,'{$aRut_emisor[$x]}','{$aNombre_emisor[$x]}','{$aTelefono_emisor[$x]}');
		                   INSERT INTO finanzas.pagos_detalle VALUES ($id_pago,{$cobros[$x]['id']},{$cobros[$x]['monto']});";
	}
	
	if (consulta_dml($SQL_insCheque) > 0) {	
		echo(msje_js("Pago registrado correctamente. Imprima el comprobante que se emite a continuación"));
		echo(js("window.open('comprobante_pago.php?id_pago=$id_pago');"));
	}
}

$cuotas_TC = array();
for ($x=0;$x<12;$x++) { $cuotas_TC[$x] = array('id'=>$x+1,'nombre'=>$x+1); }
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>

<form name="formulario" action="principal.php" method="get"
      onSubmit="if (!enblanco2(<?php echo($requeridos); ?>) || !val_nota('promedio_col','prom_nt_ies_pro') || !val_psu('puntaje_psu') || !valida_rut(formulario.rut.value)) { return false; }">

<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_pago" value="<?php echo($id_pago); ?>">

<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla">
  <tr><td class='celdaNombreAttr' colspan="6" style="text-align: center; ">Antecedentes Personales del Alumno(a)</td></tr>
  <tr>
    <td class='celdaNombreAttr'><u>RUT:</u></td>
    <td class='celdaValorAttr'><?php echo($rut_al); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($id_al); ?></td>
    <td class='celdaNombreAttr'><u>Nombre:</u></td>
    <td class='celdaValorAttr' colspan="3"><?php echo($nombre_al); ?></td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td colspan="2" class='celdaNombreAttr'>Nº Boleta:</td>
    <td colspan="8" class='textoTabla'>
      <input type='text' size='10' name='nro_boleta' value="<?php echo($_REQUEST['nro_boleta']); ?>" <?php echo($disabled); ?>><br>
      <sub><?php echo($ultima_nro_boleta); ?></sub>
    </td>
  </tr>
  <tr>
    <td colspan="2" class='celdaNombreAttr'>Fecha:</td>
    <td colspan="8" class='textoTabla'>
      <input type='text' size='10' name='fecha_pago' value="<?php echo($_REQUEST['fecha_pago']); ?>" <?php echo($disabled); ?>><br>
      <sup>DD-MM-AAAA</sup>
    </td>
  </tr>
  <tr class='filaTituloTabla'><td colspan="10" class='tituloTabla'>Cobros Asociados</td></tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Fecha<br>Vencimiento</td>    
    <td class='tituloTabla'>Glosa</td>
    <td class='tituloTabla'>Nº<br>Cuota</td>
    <td class='tituloTabla'>Monto</td>
    <td class='tituloTabla'>Inst.<br>Financiera</td>
    <td class='tituloTabla'>Nº Cuenta</td>
    <td class='tituloTabla'>Nº Docto.</td>
    <td class='tituloTabla'>RUT Emisor</td>
    <td class='tituloTabla'>Nombre Emisor</td>
    <td class='tituloTabla'>Teléfono<br>Emisor</td>
  </tr>
  <?php echo($HTML); ?>
  <tr class='filaTituloTabla'>
    <td colspan="10" class='tituloTabla' style="text-align: right">
      <input type='submit' name='registrar' value='Registrar' onClick="return valida_registro();" <?php echo($disabled); ?>>
    </td>
  </tr>
</table>
</form>


<!-- Fin: <?php echo($modulo); ?> -->


<script>
var cant_filas=<?php echo(count($cobros)); ?>,x=0;

function copiar_datos_cheque() {
	for (x=1;x<cant_filas;x++) {		
		document.forms.formulario["inst_finan["+x+"]"].value      = document.forms.formulario["inst_finan[0]"].value;
		document.forms.formulario["nro_cuenta["+x+"]"].value      = document.forms.formulario["nro_cuenta[0]"].value;
		document.forms.formulario["numero["+x+"]"].value          = parseInt(document.forms.formulario["numero[0]"].value)+x;
		document.forms.formulario["rut_emisor["+x+"]"].value      = document.forms.formulario["rut_emisor[0]"].value;
		document.forms.formulario["nombre_emisor["+x+"]"].value   = document.forms.formulario["nombre_emisor[0]"].value;
		document.forms.formulario["telefono_emisor["+x+"]"].value = document.forms.formulario["telefono_emisor[0]"].value;
	}
}

function valida_registro() {
	var problemas=false;
	
	if (document.forms.formulario['nro_boleta'].value == "") {
		alert('Debe ingresar el Nº de Boleta');
		formulario.nro_boleta.focus();
		return false;
	}
	
	for (x=0;x<cant_filas;x++) {
		if (document.forms.formulario["inst_finan["+x+"]"].value == "" ||
		    document.forms.formulario["nro_cuenta["+x+"]"].value  == "" ||
		    document.forms.formulario["numero["+x+"]"].value == "" ||
		    document.forms.formulario["rut_emisor["+x+"]"].value == "" ||
		    document.forms.formulario["nombre_emisor["+x+"]"].value == "" ||
		    document.forms.formulario["telefono_emisor["+x+"]"].value == "") {
				
			problemas = true;
		}
	}
	
	if (problemas) {
		alert("Deben registrarse todos los datos del o de los cheques que se despliegan.");
		return false;
	}
	
	return true;
}
</script>
