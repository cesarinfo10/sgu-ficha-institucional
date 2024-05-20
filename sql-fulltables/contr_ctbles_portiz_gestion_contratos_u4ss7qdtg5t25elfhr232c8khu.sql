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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'n[ií]c[oó]l[aá]s' OR  pap.rut ~* 'n[ií]c[oó]l[aá]s' OR lower(a.nombres||' '||a.apellidos) ~* 'n[ií]c[oó]l[aá]s' OR  a.rut ~* 'n[ií]c[oó]l[aá]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'n[ií]c[oó]l[aá]s' OR  av.rf_rut ~* 'n[ií]c[oó]l[aá]s' OR  text(c.id) ~* 'n[ií]c[oó]l[aá]s' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'f[eé]l[ií]p[eé]' OR  pap.rut ~* 'f[eé]l[ií]p[eé]' OR lower(a.nombres||' '||a.apellidos) ~* 'f[eé]l[ií]p[eé]' OR  a.rut ~* 'f[eé]l[ií]p[eé]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'f[eé]l[ií]p[eé]' OR  av.rf_rut ~* 'f[eé]l[ií]p[eé]' OR  text(c.id) ~* 'f[eé]l[ií]p[eé]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[oó]p[oó]rt[oó]' OR  pap.rut ~* '[oó]p[oó]rt[oó]' OR lower(a.nombres||' '||a.apellidos) ~* '[oó]p[oó]rt[oó]' OR  a.rut ~* '[oó]p[oó]rt[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[oó]p[oó]rt[oó]' OR  av.rf_rut ~* '[oó]p[oó]rt[oó]' OR  text(c.id) ~* '[oó]p[oó]rt[oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'p[aá]ch[eé]c[oó]' OR  pap.rut ~* 'p[aá]ch[eé]c[oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'p[aá]ch[eé]c[oó]' OR  a.rut ~* 'p[aá]ch[eé]c[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'p[aá]ch[eé]c[oó]' OR  av.rf_rut ~* 'p[aá]ch[eé]c[oó]' OR  text(c.id) ~* 'p[aá]ch[eé]c[oó]' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER