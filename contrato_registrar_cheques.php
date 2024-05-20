<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$id_contrato  = $_REQUEST['id_contrato'];

$SQL_contrato = "SELECT c.id AS id_contrato,c.tipo,car.nombre AS carrera,
						 CASE c.jornada WHEN 'D' THEN 'Diurna' ELSE 'Vespertina' END AS jornada,
						 CASE WHEN c.tipo='Semestral' THEN text(c.semestre)||'-'||text(c.ano) ELSE text(c.ano) END AS periodo,
						 coalesce(a.rut,pap.rut) AS rut_al,coalesce(va.nombre,vp.nombre) AS nombre_al,coalesce(a.id,pap.id) AS id_al,
						 coalesce(coalesce(a.telefono,a.tel_movil),coalesce(pap.telefono,pap.tel_movil)) AS telefono_al
				   FROM finanzas.contratos AS c
				   LEFT JOIN vista_alumnos AS va  ON va.id=c.id_alumno
				   LEFT JOIN alumnos       AS a   ON a.id=va.id
				   LEFT JOIN vista_pap     AS vp  ON vp.id=c.id_pap
				   LEFT JOIN pap                  ON pap.id=vp.id
				   LEFT JOIN carreras      AS car ON car.id=c.id_carrera      
				   WHERE c.estado IS NOT NULL AND c.id=$id_contrato
				   ORDER BY c.fecha DESC";
$contrato  = consulta_sql($SQL_contrato);
if (count($contrato) == 0) {
	echo(msje_js("Este contrato no existe o se encuentra nulo. No es posible continuar."));
	echo(js("history.back();"));
	exit;
}		
extract($contrato[0]);

$disabled = "";

$SQL_cobros = "SELECT c.id,to_char(fecha_venc,'DD-tmMon-YYYY') as fecha_venc,g.nombre AS glosa,monto,nro_cuota,id_contrato AS id_contrato_c,fecha_venc AS fec_venc
			   FROM finanzas.cobros c
			   LEFT JOIN finanzas.glosas g ON g.id=c.id_glosa
			   WHERE c.id_contrato=$id_contrato AND c.id_glosa IN (21,22)
			   ORDER BY c.id_contrato,c.fecha_venc";
$cobros     = consulta_sql($SQL_cobros);
if (count($cobros) == 0) {
	echo(msje_js("Este contrato no fue financiado con cheques. No es posible continuar"));
	echo(js("history.back();"));
	exit;
} else {
	$SQL_cheques = "SELECT if.nombre AS inst_finan,nro_cuenta,numero,rut_emisor,nombre_emisor,telefono_emisor 
	                FROM finanzas.cheques AS ch
	                LEFT JOIN finanzas.inst_financieras AS if on if.codigo=ch.cod_inst_finan
	                WHERE id_cobro IN (SELECT id FROM ($SQL_cobros) AS cobros)
	                ORDER BY id_cobro";
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
			  .   "  <td class='textoTabla' align='center'><input class='boton' type='text' style='width: 50px' name='inst_finan[$y]' value='{$_REQUEST['inst_finan'][$y]}' $onBlur></td>\n"
			  .   "  <td class='textoTabla' align='center'><input class='boton' type='text' style='width: 90px' name='nro_cuenta[$y]' value='{$_REQUEST['nro_cuenta'][$y]}' $onBlur></td>\n"
			  .   "  <td class='textoTabla' align='center'><input class='boton' type='text' style='width: 90px' name='numero[$y]' value='{$_REQUEST['numero'][$y]}' $onBlur></td>\n"
			  .   "  <td class='textoTabla' align='center'><input class='boton' type='text' style='width: 90px' name='rut_emisor[$y]' value='$rut_al' $onBlur onChange='var valor=this.value;this.value=valor.toUpperCase();'></td>\n"
			  .   "  <td class='textoTabla' align='center'><input class='boton' type='text' name='nombre_emisor[$y]' size='20' value='$nombre_al' $onBlur onChange='var valor=this.value;this.value=valor.toUpperCase();'></td>\n"
			  .   "  <td class='textoTabla' align='center'><input class='boton' type='text' style='width: 80px' name='telefono_emisor[$y]' value='$telefono_al' $onBlur></td>\n"
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
	
	$SQL_insCheque = $SQL_insPago_detalles = "";
	for($x=0;$x<count($cobros);$x++) {
		$SQL_insCheque .= "INSERT INTO finanzas.cheques (id_cobro,id_pago,cod_inst_finan,nro_cuenta,numero,monto,fecha_venc,rut_emisor,nombre_emisor,telefono_emisor)
		                               VALUES ({$cobros[$x]['id']},$id_pago,{$aInst_finan[$x]},'{$aNro_cuenta[$x]}',{$aNro_docto[$x]},{$cobros[$x]['monto']},'{$cobros[$x]['fec_venc']}'::date,'{$aRut_emisor[$x]}','{$aNombre_emisor[$x]}','{$aTelefono_emisor[$x]}');";
		$SQL_insPago_detalles .= "INSERT INTO finanzas.pagos_detalle VALUES ($id_pago,{$cobros[$x]['id']},{$cobros[$x]['monto']});";
	}
	
	if (consulta_dml($SQL_insCheque) > 0) {	
		consulta_dml($SQL_insPago_detalles);
		echo(msje_js("Pago registrado correctamente. Imprima el comprobante que se emite a continuación"));
		echo(js("window.open('comprobante_pago.php?id_pago=$id_pago');"));
	} else {
		echo(msje_js("ERROR: No se han registrado los cheques, debido a que la información ingresada contiene errores.\\n\\n"
		            ."Revise el código de banco o institución financiera, el número de cuenta corriente y el/los número(s) de documento(s)."));
		consulta_dml("DELETE FROM finanzas.pagos WHERE id=$id_pago");
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
<input type="hidden" name="id_contrato" value="<?php echo($id_contrato); ?>">

<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla">
  <tr><td class='celdaNombreAttr' colspan="10" style="text-align: center; ">Antecedentes Personales del Alumno(a)</td></tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($rut_al); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($id_al); ?></td>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="6"><?php echo($nombre_al); ?></td>
  </tr>
  <tr>
    <td colspan="2" class='celdaNombreAttr'>Nº Boleta:</td>
    <td colspan="8" class='textoTabla'>
      <input type='text' size='10' class='boton' name='nro_boleta' value="<?php echo($_REQUEST['nro_boleta']); ?>" <?php echo($disabled); ?>><br>
      <sub><?php echo($ultima_nro_boleta); ?></sub>
    </td>
  </tr>
  <tr>
    <td colspan="2" class='celdaNombreAttr'>Fecha:</td>
    <td colspan="8" class='textoTabla'>
      <input type='text' size='10' class='boton' name='fecha_pago' value="<?php echo($_REQUEST['fecha_pago']); ?>" <?php echo($disabled); ?>><br>
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
