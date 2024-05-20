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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'm[ií]g[uú][eé]l' OR  pap.rut ~* 'm[ií]g[uú][eé]l' OR lower(a.nombres||' '||a.apellidos) ~* 'm[ií]g[uú][eé]l' OR  a.rut ~* 'm[ií]g[uú][eé]l' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'm[ií]g[uú][eé]l' OR  av.rf_rut ~* 'm[ií]g[uú][eé]l' OR  text(c.id) ~* 'm[ií]g[uú][eé]l' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[eé]st[eé]b[aá]n' OR  pap.rut ~* '[eé]st[eé]b[aá]n' OR lower(a.nombres||' '||a.apellidos) ~* '[eé]st[eé]b[aá]n' OR  a.rut ~* '[eé]st[eé]b[aá]n' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[eé]st[eé]b[aá]n' OR  av.rf_rut ~* '[eé]st[eé]b[aá]n' OR  text(c.id) ~* '[eé]st[eé]b[aá]n' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[oó]t[eé][ií]z[aá]' OR  pap.rut ~* '[oó]t[eé][ií]z[aá]' OR lower(a.nombres||' '||a.apellidos) ~* '[oó]t[eé][ií]z[aá]' OR  a.rut ~* '[oó]t[eé][ií]z[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[oó]t[eé][ií]z[aá]' OR  av.rf_rut ~* '[oó]t[eé][ií]z[aá]' OR  text(c.id) ~* '[oó]t[eé][ií]z[aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'h[eé]rn[aá]nd[eé]z' OR  pap.rut ~* 'h[eé]rn[aá]nd[eé]z' OR lower(a.nombres||' '||a.apellidos) ~* 'h[eé]rn[aá]nd[eé]z' OR  a.rut ~* 'h[eé]rn[aá]nd[eé]z' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'h[eé]rn[aá]nd[eé]z' OR  av.rf_rut ~* 'h[eé]rn[aá]nd[eé]z' OR  text(c.id) ~* 'h[eé]rn[aá]nd[eé]z' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER