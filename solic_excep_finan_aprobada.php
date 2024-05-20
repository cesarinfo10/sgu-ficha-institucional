<?php

/*
Este script se ejecuta cuando una solicitud de excepción financiera ha sido aprobada.

Por lo tanto automatiza la reprogramación de deuda y cobros según lo aprobado.
*/

$solic = consulta_sql("SELECT fecha::date AS fecha,estado_fecha::date fecha_aprobacion FROM gestion.solicitudes WHERE estado='Aprobada' AND id=$id_solic");
$fecha_solic       = $solic[0]['fecha'];
$fecha_solic_aprob = $solic[0]['fecha_aprobacion'];

$solic_detalle = consulta_sql("SELECT * FROM gestion.solic_excep_finan WHERE id_solicitud=$id_solic");

for($y=0;$y<count($solic_detalle);$y++) {
	extract($solic_detalle[$y]);
	repactar_contrato($id_contrato,$monto_pie,$fecha_solic_aprob,$nro_cuotas,$diap_ini,$mesp_ini,$anop_ini);	
}

function repactar_contrato($id_contrato,$monto_pie,$fecha_repac,$nro_cuotas,$diap_ini,$mesp_ini,$anop_ini) {

	// Respaldar cobros actuales
	$Ids_glosas = "2,20";
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

	$contrato = consulta_sql("SELECT monto_saldot AS deuda_total FROM vista_contratos WHERE id=$id_contrato");
	
	$x = 0;
	$SQL_cond = "";
	$monto_condonacion  = $contrato[0]['deuda_total'];
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
		$diap         = $diap_ini;
		$mesp         = $mesp_ini;
		$anop         = $anop_ini;
		$SQL_cobros   = generar_cobros($id_contrato,$id_glosa,$cant_cuotas,$monto_cuota,$monto_total,$diap,$mesp,$anop);
		consulta_sql($SQL_cobros);
	}

	// Generar nuevos cobros (cuotas)
	if ($contrato[0]['deuda_total'] - $monto_pie > 0) {
		$id_glosa     = 20; // mensualidad de pagare de colegiatura REPACTADA
		$cant_cuotas  = $nro_cuotas;
		$monto_total  = $contrato[0]['deuda_total'] - $monto_pie;
		$monto_cuota  = round($monto_total/$cant_cuotas);
		$diap         = $diap_ini;
		$mesp         = $mesp_ini;
		$anop         = $anop_ini;
		$SQL_cobros   = generar_cobros($id_contrato,$id_glosa,$cant_cuotas,$monto_cuota,$monto_total,$diap,$mesp,$anop);
		consulta_sql($SQL_cobros);
	}
}

?>