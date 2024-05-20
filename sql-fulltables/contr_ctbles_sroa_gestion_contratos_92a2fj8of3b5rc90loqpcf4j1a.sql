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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'cr[ií]st[ií][aá]n' OR  pap.rut ~* 'cr[ií]st[ií][aá]n' OR lower(a.nombres||' '||a.apellidos) ~* 'cr[ií]st[ií][aá]n' OR  a.rut ~* 'cr[ií]st[ií][aá]n' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'cr[ií]st[ií][aá]n' OR  av.rf_rut ~* 'cr[ií]st[ií][aá]n' OR  text(c.id) ~* 'cr[ií]st[ií][aá]n' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'j[aá]v[ií][eé]r' OR  pap.rut ~* 'j[aá]v[ií][eé]r' OR lower(a.nombres||' '||a.apellidos) ~* 'j[aá]v[ií][eé]r' OR  a.rut ~* 'j[aá]v[ií][eé]r' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'j[aá]v[ií][eé]r' OR  av.rf_rut ~* 'j[aá]v[ií][eé]r' OR  text(c.id) ~* 'j[aá]v[ií][eé]r' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'c[aá]r[ií]m[aá]n' OR  pap.rut ~* 'c[aá]r[ií]m[aá]n' OR lower(a.nombres||' '||a.apellidos) ~* 'c[aá]r[ií]m[aá]n' OR  a.rut ~* 'c[aá]r[ií]m[aá]n' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'c[aá]r[ií]m[aá]n' OR  av.rf_rut ~* 'c[aá]r[ií]m[aá]n' OR  text(c.id) ~* 'c[aá]r[ií]m[aá]n' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'v[ií]ll[aá]bl[aá]nc[aá]' OR  pap.rut ~* 'v[ií]ll[aá]bl[aá]nc[aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'v[ií]ll[aá]bl[aá]nc[aá]' OR  a.rut ~* 'v[ií]ll[aá]bl[aá]nc[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'v[ií]ll[aá]bl[aá]nc[aá]' OR  av.rf_rut ~* 'v[ií]ll[aá]bl[aá]nc[aá]' OR  text(c.id) ~* 'v[ií]ll[aá]bl[aá]nc[aá]' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER