COPY (SELECT to_char(c.fecha,'DD-MM-YYYY') as fecha_emision,
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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'd[eé]n[ií]ss[eé]' OR  pap.rut ~* 'd[eé]n[ií]ss[eé]' OR lower(a.nombres||' '||a.apellidos) ~* 'd[eé]n[ií]ss[eé]' OR  a.rut ~* 'd[eé]n[ií]ss[eé]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'd[eé]n[ií]ss[eé]' OR  av.rf_rut ~* 'd[eé]n[ií]ss[eé]' OR  text(c.id) ~* 'd[eé]n[ií]ss[eé]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[ií]v[oó]n[eé]' OR  pap.rut ~* '[ií]v[oó]n[eé]' OR lower(a.nombres||' '||a.apellidos) ~* '[ií]v[oó]n[eé]' OR  a.rut ~* '[ií]v[oó]n[eé]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[ií]v[oó]n[eé]' OR  av.rf_rut ~* '[ií]v[oó]n[eé]' OR  text(c.id) ~* '[ií]v[oó]n[eé]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'h[eé]rm[oó]s[ií]ll[aá]' OR  pap.rut ~* 'h[eé]rm[oó]s[ií]ll[aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'h[eé]rm[oó]s[ií]ll[aá]' OR  a.rut ~* 'h[eé]rm[oó]s[ií]ll[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'h[eé]rm[oó]s[ií]ll[aá]' OR  av.rf_rut ~* 'h[eé]rm[oó]s[ií]ll[aá]' OR  text(c.id) ~* 'h[eé]rm[oó]s[ií]ll[aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]lm[oó]n[aá]c[ií]d' OR  pap.rut ~* '[aá]lm[oó]n[aá]c[ií]d' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]lm[oó]n[aá]c[ií]d' OR  a.rut ~* '[aá]lm[oó]n[aá]c[ií]d' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]lm[oó]n[aá]c[ií]d' OR  av.rf_rut ~* '[aá]lm[oó]n[aá]c[ií]d' OR  text(c.id) ~* '[aá]lm[oó]n[aá]c[ií]d' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER