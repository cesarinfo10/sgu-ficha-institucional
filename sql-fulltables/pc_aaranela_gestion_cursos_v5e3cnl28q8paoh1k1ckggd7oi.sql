COPY (SELECT c.id,cod_asignatura||'-'||c.seccion||' '||asignatura AS asignatura,profesor,rut,
                           u.funcion||coalesce(' ('||horas_planta_docencia||')','') as funcion,
                           u.categorizacion,cant_alumnos_asist(c.id),ga.nombre AS grado_academico,
                           2022-(SELECT min(ano) FROM cursos WHERE id_profesor=u.id)+1 AS antiguedad,
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
                    WHERE id_fusion IS NULL  AND (lower(asignatura) ~* 'j[aá]v[ií][eé]r[aá]' OR       cod_asignatura ~* 'j[aá]v[ií][eé]r[aá]' OR       text(c.id) ~* 'j[aá]v[ií][eé]r[aá]' OR       lower(profesor) ~* 'j[aá]v[ií][eé]r[aá]')  AND (lower(asignatura) ~* 'd[eé]' OR       cod_asignatura ~* 'd[eé]' OR       text(c.id) ~* 'd[eé]' OR       lower(profesor) ~* 'd[eé]')  AND (lower(asignatura) ~* 'l[oó]s' OR       cod_asignatura ~* 'l[oó]s' OR       text(c.id) ~* 'l[oó]s' OR       lower(profesor) ~* 'l[oó]s')  AND (lower(asignatura) ~* '[aá]ng[eé]l[eé]s' OR       cod_asignatura ~* '[aá]ng[eé]l[eé]s' OR       text(c.id) ~* '[aá]ng[eé]l[eé]s' OR       lower(profesor) ~* '[aá]ng[eé]l[eé]s')  AND (lower(asignatura) ~* 'pl[aá]z[aá]' OR       cod_asignatura ~* 'pl[aá]z[aá]' OR       text(c.id) ~* 'pl[aá]z[aá]' OR       lower(profesor) ~* 'pl[aá]z[aá]')  AND (lower(asignatura) ~* 'b[aá]rr[ií]g[aá]' OR       cod_asignatura ~* 'b[aá]rr[ií]g[aá]' OR       text(c.id) ~* 'b[aá]rr[ií]g[aá]' OR       lower(profesor) ~* 'b[aá]rr[ií]g[aá]')  
                    ORDER BY c.ano DESC, c.semestre DESC, vc.carrera, c.seccion, cod_asignatura ) to stdout WITH CSV HEADER