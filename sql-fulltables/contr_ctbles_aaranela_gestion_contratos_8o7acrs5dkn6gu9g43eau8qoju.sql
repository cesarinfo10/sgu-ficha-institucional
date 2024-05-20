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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'r[oó]b[eé]rt[oó]' OR  pap.rut ~* 'r[oó]b[eé]rt[oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'r[oó]b[eé]rt[oó]' OR  a.rut ~* 'r[oó]b[eé]rt[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'r[oó]b[eé]rt[oó]' OR  av.rf_rut ~* 'r[oó]b[eé]rt[oó]' OR  text(c.id) ~* 'r[oó]b[eé]rt[oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[eé]m[ií]l[ií][oó]' OR  pap.rut ~* '[eé]m[ií]l[ií][oó]' OR lower(a.nombres||' '||a.apellidos) ~* '[eé]m[ií]l[ií][oó]' OR  a.rut ~* '[eé]m[ií]l[ií][oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[eé]m[ií]l[ií][oó]' OR  av.rf_rut ~* '[eé]m[ií]l[ií][oó]' OR  text(c.id) ~* '[eé]m[ií]l[ií][oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 's[oó]t[oó]' OR  pap.rut ~* 's[oó]t[oó]' OR lower(a.nombres||' '||a.apellidos) ~* 's[oó]t[oó]' OR  a.rut ~* 's[oó]t[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 's[oó]t[oó]' OR  av.rf_rut ~* 's[oó]t[oó]' OR  text(c.id) ~* 's[oó]t[oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'b[uú]st[aá]m[aá]nt[eé]' OR  pap.rut ~* 'b[uú]st[aá]m[aá]nt[eé]' OR lower(a.nombres||' '||a.apellidos) ~* 'b[uú]st[aá]m[aá]nt[eé]' OR  a.rut ~* 'b[uú]st[aá]m[aá]nt[eé]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'b[uú]st[aá]m[aá]nt[eé]' OR  av.rf_rut ~* 'b[uú]st[aá]m[aá]nt[eé]' OR  text(c.id) ~* 'b[uú]st[aá]m[aá]nt[eé]' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER