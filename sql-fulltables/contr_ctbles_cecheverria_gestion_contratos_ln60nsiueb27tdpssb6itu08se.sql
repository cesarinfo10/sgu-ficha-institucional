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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 't[eé][uú]f[eé]rt' OR  pap.rut ~* 't[eé][uú]f[eé]rt' OR lower(a.nombres||' '||a.apellidos) ~* 't[eé][uú]f[eé]rt' OR  a.rut ~* 't[eé][uú]f[eé]rt' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 't[eé][uú]f[eé]rt' OR  av.rf_rut ~* 't[eé][uú]f[eé]rt' OR  text(c.id) ~* 't[eé][uú]f[eé]rt' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'g[oó]nz[aá]l[eé]z' OR  pap.rut ~* 'g[oó]nz[aá]l[eé]z' OR lower(a.nombres||' '||a.apellidos) ~* 'g[oó]nz[aá]l[eé]z' OR  a.rut ~* 'g[oó]nz[aá]l[eé]z' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'g[oó]nz[aá]l[eé]z' OR  av.rf_rut ~* 'g[oó]nz[aá]l[eé]z' OR  text(c.id) ~* 'g[oó]nz[aá]l[eé]z' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[eé]r[ií]ch' OR  pap.rut ~* '[eé]r[ií]ch' OR lower(a.nombres||' '||a.apellidos) ~* '[eé]r[ií]ch' OR  a.rut ~* '[eé]r[ií]ch' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[eé]r[ií]ch' OR  av.rf_rut ~* '[eé]r[ií]ch' OR  text(c.id) ~* '[eé]r[ií]ch' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]l[eé]j[aá]ndr[oó]' OR  pap.rut ~* '[aá]l[eé]j[aá]ndr[oó]' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]l[eé]j[aá]ndr[oó]' OR  a.rut ~* '[aá]l[eé]j[aá]ndr[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]l[eé]j[aá]ndr[oó]' OR  av.rf_rut ~* '[aá]l[eé]j[aá]ndr[oó]' OR  text(c.id) ~* '[aá]l[eé]j[aá]ndr[oó]' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER