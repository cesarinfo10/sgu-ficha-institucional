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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'g[aá]rr[oó]t[eé]' OR  pap.rut ~* 'g[aá]rr[oó]t[eé]' OR lower(a.nombres||' '||a.apellidos) ~* 'g[aá]rr[oó]t[eé]' OR  a.rut ~* 'g[aá]rr[oó]t[eé]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'g[aá]rr[oó]t[eé]' OR  av.rf_rut ~* 'g[aá]rr[oó]t[eé]' OR  text(c.id) ~* 'g[aá]rr[oó]t[eé]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'j[ií]r[oó]n' OR  pap.rut ~* 'j[ií]r[oó]n' OR lower(a.nombres||' '||a.apellidos) ~* 'j[ií]r[oó]n' OR  a.rut ~* 'j[ií]r[oó]n' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'j[ií]r[oó]n' OR  av.rf_rut ~* 'j[ií]r[oó]n' OR  text(c.id) ~* 'j[ií]r[oó]n' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'p[oó]l[eé]tt[eé]' OR  pap.rut ~* 'p[oó]l[eé]tt[eé]' OR lower(a.nombres||' '||a.apellidos) ~* 'p[oó]l[eé]tt[eé]' OR  a.rut ~* 'p[oó]l[eé]tt[eé]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'p[oó]l[eé]tt[eé]' OR  av.rf_rut ~* 'p[oó]l[eé]tt[eé]' OR  text(c.id) ~* 'p[oó]l[eé]tt[eé]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'fr[aá]nch[eé]sc[aá]' OR  pap.rut ~* 'fr[aá]nch[eé]sc[aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'fr[aá]nch[eé]sc[aá]' OR  a.rut ~* 'fr[aá]nch[eé]sc[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'fr[aá]nch[eé]sc[aá]' OR  av.rf_rut ~* 'fr[aá]nch[eé]sc[aá]' OR  text(c.id) ~* 'fr[aá]nch[eé]sc[aá]' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER