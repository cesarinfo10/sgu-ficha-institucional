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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'l[oó]rn[aá]' OR  pap.rut ~* 'l[oó]rn[aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'l[oó]rn[aá]' OR  a.rut ~* 'l[oó]rn[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'l[oó]rn[aá]' OR  av.rf_rut ~* 'l[oó]rn[aá]' OR  text(c.id) ~* 'l[oó]rn[aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[eé]l[ií]z[aá]b[eé]th' OR  pap.rut ~* '[eé]l[ií]z[aá]b[eé]th' OR lower(a.nombres||' '||a.apellidos) ~* '[eé]l[ií]z[aá]b[eé]th' OR  a.rut ~* '[eé]l[ií]z[aá]b[eé]th' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[eé]l[ií]z[aá]b[eé]th' OR  av.rf_rut ~* '[eé]l[ií]z[aá]b[eé]th' OR  text(c.id) ~* '[eé]l[ií]z[aá]b[eé]th' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]l[aá]m[oó]s' OR  pap.rut ~* '[aá]l[aá]m[oó]s' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]l[aá]m[oó]s' OR  a.rut ~* '[aá]l[aá]m[oó]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]l[aá]m[oó]s' OR  av.rf_rut ~* '[aá]l[aá]m[oó]s' OR  text(c.id) ~* '[aá]l[aá]m[oó]s' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'r[oó]dr[ií]g[uú][eé]z' OR  pap.rut ~* 'r[oó]dr[ií]g[uú][eé]z' OR lower(a.nombres||' '||a.apellidos) ~* 'r[oó]dr[ií]g[uú][eé]z' OR  a.rut ~* 'r[oó]dr[ií]g[uú][eé]z' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'r[oó]dr[ií]g[uú][eé]z' OR  av.rf_rut ~* 'r[oó]dr[ií]g[uú][eé]z' OR  text(c.id) ~* 'r[oó]dr[ií]g[uú][eé]z' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER