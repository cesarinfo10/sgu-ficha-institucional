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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'l[ií]z' OR  pap.rut ~* 'l[ií]z' OR lower(a.nombres||' '||a.apellidos) ~* 'l[ií]z' OR  a.rut ~* 'l[ií]z' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'l[ií]z' OR  av.rf_rut ~* 'l[ií]z' OR  text(c.id) ~* 'l[ií]z' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]lp[aá]c[aá]' OR  pap.rut ~* '[aá]lp[aá]c[aá]' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]lp[aá]c[aá]' OR  a.rut ~* '[aá]lp[aá]c[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]lp[aá]c[aá]' OR  av.rf_rut ~* '[aá]lp[aá]c[aá]' OR  text(c.id) ~* '[aá]lp[aá]c[aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'm[oó]sc[oó]l' OR  pap.rut ~* 'm[oó]sc[oó]l' OR lower(a.nombres||' '||a.apellidos) ~* 'm[oó]sc[oó]l' OR  a.rut ~* 'm[oó]sc[oó]l' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'm[oó]sc[oó]l' OR  av.rf_rut ~* 'm[oó]sc[oó]l' OR  text(c.id) ~* 'm[oó]sc[oó]l' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER