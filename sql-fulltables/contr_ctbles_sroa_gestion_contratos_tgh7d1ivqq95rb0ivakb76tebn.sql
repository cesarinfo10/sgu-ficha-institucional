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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* '[eé]d[ií]th' OR  pap.rut ~* '[eé]d[ií]th' OR lower(a.nombres||' '||a.apellidos) ~* '[eé]d[ií]th' OR  a.rut ~* '[eé]d[ií]th' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[eé]d[ií]th' OR  av.rf_rut ~* '[eé]d[ií]th' OR  text(c.id) ~* '[eé]d[ií]th' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'b[uú]rr[oó]ws' OR  pap.rut ~* 'b[uú]rr[oó]ws' OR lower(a.nombres||' '||a.apellidos) ~* 'b[uú]rr[oó]ws' OR  a.rut ~* 'b[uú]rr[oó]ws' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'b[uú]rr[oó]ws' OR  av.rf_rut ~* 'b[uú]rr[oó]ws' OR  text(c.id) ~* 'b[uú]rr[oó]ws' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER