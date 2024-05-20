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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'j[uú][aá]n' OR  pap.rut ~* 'j[uú][aá]n' OR lower(a.nombres||' '||a.apellidos) ~* 'j[uú][aá]n' OR  a.rut ~* 'j[uú][aá]n' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'j[uú][aá]n' OR  av.rf_rut ~* 'j[uú][aá]n' OR  text(c.id) ~* 'j[uú][aá]n' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'c[aá]rl[oó]s' OR  pap.rut ~* 'c[aá]rl[oó]s' OR lower(a.nombres||' '||a.apellidos) ~* 'c[aá]rl[oó]s' OR  a.rut ~* 'c[aá]rl[oó]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'c[aá]rl[oó]s' OR  av.rf_rut ~* 'c[aá]rl[oó]s' OR  text(c.id) ~* 'c[aá]rl[oó]s' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'c[oó]fr[eé]' OR  pap.rut ~* 'c[oó]fr[eé]' OR lower(a.nombres||' '||a.apellidos) ~* 'c[oó]fr[eé]' OR  a.rut ~* 'c[oó]fr[eé]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'c[oó]fr[eé]' OR  av.rf_rut ~* 'c[oó]fr[eé]' OR  text(c.id) ~* 'c[oó]fr[eé]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'h[eé]r[eé]d[ií][aá]' OR  pap.rut ~* 'h[eé]r[eé]d[ií][aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'h[eé]r[eé]d[ií][aá]' OR  a.rut ~* 'h[eé]r[eé]d[ií][aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'h[eé]r[eé]d[ií][aá]' OR  av.rf_rut ~* 'h[eé]r[eé]d[ií][aá]' OR  text(c.id) ~* 'h[eé]r[eé]d[ií][aá]' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER