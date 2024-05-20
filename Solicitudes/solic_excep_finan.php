<?php

$id_contrato = $_REQUEST['id_contrato'];

if (empty($forma)) { $forma = $_REQUEST['forma']; }

if (empty($id_alumno)) { $id_alumno = $_REQUEST['id_alumno']; }

if (is_numeric($id_alumno)) {
	$SQL_alumno = "SELECT a.id AS id_alumno,rut,nombres,apellidos,c.nombre AS carrera,
						  CASE a.jornada WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada,
						  semestre_cohorte||'-'||cohorte AS cohorte,a.id_pap
				   FROM alumnos AS a
				   LEFT JOIN carreras AS c ON c.id=a.carrera_actual
				   WHERE a.id=$id_alumno";
	$alumno = consulta_sql($SQL_alumno);
	$_REQUEST = array_merge($_REQUEST,$alumno[0]);

	$id_pap        = $alumno[0]['id_pap'];
	$SQL_contratos = "SELECT id,coalesce(semestre||'-','')||ano AS periodo,monto_saldot,monto_moroso,arancel_cuotas_pagare_coleg
					  FROM vista_contratos 
					  WHERE (id_alumno=$id_alumno OR id_pap=$id_pap) AND estado IS NOT NULL AND monto_saldot > 0 AND monto_moroso > 0
					  ORDER BY ano,semestre";
	$CONTRATOS     = consulta_sql($SQL_contratos);

  	if ($_REQUEST['crear'] == "Guardar") {

		$tel_movil   = $_REQUEST['tel_movil'];
		$telefono    = $_REQUEST['telefono'];
		$email       = $_REQUEST['email'];
		$comentarios = $_REQUEST['comentarios'];

		if (empty($comentarios)) { $comentarios = "** No registra comentarios **"; }
		
		$id_tipo   = "(SELECT id FROM gestion.solic_tipos WHERE alias='$tipo_solic')";

		$SQL_ins = "INSERT INTO gestion.solicitudes (id_alumno,telefono,tel_movil,email,id_tipo,comentarios) VALUES ($id_alumno,$telefono,$tel_movil,'$email',$id_tipo,'$comentarios')";
		if (consulta_dml($SQL_ins) > 0) {
			$id_solicitud = consulta_sql("SELECT id FROM gestion.solicitudes WHERE id_alumno=$id_alumno ORDER BY id DESC LIMIT 1");
			$id_solicitud = $id_solicitud[0]['id'];
		}
		$monto_saldot = $_REQUEST['monto_saldot'];
		$monto_moroso = $_REQUEST['monto_moroso'];
		$monto_pie    = $_REQUEST['monto_pie'];
		$nro_cuotas   = $_REQUEST['nro_cuotas'];

		list($anop_ini,$mesp_ini,$diap_ini) = explode("-",$_REQUEST['contratos_1er_venc']);
/*
		$diap_ini     = $_REQUEST['diap_ini'];
		$mes_ano_pago = $_REQUEST['mes_ano_pago'];
		$mesp_ini     = "split_part('$mes_ano_pago','-',2)::int2";
		$anop_ini     = "split_part('$mes_ano_pago','-',1)::int2";
*/

		$monto_cuota  = $_REQUEST['monto_cuota'];
		
		$SQL_ins = "";
		foreach ($monto_pie AS $id_contrato => $monto_p ) {
			$monto_p = str_replace(".","",$monto_p);
			$monto_c = str_replace(".","",$monto_cuota[$id_contrato]);
			$saldo_t = str_replace(".","",$monto_saldot[$id_contrato]);
			$monto_m = str_replace(".","",$monto_moroso[$id_contrato]);
			$SQL_ins .= "INSERT INTO gestion.solic_excep_finan (id_solicitud,id_contrato,monto_saldot,monto_moroso,monto_pie,nro_cuotas,monto_cuota,diap_ini,mesp_ini,anop_ini) "
				     .  "VALUES ($id_solicitud,$id_contrato,$saldo_t,$monto_m,$monto_p,$nro_cuotas,$monto_c,$diap_ini,$mesp_ini,$anop_ini);";
		}
		consulta_dml($SQL_ins);

		echo(msje_js("ATENCIÓN: Estás guardando un borrador de tu solicitud.\\n\\n"
					."A continuación se mostrará los datos que estás registrando y "
					."luego debes pinchar en el botón «Presentar» para que esta solicitud sea recibida y evaluada."));
		echo(js("location='$enlbase_sm=solicitudes_ver&id_solic=$id_solicitud&id_alumno=$id_alumno&tipo=$tipo_solic';"));
		exit;
	}

	if (count($CONTRATOS) > 0) {
		$HTML = "";
		$NRO_CUOTAS = array();
		$max_nro_cuotas = max(array_column($CONTRATOS,'arancel_cuotas_pagare_coleg'));
		for ($x=1;$x<=$max_nro_cuotas;$x++) { $NRO_CUOTAS = array_merge($NRO_CUOTAS,array(array("id" => $x,"nombre" => $x))); }

		$DIASP_INI = array(array("id" => 5, "nombre" => 5),
						array("id" => 15,"nombre" => 15),
						array("id" => 30,"nombre" => 30));

		$venc_min = date('Y-m');
		$venc_max = date('Y-m', strtotime('+60 days'));

		$tot_monto_saldot = $tot_monto_moroso = $tot_monto_pie = $tot_monto_cuota = $tot_monto_apagar = 0;

		$filas_contratos = count($CONTRATOS);

		$ids_contrato = array();

		for($x=0;$x<$filas_contratos;$x++) {
			$readonly = ""; $disabled = "";
			if ($x>0) { $readonly = "readonly"; $disabled = "disabled"; }

			extract($CONTRATOS[$x]);
			$monto_pie    = round($monto_moroso*0.5)+1;			
			$nro_cuotas   = nro_cuotas(round($monto_pie*100/($monto_moroso+1)));
			$monto_cuota  = round(($monto_saldot - $monto_pie)/$nro_cuotas);
			$monto_apagar = $monto_pie + $monto_cuota*$nro_cuotas;

			$contratos_1er_venc     = date("Y-m-d",strtotime(date("Y-").date("m")."-05 + 1 months"));
			$contratos_1er_venc_max = date("Y-m-d",strtotime(date()." + 2 months"));

			$tot_monto_saldot += $monto_saldot;
			$tot_monto_moroso += $monto_moroso;
			$tot_monto_pie    += $monto_pie;
			$tot_monto_cuota  += $monto_cuota;
			$tot_monto_apagar += $monto_apagar;

			$monto_saldot = number_format($monto_saldot,0,",",".");
			$monto_moroso = number_format($monto_moroso,0,",",".");

			$monto_saldot = "<input type='hidden' name='monto_saldot[$id]' value='$monto_saldot'>$monto_saldot";
			$monto_moroso = "<input type='hidden' name='monto_moroso[$id]' value='$monto_moroso'>$monto_moroso";
			$monto_pie    = "<input type='text' size='5' id='$id' name='monto_pie[$id]' value='$monto_pie' class='montos' onBlur=\"js_number_format(this); calc_tot_pie(); calc_nro_cuotas(); calc_cuotas($id);\" required>";
			$nro_cuotas   = "<select name='nro_cuotas' class='filtro' onChange='calc_cuotas($id);' required>".select($NRO_CUOTAS,$nro_cuotas)."</select>";

			$venc_1ro     = "<input type='date' name='contratos_1er_venc' value='$contratos_1er_venc' min='$contratos_1er_venc' max='$contratos_1er_venc_max' onChange='valida_1er_venc(this);' class='boton'><br>";
/*
			$venc_1ro     = "<select name='diap_ini' class='filtro' required>".select($DIASP_INI,$diap_ini)."</select> de "
							. "<input type='month' size='5' name='mes_ano_pago' class='botoncito' min='$venc_min' max='$venc_max' required>";
*/
			$monto_cuota  = "<input type='text' size='5' name='monto_cuota[$id]' value='$monto_cuota' class='montos' readonly>";
			$total_apagar = "<input type='text' size='5' name='monto_apagar[$id]' value='$monto_apagar' class='montos' readonly>";

			$HTML .= "    <tr class='filaTabla'>\n"
				  .  "      <td class='textoTabla' align='center'>$id</td>\n"
				  .  "      <td class='textoTabla' align='center'>$periodo</td>\n"
				  .  "      <td class='textoTabla' align='right'>$monto_saldot</td>\n"
				  .  "      <td class='textoTabla' align='right'>$monto_moroso</td>\n"
				  .  "      <td class='textoTabla' align='right'>$monto_pie</td>\n";

			$ids_contrato[$x] = $id;
	  
			if ($x == 0) {
				$HTML .= "      <td class='textoTabla' rowspan='$filas_contratos' style='vertical-align: middle; text-align: center'>$nro_cuotas</td>\n"
					.  "      <td class='textoTabla' rowspan='$filas_contratos' style='vertical-align: middle; text-align: center'>$venc_1ro</td>\n";
			}

			$HTML .= "      <td class='textoTabla' align='right'>$monto_cuota</td>\n"
					.  "      <td class='textoTabla' align='right'>$total_apagar</td>\n"
					.  "    </tr>\n";
		}

		$tot_monto_saldot = "<input type='hidden' name='tot_monto_saldot' value='$tot_monto_saldot'>".number_format($tot_monto_saldot,0,",",".");
		$tot_monto_moroso = "<input type='hidden' name='tot_monto_moroso' value='$tot_monto_moroso'>".number_format($tot_monto_moroso,0,",",".");
		$tot_monto_pie    = "<input type='text' size='5' name='tot_monto_pie'    value='$tot_monto_pie'    class='montos' readonly>";
		$tot_monto_cuota  = "<input type='text' size='5' name='tot_monto_cuota'  value='$tot_monto_cuota'  class='montos' readonly>";
		$tot_monto_apagar = "<input type='text' size='5' name='tot_monto_apagar' value='$tot_monto_apagar' class='montos' readonly>";

		$HTML .= "    <tr>\n"
			  .  "      <td class='celdaNombreAttr' valign='botton' align='right' colspan='2'>Total:</td>\n"
			  .  "      <td class='celdaNombreAttr' valign='botton' align='right'>$tot_monto_saldot</td>\n"
			  .  "      <td class='celdaNombreAttr' valign='botton' align='right'>$tot_monto_moroso</td>\n"
			  .  "      <td class='celdaNombreAttr' valign='botton' align='right'>$tot_monto_pie</td>\n"
			  .  "      <td class='celdaNombreAttr' valign='botton' style='text-align: left' colspan='2'><span id='estado_pie'></span></td>\n"
			  .  "      <td class='celdaNombreAttr' valign='botton' align='right'>$tot_monto_cuota</td>\n"
			  .  "      <td class='celdaNombreAttr' valign='botton' align='right'>$tot_monto_apagar</td>\n"
			  .  "    </tr>\n";

		$HTML_contratos = $HTML;
	} else {
		//echo(msje_js("ATENCIÓN: Actualmente no tienes contratos vigentes con deuda activa."));
		echo(msje_js("ATENCIÓN: Actualmente no tienes contratos vigentes con deuda vencida."));
		echo(js("parent.jQuery.fancybox.close()"));
		exit;
	}
}

?>

<div style='margin-top: 5px'>
<?php

if ($forma == 'editar') {
	echo("  <input type='submit' name='editar' value='Guardar' tabindex='99'>\n");
} else {
	echo("  <input type='submit' name='crear' value='Guardar' tabindex='99'>\n");
}

?>  
  <input type="button" name="cancelar" value="Cancelar" onclick="history.back();">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr>
	<td class='celdaNombreAttr'>Tipo de Solicitud:</td>
	<td class='celdaValorAttr'><?php echo($nombre_tipo_solic); ?></td>
	<td class='celdaNombreAttr'>Estado Solicitud:</td>
	<td class='celdaValorAttr'>En preparación</td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales y Curriculares</td></tr>
  <tr>
	<td class='celdaNombreAttr'>RUT:</td>
	<td class='celdaValorAttr'><?php echo($_REQUEST['rut']); ?></td>
	<td class='celdaNombreAttr'>ID:</td>
	<td class='celdaValorAttr'><?php echo($_REQUEST['id_alumno']); ?></td>
  </tr>
  <tr>
	<td class='celdaNombreAttr'>Apellidos:</td>
	<td class='celdaValorAttr'><?php echo($_REQUEST['apellidos']); ?></td>
	<td class='celdaNombreAttr'>Nombres:</td>
	<td class='celdaValorAttr'><?php echo($_REQUEST['nombres']); ?></td>
  </tr>
  <tr>
	<td class='celdaNombreAttr'>Carrera:</td>
	<td class='celdaValorAttr' colspan='3'><?php echo($_REQUEST['carrera']); ?></td>
  </tr>
  <tr>
	<td class='celdaNombreAttr'>Jornada:</td>
	<td class='celdaValorAttr'><?php echo($_REQUEST['jornada']); ?></td>
	<td class='celdaNombreAttr'>Cohorte:</td>
	<td class='celdaValorAttr'><?php echo($_REQUEST['cohorte']); ?></td>
  </tr>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de Contacto</td></tr>

  <tr>
	<td class='celdaNombreAttr' style='vertical-align: top'><u>e-mail:</u></td>
	<td class='celdaValorAttr' colspan='3'>
	  <input type='email' size="40" name='email' value="<?php echo($_REQUEST['email']); ?>" onKeyUp="var valor=this.value;this.value=valor.toLowerCase();" class='boton' required>
	  <small><br>En este correo se informará el resultado de esta solicitud</small>
	</td>
  </tr>
  <tr>
	<td class='celdaNombreAttr' style='vertical-align: top'><u>Tel. Fijo:</u></td>
	<td class='celdaValorAttr'>
	  <b>+56</b> <input type="number" size='9' maxlength='9' name="telefono" min="100000001" max="999999999" pattern="[0-9]*" title="Ingrese sólo números" name='telefono' value="<?php echo($_REQUEST['telefono']); ?>" class="boton" required>
	  <small><br>Si no posee un número de red fija, ingrese<br>acá también su número de teléfono móvil</small>
	</td>
	<td class='celdaNombreAttr' style='vertical-align: top'><u>Tel. Móvil:</u></td>
	<td class='celdaValorAttr'>
	  <b>+56</b> <input type="number" size='9' maxlength='9' name="tel_movil" min="100000001" max="999999999" pattern="[0-9]*" title="Ingrese sólo números" value="<?php echo($_REQUEST['tel_movil']); ?>" class="boton" required>
	  <small><br>Si no posee un número de teléfono móvil,<br>ingrese acá también su número de teléfono fijo</small>
	</td>
  </tr>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Excepción Financiera</td></tr>

  <tr>
	<td colspan="4">
	  <table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" width='100%'>
		<tr class='filaTituloTabla'>
		  <td class='tituloTabla' colspan="4">Contrato(s)</td>
		  <td class='tituloTabla' colspan="5">Propuesta de Repactación</td>
		</tr>
		<tr class='filaTituloTabla'>
		  <td class='tituloTabla'>N°</td>
		  <td class='tituloTabla'>Periodo</td>
		  <td class='tituloTabla'>S. Total</td>
		  <td class='tituloTabla'>M. Moroso</td>
		  <td class='tituloTabla'>Monto Pie</td>
		  <td class='tituloTabla'>N° Cuotas</td>
		  <td class='tituloTabla'>1er Vencimiento</td>
		  <td class='tituloTabla'>Monto Cuota</td>
		  <td class='tituloTabla'>Total a Pagar</td>
		</tr>
		<?php echo($HTML_contratos); ?>
	  </table>
	  <input type="hidden" name="ids_contrato" value="<?php echo(implode(",",$ids_contrato)); ?>">
	</td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="4">
	  <div style='text-align: center'><input type="button" onClick="document.getElementById('comentarios').style.display=''; this.style.display='none'; formulario.comentarios.focus();" value="agregar comentarios"></div>
	  <div id="comentarios" style="display: none">
	    Dispone de <span id='rchars'>500</span> caracteres para su comentario:
	    <textarea id='textarea' name="comentarios" cols="100" rows="5" class='general' maxlength="500"></textarea>
	  </div>
	</td>
  </tr>  

  <tr>
	<td class='celdaValorAttr' colspan="4">
	  <b>Glosario:</b><br>
	  <i>
	  <b>S. Total:</b> Este monto comprende cuotas o saldos de estas que se encuentren vencidas y por vencer no pagadas a la fecha.<br>
	  <b>M. Moroso:</b> Este monto corresponde sólo a las cuotas o saldos de estas que se encuentren vencidas y no pagadas.<br>
	  <b>Monto Pie:</b> Este monto deberá pagarlo inmediatamente, ya que es requisito para la aprobación de esta solicitud.<br>
	  <b>Monto Cuota:</b> Este monto se calculará automáticamente en base al Monto del Pie y el número de cuotas seleccionado.<br>
	  <b>Total a Pagar:</b> Este monto es identico a S. Total. siempre, en virtud de que no se aplican intereses por esta operación.<br>

	  </i>
	  <br>
	  <b>Considera lo siguiente en tu solicitud:</b><br>
		- El Monto del Pie debe ser a lo menos un 50% del M. Moroso o bien un 30% del S. Total.<br>
	    - Procura que el Monto del Pie abarque la mayor parte del contrato más antiguo (están ordenados por periodo).<br>
		- En la medida que modifiques el Monto del Pie que viene sugerido, la cantidad de cuotas se acomodará automáticamente.<br>
		- Mientras más grande sea el Monto del Pie, el N° de cuotas puede ser mayor.<br>
		- El Vencimiento de la 1ra cuota no puede ser superior a 30 días.<br>
		<br>
		NOTA: En caso de proponer un plan de pago fuera de las consideraciones antes descritas, 
			la solicitud podrá ser Rechazada o bien derivada a la Vicerrectoría de Administración y Finanzas.
	</td>
  </tr>
</table>

<script language="Javascript">

calc_total();

function calc_total() {
  var form = document.getElementById('form1');
  var x, campo_nombre, campo_valor, tot_monto_pie=0, tot_monto_cuota=0, tot_monto_apagar=0, estado_pie="";
  var tot_monto_moroso = formulario.tot_monto_moroso.value;
  var numero_formateado = new Intl.NumberFormat('es-CL');
  
   for (x=0;x<form.length;x++) {
	campo_nombre = form.elements[x].name;
	campo_valor  = parseInt(form.elements[x].value.replace(".","").replace(".","").replace(".",""));
	if (campo_nombre.substr(0, 9) == "monto_pie") { tot_monto_pie += campo_valor; form.elements[x].value = numero_formateado.format(campo_valor);}
	if (campo_nombre.substr(0, 11) == "monto_cuota") { tot_monto_cuota += campo_valor; form.elements[x].value = numero_formateado.format(campo_valor);}
	if (campo_nombre.substr(0, 12) == "monto_apagar") { tot_monto_apagar += campo_valor; form.elements[x].value = numero_formateado.format(campo_valor);}
  }

  formulario.tot_monto_pie.value    = tot_monto_pie;
  formulario.tot_monto_cuota.value  = tot_monto_cuota;
  formulario.tot_monto_apagar.value = tot_monto_apagar;
  puntitos(formulario.tot_monto_pie,formulario.tot_monto_pie.value.charAt(formulario.tot_monto_pie.value.length-1),formulario.tot_monto_pie.name);
  puntitos(formulario.tot_monto_cuota,formulario.tot_monto_cuota.value.charAt(formulario.tot_monto_cuota.value.length-1),formulario.tot_monto_cuota.name);
  puntitos(formulario.tot_monto_apagar,formulario.tot_monto_apagar.value.charAt(formulario.tot_monto_apagar.value.length-1),formulario.tot_monto_apagar.name);


  if (tot_monto_pie/tot_monto_moroso <= 0.1) { 
	estado_pie = "<span style='color: red; font-weight: normal'>El monto del pie está por debajo del 10%.</span>";
  } else {
	if (tot_monto_pie/tot_monto_moroso <= 0.5) {
	  estado_pie = "<span style='color: orange; font-weight: normal'>El monto del pie está por debajo del 50%.</span>";
	}
  }
  document.getElementById("estado_pie").innerHTML = estado_pie;
}

function js_number_format(campo) {
  var numero_formateado = new Intl.NumberFormat('es-CL');
  campo = parseInt(campo.value.replace(".","").replace(".","").replace(".",""));
  return campo.value=numero_formateado.format(campo.value);
}

function calc_cuotas(id_contrato) {
	var numero_formateado = new Intl.NumberFormat('es-CL');
	var x, campo_nombre, campo_valor, form = document.getElementById('form1');
	var ids_contrato = form.elements.namedItem("ids_contrato").value.split(","),y=0;
	var monto_pie, monto_saldot, monto_moroso, monto_cuota;
	var nro_cuotas       = formulario.nro_cuotas.value;
	var porc_pie;

	if (monto_pie<=0 || monto_pie>monto_saldot) {
		alert("ERROR: El Monto del Pie no puede ser cero o inferior.\n\n"
			+"De igual forma, no puede ser superior al Saldo Total. ");
		document.forms["formulario"]["monto_pie["+id_contrato+"]"].value = Math.round(monto_moroso*.5,0);
	} else { 

		for (y=0;y<ids_contrato.length;y++) {
			id_contrato  = ids_contrato[y];

			monto_saldot = parseInt(document.forms["formulario"]["monto_saldot["+id_contrato+"]"].value.replace(".","").replace(".","").replace(".",""));
			monto_moroso = parseInt(document.forms["formulario"]["monto_moroso["+id_contrato+"]"].value.replace(".","").replace(".","").replace(".",""));
			monto_pie    = parseInt(document.forms["formulario"]["monto_pie["+id_contrato+"]"].value.replace(".","").replace(".","").replace(".",""));
			monto_cuota = Math.round((monto_saldot-monto_pie)/nro_cuotas,0);    
			document.forms["formulario"]["monto_cuota["+id_contrato+"]"].value = numero_formateado.format(monto_cuota);
			document.forms["formulario"]["monto_apagar["+id_contrato+"]"].value = numero_formateado.format(monto_pie+(monto_cuota*nro_cuotas));
		}

	}
	calc_total();
	
}

function calc_tot_pie() {
	var numero_formateado = new Intl.NumberFormat('es-CL');
	var ids_contrato = form1.elements.namedItem("ids_contrato").value.split(","),y=0;
	var tot_monto_pie = 0,monto_pie, monto_moroso;

	for (y=0;y<ids_contrato.length;y++) {
		id_contrato  = ids_contrato[y];

		monto_pie = parseInt(document.forms["formulario"]["monto_pie["+id_contrato+"]"].value.replace(".","").replace(".","").replace(".",""));
		monto_moroso = parseInt(document.forms["formulario"]["monto_moroso["+id_contrato+"]"].value.replace(".","").replace(".","").replace(".",""));

		if (monto_pie <= 0) {
			alert("ERROR: No es posible establecer un monto del Pie igual o inferior a cero.");
			monto_pie = Math.round(monto_moroso*0.5,0);
			document.forms["formulario"]["monto_pie["+id_contrato+"]"].value = monto_pie;
		}

		if (monto_pie/$monto_moroso < 0.3) {
			alert("ERROR: No es posible establecer un monto del Pie inferior al 30% del monto moroso.");
			monto_pie = Math.round(monto_moroso*0.5,0);
			document.forms["formulario"]["monto_pie["+id_contrato+"]"].value = monto_pie;
		}

		tot_monto_pie += monto_pie;
	}
	formulario.tot_monto_pie.value = numero_formateado.format(tot_monto_pie);
}

function calc_nro_cuotas() {
	var tot_monto_pie    = parseInt(formulario.tot_monto_pie.value.replace(".","").replace(".","").replace(".",""));
	var tot_monto_moroso = parseInt(formulario.tot_monto_moroso.value.replace(".","").replace(".","").replace(".",""));
	var porc_pie,nro_cuotas;

	porc_pie = tot_monto_pie*100 / tot_monto_moroso;
	nro_cuotas = calc_cant_cuotas(porc_pie);
	formulario.nro_cuotas.value = nro_cuotas;
}

function calc_cant_cuotas(porc_pie) { 
	var x;
	for(x=0;x<NRO_CUOTAS.length;x++) {
		if (porc_pie <= NRO_CUOTAS[x]['porc_pie']) {
			return NRO_CUOTAS[x]['nro_cuotas'];
		}
	}
}

var maxLength = 500;
$('textarea').keyup(function() {
  var textlen = maxLength - $(this).val().length;
  $('#rchars').text(textlen);
});

function valida_1er_venc(fecha) {
	var fecha_1er_venc = new Date(fecha.value + " 00:00:00");

	var dia_1er_venc = fecha_1er_venc.getDate();

	if (dia_1er_venc != 5 && dia_1er_venc != 15 && dia_1er_venc != 30 ) {
		alert("ERROR: Sólo puede seleccionar los días 5, 15 o 30 de un mes.");
		fecha.value = fecha.min;
	}
}
</script>

<?php

function nro_cuotas($porc_pie) {
	$NRO_CUOTAS = array(array("porc_pie" =>   1, "nro_cuotas" =>  1),
						array("porc_pie" =>   8, "nro_cuotas" =>  2),
						array("porc_pie" =>  13, "nro_cuotas" =>  3),
						array("porc_pie" =>  17, "nro_cuotas" =>  4),
						array("porc_pie" =>  21, "nro_cuotas" =>  5),
						array("porc_pie" =>  25, "nro_cuotas" =>  6),
						array("porc_pie" =>  29, "nro_cuotas" =>  7),
						array("porc_pie" =>  33, "nro_cuotas" =>  8),
						array("porc_pie" =>  38, "nro_cuotas" =>  9),
						array("porc_pie" =>  42, "nro_cuotas" => 10),
						array("porc_pie" =>  46, "nro_cuotas" => 11),
						array("porc_pie" => 100, "nro_cuotas" => 12));

	$NRO_CUOTAS_json = json_encode($NRO_CUOTAS);

	echo(js("var NRO_CUOTAS = JSON.parse('$NRO_CUOTAS_json'),x;"));

	for($x=0;$x<count($NRO_CUOTAS);$x++) {
		if ($porc_pie <= $NRO_CUOTAS[$x]["porc_pie"]) {
		return $NRO_CUOTAS[$x]["nro_cuotas"];
		}
	}
}

?>