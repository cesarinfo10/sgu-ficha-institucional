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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'f[eé]rn[aá]nd[oó]' OR  pap.rut ~* 'f[eé]rn[aá]nd[oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'f[eé]rn[aá]nd[oó]' OR  a.rut ~* 'f[eé]rn[aá]nd[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'f[eé]rn[aá]nd[oó]' OR  av.rf_rut ~* 'f[eé]rn[aá]nd[oó]' OR  text(c.id) ~* 'f[eé]rn[aá]nd[oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'br[aá]v[oó]' OR  pap.rut ~* 'br[aá]v[oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'br[aá]v[oó]' OR  a.rut ~* 'br[aá]v[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'br[aá]v[oó]' OR  av.rf_rut ~* 'br[aá]v[oó]' OR  text(c.id) ~* 'br[aá]v[oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[aá]lm[ií]r[aá][ií]s' OR  pap.rut ~* '[aá]lm[ií]r[aá][ií]s' OR lower(a.nombres||' '||a.apellidos) ~* '[aá]lm[ií]r[aá][ií]s' OR  a.rut ~* '[aá]lm[ií]r[aá][ií]s' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[aá]lm[ií]r[aá][ií]s' OR  av.rf_rut ~* '[aá]lm[ií]r[aá][ií]s' OR  text(c.id) ~* '[aá]lm[ií]r[aá][ií]s' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER