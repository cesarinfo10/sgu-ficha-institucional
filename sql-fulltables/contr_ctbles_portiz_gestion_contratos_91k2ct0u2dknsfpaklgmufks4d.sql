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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'w[ií]ll[ií][aá]m' OR  pap.rut ~* 'w[ií]ll[ií][aá]m' OR lower(a.nombres||' '||a.apellidos) ~* 'w[ií]ll[ií][aá]m' OR  a.rut ~* 'w[ií]ll[ií][aá]m' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'w[ií]ll[ií][aá]m' OR  av.rf_rut ~* 'w[ií]ll[ií][aá]m' OR  text(c.id) ~* 'w[ií]ll[ií][aá]m' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]lb[eé]rt[oó]' OR  pap.rut ~* '[aá]lb[eé]rt[oó]' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]lb[eé]rt[oó]' OR  a.rut ~* '[aá]lb[eé]rt[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]lb[eé]rt[oó]' OR  av.rf_rut ~* '[aá]lb[eé]rt[oó]' OR  text(c.id) ~* '[aá]lb[eé]rt[oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'v[aá]rg[aá]s' OR  pap.rut ~* 'v[aá]rg[aá]s' OR lower(a.nombres||' '||a.apellidos) ~* 'v[aá]rg[aá]s' OR  a.rut ~* 'v[aá]rg[aá]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'v[aá]rg[aá]s' OR  av.rf_rut ~* 'v[aá]rg[aá]s' OR  text(c.id) ~* 'v[aá]rg[aá]s' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'c[aá]b[eé]ll[oó]' OR  pap.rut ~* 'c[aá]b[eé]ll[oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'c[aá]b[eé]ll[oó]' OR  a.rut ~* 'c[aá]b[eé]ll[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'c[aá]b[eé]ll[oó]' OR  av.rf_rut ~* 'c[aá]b[eé]ll[oó]' OR  text(c.id) ~* 'c[aá]b[eé]ll[oó]' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER