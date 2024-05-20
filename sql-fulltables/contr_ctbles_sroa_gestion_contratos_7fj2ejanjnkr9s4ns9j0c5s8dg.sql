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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'h[eé]rvyn' OR  pap.rut ~* 'h[eé]rvyn' OR lower(a.nombres||' '||a.apellidos) ~* 'h[eé]rvyn' OR  a.rut ~* 'h[eé]rvyn' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'h[eé]rvyn' OR  av.rf_rut ~* 'h[eé]rvyn' OR  text(c.id) ~* 'h[eé]rvyn' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'j[oó]n[aá]th[aá]n' OR  pap.rut ~* 'j[oó]n[aá]th[aá]n' OR lower(a.nombres||' '||a.apellidos) ~* 'j[oó]n[aá]th[aá]n' OR  a.rut ~* 'j[oó]n[aá]th[aá]n' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'j[oó]n[aá]th[aá]n' OR  av.rf_rut ~* 'j[oó]n[aá]th[aá]n' OR  text(c.id) ~* 'j[oó]n[aá]th[aá]n' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]g[uú]d[eé]l[oó]' OR  pap.rut ~* '[aá]g[uú]d[eé]l[oó]' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]g[uú]d[eé]l[oó]' OR  a.rut ~* '[aá]g[uú]d[eé]l[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]g[uú]d[eé]l[oó]' OR  av.rf_rut ~* '[aá]g[uú]d[eé]l[oó]' OR  text(c.id) ~* '[aá]g[uú]d[eé]l[oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'v[aá]l[eé]nc[ií][aá]' OR  pap.rut ~* 'v[aá]l[eé]nc[ií][aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'v[aá]l[eé]nc[ií][aá]' OR  a.rut ~* 'v[aá]l[eé]nc[ií][aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'v[aá]l[eé]nc[ií][aá]' OR  av.rf_rut ~* 'v[aá]l[eé]nc[ií][aá]' OR  text(c.id) ~* 'v[aá]l[eé]nc[ií][aá]' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER