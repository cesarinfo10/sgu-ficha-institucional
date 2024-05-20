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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'r[uú]b[ií][oó]' OR  pap.rut ~* 'r[uú]b[ií][oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'r[uú]b[ií][oó]' OR  a.rut ~* 'r[uú]b[ií][oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'r[uú]b[ií][oó]' OR  av.rf_rut ~* 'r[uú]b[ií][oó]' OR  text(c.id) ~* 'r[uú]b[ií][oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 's[aá]nch[eé]z' OR  pap.rut ~* 's[aá]nch[eé]z' OR lower(a.nombres||' '||a.apellidos) ~* 's[aá]nch[eé]z' OR  a.rut ~* 's[aá]nch[eé]z' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 's[aá]nch[eé]z' OR  av.rf_rut ~* 's[aá]nch[eé]z' OR  text(c.id) ~* 's[aá]nch[eé]z' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'p[aá]tr[ií]c[ií][aá]' OR  pap.rut ~* 'p[aá]tr[ií]c[ií][aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'p[aá]tr[ií]c[ií][aá]' OR  a.rut ~* 'p[aá]tr[ií]c[ií][aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'p[aá]tr[ií]c[ií][aá]' OR  av.rf_rut ~* 'p[aá]tr[ií]c[ií][aá]' OR  text(c.id) ~* 'p[aá]tr[ií]c[ií][aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'j[aá]cq[uú][eé]l[ií]n[eé]' OR  pap.rut ~* 'j[aá]cq[uú][eé]l[ií]n[eé]' OR lower(a.nombres||' '||a.apellidos) ~* 'j[aá]cq[uú][eé]l[ií]n[eé]' OR  a.rut ~* 'j[aá]cq[uú][eé]l[ií]n[eé]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'j[aá]cq[uú][eé]l[ií]n[eé]' OR  av.rf_rut ~* 'j[aá]cq[uú][eé]l[ií]n[eé]' OR  text(c.id) ~* 'j[aá]cq[uú][eé]l[ií]n[eé]' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER