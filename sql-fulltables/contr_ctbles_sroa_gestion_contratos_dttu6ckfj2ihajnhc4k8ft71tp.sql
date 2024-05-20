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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'd[ií][eé]g[oó]' OR  pap.rut ~* 'd[ií][eé]g[oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'd[ií][eé]g[oó]' OR  a.rut ~* 'd[ií][eé]g[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'd[ií][eé]g[oó]' OR  av.rf_rut ~* 'd[ií][eé]g[oó]' OR  text(c.id) ~* 'd[ií][eé]g[oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 's[oó]t[oó]' OR  pap.rut ~* 's[oó]t[oó]' OR lower(a.nombres||' '||a.apellidos) ~* 's[oó]t[oó]' OR  a.rut ~* 's[oó]t[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 's[oó]t[oó]' OR  av.rf_rut ~* 's[oó]t[oó]' OR  text(c.id) ~* 's[oó]t[oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'c[aá]nd[ií][aá]' OR  pap.rut ~* 'c[aá]nd[ií][aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'c[aá]nd[ií][aá]' OR  a.rut ~* 'c[aá]nd[ií][aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'c[aá]nd[ií][aá]' OR  av.rf_rut ~* 'c[aá]nd[ií][aá]' OR  text(c.id) ~* 'c[aá]nd[ií][aá]' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER