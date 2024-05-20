COPY (SELECT nro_contrato,ano,fecha_emision,estado,rut,razon_social,fecha_venc,cta_ctble,monto_inicial,nro_pagare,tipo,
                     (coalesce(cxc_novenc_lp,0)+coalesce(cxc_novenc_cp,0)+coalesce(cxc_masde365dias,0)+coalesce(cxc_0a30dias,0)+coalesce(cxc_31a90dias,0)+coalesce(cxc_91a365dias,0)) AS cxc_total,cxc_novenc_lp,cxc_novenc_cp,cxc_0a30dias,cxc_31a90dias,cxc_91a365dias,cxc_masde365dias,(coalesce(cxc_masde365dias,0)+coalesce(cxc_0a30dias,0)+coalesce(cxc_31a90dias,0)+coalesce(cxc_91a365dias,0)) AS cxc_vencidas
              FROM ((SELECT c.id as nro_contrato,c.ano,to_char(c.fecha,'DD-MM-YYYY') as fecha_emision,vc.estado,
                     coalesce(va.rut,vp.rut) as rut,coalesce(va.nombre,vp.nombre) as razon_social,
                     (SELECT to_char(max(fecha_venc),'DD-MM-YYYY') FROM finanzas.cobros where id_contrato=c.id) as fecha_venc,
                     '' as cta_ctble,
                     coalesce(c.arancel_efectivo,0)+coalesce(c.arancel_pagare_coleg,0)+coalesce(c.arancel_cheque,0)+coalesce(c.arancel_tarjeta_credito,0) as monto_inicial,
                     pc.id as nro_pagare,'CxC' AS tipo,
                     (SELECT sum(coalesce(monto-monto_abonado,monto)) AS monto_cxc FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND (NOT pagado OR abonado) AND fecha_venc > '2023-08-10'::date+'365 days'::interval) AS cxc_novenc_lp,
                     (SELECT sum(coalesce(monto-monto_abonado,monto)) AS monto_cxc FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND (NOT pagado OR abonado) AND fecha_venc BETWEEN '2023-08-10'::date+'1 days'::interval AND '2023-08-10'::date+'365 days'::interval) AS cxc_novenc_cp,(SELECT sum(coalesce(monto-monto_abonado,monto)) AS monto_cxc FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND (NOT pagado OR abonado) AND fecha_venc BETWEEN '2023-08-10'::date-'30 days'::interval AND '2023-08-10'::date) AS cxc_0a30dias,
                        (SELECT sum(coalesce(monto-monto_abonado,monto)) AS monto_cxc FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND (NOT pagado OR abonado) AND fecha_venc BETWEEN '2023-08-10'::date-'90 days'::interval AND '2023-08-10'::date-'31 days'::interval) AS cxc_31a90dias,
                        (SELECT sum(coalesce(monto-monto_abonado,monto)) AS monto_cxc FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND (NOT pagado OR abonado) AND fecha_venc BETWEEN '2023-08-10'::date-'365 days'::interval AND '2023-08-10'::date-'91 days'::interval) AS cxc_91a365dias,(SELECT sum(coalesce(monto-monto_abonado,monto)) AS monto_cxc FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND (NOT pagado OR abonado) AND fecha_venc < '2023-08-10'::date-'365 days'::interval) AS cxc_masde365dias
              FROM finanzas.contratos AS c 
              LEFT JOIN vista_contratos AS vc USING (id)
              LEFT JOIN vista_alumnos AS va ON va.id=c.id_alumno 
              LEFT JOIN vista_pap     AS vp ON vp.id=c.id_pap 
              LEFT JOIN finanzas.pagares_colegiatura AS pc ON pc.id_contrato=c.id
              LEFT JOIN carreras        AS car ON car.id=c.id_carrera
              WHERE (lower(pap.nombres||' '||pap.apellidos) ~* '' OR  pap.rut ~* '' OR lower(a.nombres||' '||a.apellidos) ~* '' OR  a.rut ~* '' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '' OR  av.rf_rut ~* '' OR  text(c.id) ~* '' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'm[aá]r[ií][aá]n[eé]l[aá]' OR  pap.rut ~* 'm[aá]r[ií][aá]n[eé]l[aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'm[aá]r[ií][aá]n[eé]l[aá]' OR  a.rut ~* 'm[aá]r[ií][aá]n[eé]l[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'm[aá]r[ií][aá]n[eé]l[aá]' OR  av.rf_rut ~* 'm[aá]r[ií][aá]n[eé]l[aá]' OR  text(c.id) ~* 'm[aá]r[ií][aá]n[eé]l[aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[eé]l[ií]z[aá]b[eé]th' OR  pap.rut ~* '[eé]l[ií]z[aá]b[eé]th' OR lower(a.nombres||' '||a.apellidos) ~* '[eé]l[ií]z[aá]b[eé]th' OR  a.rut ~* '[eé]l[ií]z[aá]b[eé]th' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[eé]l[ií]z[aá]b[eé]th' OR  av.rf_rut ~* '[eé]l[ií]z[aá]b[eé]th' OR  text(c.id) ~* '[eé]l[ií]z[aá]b[eé]th' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]r[ií][aá]s' OR  pap.rut ~* '[aá]r[ií][aá]s' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]r[ií][aá]s' OR  a.rut ~* '[aá]r[ií][aá]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]r[ií][aá]s' OR  av.rf_rut ~* '[aá]r[ií][aá]s' OR  text(c.id) ~* '[aá]r[ií][aá]s' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'm[aá]nd[oó]l[ií]n[ií]' OR  pap.rut ~* 'm[aá]nd[oó]l[ií]n[ií]' OR lower(a.nombres||' '||a.apellidos) ~* 'm[aá]nd[oó]l[ií]n[ií]' OR  a.rut ~* 'm[aá]nd[oó]l[ií]n[ií]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'm[aá]nd[oó]l[ií]n[ií]' OR  av.rf_rut ~* 'm[aá]nd[oó]l[ií]n[ií]' OR  text(c.id) ~* 'm[aá]nd[oó]l[ií]n[ií]' ) 
              ORDER BY c.fecha DESC ) UNION (SELECT c.id as nro_contrato,c.ano,to_char(c.fecha,'DD-MM-YYYY') as fecha_emision,vc.estado,
                         coalesce(va.rut,vp.rut) as rut,coalesce(va.nombre,vp.nombre) as razon_social,
                         (SELECT to_char(max(fecha_venc),'DD-MM-YYYY') FROM finanzas.cobros where id_contrato=c.id) as fecha_venc,
                         '' as cta_ctble,
                         coalesce(c.arancel_efectivo,0)+coalesce(c.arancel_pagare_coleg,0)+coalesce(c.arancel_cheque,0)+coalesce(c.arancel_tarjeta_credito,0) as monto_inicial,
                         pc.id as nro_pagare,'Deterioro' AS tipo,
                         (SELECT sum(coalesce(castigo_monto*-1,0)) AS monto_castigo FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND  fecha_venc > '2023-08-10'::date+'365 days'::interval) AS cxc_novenc_lp,
                         (SELECT sum(coalesce(castigo_monto*-1,0)) AS monto_castigo FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND  fecha_venc BETWEEN '2023-08-10'::date+'1 days'::interval AND '2023-08-10'::date+'365 days'::interval) AS cxc_novenc_cp,(SELECT sum(coalesce(castigo_monto*-1,0)) AS monto_castigo FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND  fecha_venc BETWEEN '2023-08-10'::date-'30 days'::interval AND '2023-08-10'::date) AS cxc_0a30dias,
                            (SELECT sum(coalesce(castigo_monto*-1,0)) AS monto_castigo FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND  fecha_venc BETWEEN '2023-08-10'::date-'90 days'::interval AND '2023-08-10'::date-'31 days'::interval) AS cxc_31a90dias,
                            (SELECT sum(coalesce(castigo_monto*-1,0)) AS monto_castigo FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND  fecha_venc BETWEEN '2023-08-10'::date-'365 days'::interval AND '2023-08-10'::date-'91 days'::interval) AS cxc_91a365dias,(SELECT sum(coalesce(castigo_monto*-1,0)) AS monto_castigo FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa NOT IN (1,10001) AND  fecha_venc < '2023-08-10'::date-'365 days'::interval) AS cxc_masde365dias
                  FROM finanzas.contratos AS c 
                  LEFT JOIN vista_contratos AS vc USING (id)
                  LEFT JOIN vista_alumnos AS va ON va.id=c.id_alumno 
                  LEFT JOIN vista_pap     AS vp ON vp.id=c.id_pap 
                  LEFT JOIN finanzas.pagares_colegiatura AS pc ON pc.id_contrato=c.id
                  LEFT JOIN carreras        AS car ON car.id=c.id_carrera
                  WHERE (lower(pap.nombres||' '||pap.apellidos) ~* '' OR  pap.rut ~* '' OR lower(a.nombres||' '||a.apellidos) ~* '' OR  a.rut ~* '' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '' OR  av.rf_rut ~* '' OR  text(c.id) ~* '' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'm[aá]r[ií][aá]n[eé]l[aá]' OR  pap.rut ~* 'm[aá]r[ií][aá]n[eé]l[aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'm[aá]r[ií][aá]n[eé]l[aá]' OR  a.rut ~* 'm[aá]r[ií][aá]n[eé]l[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'm[aá]r[ií][aá]n[eé]l[aá]' OR  av.rf_rut ~* 'm[aá]r[ií][aá]n[eé]l[aá]' OR  text(c.id) ~* 'm[aá]r[ií][aá]n[eé]l[aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[eé]l[ií]z[aá]b[eé]th' OR  pap.rut ~* '[eé]l[ií]z[aá]b[eé]th' OR lower(a.nombres||' '||a.apellidos) ~* '[eé]l[ií]z[aá]b[eé]th' OR  a.rut ~* '[eé]l[ií]z[aá]b[eé]th' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[eé]l[ií]z[aá]b[eé]th' OR  av.rf_rut ~* '[eé]l[ií]z[aá]b[eé]th' OR  text(c.id) ~* '[eé]l[ií]z[aá]b[eé]th' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]r[ií][aá]s' OR  pap.rut ~* '[aá]r[ií][aá]s' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]r[ií][aá]s' OR  a.rut ~* '[aá]r[ií][aá]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]r[ií][aá]s' OR  av.rf_rut ~* '[aá]r[ií][aá]s' OR  text(c.id) ~* '[aá]r[ií][aá]s' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'm[aá]nd[oó]l[ií]n[ií]' OR  pap.rut ~* 'm[aá]nd[oó]l[ií]n[ií]' OR lower(a.nombres||' '||a.apellidos) ~* 'm[aá]nd[oó]l[ií]n[ií]' OR  a.rut ~* 'm[aá]nd[oó]l[ií]n[ií]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'm[aá]nd[oó]l[ií]n[ií]' OR  av.rf_rut ~* 'm[aá]nd[oó]l[ií]n[ií]' OR  text(c.id) ~* 'm[aá]nd[oó]l[ií]n[ií]' ) 
                  ORDER BY c.fecha DESC )) AS cxc) to stdout WITH CSV HEADER