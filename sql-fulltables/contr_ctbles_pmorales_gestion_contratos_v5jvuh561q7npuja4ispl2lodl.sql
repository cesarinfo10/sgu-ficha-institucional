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
                     WHERE (lower(pap.nombres||' '||pap.apellidos) ~* 'j[eé]ss[ií]c[aá]' OR  pap.rut ~* 'j[eé]ss[ií]c[aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'j[eé]ss[ií]c[aá]' OR  a.rut ~* 'j[eé]ss[ií]c[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'j[eé]ss[ií]c[aá]' OR  av.rf_rut ~* 'j[eé]ss[ií]c[aá]' OR  text(c.id) ~* 'j[eé]ss[ií]c[aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'br[eé]nd[aá]' OR  pap.rut ~* 'br[eé]nd[aá]' OR lower(a.nombres||' '||a.apellidos) ~* 'br[eé]nd[aá]' OR  a.rut ~* 'br[eé]nd[aá]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'br[eé]nd[aá]' OR  av.rf_rut ~* 'br[eé]nd[aá]' OR  text(c.id) ~* 'br[eé]nd[aá]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'c[oó]l[ií]ñ[aá]nc[oó]' OR  pap.rut ~* 'c[oó]l[ií]ñ[aá]nc[oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'c[oó]l[ií]ñ[aá]nc[oó]' OR  a.rut ~* 'c[oó]l[ií]ñ[aá]nc[oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'c[oó]l[ií]ñ[aá]nc[oó]' OR  av.rf_rut ~* 'c[oó]l[ií]ñ[aá]nc[oó]' OR  text(c.id) ~* 'c[oó]l[ií]ñ[aá]nc[oó]' ) AND (lower(pap.nombres||' '||pap.apellidos) ~* 'p[aá][ií]n[eé]q[uú][eé][oó]' OR  pap.rut ~* 'p[aá][ií]n[eé]q[uú][eé][oó]' OR lower(a.nombres||' '||a.apellidos) ~* 'p[aá][ií]n[eé]q[uú][eé][oó]' OR  a.rut ~* 'p[aá][ií]n[eé]q[uú][eé][oó]' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* 'p[aá][ií]n[eé]q[uú][eé][oó]' OR  av.rf_rut ~* 'p[aá][ií]n[eé]q[uú][eé][oó]' OR  text(c.id) ~* 'p[aá][ií]n[eé]q[uú][eé][oó]' ) 
                     ORDER BY c.fecha DESC ) to stdout WITH CSV HEADER