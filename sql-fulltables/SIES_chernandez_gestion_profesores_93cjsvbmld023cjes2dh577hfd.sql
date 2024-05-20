COPY (SELECT 'R' AS "TIPO_DOCUMENTO",
                           split_part(u.rut,'-',1) AS "NUM_DOCUMENTO",
                           split_part(u.rut,'-',2) AS "DV",
                           translate(upper(split_part(trim(u.apellido),' ',1)),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS "AP_PATERNO",
                           translate(upper(split_part(trim(u.apellido),' ',2)),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS "AP_MATERNO",
                           translate(upper(trim(u.nombre)),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS "NOMBRES",
                           CASE u.sexo WHEN 'f' THEN 'M' WHEN 'm' THEN 'H' END AS "SEXO",
                           to_char(u.fec_nac,'DD-MM-YYYY') AS "FECHA_NACIMIENTO",
                           p.cod_sies AS "NACIONALIDAD",
						   CASE ga.nombre 
                                WHEN 'Doctor'   THEN 1
                                WHEN 'Magister' THEN 2
                                WHEN 'Profesional' THEN 3
                                WHEN 'Licenciado' THEN 4
                                WHEN 'Técnico Nivel Superior' THEN 5
                                WHEN 'Técnico Nivel Medio' THEN 6
                                WHEN 'Licencia Media' THEN 7
                                WHEN 'No tiene' THEN 8
                           END AS "NIVEL_FORMACION_ACADEMICO",
                           translate(upper(u.grado_acad_nombre),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS "NOMBRE_TITULO_O_GRADO",
                           translate(upper(u.grado_acad_universidad),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS "NOMBRE_INSTITUCION_OBT_TITULO",
                           p2.cod_sies AS "PAIS_OBTENCION_TIT_O_GRADO",
                           to_char(u.grado_acad_fecha,'DD-MM-YYYY') AS "FECHA_OBT_TIT_O_GRADO",
                           '' AS "NIVEL_FORMACION_ESPECIALIDAD",
                           '' AS "NOMBRE_ESPECIALIDAD",
                           '' AS "NOMBRE_INST_OBT_ESPECIALIDAD",
                           '' AS "PAIS_OBTENCION_ESPECIALIDAD",
                           '' AS "FECHA_OBTENCION_ESPECIALIDAD",
                           translate(upper(u.funcion::text),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS "PRINCIPAL_CARGO_ACADEMICO",
                           u.id_cargo_normalizado_sies AS "CARGO_NORMALIZADO",
                           'ESCUELA' AS "NIVEL_SUPERIOR_ADSCRIPCION",
                           '' AS "NIVEL_SECUNDARIO_ADSCRIPCION",
                           'SANTIAGO' AS "COMUNA_MAYOR_FUNCION",
                           (SELECT translate(upper(carrera),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') FROM vista_cursos WHERE ano=2023 AND semestre=1 AND id_profesor=u.id ORDER BY carrera LIMIT 1) AS "NOMBRE_PRINCIPAL_PROGRAMA",
                           (SELECT sum(horas_semanal) FROM cursos AS c1 LEFT JOIN prog_asig AS pa1 ON pa1.id=c1.id_prog_asig WHERE id_fusion IS NULL AND c1.ano=2023 AND semestre=1 AND id_profesor=u.id) AS "TOTAL_HORAS_PRINCIPAL_PROGRAMA",
                           'SANTIAGO' AS "COMUNA_PRINCIPAL_PROGRAMA",
                           CASE WHEN u.horas_planta IS NOT NULL AND (SELECT sum(horas_semanal) FROM cursos AS c1 LEFT JOIN prog_asig AS pa1 ON pa1.id=c1.id_prog_asig WHERE id_fusion IS NULL AND c1.ano=2023 AND semestre=1 AND id_profesor=u.id) > 0 THEN u.horas_planta + ceil(((SELECT sum(horas_semanal) FROM cursos AS c1 LEFT JOIN prog_asig AS pa1 ON pa1.id=c1.id_prog_asig WHERE id_fusion IS NULL AND c1.ano=2023 AND semestre=1 AND id_profesor=u.id)-coalesce(u.horas_planta_docencia,0))*1.25)
                                WHEN u.horas_planta IS NOT NULL THEN u.horas_planta 
                           END AS "NUM_HORAS_PLANTA",
                           CASE WHEN u.horas_plazo_fijo IS NOT NULL THEN u.horas_plazo_fijo END AS "NUM_HORAS_CONTRATA",
                           CASE WHEN coalesce(u.horas_planta,0)+coalesce(u.horas_plazo_fijo,0)>0 THEN 0
                                WHEN coalesce(u.horas_planta,0)+coalesce(u.horas_plazo_fijo,0)+coalesce(u.horas_honorarios,0)=0 THEN ceil((SELECT sum(horas_semanal) FROM cursos AS c1 LEFT JOIN prog_asig AS pa1 ON pa1.id=c1.id_prog_asig WHERE id_fusion IS NULL AND c1.ano=2023 AND semestre=1 AND id_profesor=u.id)*1.25)
                                WHEN u.horas_honorarios IS NOT NULL THEN u.horas_honorarios
                           END AS "NUM_HORAS_HONORARIOS",                           
                           '' AS "HORAS_DOCENCIA_PLANTA",
                           '' AS "HORAS_DOCENCIA_CONTRATA",
                           '' AS "HORAS_DOCENCIA_HONORARIOS",
                           1 AS "VIGENCIA",
                           ga.nombre AS grado_academico,u.categorizacion
                     FROM usuarios AS u
                     LEFT JOIN vista_profesores AS vu USING (id)
                     LEFT JOIN grado_acad       AS ga ON ga.id=u.grado_academico
                     LEFT JOIN pais             AS p  ON p.localizacion=u.nacionalidad
                     LEFT JOIN pais             AS p2 ON p2.localizacion=u.grado_acad_pais
                     WHERE u.id IN (SELECT vc.id_profesor 
						  FROM vista_cursos  AS vc
						  LEFT JOIN carreras AS car ON car.id=vc.id_carrera 
						  WHERE true  AND vc.ano=2023  AND vc.semestre=1  AND vc.id_carrera IN (94,97,104,110,98,105,102,91,15,103,126,4) )) to stdout WITH CSV HEADER