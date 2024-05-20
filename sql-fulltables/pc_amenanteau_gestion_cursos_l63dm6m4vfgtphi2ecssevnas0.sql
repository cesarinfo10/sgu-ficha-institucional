COPY (SELECT c.id,cod_asignatura||'-'||c.seccion||' '||asignatura AS asignatura,profesor,rut,
                           u.funcion||coalesce(' ('||horas_planta_docencia||')','') as funcion,
                           u.categorizacion,cant_alumnos_asist(c.id),ga.nombre AS grado_academico,
                           2023-(SELECT min(ano) FROM cursos WHERE id_profesor=u.id)+1 AS antiguedad,
                           (SELECT count(id) AS cant_sesiones FROM cursos_sesiones WHERE id_curso=c.id) AS cant_sesiones,
                           c.semestre||'-'||c.ano AS periodo,sesion1,sesion2,sesion3,(SELECT CASE WHEN count(id)=count(materia) AND count(id)>0 THEN 'SI' WHEN c.seccion=9 THEN 'NC' ELSE 'NO' END AS cal FROM calendarizaciones WHERE id_curso=c.id) AS calendarizado,
                           to_char(c.fec_ini,'TMDay DD-tmMon-YYYY') AS fec_ini,to_char(c.fec_fin,'TMDay DD-tmMon-YYYY') AS fec_fin,c.seccion,
                           (SELECT char_comma_sum(vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura) AS asig_fusionadas FROM vista_gestion_cursos AS vc LEFT JOIN cursos AS ca USING (id) WHERE ca.id_fusion = c.id) AS asig_fusionadas,
                           CASE WHEN ayudantia THEN 'Si' ELSE 'No' END AS ayudantia
                    FROM vista_gestion_cursos AS vc
                    LEFT JOIN cursos AS c USING (id)
                    LEFT JOIN carreras AS car ON car.id=vc.id_carrera
                    LEFT JOIN usuarios AS u ON u.id=vc.id_profesor
                    LEFT JOIN grado_acad AS ga ON ga.id=u.grado_academico
                    WHERE id_fusion IS NULL  AND (lower(asignatura) ~* 'm[aá]r[ií][oó]' OR       cod_asignatura ~* 'm[aá]r[ií][oó]' OR       text(c.id) ~* 'm[aá]r[ií][oó]' OR       lower(profesor) ~* 'm[aá]r[ií][oó]')  AND (lower(asignatura) ~* '[aá]lb[eé]rt[oó]' OR       cod_asignatura ~* '[aá]lb[eé]rt[oó]' OR       text(c.id) ~* '[aá]lb[eé]rt[oó]' OR       lower(profesor) ~* '[aá]lb[eé]rt[oó]')  AND (lower(asignatura) ~* '[eé]l[ií]z[oó]nd[oó]' OR       cod_asignatura ~* '[eé]l[ií]z[oó]nd[oó]' OR       text(c.id) ~* '[eé]l[ií]z[oó]nd[oó]' OR       lower(profesor) ~* '[eé]l[ií]z[oó]nd[oó]')  AND (lower(asignatura) ~* '[aá]r[eé]n[aá]s' OR       cod_asignatura ~* '[aá]r[eé]n[aá]s' OR       text(c.id) ~* '[aá]r[eé]n[aá]s' OR       lower(profesor) ~* '[aá]r[eé]n[aá]s')  AND id_carrera IN (101,62,26,88,18,3,36,67,12)  
                    ORDER BY c.ano DESC, c.semestre DESC, vc.carrera, c.seccion, cod_asignatura ) to stdout WITH CSV HEADER