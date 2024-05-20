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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'j[uú][aá]n' OR  pap.rut ~* 'j[uú][aá]n' OR lower(a.nombres||' '||a.apellidos) ~* 'j[uú][aá]n' OR  a.rut ~* 'j[uú][aá]n' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'j[uú][aá]n' OR  av.rf_rut ~* 'j[uú][aá]n' OR  text(c.id) ~* 'j[uú][aá]n' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'fr[aá]nc[ií]sc[oó]' OR  pap.rut ~* 'fr[aá]nc[ií]sc[oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'fr[aá]nc[ií]sc[oó]' OR  a.rut ~* 'fr[aá]nc[ií]sc[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'fr[aá]nc[ií]sc[oó]' OR  av.rf_rut ~* 'fr[aá]nc[ií]sc[oó]' OR  text(c.id) ~* 'fr[aá]nc[ií]sc[oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]r[aá]y[aá]' OR  pap.rut ~* '[aá]r[aá]y[aá]' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]r[aá]y[aá]' OR  a.rut ~* '[aá]r[aá]y[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]r[aá]y[aá]' OR  av.rf_rut ~* '[aá]r[aá]y[aá]' OR  text(c.id) ~* '[aá]r[aá]y[aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'g[aá]ld[aá]m[eé]s' OR  pap.rut ~* 'g[aá]ld[aá]m[eé]s' OR lower(a.nombres||' '||a.apellidos) ~* 'g[aá]ld[aá]m[eé]s' OR  a.rut ~* 'g[aá]ld[aá]m[eé]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'g[aá]ld[aá]m[eé]s' OR  av.rf_rut ~* 'g[aá]ld[aá]m[eé]s' OR  text(c.id) ~* 'g[aá]ld[aá]m[eé]s' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER