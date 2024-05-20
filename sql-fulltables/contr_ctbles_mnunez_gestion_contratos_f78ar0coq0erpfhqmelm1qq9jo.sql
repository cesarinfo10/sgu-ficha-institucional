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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]nt[oó]n[ií][oó]' OR  pap.rut ~* '[aá]nt[oó]n[ií][oó]' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]nt[oó]n[ií][oó]' OR  a.rut ~* '[aá]nt[oó]n[ií][oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]nt[oó]n[ií][oó]' OR  av.rf_rut ~* '[aá]nt[oó]n[ií][oó]' OR  text(c.id) ~* '[aá]nt[oó]n[ií][oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'm[uú]ñ[oó]z' OR  pap.rut ~* 'm[uú]ñ[oó]z' OR lower(a.nombres||' '||a.apellidos) ~* 'm[uú]ñ[oó]z' OR  a.rut ~* 'm[uú]ñ[oó]z' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'm[uú]ñ[oó]z' OR  av.rf_rut ~* 'm[uú]ñ[oó]z' OR  text(c.id) ~* 'm[uú]ñ[oó]z' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'f[uú][eé]nt[eé]s' OR  pap.rut ~* 'f[uú][eé]nt[eé]s' OR lower(a.nombres||' '||a.apellidos) ~* 'f[uú][eé]nt[eé]s' OR  a.rut ~* 'f[uú][eé]nt[eé]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'f[uú][eé]nt[eé]s' OR  av.rf_rut ~* 'f[uú][eé]nt[eé]s' OR  text(c.id) ~* 'f[uú][eé]nt[eé]s' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER