COPY (SELECT c.id,to_char(c.fecha,'DD-MM-YYYY') AS fecha,to_char(c.fecha,'HH24:MI') AS hora,c.tipo,c.estado,to_char(c.estado_fecha,'DD-MM-YYYY') AS estado_fecha,
                         c.morosidad_manual,be.nombre AS beca_externa,
                         CASE WHEN c.tipo='Semestral' THEN text(c.semestre)||'-'||text(c.ano) ELSE text(c.ano) END AS periodo,c.ano,
                         trim(coalesce(a.rut,pap.rut)) AS rut,coalesce(a.rut,pap.rut) AS rut_al,c.id_alumno,
                         upper(coalesce(a.apellidos,pap.apellidos)) AS al_apellidos,initcap(coalesce(a.nombres,pap.nombres)) AS al_nombres,av.rf_parentezco,
                         av.rf_rut,upper(av.rf_apellidos)||' '||initcap(av.rf_nombres) AS nombre_rf,av.rf_nombre_empresa,av.rf_telefono_empresa,
                         CASE c.estado 
                              WHEN 'E' THEN 'Emitido' 
                              WHEN 'F' THEN 'Firmado' 
                              WHEN 'R' THEN 'Retirado' 
                              WHEN 'S' THEN 'Suspendido' 
                              WHEN 'A' THEN 'Abandonado' 
                              ELSE 'Nulo'
                         END AS estado,c.monto_matricula,vc.monto_mat::int4,c.monto_matricula-vc.monto_mat::int4 AS beca_mat,c.monto_arancel,
                         CASE WHEN c.id_convenio IS NOT NULL THEN 'Procedencia' ELSE b.alias END AS nombre_beca,
                         CASE WHEN c.id_convenio IS NOT NULL        THEN round(c.monto_arancel*0.2,0)
                              WHEN c.porc_beca_arancel IS NOT NULL  THEN round(c.monto_arancel*(c.porc_beca_arancel/100),0)
                              ELSE c.monto_beca_arancel
                         END AS monto_beca_arancel_calc,
                         CASE WHEN c.monto_arancel > 0 THEN round(vc.monto_beca_arancel_calc*(1-(coalesce(CASE WHEN c.fecha_condonacion BETWEEN '2022-01-01'::date AND '2023-03-31'::date THEN c.monto_condonacion ELSE null END,0)::float/c.monto_arancel::float))) ELSE 0 END AS arancel_beca_contable,
                         CASE WHEN c.monto_arancel > 0 THEN (monto_beca_arancel_calc::float/c.monto_arancel::float) ELSE 0 END AS porc_beca_arancel,
                         c.arancel_cred_interno,
                         CASE WHEN c.monto_arancel > 0 THEN round(c.arancel_cred_interno*(1-(coalesce(CASE WHEN c.fecha_condonacion BETWEEN '2022-01-01'::date AND '2023-03-31'::date THEN c.monto_condonacion ELSE null END,0)::float/c.monto_arancel::float))) ELSE 0 END AS arancel_cred_int_contable,
                         trim(car.alias) AS carrera,c.jornada,
                         trim(c.financiamiento) AS financiamiento,
                         CASE WHEN c.id_alumno IS NOT NULL THEN 'A' 
                              WHEN c.id_pap IS NOT NULL THEN 'N'
                         END AS tipo_alumno, c.arancel_efectivo,c.arancel_cheque,
                         coalesce(c.arancel_cant_cheques,0) AS arancel_cant_cheques,
                         c.arancel_pagare_coleg,coalesce(c.arancel_cuotas_pagare_coleg,0) AS arancel_cuotas_pagare_coleg,
                         c.arancel_tarjeta_credito,coalesce(c.arancel_cant_tarj_credito,0) AS arancel_cant_tarj_credito,
                         to_char(c.fecha_condonacion,'DD-MM-YYYY') AS fecha_condonacion,
                         CASE WHEN c.fecha_condonacion BETWEEN '2022-01-01'::date AND '2023-03-31'::date THEN c.monto_condonacion ELSE null END AS monto_condonacion,
                         vc.monto_pagado,vc.monto_saldot,(SELECT sum(monto) FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa>1 AND NOT pagado AND NOT abonado) AS monto_saldot_sin_abonos,(SELECT sum(coalesce(monto-monto_abonado,monto)) FROM finanzas.cobros WHERE date_part('year',fecha_venc)>date_part('year','2023-03-31'::date)+1 AND id_contrato=c.id AND id_glosa>1 AND (NOT pagado OR abonado)) AS monto_saldot_lp,
                         vc.monto_moroso,() AS pagos_rango_fechas,
                         vc.cuotas_morosas,(SELECT max(p.fecha::date) FROM finanzas.pagos_detalle pd LEFT JOIN finanzas.pagos p ON p.id=pd.id_pago LEFT JOIN finanzas.cobros cob ON cob.id=pd.id_cobro WHERE cob.id_contrato=c.id AND p.fecha BETWEEN '2022-01-01'::date AND '2023-03-31'::date) AS fecha_ult_pago,vc.monto_repactado_anual,(SELECT sum(monto)::bigint as monto_repactado 
                       FROM finanzas.cobros 
                       WHERE id_contrato=vc.id AND id_glosa IN (20,22) 
                         AND fecha_reg BETWEEN '2022-01-01'::date AND '2023-03-31'::date) AS monto_repactado,vc.cuotas_repactadas,vc.mat_pagada,
                         u.nombre_usuario AS emisor,c.comentarios,pc.id AS nro_pagare_coleg,
                         CASE WHEN c.id_alumno IS NOT NULL 
                              THEN substr(split_part(a.apellidos,' ',1),1,20)||' '||substr(split_part(a.apellidos,' ',2),1,20)||' '||substr(a.nombres,1,20) 
                              ELSE substr(split_part(pap.apellidos,' ',1),1,20)||' '||substr(split_part(pap.apellidos,' ',2),1,20)||' '||substr(pap.nombres,1,20) 
                         END AS nombre_al_dicom,coalesce(a.cohorte,pap.cohorte) AS cohorte,
                         coalesce(a.direccion,pap.direccion) AS direccion,coalesce(va.comuna,vpap.comuna) as comuna,coalesce(va.region,vpap.region) AS region,
                         coalesce(a.telefono,pap.telefono) AS telefono,coalesce(a.tel_movil,pap.tel_movil) AS tel_movil,coalesce(a.email,pap.email) as email,
                         coalesce(a.nombre_usuario,(SELECT nombre_usuario FROM alumnos WHERE id_pap=c.id_pap LIMIT 1))||'@alumni.umc.cl' AS email_gsuite,coalesce(pap.genero,a.genero) AS genero,
                         coalesce(a.carr_ies_pro,pap.carr_ies_pro) AS profesion,coalesce(va.ies,vpap.ies) as ies_anterior,coalesce(pap.fec_nac,a.fec_nac) as fec_nac
                   FROM finanzas.contratos AS c
                   LEFT JOIN vista_contratos AS vc USING (id)
                   LEFT JOIN alumnos         AS a   ON a.id=c.id_alumno
                   LEFT JOIN pap                    ON pap.id=c.id_pap
                   LEFT JOIN vista_avales    AS vav ON vav.id=c.id_aval
                   LEFT JOIN avales          AS av  ON av.id=c.id_aval
                   LEFT JOIN carreras        AS car ON car.id=c.id_carrera      
                   LEFT JOIN becas           AS b   ON b.id=c.id_beca_arancel                             
                   LEFT JOIN usuarios        AS u   ON u.id=c.id_emisor                             
                   LEFT JOIN finanzas.pagares_colegiatura AS pc ON pc.id_contrato=c.id
                   LEFT JOIN vista_alumnos                AS va   ON va.id=c.id_alumno
                   LEFT JOIN vista_pap                    AS vpap ON vpap.id=c.id_pap
                   LEFT JOIN finanzas.becas_externas AS be ON be.id=c.id_beca_externa
                   WHERE (lower(pap.nombres||' '||pap.apellidos) ~* '16786412' OR  pap.rut ~* '16786412' OR lower(a.nombres||' '||a.apellidos) ~* '16786412' OR  a.rut ~* '16786412' OR lower(av.rf_nombres||' '||av.rf_apellidos) ~* '16786412' OR  av.rf_rut ~* '16786412' OR  text(c.id) ~* '16786412' ) 
                   ORDER BY c.fecha DESC,al_apellidos,al_nombres ) to stdout WITH CSV HEADER