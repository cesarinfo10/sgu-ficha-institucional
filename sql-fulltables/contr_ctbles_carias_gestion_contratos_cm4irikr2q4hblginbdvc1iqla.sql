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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'd[aá]' OR  pap.rut ~* 'd[aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'd[aá]' OR  a.rut ~* 'd[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'd[aá]' OR  av.rf_rut ~* 'd[aá]' OR  text(c.id) ~* 'd[aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'c[oó]nc[eé][ií]c[aá][oó]' OR  pap.rut ~* 'c[oó]nc[eé][ií]c[aá][oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'c[oó]nc[eé][ií]c[aá][oó]' OR  a.rut ~* 'c[oó]nc[eé][ií]c[aá][oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'c[oó]nc[eé][ií]c[aá][oó]' OR  av.rf_rut ~* 'c[oó]nc[eé][ií]c[aá][oó]' OR  text(c.id) ~* 'c[oó]nc[eé][ií]c[aá][oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[eé]sp[ií]n[oó]z[aá]' OR  pap.rut ~* '[eé]sp[ií]n[oó]z[aá]' OR lower(a.nombres||' '||a.apellidos) ~* '[eé]sp[ií]n[oó]z[aá]' OR  a.rut ~* '[eé]sp[ií]n[oó]z[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[eé]sp[ií]n[oó]z[aá]' OR  av.rf_rut ~* '[eé]sp[ií]n[oó]z[aá]' OR  text(c.id) ~* '[eé]sp[ií]n[oó]z[aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[ií]ngr[ií]d' OR  pap.rut ~* '[ií]ngr[ií]d' OR lower(a.nombres||' '||a.apellidos) ~* '[ií]ngr[ií]d' OR  a.rut ~* '[ií]ngr[ií]d' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[ií]ngr[ií]d' OR  av.rf_rut ~* '[ií]ngr[ií]d' OR  text(c.id) ~* '[ií]ngr[ií]d' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER