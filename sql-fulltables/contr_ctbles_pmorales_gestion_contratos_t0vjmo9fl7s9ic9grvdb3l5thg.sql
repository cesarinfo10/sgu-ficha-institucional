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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'k[aá]rl[aá]' OR  pap.rut ~* 'k[aá]rl[aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'k[aá]rl[aá]' OR  a.rut ~* 'k[aá]rl[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'k[aá]rl[aá]' OR  av.rf_rut ~* 'k[aá]rl[aá]' OR  text(c.id) ~* 'k[aá]rl[aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]ndr[eé][aá]' OR  pap.rut ~* '[aá]ndr[eé][aá]' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]ndr[eé][aá]' OR  a.rut ~* '[aá]ndr[eé][aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]ndr[eé][aá]' OR  av.rf_rut ~* '[aá]ndr[eé][aá]' OR  text(c.id) ~* '[aá]ndr[eé][aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'v[aá]ld[eé]s' OR  pap.rut ~* 'v[aá]ld[eé]s' OR lower(a.nombres||' '||a.apellidos) ~* 'v[aá]ld[eé]s' OR  a.rut ~* 'v[aá]ld[eé]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'v[aá]ld[eé]s' OR  av.rf_rut ~* 'v[aá]ld[eé]s' OR  text(c.id) ~* 'v[aá]ld[eé]s' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'd[uú]r[aá]n' OR  pap.rut ~* 'd[uú]r[aá]n' OR lower(a.nombres||' '||a.apellidos) ~* 'd[uú]r[aá]n' OR  a.rut ~* 'd[uú]r[aá]n' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'd[uú]r[aá]n' OR  av.rf_rut ~* 'd[uú]r[aá]n' OR  text(c.id) ~* 'd[uú]r[aá]n' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER