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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'g[aá]rc[ií][aá]' OR  pap.rut ~* 'g[aá]rc[ií][aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'g[aá]rc[ií][aá]' OR  a.rut ~* 'g[aá]rc[ií][aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'g[aá]rc[ií][aá]' OR  av.rf_rut ~* 'g[aá]rc[ií][aá]' OR  text(c.id) ~* 'g[aá]rc[ií][aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'r[eé]t[aá]m[aá]l' OR  pap.rut ~* 'r[eé]t[aá]m[aá]l' OR lower(a.nombres||' '||a.apellidos) ~* 'r[eé]t[aá]m[aá]l' OR  a.rut ~* 'r[eé]t[aá]m[aá]l' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'r[eé]t[aá]m[aá]l' OR  av.rf_rut ~* 'r[eé]t[aá]m[aá]l' OR  text(c.id) ~* 'r[eé]t[aá]m[aá]l' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'p[aá][oó]l[aá]' OR  pap.rut ~* 'p[aá][oó]l[aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'p[aá][oó]l[aá]' OR  a.rut ~* 'p[aá][oó]l[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'p[aá][oó]l[aá]' OR  av.rf_rut ~* 'p[aá][oó]l[aá]' OR  text(c.id) ~* 'p[aá][oó]l[aá]' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER