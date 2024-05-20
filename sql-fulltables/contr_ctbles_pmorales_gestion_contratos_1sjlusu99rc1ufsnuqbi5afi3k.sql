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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'c[aá]m[ií]l[oó]' OR  pap.rut ~* 'c[aá]m[ií]l[oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'c[aá]m[ií]l[oó]' OR  a.rut ~* 'c[aá]m[ií]l[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'c[aá]m[ií]l[oó]' OR  av.rf_rut ~* 'c[aá]m[ií]l[oó]' OR  text(c.id) ~* 'c[aá]m[ií]l[oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[ií]b[aá]ñ[eé]z' OR  pap.rut ~* '[ií]b[aá]ñ[eé]z' OR lower(a.nombres||' '||a.apellidos) ~* '[ií]b[aá]ñ[eé]z' OR  a.rut ~* '[ií]b[aá]ñ[eé]z' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[ií]b[aá]ñ[eé]z' OR  av.rf_rut ~* '[ií]b[aá]ñ[eé]z' OR  text(c.id) ~* '[ií]b[aá]ñ[eé]z' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER