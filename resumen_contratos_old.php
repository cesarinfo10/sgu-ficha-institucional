<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");
include("validar_modulo.php");

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
$condonado           = $_REQUEST['condonado'];
$repactado           = $_REQUEST['repactado'];
$regimen             = $_REQUEST['regimen'];
$mat_pagada          = $_REQUEST['mat_pagada'];
$cant_cuotas_morosas = $_REQUEST['cant_cuotas_morosas'];
$forma_pago          = $_REQUEST['forma_pago'];
$emisor              = $_REQUEST['emisor'];
$divisor_valores     = $_REQUEST['divisor_valores'];
$fec_ini_emision     = $_REQUEST['fec_ini_emision'];
$fec_fin_emision     = $_REQUEST['fec_fin_emision'];
$fec_ini_pago        = $_REQUEST['fec_ini_pago'];
$fec_fin_pago        = $_REQUEST['fec_fin_pago'];

if (empty($ano))      { $ano = $ANO; }
if (empty($semestre)) { $semestre = null; }
if ($estado == "")    { $estado = '1'; }
if (empty($_REQUEST['cohorte'])) { $cohorte = 0; }
if (empty($_REQUEST['semestre_cohorte'])) { $semestre_cohorte = 0; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }
if ($_REQUEST['cant_cuotas_morosas'] == "") { $cant_cuotas_morosas = -1; }
if ($divisor_valores == "") { $divisor_valores = 1000; }

$condicion = $cond_pagos = "WHERE true ";

if ($ano > 0) { $condicion .= "AND vc.ano=$ano "; }

if ($tipo == "Anual" || $tipo == "Modular") { $semestre = null; }

if (!is_null($semestre)) { $condicion .= "AND vc.semestre=$semestre "; }

if ($estado <> "") {
	if ($estado == "N")  { $condicion .= "AND vc.estado IS NULL "; } 
	elseif ($estado == "1") { $condicion .= "AND vc.estado IS NOT NULL "; }
	elseif ($estado == "D") { $condicion .= "AND vc.estado IN ('S','R','A') "; }
	elseif ($estado != "0") { $condicion .= "AND vc.estado='$estado' "; }
}

if ($tipo <> "" && $tipo <> "0") { $condicion .= "AND vc.tipo='$tipo' "; }

if ($tipo_alumno == "N") { $condicion .= "AND vc.id_pap IS NOT NULL "; }
if ($tipo_alumno == "A") { $condicion .= "AND vc.id_alumno IS NOT NULL "; }

if ($beca == "100")  { $condicion .= "AND vc.id_convenio IS NOT NULL "; }
elseif ($beca <> "") { $condicion .= "AND vc.id_beca_arancel = $beca "; }

if ($condonado == "t") { $condicion .= "AND vc.monto_condonacion IS NOT NULL "; }

if ($repactado == "t") { $condicion .= "AND vvc.monto_repactado IS NOT NULL "; }

if ($mat_pagada == "t") { $condicion .= "AND (mat_pagada > 0 ) "; } 
elseif ($mat_pagada == "f") { $condicion .= "AND (mat_pagada = 0 AND monto_mat>0 AND (coalesce(0,c.porc_beca_mat)<100 OR c.monto_matricula<>coalesce(0,c.monto_beca_mat))) "; } 

if ($cant_cuotas_morosas >= 0) { $condicion .= "AND vc.cuotas_morosas = $cant_cuotas_morosas "; }

if ($emisor > 0) { $condicion .= "AND vc.id_emisor=$emisor "; }

if ($forma_pago <> "") { $condicion .= "AND $forma_pago>0 "; }

if ($regimen <> "" && $regimen <> "t") { $condicion .= "AND (car.regimen = '$regimen') "; }

if ($id_carrera <> "") { $condicion .= "AND vc.id_carrera=$id_carrera "; }

if ($jornada <> "") { $condicion .= "AND vc.jornada='$jornada' "; }

if (empty($fec_ini_emision) && empty($fec_fin_emision)) { 
	$SQL_contratos_per = "SELECT min(vc.fecha::date) AS fec_ini_emision,max(vc.fecha::date) AS fec_fin_emision 
	                      FROM finanzas.contratos AS vc
                          LEFT JOIN carreras   AS car ON car.id=vc.id_carrera
                          $condicion";
	$contratos_per = consulta_sql($SQL_contratos_per);
	if (count($contratos_per) > 0) {
		extract($contratos_per[0]);
		$fec_ini_pago = $fec_ini_emision;
		$fec_fin_pago = $fec_fin_emision;
	}
}

if (!empty($fec_ini_emision) && !empty($fec_fin_emision)) { $condicion .= " AND vc.fecha::date BETWEEN '$fec_ini_emision'::date AND '$fec_fin_emision'::date "; }

if (!empty($fec_ini_pago) && !empty($fec_fin_pago)) { $cond_pagos .= " AND p.fecha::date BETWEEN '$fec_ini_pago'::date AND '$fec_fin_pago'::date "; }


$SQL_pagos_matriculas = "(SELECT sum(pd.monto_pagado) 
                          FROM finanzas.pagos_detalle AS pd 
                          LEFT JOIN finanzas.cobros AS cob ON cob.id=pd.id_cobro
                          LEFT JOIN finanzas.pagos AS pag ON pag.id=pd.id_pago
                          WHERE cob.id_glosa IN (1,10001) AND cob.id_contrato=vc.id AND pag.fecha::date BETWEEN '$fec_ini_pago'::date AND '$fec_fin_pago'::date)";
echo("<!-- $SQL_pagos_matriculas -->");
/*
$SQL_pagos_matriculas = "(SELECT sum(coalesce(monto_abonado,monto)) 
                          FROM finanzas.cobros 
                          WHERE id_glosa=1 AND id_contrato=vc.id AND (pagado or abonado)
                         )";
*/

$SQL_matriculados = "SELECT estado,tipo_alumno AS tipo_beca,count(rut) AS beca_arancel
                     FROM (SELECT DISTINCT ON (coalesce(a.rut,pap.rut)) coalesce(a.rut,pap.rut) AS rut,vc.estado,
                                 'Matriculados' AS tipo_alumno,cod_beca_mat
                          FROM vista_contratos AS vc 
                          LEFT JOIN alumnos    AS a   ON a.id=vc.id_alumno
                          LEFT JOIN pap               ON pap.id=vc.id_pap
                          LEFT JOIN carreras   AS car ON car.id=vc.id_carrera
                          LEFT JOIN becas AS b ON b.id=vc.id_beca_arancel
                          $condicion) AS foo
                    GROUP BY estado,tipo_alumno
                    ORDER BY estado,tipo_alumno";
$matriculados = consulta_sql($SQL_matriculados);
//echo("$SQL_matriculados");

$SQL_becados_mat = "SELECT estado,tipo_alumno AS tipo_beca,count(rut) AS beca_arancel
                    FROM (SELECT DISTINCT ON (coalesce(a.rut,pap.rut)) coalesce(a.rut,pap.rut) AS rut,vc.estado,
                                 'Beneficiario(a)s' AS tipo_alumno,cod_beca_mat
                          FROM vista_contratos AS vc 
                          LEFT JOIN alumnos    AS a   ON a.id=vc.id_alumno
                          LEFT JOIN pap               ON pap.id=vc.id_pap
                          LEFT JOIN carreras   AS car ON car.id=vc.id_carrera
                          LEFT JOIN becas AS b ON b.id=vc.id_beca_arancel
                          $condicion AND (monto_beca_mat > 0 OR porc_beca_mat > 0 OR cod_beca_mat IS NOT NULL)) AS foo
                    GROUP BY estado,tipo_alumno
                    ORDER BY estado,tipo_alumno";
$becados_mat = consulta_sql($SQL_becados_mat);
//echo("$SQL_becados_mat");

$SQL_pagos_aranceles = "(SELECT sum(monto_pagado) 
                         FROM finanzas.cobros AS c 
                         LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_cobro=c.id
                         LEFT JOIN finanzas.pagos         AS p  ON p.id=pd.id_pago 
                         $cond_pagos AND id_glosa NOT IN (1,10001) AND id_contrato=vc.id
                        )";
                        
$SQL_pagos_arancel_anticip = "(SELECT sum(monto_pagado) 
                               FROM finanzas.cobros AS c 
                               LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_cobro=c.id
                               LEFT JOIN finanzas.pagos         AS p  ON p.id=pd.id_pago 
                               $cond_pagos AND id_glosa NOT IN (1,10001) AND id_contrato=vc.id AND date_part('year',p.fecha) < $ano
                              )";
                              
$SQL_pagos_arancel_delano = "(SELECT sum(monto_pagado) 
                              FROM finanzas.cobros AS c 
                              LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_cobro=c.id
                              LEFT JOIN finanzas.pagos         AS p  ON p.id=pd.id_pago 
                              $cond_pagos AND id_glosa NOT IN (1,10001) AND id_contrato=vc.id AND date_part('year',p.fecha) = $ano
                             )";

$SQL_pagos_arancel_post = "(SELECT sum(monto_pagado) 
                            FROM finanzas.cobros AS c 
                            LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_cobro=c.id
                            LEFT JOIN finanzas.pagos         AS p  ON p.id=pd.id_pago 
                            $cond_pagos AND id_glosa NOT IN (1,10001) AND id_contrato=vc.id AND date_part('year',p.fecha) > $ano
                           )";
                        
$SQL_pagos_arancel_pagares = "(SELECT sum(monto_pagado) 
                               FROM finanzas.cobros AS c 
                               LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_cobro=c.id
                               LEFT JOIN finanzas.pagos         AS p  ON p.id=pd.id_pago 
                               $cond_pagos AND id_glosa IN (2,20) AND id_contrato=vc.id
                              )";
                              
$SQL_pagos_arancel_cheques = "(SELECT sum(monto_pagado) 
                               FROM finanzas.cobros AS c 
                               LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_cobro=c.id
                               LEFT JOIN finanzas.pagos         AS p  ON p.id=pd.id_pago 
                               $cond_pagos AND id_glosa IN (21,22) AND id_contrato=vc.id
                              )";
                              
$SQL_pagos_arancel_efectivo = "(SELECT sum(monto_pagado) 
                                FROM finanzas.cobros AS c 
                                LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_cobro=c.id
                                LEFT JOIN finanzas.pagos         AS p  ON p.id=pd.id_pago 
                                $cond_pagos AND id_glosa IN (3,31,10003) AND id_contrato=vc.id
                               )";

$SQL_condona_efectivo = "(CASE WHEN (coalesce(vc.arancel_efectivo,0)+coalesce(vc.arancel_tarjeta_credito,0)>0) AND vc.arancel_pagare_coleg IS NULL AND vc.monto_condonacion IS NOT NULL 
                                    THEN vc.monto_condonacion 
                               WHEN (coalesce(vc.arancel_efectivo,0)+coalesce(vc.arancel_tarjeta_credito,0)>0) AND vc.arancel_pagare_coleg IS NOT NULL AND vc.monto_condonacion IS NOT NULL
                                    THEN CASE WHEN vc.monto_condonacion >= vc.arancel_pagare_coleg THEN vc.monto_condonacion-vc.arancel_pagare_coleg ELSE 0 END 
                               ELSE 0 END) ";

$SQL_condona_cheques  = "(CASE WHEN vc.arancel_cheque IS NOT NULL AND vc.monto_condonacion IS NOT NULL THEN vc.monto_condonacion ELSE 0 END) ";

$SQL_condona_pagares  = "(CASE WHEN (coalesce(vc.arancel_efectivo,0)+coalesce(vc.arancel_tarjeta_credito,0)=0) AND vc.arancel_pagare_coleg IS NOT NULL AND vc.monto_condonacion IS NOT NULL 
                                    THEN vc.monto_condonacion 
                               WHEN (coalesce(vc.arancel_efectivo,0)+coalesce(vc.arancel_tarjeta_credito,0)>0) AND vc.arancel_pagare_coleg IS NOT NULL AND vc.monto_condonacion IS NOT NULL 
                                    THEN CASE WHEN vc.monto_condonacion >= vc.arancel_pagare_coleg THEN vc.arancel_pagare_coleg ELSE vc.monto_condonacion END 
                               ELSE 0 END) ";
                        
$SQL_cobros_ano_ant = "(SELECT sum(monto) FROM finanzas.cobros WHERE id_glosa > 1 AND id_contrato=vc.id AND date_part('year',fecha_venc)<$ano)";
$SQL_cobros_del_ano = "(SELECT sum(monto) FROM finanzas.cobros WHERE id_glosa > 1 AND id_contrato=vc.id AND date_part('year',fecha_venc)=$ano)";
$SQL_cobros_ano_sig = "(SELECT sum(monto) FROM finanzas.cobros WHERE id_glosa > 1 AND id_contrato=vc.id AND date_part('year',fecha_venc)>$ano)";
                     
$SQL_contratos = "SELECT estado,
                         sum(monto_matricula)::bigint AS matricula_bruta,
                         sum(coalesce(monto_beca_mat,round(monto_matricula*porc_beca_mat/100,0)))::bigint AS matricula_beca,
                         sum(monto_mat)::bigint as matricula_neta,
                         sum($SQL_pagos_matriculas)::bigint AS matricula_pagada,
                         sum(monto_arancel)::bigint AS arancel_bruto,
                         sum(monto_beca_arancel_calc::int8)::bigint AS arancel_beca,
                         sum(round(monto_beca_arancel_calc*(1-(coalesce(monto_condonacion,0)::float/monto_arancel::float)))) AS arancel_beca_contable,
                         sum(arancel_cred_interno)::bigint as arancel_cred_int,
                         sum(round(arancel_cred_interno*(1-(coalesce(monto_condonacion,0)::float/monto_arancel::float)))) AS arancel_cred_int_contable,
                         sum(arancel_efectivo)::bigint AS finan_efectivo,
                         sum(arancel_cheque)::bigint as finan_cheque,
                         sum(arancel_pagare_coleg)::bigint as finan_pagare_coleg,
                         sum(arancel_tarjeta_credito)::bigint as finan_tarj_cred,
                         sum(monto_repactado)::bigint as repactado,
                         sum($SQL_cobros_ano_ant)::bigint AS pactado_ano_ant,
                         sum($SQL_cobros_del_ano)::bigint AS pactado_ano_actual,
                         sum($SQL_cobros_ano_sig)::bigint AS pactado_ano_sgte,
                         sum(monto_condonacion)::bigint as condonaciones,
                         sum($SQL_condona_efectivo)::bigint AS condona_efectivo,
                         sum($SQL_condona_cheques)::bigint AS condona_cheques,
                         sum($SQL_condona_pagares)::bigint AS condona_pagares,
                         sum($SQL_pagos_aranceles)::bigint AS pagos_arancel,
                         sum($SQL_pagos_arancel_efectivo)::bigint AS pagos_arancel_efectivo,
                         sum($SQL_pagos_arancel_cheques)::bigint AS pagos_arancel_cheques,
                         sum($SQL_pagos_arancel_pagares)::bigint AS pagos_arancel_pagares,
                         sum($SQL_pagos_arancel_anticip)::bigint AS pagos_arancel_anticip,
                         sum($SQL_pagos_arancel_delano)::bigint AS pagos_arancel_delano,
                         sum($SQL_pagos_arancel_post)::bigint AS pagos_arancel_post
                  FROM vista_contratos AS vc
                  LEFT JOIN carreras   AS car ON car.id=vc.id_carrera
                  $condicion AND vc.monto_condonacion IS NULL
                  GROUP BY estado
                  UNION ALL
                  SELECT estado,
                         sum(monto_matricula)::bigint AS matricula_bruta,
                         sum(coalesce(monto_beca_mat,round(monto_matricula*porc_beca_mat/100,0)))::bigint AS matricula_beca,
                         sum(monto_mat)::bigint as matricula_neta,
                         sum($SQL_pagos_matriculas)::bigint AS matricula_pagada,
                         sum(monto_arancel)::bigint AS arancel_bruto,
                         sum(monto_beca_arancel_calc::int8)::bigint AS arancel_beca,
                         sum(monto_beca_arancel_calc::int8) AS arancel_beca_contable,
                         sum(arancel_cred_interno)::bigint as arancel_cred_int,
                         sum(arancel_cred_interno) AS arancel_cred_int_contable,
                         sum(arancel_efectivo)::bigint AS finan_efectivo,
                         sum(arancel_cheque)::bigint as finan_cheque,
                         sum(arancel_pagare_coleg)::bigint as finan_pagare_coleg,
                         sum(arancel_tarjeta_credito)::bigint as finan_tarj_cred,
                         sum(monto_repactado)::bigint as repactado,
                         sum($SQL_cobros_ano_ant)::bigint AS pactado_ano_ant,
                         sum($SQL_cobros_del_ano)::bigint AS pactado_ano_actual,
                         sum($SQL_cobros_ano_sig)::bigint AS pactado_ano_sgte,
                         sum(0)::bigint as condonaciones,
                         sum(0)::bigint as condona_efectivo,
                         sum(0)::bigint as condona_cheques,
                         sum(0)::bigint as condona_pagares,
                         sum($SQL_pagos_aranceles)::bigint AS pagos_arancel,
                         sum($SQL_pagos_arancel_efectivo)::bigint AS pagos_arancel_efectivo,
                         sum($SQL_pagos_arancel_cheques)::bigint AS pagos_arancel_cheques,
                         sum($SQL_pagos_arancel_pagares)::bigint AS pagos_arancel_pagares,
                         sum($SQL_pagos_arancel_anticip)::bigint AS pagos_arancel_anticip,
                         sum($SQL_pagos_arancel_delano)::bigint AS pagos_arancel_delano,
                         sum($SQL_pagos_arancel_post)::bigint AS pagos_arancel_post
                  FROM vista_contratos AS vc
                  LEFT JOIN carreras   AS car ON car.id=vc.id_carrera
                  $condicion AND vc.monto_condonacion IS NOT NULL AND vc.fecha_condonacion NOT BETWEEN '$fec_ini_pago'::date AND '$fec_fin_pago'::date
                  GROUP BY estado
                  UNION ALL
                  SELECT estado,
                         sum(monto_matricula)::bigint AS matricula_bruta,
                         sum(coalesce(monto_beca_mat,round(monto_matricula*porc_beca_mat/100,0)))::bigint AS matricula_beca,
                         sum(monto_mat)::bigint as matricula_neta,
                         sum($SQL_pagos_matriculas)::bigint AS matricula_pagada,
                         sum(monto_arancel)::bigint AS arancel_bruto,
                         sum(monto_beca_arancel_calc::int8)::bigint AS arancel_beca,
                         sum(round(monto_beca_arancel_calc*(1-(coalesce(monto_condonacion,0)::float/monto_arancel::float)))) AS arancel_beca_contable,
                         sum(arancel_cred_interno)::bigint as arancel_cred_int,
                         sum(round(arancel_cred_interno*(1-(coalesce(monto_condonacion,0)::float/monto_arancel::float)))) AS arancel_cred_int_contable,
                         sum(arancel_efectivo)::bigint AS finan_efectivo,
                         sum(arancel_cheque)::bigint as finan_cheque,
                         sum(arancel_pagare_coleg)::bigint as finan_pagare_coleg,
                         sum(arancel_tarjeta_credito)::bigint as finan_tarj_cred,
                         sum(monto_repactado)::bigint as repactado,
                         sum($SQL_cobros_ano_ant)::bigint AS pactado_ano_ant,
                         sum($SQL_cobros_del_ano)::bigint AS pactado_ano_actual,
                         sum($SQL_cobros_ano_sig)::bigint AS pactado_ano_sgte,
                         sum(monto_condonacion)::bigint as condonaciones,
                         sum($SQL_condona_efectivo)::bigint AS condona_efectivo,
                         sum($SQL_condona_cheques)::bigint AS condona_cheques,
                         sum($SQL_condona_pagares)::bigint AS condona_pagares,
                         sum($SQL_pagos_aranceles)::bigint AS pagos_arancel,
                         sum($SQL_pagos_arancel_efectivo)::bigint AS pagos_arancel_efectivo,
                         sum($SQL_pagos_arancel_cheques)::bigint AS pagos_arancel_cheques,
                         sum($SQL_pagos_arancel_pagares)::bigint AS pagos_arancel_pagares,
                         sum($SQL_pagos_arancel_anticip)::bigint AS pagos_arancel_anticip,
                         sum($SQL_pagos_arancel_delano)::bigint AS pagos_arancel_delano,
                         sum($SQL_pagos_arancel_post)::bigint AS pagos_arancel_post
                  FROM vista_contratos AS vc
                  LEFT JOIN carreras   AS car ON car.id=vc.id_carrera
                  $condicion AND vc.monto_condonacion IS NOT NULL AND vc.fecha_condonacion BETWEEN '$fec_ini_pago'::date AND '$fec_fin_pago'::date
                  GROUP BY estado";
                  
$SQL_contratos = "SELECT estado,
                         sum(matricula_bruta)::bigint AS matricula_bruta,
                         sum(matricula_beca)::bigint AS matricula_beca,
                         sum(matricula_neta)::bigint as matricula_neta,
                         sum(matricula_pagada)::bigint AS matricula_pagada,
                         sum(arancel_bruto)::bigint AS arancel_bruto,
                         sum(arancel_beca::int8)::bigint AS arancel_beca,
                         sum(arancel_beca_contable) AS arancel_beca_contable,
                         sum(arancel_cred_int)::bigint as arancel_cred_int,
                         sum(arancel_cred_int_contable)::bigint as arancel_cred_int_contable,
                         sum(finan_efectivo)::bigint AS finan_efectivo,
                         sum(finan_cheque)::bigint as finan_cheque,
                         sum(finan_pagare_coleg)::bigint as finan_pagare_coleg,
                         sum(finan_tarj_cred)::bigint as finan_tarj_cred,
                         sum(repactado)::bigint as repactado,
                         sum(pactado_ano_ant)::bigint AS pactado_ano_ant,
                         sum(pactado_ano_actual)::bigint AS pactado_ano_actual,
                         sum(pactado_ano_sgte)::bigint AS pactado_ano_sgte,
                         sum(condonaciones)::bigint as condonaciones,
                         sum(condona_efectivo)::bigint as condona_efectivo,
                         sum(condona_cheques)::bigint as condona_cheques,
                         sum(condona_pagares)::bigint as condona_pagares,
                         sum(pagos_arancel)::bigint AS pagos_arancel,
                         sum(pagos_arancel_efectivo)::bigint AS pagos_arancel_efectivo,
                         sum(pagos_arancel_cheques)::bigint AS pagos_arancel_cheques,
                         sum(pagos_arancel_pagares)::bigint AS pagos_arancel_pagares,
                         sum(pagos_arancel_anticip)::bigint AS pagos_arancel_anticip,
                         sum(pagos_arancel_delano)::bigint AS pagos_arancel_delano,
                         sum(pagos_arancel_post)::bigint AS pagos_arancel_post
                   FROM ($SQL_contratos) AS c
                   GROUP BY estado";
/*                  
$SQL_contratos = "SELECT estado,
                         sum(monto_matricula)::bigint AS matricula_bruta,
                         sum(coalesce(monto_beca_mat,round(monto_matricula*porc_beca_mat/100,0)))::bigint AS matricula_beca,
                         sum(monto_mat)::bigint as matricula_neta,
                         sum($SQL_pagos_matriculas)::bigint AS matricula_pagada,
                         sum(monto_arancel)::bigint AS arancel_bruto,
                         sum(monto_beca_arancel_calc::int8)::bigint AS arancel_beca,
                         sum(round(monto_beca_arancel_calc*(1-(coalesce(monto_condonacion,0)::float/monto_arancel::float)))) AS arancel_beca_contable,
                         sum(arancel_cred_interno)::bigint as arancel_cred_int,
                         sum(round(arancel_cred_interno*(1-(coalesce(monto_condonacion,0)::float/monto_arancel::float)))) AS arancel_cred_int_contable,
                         sum(arancel_efectivo)::bigint AS finan_efectivo,
                         sum(arancel_cheque)::bigint as finan_cheque,
                         sum(arancel_pagare_coleg)::bigint as finan_pagare_coleg,
                         sum(arancel_tarjeta_credito)::bigint as finan_tarj_cred,
                         sum(monto_repactado)::bigint as repactado,
                         sum($SQL_cobros_ano_ant)::bigint AS pactado_ano_ant,
                         sum($SQL_cobros_del_ano)::bigint AS pactado_ano_actual,
                         sum($SQL_cobros_ano_sig)::bigint AS pactado_ano_sgte,
                         sum(monto_condonacion)::bigint as condonaciones,
                         sum($SQL_pagos_aranceles)::bigint AS pagos_arancel
                  FROM vista_contratos AS vc
                  LEFT JOIN carreras   AS car ON car.id=vc.id_carrera
                  $condicion AND vc.monto_condonacion IS NULL
                  GROUP BY estado";
*/

$contratos_resumen = consulta_sql($SQL_contratos);
//echo($SQL_contratos);
$SQL_contratos_cond = "SELECT estado,date_part('year',vc.fecha_condonacion) AS ano_cond,
                              sum(monto_condonacion)::bigint as monto_condonaciones
                       FROM vista_contratos AS vc
                       LEFT JOIN carreras   AS car ON car.id=vc.id_carrera
                       $condicion AND monto_condonacion IS NOT NULL AND vc.fecha_condonacion::date BETWEEN '$fec_ini_emision'::date AND '$fec_fin_emision'::date
                       GROUP BY estado,date_part('year',vc.fecha_condonacion)
                       ORDER BY estado,date_part('year',vc.fecha_condonacion)";

$SQL_cond_ano_ant = "SELECT estado,'a) Año anterior' AS ano_cond,
                            sum(monto_condonacion*-1)::bigint as monto_condonaciones
                     FROM vista_contratos AS vc
                     LEFT JOIN carreras   AS car ON car.id=vc.id_carrera
                     $condicion AND monto_condonacion IS NOT NULL AND date_part('year',vc.fecha_condonacion::date) < $ano
                     GROUP BY estado,ano_cond
                     ORDER BY estado,ano_cond";
$SQL_cond_ano_act = "SELECT estado,'b) Año actual' AS ano_cond,
                            sum(monto_condonacion*-1)::bigint as monto_condonaciones
                     FROM vista_contratos AS vc
                     LEFT JOIN carreras   AS car ON car.id=vc.id_carrera
                     $condicion AND monto_condonacion IS NOT NULL AND date_part('year',vc.fecha_condonacion::date) = $ano
                     GROUP BY estado,ano_cond
                     ORDER BY estado,ano_cond";
$SQL_cond_ano_sig = "SELECT estado,'c) Años posteriores' AS ano_cond,
                            sum(monto_condonacion*-1)::bigint as monto_condonaciones
                     FROM vista_contratos AS vc
                     LEFT JOIN carreras   AS car ON car.id=vc.id_carrera
                     $condicion AND monto_condonacion IS NOT NULL AND date_part('year',vc.fecha_condonacion::date) > $ano
                     GROUP BY estado,ano_cond
                     ORDER BY estado,ano_cond";
$SQL_contratos_cond = "SELECT * FROM ($SQL_cond_ano_ant) AS cond_ano_ant
                       UNION
                       SELECT * FROM ($SQL_cond_ano_act) AS cond_ano_act
                       UNION
                       SELECT * FROM ($SQL_cond_ano_sig) AS cond_ano_sig
					   ORDER BY estado,ano_cond";

$contratos_cond = consulta_sql($SQL_contratos_cond);
//echo($SQL_contratos_cond);

$SQL_becas = "SELECT estado,CASE WHEN id_convenio IS NOT NULL THEN 'Convenios de Procedencia' WHEN id_beca_arancel IS NOT NULL THEN  b.nombre END AS tipo_beca,
                     sum(monto_beca_arancel_calc)::bigint AS beca_arancel
              FROM vista_contratos AS vc
              LEFT JOIN carreras   AS car ON car.id=vc.id_carrera
              LEFT JOIN becas AS b ON b.id=vc.id_beca_arancel
              $condicion AND (id_beca_arancel IS NOT NULL OR id_convenio IS NOT NULL)
              GROUP BY estado,tipo_beca
              ORDER BY estado,tipo_beca";
$becas = consulta_sql($SQL_becas);

$SQL_becas_cont = "SELECT estado,CASE WHEN id_convenio IS NOT NULL THEN 'Convenios de Procedencia' WHEN id_beca_arancel IS NOT NULL THEN  b.nombre END AS tipo_beca,
                          sum(round(monto_beca_arancel_calc*(1-(coalesce(monto_condonacion,0)::float/monto_arancel::float))))::bigint AS beca_arancel
                   FROM vista_contratos AS vc
                   LEFT JOIN carreras   AS car ON car.id=vc.id_carrera
                   LEFT JOIN becas AS b ON b.id=vc.id_beca_arancel
                   $condicion AND (id_beca_arancel IS NOT NULL OR id_convenio IS NOT NULL) 
                              AND (vc.monto_condonacion IS NULL 
                               OR (vc.monto_condonacion IS NOT NULL AND vc.fecha_condonacion BETWEEN '$fec_ini_emision'::date AND '$fec_fin_emision'::date))
                   GROUP BY estado,tipo_beca
                   UNION
                   SELECT estado,CASE WHEN id_convenio IS NOT NULL THEN 'Convenios de Procedencia' WHEN id_beca_arancel IS NOT NULL THEN  b.nombre END AS tipo_beca,
                          sum(monto_beca_arancel_calc)::bigint AS beca_arancel
                   FROM vista_contratos AS vc
                   LEFT JOIN carreras   AS car ON car.id=vc.id_carrera
                   LEFT JOIN becas AS b ON b.id=vc.id_beca_arancel
                   $condicion AND (id_beca_arancel IS NOT NULL OR id_convenio IS NOT NULL) 
                              AND (vc.monto_condonacion IS NOT NULL AND vc.fecha_condonacion NOT BETWEEN '$fec_ini_emision'::date AND '$fec_fin_emision'::date)
                   GROUP BY estado,tipo_beca";
$SQL_becas_cont = "SELECT estado,tipo_beca,
                          sum(beca_arancel)::bigint AS beca_arancel
                   FROM ($SQL_becas_cont) AS becas_cont
                   GROUP BY estado,tipo_beca
                   ORDER BY estado,tipo_beca";
$becas_cont = consulta_sql($SQL_becas_cont);
//echo("<!-- $SQL_becas -->");

/* esta consulta retorna todas las becas por rut, aun cuando tengan mas de una beca. La consulta siguiente retorna una beca por rut como maximo.
$SQL_becas_al = "SELECT estado,tipo_beca,count(beca_arancel) AS beca_arancel
                 FROM (SELECT DISTINCT ON (coalesce(a.rut,pap.rut),vc.id_convenio,vc.id_beca_arancel) vc.estado,
                              CASE WHEN vc.id_convenio IS NOT NULL THEN 'Convenios de Procedencia' WHEN id_beca_arancel IS NOT NULL THEN  b.nombre END AS tipo_beca,
                              1 AS beca_arancel
                       FROM vista_contratos AS vc
                       LEFT JOIN alumnos    AS a ON a.id=vc.id_alumno
                       LEFT JOIN pap        ON pap.id=vc.id_pap
                       LEFT JOIN carreras   AS car ON car.id=vc.id_carrera
                       LEFT JOIN becas AS b ON b.id=vc.id_beca_arancel
                       $condicion AND (id_beca_arancel IS NOT NULL OR vc.id_convenio IS NOT NULL)) AS foo
                 GROUP BY estado,tipo_beca
                 ORDER BY estado,tipo_beca"; */

$SQL_becas_al = "SELECT estado,tipo_beca,count(beca_arancel) AS beca_arancel
                 FROM (SELECT DISTINCT ON (coalesce(a.rut,pap.rut)) vc.estado,
                              CASE WHEN vc.id_convenio IS NOT NULL THEN 'Convenios de Procedencia' WHEN id_beca_arancel IS NOT NULL THEN  b.nombre END AS tipo_beca,
                              1 AS beca_arancel
                       FROM vista_contratos AS vc
                       LEFT JOIN alumnos    AS a ON a.id=vc.id_alumno
                       LEFT JOIN pap        ON pap.id=vc.id_pap
                       LEFT JOIN carreras   AS car ON car.id=vc.id_carrera
                       LEFT JOIN becas AS b ON b.id=vc.id_beca_arancel
                       $condicion AND (id_beca_arancel IS NOT NULL OR vc.id_convenio IS NOT NULL)) AS foo
                 GROUP BY estado,tipo_beca
                 ORDER BY estado,tipo_beca";
$becas_al = consulta_sql($SQL_becas_al);
echo("<!-- $SQL_becas_al -->");

$SQL_ci_al = "SELECT estado,tipo_alumno AS tipo_beca,count(tipo_alumno) AS beca_arancel
              FROM (SELECT DISTINCT ON (coalesce(a.rut,pap.rut)) vc.estado,
                           CASE WHEN vc.id_alumno IS NOT NULL THEN 'Estudiantes Antiguos'
                                WHEN vc.id_pap IS NOT NULL    THEN 'Estudiantes Nuevos'
                           END AS tipo_alumno
                    FROM vista_contratos AS vc
                    LEFT JOIN alumnos    AS a   ON a.id=vc.id_alumno
                    LEFT JOIN pap               ON pap.id=vc.id_pap
                    LEFT JOIN carreras   AS car ON car.id=vc.id_carrera
                    $condicion AND (arancel_cred_interno>0)) AS foo
              GROUP BY estado,tipo_alumno
              ORDER BY estado,tipo_alumno";
$ci_al = consulta_sql($SQL_ci_al);
//echo("<!-- $SQL_ci_al -->");

$SQL_repactados = "SELECT estado,tipo_alumno AS tipo_beca,sum(monto_repactado) AS beca_arancel
                   FROM (SELECT vc.estado,
                                CASE WHEN vc.id_alumno IS NOT NULL THEN 'Estudiantes Antiguos'
                                     WHEN vc.id_pap IS NOT NULL    THEN 'Estudiantes Nuevos'
                                END AS tipo_alumno,
                                (SELECT sum(monto)::bigint as monto_repactado 
                                 FROM finanzas.cobros 
                                 WHERE id_contrato=vc.id AND id_glosa IN (20,22) 
                                   AND fecha_reg BETWEEN '$fec_ini_emision'::date AND '$fec_fin_emision'::date) AS monto_repactado
                         FROM vista_contratos      AS vc
                         LEFT JOIN alumnos         AS a   ON a.id=vc.id_alumno
                         LEFT JOIN pap                    ON pap.id=vc.id_pap
                         LEFT JOIN carreras        AS car ON car.id=vc.id_carrera
                         $condicion) AS foo
                   GROUP BY estado,tipo_alumno
                   ORDER BY estado,tipo_alumno";
$repactados = consulta_sql($SQL_repactados);
//echo("<!-- $SQL_repactados -->");

$SQL_repact_al = "SELECT estado,tipo_alumno AS tipo_beca,count(tipo_alumno) AS beca_arancel
                   FROM (SELECT DISTINCT ON (rut) estado,tipo_alumno,monto_repactado 
                         FROM (SELECT coalesce(a.rut,pap.rut) AS rut,vc.estado,
                                      CASE WHEN vc.id_alumno IS NOT NULL THEN 'Estudiantes Antiguos'
                                           WHEN vc.id_pap IS NOT NULL    THEN 'Estudiantes Nuevos'
                                      END AS tipo_alumno,
                                      (SELECT sum(monto)::bigint as monto_repactado 
                                       FROM finanzas.cobros 
                                       WHERE id_contrato=vc.id AND id_glosa IN (20,22) 
                                       AND fecha_reg BETWEEN '$fec_ini_emision'::date AND '$fec_fin_emision'::date) AS monto_repactado
                               FROM vista_contratos      AS vc
                               LEFT JOIN alumnos         AS a   ON a.id=vc.id_alumno
                               LEFT JOIN pap                    ON pap.id=vc.id_pap
                               LEFT JOIN carreras        AS car ON car.id=vc.id_carrera
                               $condicion) AS foo
                         WHERE monto_repactado > 0) AS foo2                   
                   GROUP BY estado,tipo_alumno
                   ORDER BY estado,tipo_alumno";
$repact_al = consulta_sql($SQL_repact_al);
//echo("<!-- $SQL_repact_al -->");

$cond_al_oi = "";
if (!empty($regimen)) {
	$cond_al_oi = "AND c.id_alumno IN (SELECT id FROM alumnos WHERE carrera_actual IN (SELECT id FROM carreras WHERE regimen='$regimen'))";
}

$SQL_otros_ing_desg = "SELECT g.agrupador,g.nombre,sum(pd.monto_pagado) AS monto
                       FROM finanzas.pagos_detalle AS pd
                       LEFT JOIN finanzas.cobros   AS c ON c.id=pd.id_cobro
                       LEFT JOIN finanzas.pagos    AS p ON p.id=pd.id_pago
                       LEFT JOIN finanzas.glosas   AS g ON g.id=c.id_glosa
                       WHERE g.tipo='3 Otros Ingresos' AND date_part('year',p.fecha)=$ano $cond_al_oi
                       GROUP BY g.agrupador,g.nombre
                       ORDER BY g.agrupador,g.nombre";
$otros_ing_desg = consulta_sql($SQL_otros_ing_desg);
//echo($SQL_otros_ing_desg);

$SQL_otros_ingresos = "SELECT g.agrupador,sum(pd.monto_pagado) AS monto
                       FROM finanzas.pagos_detalle AS pd
                       LEFT JOIN finanzas.cobros   AS c ON c.id=pd.id_cobro
                       LEFT JOIN finanzas.pagos    AS p ON p.id=pd.id_pago
                       LEFT JOIN finanzas.glosas   AS g ON g.id=c.id_glosa
                       WHERE g.tipo='3 Otros Ingresos' AND date_part('year',p.fecha)=$ano $cond_al_oi
                       GROUP BY g.agrupador
                       ORDER BY g.agrupador";
$otros_ingresos = consulta_sql($SQL_otros_ingresos);

if (count($contratos_resumen) > 0) {

	$SQL_pagos_min = "(SELECT min(p.fecha::date)
	                   FROM finanzas.cobros AS c 
	                   LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_cobro=c.id 
	                   LEFT JOIN finanzas.pagos         AS p  ON p.id=pd.id_pago
	                   WHERE id_contrato=vc.id
	                  )";

	$SQL_pagos_max = "(SELECT max(p.fecha::date)
	                   FROM finanzas.cobros AS c 
	                   LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_cobro=c.id 
	                   LEFT JOIN finanzas.pagos         AS p  ON p.id=pd.id_pago
	                   WHERE id_contrato=vc.id
	                  )";

	$SQL_pagos_ini_fin = "SELECT min($SQL_pagos_min) AS fec_ini,max($SQL_pagos_max) AS fec_fin
	                      FROM vista_contratos AS vc
	                      LEFT JOIN carreras   AS car ON car.id=vc.id_carrera
	                      $condicion";
	//echo($SQL_pagos_ini_fin);
	$pagos_ini_fin = consulta_sql($SQL_pagos_ini_fin);
	if (empty($fec_ini_pago)) {	$fec_ini_pagos = $pagos_ini_fin[0]['fec_ini']; } else { $fec_ini_pagos = $fec_ini_pago; }
	if (empty($fec_fin_pago)) { $fec_fin_pagos = $pagos_ini_fin[0]['fec_fin']; } else { $fec_fin_pagos = $fec_fin_pago; }

/*
	$SQL_contratos_ini_fin = "SELECT min(vc.fecha::date) AS fec_ini,max(vc.fecha::date) AS fec_fin
	                          FROM vista_contratos AS vc
	                          LEFT JOIN carreras   AS car ON car.id=vc.id_carrera
	                          $condicion";
	//echo($SQL_contratos_ini_fin);
	$contratos_ini_fin = consulta_sql($SQL_contratos_ini_fin);
	$fec_ini_contratos = $contratos_ini_fin[0]['fec_ini'];
	$fec_fin_contratos = $contratos_ini_fin[0]['fec_fin'];
*/
	
	$aEstados_contratos = array("E"=>"Emitido","A"=>"Abandono","S"=>"Suspensión","R"=>"Retiro","Z"=>"Reemplazado");

	$aCond_anos = array();
	$anos_cond  = array_unique(array_column($contratos_cond,'ano_cond'));
	foreach($aEstados_contratos AS $cod_estado => $nom_estado) {
		for ($i=0;$i<count($anos_cond);$i++) {
			$num = $i+1;
			for ($j=0;$j<count($contratos_cond);$j++) {
				if ($contratos_cond[$j]['estado'] == $cod_estado) {
					$aCond_anos["c_(10.$num) Condonación año ".$anos_cond[$i]][$nom_estado] = 0;
					if ($contratos_cond[$j]['ano_cond'] == $anos_cond[$i]) {
						$aCond_anos["c_(10.$num) Condonación año ".$anos_cond[$i]][$nom_estado] = $contratos_cond[$j]['condonaciones'] * -1;
						//$aCond_anos[$nom_estado][$anos_cond[$i]] = $contratos_cond[$j]['condonaciones'];
					}
				}
			}
		}
	}
	//var_dump($aCond_anos);
	
	$aSaldos = $estados_v = array();
	foreach($aEstados_contratos AS $cod_estado => $nom_estado) {
		for ($x=0;$x<count($contratos_resumen);$x++) {
			if ($contratos_resumen[$x]['estado'] == $cod_estado) {
				
				$aSaldos['m_(1) Matrículas Brutas'][$nom_estado]          = intval($contratos_resumen[$x]['matricula_bruta']);
				$aSaldos['m_(2) Becas de Matrícula'][$nom_estado]         = intval($contratos_resumen[$x]['matricula_beca']) * -1;
				$aSaldos['m_(3) Matrículas Netas (1+2)'][$nom_estado]     = intval($contratos_resumen[$x]['matricula_neta']);
				$aSaldos['m_(4) Matrículas Pagadas'][$nom_estado]         = intval($contratos_resumen[$x]['matricula_pagada']);
				$aSaldos['m_(5) Matrículas Adeudadas (3-4)'][$nom_estado] = $aSaldos['m_(4) Matrículas Pagadas'][$nom_estado] 
				                                                          - $aSaldos['m_(3) Matrículas Netas (1+2)'][$nom_estado];
				
				$aSaldos['a_(6) Aranceles Brutos'][$nom_estado]        = intval($contratos_resumen[$x]['arancel_bruto']);
				$aSaldos['a_(7) Becas de Arancel'][$nom_estado]        = intval($contratos_resumen[$x]['arancel_beca'])  * -1;
				$aSaldos['a_(7.1) Becas (contable)'][$nom_estado]      = intval($contratos_resumen[$x]['arancel_beca_contable'])  * -1;
				$aSaldos['a_(8) Créditos Internos'][$nom_estado]       = intval($contratos_resumen[$x]['arancel_cred_int']) * -1;
				$aSaldos['a_(8.1) Créd. Int. (contable)'][$nom_estado] = intval($contratos_resumen[$x]['arancel_cred_int_contable']) * -1;
				$aSaldos['a_(9) Aranceles Netos (6+7+8)'][$nom_estado] = $aSaldos['a_(6) Aranceles Brutos'][$nom_estado] 
				                                                       + $aSaldos['a_(7) Becas de Arancel'][$nom_estado] 
				                                                       + $aSaldos['a_(8) Créditos Internos'][$nom_estado];

				$aSaldos['a_(9.1) Aranceles Netos Contable (6 + 7.1 + 8.1)'][$nom_estado] = $aSaldos['a_(6) Aranceles Brutos'][$nom_estado] 
				                                                                          + $aSaldos['a_(7.1) Becas (contable)'][$nom_estado] 
				                                                                          + $aSaldos['a_(8.1) Créd. Int. (contable)'][$nom_estado];
				
				$aSaldos['f_(9.2) Pactado en Efectivo'][$nom_estado]       = intval($contratos_resumen[$x]['finan_efectivo']);
				$aSaldos['f_(9.3) Pactado con Cheques'][$nom_estado]       = intval($contratos_resumen[$x]['finan_cheque']);
				$aSaldos['f_(9.4) Pactado con Pagaré'][$nom_estado]        = intval($contratos_resumen[$x]['finan_pagare_coleg']);
				$aSaldos['f_(9.5) Pactado con T. de Crédito'][$nom_estado] = intval($contratos_resumen[$x]['finan_tarj_cred']);
				
				$aSaldos['c_(10) Condonaciones'][$nom_estado] = intval($contratos_resumen[$x]['condonaciones']) * -1;
				$aSaldos['c_(10.1) Diferencia por no uso de servicio'][$nom_estado] = $aSaldos['a_(7) Becas de Arancel'][$nom_estado]
				                                                                    - $aSaldos['a_(7.1) Becas (contable)'][$nom_estado]
				                                                                    + $aSaldos['a_(8) Créditos Internos'][$nom_estado]
				                                                                    - $aSaldos['a_(8.1) Créd. Int. (contable)'][$nom_estado];
        $aSaldos['x_(10.2) Condonaciones pactado en Efectivo y T. Crédito'][$nom_estado] = intval($contratos_resumen[$x]['condona_efectivo']) * -1;
        $aSaldos['x_(10.3) Condonaciones pactado con Cheques'][$nom_estado] = intval($contratos_resumen[$x]['condona_cheques']) * -1;
        $aSaldos['x_(10.4) Condonaciones pactado Pagarés'][$nom_estado] = intval($contratos_resumen[$x]['condona_pagares']) * -1;
				
				//$aSaldos = array_merge($aSaldos,$aCond_anos);

				$aSaldos['n_(11) Aranceles Cobrables (9+10)'][$nom_estado] = $aSaldos['a_(9) Aranceles Netos (6+7+8)'][$nom_estado] 
				                                                           + $aSaldos['c_(10) Condonaciones'][$nom_estado];

				$aSaldos['n_(11.1) Aranceles Cobrables Contable (9.1 + 10)'][$nom_estado] = $aSaldos['a_(9.1) Aranceles Netos Contable (6 + 7.1 + 8.1)'][$nom_estado] 
				                                                                          + $aSaldos['c_(10) Condonaciones'][$nom_estado];
				                                                           
				$aSaldos['n_(11.2) Pactado en Año anterior'][$nom_estado]  = intval($contratos_resumen[$x]['pactado_ano_ant']);
				$aSaldos['n_(11.3) Pactado en Año actual'][$nom_estado]    = intval($contratos_resumen[$x]['pactado_ano_actual']);
				$aSaldos['n_(11.4) Pactado en Año siguiente'][$nom_estado] = intval($contratos_resumen[$x]['pactado_ano_sgte']);
				
				$aSaldos['p_(12) Pagos Aranceles'][$nom_estado] = intval($contratos_resumen[$x]['pagos_arancel']);

        $aSaldos['p_(12.1.1) Pagos Aranceles Anticipados'][$nom_estado] = intval($contratos_resumen[$x]['pagos_arancel_anticip']);
				$aSaldos['p_(12.1.2) Pagos Aranceles del Año'][$nom_estado]     = intval($contratos_resumen[$x]['pagos_arancel_delano']);
				$aSaldos['p_(12.1.3) Pagos Aranceles Posteriores'][$nom_estado] = intval($contratos_resumen[$x]['pagos_arancel_post']);
				
        $aSaldos['d_(12.2) Pagos Aranceles pactado en Efectivo o T. Crédito'][$nom_estado] = intval($contratos_resumen[$x]['pagos_arancel_efectivo']);
        $aSaldos['d_(12.3) Pagos Aranceles pactado con Cheques'][$nom_estado] = intval($contratos_resumen[$x]['pagos_arancel_cheques']);
        $aSaldos['d_(12.4) Pagos Aranceles pactado con Pagaré'][$nom_estado] = intval($contratos_resumen[$x]['pagos_arancel_pagares']);

				$aSaldos['s_(13) Total Saldos Adeudados (11-12)'][$nom_estado] = $aSaldos['n_(11) Aranceles Cobrables (9+10)'][$nom_estado] 
				                                                           - $aSaldos['p_(12) Pagos Aranceles'][$nom_estado];
        
     //   $aSaldos['s_(13.1.1) Saldos Adeudados pactado en Efectivo y T. Credito (11-12)'][$nom_estado] = $aSaldos['n_(11) Aranceles Cobrables (9+10)'][$nom_estado] 
		//		                                                           - $aSaldos['p_(12) Pagos Aranceles'][$nom_estado];

				$aSaldos['s_(13.1) Total Saldos Adeudados Contables (11.1 + 10.1 - 12)'][$nom_estado] = $aSaldos['n_(11.1) Aranceles Cobrables Contable (9.1 + 10)'][$nom_estado] 
				                                                                                  + $aSaldos['c_(10.1) Diferencia por no uso de servicio'][$nom_estado] 
				                                                                                  - $aSaldos['p_(12) Pagos Aranceles'][$nom_estado];

        $aSaldos['z_(13.2) Saldos Adeudados pactado en Efectivo o T. Crédito (9.2 + 9.5 + 10.2 - 12.2)'][$nom_estado] = $aSaldos['f_(9.2) Pactado en Efectivo'][$nom_estado] 
                                                                                                                      + $aSaldos['f_(9.5) Pactado con T. de Crédito'][$nom_estado] 
                                                                                                                      + $aSaldos['x_(10.2) Condonaciones pactado en Efectivo y T. Crédito'][$nom_estado]
                                                                                                                      - $aSaldos['d_(12.2) Pagos Aranceles pactado en Efectivo o T. Crédito'][$nom_estado];

        $aSaldos['z_(13.3) Saldos Adeudados pactados en Cheques (9.3 + 10.3 - 12.3)'][$nom_estado] = $aSaldos['f_(9.3) Pactado con Cheques'][$nom_estado] 
                                                                                                   + $aSaldos['x_(10.3) Condonaciones pactado con Cheques'][$nom_estado]
                                                                                                   - $aSaldos['d_(12.3) Pagos Aranceles pactado con Cheques'][$nom_estado];
                       
        $aSaldos['z_(13.4) Cuentas por Cobrar (pactados con Pagarés) (9.4 + 10.4 - 12.4)'][$nom_estado] = $aSaldos['f_(9.4) Pactado con Pagaré'][$nom_estado] 
                                                                                                        + $aSaldos['x_(10.4) Condonaciones pactado Pagarés'][$nom_estado]
                                                                                                        - $aSaldos['d_(12.4) Pagos Aranceles pactado con Pagaré'][$nom_estado];
                       
//				$aSaldos['r_Repactaciones'][$nom_estado] = intval($contratos_resumen[$x]['repactado']);

				$estados_v[$cod_estado] = $nom_estado;
			}
		}
	}
	
	$aBecas = $estados_vv = array();
	foreach($aEstados_contratos AS $cod_estado => $nom_estado) {
		for ($x=0;$x<count($becas);$x++) {
			$nombre_beca = $becas[$x]['tipo_beca'];
			$nom_beca = "b_$nombre_beca";
			if (!isset($aBecas[$nom_beca][$nom_estado])) { $aBecas[$nom_beca][$nom_estado]= 0; }
			if ($becas[$x]['estado'] == $cod_estado) {
				$aBecas[$nom_beca][$nom_estado] = intval($becas[$x]['beca_arancel']);
				$estados_vv[$cod_estado] = $nom_estado;
			}
		}
	}
	
	$aBecas_cont = $estados_vv = array();
	foreach($aEstados_contratos AS $cod_estado => $nom_estado) {
		for ($x=0;$x<count($becas_cont);$x++) {
			$nombre_beca = $becas_cont[$x]['tipo_beca'];
			$nom_beca = "b_$nombre_beca";
			if (!isset($aBecas_cont[$nom_beca][$nom_estado])) { $aBecas_cont[$nom_beca][$nom_estado]= 0; }
			if ($becas_cont[$x]['estado'] == $cod_estado) {
				$aBecas_cont[$nom_beca][$nom_estado] = intval($becas_cont[$x]['beca_arancel']);
				$estados_vv[$cod_estado] = $nom_estado;
			}
		}
	}
	
	$aBecas_al = $estados_vv = array();
	foreach($aEstados_contratos AS $cod_estado => $nom_estado) {
		for ($x=0;$x<count($becas_al);$x++) {
			$nombre_beca = $becas_al[$x]['tipo_beca'];
			$nom_beca = "b_$nombre_beca";
			if (!isset($aBecas_al[$nom_beca][$nom_estado])) { $aBecas_al[$nom_beca][$nom_estado]= 0; }
			if ($becas_al[$x]['estado'] == $cod_estado) {
				$aBecas_al[$nom_beca][$nom_estado] = intval($becas_al[$x]['beca_arancel']);
				$estados_vv[$cod_estado] = $nom_estado;
			}
		}
	}	
  
  $aMatriculados = $estados_vv = array();
	foreach($aEstados_contratos AS $cod_estado => $nom_estado) {
		for ($x=0;$x<count($matriculados);$x++) {
			$nombre_beca = $matriculados[$x]['tipo_beca'];
			$nom_beca = "b_$nombre_beca";
			if (!isset($aMatriculados[$nom_beca][$nom_estado])) { $aMatriculados[$nom_beca][$nom_estado]= 0; }
			if ($matriculados[$x]['estado'] == $cod_estado) {
				$aMatriculados[$nom_beca][$nom_estado] = intval($matriculados[$x]['beca_arancel']);
				$estados_vv[$cod_estado] = $nom_estado;
			}
		}
	} 
  
  $aBecados_mat_al = $estados_vv = array();
	foreach($aEstados_contratos AS $cod_estado => $nom_estado) {
		for ($x=0;$x<count($becados_mat);$x++) {
			$nombre_beca = $becados_mat[$x]['tipo_beca'];
			$nom_beca = "b_$nombre_beca";
			if (!isset($aBecados_mat_al[$nom_beca][$nom_estado])) { $aBecados_mat_al[$nom_beca][$nom_estado]= 0; }
			if ($becados_mat[$x]['estado'] == $cod_estado) {
				$aBecados_mat_al[$nom_beca][$nom_estado] = intval($becados_mat[$x]['beca_arancel']);
				$estados_vv[$cod_estado] = $nom_estado;
			}
		}
	}
  
  $aContratos_cond = $estados_vv = array();
	foreach($aEstados_contratos AS $cod_estado => $nom_estado) {
		for ($x=0;$x<count($contratos_cond);$x++) {
			$ano_cond = "c_".$contratos_cond[$x]['ano_cond'];
			if (!isset($aContratos_cond[$ano_cond][$nom_estado])) { $aContratos_cond[$ano_cond][$nom_estado]= 0; }
			if ($contratos_cond[$x]['estado'] == $cod_estado) {
				$aContratos_cond[$ano_cond][$nom_estado] = intval($contratos_cond[$x]['monto_condonaciones']);
				$estados_vv[$cod_estado] = $nom_estado;
			}
		}
	}

	$aRepactados_al = $estados_vv = array();
	foreach($aEstados_contratos AS $cod_estado => $nom_estado) {
		for ($x=0;$x<count($repactados);$x++) {
			$nombre_beca = $repactados[$x]['tipo_beca'];
			$nom_beca = "b_$nombre_beca";
			if (!isset($aRepactados_al[$nom_beca][$nom_estado])) { $aRepactados_al[$nom_beca][$nom_estado]= 0; }
			if ($repactados[$x]['estado'] == $cod_estado) {
				$aRepactados_al[$nom_beca][$nom_estado] = intval($repactados[$x]['beca_arancel']);
				$estados_vv[$cod_estado] = $nom_estado;
			}
		}
	}
	
	$aRepact_al = $estados_vv = array();
	foreach($aEstados_contratos AS $cod_estado => $nom_estado) {
		for ($x=0;$x<count($repact_al);$x++) {
			$nombre_beca = $repact_al[$x]['tipo_beca'];
			$nom_beca = "b_$nombre_beca";
			if (!isset($aRepact_al[$nom_beca][$nom_estado])) { $aRepact_al[$nom_beca][$nom_estado]= 0; }
			if ($repact_al[$x]['estado'] == $cod_estado) {
				$aRepact_al[$nom_beca][$nom_estado] = intval($repact_al[$x]['beca_arancel']);
				$estados_vv[$cod_estado] = $nom_estado;
			}
		}
	}
	
	$aCI_al = $estados_vv = array();
	foreach($aEstados_contratos AS $cod_estado => $nom_estado) {
		for ($x=0;$x<count($ci_al);$x++) {
			$nombre_beca = $ci_al[$x]['tipo_beca'];
			$nom_beca = "b_$nombre_beca";
			if (!isset($aCI_al[$nom_beca][$nom_estado])) { $aCI_al[$nom_beca][$nom_estado]= 0; }
			if ($ci_al[$x]['estado'] == $cod_estado) {
				$aCI_al[$nom_beca][$nom_estado] = intval($ci_al[$x]['beca_arancel']);
				$estados_vv[$cod_estado] = $nom_estado;
			}
		}
	}
	
	$HTML = "";
	foreach($aSaldos AS $cat_aSaldo => $montos_aSaldo) { 
		$cat = substr($cat_aSaldo,0,2);

		if (!empty($cat_aux) && $cat_aux <> substr($cat_aSaldo,0,2)) { $HTML .= "<tr><td class='textoTabla' colspan='6'>&nbsp;</td></tr>"; }
		
		$categoria = substr($cat_aSaldo,2);
		$HTML .= "<tr class='filaTabla'>\n"
		      .  "  <td class='tituloTabla' style='text-align: left'>$categoria</td>\n";
		$tot_cat = $j = 0;
		reset($estados_v);
		//var_dump($montos_aSaldo);
		foreach($montos_aSaldo AS $estado_aSaldo => $monto_aSaldo) {
			$estado_contrato = each($estados_v);
			//var_dump($estado_aSaldo);
			if ($estado_aSaldo == $estado_contrato[value]) {
				$estilo = "";
				if ($monto_aSaldo < 0) { $estilo = "color: #ff0000"; }
				$monto = "<span style='$estilo'>".number_format(round($monto_aSaldo/$divisor_valores,0),0,',','.')."</span>";
				
				$HTML .= "  <td class='textoTabla' style='text-align: right'>$monto</td>\n";
				$tot_cat += $monto_aSaldo;
			} else {
				$HTML .= "  <td class='textoTabla' style='text-align: right'>0</td>\n";
			}
		}
		$estilo = "";
		if ($tot_cat < 0) { $estilo = "color: #ff0000"; }
		$monto = "<span style='$estilo'>".number_format(round($tot_cat/$divisor_valores,0),0,',','.')."</span>";

		$HTML .= "  <td class='textoTabla' style='text-align: right'><b>$monto</b></td>\n"
		      .  "</tr>";
		$cat_aux = $cat;
	}
	$HTML_resumen_contratos = $HTML;

  $HTML_resumen_matriculados   = html_resumen_becas($aMatriculados,1,"(1.1) Detalle de Matriculados Periodo $ano","Total Matriculados");

  $HTML_resumen_becados_mat_al = html_resumen_becas($aBecados_mat_al,1,"(2.1) Detalle Beneficiarios de Becas de Matrícula Periodo $ano","Total Becados Mat.");

// Resumen de Becas (detallado por nombre de beca)

	$HTML_resumen_becas = html_resumen_becas($aBecas,$divisor_valores,"(7) Detalle de Becas Periodo $ano","Total Becas");
	
	$HTML_resumen_becas_cont = html_resumen_becas($aBecas_cont,$divisor_valores,"(7.1) Detalle de Becas Contables Periodo $ano","Total Becas Contables");
	
	$HTML_resumen_becas_al = html_resumen_becas($aBecas_al,1,"(7) Detalle Beneficiarios de Becas Periodo $ano","Total Becados");	

// Resumen de Credito Interno	
	
	$HTML_resumen_ci_al = html_resumen_becas($aCI_al,1,"(8) Detalle Beneficiarios de Crédito Interno Periodo $ano","Total Beneficiarios CI");
	
// Resumen de Repactaciones

	$HTML_resumen_repact = html_resumen_becas($aRepactados_al,$divisor_valores,"Detalle de Repactaciones Periodo $ano","Total Repactado");
	
	$HTML_resumen_repact_al = html_resumen_becas($aRepact_al,1,"Detalle de Beneficiarios de Repactaciones Periodo $ano","Total Beneficiarios");

  $HTML_resumen_condonaciones = html_resumen_becas($aContratos_cond,$divisor_valores,"(10) Detalle Condonaciones de los Contratos Periodo $ano","Total Condonaciones");

	//var_dump($aBecas);

//Otros Ingresos	
	$HTML = "";
	$tot_oi = 0;
	for ($x=0;$x<count($otros_ingresos);$x++) {
		$monto = "<span>".number_format(round($otros_ingresos[$x]['monto']/$divisor_valores,0),0,',','.')."</span>";
		$tot_oi += $otros_ingresos[$x]['monto'];
		
		$HTML .= "<tr>\n"
		      .  "  <td class='tituloTabla' style='text-align: left'>{$otros_ingresos[$x]['agrupador']}</td>\n"
		      .  "  <td class='textoTabla' style='text-align: right'>$monto</td>\n"
		      .  "</tr>\n";
	}

	$monto = "<span>".number_format(round($tot_oi/$divisor_valores,0),0,',','.')."</span>";
	$HTML .= "<tr>\n"
	      .  "  <td class='tituloTabla' style='text-align: right'>Total Otros Ingresos:</td>\n"
	      .  "  <td class='textoTabla' style='text-align: right'><b>$monto</b></td>\n"
	      .  "</tr>\n";
	$HTML_otros_ingresos = $HTML;	
	
//Otros Ingresos desglosados
	$HTML = "";
	$tot_oi = 0;
	$tot_agrup = array();
	$agrup = "";
	for ($x=0;$x<count($otros_ing_desg);$x++) { $tot_agrup[$otros_ing_desg[$x]['agrupador']]++; }
	//var_dump($tot_agrup);
	for ($x=0;$x<count($otros_ing_desg);$x++) {
		$monto = "<span>".number_format(round($otros_ing_desg[$x]['monto']/$divisor_valores,0),0,',','.')."</span>";
		$tot_oi += $otros_ing_desg[$x]['monto'];
		
		$HTML .= "<tr>\n";
		if ($agrup <> $otros_ing_desg[$x]['agrupador']) {
			$HTML .= "  <td class='tituloTabla' rowspan='{$tot_agrup[$otros_ing_desg[$x]['agrupador']]}' style='width: 10px; text-align: center'>\n"
			      .  "    <div style='-webkit-transform: rotate(-90deg); -moz-transform: rotate(-90deg)'>{$otros_ing_desg[$x]['agrupador']}</div>\n"
			      .  "  </td>\n";
		}
		
		$HTML .= "  <td class='tituloTabla' style='text-align: left'>{$otros_ing_desg[$x]['nombre']}</td>\n"
		      .  "  <td class='textoTabla' style='text-align: right'>$monto</td>\n"
		      .  "</tr>\n";
		$agrup = $otros_ing_desg[$x]['agrupador'];
	}

	$monto = "<span>".number_format(round($tot_oi/$divisor_valores,0),0,',','.')."</span>";
	$HTML .= "<tr>\n"
	      .  "  <td class='tituloTabla' colspan='2' style='text-align: right'>Total Otros Ingresos:</td>\n"
	      .  "  <td class='textoTabla' style='text-align: right'><b>$monto</b></td>\n"
	      .  "</tr>\n";
	$HTML_otros_ing_desg = $HTML;	
	
}
		
$SQL_monto_pagado   = "SELECT sum(coalesce(monto_abonado,monto)) FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa>1 AND (pagado OR abonado)";
$SQL_mat_pagada     = "SELECT count(id) FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa=1 AND pagado";
$SQL_monto_saldot   = "SELECT sum(coalesce(monto-monto_abonado,monto)) FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa>1 AND (NOT pagado OR abonado)";
$SQL_monto_moroso   = "SELECT sum(coalesce(monto-monto_abonado,monto)) FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa>1 AND fecha_venc < now() AND (NOT pagado OR abonado)";
$SQL_cuotas_morosas = "SELECT count(id) FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa>1 AND fecha_venc < now() AND (NOT pagado OR abonado)";

$contratos     = consulta_sql($SQL_contratos);
//var_dump($contratos);

$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "")      { $cond_carreras .= "AND regimen='$regimen' "; }
$carreras = consulta_sql("SELECT id,nombre FROM carreras $cond_carreras ORDER BY nombre;");

$cohortes = $anos;

$SEMESTRES = array(array("id"=>0,"nombre"=>0),
                   array("id"=>1,"nombre"=>1),
                   array("id"=>2,"nombre"=>2));

$becas = consulta_sql("SELECT id,nombre from becas ORDER BY nombre");
$becas = array_merge($becas,array(array("id"=>100,"nombre"=>"Procedencia")));

$emisores = consulta_sql("SELECT id,nombre FROM vista_usuarios WHERE id IN (SELECT id_emisor FROM finanzas.contratos WHERE ano=$ano)");

$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();

$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);

$FORMAS_PAGO = array(array('id'=>"vc.arancel_efectivo",       'nombre'=>"Efectivo"),
                     array('id'=>"vc.arancel_cheque",         'nombre'=>"Cheque(s)"),
                     array('id'=>"vc.arancel_pagare_coleg",   'nombre'=>"Pagaré Colegiatura"),
                     array('id'=>"vc.arancel_tarjeta_credito",'nombre'=>"Tarjeta de Crédito"));

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

$DIVISORES = array(array('id'=>1      ,'nombre'=>"en pesos"),
                   array('id'=>1000   ,'nombre'=>"en miles de pesos"),
                   array('id'=>1000000,'nombre'=>"en millones de pesos"));
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
  <tr>
    <td class="celdaFiltro">
      <div align='left'>Periodo matrícula:</div>
      <select class="filtro" name="semestre" onChange="reestablecer_fechas(); submitform();">
        <option value=""></option>
        <?php echo(select($SEMESTRES,$semestre)); ?>    
      </select>
      - 
      <select class="filtro" name="ano" onChange="reestablecer_fechas(); submitform();">
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
      <select class="filtro" name="beca" onChange="submitform();">
        <option value="">Todas</option>
        <?php echo(select($becas,$beca)); ?>    
      </select>
    </td>
    <td class="celdaFiltro">
      <div align='left'>Emisor:</div>
      <select class="filtro" name="emisor" onChange="submitform();">
        <option value="t">Todos</option>
        <?php echo(select($emisores,$emisor)); ?>
      </select>
    </td>
	<td class="celdaFiltro">
      Mostrar valores:<br>
      <select name="divisor_valores" onChange="submitform();" class="filtro">
        <?php echo(select($DIVISORES,$divisor_valores)); ?>
      </select>
    </td>
</table>
<table cellpadding="1" border="0" cellspacing="2" width="auto">
<!--    <td class="celdaFiltro">
      <div align='left'>Condonado:</div>
      <select class="filtro" name="condonado" onChange="submitform();">
        <option value="">Todos</option>
        <?php echo(select($sino,$condonado)); ?>    
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
      <div align='left'>Cuotas Morosas:</div>
      <select class="filtro" name="cant_cuotas_morosas" onChange="submitform();">
        <option value="-1">Todos</option>
        <?php echo(select($CUOTAS_MOROSAS,$cant_cuotas_morosas)); ?>
      </select>
    </td>
    <td class="celdaFiltro">
      <div align='left'>Forma de Pago:</div>
      <select class="filtro" name="forma_pago" onChange="submitform();">
        <option value="">Todas</option>
        <?php echo(select($FORMAS_PAGO,$forma_pago)); ?>
      </select>
    </td> -->
    <td class="celdaFiltro">
      <div align='left'>Emisión de Contratos entre:</div>
      <div style='font-weight: normal'>
        <input type="date" name="fec_ini_emision" value="<?php echo($fec_ini_emision); ?>" class="boton" onBlur="formulario.fec_ini_pago.value=this.value" style='font-size: 8pt'>
        y <input type="date" name="fec_fin_emision" value="<?php echo($fec_fin_emision); ?>" class="boton" onBlur="formulario.fec_fin_pago.value=this.value" style='font-size: 8pt'>
        <input type="submit" name="Buscar" value="Buscar">
      </div>
    </td>
    <td class="celdaFiltro">
      <div align='left'>Pagos y Condonaciones entre:</div>
      <div style='font-weight: normal'>
        <input type="date" name="fec_ini_pago" value="<?php echo($fec_ini_pagos); ?>" class="boton" style='font-size: 8pt'>
        y <input type="date" name="fec_fin_pago" value="<?php echo($fec_fin_pagos); ?>" class="boton" style='font-size: 8pt'>
        <input type="submit" name="Buscar" value="Buscar">
      </div>
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
      <div align='left'>Régimen:</div>
      <select class="filtro" name="regimen" onChange="submitform();">
        <option value="t">Todos</option>
        <?php echo(select($REGIMENES,$regimen)); ?>
      </select>
    </td>
  </tr>
</table>
</form>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla'>
  <tr class='filaTituloTabla'>
    <td colspan='<?php echo(count($estados_v)+2); ?>' class='tituloTabla' style='text-align: left'>Periodo <?php echo($ano); ?></td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='text-align: center'></td>
    <?php foreach($estados_v AS $cod_estado => $nom_estado) { echo("<td class='tituloTabla' style='text-align: center'>$nom_estado</td>"); } ?>
    <td class='tituloTabla' style='text-align: center'>Total</td>
  </tr>
  <?php echo($HTML_resumen_contratos); ?>
</table>
<br>
<?php echo($HTML_resumen_matriculados); ?>
<br>
<?php echo($HTML_resumen_becados_mat_al); ?>
<br>
<?php echo($HTML_resumen_becas); ?>
<br>
<?php echo($HTML_resumen_becas_cont); ?>
<br>
<?php echo($HTML_resumen_becas_al); ?>
<br>
<?php echo($HTML_resumen_ci_al); ?>
<br>
<?php echo($HTML_resumen_repact); ?>
<br>
<?php echo($HTML_resumen_repact_al); ?>
<br>
<?php echo($HTML_resumen_condonaciones); ?>
<br>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td colspan='<?php echo(count($aEstados_contratos)+2); ?>' class='tituloTabla' style='text-align: left'>(14) Detalle de Otros Ingresos Periodo <?php echo($ano); ?></td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='text-align: center'></td>
    <td class='tituloTabla' style='text-align: center'>Total</td>
  </tr>
  <?php echo($HTML_otros_ingresos); ?>
</table>
<br>
<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td colspan='3' class='tituloTabla' style='text-align: left'>(14) Detalle de Otros Ingresos Desglosados Periodo <?php echo($ano); ?></td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='text-align: center'></td>
    <td class='tituloTabla' style='text-align: center'></td>
    <td class='tituloTabla' style='text-align: center'>Total</td>
  </tr>
  <?php echo($HTML_otros_ing_desg); ?>
</table>

<script>
function reestablecer_fechas() {
	formulario.fec_ini_emision.value = null;
	formulario.fec_fin_emision.value = null;
	formulario.fec_ini_pago.value = null;
	formulario.fec_fin_pago.value = null;
}

function mostrar_elementos(nombre_total) {
	var filas = document.getElementById(nombre_total).getElementsByTagName("tr");
	var x;
	var display;
	var boton;
	if (document.getElementById('boton_'+nombre_total).textContent == '+') { 
		display = '';
		boton = '-';
	} else {
		display = 'none';
		boton = '+';
	}
	for (x=0; x<filas.length; x++) { 
		if (filas[x].className == 'filaTabla') {
			filas[x].style.display=display;
		}
	}
	document.getElementById('boton_'+nombre_total).innerHTML = boton;
}
</script>

<?php

function html_resumen_becas($aBecas,$divisor_valores,$titulo,$nombre_total) {
	//var_dump($aBecas);
	global $aEstados_contratos;
	$HTML = $cat_aux = "";
	$tot_becas = 0;
	$tot_estados = array();
	
	$colspan_titulo = count($aEstados_contratos)+2;
	$HTML .= "<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' id='$nombre_total' style='margin-top: 5px'>\n"
	      .  "  <tr class='filaTituloTabla'>\n"
	      .  "    <td colspan='$colspan_titulo' class='tituloTabla' style='text-align: left'>"
	      .         $titulo
	      .  "      <a class='boton' onClick=\"mostrar_elementos('$nombre_total');\" id='boton_$nombre_total'>+</a>"
	      .  "    </td>\n"
	      .  "  </tr>\n"
	      .  "  <tr class='filaTituloTabla'>\n"
	      .  "    <td class='tituloTabla' style='text-align: center'></td>\n";
	foreach($aEstados_contratos AS $cod_estado => $nom_estado) { $HTML .= "    <td class='tituloTabla' style='text-align: center'>$nom_estado</td>\n"; }
	$HTML .= "    <td class='tituloTabla' style='text-align: center'>Total</td>\n"
	      .  "  </tr>\n";
	
	foreach($aBecas AS $cat_aBeca => $montos_aBeca) { 
		$cat = substr($cat_aBeca,0,2);

		if (!empty($cat_aux) && $cat_aux <> substr($cat_aBeca,0,2)) { $HTML .= "<tr><td class='textoTabla' colspan='6'>&nbsp;</td></tr>\n"; }
		
		$i = 0;
		$categoria = substr($cat_aBeca,2);
		$HTML .= "  <tr class='filaTabla' name='$nombre_total' style='display: none'>\n"
		      .  "    <td class='textoTabla' style='text-align: left; font-weight: normal'>$categoria</td>\n";
		$tot_cat = $j = 0;
		reset($aEstados_contratos);
		foreach($montos_aBeca AS $estado_aBeca => $monto_aBeca) {
			$estado_contrato = each($aEstados_contratos);
			//var_dump($estado_aBeca);
			if ($estado_aBeca == $estado_contrato[value]) {
				$estilo = "";
				if ($monto_aBeca < 0) { $estilo = "color: #ff0000"; }
				$monto = "<span style='$estilo'>".number_format(round($monto_aBeca/$divisor_valores,0),0,',','.')."</span>";
				
				$HTML .= "    <td class='textoTabla' style='text-align: right'>$monto</td>\n";
				$tot_cat += $monto_aBeca;
				$tot_estados[$estado_aBeca] += $monto_aBeca; 
			} else {
				$HTML .= "    <td class='textoTabla' style='text-align: right'>0</td>\n";
			}
		}
		$estilo = "";
		if ($tot_cat < 0) { $estilo = "color: #ff0000"; }
		$monto = "<span style='$estilo'>".number_format(round($tot_cat/$divisor_valores,0),0,',','.')."</span>";
		$tot_becas += $tot_cat;

		$HTML .= "    <td class='textoTabla' style='text-align: right'><b>$monto</b></td>\n"
		      .  "  </tr>";
		$cat_aux = $cat;
		$i++;
	}
	//var_dump($tot_estados);
	$HTML .= "  <tr class='filaTituloTabla'>\n"
	      .  "    <td class='celdaNombreAttr' style='text-align: right'>$nombre_total:</td>\n";
	foreach($tot_estados AS $monto) { 
		$HTML .= "    <td class='celdaNombreAttr' style='text-align: right'><b>".number_format(round($monto/$divisor_valores,0),0,',','.')."</b></td>\n";	
	}
	$HTML .= "    <td class='celdaNombreAttr' style='text-align: right'><b>".number_format(round($tot_becas/$divisor_valores,0),0,',','.')."</b></td>\n"
	      .  "  </tr>\n"
	      .  "</table>\n";
	return $HTML;
}

?>
