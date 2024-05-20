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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* '[oó]ss[aá]nd[oó]n' OR  pap.rut ~* '[oó]ss[aá]nd[oó]n' OR lower(a.nombres||' '||a.apellidos) ~* '[oó]ss[aá]nd[oó]n' OR  a.rut ~* '[oó]ss[aá]nd[oó]n' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[oó]ss[aá]nd[oó]n' OR  av.rf_rut ~* '[oó]ss[aá]nd[oó]n' OR  text(c.id) ~* '[oó]ss[aá]nd[oó]n' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'r[eé]y[eé]s' OR  pap.rut ~* 'r[eé]y[eé]s' OR lower(a.nombres||' '||a.apellidos) ~* 'r[eé]y[eé]s' OR  a.rut ~* 'r[eé]y[eé]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'r[eé]y[eé]s' OR  av.rf_rut ~* 'r[eé]y[eé]s' OR  text(c.id) ~* 'r[eé]y[eé]s' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]l[aá]n[ií]s' OR  pap.rut ~* '[aá]l[aá]n[ií]s' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]l[aá]n[ií]s' OR  a.rut ~* '[aá]l[aá]n[ií]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]l[aá]n[ií]s' OR  av.rf_rut ~* '[aá]l[aá]n[ií]s' OR  text(c.id) ~* '[aá]l[aá]n[ií]s' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'sc[aá]rl[eé]tt' OR  pap.rut ~* 'sc[aá]rl[eé]tt' OR lower(a.nombres||' '||a.apellidos) ~* 'sc[aá]rl[eé]tt' OR  a.rut ~* 'sc[aá]rl[eé]tt' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'sc[aá]rl[eé]tt' OR  av.rf_rut ~* 'sc[aá]rl[eé]tt' OR  text(c.id) ~* 'sc[aá]rl[eé]tt' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER