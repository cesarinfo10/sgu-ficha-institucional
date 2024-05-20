<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$fecha_docto = $_REQUEST['fecha_docto'];

if ($fecha_docto == "") { $fecha_docto = date("Y-m-d"); }

if ($_REQUEST['subir'] == "Registrar Planilla y Generar Boletas" && $_REQUEST['id_pp'] > 0) {
	$id_pp  = $_REQUEST['id_pp'];
	$pp     = consulta_sql("SELECT * FROM finanzas.pagos_planillas WHERE id=$id_pp");
	extract($pp[0]);

	$SQL_pp_det = "SELECT pm.*,monto_adeudado,p.id AS id_pago
                   FROM finanzas.pagos_planillas_detalle AS pm 
                   LEFT JOIN vista_contratos_rut_carrera_monto_adeudado AS vcrcma ON (vcrcma.rut=pm.rut AND vcrcma.id_carrera=pm.id_carrera)
                   LEFT JOIN finanzas.pagos AS p ON p.$tipo_docto=folio
                   WHERE id_pp=$id_pp";
	$pp_det = consulta_sql($SQL_pp_det);
	
	$id_carrera_cambio = $_REQUEST["id_carrera_cambio"];
	$crear_oi          = $_REQUEST["crear_oi"];

	include_once("integracion/boletas.php");
	for($x=0;$x<count($pp_det);$x++) { api_manager_agrmod_alumno(trim($pp_det[$x]['rut'])); }

	for($x=0;$x<count($pp_det);$x++) {
		if ($pp_det[$x]['monto'] > $pp_det[$x]['monto_adeudado'] && $crear_oi[$pp_det[$x]['id']] > 0) {
			$dif = $pp_det[$x]['monto'] - $pp_det[$x]['monto_adeudado'];

			$SQL_id_contrato = "SELECT id FROM vista_contratos_montos WHERE estado IS NOT NULL AND id_carrera={$pp_det[$x]['id_carrera']} AND rut='{$pp_det[$x]['rut']}' ORDER BY fecha DESC LIMIT 1";
			$id_contrato = consulta_sql($SQL_id_contrato);
			if (count($id_contrato) == 0) {
				$SQL_id_contrato = "SELECT id FROM vista_contratos_montos WHERE estado IS NOT NULL AND rut='{$pp_det[$x]['rut']}' ORDER BY fecha DESC LIMIT 1";
			}

			$id_glosa = 8; // glosa: Otros 

			$SQL_ins_oi = "INSERT INTO finanzas.cobros (id_contrato,nro_cuota,id_glosa,monto,fecha_venc) "
						. "VALUES (($SQL_id_contrato),1,$id_glosa,$dif,'{$pp[0]['fecha_docto']}'::date)";
			consulta_dml($SQL_ins_oi);
		}

		if ($pp_det[$x]['monto_adeudado'] == "" && $id_carrera_cambio[$pp_det[$x]['id']] > 0) {
			$pp_det[$x]['id_carrera'] = $id_carrera_cambio[$pp_det[$x]['id']];
		}

		if (ingresar_pago($pp_det[$x],$tipo_docto,$fecha_docto)) {
			consulta_dml("UPDATE finanzas.pagos_planillas_detalle SET registrado=true WHERE id={$pp_det[$x]['id']}");
		}
	}

	$tot_registrado = consulta_sql("SELECT count(id) AS tot_registrado FROM finanzas.pagos_planillas_detalle WHERE id_pp=$id_pp AND registrado");
	$tot_registrado = $tot_registrado[0]['tot_registrado'];

	if ($tot_registrado == count($pp_det)) {
		consulta_dml("UPDATE finanzas.pagos_planillas SET estado='Pagos registrados' WHERE id=$id_pp");
		echo(msje_js("Se han registrado todos los pagos correctamente."));
	}
	echo(js("parent.jQuery.fancybox.close();"));
	echo(js("location='$enlbase=gestion_pagos';"));
	exit;
}

if ($_REQUEST['subir'] == "Subir y Validar") {
	$tipo_docto  = $_REQUEST['tipo_docto'];
	$descripcion = $_REQUEST['descripcion'];
	
	$planilla_nomarch     = $_FILES['planilla_pagos']['name'];
	$planilla_nomarch_tmp = $_FILES['planilla_pagos']['tmp_name'];
	$planilla_mime        = $_FILES['planilla_pagos']['type'];
	$planilla_size        = $_FILES['planilla_pagos']['size'];
	
	if ($planilla_mime == "text/csv") {
		$planilla = file($planilla_nomarch_tmp);
		$planilla_encabezado = explode(",",trim($planilla[0]));
		if (count($planilla_encabezado) == 4) {

			$problemas = false;
			if ($planilla_encabezado[0] <> "RUT")        { $problemas = true; }
			if ($planilla_encabezado[1] <> "ID_CARRERA") { $problemas = true; }
			if ($planilla_encabezado[2] <> "MONTO")      { $problemas = true; }
			if ($planilla_encabezado[3] <> "FOLIO")      { $problemas = true; }
			
			if (!$problemas) {				

				$SQL_ins_pago_planilla = "INSERT INTO finanzas.pagos_planillas (tipo_docto,fecha_docto,descripcion,id_usuario) "
									   . " VALUES ('$tipo_docto','$fecha_docto','$descripcion',{$_SESSION['id_usuario']})";

				consulta_dml($SQL_ins_pago_planilla);
				
				$id_pp = consulta_sql("SELECT max(id) AS id FROM finanzas.pagos_planillas");
				$id_pp = $id_pp[0]['id'];
	
				$planilla[0] .= str_replace("\n","",$planilla[0]).",id_pp\n";
				for ($x=1;$x<count($planilla);$x++) { $planilla[$x] = str_replace("\r","",str_replace("\n","",$planilla[$x])).",$id_pp\n"; }
				array_shift($planilla);
				//var_dump($planilla);
				//exit;
				$copia_pp = pg_copy_from($bdcon,"finanzas.pagos_planillas_detalle (rut,id_carrera,monto,folio,id_pp)",$planilla,",","");

				if ($copia_pp) {

					$SQL_carr = "SELECT char_comma_sum(id_carrera::text) FROM vista_contratos_rut_carrera_monto_adeudado WHERE rut=pm.rut AND monto_adeudado>pm.monto";

					$SQL_validacion = "SELECT pm.*,c.alias AS carrera,monto_adeudado,($SQL_carr) AS carreras_contratos,p.id AS id_pago
									   FROM finanzas.pagos_planillas_detalle AS pm 
									   LEFT JOIN vista_contratos_rut_carrera_monto_adeudado AS vcrcma ON (vcrcma.rut=pm.rut AND vcrcma.id_carrera=pm.id_carrera)
									   LEFT JOIN finanzas.pagos AS p ON p.$tipo_docto=folio
									   LEFT JOIN carreras AS c ON c.id=pm.id_carrera
									   WHERE id_pp=$id_pp";
//					$SQL_validacion = "SELECT * FROM ($SQL_validacion) AS foo WHERE obs IS NOT NULL";
//					echo($SQL_validacion);
					$aPagos_problemas = consulta_sql($SQL_validacion);
					$pagos_prob = false;

					for($x=0;$x<count($aPagos_problemas);$x++) {
						$aPagos_problemas[$x]['obs'] = "OK";
						if ($aPagos_problemas[$x]['monto_adeudado'] == "" && $aPagos_problemas[$x]['carreras_contratos'] <> "") {
							$pagos_prob = true;
							$aPagos_problemas[$x]['obs'] = "RUT sin contrato en carrera<br>"
														 . "(RUT tiene contrato en carrera: {$aPagos_problemas[$x]['carreras_contratos']})<br>";
							$CARRERAS_CONTRATOS = consulta_sql("SELECT id,alias||' ('||id||')' AS nombre FROM carreras WHERE id IN ({$aPagos_problemas[$x]['carreras_contratos']}) ORDER BY regimen,alias");
							//$aCarreras_contratos = explode(",",$aPagos_problemas[$x]['carreras_contratos']);
							//$CARRERAS_CONTRATOS = array();
							//for($y=0;$y<count($aCarreras_contratos);$y++) { 
							//	$CARRERAS_CONTRATOS[$y] = array('id' => $aCarreras_contratos[$y], 'nombre' => $aCarreras_contratos[$y]);
							//}
							$aPagos_problemas[$x]['solucion'] = "<select name='id_carrera_cambio[{$aPagos_problemas[$x]['id']}]' class='filtro' required>"
									                          . "  <option>-- Sel. Carrera --</option>"
									                          .    select($CARRERAS_CONTRATOS,"")
									                          . "</select>";							 
						} elseif ($aPagos_problemas[$x]['monto'] > $aPagos_problemas[$x]['monto_adeudado']) {
							$pagos_prob = true;
							$dif = $aPagos_problemas[$x]['monto'] - $aPagos_problemas[$x]['monto_adeudado'];

							$_onclick = "";
							if ($dif > 1000) { 
								$_onclick = " onClick=\"return confirm('¿Está seguro de crear un Otro Cobro por $".number_format($dif,0,',','.')."?');\" ";
							}

							$monto_adeudado = number_format($aPagos_problemas[$x]['monto_adeudado'],0,',','.');
							$aPagos_problemas[$x]['obs'] = "Pago excede saldo total ($monto_adeudado, dif: $".number_format($dif,0,',','.').")";
							$aPagos_problemas[$x]['solucion'] = "<input type='checkbox' name='crear_oi[{$aPagos_problemas[$x]['id']}]' id='crear_oi[{$aPagos_problemas[$x]['id']}]' value='$dif' $_onclick required> "
														      . "<label for='crear_oi[{$aPagos_problemas[$x]['id']}]'>Crear OI<br>por $".number_format($dif,0,',','.')."</label>";
						}
						if ($aPagos_problemas[$x]['id_pago'] > 0) {
							$pagos_prob = true;
							$aPagos_problemas[$x]['obs'] = "ERROR: Folio ya utilizado. Debe corregir planilla y volver a subir.<br>";
							$aPagos_problemas[$x]['solucion'] = "<input type='checkbox' name='nada[{$aPagos_problemas[$x]['id']}]' onClick='return false;' required> ";
						}
						if ($tipo_docto == "nro_boleta" && $aPagos_problemas[$x]['folio'] == "") {
							$pagos_prob = true;
							$aPagos_problemas[$x]['obs'] = "ERROR: El campo FOLIO está vacio e indicó Boleta como tipo de docto. No es posible<br>";
							$aPagos_problemas[$x]['solucion'] = "<input type='checkbox' name='nada[{$aPagos_problemas[$x]['id']}]' onClick='return false;' required> ";
						}
					}
					if ($pagos_prob) { 
						consulta_dml("UPDATE finanzas.pagos_planillas SET estado='Con errores' WHERE id=$id_pp");
					}
					
					$pp_det_tot = consulta_sql("SELECT count(id_pp) AS cant_pagos,sum(monto) AS monto_total FROM finanzas.pagos_planillas_detalle WHERE id_pp=$id_pp");	
				} else {
					echo(msje_js("ERROR: El archivo que ha subido, contiene en la columna ID_CARRERA valores erróneos o no todas las columnas contienen datos.\\n\\n"
								."Sólo la columna FOLIO se permite que esté vacía."));								
					consulta_dml("UPDATE finanzas.pagos_planillas SET estado='Con errores' WHERE id=$id_pp");
					echo(js("location='$enlbase_sm=$modulo&descripcion=$descripcion&tipo_docto=$tipo_docto&fecha_docto=$fecha_docto';"));
				}
			} else {
				echo(msje_js("ERROR: El archivo que ha subido en su primera línea no contiene los nombres de columna exigidos."));
			}
				
		} else {
			echo(msje_js("ERROR: El archivo que ha subido no contiene 4 columnas esperadas.\\n\\n"
						."Verifique que el delimitador o separador de columnas sea una coma.\\n\\n"
						."Habitualmente MS-Excel usa punto y coma, por lo que deberá configurar esto al exportar el archivo."));
		}
		
	} else {
		echo(msje_js("ERROR: El archivo que ha subido no está en formato CSV."));
	}
		
	$HTML = $HTML_pagos_problemas = $HTML_pagos_sin_problemas = "";
	$cant_pagos_prob = $cant_pagos_sin_prob = 0;
	for ($x=0;$x<count($aPagos_problemas);$x++) {
		$rut = "<a target='_blank' href='$enlbase=gestion_contratos&texto_buscar={$aPagos_problemas[$x]['rut']}&buscar=Buscar' class='enlaces'>{$aPagos_problemas[$x]['rut']}</a>";

		$monto = number_format($aPagos_problemas[$x]['monto'],0,',','.');
		$HTML =  "<tr class='filaTabla'>\n"
			  .  "  <td class='textoTabla' align='right'>$rut</td>\n"
			  .  "  <td class='textoTabla' align='center'>{$aPagos_problemas[$x]['id_carrera']} ({$aPagos_problemas[$x]['carrera']})</td>\n"
			  .  "  <td class='textoTabla' align='right'>$monto</td>\n"
			  .  "  <td class='textoTabla' align='right'>{$aPagos_problemas[$x]['folio']}</td>\n"
			  .  "  <td class='textoTabla'><label for='crear_oi[{$aPagos_problemas[$x]['id']}]'>{$aPagos_problemas[$x]['obs']}</label></td>\n"
			  .  "  <td class='textoTabla' align='center'>{$aPagos_problemas[$x]['solucion']}</td>\n"
			  .  "</tr>\n";	

		if ($aPagos_problemas[$x]['obs'] <> "OK") {

			$HTML_pagos_problemas .= $HTML;
			$cant_pagos_prob++;

		} else {

			$HTML_pagos_sin_problemas .= $HTML;
			$cant_pagos_sin_prob++;
	
		}
	}
}

if ($_REQUEST['subir'] == "") {

	$SQL_pp_det1 = "SELECT count(id_pp) FROM finanzas.pagos_planillas_detalle WHERE id_pp=pp.id";
	$SQL_pp_det2 = "SELECT sum(monto) FROM finanzas.pagos_planillas_detalle WHERE id_pp=pp.id";

	$SQL_pagos_planillas = "SELECT pp.*,
								   to_char(pp.fecha,'DD-tmMon-YYYY HH24:MI') AS fecha,
								   to_char(pp.fecha_docto,'DD-tmMon-YYYY') AS fecha_docto,
								   CASE tipo_docto 
									    WHEN 'nro_boleta'   THEN 'Boleta'
									    WHEN 'nro_boleta_e' THEN 'Bol-E'
								   END AS tipo_docto,
								   u.nombre_usuario,
								   ($SQL_pp_det1) AS cant_pagos,
								   ($SQL_pp_det2) AS monto_total
							FROM finanzas.pagos_planillas AS pp 
							LEFT JOIN usuarios AS u ON u.id=pp.id_usuario
							ORDER BY pp.fecha DESC";
	$pagos_planillas = consulta_sql($SQL_pagos_planillas);
	for ($x=0;$x<count($pagos_planillas);$x++) {
		extract($pagos_planillas[$x]);
		$monto_total = number_format($monto_total,0,",",".");
		$HTML_pagos_planilla .= "<tr class='filaTabla'>\n"
							.  "  <td class='textoTabla' align='right'>$id</td>\n"
							.  "  <td class='textoTabla'>$descripcion</td>\n"
							.  "  <td class='textoTabla' align='center'>$fecha<br>($nombre_usuario)</td>\n"
							.  "  <td class='textoTabla' align='center'>$fecha_docto<br>$tipo_docto</td>\n" 
							.  "  <td class='textoTabla' align='center'>$cant_pagos</td>\n"
							.  "  <td class='textoTabla' align='center'>$monto_total</td>\n"
							.  "  <td class='textoTabla' align='center'>$estado</td>\n"
							.  "</tr>\n";
	}
}

$TIPOS_DOCTOS = array(array('id' => "nro_boleta_e", 'nombre' => "Bol-E"),
                      array('id' => "nro_boleta",   'nombre' => "Boleta"));

$aTipos_doctos = array_column($TIPOS_DOCTOS,'nombre','id'); 
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>

<form name="formulario" method="post" enctype="multipart/form-data">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">


<?php if (!isset($id_pp)) { ?>

<div style='margin-top: 5px'>
  <input type="submit" name="subir" value="Subir y Validar" onClick="return confirm('Está seguro de continuar?');" onBlur="this.disabled=true;">
  <input type="button" name="Cancelar" value="Cancelar" onClick="">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr'><u>Descripción:</u></td>
    <td class='celdaValorAttr' colspan='3'><input type="text" name="descripcion" size="40" value="<?php echo($_REQUEST['descripcion']); ?>" class="boton" required></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tipo de Documento:</td>
    <td class='celdaValorAttr'>
      <select name="tipo_docto" class="filtro" required>
        <option value="">-- Seleccione --</option>
        <?php echo(select($TIPOS_DOCTOS,$_REQUEST['tipo_docto'])); ?>
      </select>
    </td>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><input type='date' name='fecha_docto' value='<?php echo($_REQUEST['fecha_docto']); ?>' class="boton" required></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Planilla:</u></td>
    <td class='celdaValorAttr' colspan='3'>
      <input type="file" accept=".csv" name="planilla_pagos" size="50" required><br><br>
      NOTA: El archivo debe estar en formato CSV (delimitado por comas) y campos sin formato.<br><br>
      La primera línea de la planilla debe tener los nombres de campos o columnas siguientes:<br>
      <ul>
		  <li><b>RUT</b> (del estudiante, sin puntos, con guión y dígito verificador en mayúscula).</li>
		  <li><b>ID_CARRERA</b> (ver <a target='_blank' href='<?php echo("$enlbase=gestion_carreras"); ?>' class='enlaces'>listado de carreras</a>).</li>
		  <li><b>MONTO</b> (pago total de arancel)</li>
		  <li><b>FOLIO</b> (N° Documento, vacio para automático sólo para Bol-E)</li>
      </ul>
    </td>
  </tr>
</table>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
  <tr class='filaTituloTabla'><td class='tituloTabla' colspan="8">Subidas anteriores (<?php echo(count($pagos_planillas)); ?>)</td></tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>Descripción</td>
    <td class='tituloTabla'>Fecha<br>Op.</td>
    <td class='tituloTabla'>Fecha y Tipo<br>Docto</td>
    <td class='tituloTabla'>Cantidad</td>
    <td class='tituloTabla'>Monto<br>Total</td>
    <td class='tituloTabla'>Estado</td>
  </tr>
  <?php echo($HTML_pagos_planilla); ?>
</table>

<?php } elseif($id_pp > 0) { ?>

<input type="hidden" name="id_pp" value="<?php echo($id_pp); ?>">

<div style='margin-top: 5px'>
  <input type="submit" name="subir" value="Registrar Planilla y Generar Boletas" onClick="return confirm('Está seguro de continuar?');" onBlur="this.disabled=true;">
  <input type="button" name="Cancelar" value="Cancelar" onClick="">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: center'>Subida de Planilla de Pagos</td></tr>
  <tr>
    <td class='celdaNombreAttr'><u>Descripción:</u></td>
    <td class='celdaValorAttr' colspan='3'><?php echo($descripcion); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tipo de Documento:</td>
    <td class='celdaValorAttr'><?php echo($aTipos_doctos[$tipo_docto]); ?></td>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><?php echo($fecha_docto); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Archivo:</u></td>
    <td class='celdaValorAttr' colspan='3'><?php echo($planilla_nomarch); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cantidad de pagos:</td>
    <td class='celdaValorAttr'><?php echo($pp_det_tot[0]['cant_pagos']); ?></td>
    <td class='celdaNombreAttr'>Monto Total:</td>
    <td class='celdaValorAttr'><?php echo(number_format($pp_det_tot[0]['monto_total'],0,',','.')); ?></td>
  </tr>
</table>

<?php 	if (count($aPagos_problemas) > 0) { ?>

<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="10">
	  Pagos con Observaciones (<?php echo($cant_pagos_prob); ?>)<br>
	  <small>Debe establecer la forma de registrar, usando la columna «Solución»</small>
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>RUT</td>
    <td class='tituloTabla'>ID_CARRERA</td>
    <td class='tituloTabla'>MONTO</td>
    <td class='tituloTabla'>FOLIO</td>
    <td class='tituloTabla'>Observación</td>
    <td class='tituloTabla'>Solución</td>
  </tr>
  <?php echo($HTML_pagos_problemas); ?>
  <tr>
    <td class='celdaNombreAttr' colspan='10' align='right'>
	  <input type="submit" name="subir" value="Registrar Planilla y Generar Boletas" onClick="return confirm('Está seguro de continuar?');" onBlur="this.disabled=true;">
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="10">
	  Pagos sin Observaciones (<?php echo($cant_pagos_sin_prob); ?>)<br>
    </td>
  </tr>
  <?php echo($HTML_pagos_sin_problemas); ?>
</table>

<?php 	}  ?>

<?php } ?>

</form>

<?php

function ingresar_pago($detalle,$tipo_docto,$fecha_docto) {
	extract($detalle);
	$EF = $monto;

	if ($folio == "" && $tipo_docto == "nro_boleta_e") { $folio = "nextval('finanzas.pagos_nro_boleta_e_seq'::regclass)"; }

	$SQL_insPago = "INSERT INTO finanzas.pagos (efectivo,$tipo_docto,id_cajero,fecha)
							VALUES ($EF,$folio,{$_SESSION['id_usuario']},'$fecha_docto'::date);";
	if (consulta_dml($SQL_insPago) > 0) {
		$pago    = consulta_sql("SELECT last_value AS id FROM finanzas.pagos_id_seq;");
		$id_pago = $pago[0]['id'];
		//$nro_boleta_e = consulta_sql("SELECT last_value AS id FROM finanzas.pagos_nro_boleta_e_seq;");
		//$nro_boleta_e = $nro_boleta_e[0]['id'];
		$total_pago = $monto;

		$SQL_contratos = "SELECT id 
						  FROM vista_contratos_montos AS vcm 
						  WHERE estado IS NOT NULL AND rut='$rut' AND id_carrera=$id_carrera AND monto_adeudado>0
						  ORDER BY fecha DESC";

		$contratos = consulta_sql($SQL_contratos);
		if (count($contratos) == 0)	{
			$SQL_contratos = "SELECT id 
			                  FROM vista_contratos_montos AS vcm 
			                  WHERE estado IS NOT NULL AND rut='$rut' AND monto_adeudado>0
			                  ORDER BY fecha DESC";
		}
	
		$SQL_cobros = "SELECT c.id,to_char(fecha_venc,'DD-tmMon-YYYY') as fecha_venc,g.nombre::varchar(30) AS glosa,monto,monto_uf,nro_cuota,id_contrato AS id_contrato_c,
							  monto_abonado,abonado
					   FROM finanzas.cobros c
					   LEFT JOIN finanzas.glosas g ON g.id=c.id_glosa
					   WHERE NOT pagado AND c.id_contrato IN ($SQL_contratos) AND c.id_glosa>1
					   ORDER BY c.fecha_venc,id_contrato";
		$cobros     = consulta_sql($SQL_cobros);
	
		$SQL_updCobro = "";
		$ids_cobros = array();
		$tot_pago = $total_pago;
		//var_dump($cobros);
	
		for ($x=0;$x<count($cobros);$x++) {
			$monto = $cobros[$x]['monto'];
			
			$ids_cobros[$x] = $cobros[$x]['id'];
			
			if ($cobros[$x]['abonado'] == "t") { $monto -= $cobros[$x]['monto_abonado']; }
			
			if ($tot_pago < $monto) {
				$monto = $tot_pago;
				$SQL_updCobro .= "UPDATE finanzas.cobros SET abonado=true,monto_abonado=coalesce(monto_abonado,0)+$tot_pago WHERE id={$cobros[$x]['id']};
								  INSERT INTO finanzas.pagos_detalle VALUES ($id_pago,{$cobros[$x]['id']},$monto);";
				$tot_pago -= $monto;
				break;
			}
			if ($tot_pago == $monto) {
				$SQL_updCobro .= "UPDATE finanzas.cobros SET pagado=true,abonado=false,monto_abonado=null WHERE id={$cobros[$x]['id']};
								  INSERT INTO finanzas.pagos_detalle VALUES ($id_pago,{$cobros[$x]['id']},$monto);";
				$tot_pago -= $monto;
				break;
			}
			if ($tot_pago > $monto) {
				$SQL_updCobro .= "UPDATE finanzas.cobros SET pagado=true,abonado=false,monto_abonado=null WHERE id={$cobros[$x]['id']};
								  INSERT INTO finanzas.pagos_detalle VALUES ($id_pago,{$cobros[$x]['id']},$monto);";
				$tot_pago -= $monto;
			}		
		}
		
		consulta_dml($SQL_updCobro);
		//echo($SQL_updCobro);
	
		//echo(msje_js("Pago registrado correctamente. Imprima el comprobante que se emite a continuación"));

		if ($tipo_docto == "nro_boleta_e") {
			
			include_once("integracion/boletas.php");
			//api_manager_agregar_alumno($rut);
			//api_manager_mod_alumno($rut);
			$respuesta_api = api_manager_crear_boleta($id_pago);
			$bol_e_cod_erp = "null";
			if (is_numeric($respuesta_api)) {
				//echo(msje_js("Boleta aceptada por la API Manager"));
				$bol_e_cod_erp = intval($respuesta_api);
			} else {
				//echo(msje_js("ERROR: la boleta NO ha sido aceptada por la API Manager:\\n\\n"
				//			.$respuesta_api));
			}
			consulta_dml("UPDATE finanzas.pagos SET bol_e_respuesta_api = '$respuesta_api',bol_e_cod_erp = $bol_e_cod_erp WHERE id=$id_pago");
			
		}
		//comp_pago_email($id_pago);
		//echo(js("location.href='$enlbase_sm=ver_pago&id_pago=$id_pago';"));
		//echo(js("parent.jQuery.fancybox.close();"));
		return true;
	
	} else {
		//echo(msje_js("Ha ocurrido un error, no ha podido guardarse la boleta.\\n\\n "
		//			."Es muy probable que el Nº de Boleta ya se encuentre registrado.\\n\\n "
		//			."Por favor comunique este error al Departamento de Informática."));
		return false;
	}
}


/*
											  CASE WHEN monto_adeudado IS NULL THEN 'RUT sin contrato en carrera (RUT tiene contrato en carrera: '||($SQL_carr)||')'
												   WHEN monto>monto_adeudado THEN 'Pago excede saldo total ('||to_char(monto_adeudado,'L99G999G999')||', dif: $'||monto_adeudado-monto||')'
												   WHEN p.id IS NOT NULL THEN 'ERROR: Folio ya utilizado. Debe corregir planilla y volver a subir.'
												   WHEN '$tipo_docto'='nro_boleta' AND pm.folio IS NULL THEN 'ERROR: El campo FOLIO está vacio e indicó Boleta como tipo de docto. No es posible'  
											  END AS obs 

		$solucion = "";
		if ($aPagos_problemas[$x]['monto_adeudado'] <> "" && $aPagos_problemas[$x]['monto'] > $aPagos_problemas[$x]['monto_adeudado']) {
			$dif = $aPagos_problemas[$x]['monto_adeudado'] - $aPagos_problemas[$x]['monto'];
 			$solucion = "<input type='checkbox' name='crear_oi[{$aPagos_problemas[$x]['id']}]' id='crear_oi[{$aPagos_problemas[$x]['id']}]' value='$dif' required> "
			          . "<label for='crear_oi[{$aPagos_problemas[$x]['id']}]'>Crear OI</label>";
		}
		if ($aPagos_problemas[$x]['monto_adeudado'] == "") {
			$aCarreras_contratos = explode(",",$aPagos_problemas[$x]['carreras_contratos']);
			$CARRERAS_CONTRATOS = array();
			for($y=0;$y<count($aCarreras_contratos);$y++) { 
				$CARRERAS_CONTRATOS[$y] = array('id' => $aCarreras_contratos[$y], 'nombre' => $aCarreras_contratos[$y]);
			}
			$solucion = "<select name='id_carrera_cambio[{$aPagos_problemas[$x]['id']}]' class='filtro' required>"
					  . "  <option>-- Sel. Carrera --</option>"
					  . select($CARRERAS_CONTRATOS,"")
					  . "</select>";
		}
		if ($aPagos_problemas[$x]['id_pago'] <> "") {
			$solucion = "<input type='checkbox' name='folio_incorrecto[{$aPagos_problemas[$x]['id']}]' onclick='return false;' required>";
		}


											  */
?>
