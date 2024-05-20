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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'f[ií]ll[aá]' OR  pap.rut ~* 'f[ií]ll[aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'f[ií]ll[aá]' OR  a.rut ~* 'f[ií]ll[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'f[ií]ll[aá]' OR  av.rf_rut ~* 'f[ií]ll[aá]' OR  text(c.id) ~* 'f[ií]ll[aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'v[eé]r[aá]' OR  pap.rut ~* 'v[eé]r[aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'v[eé]r[aá]' OR  a.rut ~* 'v[eé]r[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'v[eé]r[aá]' OR  av.rf_rut ~* 'v[eé]r[aá]' OR  text(c.id) ~* 'v[eé]r[aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'j[oó]n[aá]th[aá]n' OR  pap.rut ~* 'j[oó]n[aá]th[aá]n' OR lower(a.nombres||' '||a.apellidos) ~* 'j[oó]n[aá]th[aá]n' OR  a.rut ~* 'j[oó]n[aá]th[aá]n' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'j[oó]n[aá]th[aá]n' OR  av.rf_rut ~* 'j[oó]n[aá]th[aá]n' OR  text(c.id) ~* 'j[oó]n[aá]th[aá]n' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'h[aá]r[ií][eé]t' OR  pap.rut ~* 'h[aá]r[ií][eé]t' OR lower(a.nombres||' '||a.apellidos) ~* 'h[aá]r[ií][eé]t' OR  a.rut ~* 'h[aá]r[ií][eé]t' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'h[aá]r[ií][eé]t' OR  av.rf_rut ~* 'h[aá]r[ií][eé]t' OR  text(c.id) ~* 'h[aá]r[ií][eé]t' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER