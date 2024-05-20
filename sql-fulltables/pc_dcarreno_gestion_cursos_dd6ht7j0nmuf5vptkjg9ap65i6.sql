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
                    WHERE id_fusion IS NULL  AND (lower(asignatura) ~* 'j[aá][ií]m[eé]' OR       cod_asignatura ~* 'j[aá][ií]m[eé]' OR       text(c.id) ~* 'j[aá][ií]m[eé]' OR       lower(profesor) ~* 'j[aá][ií]m[eé]')  AND (lower(asignatura) ~* 'g[eé]r[aá]rd[oó]' OR       cod_asignatura ~* 'g[eé]r[aá]rd[oó]' OR       text(c.id) ~* 'g[eé]r[aá]rd[oó]' OR       lower(profesor) ~* 'g[eé]r[aá]rd[oó]')  AND (lower(asignatura) ~* 'p[ií]nt[oó]' OR       cod_asignatura ~* 'p[ií]nt[oó]' OR       text(c.id) ~* 'p[ií]nt[oó]' OR       lower(profesor) ~* 'p[ií]nt[oó]')  AND (lower(asignatura) ~* 'k[aá]l[ií]sk[ií]' OR       cod_asignatura ~* 'k[aá]l[ií]sk[ií]' OR       text(c.id) ~* 'k[aá]l[ií]sk[ií]' OR       lower(profesor) ~* 'k[aá]l[ií]sk[ií]')  
                    ORDER BY c.ano DESC, c.semestre DESC, vc.carrera, c.seccion, cod_asignatura ) to stdout WITH CSV HEADER