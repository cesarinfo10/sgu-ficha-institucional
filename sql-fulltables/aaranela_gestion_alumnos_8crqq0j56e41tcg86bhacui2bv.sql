COPY (SELECT a.id,trim(a.rut) AS rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre,a.genero,a.fec_nac,c.alias||'-'||a.jornada AS carrera,c.regimen,
                       a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.mes_cohorte,a.email,(SELECT max(email_fecha::date) FROM alumnos_datos_contacto WHERE id_alumno=a.id) AS email_fecha,
                       a.semestre_cohorte_reinc||'-'||a.cohorte_reinc AS cohorte_reinc,a.mes_cohorte_reinc,
                       a.nombre_usuario||'@'||dominio as email_institucional,a.nombre_usuario||'@'||dominio_gsuite as email_gsuite,
                       a.tel_movil,a.telefono,adm.nombre as admision,
                       (SELECT estado 
                          FROM gestion.solicitudes AS sol 
                          LEFT JOIN gestion.solic_tipos AS gst ON gst.id=sol.id_tipo 
                          WHERE id_alumno=a.id AND gst.alias='solic_excep_finan' AND sol.fecha::date>='2021-10-01'
                          ORDER BY sol.fecha DESC
                          LIMIT 1) AS excep_finan,
                       (SELECT CASE WHEN fecha_compromiso IS NOT NULL   THEN 'Comprometido(a)'
                                   WHEN id_motivo_no_remat IS NOT NULL THEN 'Desertor(a)'
                                   WHEN obtiene_respuesta='f'          THEN 'Sin respuesta'
                              END AS atencion_remat
                       FROM gestion.atenciones_remat AS gar
                       WHERE gar.id_alumno=a.id AND ((fecha_compromiso IS NOT NULL AND fecha_compromiso>=now()::date) OR id_motivo_no_remat IS NOT NULL OR NOT obtiene_respuesta)
                       ORDER BY gar.fecha DESC
                       LIMIT 1) AS remat_atencion,
                       (SELECT estado
                     FROM dae.fuas
                     WHERE id_alumno=a.id and ano=2023
                     ORDER BY fecha_creacion DESC
                     LIMIT 1) AS post_becaumc,
                       be.benef_fiscal,
                       coalesce(a.rbd_colegio,pap.rbd_colegio) AS rbd_colegio,
                       coalesce(a.promedio_col,pap.promedio_col) AS nem,
                       coalesce(a.puntaje_psu,pap.puntaje_psu) AS puntaje_psu,
                       ae.nombre AS estado,to_char(a.estado_fecha,'DD-MM-YYYY') AS estado_fecha,u.nombre_usuario AS estado_operador,
                       paa.nota_final AS prom_ano_ant,
                       CASE WHEN paa.cant_asig > 0 THEN round(paaa.cant_asig*100/paa.cant_asig,0) ELSE 0 END AS avance_acad_ano_ant,
                       monto_adeudado AS deuda_total,
                       CASE WHEN moroso_financiero THEN 'Si' ELSE 'No' END AS moroso_financiero,
                       CASE WHEN (SELECT count(id) FROM sinergia.respuestas WHERE ano=2023 AND semestre=1 AND rut_alumno=a.rut)=2 THEN 'Si' ELSE 'No' END AS prubeas_psico,
                       CASE WHEN (SELECT 1 FROM matriculas WHERE id_alumno=a.id AND ano=2023 AND semestre=1 LIMIT 1)=1  THEN 'Si' ELSE 'No' END AS matriculado,(SELECT max(fecha) FROM matriculas WHERE id_alumno=a.id) AS fecha_ult_mat,
                       CASE WHEN (SELECT 1 FROM inscripciones_cursos WHERE id_alumno=a.id LIMIT 1) > 0 THEN 'Si' ELSE 'No' END AS tr_act,
                       CASE WHEN (SELECT count(id) FROM cargas_academicas WHERE id_alumno=a.id AND convalidado AND id_pa IN (SELECT id_prog_asig FROM detalle_mallas WHERE id_malla=a.malla_actual)) > 0 THEN round((SELECT count(id) FROM cargas_academicas WHERE id_alumno=a.id AND convalidado AND id_pa IN (SELECT id_prog_asig FROM detalle_mallas WHERE id_malla=a.malla_actual))::numeric*100/(coalesce(m.cant_asig_oblig,0)+coalesce(m.cant_asig_elect,0)+coalesce(m.cant_asig_efp,0)),0) ELSE 0 END AS porc_conv,
                       CASE WHEN (SELECT count(id_curso) FROM cargas_academicas WHERE id_alumno=a.id AND id_curso IN (SELECT id FROM cursos WHERE semestre=2 AND ano=2022))>0 THEN ((SELECT count(id_curso) FROM cargas_academicas WHERE id_alumno=a.id AND id_estado=1 AND id_curso IN (SELECT id FROM cursos WHERE semestre=2 AND ano=2022))::real/(SELECT count(id_curso) FROM cargas_academicas WHERE id_alumno=a.id AND id_curso IN (SELECT id FROM cursos WHERE semestre=2 AND ano=2022))::real*100)::numeric(4,1) ELSE 0 END AS tasa_aprobacion_ant,
                       CASE WHEN (SELECT count(id_curso) FROM cargas_academicas WHERE id_alumno=a.id AND id_curso IN (SELECT id FROM cursos WHERE ano=2023 AND semestre=1))>0 THEN ((SELECT count(id_curso) FROM cargas_academicas WHERE id_alumno=a.id AND id_estado=1 AND id_curso IN (SELECT id FROM cursos WHERE ano=2023 AND semestre=1))::real/(SELECT count(id_curso) FROM cargas_academicas WHERE id_alumno=a.id AND id_curso IN (SELECT id FROM cursos WHERE ano=2023 AND semestre=1))::real*100)::numeric(4,1) ELSE 0 END AS tasa_aprobacion_act,
                       CASE WHEN (SELECT count(id_alumno) AS cant_insc FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE ano=2023) AND id_alumno=a.id)>0 THEN ((SELECT count(id_alumno) AS cant_aprob FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE ano=2023) AND id_estado=1 AND id_alumno=a.id)::real/(SELECT count(id_alumno) AS cant_insc FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE ano=2023) AND id_alumno=a.id)::real*100)::numeric(4,1) ELSE 0 END AS porcentaje_avance,
                       (SELECT count(id_curso) FROM cargas_academicas AS ca LEFT JOIN cursos AS c ON c.id=ca.id_curso WHERE id_alumno=a.id AND c.seccion=9 AND ca.id_estado <> 6) AS total_cursos_modulares,
                       a.salida_int_fecha,a.salida_int_nroreg_libro,salida_int_calif,a.fecha_graduacion,a.nota_graduacion,
                       fecha_titulacion,a.fecha_egreso,anotaciones,
                       split_part(a.rut,'-',1) AS rut, split_part(a.rut,'-',2) AS dv,
                       translate(upper(split_part(trim(a.apellidos),' ',1)),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS ape_pat,
                       translate(upper(split_part(trim(a.apellidos),' ',2)),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS ape_mat,
                       translate(upper(a.nombres),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS nombre,
                       (SELECT monto_arancel*CASE trim(tipo) WHEN 'Semestral' THEN 2 ELSE 1 END AS arancel_real FROM finanzas.contratos WHERE ano=2023 AND (id_alumno=a.id OR id_pap=a.id_pap) AND estado IN ('E','S','R','A') LIMIT 1) AS arancel_real,
                       translate(upper(a.direccion),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS direccion,
                       co.cod_ingresa_cae AS cod_comuna,co.cod_ciudad_ingresa_cae AS cod_ciudad,a.region AS cod_region,
                       coalesce((SELECT ceil(max(nivel)::float/2) AS nivel
                       FROM vista_alumnos_cursos AS vac 
                       LEFT JOIN detalle_mallas dm ON (dm.id_malla=a.malla_actual AND dm.id_prog_asig=vac.id_prog_asig) 
                       WHERE vac.ano=2023 AND vac.id_alumno=a.id),1) AS nivel_estudios, 
                       a.nombres,a.apellidos,co.nombre AS comuna,p.nacionalidad,m.ano AS malla,a.nombre_usuario,
                       ies.nombre_original AS ies_proced,a.carr_ies_pro AS ies_carrera_proced,col.rbd,col.nombre AS colegio,
                       a.examen_grado_titulo_fecha,a.examen_grado_titulo_oportunidades,a.examen_grado_titulo_calif,a.nota_titulacion,a.nro_registro_libro_tit,
                       CASE WHEN (SELECT 1 FROM alumnos_sies WHERE ano=2023 AND regimen='PRE' AND rut=a.rut)=1 THEN 'Si' ELSE 'No' END AS al_sies_2023  
                FROM alumnos AS a
                LEFT JOIN pap ON pap.id=a.id_pap
                LEFT JOIN carreras   AS c ON c.id=a.carrera_actual
                LEFT JOIN regimenes_ AS r ON r.id=c.regimen
                LEFT JOIN admision_tipo AS adm ON adm.id=a.admision
                LEFT JOIN mallas     AS m ON m.id=a.malla_actual
                LEFT JOIN colegios   AS col ON col.rbd=a.rbd_colegio
                LEFT JOIN inst_edsup AS ies ON ies.id=a.id_inst_edsup_proced
                LEFT JOIN comunas    AS co ON co.id=a.comuna
                LEFT JOIN pais       AS p ON p.localizacion=a.nacionalidad
                LEFT JOIN al_estados AS ae ON ae.id=a.estado
                LEFT JOIN usuarios   AS u ON u.id=a.estado_id_usuario
                LEFT JOIN (SELECT id_alumno,round(avg(nota_final),1) as nota_final,count(id) AS cant_asig FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE ano=2023-1) AND id_estado IN (1,2) GROUP BY id_alumno)       AS paa ON paa.id_alumno=a.id
                LEFT JOIN (SELECT id_alumno,round(avg(nota_final),1) as nota_final,count(id) AS cant_asig FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE ano=2023-1) AND id_estado=1 GROUP BY id_alumno) AS paaa ON paaa.id_alumno=a.id
                LEFT JOIN vista_contratos_rut_carrera_monto_adeudado AS vcrcma ON (vcrcma.rut=a.rut AND vcrcma.id_carrera=a.carrera_actual)
                
                
                
                
                
                LEFT JOIN (SELECT DISTINCT ON (rut,be.nombre) rut,be.nombre AS benef_fiscal 
                               FROM finanzas.contratos AS c 
                               LEFT JOIN vista_contratos_rut vcr USING (id)
                               LEFT JOIN finanzas.becas_externas be ON be.id=c.id_beca_externa 
                               WHERE ano=2023 AND id_beca_externa IS NOT NULL) AS be ON be.rut=a.rut
                WHERE true  AND (a.carrera_actual = 17) AND (c.regimen = 'PRE') 
                ORDER BY a.apellidos,a.nombres ) to stdout WITH CSV HEADER