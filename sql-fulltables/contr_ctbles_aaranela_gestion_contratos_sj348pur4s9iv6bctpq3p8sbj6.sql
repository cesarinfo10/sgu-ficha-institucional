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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'l[oó]z[aá]n[oó]' OR  pap.rut ~* 'l[oó]z[aá]n[oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'l[oó]z[aá]n[oó]' OR  a.rut ~* 'l[oó]z[aá]n[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'l[oó]z[aá]n[oó]' OR  av.rf_rut ~* 'l[oó]z[aá]n[oó]' OR  text(c.id) ~* 'l[oó]z[aá]n[oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'c[oó]st[aá]nz[oó]' OR  pap.rut ~* 'c[oó]st[aá]nz[oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'c[oó]st[aá]nz[oó]' OR  a.rut ~* 'c[oó]st[aá]nz[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'c[oó]st[aá]nz[oó]' OR  av.rf_rut ~* 'c[oó]st[aá]nz[oó]' OR  text(c.id) ~* 'c[oó]st[aá]nz[oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'm[aá][uú]r[ií]c[ií][oó]' OR  pap.rut ~* 'm[aá][uú]r[ií]c[ií][oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'm[aá][uú]r[ií]c[ií][oó]' OR  a.rut ~* 'm[aá][uú]r[ií]c[ií][oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'm[aá][uú]r[ií]c[ií][oó]' OR  av.rf_rut ~* 'm[aá][uú]r[ií]c[ií][oó]' OR  text(c.id) ~* 'm[aá][uú]r[ií]c[ií][oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]ndr[eé]s' OR  pap.rut ~* '[aá]ndr[eé]s' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]ndr[eé]s' OR  a.rut ~* '[aá]ndr[eé]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]ndr[eé]s' OR  av.rf_rut ~* '[aá]ndr[eé]s' OR  text(c.id) ~* '[aá]ndr[eé]s' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER