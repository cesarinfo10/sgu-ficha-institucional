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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'd[oó]m[ií]ng[oó]' OR  pap.rut ~* 'd[oó]m[ií]ng[oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'd[oó]m[ií]ng[oó]' OR  a.rut ~* 'd[oó]m[ií]ng[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'd[oó]m[ií]ng[oó]' OR  av.rf_rut ~* 'd[oó]m[ií]ng[oó]' OR  text(c.id) ~* 'd[oó]m[ií]ng[oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[oó]v[aá]ll[eé]' OR  pap.rut ~* '[oó]v[aá]ll[eé]' OR lower(a.nombres||' '||a.apellidos) ~* '[oó]v[aá]ll[eé]' OR  a.rut ~* '[oó]v[aá]ll[eé]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[oó]v[aá]ll[eé]' OR  av.rf_rut ~* '[oó]v[aá]ll[eé]' OR  text(c.id) ~* '[oó]v[aá]ll[eé]' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER