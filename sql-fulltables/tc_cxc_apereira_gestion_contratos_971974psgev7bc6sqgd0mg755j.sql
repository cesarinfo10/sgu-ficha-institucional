COPY (SELECT nro_contrato,fecha_emision,rut,nombre,centro_costo,nro_pagare,
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
               FROM (SELECT c.id as nro_contrato,
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
                      CASE WHEN c.monto_arancel > 0 THEN round(vc.monto_beca_arancel_calc*(1-(coalesce(CASE WHEN c.fecha_condonacion BETWEEN '2022-01-01'::date AND '2023-07-06'::date THEN c.monto_condonacion ELSE null END,0)::float/c.monto_arancel::float))) ELSE 0 END AS beca,
                      CASE WHEN c.monto_arancel > 0 THEN round(c.arancel_cred_interno*(1-(coalesce(CASE WHEN c.fecha_condonacion BETWEEN '2022-01-01'::date AND '2023-07-06'::date THEN c.monto_condonacion ELSE null END,0)::float/c.monto_arancel::float))) ELSE 0 END AS cred_interno,
                      CASE WHEN c.fecha_condonacion BETWEEN '2022-01-01'::date AND '2023-07-06'::date THEN c.monto_condonacion ELSE null END AS condonacion,
                      (CASE WHEN c.fecha_condonacion BETWEEN '2022-01-01'::date AND '2023-07-06'::date AND c.monto_condonacion > 0 AND c.arancel_pagare_coleg > 0 AND c.arancel_efectivo IS NULL AND c.arancel_tarjeta_credito IS NULL 
                                          THEN c.monto_condonacion
                                     WHEN c.fecha_condonacion BETWEEN '2022-01-01'::date AND '2023-07-06'::date AND c.monto_condonacion > 0 AND c.arancel_pagare_coleg > 0 AND (c.arancel_efectivo IS NOt NULL OR c.arancel_tarjeta_credito IS NOT NULL) 
                                          THEN CASE WHEN c.monto_condonacion >= c.arancel_pagare_coleg THEN c.arancel_pagare_coleg ELSE c.monto_condonacion END
                                     ELSE 0
                                END) AS condonacion_arancel_pagare,
                      (CASE WHEN c.fecha_condonacion BETWEEN '2022-01-01'::date AND '2023-07-06'::date AND c.monto_condonacion > 0 AND c.arancel_pagare_coleg IS NULL AND (c.arancel_efectivo IS NOT NULL OR c.arancel_tarjeta_credito IS NOT NULL) 
                                          THEN c.monto_condonacion
                                     WHEN c.fecha_condonacion BETWEEN '2022-01-01'::date AND '2023-07-06'::date AND c.monto_condonacion > 0 AND c.arancel_pagare_coleg > 0 AND (c.arancel_efectivo IS NOt NULL OR c.arancel_tarjeta_credito IS NOT NULL) 
                                          THEN CASE WHEN c.monto_condonacion >= c.arancel_pagare_coleg THEN c.monto_condonacion-c.arancel_pagare_coleg ELSE 0 END
                                     ELSE 0
                                END) AS condonacion_arancel_contado,
                      (CASE WHEN c.fecha_condonacion BETWEEN '2022-01-01'::date AND '2023-07-06'::date AND c.monto_condonacion > 0 AND c.arancel_cheque IS NOT NULL THEN c.monto_condonacion ELSE 0 END) AS condonacion_arancel_cheque,
                      () AS pagos_total,
                      (SELECT sum(pd.monto_pagado) 
                              FROM finanzas.pagos_detalle AS pd 
                              LEFT JOIN finanzas.cobros   AS c on c.id=pd.id_cobro 
                              LEFT JOIN finanzas.pagos    AS p on p.id=pd.id_pago
                              WHERE true  AND c.id_contrato=vc.id AND id_glosa IN (2,20)) AS pagos_arancel_pagare,
                      (SELECT sum(pd.monto_pagado) 
                              FROM finanzas.pagos_detalle AS pd 
                              LEFT JOIN finanzas.cobros   AS c on c.id=pd.id_cobro 
                              LEFT JOIN finanzas.pagos    AS p on p.id=pd.id_pago
                              WHERE true  AND c.id_contrato=vc.id AND id_glosa IN (21,22)) AS pagos_arancel_cheque,
                      (SELECT sum(pd.monto_pagado) 
                              FROM finanzas.pagos_detalle AS pd 
                              LEFT JOIN finanzas.cobros   AS c on c.id=pd.id_cobro 
                              LEFT JOIN finanzas.pagos    AS p on p.id=pd.id_pago
                              WHERE true  AND c.id_contrato=vc.id AND id_glosa IN (3,31)) AS pagos_arancel_contado
               FROM finanzas.contratos AS c 
               LEFT JOIN vista_contratos AS vc USING (id)
               LEFT JOIN vista_alumnos AS va ON va.id=c.id_alumno 
               LEFT JOIN vista_pap     AS vp ON vp.id=c.id_pap 
               LEFT JOIN finanzas.pagares_colegiatura AS pc ON pc.id_contrato=c.id
               LEFT JOIN finanzas.conta_centrosdecosto AS cc ON cc.id_carrera=c.id_carrera
               LEFT JOIN carreras        AS car ON car.id=c.id_carrera
               WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'p[aá][oó]l[aá]' OR  pap.rut ~* 'p[aá][oó]l[aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'p[aá][oó]l[aá]' OR  a.rut ~* 'p[aá][oó]l[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'p[aá][oó]l[aá]' OR  av.rf_rut ~* 'p[aá][oó]l[aá]' OR  text(c.id) ~* 'p[aá][oó]l[aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]ndr[eé][aá]' OR  pap.rut ~* '[aá]ndr[eé][aá]' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]ndr[eé][aá]' OR  a.rut ~* '[aá]ndr[eé][aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]ndr[eé][aá]' OR  av.rf_rut ~* '[aá]ndr[eé][aá]' OR  text(c.id) ~* '[aá]ndr[eé][aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'r[oó]j[aá]s' OR  pap.rut ~* 'r[oó]j[aá]s' OR lower(a.nombres||' '||a.apellidos) ~* 'r[oó]j[aá]s' OR  a.rut ~* 'r[oó]j[aá]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'r[oó]j[aá]s' OR  av.rf_rut ~* 'r[oó]j[aá]s' OR  text(c.id) ~* 'r[oó]j[aá]s' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'f[ií]sch[eé]r' OR  pap.rut ~* 'f[ií]sch[eé]r' OR lower(a.nombres||' '||a.apellidos) ~* 'f[ií]sch[eé]r' OR  a.rut ~* 'f[ií]sch[eé]r' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'f[ií]sch[eé]r' OR  av.rf_rut ~* 'f[ií]sch[eé]r' OR  text(c.id) ~* 'f[ií]sch[eé]r' ) 
               ORDER BY c.fecha DESC ) AS foo) to stdout WITH CSV HEADER