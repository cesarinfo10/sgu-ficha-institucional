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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'm[aá]rc[eé]l[aá]' OR  pap.rut ~* 'm[aá]rc[eé]l[aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'm[aá]rc[eé]l[aá]' OR  a.rut ~* 'm[aá]rc[eé]l[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'm[aá]rc[eé]l[aá]' OR  av.rf_rut ~* 'm[aá]rc[eé]l[aá]' OR  text(c.id) ~* 'm[aá]rc[eé]l[aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]ndr[eé][aá]' OR  pap.rut ~* '[aá]ndr[eé][aá]' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]ndr[eé][aá]' OR  a.rut ~* '[aá]ndr[eé][aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]ndr[eé][aá]' OR  av.rf_rut ~* '[aá]ndr[eé][aá]' OR  text(c.id) ~* '[aá]ndr[eé][aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'r[aá]m[oó]s' OR  pap.rut ~* 'r[aá]m[oó]s' OR lower(a.nombres||' '||a.apellidos) ~* 'r[aá]m[oó]s' OR  a.rut ~* 'r[aá]m[oó]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'r[aá]m[oó]s' OR  av.rf_rut ~* 'r[aá]m[oó]s' OR  text(c.id) ~* 'r[aá]m[oó]s' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'br[aá]v[oó]' OR  pap.rut ~* 'br[aá]v[oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'br[aá]v[oó]' OR  a.rut ~* 'br[aá]v[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'br[aá]v[oó]' OR  av.rf_rut ~* 'br[aá]v[oó]' OR  text(c.id) ~* 'br[aá]v[oó]' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER