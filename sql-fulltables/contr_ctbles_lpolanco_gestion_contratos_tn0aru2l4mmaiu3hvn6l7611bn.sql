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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'g[aá]br[ií][eé]l' OR  pap.rut ~* 'g[aá]br[ií][eé]l' OR lower(a.nombres||' '||a.apellidos) ~* 'g[aá]br[ií][eé]l' OR  a.rut ~* 'g[aá]br[ií][eé]l' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'g[aá]br[ií][eé]l' OR  av.rf_rut ~* 'g[aá]br[ií][eé]l' OR  text(c.id) ~* 'g[aá]br[ií][eé]l' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'v[aá]sq[uú][eé]z' OR  pap.rut ~* 'v[aá]sq[uú][eé]z' OR lower(a.nombres||' '||a.apellidos) ~* 'v[aá]sq[uú][eé]z' OR  a.rut ~* 'v[aá]sq[uú][eé]z' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'v[aá]sq[uú][eé]z' OR  av.rf_rut ~* 'v[aá]sq[uú][eé]z' OR  text(c.id) ~* 'v[aá]sq[uú][eé]z' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* '[oó]s[oó]r[ií][oó]' OR  pap.rut ~* '[oó]s[oó]r[ií][oó]' OR lower(a.nombres||' '||a.apellidos) ~* '[oó]s[oó]r[ií][oó]' OR  a.rut ~* '[oó]s[oó]r[ií][oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '[oó]s[oó]r[ií][oó]' OR  av.rf_rut ~* '[oó]s[oó]r[ií][oó]' OR  text(c.id) ~* '[oó]s[oó]r[ií][oó]' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER