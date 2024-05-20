COPY (SELECT vc.carreras,c.id,vc.cod_asignatura||'-'||c.seccion||' '||vc.asignatura AS asignatura,profesor,rut,
                           u.funcion||coalesce(' ('||horas_planta_docencia||')','') as funcion,
                           u.categorizacion,cant_alumnos_asist(c.id),
						   (pa.horas_semanal*pa.nro_semanas_semestrales) AS horas_semestrales,
						   ga.nombre AS grado_academico,
                           2024-(SELECT min(ano) FROM cursos WHERE id_profesor=u.id)+1 AS antiguedad,
                           (SELECT count(id) AS cant_sesiones FROM cursos_sesiones WHERE id_curso=c.id) AS cant_sesiones,
                           c.semestre||'-'||c.ano AS periodo,sesion1,sesion2,sesion3,(SELECT CASE WHEN count(id)=count(materia) AND count(id)>0 THEN 'SI' WHEN c.seccion=9 THEN 'NC' ELSE 'NO' END AS cal FROM calendarizaciones WHERE id_curso=c.id) AS calendarizado,
                           to_char(c.fec_ini,'TMDay DD-tmMon-YYYY') AS fec_ini,to_char(c.fec_fin,'TMDay DD-tmMon-YYYY') AS fec_fin,c.seccion,
                           (SELECT char_comma_sum(vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura) AS asig_fusionadas FROM vista_gestion_cursos AS vc LEFT JOIN cursos AS ca USING (id) WHERE ca.id_fusion = c.id) AS asig_fusionadas,
                           CASE WHEN ayudantia THEN 'Si' ELSE 'No' END AS ayudantia
                    FROM vista_gestion_cursos AS vc
                    LEFT JOIN cursos     AS c USING (id)
                    LEFT JOIN carreras   AS car ON car.id=vc.id_carrera
                    LEFT JOIN usuarios   AS u ON u.id=vc.id_profesor
                    LEFT JOIN grado_acad AS ga ON ga.id=u.grado_academico
					LEFT JOIN prog_asig  AS pa ON pa.id=c.id_prog_asig 
                    WHERE id_fusion IS NULL  AND (lower(asignatura) ~* 'gl[oó]r[ií][aá]' OR       cod_asignatura ~* 'gl[oó]r[ií][aá]' OR       text(c.id) ~* 'gl[oó]r[ií][aá]' OR       lower(profesor) ~* 'gl[oó]r[ií][aá]')  AND (lower(asignatura) ~* '[aá]l[eé]j[aá]ndr[aá]' OR       cod_asignatura ~* '[aá]l[eé]j[aá]ndr[aá]' OR       text(c.id) ~* '[aá]l[eé]j[aá]ndr[aá]' OR       lower(profesor) ~* '[aá]l[eé]j[aá]ndr[aá]')  AND (lower(asignatura) ~* 'c[aá]n[aá]l[eé]s' OR       cod_asignatura ~* 'c[aá]n[aá]l[eé]s' OR       text(c.id) ~* 'c[aá]n[aá]l[eé]s' OR       lower(profesor) ~* 'c[aá]n[aá]l[eé]s')  AND (lower(asignatura) ~* 'r[eé]y[eé]s' OR       cod_asignatura ~* 'r[eé]y[eé]s' OR       text(c.id) ~* 'r[eé]y[eé]s' OR       lower(profesor) ~* 'r[eé]y[eé]s')  
                    ORDER BY c.ano DESC, c.semestre DESC, vc.carrera, c.seccion, vc.cod_asignatura ) to stdout WITH CSV HEADER