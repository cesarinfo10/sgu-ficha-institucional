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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'c[aá]m[ií]l[aá]' OR  pap.rut ~* 'c[aá]m[ií]l[aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'c[aá]m[ií]l[aá]' OR  a.rut ~* 'c[aá]m[ií]l[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'c[aá]m[ií]l[aá]' OR  av.rf_rut ~* 'c[aá]m[ií]l[aá]' OR  text(c.id) ~* 'c[aá]m[ií]l[aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'b[eé]l[eé]n' OR  pap.rut ~* 'b[eé]l[eé]n' OR lower(a.nombres||' '||a.apellidos) ~* 'b[eé]l[eé]n' OR  a.rut ~* 'b[eé]l[eé]n' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'b[eé]l[eé]n' OR  av.rf_rut ~* 'b[eé]l[eé]n' OR  text(c.id) ~* 'b[eé]l[eé]n' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'n[aá]v[aá]rr[eé]t[eé]' OR  pap.rut ~* 'n[aá]v[aá]rr[eé]t[eé]' OR lower(a.nombres||' '||a.apellidos) ~* 'n[aá]v[aá]rr[eé]t[eé]' OR  a.rut ~* 'n[aá]v[aá]rr[eé]t[eé]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'n[aá]v[aá]rr[eé]t[eé]' OR  av.rf_rut ~* 'n[aá]v[aá]rr[eé]t[eé]' OR  text(c.id) ~* 'n[aá]v[aá]rr[eé]t[eé]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'p[ií]nt[oó]' OR  pap.rut ~* 'p[ií]nt[oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'p[ií]nt[oó]' OR  a.rut ~* 'p[ií]nt[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'p[ií]nt[oó]' OR  av.rf_rut ~* 'p[ií]nt[oó]' OR  text(c.id) ~* 'p[ií]nt[oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '' OR  pap.rut ~* '' OR lower(a.nombres||' '||a.apellidos) ~* '' OR  a.rut ~* '' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '' OR  av.rf_rut ~* '' OR  text(c.id) ~* '' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER