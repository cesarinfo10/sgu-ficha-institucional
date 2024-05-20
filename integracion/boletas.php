<?php

function api_manager_crear_boleta($id_pago) {
	//instalar php-curl
	$SQL_pago = "SELECT p.id,p.nro_boleta_e,to_char(p.fecha::date,'DD-MM-YYYY') AS fecha,u.nombre_usuario AS cajero,p.id_cajero,
						efectivo,cheque,transferencia,tarj_credito,tarj_debito,cheque_afecha,
						ccc.codigo_erp AS cod_centrodecosto_erp,
						CASE WHEN id_arqueo IS NULL THEN 'No' ELSE 'Si' END AS rendida,p.nulo,p.nulo_motivo,to_char(p.nulo_fecha,'DD tmMonth YYYY') AS nulo_fecha,
						CASE
						  WHEN cob.id_contrato    IS NOT NULL THEN coalesce(trim(a.rut),trim(pap.rut))
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
                 LEFT JOIN finanzas.conta_centrosdecosto AS ccc ON ccc.id_carrera=coalesce(a.carrera_actual,a2.carrera_actual,a3.carrera_actual)
				 WHERE p.id=$id_pago";
	$pago     = consulta_sql($SQL_pago);
	
	if (count($pago) == 0) { 
		echo(msje_js("ERROR: No es posible enviar boleta a API Manager"));
	}	
	extract($pago[0]);
	
	$monto_total = $efectivo+$cheque+$transferencia+$tarj_credito+$tarj_debito+$cheque_afecha;
	
	/*
	$forma_pago = array();
	if ($efectivo > 0)      { $forma_pago[] = "Efectivo"; }
	if ($cheque > 0)        { $forma_pago[] = "Cheque al día"; }
	if ($cheque_afecha > 0) { $forma_pago[] = "Cheque a fecha"; }
	if ($transferencia > 0) { $forma_pago[] = "transferencia"; }
	if ($tarj_credito > 0)  { $forma_pago[] = "Tarjeta de Crédito"; }
	if ($tarj_debito > 0)   { $forma_pago[] = "Tarjeta de Débito"; }	
	$forma_pago = implode("/",$forma_pago);	
	*/
	
	$forma_pago = "Efectivo"; // Todo se va a caja, luego en manager hacen la separación.
	
	$cabecera = array("rutEmpresa"         => "73124400-6",
//	                  "tokenEmpresa"       => "h7QK9STIfk_afQKg7J1xL_MhU_NNXv7LvqrEpZ26M7Z1Lp6a15", // token de desarrollo
	                  "tokenEmpresa"       => "gD7G3UkDK1E2woKQazzsG19ks39zdU6K43q9u62g8M1kyCZcq3", // token de producción
					  "numDocumento"       => $nro_boleta_e,
					  "fecha"              => $fecha,
					  "rutCliente"         => $rut_alumno,
					  "codigoMoneda"       => "$",
					  "codigoVendedor"     => 0, //agregar en tabla personal de manager
					  "comision"           => 0,
					  "atencionA"          => $nombre_alumno,
					  "descuentoTipo"      => 0,
					  "descuento"          => 0,
//					  "codigoSucursal"     => 1, 
//					  "codigoSucursal"     => 2440, //desde 11-10-2020
					  "codigoSucursal"     => 1, // desde 3-12-2020
					  "nula"               => 0,
					  "esElectronica"      => 1,
					  "formaPago"          => $forma_pago,
					  "codigoCtaCble"      => "110503", // Cuenta contable de clientes con boletas (manager)
					  "codigoCentroCosto"  => "$cod_centrodecosto_erp",
					  "glosaContable"      => "Pago recibido por Aranceles, Matrículas, Créditos Solidarios, Certificaciones o Solicitudes",
					  "turno"              => 0,
					  "observaciones"      => "Pago registrado vía SGU",
					  "numSerie"           => "",
					  "numItemRef"         => "",
					  "numreg"             => 0);
					   
	$SQL_pago_detalle = "SELECT c.id AS id_cobro,g.nombre AS glosa,pd.monto_pagado,c.monto,
	                            to_char(c.fecha_venc,'DD-tmMon-YYYY') AS fecha_venc,nro_cuota,
	                            coalesce(id_contrato,id_convenio_ci) AS nro_docto,
	                            coalesce(con.ano,date_part('year',cci.fecha)) AS ano_docto,
	                            g.cod_producto_erp,ccc.codigo_erp AS cod_centrodecosto_erp,
	                            coalesce(g.cod_cta_contable_erp,cpc1.codigo::text,cpc2.codigo::text) AS cod_cta_contable_erp
	                     FROM finanzas.pagos_detalle            AS pd
	                     LEFT JOIN finanzas.cobros              AS c    ON c.id=pd.id_cobro
	                     LEFT JOIN finanzas.glosas              AS g    ON g.id=c.id_glosa
	                     LEFT JOIN finanzas.contratos           AS con  ON con.id=c.id_contrato
	                     LEFT JOIN finanzas.convenios_ci        AS cci  ON cci.id=c.id_convenio_ci
	                     LEFT JOIN alumnos						AS a1   ON a1.id=cci.id_alumno
	                     LEFT JOIN alumnos						AS a2   ON a2.id=c.id_alumno
	                     LEFT JOIN carreras						AS c1   ON c1.id=con.id_carrera
	                     LEFT JOIN carreras						AS c2   ON c2.id=a1.carrera_actual
	                     LEFT JOIN carreras						AS c3   ON c3.id=a2.carrera_actual	                     
	                     LEFT JOIN finanzas.conta_plandecuentas AS cpc1 ON (cpc1.ano=coalesce(con.ano,date_part('year',cci.fecha)) 
	                                                                    AND cpc1.regimen=coalesce(c1.regimen,c2.regimen,c3.regimen) 
	                                                                    AND cpc1.docto_xcobrar=g.docto_xcobrar)
	                     LEFT JOIN finanzas.conta_plandecuentas AS cpc2 ON (cpc2.docto_xcobrar=g.docto_xcobrar AND cpc2.ano IS NULL AND cpc2.regimen IS NULL)
	                     LEFT JOIN finanzas.conta_centrosdecosto AS ccc ON ccc.id_carrera=coalesce(a1.carrera_actual,a2.carrera_actual,con.id_carrera)
	                     WHERE pd.id_pago=$id_pago
	                     ORDER BY c.fecha_venc";
	$pago_detalle     = consulta_sql($SQL_pago_detalle);
	
	$detalles = $detalle = array();
	for ($x=0;$x<count($pago_detalle);$x++) {
		$n = $x + 1;
		$detalle = array("codigoProducto".$n     => $pago_detalle[$x]['cod_producto_erp'],
					      "cantidad".$n          => 1,
					      "precunit".$n          => $pago_detalle[$x]['monto_pagado'],
					      "numLote".$n           => "",
					      "codigoBodega".$n      => "B1",
					      "CtaCtble".$n          => $pago_detalle[$x]['cod_cta_contable_erp'],
					      "CenCosto".$n          => $pago_detalle[$x]['cod_centrodecosto_erp'],
					      "DescuentoDetalle".$n  => 0);
		$detalles = array_merge($detalles,$detalle);
	}

//	$url = "desamanager8.manager.cl/Boletas/Api/Boleta"; // URL de desarrollo
//	$url = "https://api.manager.cl/ventas/Api/Boleta";  // URL de producción hasta el 10-11-20
	$url = "https://api.manager.cl/Manager/ws/UCervantes/Ventas/Api/Boleta";  // URL de producción desde el 11-11-20
	
	$datos = array_merge($cabecera,$detalles);

	$datos_json = json_encode($datos);

//	echo $datos_json;
//	error_reporting(-1);
	
	$cliente = curl_init($url);
	curl_setopt($cliente, CURLOPT_POSTFIELDS, $datos_json);
	curl_setopt($cliente, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
	curl_setopt($cliente, CURLOPT_RETURNTRANSFER, true);
//	curl_setopt($cliente, CURLOPT_SSL_CIPHER_LIST, TLSv1); // parametros para API de producción (uso de SSL)
//	curl_setopt($cliente, CURLOPT_SSLVERSION, 1); // parametros para API de producción (uso de SSL)
	
	$respuesta = curl_exec($cliente);
	
	curl_close($cliente);
	
	$resultado = json_decode($respuesta);
	
	//echo($resultado);
	
	if (!is_numeric($resultado)) {
		consulta_dml("UPDATE finanzas.pagos SET bol_e_api_json='$datos_json' WHERE id=$id_pago");
	}
		
	return $resultado;

}

function api_manager_agregar_alumno($rut) {
	
	$token_api_manager = "gD7G3UkDK1E2woKQazzsG19ks39zdU6K43q9u62g8M1kyCZcq3";
	
	$SQL_alumno = "SELECT trim(a.rut) AS rut,a.apellidos||' '||a.nombres AS nombre,a.direccion,c.nombre AS comuna,r.nombre AS region,a.tel_movil,
                          CASE WHEN car.regimen IN ('POST-GD','POST-TD','DIP-D') THEN 'tescobdpi@corp.umc.cl' ELSE a.email END AS email, 
						  coalesce(pap.fecha_post,p2.fecha_post,now()::date) AS fecha_ingreso
				   FROM alumnos AS a
				   LEFT JOIN carreras AS car ON car.id=a.carrera_actual
				   LEFT JOIN comunas  AS c ON c.id=a.comuna
				   LEFT JOIN regiones AS r ON r.id=a.region
				   LEFT JOIN pap           ON pap.id=a.id_pap
				   LEFT JOIN pap      AS p2 ON p2.rut=a.rut
				   WHERE a.rut='$rut'
				   LIMIT 1";
	$alumno = consulta_sql($SQL_alumno);

	if (count($alumno) == 0) {
		$SQL_pap = "SELECT trim(rut) AS rut,apellidos||' '||nombres AS nombre,direccion,c.nombre AS comuna,r.nombre AS region,email,tel_movil,
						   fecha_post AS fecha_ingreso
					FROM pap
					LEFT JOIN comunas AS c ON c.id=pap.comuna
					LEFT JOIN regiones AS r ON r.id=pap.region
					WHERE rut='$rut'";
		$alumno = consulta_sql($SQL_pap);            
	}

	if (count($alumno) > 0) {

		extract($alumno[0]);
		
		$cliente_alumno = new SoapClient("https://api.manager.cl/sec/prod/clienteproveedor.asmx?WSDL",array('trace' => 1));
		
		$opts = array("ssl" => array("ciphers"=>'RC4-SHA', "verify_peer"=>false, "verify_peer_name"=>false));

		$params = array ("encoding"           => 'UTF-8', 
						 "verifypeer"         => false, 
						 "verifyhost"         => false,
						 "soap_version"       => SOAP_1_2, 
						 "trace"              => 1, 
						 "exceptions"         => 1, 
						 "connection_timeout" => 180, 
						 "stream_context"     => stream_context_create($opts));


		$cabecera = array("rutEmpresa"     => "73124400-6",
						  "token"          => $token_api_manager,
						  "rut"            => $rut,                  
						  "nombre"         => $nombre,
						  "dir"            => $direccion,
						  "comuna"         => $comuna,
						  "ciudad"         => $region,
						  "dirDespacho"    => $direccion,
						  "comunaDespacho" => $comuna,
						  "ciudadDespacho" => $region,
						  "email"          => $email,
						  "fono"           => $tel_movil,
						  "giro"           => "Estudiante",
						  "holding"        => "",
						  "nomFantasia"    => $nombre,
						  "clasif"         => 1,
						  "estado"         => 1,
						  "fax"            => "",
						  "emailSii"       => $email,
						  "clase1"         => "",
						  "clase2"         => "",
						  "clase3"         => "",
						  "clase4"         => "",
						  "fechacre"       => $fecha_ingreso,
						  "fecultcon"      => $fecha_ingreso,
						  "fecultvta"      => $fecha_ingreso,
						  "comentario"     => "",
						  "moneda"         => "$",                  
						  "monto"          => 0,
						  "desde"          => 0,
						  "bloquea"        => 0);
						  
		//var_dump($cabecera);
						  
		$alumno = $cliente_alumno -> InsertaCliente2($cabecera);
		return $alumno;
			
		//echo(msje_js($alumno));
	}
}

function api_manager_mod_alumno($rut) {
	
	$token_api_manager = "gD7G3UkDK1E2woKQazzsG19ks39zdU6K43q9u62g8M1kyCZcq3";
	
	$SQL_alumno = "SELECT trim(a.rut) AS rut,a.apellidos||' '||a.nombres AS nombre,a.direccion,c.nombre AS comuna,r.nombre AS region,a.tel_movil,
	                      CASE WHEN car.regimen IN ('POST-GD','POST-TD','DIP-D') THEN 'rrojas@umcervantes.cl' ELSE a.email END AS email, 
						  coalesce(pap.fecha_post,p2.fecha_post,now()::date) AS fecha_ingreso
				   FROM alumnos AS a
				   LEFT JOIN carreras AS car ON car.id=a.carrera_actual
				   LEFT JOIN comunas  AS c ON c.id=a.comuna
				   LEFT JOIN regiones AS r ON r.id=a.region
				   LEFT JOIN pap           ON pap.id=a.id_pap
				   LEFT JOIN pap      AS p2 ON p2.rut=a.rut
				   WHERE a.rut='$rut'
				   LIMIT 1";
	$alumno = consulta_sql($SQL_alumno);

	if (count($alumno) == 0) {
		$SQL_pap = "SELECT trim(rut) AS rut,apellidos||' '||nombres AS nombre,direccion,c.nombre AS comuna,r.nombre AS region,email,tel_movil,
						   fecha_post AS fecha_ingreso
					FROM pap
					LEFT JOIN comunas AS c ON c.id=pap.comuna
					LEFT JOIN regiones AS r ON r.id=pap.region
					WHERE rut='$rut'";
		$alumno = consulta_sql($SQL_pap);            
	}

	if (count($alumno) > 0) {

		extract($alumno[0]);
		
		$cliente_alumno = new SoapClient("https://api.manager.cl/sec/prod/clienteproveedor.asmx?WSDL",array('trace' => 1));
		
		$opts = array("ssl" => array("ciphers"=>'RC4-SHA', "verify_peer"=>false, "verify_peer_name"=>false));

		$params = array ("encoding"           => 'UTF-8', 
						 "verifypeer"         => false, 
						 "verifyhost"         => false,
						 "soap_version"       => SOAP_1_2, 
						 "trace"              => 1, 
						 "exceptions"         => 1, 
						 "connection_timeout" => 180, 
						 "stream_context"     => stream_context_create($opts));


		$cabecera = array("rutEmpresa"     => "73124400-6",
						  "token"          => $token_api_manager,
						  "rut"            => $rut,                  
						  "nombre"         => $nombre,
						  "dir"            => $direccion,
						  "comuna"         => $comuna,
						  "ciudad"         => $region,
						  "dirDespacho"    => $direccion,
						  "comunaDespacho" => $comuna,
						  "ciudadDespacho" => $region,
						  "email"          => $email,
						  "fono"           => $tel_movil,
						  "giro"           => "Estudiante",
						  "holding"        => "",
						  "nomFantasia"    => $nombre,
						  "clasif"         => 1,
						  "estado"         => 1,
						  "fax"            => "",
						  "emailSii"       => $email,
						  "clase1"         => "",
						  "clase2"         => "",
						  "clase3"         => "",
						  "clase4"         => "",
						  "fechacre"       => $fecha_ingreso,
						  "fecultcon"      => $fecha_ingreso,
						  "fecultvta"      => $fecha_ingreso,
						  "comentario"     => "",
						  "moneda"         => "$",                  
						  "monto"          => 0,
						  "desde"          => 0,
						  "bloquea"        => 0);
						  
		$alumno = $cliente_alumno -> ModificaCliente2($cabecera);
			
		//var_dump($alumno);
	}
}

function api_manager_agrmod_alumno($rut) {
	
	$token_api_manager = "gD7G3UkDK1E2woKQazzsG19ks39zdU6K43q9u62g8M1kyCZcq3";
	
	$SQL_alumno = "SELECT trim(a.rut) AS rut,a.apellidos||' '||a.nombres AS nombre,a.direccion,c.nombre AS comuna,r.nombre AS region,a.tel_movil,
                          CASE WHEN car.regimen IN ('POST-GD','POST-TD','DIP-D') THEN 'tescobdpi@corp.umc.cl' ELSE a.email END AS email,
                          a.nombre_usuario||'@alumni.umc.cl' AS email_gsuite,
						  coalesce(pap.fecha_post,p2.fecha_post,now()::date) AS fecha_ingreso
				   FROM alumnos AS a
				   LEFT JOIN carreras AS car ON car.id=a.carrera_actual
				   LEFT JOIN comunas  AS c ON c.id=a.comuna
				   LEFT JOIN regiones AS r ON r.id=a.region
				   LEFT JOIN pap           ON pap.id=a.id_pap
				   LEFT JOIN pap      AS p2 ON p2.rut=a.rut
				   WHERE a.rut='$rut'
				   LIMIT 1";
	$alumno = consulta_sql($SQL_alumno);

	if (count($alumno) == 0) {
		$SQL_pap = "SELECT trim(rut) AS rut,apellidos||' '||nombres AS nombre,direccion,c.nombre AS comuna,r.nombre AS region,email,email AS email_gsuite,tel_movil,
						   fecha_post AS fecha_ingreso
					FROM pap
					LEFT JOIN comunas AS c ON c.id=pap.comuna
					LEFT JOIN regiones AS r ON r.id=pap.region
					WHERE rut='$rut'";
		$alumno = consulta_sql($SQL_pap);            
	}

	if (count($alumno) > 0) {

		extract($alumno[0]);
		
		$cliente_alumno = new SoapClient("https://api.manager.cl/sec/prod/clienteproveedor.asmx?WSDL",array('trace' => 1));
		
		$opts = array("ssl" => array("ciphers"=>'RC4-SHA', "verify_peer"=>false, "verify_peer_name"=>false));

		$params = array ("encoding"           => 'UTF-8', 
						 "verifypeer"         => false, 
						 "verifyhost"         => false,
						 "soap_version"       => SOAP_1_2, 
						 "trace"              => 1, 
						 "exceptions"         => 1, 
						 "connection_timeout" => 180, 
						 "stream_context"     => stream_context_create($opts));


		$cabecera = array("rutEmpresa"     => "73124400-6",
						  "token"          => $token_api_manager,
						  "rut"            => $rut,                  
						  "nombre"         => $nombre,
						  "giro"           => "Estudiante",
						  "dir"            => $direccion,
						  "comuna"         => $comuna,
						  "ciudad"         => $region,
						  "email"          => $email_gsuite,
						  "emailsii"       => $email,
  						  "fono"           => $tel_movil);
						  
		//echo("<!--".json_encode($cabecera)."-->");
						  
		$alumno = $cliente_alumno -> insertaCliente_Ucervantes($cabecera);
		return $alumno;
			
		//echo(msje_js($alumno));
	}
}

function api_manager_consulta_alumno($rut_alumno) {
	
	$cliente_alumno = new SoapClient("https://api.manager.cl/sec/prod/clienteproveedor.asmx?WSDL",array('trace' => 1));

	$token = "gD7G3UkDK1E2woKQazzsG19ks39zdU6K43q9u62g8M1kyCZcq3";
	$rut_empresa = "73124400-6";

	$opts = array("ssl" => array("ciphers"=>'RC4-SHA', "verify_peer"=>false, "verify_peer_name"=>false));

	$params = array ("encoding"           => 'UTF-8', 
                     "verifypeer"         => false, 
                     "verifyhost"         => false,
                     "soap_version"       => SOAP_1_2, 
                     "trace"              => 1, 
                     "exceptions"         => 1, 
                     "connection_timeout" => 180, 
                     "stream_context"     => stream_context_create($opts));
                 
	$alumno = $cliente_alumno -> ObtenerCliente2(array("rutEmpresa" => $rut_empresa,
                                                       "token"      => $token,
                                                       "rutCliente" => $rut_alumno));

	$alumno = (array) $alumno;
	$alumno = (array) $alumno["ObtenerCliente2Result"];

	return $alumno["Ok"];
	
}

?>
