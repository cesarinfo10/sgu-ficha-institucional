<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

setlocale(LC_MONETARY,"es_CL.UTF8");
include("validar_modulo.php");

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) {	$cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if (empty($reg_inicio)) { $reg_inicio = 0; }

$texto_buscar        = $_REQUEST['texto_buscar'];
$buscar              = $_REQUEST['buscar'];
$buscar_fecha        = $_REQUEST['buscar_fecha'];
$semestre            = $_REQUEST['semestre'];
$ano                 = $_REQUEST['ano'];
$estado              = $_REQUEST['estado'];
$tipo                = $_REQUEST['tipo'];
$tipo_alumno         = $_REQUEST['tipo_alumno'];
$id_carrera          = $_REQUEST['id_carrera'];
$jornada             = $_REQUEST['jornada'];
$semestre_cohorte    = $_REQUEST['semestre_cohorte'];
$cohorte             = $_REQUEST['cohorte'];
$beca                = $_REQUEST['beca'];
$benef_fiscal        = $_REQUEST['benef_fiscal'];
$condonado           = $_REQUEST['condonado'];
$repactado           = $_REQUEST['repactado'];
$regimen             = $_REQUEST['regimen'];
$mat_pagada          = $_REQUEST['mat_pagada'];
$cant_cuotas_morosas = $_REQUEST['cant_cuotas_morosas'];
$forma_pago          = $_REQUEST['forma_pago'];
$emisor              = $_REQUEST['emisor'];
$fec_ini             = $_REQUEST['fec_ini'];
$fec_fin             = $_REQUEST['fec_fin'];
$fec_ini_pago        = $_REQUEST['fec_ini_pago'];
$fec_fin_pago        = $_REQUEST['fec_fin_pago'];

$mostrar_aval        = $_REQUEST['mostrar_aval'];
$firmados            = $_REQUEST['firmados'];

if (empty($ano))      { $ano = $ANO; }
if (empty($semestre)) { $semestre = null; }
if ($estado == "")    { $estado = '1'; }
if (empty($_REQUEST['cohorte'])) { $cohorte = 0; }
if (empty($_REQUEST['semestre_cohorte'])) { $semestre_cohorte = 0; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }
if (empty($fec_ini)) { $ano_ini = $ano -1 ; $fec_ini = "$ano_ini-01-01"; }
if (empty($fec_fin)) { $fec_fin = date("Y-m-d"); }
if (empty($fec_ini_pago)) { $ano_ini_pago = $ano -1 ; $fec_ini_pago = "$ano_ini_pago-01-01"; }
if (empty($fec_fin_pago)) { $fec_fin_pago = date("Y-m-d"); }
if ($_REQUEST['cant_cuotas_morosas'] == "") { $cant_cuotas_morosas = -2; }

$condicion = $cond_pagos = "WHERE true ";

if ($buscar == "Buscar" && $texto_buscar <> "") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar = explode(" ",$texto_buscar_regexp);
	$condicion = "WHERE ";
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= "(lower(pap.nombres||' '||pap.apellidos) ~* '$cadena_buscada' OR "
		           .  " pap.rut ~* '$cadena_buscada' OR "
		           .  "lower(a.nombres||' '||a.apellidos) ~* '$cadena_buscada' OR "
		           .  " a.rut ~* '$cadena_buscada' OR "
		           .  "lower(av.rf_nombres||' '||av.rf_apellidos) ~* '$cadena_buscada' OR "
		           .  " av.rf_rut ~* '$cadena_buscada' OR "
		           .  " text(c.id) ~* '$cadena_buscada' "
		           .  ") AND ";
	}
	$condicion=substr($condicion,0,strlen($condicion)-4);
	$ano = $estado = $tipo = $id_carrera = $jornada = null;	
} else {

	if ($id_carrera <> "") { $condicion .= "AND c.id_carrera=$id_carrera "; }

	if ($jornada <> "") { $condicion .= "AND c.jornada='$jornada' "; }
	
	if ($cohorte > 0) {
		$condicion .= "AND (a.cohorte = '$cohorte') ";
	}

	if ($semestre_cohorte > 0) {
		$condicion .= "AND (a.semestre_cohorte = $semestre_cohorte) ";
	}

	if ($ano > 0) { $condicion .= "AND c.ano=$ano "; }
	
	if ($tipo == "Anual" || $tipo == "Modular") { $semestre = null; }
	
	if (!is_null($semestre)) { $condicion .= "AND c.semestre=$semestre "; }

  if ($firmados == "t") { $condicion .= "AND c.firmado "; }
  if ($firmados == "f") { $condicion .= "AND NOT c.firmado "; }

	if ($estado <> "") {
		if ($estado == "N")  { $condicion .= "AND c.estado IS NULL "; } 
		elseif ($estado == "1") { $condicion .= "AND c.estado IS NOT NULL "; }
		elseif ($estado == "D") { $condicion .= "AND c.estado IN ('S','R','A') "; }
		elseif ($estado != "0") { $condicion .= "AND c.estado='$estado' "; }
	}
	
	if ($tipo <> "" && $tipo <> "0") { $condicion .= "AND c.tipo='$tipo' "; }
	
	if ($tipo_alumno == "N") { $condicion .= "AND c.id_pap IS NOT NULL "; }
	if ($tipo_alumno == "A") { $condicion .= "AND c.id_alumno IS NOT NULL "; }
	
	if ($beca == "100")  { $condicion .= "AND c.id_convenio IS NOT NULL "; }
	elseif ($beca <> "") { $condicion .= "AND c.id_beca_arancel = $beca "; }

  if ($benef_fiscal > 0) { $condicion .= "AND c.id_beca_externa=$benef_fiscal "; }
  if ($benef_fiscal == -2) { $condicion .= "AND c.id_beca_externa IS NOT NULL "; }
	
	if ($condonado == "t") { $condicion .= "AND c.monto_condonacion IS NOT NULL "; }
	if ($condonado == "f") { $condicion .= "AND c.monto_condonacion IS NULL "; }
	
	if ($repactado == "t") { $condicion .= "AND vc.monto_repactado IS NOT NULL "; }
	
	if ($regimen <> "" && $regimen <> "t") { $condicion .= "AND (car.regimen = '$regimen') "; }
	
	if ($mat_pagada == "t") { $condicion .= "AND (mat_pagada > 0 ) "; } 
	elseif ($mat_pagada == "f") { $condicion .= "AND (mat_pagada = 0 AND monto_mat>0 AND (coalesce(0,c.porc_beca_mat)<100 OR c.monto_matricula<>coalesce(0,c.monto_beca_mat))) "; } 
	
	if ($cant_cuotas_morosas >= 0) { $condicion .= "AND vc.cuotas_morosas = $cant_cuotas_morosas "; }
	if ($cant_cuotas_morosas == -1) { $condicion .= "AND vc.cuotas_morosas >= 1 "; }

	
	if ($emisor > 0) { $condicion .= "AND c.id_emisor=$emisor "; }
	
	if ($forma_pago <> "") { $condicion .= "AND $forma_pago>0 "; }
	
	$SQL_pagos_aranceles = "SELECT 0";	
	if (!empty($fec_ini_pago) && !empty($fec_fin_pago)) { 
		$cond_pagos .= " AND p.fecha::date BETWEEN '$fec_ini_pago'::date AND '$fec_fin_pago'::date ";
		$SQL_pagos_aranceles = "(SELECT sum(monto_pagado) 
                                 FROM finanzas.cobros AS c 
                                 LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_cobro=c.id
                                 LEFT JOIN finanzas.pagos         AS p  ON p.id=pd.id_pago 
                                 $cond_pagos AND id_glosa>1 AND id_contrato=vc.id
                                )";
  }

	if (!empty($fec_ini) && !empty($fec_fin)) { 
    $condicion .= " AND c.fecha::date BETWEEN '$fec_ini'::date AND '$fec_fin'::date ";
	}
	
}

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_monto_pagado   = "SELECT sum(coalesce(monto_abonado,monto)) FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa>1 AND (pagado OR abonado)";
$SQL_mat_pagada     = "SELECT count(id) FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa IN (1,10001) AND pagado";
$SQL_monto_saldot   = "SELECT sum(coalesce(monto-monto_abonado,monto)) FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa>1 AND (NOT pagado OR abonado)";
$SQL_monto_moroso   = "SELECT sum(coalesce(monto-monto_abonado,monto)) FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa>1 AND fecha_venc < now() AND (NOT pagado OR abonado)";
$SQL_cuotas_morosas = "SELECT count(id) FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa>1 AND fecha_venc < now() AND (NOT pagado OR abonado)";
$SQL_saldot_lp      = "SELECT sum(coalesce(monto-monto_abonado,monto)) FROM finanzas.cobros WHERE date_part('year',fecha_venc)>date_part('year','$fec_fin_pago'::date)+1 AND id_contrato=c.id AND id_glosa>1 AND (NOT pagado OR abonado)";

$SQL_repactados     = "SELECT sum(monto)::bigint as monto_repactado 
                       FROM finanzas.cobros 
                       WHERE id_contrato=vc.id AND id_glosa IN (20,22) 
                         AND fecha_reg BETWEEN '$fec_ini'::date AND '$fec_fin'::date";

$SQL_contratos = "SELECT c.id,to_char(c.fecha,'DD-MM-YYYY') AS fecha,to_char(c.fecha,'HH24:MI') AS hora,c.tipo,c.estado,c.morosidad_manual,
                         CASE WHEN c.tipo='Semestral' THEN text(c.semestre)||'-'||text(c.ano) ELSE text(c.ano) END AS periodo,c.ano,
                         trim(coalesce(a.rut,pap.rut)) AS rut,coalesce(a.rut,pap.rut) AS rut_al,c.id_alumno,
                         upper(coalesce(a.apellidos,pap.apellidos)) AS al_apellidos,initcap(coalesce(a.nombres,pap.nombres)) AS al_nombres,av.rf_parentezco,
                         av.rf_rut,upper(av.rf_apellidos)||' '||initcap(av.rf_nombres) AS nombre_rf,av.rf_nombre_empresa,av.rf_telefono_empresa,
                         CASE c.estado 
                              WHEN 'E' THEN 'Emitido' 
                              WHEN 'F' THEN 'Firmado' 
                              WHEN 'R' THEN 'Retirado' 
                              WHEN 'S' THEN 'Suspendido' 
                              WHEN 'A' THEN 'Abandonado' 
                              WHEN 'Z' THEN 'Reemplazado' 
                              ELSE 'Nulo'
                         END AS estado,c.monto_matricula,vc.monto_mat::int4,c.monto_matricula-vc.monto_mat::int4 AS beca_mat,c.monto_arancel,
                         CASE WHEN c.id_convenio IS NOT NULL THEN 'Procedencia' ELSE b.alias END AS nombre_beca,
                         CASE WHEN c.id_convenio IS NOT NULL        THEN round(c.monto_arancel*0.2,0)
                              WHEN c.porc_beca_arancel IS NOT NULL  THEN round(c.monto_arancel*(c.porc_beca_arancel/100),0)
                              ELSE c.monto_beca_arancel
                         END AS monto_beca_arancel_calc,
                         CASE WHEN c.monto_arancel > 0 THEN (monto_beca_arancel_calc::float/c.monto_arancel::float) ELSE 0 END AS porc_beca_arancel,
                         c.arancel_cred_interno,
                         trim(car.alias) AS carrera,c.jornada,
                         trim(c.financiamiento) AS financiamiento,
                         CASE WHEN c.id_alumno IS NOT NULL THEN 'A' 
                              WHEN c.id_pap IS NOT NULL THEN 'N'
                         END AS tipo_alumno, c.arancel_efectivo,c.arancel_cheque,
                         coalesce(c.arancel_cant_cheques,0) AS arancel_cant_cheques,
                         c.arancel_pagare_coleg,coalesce(c.arancel_cuotas_pagare_coleg,0) AS arancel_cuotas_pagare_coleg,
                         c.arancel_tarjeta_credito,coalesce(c.arancel_cant_tarj_credito,0) AS arancel_cant_tarj_credito,
                         CASE WHEN c.monto_condonacion>0 THEN '(C)' END AS condonacion,c.monto_condonacion,to_char(c.fecha_condonacion,'DD-MM-YYYY') AS fecha_condonacion,
                         vc.monto_pagado,vc.monto_saldot,vc.monto_moroso,vc.cuotas_morosas,($SQL_repactados) AS monto_repactado,vc.cuotas_repactadas,vc.mat_pagada,
                         u.nombre_usuario AS emisor,c.comentarios,
                         CASE WHEN c.firmado THEN '[Firmado]' END AS firmado
                   FROM finanzas.contratos AS c
                   LEFT JOIN vista_contratos AS vc USING (id)
                   LEFT JOIN alumnos         AS a   ON a.id=c.id_alumno
                   LEFT JOIN pap                    ON pap.id=c.id_pap
				           LEFT JOIN avales          AS av  ON av.id=c.id_aval
                   LEFT JOIN carreras        AS car ON car.id=c.id_carrera      
                   LEFT JOIN becas           AS b   ON b.id=c.id_beca_arancel                             
                   LEFT JOIN usuarios        AS u   ON u.id=c.id_emisor                             
                   $condicion
                   ORDER BY c.fecha DESC,al_apellidos,al_nombres
                   $limite_reg OFFSET $reg_inicio;";
//echo("<!-- $SQL_contratos -->");
$contratos     = consulta_sql($SQL_contratos);

$SQL_ult_pago = "SELECT max(p.fecha::date) FROM finanzas.pagos_detalle pd LEFT JOIN finanzas.pagos p ON p.id=pd.id_pago LEFT JOIN finanzas.cobros cob ON cob.id=pd.id_cobro WHERE cob.id_contrato=c.id AND p.fecha BETWEEN '$fec_ini_pago'::date AND '$fec_fin_pago'::date";
$SQL_monto_saldot2   = "SELECT sum(monto) FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa>1 AND NOT pagado AND NOT abonado";

$SQL_contratos = "SELECT c.id,to_char(c.fecha,'DD-MM-YYYY') AS fecha,to_char(c.fecha,'HH24:MI') AS hora,c.tipo,c.estado,to_char(c.estado_fecha,'DD-MM-YYYY') AS estado_fecha,
                         c.morosidad_manual,be.nombre AS beca_externa,
                         CASE WHEN c.tipo='Semestral' THEN text(c.semestre)||'-'||text(c.ano) ELSE text(c.ano) END AS periodo,c.ano,
                         trim(coalesce(a.rut,pap.rut)) AS rut,coalesce(a.rut,pap.rut) AS rut_al,c.id_alumno,
                         upper(coalesce(a.apellidos,pap.apellidos)) AS al_apellidos,initcap(coalesce(a.nombres,pap.nombres)) AS al_nombres,av.rf_parentezco,
                         av.rf_rut,upper(av.rf_apellidos)||' '||initcap(av.rf_nombres) AS nombre_rf,av.rf_nombre_empresa,av.rf_telefono_empresa,
                         CASE c.estado 
                              WHEN 'E' THEN 'Emitido' 
                              WHEN 'F' THEN 'Firmado' 
                              WHEN 'R' THEN 'Retirado' 
                              WHEN 'S' THEN 'Suspendido' 
                              WHEN 'A' THEN 'Abandonado' 
                              WHEN 'Z' THEN 'Reemplazado'
                              ELSE 'Nulo'
                         END AS estado,c.monto_matricula,vc.monto_mat::int4,c.monto_matricula-vc.monto_mat::int4 AS beca_mat,c.monto_arancel,
                         CASE WHEN c.id_convenio IS NOT NULL THEN 'Procedencia' ELSE b.alias END AS nombre_beca,
                         CASE WHEN c.id_convenio IS NOT NULL        THEN round(c.monto_arancel*0.2,0)
                              WHEN c.porc_beca_arancel IS NOT NULL  THEN round(c.monto_arancel*(c.porc_beca_arancel/100),0)
                              ELSE c.monto_beca_arancel
                         END AS monto_beca_arancel_calc,
                         CASE WHEN c.monto_arancel > 0 THEN round(vc.monto_beca_arancel_calc*(1-(coalesce(CASE WHEN c.fecha_condonacion BETWEEN '$fec_ini_pago'::date AND '$fec_fin_pago'::date THEN c.monto_condonacion ELSE null END,0)::float/c.monto_arancel::float))) ELSE 0 END AS arancel_beca_contable,
                         CASE WHEN c.monto_arancel > 0 THEN (monto_beca_arancel_calc::float/c.monto_arancel::float) ELSE 0 END AS porc_beca_arancel,
                         c.arancel_cred_interno,
                         CASE WHEN c.monto_arancel > 0 THEN round(c.arancel_cred_interno*(1-(coalesce(CASE WHEN c.fecha_condonacion BETWEEN '$fec_ini_pago'::date AND '$fec_fin_pago'::date THEN c.monto_condonacion ELSE null END,0)::float/c.monto_arancel::float))) ELSE 0 END AS arancel_cred_int_contable,
                         trim(car.alias) AS carrera,c.jornada,
                         trim(c.financiamiento) AS financiamiento,
                         CASE WHEN c.id_alumno IS NOT NULL THEN 'A' 
                              WHEN c.id_pap IS NOT NULL THEN 'N'
                         END AS tipo_alumno, c.arancel_efectivo,c.arancel_cheque,
                         coalesce(c.arancel_cant_cheques,0) AS arancel_cant_cheques,
                         c.arancel_pagare_coleg,coalesce(c.arancel_cuotas_pagare_coleg,0) AS arancel_cuotas_pagare_coleg,
                         c.arancel_tarjeta_credito,coalesce(c.arancel_cant_tarj_credito,0) AS arancel_cant_tarj_credito,
                         to_char(c.fecha_condonacion,'DD-MM-YYYY') AS fecha_condonacion,
                         CASE WHEN c.fecha_condonacion BETWEEN '$fec_ini_pago'::date AND '$fec_fin_pago'::date THEN c.monto_condonacion ELSE null END AS monto_condonacion,
                         vc.monto_pagado,vc.monto_saldot,($SQL_monto_saldot2) AS monto_saldot_sin_abonos,($SQL_saldot_lp) AS monto_saldot_lp,
                         vc.monto_moroso,($SQL_pagos_aranceles) AS pagos_rango_fechas,
                         vc.cuotas_morosas,($SQL_ult_pago) AS fecha_ult_pago,vc.monto_repactado_anual,($SQL_repactados) AS monto_repactado,vc.cuotas_repactadas,vc.mat_pagada,
                         u.nombre_usuario AS emisor,c.comentarios,pc.id AS nro_pagare_coleg,
                         CASE WHEN c.id_alumno IS NOT NULL 
                              THEN substr(split_part(a.apellidos,' ',1),1,20)||' '||substr(split_part(a.apellidos,' ',2),1,20)||' '||substr(a.nombres,1,20) 
                              ELSE substr(split_part(pap.apellidos,' ',1),1,20)||' '||substr(split_part(pap.apellidos,' ',2),1,20)||' '||substr(pap.nombres,1,20) 
                         END AS nombre_al_dicom,coalesce(a.cohorte,pap.cohorte) AS cohorte,
                         coalesce(a.direccion,pap.direccion) AS direccion,coalesce(va.comuna,vpap.comuna) as comuna,coalesce(va.region,vpap.region) AS region,
                         coalesce(a.telefono,pap.telefono) AS telefono,coalesce(a.tel_movil,pap.tel_movil) AS tel_movil,coalesce(a.email,pap.email) as email,
                         coalesce(a.nombre_usuario,(SELECT nombre_usuario FROM alumnos WHERE id_pap=c.id_pap LIMIT 1))||'@alumni.umc.cl' AS email_gsuite,coalesce(pap.genero,a.genero) AS genero,
                         coalesce(a.carr_ies_pro,pap.carr_ies_pro) AS profesion,coalesce(va.ies,vpap.ies) as ies_anterior,coalesce(pap.fec_nac,a.fec_nac) as fec_nac
                   FROM finanzas.contratos AS c
                   LEFT JOIN vista_contratos AS vc USING (id)
                   LEFT JOIN alumnos         AS a   ON a.id=c.id_alumno
                   LEFT JOIN pap                    ON pap.id=c.id_pap
                   LEFT JOIN vista_avales    AS vav ON vav.id=c.id_aval
                   LEFT JOIN avales          AS av  ON av.id=c.id_aval
                   LEFT JOIN carreras        AS car ON car.id=c.id_carrera      
                   LEFT JOIN becas           AS b   ON b.id=c.id_beca_arancel                             
                   LEFT JOIN usuarios        AS u   ON u.id=c.id_emisor                             
                   LEFT JOIN finanzas.pagares_colegiatura AS pc ON pc.id_contrato=c.id
                   LEFT JOIN vista_alumnos                AS va   ON va.id=c.id_alumno
                   LEFT JOIN vista_pap                    AS vpap ON vpap.id=c.id_pap
                   LEFT JOIN finanzas.becas_externas AS be ON be.id=c.id_beca_externa
                   $condicion
                   ORDER BY c.fecha DESC,al_apellidos,al_nombres ";
$SQL_tabla_completa = "COPY ($SQL_contratos) to stdout WITH CSV HEADER";

$SQL_contr_ctbles = "SELECT to_char(c.fecha,'DD-MM-YYYY') as fecha_emision,
                            (SELECT to_char(max(fecha_venc),'DD-MM-YYYY') FROM finanzas.cobros where id_contrato=c.id) as fecha_venc,
                            '' as cta_ctble,
                            coalesce(c.arancel_efectivo,0)+coalesce(c.arancel_pagare_coleg,0)+coalesce(c.arancel_cheque,0)+coalesce(c.arancel_tarjeta_credito,0) as monto,
                            coalesce(va.rut,vp.rut) as rut,coalesce(va.nombre,vp.nombre) as razon_social,
                            c.id as nro_contrato,pc.id as nro_pagare 
                     FROM finanzas.contratos AS c 
                     LEFT JOIN vista_contratos AS vc USING (id)
                     LEFT JOIN vista_alumnos AS va ON va.id=c.id_alumno 
                     LEFT JOIN vista_pap     AS vp ON vp.id=c.id_pap 
                     LEFT JOIN finanzas.pagares_colegiatura AS pc ON pc.id_contrato=c.id
                     LEFT JOIN carreras        AS car ON car.id=c.id_carrera
                     $condicion
                     ORDER BY c.fecha DESC ";
$SQL_tc_cont_ctbles = "COPY ($SQL_contr_ctbles) to stdout WITH CSV HEADER";


$SQL_pagos_arancel_pagares = "SELECT sum(pd.monto_pagado) 
                              FROM finanzas.pagos_detalle AS pd 
                              LEFT JOIN finanzas.cobros   AS c on c.id=pd.id_cobro 
                              LEFT JOIN finanzas.pagos    AS p on p.id=pd.id_pago
                              $cond_pagos AND c.id_contrato=vc.id AND id_glosa IN (2,20)";

$SQL_pagos_arancel_cheques = "SELECT sum(pd.monto_pagado) 
                              FROM finanzas.pagos_detalle AS pd 
                              LEFT JOIN finanzas.cobros   AS c on c.id=pd.id_cobro 
                              LEFT JOIN finanzas.pagos    AS p on p.id=pd.id_pago
                              $cond_pagos AND c.id_contrato=vc.id AND id_glosa IN (21,22)";

$SQL_pagos_arancel_contado = "SELECT sum(pd.monto_pagado) 
                              FROM finanzas.pagos_detalle AS pd 
                              LEFT JOIN finanzas.cobros   AS c on c.id=pd.id_cobro 
                              LEFT JOIN finanzas.pagos    AS p on p.id=pd.id_pago
                              $cond_pagos AND c.id_contrato=vc.id AND id_glosa IN (3,31)";

$SQL_condona_arancel_pagares = "CASE WHEN c.fecha_condonacion BETWEEN '$fec_ini_pago'::date AND '$fec_fin_pago'::date AND c.monto_condonacion > 0 AND c.arancel_pagare_coleg > 0 AND c.arancel_efectivo IS NULL AND c.arancel_tarjeta_credito IS NULL 
                                          THEN c.monto_condonacion
                                     WHEN c.fecha_condonacion BETWEEN '$fec_ini_pago'::date AND '$fec_fin_pago'::date AND c.monto_condonacion > 0 AND c.arancel_pagare_coleg > 0 AND (c.arancel_efectivo IS NOt NULL OR c.arancel_tarjeta_credito IS NOT NULL) 
                                          THEN CASE WHEN c.monto_condonacion >= c.arancel_pagare_coleg THEN c.arancel_pagare_coleg ELSE c.monto_condonacion END
                                     ELSE 0
                                END";

$SQL_condona_arancel_contado = "CASE WHEN c.fecha_condonacion BETWEEN '$fec_ini_pago'::date AND '$fec_fin_pago'::date AND c.monto_condonacion > 0 AND c.arancel_pagare_coleg IS NULL AND (c.arancel_efectivo IS NOT NULL OR c.arancel_tarjeta_credito IS NOT NULL) 
                                          THEN c.monto_condonacion
                                     WHEN c.fecha_condonacion BETWEEN '$fec_ini_pago'::date AND '$fec_fin_pago'::date AND c.monto_condonacion > 0 AND c.arancel_pagare_coleg > 0 AND (c.arancel_efectivo IS NOt NULL OR c.arancel_tarjeta_credito IS NOT NULL) 
                                          THEN CASE WHEN c.monto_condonacion >= c.arancel_pagare_coleg THEN c.monto_condonacion-c.arancel_pagare_coleg ELSE 0 END
                                     ELSE 0
                                END";

$SQL_condona_arancel_cheques = "CASE WHEN c.fecha_condonacion BETWEEN '$fec_ini_pago'::date AND '$fec_fin_pago'::date AND c.monto_condonacion > 0 AND c.arancel_cheque IS NOT NULL THEN c.monto_condonacion ELSE 0 END";

$SQL_tc_cxc = "SELECT c.id as nro_contrato,
                      to_char(c.fecha,'DD-MM-YYYY') as fecha_emision,
                      coalesce(va.rut,vp.rut) as rut,
                      coalesce(va.nombre,vp.nombre) as nombre,
                      cc.codigo_erp AS centro_costo,
                      pc.id as nro_pagare,
                      coalesce(c.monto_arancel,0) AS monto_arancel,
                      coalesce(c.monto_arancel,0)-coalesce(vc.monto_beca_arancel_calc,0)-coalesce(c.arancel_cred_interno,0) as cxc_original,
                      coalesce(c.arancel_efectivo,0)+coalesce(c.arancel_tarjeta_credito,0) AS arancel_contado,
                      coalesce(c.arancel_pagare_coleg,0) AS arancel_pagare,
                      coalesce(c.arancel_cheque,0) AS arancel_cheque,
                      CASE WHEN c.monto_arancel > 0 THEN round(vc.monto_beca_arancel_calc*(1-(coalesce(CASE WHEN c.fecha_condonacion BETWEEN '$fec_ini_pago'::date AND '$fec_fin_pago'::date THEN c.monto_condonacion ELSE null END,0)::float/c.monto_arancel::float))) ELSE 0 END AS beca,
                      CASE WHEN c.monto_arancel > 0 THEN round(c.arancel_cred_interno*(1-(coalesce(CASE WHEN c.fecha_condonacion BETWEEN '$fec_ini_pago'::date AND '$fec_fin_pago'::date THEN c.monto_condonacion ELSE null END,0)::float/c.monto_arancel::float))) ELSE 0 END AS cred_interno,
                      CASE WHEN c.fecha_condonacion BETWEEN '$fec_ini_pago'::date AND '$fec_fin_pago'::date THEN c.monto_condonacion ELSE null END AS condonacion,
                      ($SQL_condona_arancel_pagares) AS condonacion_arancel_pagare,
                      ($SQL_condona_arancel_contado) AS condonacion_arancel_contado,
                      ($SQL_condona_arancel_cheques) AS condonacion_arancel_cheque,
                      ($SQL_pagos_aranceles) AS pagos_total,
                      ($SQL_pagos_arancel_pagares) AS pagos_arancel_pagare,
                      ($SQL_pagos_arancel_cheques) AS pagos_arancel_cheque,
                      ($SQL_pagos_arancel_contado) AS pagos_arancel_contado
               FROM finanzas.contratos AS c 
               LEFT JOIN vista_contratos AS vc USING (id)
               LEFT JOIN vista_alumnos AS va ON va.id=c.id_alumno 
               LEFT JOIN vista_pap     AS vp ON vp.id=c.id_pap 
               LEFT JOIN finanzas.pagares_colegiatura AS pc ON pc.id_contrato=c.id
               LEFT JOIN finanzas.conta_centrosdecosto AS cc ON cc.id_carrera=c.id_carrera
               LEFT JOIN carreras        AS car ON car.id=c.id_carrera
               $condicion
               ORDER BY c.fecha DESC ";
$SQL_tc_cxc = "SELECT nro_contrato,fecha_emision,rut,nombre,centro_costo,nro_pagare,
                      monto_arancel,beca,cred_interno,
                      monto_arancel-coalesce(beca,0)-coalesce(cred_interno,0)+round(cxc_original-(monto_arancel-coalesce(beca,0)-coalesce(cred_interno,0))) AS cxc_inicial,
                      arancel_contado,arancel_pagare,arancel_cheque,
                      round(cxc_original-(monto_arancel-coalesce(beca,0)-coalesce(cred_interno,0))) AS dif_x_nousoserv,
                      condonacion_arancel_contado,condonacion_arancel_pagare,condonacion_arancel_cheque,condonacion,
                      pagos_arancel_pagare,pagos_arancel_cheque,pagos_arancel_contado,pagos_total,
                      arancel_contado-coalesce(pagos_arancel_contado,0)-condonacion_arancel_contado AS saldo_arancel_contado,
                      arancel_pagare-coalesce(pagos_arancel_pagare,0)-condonacion_arancel_pagare AS cxc,
                      arancel_cheque-coalesce(pagos_arancel_cheque,0)-condonacion_arancel_cheque AS saldo_arancel_cheque,
                      round(cxc_original-(monto_arancel-coalesce(beca,0)-coalesce(cred_interno,0)))+monto_arancel-coalesce(beca,0)-coalesce(cred_interno,0)-coalesce(pagos_total,0)-coalesce(condonacion,0) AS saldo_total
               FROM ($SQL_tc_cxc) AS foo";
$SQL_tc_cxc = "COPY ($SQL_tc_cxc) to stdout WITH CSV HEADER";

//var_dump($contratos);
$enlace_nav = "$enlbase=$modulo"
            . "&ano=$ano"
            . "&semestre=$semestre"
            . "&estado=$estado"
            . "&tipo=$tipo"
            . "&tipo_alumno=$tipo_alumno"
            . "&beca=$beca"
            . "&condonado=$condonado"
            . "&regimen=$regimen"
            . "&cant_cuotas_morosas=$cant_cuotas_morosas"
            . "&mat_pagada=$mat_pagada"
            . "&id_carrera=$id_carrera"
            . "&jornada=$jornada"
            . "&texto_buscar=$texto_buscar"
            . "&buscar=$buscar"
            . "&cant_reg=$cant_reg"
            . "&r_inicio";

if (count($contratos) > 0) {
	$SQL_contratos = "SELECT c.id 
	                  FROM finanzas.contratos c
	                  LEFT JOIN vista_contratos AS vc USING (id)
	                  LEFT JOIN alumnos         AS a   ON a.id=c.id_alumno
	                  LEFT JOIN pap                    ON pap.id=c.id_pap
	                  LEFT JOIN vista_avales    AS vav ON vav.id=c.id_aval
	                  LEFT JOIN avales          AS av  ON av.id=c.id_aval
	                  LEFT JOIN carreras        AS car ON car.id=c.id_carrera      
	                  LEFT JOIN becas           AS b   ON b.id=c.id_beca_arancel
                      LEFT JOIN usuarios        AS u   ON u.id=c.id_emisor
                      LEFT JOIN finanzas.pagares_colegiatura AS pc ON pc.id_contrato=c.id
                      LEFT JOIN vista_alumnos                AS va   ON va.id=c.id_alumno
                      LEFT JOIN vista_pap                    AS vpap ON vpap.id=c.id_pap
	                  $condicion";
	$SQL_max_cuotas_morosas = "SELECT floor(date_part('day',now()-min(fecha_venc))/30) AS max_cuotas_morosas
	                           FROM finanzas.cobros
	                           WHERE NOT pagado AND id_contrato IN ($SQL_contratos)";
	$max_cuotas_morosas     = consulta_sql($SQL_max_cuotas_morosas);
	$CUOTAS_MOROSAS = array();
	for ($x=0;$x<=$max_cuotas_morosas[0]['max_cuotas_morosas'];$x++) {
		$CUOTAS_MOROSAS = array_merge($CUOTAS_MOROSAS,array(array('id'=>$x,'nombre'=>"$x")));
	}
	$CUOTAS_MOROSAS = array_merge(array(array('id'=>-1,'nombre'=>"1 o más")),$CUOTAS_MOROSAS);


	$SQL_total_contratos =  "SELECT count(c.id) AS total_contratos
	                         FROM finanzas.contratos AS c
	                         LEFT JOIN vista_contratos AS vc USING (id)
	                         LEFT JOIN alumnos       AS a   ON a.id=c.id_alumno
	                         LEFT JOIN pap                  ON pap.id=c.id_pap
	                         LEFT JOIN vista_avales  AS vav ON vav.id=c.id_aval
	                         LEFT JOIN avales        AS av  ON av.id=vav.id
	                         LEFT JOIN carreras      AS car ON car.id=c.id_carrera      
	                         LEFT JOIN becas         AS b   ON b.id=c.id_beca_arancel
	                         LEFT JOIN usuarios      AS u   ON u.id=c.id_emisor
	                         LEFT JOIN finanzas.pagares_colegiatura AS pc ON pc.id_contrato=c.id
	                         LEFT JOIN vista_alumnos                AS va   ON va.id=c.id_alumno
	                         LEFT JOIN vista_pap                    AS vpap ON vpap.id=c.id_pap
	                         $condicion";
	$total_contratos = consulta_sql($SQL_total_contratos);
	$tot_reg         = $total_contratos[0]['total_contratos'];
	
	$HTML_paginador = "Páginas ".html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}

$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "")      { $cond_carreras .= "AND regimen='$regimen' "; }
$carreras = consulta_sql("SELECT id,nombre FROM carreras $cond_carreras ORDER BY nombre;");

$cohortes = $anos;

$SEMESTRES_COHORTES = array(array("id"=>1,"nombre"=>1),
                            array("id"=>2,"nombre"=>2));

$SEMESTRES = array(array("id"=>0,"nombre"=>0),
                   array("id"=>1,"nombre"=>1),
                   array("id"=>2,"nombre"=>2));

$_SESSION['enlace_volver'] = "$enlace_nav=$reg_inicio";

$becas = consulta_sql("SELECT id,alias||' - '||nombre as nombre from becas ORDER BY nombre");
$beca_proc = array(array("id"=>100,"nombre"=>"Procedencia"));
$becas = array_merge($becas,$beca_proc);
$emisores = consulta_sql("SELECT id,nombre FROM vista_usuarios WHERE id IN (SELECT id_emisor FROM finanzas.contratos WHERE ano=$ano)");

$BENEF_FISCALES = consulta_sql("SELECT id,nombre||' ('||alias||')' AS nombre,dependencia AS grupo FROM finanzas.becas_externas WHERE activa ORDER BY dependencia,nombre");
$BENEF_FISCALES[] = array("id" => -2,"nombre" => "Todos los beneficiarios","grupo" => "Todos los Beneficiarios");

$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();

$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);

$boton_tc_contr_ctbles = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=contr_ctbles_$id_sesion');\" class='boton'><small>TC Contr. Ctbles.</small></a>";
$nombre_arch = "sql-fulltables/contr_ctbles_$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tc_cont_ctbles);

$boton_tc_cxc_cna = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=tc_cxc_cna_$id_sesion');\" class='boton'><small>TC CxC CNA</small></a>";
$nombre_arch = "sql-fulltables/tc_cxc_cna_$id_sesion.sql";
$SQL_tc_cxc_cna = "COPY (".cxc_clasif("CNA").") to stdout WITH CSV HEADER";
file_put_contents($nombre_arch,$SQL_tc_cxc_cna);

$boton_tc_cxc_sies = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=tc_cxc_sies_$id_sesion');\" class='boton'><small>TC CxC SIES</small></a>";
$nombre_arch = "sql-fulltables/tc_cxc_sies_$id_sesion.sql";
$SQL_tc_cxc_sies = "COPY (".cxc_clasif("SIES").") to stdout WITH CSV HEADER";
file_put_contents($nombre_arch,$SQL_tc_cxc_sies);

$boton_tc_cxc = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=tc_cxc_$id_sesion');\" class='boton'><small>TC CxC</small></a>";
$nombre_arch = "sql-fulltables/tc_cxc_$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tc_cxc);

$FORMAS_PAGO = array(array('id'=>"c.arancel_efectivo",       'nombre'=>"Efectivo"),
                     array('id'=>"c.arancel_cheque",         'nombre'=>"Cheque(s)"),
                     array('id'=>"c.arancel_pagare_coleg",   'nombre'=>"Pagaré Colegiatura"),
                     array('id'=>"c.arancel_tarjeta_credito",'nombre'=>"Tarjeta de Crédito"));

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<div class="texto" style='margin-top: 5px'>
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          <div align='left'>Periodo matrícula:</div>
          <select class="filtro" name="semestre" onChange="submitform();">
            <option value=""></option>
            <?php echo(select($SEMESTRES,$semestre)); ?>    
          </select>
          - 
          <select class="filtro" name="ano" onChange="formulario.fec_ini_pago.value=null; submitform();">
            <option value="-1">Todos</option>
            <?php echo(select($anos_contratos,$ano)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Estado:</div>
          <select class="filtro" name="estado" onChange="submitform();">
            <option value="0">Todos</option>
            <?php echo(select($estados_contratos,$estado)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Tipo:</div>
          <select class="filtro" name="tipo" onChange="submitform();">
            <option value="0">Todos</option>
            <?php echo(select($tipos_contratos,$tipo)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Alumnos:</div>
          <select class="filtro" name="tipo_alumno" onChange="submitform();">
            <option value="">Todos</option>
            <?php echo(select($tipos_alumnos,$tipo_alumno)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Beca:</div>
          <select class="filtro" name="beca" onChange="submitform();" style="max-width: 100px">
            <option value="">Todas</option>
            <?php echo(select($becas,$beca)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Benef. Fiscal:</div>
          <select class="filtro" name="benef_fiscal" onChange="submitform();" style="max-width: 100px">
            <option value="">Todas</option>
            <?php echo(select_group($BENEF_FISCALES,$benef_fiscal)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Condonado:</div>
          <select class="filtro" name="condonado" onChange="submitform();">
            <option value="">Todos</option>
            <?php echo(select($sino,$condonado)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Firmado:</div>
          <select class="filtro" name="firmados" onChange="submitform();">
            <option value="">Todos</option>
            <?php echo(select($sino,$firmados)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Repactado:</div>
          <select class="filtro" name="repactado" onChange="submitform();">
            <option value="">Todos</option>
            <?php echo(select($sino,$repactado)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Mat. Pagada:</div>
          <select class="filtro" name="mat_pagada" onChange="submitform();">
            <option value="0">Todos</option>
            <?php echo(select($sino,$mat_pagada)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>C. Morosas:</div>
          <select class="filtro" name="cant_cuotas_morosas" onChange="submitform();">
            <option value="-2">Todos</option>
            <?php echo(select($CUOTAS_MOROSAS,$cant_cuotas_morosas)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Forma de Pago:</div>
          <select class="filtro" name="forma_pago" onChange="submitform();" style="max-width: 100px">
            <option value="">Todas</option>
            <?php echo(select($FORMAS_PAGO,$forma_pago)); ?>
          </select>
        </td>
      </tr>
    </table>
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          <div align='left'>Carrera/Programa:</div>
          <select class="filtro" name="id_carrera" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($carreras,$id_carrera)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Jornada:</div>
          <select class="filtro" name="jornada" onChange="submitform();">
            <option value="">Ambas</option>
            <?php echo(select($JORNADAS,$jornada)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Cohorte:</div>
          <select class="filtro" name="semestre_cohorte" onChange="submitform();">
            <option value="0"></option>
            <?php echo(select($SEMESTRES_COHORTES,$semestre_cohorte)); ?>    
          </select>
          -
          <select class="filtro" name="cohorte" onChange="submitform();">
            <option value="0">Todas</option>
            <?php echo(select($cohortes,$cohorte)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Régimen:</div>
          <select class="filtro" name="regimen" onChange="submitform();">
            <option value="t">Todos</option>
            <?php echo(select($REGIMENES,$regimen)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Emitidos entre:</div>
          <div style='font-weight: normal'>
            <input type="date" name="fec_ini" value="<?php echo($fec_ini); ?>" onBlur='formulario.fec_ini_pago.value=this.value' class="boton" style='font-size: 8pt'>
            <input type="date" name="fec_fin" value="<?php echo($fec_fin); ?>" onBlur='formulario.fec_fin_pago.value=this.value' class="boton" style='font-size: 8pt'>
          </div>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Pagos y condonaciones entre:</div>
          <div style='font-weight: normal'>
            <input type="date" name="fec_ini_pago" value="<?php echo($fec_ini_pago); ?>" class="boton" style='font-size: 8pt'>
            <input type="date" name="fec_fin_pago" value="<?php echo($fec_fin_pago); ?>" class="boton" style='font-size: 8pt'>
            <input type="submit" name="buscar" value="Buscar" style='font-size: 10pt'>
          </div>
        </td>
      </tr>
    </table>
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          <div align='left'>Buscar por Nº contrato, RUT o nombre del alumno/postulante o del responsable financiero:</div>
          <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="45" id="texto_buscar" class="boton">
          <script>document.getElementById("texto_buscar").focus();</script>     
          <input type="submit" name="buscar" value="Buscar">          
          <?php 
          	if ($buscar == "Buscar" && $texto_buscar <> "") {
          		echo("<input type='submit' name='buscar' value='Vaciar'>");          		
          	};
          ?>
        </td>
        <td class="celdaFiltro">
          <input type="checkbox" name="mostrar_aval" id="mostrar_aval" onClick="submitform()" <?php if ($mostrar_aval == "on") { echo "checked"; } ?>>
          <label for='mostrar_aval'>Ver Aval</label><br>
          <!-- <input type="checkbox" name="firmados" id="firmados" onClick="submitform()" <?php if ($firmados == "on") { echo "checked"; } ?>>
          <label for='firmados'>Firmados</label> -->
        </td>
        <td class="celdaFiltro">
          Acciones<br>
          <?php echo("<a href='$enlbase_sm=resumen_contratos&ano=$ano&fec_ini_emision=$fec_ini&fec_fin_emision=$fec_fin&fec_ini_pago=$fec_ini_pago&fec_fin_pago=$fec_fin_pago' class='boton' id='sgu_fancybox'>Resumen</a>"); ?>
          <?php echo("<a href='$enlbase_sm=contratos_marcar_firma' class='boton' id='sgu_fancybox'>Marcar firma</a>"); ?>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Emisor:</div>
          <select class="filtro" name="emisor" onChange="submitform();">
            <option value="t">Todos</option>
            <?php echo(select($emisores,$emisor)); ?>
          </select>
        </td>
      </tr>
    </table>  
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="5">
      Mostrando <b><?php echo($tot_reg); ?></b> contrato(s) en total, en página(s) de
      <select class='filtro' name="cant_reg" onChange="submitform();">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas
    </td>
    <td class="texto" align="right" colspan="6">
      <?php echo($HTML_paginador); ?>
      <?php echo($boton_tc_contr_ctbles); ?>
      <?php echo($boton_tc_cxc); ?>
      <?php echo($boton_tabla_completa); ?>
      <?php echo($boton_tc_cxc_cna); ?>
      <?php echo($boton_tc_cxc_sies); ?>
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Nº<br>Estado</td>
    <td class='tituloTabla'>Periodo<br>y Tipo</td>    
    <td class='tituloTabla'>Alumno</td>
    <td class='tituloTabla'>Carrera</td>
    <td class='tituloTabla'>Valores<br>Nominales</td>
    <td class='tituloTabla'>Beneficios</td>
    <td class='tituloTabla'>Arancel<br>Efectivo</td>
    <td class='tituloTabla'>Forma<br>de Pago</td>
    <td class='tituloTabla'>Monto Pagado<br>[Saldo Total]</td>
    <td class='tituloTabla'>Monto<br>Moroso</td>
    <td class='tituloTabla'>Fecha y<br>Emisor</td>
  </tr>
<?php
	$HTML = "";
	if (count($contratos) > 0) {
		for ($x=0;$x<count($contratos);$x++) {
			extract($contratos[$x]);
			
			$tipo = trim($tipo);
			
			$al_apellidos = "<div>$al_apellidos</div>";
			$al_nombres   = "<div>$al_nombres</div>";
			
			if ($tipo == "Anual" || $tipo == "Semestral") {
				if ($tipo_alumno == "N") { $fmt_contrato = "al_nuevo"; } elseif ($tipo_alumno == "A") { $fmt_contrato = "al_antiguo"; }
			}
			if ($tipo == "Estival") { $fmt_contrato = "estival"; }
			if ($tipo == "Modular") {
				if ($tipo_alumno == "N") { $fmt_contrato = "al_nuevo_modular"; } elseif ($tipo_alumno == "A") { $fmt_contrato = "al_antiguo_modular"; }
			}
			
			//$enl = "contrato.php?id_contrato=$id&tipo=$fmt_contrato";
			$enl = "$enlbase=form_matricula_ver&id_contrato=$id#cobros";
			//$enlace = "a class='enlitem' href='$enl'";

			if ($rf_parentezco == "Ninguno") { $rf_parentezco = "El mismo Alumno"; $nombre_rf = ""; }
			$rf_parentezco = "($rf_parentezco)";
			
			$porc_beca_arancel    = ($monto_beca_arancel_calc / $monto_arancel) * 100;
			$porc_cred_interno    = ($arancel_cred_interno / $monto_arancel) * 100;
			$arancelEfectivo = $monto_arancel - $monto_beca_arancel_calc - $arancel_cred_interno;

			$cond = "";
			if ($monto_condonacion > 0) {
				$monto_condonacion *= -1;
				$arancel_cobrable   = $arancelEfectivo + $monto_condonacion;
				$monto_condonacion  = money_format("%7#7.0n",$monto_condonacion);
				$arancel_cobrable   = money_format("%7#7.0n",$arancel_cobrable);
				$cond               = "<div><small>Cond: $monto_condonacion</small></div>"
				                    . "<div><small>Arancel Cobrable:</small></div>"
				                    . "<div><b>$arancel_cobrable</b></div>";
			}

			$monto_arancel        = number_format($monto_arancel,0,',','.');
			$monto_matricula      = money_format("%(#7.0n",$monto_mat);
			$monto_beca_arancel   = number_format($monto_beca_arancel_calc,0,',','.');
			$porc_beca_arancel    = number_format($porc_beca_arancel,0,',','.');
			$arancel_cred_interno = number_format($arancel_cred_interno,0,',','.');
			$porc_cred_interno    = number_format($porc_cred_interno,0,',','.');
			$arancelEfectivo      = number_format($arancelEfectivo,0,',','.');
			
			$monto_pagado         = money_format("%(#7.0n",$monto_pagado);
			$monto_saldot         = money_format("%(#7.0n",$monto_saldot);
			
			if ($monto_moroso > 0) { 
				$monto_moroso   = "<span style='color: #ff0000'>".money_format("%(#7.0n",$monto_moroso)."</span>";
				$cuotas_morosas = "<span style='color: #ff0000'>($cuotas_morosas)</span>";
				$estado_financiero = "<span class='no'>MOROSO</span>";
			} else {
				$estado_financiero = "<span class='si'>Al día</span>";
			}
			
			if ($morosidad_manual == 't') { $morosidad_manual = "<span class='no'>M.M.</span>"; } else { $morosidad_manual = ""; }
			
			$arancel_efectivo        = money_format("%=*(!7#7.0n",$arancel_efectivo);
			$arancel_cheque          = money_format("%=*(!7#7.0n",$arancel_cheque);
			$arancel_pagare_coleg    = money_format("%=*(!7#7.0n",$arancel_pagare_coleg);
			$arancel_tarjeta_credito = money_format("%=*(!7#7.0n",$arancel_tarjeta_credito);
					
			list($forma_pago_nombre,$forma_pago_cuotas,$forma_pago_monto) = explode(",",str_replace(array("{","}"),"",$forma_pago));			
			$forma_pago_monto     = number_format($forma_pago_monto,0,',','.');
			if ($forma_pago_cuotas > 0) { $forma_pago_cuotas = "($forma_pago_cuotas)"; } else { $forma_pago_cuotas = ""; }
			
			$id = "<a href='$enl' class='enlaces'>$id</a>";
			
			$aval = "";
			if ($mostrar_aval == "on") {
				$aval = "<div><small>  <b>Aval:</b> $nombre_rf $rf_parentezco</small></div>";
			}
			
			if ($mat_pagada > 0) { $color_mat = "si"; } else { $color_mat = "no"; }			
			$monto_matricula = "<span class='$color_mat'>$monto_matricula</span>";
			
			$background = "";
			if ($estado == "Nulo") {
				$background = "bgcolor='#FF8E8E'";
				$estado_financiero = $monto_moroso = $cuotas_morosas = $monto_saldot = "";
			}
			if ($estado == "Reemplazado") {
				$background = "bgcolor='#D3D3D3'";
      }
			
			if ($emisor <> "") { $emisor = "($emisor)"; }
			
			if (!empty($comentarios)) {
				$comentarios = str_replace("###","blockquote",wordwrap(nl2br($comentarios),90)); 
				$id = "<div title='header=[Observaciones] fade=[on] body=[$comentarios]' style='background: #BFE4BF; border-radius: 25px; padding: 0px 2px 0px 2px'>$id</div>";
			}
			
			$HTML .= "  <tr class='filaTabla' $background onClick=\"window.location='$enl';\">\n"
			      . "    <td class='textoTabla' align='center'>"
			      . "      <div>$id</div>"
			      . "      <div>$estado</div>"
			      . "      <div><small>$condonacion</small></div>"
			      . "      <div><small>$estado_financiero $morosidad_manual</small></div>"
			      . "    </td>\n"
			      . "    <td class='textoTabla' align='center'>$periodo<br><small>$tipo</small></td>\n"
			      . "    <td class='textoTabla'><div>$rut <small>($tipo_alumno)</small></div> $al_apellidos $al_nombres $aval</td>\n"
			      . "    <td class='textoTabla'>$carrera-$jornada</td>\n"
			      . "    <td class='textoTabla' align='right'><small>"
			      . "      <div>Matríc: $monto_matricula</div>"
			      . "      <div align='left'>Arancel $financiamiento:</div>"
			      . "      <div><b>$$monto_arancel</b></div>"
			      . "    </small></td>\n"
			      . "    <td class='textoTabla' align='right'><small>"
			      . "      <div style='background: rgba(229,229,229,0.75)'>Beca: $$monto_beca_arancel</div>"
			      . "      <div style='background: rgba(229,229,229,0.75)'><small>$nombre_beca ($porc_beca_arancel%)</small></div>"
			      . "      <div>C. I.: $$arancel_cred_interno</div>"
			      . "      <div><small>($porc_cred_interno%)</small></div>"
			      . "    </small></td>\n"
			      . "    <td class='textoTabla' align='right' style='vertical-align: middle'>"
			      . "      <div>$$arancelEfectivo</div>"
			      . "      $cond"
			      . "    </td>\n"
			      . "    <td class='textoTabla'><small style='font-family: ubuntu mono,mono'>"
			      . "      <div>&nbsp;EF: $arancel_efectivo</div>" 
			      . "      <div>&nbsp;CH: $arancel_cheque ($arancel_cant_cheques)</div>" 
			      . "      <div>Pag: $arancel_pagare_coleg ($arancel_cuotas_pagare_coleg)</div>"
			      . "      <div>&nbsp;TC: $arancel_tarjeta_credito ($arancel_cant_tarj_credito)</div>"			                 
			      . "    </small></td>\n"
			      . "    <td class='textoTabla' align='right'><div>$monto_pagado&nbsp;</div><div>[$monto_saldot]</div></td>\n"
			      . "    <td class='textoTabla' align='right'><div>$monto_moroso</div><div>$cuotas_morosas</div></td>\n"
			      . "    <td class='textoTabla'><div align='center'>$fecha<br>$hora</div><div align='center'>$emisor<br><small class='si'><b>$firmado</b></small></div></td>\n"
			      . "  </tr>\n";
		}
	} else {
		$HTML = "  <tr>"
		      . "    <td class='textoTabla' colspan='11'><br><br>"
		      . "      <center>*** No hay registros para los criterios de búsqueda/selección ***</center><br><br>"
		      . "    </td>\n"
		      . "  </tr>";
	}
	echo($HTML);
?>
</table>
</form>
<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 1200,
		'height'			: 600,
		'afterClose'		: function () {  },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 700,
		'height'			: 600,
		'maxHeight'			: 600,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});
</script>

<!-- Fin: <?php echo($modulo); ?> -->

<?php

function cxc_clasif($tipo) {
  global $fec_fin_pago,$fec_fin,$condicion;

  $SQL_cxc = "SELECT sum(coalesce(monto-monto_abonado,monto)) AS monto_cxc FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa IN (2,20) AND (NOT pagado OR abonado) AND ";
  $SQL_castigo = "SELECT sum(coalesce(castigo_monto*-1,0)) AS monto_castigo FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa IN (2,20) AND  ";

  if ($tipo=="CNA") {
    $SQL_bloque1_venc = "fecha_venc BETWEEN '$fec_fin'::date-'90 days'::interval AND '$fec_fin'::date";
    $SQL_bloque2_venc = "fecha_venc BETWEEN '$fec_fin'::date-'180 days'::interval AND '$fec_fin'::date-'91 days'::interval";
    $SQL_bloque2_venc = "fecha_venc BETWEEN '$fec_fin'::date-'365 days'::interval AND '$fec_fin'::date-'181 days'::interval";
  } else {
    $SQL_bloque1_venc = "fecha_venc BETWEEN '$fec_fin'::date-'30 days'::interval AND '$fec_fin'::date";
    $SQL_bloque2_venc = "fecha_venc BETWEEN '$fec_fin'::date-'90 days'::interval AND '$fec_fin'::date-'31 days'::interval";
    $SQL_bloque3_venc = "fecha_venc BETWEEN '$fec_fin'::date-'365 days'::interval AND '$fec_fin'::date-'91 days'::interval";
  }  
  $SQL_bloque4_venc = "fecha_venc < '$fec_fin'::date-'365 days'::interval";
  $SQL_novenc_cp    = "fecha_venc BETWEEN '$fec_fin'::date+'1 days'::interval AND '$fec_fin'::date+'365 days'::interval";
  $SQL_novenc_lp    = "fecha_venc > '$fec_fin'::date+'365 days'::interval";
  
  $SQL_cxc_bloque1_venc = $SQL_cxc . $SQL_bloque1_venc;
  $SQL_cxc_bloque2_venc = $SQL_cxc . $SQL_bloque2_venc;
  $SQL_cxc_bloque3_venc = $SQL_cxc . $SQL_bloque3_venc;
  $SQL_cxc_bloque4_venc = $SQL_cxc . $SQL_bloque4_venc;
  $SQL_cxc_novenc_cp    = $SQL_cxc . $SQL_novenc_cp;
  $SQL_cxc_novenc_lp    = $SQL_cxc . $SQL_novenc_lp;

  $SQL_castigo_bloque1_venc = $SQL_castigo . $SQL_bloque1_venc;
  $SQL_castigo_bloque2_venc = $SQL_castigo . $SQL_bloque2_venc;
  $SQL_castigo_bloque3_venc = $SQL_castigo . $SQL_bloque3_venc;
  $SQL_castigo_bloque4_venc = $SQL_castigo . $SQL_bloque4_venc;
  $SQL_castigo_novenc_cp    = $SQL_castigo . $SQL_novenc_cp;
  $SQL_castigo_novenc_lp    = $SQL_castigo . $SQL_novenc_lp;

  $SQL_cxc_clasif = "($SQL_cxc_novenc_lp) AS cxc_novenc_lp,
                     ($SQL_cxc_novenc_cp) AS cxc_novenc_cp,";
  if ($tipo=="CNA") {
    $SQL_cxc_clasif .= "($SQL_cxc_bloque1_venc) AS cxc_0a90dias,
                        ($SQL_cxc_bloque2_venc) AS cxc_91a180dias,
                        ($SQL_cxc_bloque3_venc) AS cxc_181a365dias,";
  } else {
    $SQL_cxc_clasif .= "($SQL_cxc_bloque1_venc) AS cxc_0a30dias,
                        ($SQL_cxc_bloque2_venc) AS cxc_31a90dias,
                        ($SQL_cxc_bloque3_venc) AS cxc_91a365dias,";
  }
  $SQL_cxc_clasif .= "($SQL_cxc_bloque4_venc) AS cxc_masde365dias";
  
  $SQL_castigo_clasif = "($SQL_castigo_novenc_lp) AS cxc_novenc_lp,
                         ($SQL_castigo_novenc_cp) AS cxc_novenc_cp,";
  if ($tipo=="CNA") {
    $SQL_castigo_clasif .= "($SQL_castigo_bloque1_venc) AS cxc_0a90dias,
                            ($SQL_castigo_bloque2_venc) AS cxc_91a180dias,
                            ($SQL_castigo_bloque3_venc) AS cxc_181a365dias,";
  } else {
    $SQL_castigo_clasif .= "($SQL_castigo_bloque1_venc) AS cxc_0a30dias,
                            ($SQL_castigo_bloque2_venc) AS cxc_31a90dias,
                            ($SQL_castigo_bloque3_venc) AS cxc_91a365dias,";
  }
  $SQL_castigo_clasif .= "($SQL_castigo_bloque4_venc) AS cxc_masde365dias";

  $SQL_castigo_total = "()";
    
  $SQL_cxc = "SELECT c.id as nro_contrato,c.ano,reg.nombre AS regimen,to_char(c.fecha,'DD-MM-YYYY') as fecha_emision,vc.estado,
                     coalesce(va.rut,vp.rut) as rut,coalesce(va.nombre,vp.nombre) as razon_social,
                     (SELECT to_char(max(fecha_venc),'DD-MM-YYYY') FROM finanzas.cobros where id_contrato=c.id) as fecha_venc,
                     '' as cta_ctble,
                     coalesce(c.arancel_efectivo,0)+coalesce(c.arancel_pagare_coleg,0)+coalesce(c.arancel_cheque,0)+coalesce(c.arancel_tarjeta_credito,0) as monto_inicial,
                     pc.id as nro_pagare,'CxC' AS tipo,
                     $SQL_cxc_clasif
              FROM finanzas.contratos AS c 
              LEFT JOIN vista_contratos AS vc USING (id)
              LEFT JOIN vista_alumnos AS va ON va.id=c.id_alumno 
              LEFT JOIN vista_pap     AS vp ON vp.id=c.id_pap 
              LEFT JOIN finanzas.pagares_colegiatura AS pc ON pc.id_contrato=c.id
              LEFT JOIN carreras        AS car ON car.id=c.id_carrera
              LEFT JOIN regimenes       AS reg ON reg.id=car.regimen
              $condicion
              ORDER BY c.fecha DESC ";

  $SQL_castigo = "SELECT c.id as nro_contrato,c.ano,reg.nombre AS regimen,to_char(c.fecha,'DD-MM-YYYY') as fecha_emision,vc.estado,
                         coalesce(va.rut,vp.rut) as rut,coalesce(va.nombre,vp.nombre) as razon_social,
                         (SELECT to_char(max(fecha_venc),'DD-MM-YYYY') FROM finanzas.cobros where id_contrato=c.id) as fecha_venc,
                         '' as cta_ctble,
                         coalesce(c.arancel_efectivo,0)+coalesce(c.arancel_pagare_coleg,0)+coalesce(c.arancel_cheque,0)+coalesce(c.arancel_tarjeta_credito,0) as monto_inicial,
                         pc.id as nro_pagare,'Deterioro' AS tipo,
                         $SQL_castigo_clasif
                  FROM finanzas.contratos AS c 
                  LEFT JOIN vista_contratos AS vc USING (id)
                  LEFT JOIN vista_alumnos AS va ON va.id=c.id_alumno 
                  LEFT JOIN vista_pap     AS vp ON vp.id=c.id_pap 
                  LEFT JOIN finanzas.pagares_colegiatura AS pc ON pc.id_contrato=c.id
                  LEFT JOIN carreras        AS car ON car.id=c.id_carrera
                  LEFT JOIN regimenes       AS reg ON reg.id=car.regimen
                  $condicion
                  ORDER BY c.fecha DESC ";


  $SQL_cxc_clasif = $SQL_sum_clasif = "";
  $SQL_cxc_venc = "coalesce(cxc_masde365dias,0)";
  $SQL_cxc_total = "coalesce(cxc_novenc_lp,0)+coalesce(cxc_novenc_cp,0)+coalesce(cxc_masde365dias,0)";
  if ($tipo == "CNA") {
    $SQL_cxc_clasif = "cxc_0a90dias,cxc_91a180dias,cxc_181a365dias";
    $SQL_sum_clasif = "+coalesce(cxc_0a90dias,0)+coalesce(cxc_91a180dias,0)+coalesce(cxc_181a365dias,0)";
  } else {
    $SQL_cxc_clasif = "cxc_0a30dias,cxc_31a90dias,cxc_91a365dias";
    $SQL_sum_clasif = "+coalesce(cxc_0a30dias,0)+coalesce(cxc_31a90dias,0)+coalesce(cxc_91a365dias,0)";
  }
  $SQL_cxc_total .= $SQL_sum_clasif;
  $SQL_cxc_venc .= $SQL_sum_clasif;

  $SQL_cxc = "SELECT nro_contrato,ano,regimen,fecha_emision,estado,rut,razon_social,fecha_venc,cta_ctble,monto_inicial,nro_pagare,tipo,
                     ($SQL_cxc_total) AS cxc_total,cxc_novenc_lp,cxc_novenc_cp,$SQL_cxc_clasif,cxc_masde365dias,($SQL_cxc_venc) AS cxc_vencidas
              FROM (($SQL_cxc) UNION ($SQL_castigo)) AS cxc";            
  return $SQL_cxc;
  
}

?>