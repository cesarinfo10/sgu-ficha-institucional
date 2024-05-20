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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'j[uú][aá]n' OR  pap.rut ~* 'j[uú][aá]n' OR lower(a.nombres||' '||a.apellidos) ~* 'j[uú][aá]n' OR  a.rut ~* 'j[uú][aá]n' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'j[uú][aá]n' OR  av.rf_rut ~* 'j[uú][aá]n' OR  text(c.id) ~* 'j[uú][aá]n' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'm[aá]n[uú][eé]l' OR  pap.rut ~* 'm[aá]n[uú][eé]l' OR lower(a.nombres||' '||a.apellidos) ~* 'm[aá]n[uú][eé]l' OR  a.rut ~* 'm[aá]n[uú][eé]l' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'm[aá]n[uú][eé]l' OR  av.rf_rut ~* 'm[aá]n[uú][eé]l' OR  text(c.id) ~* 'm[aá]n[uú][eé]l' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'r[oó]j[aá]s' OR  pap.rut ~* 'r[oó]j[aá]s' OR lower(a.nombres||' '||a.apellidos) ~* 'r[oó]j[aá]s' OR  a.rut ~* 'r[oó]j[aá]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'r[oó]j[aá]s' OR  av.rf_rut ~* 'r[oó]j[aá]s' OR  text(c.id) ~* 'r[oó]j[aá]s' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 's[oó]t[oó]' OR  pap.rut ~* 's[oó]t[oó]' OR lower(a.nombres||' '||a.apellidos) ~* 's[oó]t[oó]' OR  a.rut ~* 's[oó]t[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 's[oó]t[oó]' OR  av.rf_rut ~* 's[oó]t[oó]' OR  text(c.id) ~* 's[oó]t[oó]' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER