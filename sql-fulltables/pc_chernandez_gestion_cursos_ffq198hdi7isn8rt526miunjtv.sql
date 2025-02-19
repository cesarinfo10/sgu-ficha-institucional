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
                    WHERE id_fusion IS NULL  AND (lower(asignatura) ~* 'c[aá]m[ií]l[aá]' OR       cod_asignatura ~* 'c[aá]m[ií]l[aá]' OR       text(c.id) ~* 'c[aá]m[ií]l[aá]' OR       lower(profesor) ~* 'c[aá]m[ií]l[aá]')  AND (lower(asignatura) ~* '[aá]ndr[eé][aá]' OR       cod_asignatura ~* '[aá]ndr[eé][aá]' OR       text(c.id) ~* '[aá]ndr[eé][aá]' OR       lower(profesor) ~* '[aá]ndr[eé][aá]')  AND (lower(asignatura) ~* 'p[eé]ñ[aá]l[oó]z[aá]' OR       cod_asignatura ~* 'p[eé]ñ[aá]l[oó]z[aá]' OR       text(c.id) ~* 'p[eé]ñ[aá]l[oó]z[aá]' OR       lower(profesor) ~* 'p[eé]ñ[aá]l[oó]z[aá]')  AND (lower(asignatura) ~* 'c[aá]c[eé]r[eé]s' OR       cod_asignatura ~* 'c[aá]c[eé]r[eé]s' OR       text(c.id) ~* 'c[aá]c[eé]r[eé]s' OR       lower(profesor) ~* 'c[aá]c[eé]r[eé]s')  AND id_carrera IN (94,97,104,110,98,105,102,91,15,103,126,4,12)  
                    ORDER BY c.ano DESC, c.semestre DESC, vc.carrera, c.seccion, cod_asignatura ) to stdout WITH CSV HEADER