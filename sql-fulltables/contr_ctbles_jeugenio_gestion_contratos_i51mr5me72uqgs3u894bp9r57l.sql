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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'p[ií]n[eé]d[aá]' OR  pap.rut ~* 'p[ií]n[eé]d[aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'p[ií]n[eé]d[aá]' OR  a.rut ~* 'p[ií]n[eé]d[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'p[ií]n[eé]d[aá]' OR  av.rf_rut ~* 'p[ií]n[eé]d[aá]' OR  text(c.id) ~* 'p[ií]n[eé]d[aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'j[eé]ldr[eé]s' OR  pap.rut ~* 'j[eé]ldr[eé]s' OR lower(a.nombres||' '||a.apellidos) ~* 'j[eé]ldr[eé]s' OR  a.rut ~* 'j[eé]ldr[eé]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'j[eé]ldr[eé]s' OR  av.rf_rut ~* 'j[eé]ldr[eé]s' OR  text(c.id) ~* 'j[eé]ldr[eé]s' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'y[eé]nn[ií]f[eé]r' OR  pap.rut ~* 'y[eé]nn[ií]f[eé]r' OR lower(a.nombres||' '||a.apellidos) ~* 'y[eé]nn[ií]f[eé]r' OR  a.rut ~* 'y[eé]nn[ií]f[eé]r' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'y[eé]nn[ií]f[eé]r' OR  av.rf_rut ~* 'y[eé]nn[ií]f[eé]r' OR  text(c.id) ~* 'y[eé]nn[ií]f[eé]r' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'r[oó]c[ií][oó]' OR  pap.rut ~* 'r[oó]c[ií][oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'r[oó]c[ií][oó]' OR  a.rut ~* 'r[oó]c[ií][oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'r[oó]c[ií][oó]' OR  av.rf_rut ~* 'r[oó]c[ií][oó]' OR  text(c.id) ~* 'r[oó]c[ií][oó]' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER