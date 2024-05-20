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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]rl[eé]tt[eé]' OR  pap.rut ~* '[aá]rl[eé]tt[eé]' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]rl[eé]tt[eé]' OR  a.rut ~* '[aá]rl[eé]tt[eé]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]rl[eé]tt[eé]' OR  av.rf_rut ~* '[aá]rl[eé]tt[eé]' OR  text(c.id) ~* '[aá]rl[eé]tt[eé]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]g[uú][ií]l[aá]r' OR  pap.rut ~* '[aá]g[uú][ií]l[aá]r' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]g[uú][ií]l[aá]r' OR  a.rut ~* '[aá]g[uú][ií]l[aá]r' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]g[uú][ií]l[aá]r' OR  av.rf_rut ~* '[aá]g[uú][ií]l[aá]r' OR  text(c.id) ~* '[aá]g[uú][ií]l[aá]r' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'l[aá]r[eé]n[aá]s' OR  pap.rut ~* 'l[aá]r[eé]n[aá]s' OR lower(a.nombres||' '||a.apellidos) ~* 'l[aá]r[eé]n[aá]s' OR  a.rut ~* 'l[aá]r[eé]n[aá]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'l[aá]r[eé]n[aá]s' OR  av.rf_rut ~* 'l[aá]r[eé]n[aá]s' OR  text(c.id) ~* 'l[aá]r[eé]n[aá]s' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER