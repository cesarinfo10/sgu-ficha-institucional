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
                      CASE WHEN c.monto_arancel > 0 THEN round(vc.monto_beca_arancel_calc*(1-(coalesce(CASE WHEN c.fecha_condonacion BETWEEN '2022-01-01'::date AND '2023-04-13'::date THEN c.monto_condonacion ELSE null END,0)::float/c.monto_arancel::float))) ELSE 0 END AS beca,
                      CASE WHEN c.monto_arancel > 0 THEN round(c.arancel_cred_interno*(1-(coalesce(CASE WHEN c.fecha_condonacion BETWEEN '2022-01-01'::date AND '2023-04-13'::date THEN c.monto_condonacion ELSE null END,0)::float/c.monto_arancel::float))) ELSE 0 END AS cred_interno,
                      CASE WHEN c.fecha_condonacion BETWEEN '2022-01-01'::date AND '2023-04-13'::date THEN c.monto_condonacion ELSE null END AS condonacion,
                      (CASE WHEN c.fecha_condonacion BETWEEN '2022-01-01'::date AND '2023-04-13'::date AND c.monto_condonacion > 0 AND c.arancel_pagare_coleg > 0 AND c.arancel_efectivo IS NULL AND c.arancel_tarjeta_credito IS NULL 
                                          THEN c.monto_condonacion
                                     WHEN c.fecha_condonacion BETWEEN '2022-01-01'::date AND '2023-04-13'::date AND c.monto_condonacion > 0 AND c.arancel_pagare_coleg > 0 AND (c.arancel_efectivo IS NOt NULL OR c.arancel_tarjeta_credito IS NOT NULL) 
                                          THEN CASE WHEN c.monto_condonacion >= c.arancel_pagare_coleg THEN c.arancel_pagare_coleg ELSE c.monto_condonacion END
                                     ELSE 0
                                END) AS condonacion_arancel_pagare,
                      (CASE WHEN c.fecha_condonacion BETWEEN '2022-01-01'::date AND '2023-04-13'::date AND c.monto_condonacion > 0 AND c.arancel_pagare_coleg IS NULL AND (c.arancel_efectivo IS NOT NULL OR c.arancel_tarjeta_credito IS NOT NULL) 
                                          THEN c.monto_condonacion
                                     WHEN c.fecha_condonacion BETWEEN '2022-01-01'::date AND '2023-04-13'::date AND c.monto_condonacion > 0 AND c.arancel_pagare_coleg > 0 AND (c.arancel_efectivo IS NOt NULL OR c.arancel_tarjeta_credito IS NOT NULL) 
                                          THEN CASE WHEN c.monto_condonacion >= c.arancel_pagare_coleg THEN c.monto_condonacion-c.arancel_pagare_coleg ELSE 0 END
                                     ELSE 0
                                END) AS condonacion_arancel_contado,
                      (CASE WHEN c.fecha_condonacion BETWEEN '2022-01-01'::date AND '2023-04-13'::date AND c.monto_condonacion > 0 AND c.arancel_cheque IS NOT NULL THEN c.monto_condonacion ELSE 0 END) AS condonacion_arancel_cheque,
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
               WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 't[eé][uú]f[eé]rt' OR  pap.rut ~* 't[eé][uú]f[eé]rt' OR lower(a.nombres||' '||a.apellidos) ~* 't[eé][uú]f[eé]rt' OR  a.rut ~* 't[eé][uú]f[eé]rt' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 't[eé][uú]f[eé]rt' OR  av.rf_rut ~* 't[eé][uú]f[eé]rt' OR  text(c.id) ~* 't[eé][uú]f[eé]rt' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'g[oó]nz[aá]l[eé]z' OR  pap.rut ~* 'g[oó]nz[aá]l[eé]z' OR lower(a.nombres||' '||a.apellidos) ~* 'g[oó]nz[aá]l[eé]z' OR  a.rut ~* 'g[oó]nz[aá]l[eé]z' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'g[oó]nz[aá]l[eé]z' OR  av.rf_rut ~* 'g[oó]nz[aá]l[eé]z' OR  text(c.id) ~* 'g[oó]nz[aá]l[eé]z' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[eé]r[ií]ch' OR  pap.rut ~* '[eé]r[ií]ch' OR lower(a.nombres||' '||a.apellidos) ~* '[eé]r[ií]ch' OR  a.rut ~* '[eé]r[ií]ch' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[eé]r[ií]ch' OR  av.rf_rut ~* '[eé]r[ií]ch' OR  text(c.id) ~* '[eé]r[ií]ch' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]l[eé]j[aá]ndr[oó]' OR  pap.rut ~* '[aá]l[eé]j[aá]ndr[oó]' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]l[eé]j[aá]ndr[oó]' OR  a.rut ~* '[aá]l[eé]j[aá]ndr[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]l[eé]j[aá]ndr[oó]' OR  av.rf_rut ~* '[aá]l[eé]j[aá]ndr[oó]' OR  text(c.id) ~* '[aá]l[eé]j[aá]ndr[oó]' ) 
               ORDER BY c.fecha DESC ) AS foo) to stdout WITH CSV HEADER