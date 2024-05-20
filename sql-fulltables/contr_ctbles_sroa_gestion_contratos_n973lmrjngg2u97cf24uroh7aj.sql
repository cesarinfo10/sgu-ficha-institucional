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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* '[ií]gn[aá]c[ií][oó]' OR  pap.rut ~* '[ií]gn[aá]c[ií][oó]' OR lower(a.nombres||' '||a.apellidos) ~* '[ií]gn[aá]c[ií][oó]' OR  a.rut ~* '[ií]gn[aá]c[ií][oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[ií]gn[aá]c[ií][oó]' OR  av.rf_rut ~* '[ií]gn[aá]c[ií][oó]' OR  text(c.id) ~* '[ií]gn[aá]c[ií][oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'g[aá]r[aá]y' OR  pap.rut ~* 'g[aá]r[aá]y' OR lower(a.nombres||' '||a.apellidos) ~* 'g[aá]r[aá]y' OR  a.rut ~* 'g[aá]r[aá]y' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'g[aá]r[aá]y' OR  av.rf_rut ~* 'g[aá]r[aá]y' OR  text(c.id) ~* 'g[aá]r[aá]y' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER