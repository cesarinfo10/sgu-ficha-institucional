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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'm[aá]t[uú]s' OR  pap.rut ~* 'm[aá]t[uú]s' OR lower(a.nombres||' '||a.apellidos) ~* 'm[aá]t[uú]s' OR  a.rut ~* 'm[aá]t[uú]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'm[aá]t[uú]s' OR  av.rf_rut ~* 'm[aá]t[uú]s' OR  text(c.id) ~* 'm[aá]t[uú]s' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'd[eé]' OR  pap.rut ~* 'd[eé]' OR lower(a.nombres||' '||a.apellidos) ~* 'd[eé]' OR  a.rut ~* 'd[eé]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'd[eé]' OR  av.rf_rut ~* 'd[eé]' OR  text(c.id) ~* 'd[eé]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'l[aá]' OR  pap.rut ~* 'l[aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'l[aá]' OR  a.rut ~* 'l[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'l[aá]' OR  av.rf_rut ~* 'l[aá]' OR  text(c.id) ~* 'l[aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'p[aá]rr[aá]' OR  pap.rut ~* 'p[aá]rr[aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'p[aá]rr[aá]' OR  a.rut ~* 'p[aá]rr[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'p[aá]rr[aá]' OR  av.rf_rut ~* 'p[aá]rr[aá]' OR  text(c.id) ~* 'p[aá]rr[aá]' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER