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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'r[aá]m[ií]r[eé]z' OR  pap.rut ~* 'r[aá]m[ií]r[eé]z' OR lower(a.nombres||' '||a.apellidos) ~* 'r[aá]m[ií]r[eé]z' OR  a.rut ~* 'r[aá]m[ií]r[eé]z' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'r[aá]m[ií]r[eé]z' OR  av.rf_rut ~* 'r[aá]m[ií]r[eé]z' OR  text(c.id) ~* 'r[aá]m[ií]r[eé]z' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'm[aá]rt[ií]n[eé]z' OR  pap.rut ~* 'm[aá]rt[ií]n[eé]z' OR lower(a.nombres||' '||a.apellidos) ~* 'm[aá]rt[ií]n[eé]z' OR  a.rut ~* 'm[aá]rt[ií]n[eé]z' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'm[aá]rt[ií]n[eé]z' OR  av.rf_rut ~* 'm[aá]rt[ií]n[eé]z' OR  text(c.id) ~* 'm[aá]rt[ií]n[eé]z' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '' OR  pap.rut ~* '' OR lower(a.nombres||' '||a.apellidos) ~* '' OR  a.rut ~* '' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '' OR  av.rf_rut ~* '' OR  text(c.id) ~* '' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER