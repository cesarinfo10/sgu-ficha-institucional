COPY (SELECT * FROM (SELECT *,CASE WHEN tasa_riesgo BETWEEN 75 and 100 THEN 'Alto'
                              WHEN tasa_riesgo BETWEEN 50 and 74  THEN 'Medio Alto'
                              WHEN tasa_riesgo BETWEEN 25 and 50  THEN 'Medio'
                              WHEN tasa_riesgo BETWEEN 0  and 25  THEN 'Bajo' END AS riesgo_desercion
                FROM (SELECT *,(riesgo_morosidad+riesgo_asistencia+riesgo_c1+riesgo_s1+riesgo_s2) AS tasa_riesgo FROM (SELECT a.id,trim(a.rut) AS rut,upper(a.apellidos) AS apellidos,initcap(a.nombres) AS nombres,
                       a.genero,a.fec_nac,c.alias||'-'||a.jornada AS carrera,c.regimen,at.nombre AS admision,
                       (SELECT dd.id FROM doctos_digitalizados dd LEFT JOIN doctos_digital_tipos ddt ON dd.id_tipo=ddt.id WHERE rut=a.rut AND ddt.alias='fotos' AND NOT eliminado) AS id_foto,
                       a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.mes_cohorte,
                       a.tel_movil,a.telefono,a.email,a.nombre_usuario||'@alumni.umc.cl' AS email_gsuite,
                       ae.nombre AS estado,to_char(a.estado_fecha,'DD-MM-YYYY') AS estado_fecha,
                       (SELECT count(id_alumno) FROM cargas_academicas WHERE id_alumno=a.id AND id_curso IN (SELECT id FROM cursos WHERE semestre=2 AND ano=2022) AND (id_estado IS NULL OR id_estado <> 6)) AS cant_cursos_insc,
                       (SELECT count(id_alumno) FROM cargas_academicas WHERE id_alumno=a.id AND id_curso IN (SELECT id FROM cursos WHERE semestre=1 AND ano=2022) AND (id_estado IS NULL OR id_estado <> 6)) AS cant_cursos_insc_2dosem,
                       CASE WHEN mat.id_alumno IS NOT NULL THEN 'Si' ELSE 'No' END AS matriculado,moroso_financiero,
                       coalesce(niv.nota::text,'NSP') AS nivelacion,
                       CASE WHEN niv.nota IS NULL            THEN 10
                            WHEN niv.nota BETWEEN 1 AND 3.99 THEN 10
                            WHEN niv.nota BETWEEN 4 AND 5    THEN 8
                            WHEN niv.nota BETWEEN 5 AND 6    THEN 4
                            WHEN niv.nota BETWEEN 6 AND 7    THEN 0
                       END riesgo_niv,
                       CASE WHEN moroso_financiero AND (SELECT sum(coalesce(cuotas_morosas,0)) AS cuotas_morosas FROM vista_contratos WHERE id_alumno=a.id OR id_pap=a.id_pap) >= 3 AND (SELECT sum(coalesce(monto_repactado,0)) AS monto_repactado FROM vista_contratos WHERE id_alumno=a.id OR id_pap=a.id_pap) = 0               THEN 25
                            WHEN moroso_financiero AND (SELECT sum(coalesce(cuotas_morosas,0)) AS cuotas_morosas FROM vista_contratos WHERE id_alumno=a.id OR id_pap=a.id_pap) IN (1,2) AND (SELECT sum(coalesce(monto_repactado,0)) AS monto_repactado FROM vista_contratos WHERE id_alumno=a.id OR id_pap=a.id_pap) = 0           THEN 15
                            WHEN moroso_financiero AND (SELECT sum(coalesce(monto_moroso,0)) AS monto_moroso FROM vista_contratos WHERE id_alumno=a.id OR id_pap=a.id_pap) > 0 AND (SELECT sum(coalesce(monto_repactado,0)) AS monto_repactado FROM vista_contratos WHERE id_alumno=a.id OR id_pap=a.id_pap) > 0                  THEN 15
                            WHEN moroso_financiero AND (SELECT sum(coalesce(monto_moroso,0)) AS monto_moroso FROM vista_contratos WHERE id_alumno=a.id OR id_pap=a.id_pap) > (150746) AND (SELECT sum(coalesce(monto_repactado,0)) AS monto_repactado FROM vista_contratos WHERE id_alumno=a.id OR id_pap=a.id_pap) = 0 THEN 15
                            WHEN moroso_financiero AND (SELECT sum(coalesce(monto_moroso,0)) AS monto_moroso FROM vista_contratos WHERE id_alumno=a.id OR id_pap=a.id_pap) BETWEEN 1 AND (150746)                    THEN 5
                            WHEN NOT moroso_financiero                                                                         THEN 0
                            ELSE 0
                       END AS riesgo_morosidad,
                       CASE WHEN (SELECT count(ca.id) 
                       FROM cargas_academicas AS ca 
                       LEFT JOIN ca_asistencia AS caa ON caa.id_ca=ca.id 
                       WHERE id_sesion IN (SELECT id FROM cursos_sesiones WHERE fecha BETWEEN now()::date-'4 weeks'::interval AND now()::date 
                                                                            AND id_curso IN (SELECT id FROM cursos WHERE ano=2022 AND semestre=2)) 
                         AND id_alumno=a.id) > 0 THEN round((SELECT count(ca.id) 
                       FROM cargas_academicas AS ca 
                       LEFT JOIN ca_asistencia AS caa ON caa.id_ca=ca.id 
                       WHERE id_sesion IN (SELECT id FROM cursos_sesiones WHERE fecha BETWEEN now()::date-'4 weeks'::interval AND now()::date 
                                                                            AND id_curso IN (SELECT id FROM cursos WHERE ano=2022 AND semestre=2)) 
                         AND id_alumno=a.id AND presente)::numeric*100/(SELECT count(ca.id) 
                       FROM cargas_academicas AS ca 
                       LEFT JOIN ca_asistencia AS caa ON caa.id_ca=ca.id 
                       WHERE id_sesion IN (SELECT id FROM cursos_sesiones WHERE fecha BETWEEN now()::date-'4 weeks'::interval AND now()::date 
                                                                            AND id_curso IN (SELECT id FROM cursos WHERE ano=2022 AND semestre=2)) 
                         AND id_alumno=a.id),0) ELSE 0 END AS asistencia,
                       CASE WHEN (SELECT count(ca.id) 
                       FROM cargas_academicas AS ca 
                       LEFT JOIN ca_asistencia AS caa ON caa.id_ca=ca.id 
                       WHERE id_sesion IN (SELECT id FROM cursos_sesiones WHERE fecha BETWEEN now()::date-'4 weeks'::interval AND now()::date 
                                                                            AND id_curso IN (SELECT id FROM cursos WHERE ano=2022 AND semestre=2)) 
                         AND id_alumno=a.id) = 0 OR ((SELECT count(ca.id) 
                       FROM cargas_academicas AS ca 
                       LEFT JOIN ca_asistencia AS caa ON caa.id_ca=ca.id 
                       WHERE id_sesion IN (SELECT id FROM cursos_sesiones WHERE fecha BETWEEN now()::date-'4 weeks'::interval AND now()::date 
                                                                            AND id_curso IN (SELECT id FROM cursos WHERE ano=2022 AND semestre=2)) 
                         AND id_alumno=a.id) > 0 AND round((SELECT count(ca.id) 
                       FROM cargas_academicas AS ca 
                       LEFT JOIN ca_asistencia AS caa ON caa.id_ca=ca.id 
                       WHERE id_sesion IN (SELECT id FROM cursos_sesiones WHERE fecha BETWEEN now()::date-'4 weeks'::interval AND now()::date 
                                                                            AND id_curso IN (SELECT id FROM cursos WHERE ano=2022 AND semestre=2)) 
                         AND id_alumno=a.id AND presente)::numeric/(SELECT count(ca.id) 
                       FROM cargas_academicas AS ca 
                       LEFT JOIN ca_asistencia AS caa ON caa.id_ca=ca.id 
                       WHERE id_sesion IN (SELECT id FROM cursos_sesiones WHERE fecha BETWEEN now()::date-'4 weeks'::interval AND now()::date 
                                                                            AND id_curso IN (SELECT id FROM cursos WHERE ano=2022 AND semestre=2)) 
                         AND id_alumno=a.id),1) < 0.5) THEN 10 ELSE 0 END AS riesgo_asistencia,
                       CASE WHEN (SELECT count(id) FROM sinergia.respuestas WHERE rut_alumno=a.rut) = 0 THEN 10 ELSE 0 END AS riesgo_pruebas_psico,
                       CASE WHEN round((SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND c1='Aprob' GROUP BY id_alumno)::numeric*100/(SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id )) BETWEEN 67 AND 100 THEN 0
                            WHEN round((SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND c1='Aprob' GROUP BY id_alumno)::numeric*100/(SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id )) BETWEEN 34 AND 66  THEN 5
                            WHEN round((SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND c1='Aprob' GROUP BY id_alumno)::numeric*100/(SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id )) BETWEEN 0  AND 33  THEN 10
                            WHEN (SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND c1='Aprob' GROUP BY id_alumno) IS NULL                                          THEN 10
                       END AS riesgo_c1,
                       CASE WHEN (SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND c1='N/A' GROUP BY id_alumno) = (SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id ) THEN 10 ELSE 0 END AS base_riesgo_c1,
                       CASE WHEN round((SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND solemne1='Aprob' GROUP BY id_alumno)::numeric*100/(SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id )) BETWEEN 67 AND 100 THEN 0
                            WHEN round((SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND solemne1='Aprob' GROUP BY id_alumno)::numeric*100/(SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id )) BETWEEN 34 AND 66  THEN 10
                            WHEN round((SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND solemne1='Aprob' GROUP BY id_alumno)::numeric*100/(SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id )) BETWEEN 0  AND 33  THEN 15
                            WHEN (SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND solemne1='Aprob' GROUP BY id_alumno) IS NULL                                          THEN 15
                       END AS riesgo_s1,
                       CASE WHEN (SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND solemne1='N/A' GROUP BY id_alumno) = (SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id ) THEN 15 ELSE 0 END AS base_riesgo_s1,
                       CASE WHEN round((SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND solemne2='Aprob' GROUP BY id_alumno)::numeric*100/(SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id )) BETWEEN 67 AND 100 THEN 0
                            WHEN round((SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND solemne2='Aprob' GROUP BY id_alumno)::numeric*100/(SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id )) BETWEEN 34 AND 66  THEN 10
                            WHEN round((SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND solemne2='Aprob' GROUP BY id_alumno)::numeric*100/(SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id )) BETWEEN 0  AND 33  THEN 20
                            WHEN (SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND solemne2='Aprob' GROUP BY id_alumno) IS NULL                                          THEN 20
                       END AS riesgo_s2,
                       CASE WHEN (SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND solemne2='N/A' GROUP BY id_alumno) = (SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id ) THEN 20 ELSE 0 END AS base_riesgo_s2,
                       CASE WHEN round((SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND c1='Aprob' GROUP BY id_alumno)::numeric*100/(SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id )) BETWEEN 67 AND 100 THEN 0
                            WHEN round((SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND c1='Aprob' GROUP BY id_alumno)::numeric*100/(SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id )) BETWEEN 34 AND 66  THEN 5
                            WHEN round((SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND c1='Aprob' GROUP BY id_alumno)::numeric*100/(SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id )) BETWEEN 0  AND 33  THEN 10
                            WHEN (SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND c1='Aprob' GROUP BY id_alumno) IS NULL                                          THEN 10
                       END AS riesgo_c1_sem2,
                       CASE WHEN (SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND c1='N/A' GROUP BY id_alumno) = (SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id ) THEN 10 ELSE 0 END AS base_riesgo_c1_sem2,
                       CASE WHEN round((SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND solemne1='Aprob' GROUP BY id_alumno)::numeric*100/(SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id )) BETWEEN 67 AND 100 THEN 0
                            WHEN round((SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND solemne1='Aprob' GROUP BY id_alumno)::numeric*100/(SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id )) BETWEEN 34 AND 66  THEN 10
                            WHEN round((SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND solemne1='Aprob' GROUP BY id_alumno)::numeric*100/(SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id )) BETWEEN 0  AND 33  THEN 20
                            WHEN (SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND solemne1='Aprob' GROUP BY id_alumno) IS NULL                                          THEN 20
                       END AS riesgo_s1_sem2,
                       CASE WHEN (SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND solemne1='N/A' GROUP BY id_alumno) = (SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id ) THEN 20 ELSE 0 END AS base_riesgo_s1_sem2,
                       CASE WHEN round((SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND solemne2='Aprob' GROUP BY id_alumno)::numeric*100/(SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id )) BETWEEN 67 AND 100 THEN 0
                            WHEN round((SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND solemne2='Aprob' GROUP BY id_alumno)::numeric*100/(SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id )) BETWEEN 34 AND 66  THEN 10
                            WHEN round((SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND solemne2='Aprob' GROUP BY id_alumno)::numeric*100/(SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id )) BETWEEN 0  AND 33  THEN 15
                            WHEN (SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND solemne2='Aprob' GROUP BY id_alumno) IS NULL                                          THEN 15
                       END AS riesgo_s2_sem2,
                       CASE WHEN (SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND solemne2='N/A' GROUP BY id_alumno) = (SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id ) THEN 20 ELSE 0 END AS base_riesgo_s2_sem2,
                       (SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND c1='NSP' GROUP BY id_alumno) AS c1_nsp,(SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND c1='N/A' GROUP BY id_alumno) AS c1_na,(SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND c1='Reprob' GROUP BY id_alumno) AS c1_reprob,(SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND c1='Aprob' GROUP BY id_alumno) AS c1_aprob,
                       (SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND solemne1='NSP' GROUP BY id_alumno) AS s1_nsp,(SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND solemne1='N/A' GROUP BY id_alumno) AS s1_na,(SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND solemne1='Reprob' GROUP BY id_alumno) AS s1_reprob,(SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND solemne1='Aprob' GROUP BY id_alumno) AS s1_aprob,
                       (SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND solemne2='NSP' GROUP BY id_alumno) AS s2_nsp,(SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND solemne2='N/A' GROUP BY id_alumno) AS s2_na,(SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND solemne2='Reprob' GROUP BY id_alumno) AS s2_reprob,(SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id AND solemne2='Aprob' GROUP BY id_alumno) AS s2_aprob,
                       (SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND c1='NSP' GROUP BY id_alumno) AS c1_nsp_2sem,(SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND c1='N/A' GROUP BY id_alumno) AS c1_na_2sem,(SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND c1='Reprob' GROUP BY id_alumno) AS c1_reprob_2sem,(SELECT count(c1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND c1='Aprob' GROUP BY id_alumno) AS c1_aprob_2sem,
                       (SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND solemne1='NSP' GROUP BY id_alumno) AS s1_nsp_2sem,(SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND solemne1='N/A' GROUP BY id_alumno) AS s1_na_2sem,(SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND solemne1='Reprob' GROUP BY id_alumno) AS s1_reprob_2sem,(SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND solemne1='Aprob' GROUP BY id_alumno) AS s1_aprob_2sem,
                       (SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND solemne2='NSP' GROUP BY id_alumno) AS s2_nsp_2sem,(SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND solemne2='N/A' GROUP BY id_alumno) AS s2_na_2sem,(SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND solemne2='Reprob' GROUP BY id_alumno) AS s2_reprob_2sem,(SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id AND solemne2='Aprob' GROUP BY id_alumno) AS s2_aprob_2sem,
                       (SELECT avg(nota_final)::numeric(3,1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=1 AND id_alumno=a.id ) AS prom_nf_s1,(SELECT avg(nota_final)::numeric(3,1) FROM vista_seg_rendacad WHERE ano=2022 AND semestre=2 AND id_alumno=a.id ) AS prom_nf_s2,(SELECT avg(nota_final)::numeric(3,1) FROM vista_seg_rendacad WHERE ano=2022 AND id_alumno=a.id ) AS prom_nf_anual
                FROM alumnos AS a
                LEFT JOIN pap            ON pap.id=a.id_pap
                LEFT JOIN carreras          AS c ON c.id=a.carrera_actual
                LEFT JOIN admision_tipo     AS at ON at.id=a.admision
                LEFT JOIN al_estados        AS ae ON ae.id=a.estado
                LEFT JOIN (SELECT rut_alumno,max(nota) AS nota FROM sinergia.resp_pruebas_diag WHERE ano=2022 AND semestre=2 GROUP BY rut_alumno) AS niv ON niv.rut_alumno=a.rut
                LEFT JOIN matriculas        AS mat ON (mat.id_alumno=a.id AND mat.semestre=2 AND mat.ano=2022)
                WHERE true  AND (a.cohorte = '2022') AND (a.semestre_cohorte = 2) AND (a.estado = '1') AND (c.regimen = 'PRE') AND (a.id IN (SELECT id_alumno FROM matriculas WHERE ano=2022 AND semestre=2)) 
                ORDER BY a.apellidos,a.nombres ) AS al WHERE true ) AS foo ) AS foo WHERE riesgo_desercion = 'Alto'	) to stdout WITH CSV HEADER