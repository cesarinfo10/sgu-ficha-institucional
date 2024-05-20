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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'cl[aá][uú]d[ií][oó]' OR  pap.rut ~* 'cl[aá][uú]d[ií][oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'cl[aá][uú]d[ií][oó]' OR  a.rut ~* 'cl[aá][uú]d[ií][oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'cl[aá][uú]d[ií][oó]' OR  av.rf_rut ~* 'cl[aá][uú]d[ií][oó]' OR  text(c.id) ~* 'cl[aá][uú]d[ií][oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'd[ií][aá]z' OR  pap.rut ~* 'd[ií][aá]z' OR lower(a.nombres||' '||a.apellidos) ~* 'd[ií][aá]z' OR  a.rut ~* 'd[ií][aá]z' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'd[ií][aá]z' OR  av.rf_rut ~* 'd[ií][aá]z' OR  text(c.id) ~* 'd[ií][aá]z' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 's[ií]lv[aá]' OR  pap.rut ~* 's[ií]lv[aá]' OR lower(a.nombres||' '||a.apellidos) ~* 's[ií]lv[aá]' OR  a.rut ~* 's[ií]lv[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 's[ií]lv[aá]' OR  av.rf_rut ~* 's[ií]lv[aá]' OR  text(c.id) ~* 's[ií]lv[aá]' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER