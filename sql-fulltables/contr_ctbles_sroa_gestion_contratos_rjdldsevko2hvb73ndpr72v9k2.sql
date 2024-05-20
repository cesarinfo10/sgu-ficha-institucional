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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'm[ií]ch[aá][eé]l' OR  pap.rut ~* 'm[ií]ch[aá][eé]l' OR lower(a.nombres||' '||a.apellidos) ~* 'm[ií]ch[aá][eé]l' OR  a.rut ~* 'm[ií]ch[aá][eé]l' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'm[ií]ch[aá][eé]l' OR  av.rf_rut ~* 'm[ií]ch[aá][eé]l' OR  text(c.id) ~* 'm[ií]ch[aá][eé]l' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'm[oó]r[aá]l[eé]s' OR  pap.rut ~* 'm[oó]r[aá]l[eé]s' OR lower(a.nombres||' '||a.apellidos) ~* 'm[oó]r[aá]l[eé]s' OR  a.rut ~* 'm[oó]r[aá]l[eé]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'm[oó]r[aá]l[eé]s' OR  av.rf_rut ~* 'm[oó]r[aá]l[eé]s' OR  text(c.id) ~* 'm[oó]r[aá]l[eé]s' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'd[oó]m[ií]n[ií]q[uú][eé]' OR  pap.rut ~* 'd[oó]m[ií]n[ií]q[uú][eé]' OR lower(a.nombres||' '||a.apellidos) ~* 'd[oó]m[ií]n[ií]q[uú][eé]' OR  a.rut ~* 'd[oó]m[ií]n[ií]q[uú][eé]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'd[oó]m[ií]n[ií]q[uú][eé]' OR  av.rf_rut ~* 'd[oó]m[ií]n[ií]q[uú][eé]' OR  text(c.id) ~* 'd[oó]m[ií]n[ií]q[uú][eé]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]l[eé]j[aá]ndr[aá]' OR  pap.rut ~* '[aá]l[eé]j[aá]ndr[aá]' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]l[eé]j[aá]ndr[aá]' OR  a.rut ~* '[aá]l[eé]j[aá]ndr[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]l[eé]j[aá]ndr[aá]' OR  av.rf_rut ~* '[aá]l[eé]j[aá]ndr[aá]' OR  text(c.id) ~* '[aá]l[eé]j[aá]ndr[aá]' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER