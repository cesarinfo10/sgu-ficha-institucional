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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'b[eé]nj[aá]m[ií]n' OR  pap.rut ~* 'b[eé]nj[aá]m[ií]n' OR lower(a.nombres||' '||a.apellidos) ~* 'b[eé]nj[aá]m[ií]n' OR  a.rut ~* 'b[eé]nj[aá]m[ií]n' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'b[eé]nj[aá]m[ií]n' OR  av.rf_rut ~* 'b[eé]nj[aá]m[ií]n' OR  text(c.id) ~* 'b[eé]nj[aá]m[ií]n' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]l[eé]j[aá]ndr[oó]' OR  pap.rut ~* '[aá]l[eé]j[aá]ndr[oó]' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]l[eé]j[aá]ndr[oó]' OR  a.rut ~* '[aá]l[eé]j[aá]ndr[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]l[eé]j[aá]ndr[oó]' OR  av.rf_rut ~* '[aá]l[eé]j[aá]ndr[oó]' OR  text(c.id) ~* '[aá]l[eé]j[aá]ndr[oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'h[eé]rn[aá]nd[eé]z' OR  pap.rut ~* 'h[eé]rn[aá]nd[eé]z' OR lower(a.nombres||' '||a.apellidos) ~* 'h[eé]rn[aá]nd[eé]z' OR  a.rut ~* 'h[eé]rn[aá]nd[eé]z' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'h[eé]rn[aá]nd[eé]z' OR  av.rf_rut ~* 'h[eé]rn[aá]nd[eé]z' OR  text(c.id) ~* 'h[eé]rn[aá]nd[eé]z' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'p[oó]bl[eé]t[eé]' OR  pap.rut ~* 'p[oó]bl[eé]t[eé]' OR lower(a.nombres||' '||a.apellidos) ~* 'p[oó]bl[eé]t[eé]' OR  a.rut ~* 'p[oó]bl[eé]t[eé]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'p[oó]bl[eé]t[eé]' OR  av.rf_rut ~* 'p[oó]bl[eé]t[eé]' OR  text(c.id) ~* 'p[oó]bl[eé]t[eé]' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER