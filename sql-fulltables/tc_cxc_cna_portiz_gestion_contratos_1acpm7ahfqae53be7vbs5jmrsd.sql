COPY (SELECT nro_contrato,ano,fecha_emision,estado,rut,razon_social,fecha_venc,cta_ctble,monto_inicial,nro_pagare,tipo,
                     (coalesce(cxc_novenc_lp,0)+coalesce(cxc_novenc_cp,0)+coalesce(cxc_masde365dias,0)+coalesce(cxc_0a90dias,0)+coalesce(cxc_91a180dias,0)+coalesce(cxc_181a365dias,0)) AS cxc_total,cxc_novenc_lp,cxc_novenc_cp,cxc_0a90dias,cxc_91a180dias,cxc_181a365dias,cxc_masde365dias,(coalesce(cxc_masde365dias,0)+coalesce(cxc_0a90dias,0)+coalesce(cxc_91a180dias,0)+coalesce(cxc_181a365dias,0)) AS cxc_vencidas
              FROM ((SELECT c.id as nro_contrato,c.ano,to_char(c.fecha,'DD-MM-YYYY') as fecha_emision,vc.estado,
                     coalesce(va.rut,vp.rut) as rut,coalesce(va.nombre,vp.nombre) as razon_social,
                     (SELECT to_char(max(fecha_venc),'DD-MM-YYYY') FROM finanzas.cobros where id_contrato=c.id) as fecha_venc,
                     '' as cta_ctble,
                     coalesce(c.arancel_efectivo,0)+coalesce(c.arancel_pagare_coleg,0)+coalesce(c.arancel_cheque,0)+coalesce(c.arancel_tarjeta_credito,0) as monto_inicial,
                     pc.id as nro_pagare,'CxC' AS tipo,
                     (SELECT sum(coalesce(monto-monto_abonado,monto)) AS monto_cxc FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND (NOT pagado OR abonado) AND fecha_venc > '2023-06-28'::date+'365 days'::interval) AS cxc_novenc_lp,
                     (SELECT sum(coalesce(monto-monto_abonado,monto)) AS monto_cxc FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND (NOT pagado OR abonado) AND fecha_venc BETWEEN '2023-06-28'::date+'1 days'::interval AND '2023-06-28'::date+'365 days'::interval) AS cxc_novenc_cp,(SELECT sum(coalesce(monto-monto_abonado,monto)) AS monto_cxc FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND (NOT pagado OR abonado) AND fecha_venc BETWEEN '2023-06-28'::date-'90 days'::interval AND '2023-06-28'::date) AS cxc_0a90dias,
                        (SELECT sum(coalesce(monto-monto_abonado,monto)) AS monto_cxc FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND (NOT pagado OR abonado) AND fecha_venc BETWEEN '2023-06-28'::date-'365 days'::interval AND '2023-06-28'::date-'181 days'::interval) AS cxc_91a180dias,
                        (SELECT sum(coalesce(monto-monto_abonado,monto)) AS monto_cxc FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND (NOT pagado OR abonado) AND ) AS cxc_181a365dias,(SELECT sum(coalesce(monto-monto_abonado,monto)) AS monto_cxc FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND (NOT pagado OR abonado) AND fecha_venc < '2023-06-28'::date-'365 days'::interval) AS cxc_masde365dias
              FROM finanzas.contratos AS c 
              LEFT JOIN vista_contratos AS vc USING (id)
              LEFT JOIN vista_alumnos AS va ON va.id=c.id_alumno 
              LEFT JOIN vista_pap     AS vp ON vp.id=c.id_pap 
              LEFT JOIN finanzas.pagares_colegiatura AS pc ON pc.id_contrato=c.id
              LEFT JOIN carreras        AS car ON car.id=c.id_carrera
              WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'j[uú][aá]n' OR  pap.rut ~* 'j[uú][aá]n' OR lower(a.nombres||' '||a.apellidos) ~* 'j[uú][aá]n' OR  a.rut ~* 'j[uú][aá]n' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'j[uú][aá]n' OR  av.rf_rut ~* 'j[uú][aá]n' OR  text(c.id) ~* 'j[uú][aá]n' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'fr[aá]nc[ií]sc[oó]' OR  pap.rut ~* 'fr[aá]nc[ií]sc[oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'fr[aá]nc[ií]sc[oó]' OR  a.rut ~* 'fr[aá]nc[ií]sc[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'fr[aá]nc[ií]sc[oó]' OR  av.rf_rut ~* 'fr[aá]nc[ií]sc[oó]' OR  text(c.id) ~* 'fr[aá]nc[ií]sc[oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]r[aá]y[aá]' OR  pap.rut ~* '[aá]r[aá]y[aá]' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]r[aá]y[aá]' OR  a.rut ~* '[aá]r[aá]y[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]r[aá]y[aá]' OR  av.rf_rut ~* '[aá]r[aá]y[aá]' OR  text(c.id) ~* '[aá]r[aá]y[aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'g[aá]ld[aá]m[eé]s' OR  pap.rut ~* 'g[aá]ld[aá]m[eé]s' OR lower(a.nombres||' '||a.apellidos) ~* 'g[aá]ld[aá]m[eé]s' OR  a.rut ~* 'g[aá]ld[aá]m[eé]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'g[aá]ld[aá]m[eé]s' OR  av.rf_rut ~* 'g[aá]ld[aá]m[eé]s' OR  text(c.id) ~* 'g[aá]ld[aá]m[eé]s' ) 
              ORDER BY c.fecha DESC ) UNION (SELECT c.id as nro_contrato,c.ano,to_char(c.fecha,'DD-MM-YYYY') as fecha_emision,vc.estado,
                         coalesce(va.rut,vp.rut) as rut,coalesce(va.nombre,vp.nombre) as razon_social,
                         (SELECT to_char(max(fecha_venc),'DD-MM-YYYY') FROM finanzas.cobros where id_contrato=c.id) as fecha_venc,
                         '' as cta_ctble,
                         coalesce(c.arancel_efectivo,0)+coalesce(c.arancel_pagare_coleg,0)+coalesce(c.arancel_cheque,0)+coalesce(c.arancel_tarjeta_credito,0) as monto_inicial,
                         pc.id as nro_pagare,'Deterioro' AS tipo,
                         (SELECT sum(coalesce(castigo_monto*-1,0)) AS monto_castigo FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND  fecha_venc > '2023-06-28'::date+'365 days'::interval) AS cxc_novenc_lp,
                         (SELECT sum(coalesce(castigo_monto*-1,0)) AS monto_castigo FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND  fecha_venc BETWEEN '2023-06-28'::date+'1 days'::interval AND '2023-06-28'::date+'365 days'::interval) AS cxc_novenc_cp,(SELECT sum(coalesce(castigo_monto*-1,0)) AS monto_castigo FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND  fecha_venc BETWEEN '2023-06-28'::date-'90 days'::interval AND '2023-06-28'::date) AS cxc_0a90dias,
                            (SELECT sum(coalesce(castigo_monto*-1,0)) AS monto_castigo FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND  fecha_venc BETWEEN '2023-06-28'::date-'365 days'::interval AND '2023-06-28'::date-'181 days'::interval) AS cxc_91a180dias,
                            (SELECT sum(coalesce(castigo_monto*-1,0)) AS monto_castigo FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND  ) AS cxc_181a365dias,(SELECT sum(coalesce(castigo_monto*-1,0)) AS monto_castigo FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND  fecha_venc < '2023-06-28'::date-'365 days'::interval) AS cxc_masde365dias
                  FROM finanzas.contratos AS c 
                  LEFT JOIN vista_contratos AS vc USING (id)
                  LEFT JOIN vista_alumnos AS va ON va.id=c.id_alumno 
                  LEFT JOIN vista_pap     AS vp ON vp.id=c.id_pap 
                  LEFT JOIN finanzas.pagares_colegiatura AS pc ON pc.id_contrato=c.id
                  LEFT JOIN carreras        AS car ON car.id=c.id_carrera
                  WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'j[uú][aá]n' OR  pap.rut ~* 'j[uú][aá]n' OR lower(a.nombres||' '||a.apellidos) ~* 'j[uú][aá]n' OR  a.rut ~* 'j[uú][aá]n' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'j[uú][aá]n' OR  av.rf_rut ~* 'j[uú][aá]n' OR  text(c.id) ~* 'j[uú][aá]n' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'fr[aá]nc[ií]sc[oó]' OR  pap.rut ~* 'fr[aá]nc[ií]sc[oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'fr[aá]nc[ií]sc[oó]' OR  a.rut ~* 'fr[aá]nc[ií]sc[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'fr[aá]nc[ií]sc[oó]' OR  av.rf_rut ~* 'fr[aá]nc[ií]sc[oó]' OR  text(c.id) ~* 'fr[aá]nc[ií]sc[oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]r[aá]y[aá]' OR  pap.rut ~* '[aá]r[aá]y[aá]' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]r[aá]y[aá]' OR  a.rut ~* '[aá]r[aá]y[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]r[aá]y[aá]' OR  av.rf_rut ~* '[aá]r[aá]y[aá]' OR  text(c.id) ~* '[aá]r[aá]y[aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'g[aá]ld[aá]m[eé]s' OR  pap.rut ~* 'g[aá]ld[aá]m[eé]s' OR lower(a.nombres||' '||a.apellidos) ~* 'g[aá]ld[aá]m[eé]s' OR  a.rut ~* 'g[aá]ld[aá]m[eé]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'g[aá]ld[aá]m[eé]s' OR  av.rf_rut ~* 'g[aá]ld[aá]m[eé]s' OR  text(c.id) ~* 'g[aá]ld[aá]m[eé]s' ) 
                  ORDER BY c.fecha DESC )) AS cxc) to stdout WITH CSV HEADER