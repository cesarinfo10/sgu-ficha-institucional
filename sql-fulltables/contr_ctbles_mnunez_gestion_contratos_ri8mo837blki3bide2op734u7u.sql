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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'c[aá]str[oó]' OR  pap.rut ~* 'c[aá]str[oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'c[aá]str[oó]' OR  a.rut ~* 'c[aá]str[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'c[aá]str[oó]' OR  av.rf_rut ~* 'c[aá]str[oó]' OR  text(c.id) ~* 'c[aá]str[oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'b[eé]n[aá]v[ií]d[eé]s' OR  pap.rut ~* 'b[eé]n[aá]v[ií]d[eé]s' OR lower(a.nombres||' '||a.apellidos) ~* 'b[eé]n[aá]v[ií]d[eé]s' OR  a.rut ~* 'b[eé]n[aá]v[ií]d[eé]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'b[eé]n[aá]v[ií]d[eé]s' OR  av.rf_rut ~* 'b[eé]n[aá]v[ií]d[eé]s' OR  text(c.id) ~* 'b[eé]n[aá]v[ií]d[eé]s' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[eé]l[ií]z[aá]b[eé]th' OR  pap.rut ~* '[eé]l[ií]z[aá]b[eé]th' OR lower(a.nombres||' '||a.apellidos) ~* '[eé]l[ií]z[aá]b[eé]th' OR  a.rut ~* '[eé]l[ií]z[aá]b[eé]th' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[eé]l[ií]z[aá]b[eé]th' OR  av.rf_rut ~* '[eé]l[ií]z[aá]b[eé]th' OR  text(c.id) ~* '[eé]l[ií]z[aá]b[eé]th' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'n[aá]t[aá]l[ií][aá]' OR  pap.rut ~* 'n[aá]t[aá]l[ií][aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'n[aá]t[aá]l[ií][aá]' OR  a.rut ~* 'n[aá]t[aá]l[ií][aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'n[aá]t[aá]l[ií][aá]' OR  av.rf_rut ~* 'n[aá]t[aá]l[ií][aá]' OR  text(c.id) ~* 'n[aá]t[aá]l[ií][aá]' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER