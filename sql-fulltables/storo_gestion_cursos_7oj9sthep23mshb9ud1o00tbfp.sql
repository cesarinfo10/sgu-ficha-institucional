COPY (SELECT c.diferencias_sgu_moodle as diferencias_moodle, 
                      diferencias_sgu_moodle_fec as diferencias_moodle_fec, 
                      c.id,cod_asignatura||'-'||c.seccion AS cod_asignatura, asignatura,c.semestre||'-'||c.ano AS periodo,c.id_profesor,
                      upper(u.apellido) as apellidos,initcap(u.nombre) AS nombres,u.nombre_usuario||'@profe.umc.cl' AS email_gsuite,vc.carrera,car.regimen,sesion1,sesion2,sesion3,
                      cantidad_alumnos(c.id) AS cant_alumnos,cant_alumnos_asist(c.id) AS al_asist,c.cerrado,
                      (SELECT CASE WHEN count(id_alumno)>0 THEN ((count(c1)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas AS ca LEFT JOIN calificaciones_parciales AS cap ON cap.id_ca=ca.id WHERE id_curso IN (SELECT id FROM cursos WHERE c.id IN (id,id_fusion))) AS c1, (SELECT CASE WHEN count(id_alumno)>0 THEN ((count(solemne1)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE c.id IN (id,id_fusion))) AS s1, (SELECT CASE WHEN count(id_alumno)>0 THEN ((count(nota_catedra)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE c.id IN (id,id_fusion))) AS nc, (SELECT CASE WHEN count(id_alumno)>0 THEN ((count(solemne2)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE c.id IN (id,id_fusion))) AS s2, (SELECT CASE WHEN count(id_alumno)>0 THEN ((count(id_estado)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE c.id IN (id,id_fusion)) and id_estado NOT IN (6,10)) AS nf, (SELECT CASE WHEN count(id)=count(materia) AND count(id)>0 THEN 'SI' WHEN c.seccion=9 THEN 'NC' ELSE 'NO' END AS cal FROM calendarizaciones WHERE id_curso=c.id) AS cal,
                      to_char(c.fec_ini,'TMDay DD-tmMon-YYYY') AS fec_ini,to_char(c.fec_fin,'TMDay DD-tmMon-YYYY') AS fec_fin,c.seccion,
                      (SELECT count(id_sesion) FROM ca_asistencia caa LEFT JOIN cargas_academicas ca on ca.id=caa.id_ca WHERE id_sesion IN (SELECT cs.id FROM cursos_sesiones AS cs WHERE id_curso=c.id AND fecha BETWEEN '2023-08-07'::date AND '2023-12-17'::date) AND (id_estado IS NULL OR id_estado NOT IN (6,10,11,12))  AND presente) AS asist_presentes,(SELECT count(id_sesion) FROM ca_asistencia caa LEFT JOIN cargas_academicas ca on ca.id=caa.id_ca WHERE id_sesion IN (SELECT cs.id FROM cursos_sesiones AS cs WHERE id_curso=c.id AND fecha BETWEEN '2023-08-07'::date AND '2023-12-17'::date) AND (id_estado IS NULL OR id_estado NOT IN (6,10,11,12))  AND NOT presente) AS asist_ausentes,
                      cantidad_sesiones_curso(c.id,'2023-08-07','2023-12-17') AS cant_sesiones,
                      CASE WHEN cant_alumnos_asist(c.id) > 0 AND cantidad_sesiones_curso(c.id,'2023-08-07','2023-12-17') > 0 AND (SELECT count(id_sesion) FROM ca_asistencia caa LEFT JOIN cargas_academicas ca on ca.id=caa.id_ca WHERE id_sesion IN (SELECT cs.id FROM cursos_sesiones AS cs WHERE id_curso=c.id AND fecha BETWEEN '2023-08-07'::date AND '2023-12-17'::date) AND (id_estado IS NULL OR id_estado NOT IN (6,10,11,12)) ) > 0
                           THEN round(((SELECT count(id_sesion) FROM ca_asistencia caa LEFT JOIN cargas_academicas ca on ca.id=caa.id_ca WHERE id_sesion IN (SELECT cs.id FROM cursos_sesiones AS cs WHERE id_curso=c.id AND fecha BETWEEN '2023-08-07'::date AND '2023-12-17'::date) AND (id_estado IS NULL OR id_estado NOT IN (6,10,11,12))  AND presente)::numeric*100/(SELECT count(id_sesion) FROM ca_asistencia caa LEFT JOIN cargas_academicas ca on ca.id=caa.id_ca WHERE id_sesion IN (SELECT cs.id FROM cursos_sesiones AS cs WHERE id_curso=c.id AND fecha BETWEEN '2023-08-07'::date AND '2023-12-17'::date) AND (id_estado IS NULL OR id_estado NOT IN (6,10,11,12)) ))) ELSE 0 
                      END AS tasa_presentes,
                      CASE WHEN cant_alumnos_asist(c.id) > 0 AND cantidad_sesiones_curso(c.id,'2023-08-07','2023-12-17') > 0 AND (SELECT count(id_sesion) FROM ca_asistencia caa LEFT JOIN cargas_academicas ca on ca.id=caa.id_ca WHERE id_sesion IN (SELECT cs.id FROM cursos_sesiones AS cs WHERE id_curso=c.id AND fecha BETWEEN '2023-08-07'::date AND '2023-12-17'::date) AND (id_estado IS NULL OR id_estado NOT IN (6,10,11,12)) ) > 0
                           THEN round(((SELECT count(id_sesion) FROM ca_asistencia caa LEFT JOIN cargas_academicas ca on ca.id=caa.id_ca WHERE id_sesion IN (SELECT cs.id FROM cursos_sesiones AS cs WHERE id_curso=c.id AND fecha BETWEEN '2023-08-07'::date AND '2023-12-17'::date) AND (id_estado IS NULL OR id_estado NOT IN (6,10,11,12))  AND NOT presente)::numeric*100/(SELECT count(id_sesion) FROM ca_asistencia caa LEFT JOIN cargas_academicas ca on ca.id=caa.id_ca WHERE id_sesion IN (SELECT cs.id FROM cursos_sesiones AS cs WHERE id_curso=c.id AND fecha BETWEEN '2023-08-07'::date AND '2023-12-17'::date) AND (id_estado IS NULL OR id_estado NOT IN (6,10,11,12)) ))) ELSE 0 
                      END AS tasa_ausentes,c.cod_google_classroom,c.tipo_clase,(SELECT count(id) FROM cargas_academicas WHERE id_curso=vc.id AND asistencia='Presencial') AS cant_presenciales,
                      (SELECT char_comma_sum(vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura) AS asig_fusionadas FROM vista_gestion_cursos AS vc LEFT JOIN cursos AS ca USING (id) WHERE ca.id_fusion = c.id) AS asig_fusionadas
               FROM vista_gestion_cursos AS vc
               LEFT JOIN cursos AS c USING (id)
               LEFT JOIN carreras AS car ON car.id=vc.id_carrera
               LEFT JOIN usuarios AS u ON u.id=vc.id_profesor
               WHERE id_fusion IS NULL  AND (lower(asignatura) ~* 'r[oó]b[eé]rt[oó]' OR       cod_asignatura ~* 'r[oó]b[eé]rt[oó]' OR       text(c.id) ~* 'r[oó]b[eé]rt[oó]' OR       lower(profesor) ~* 'r[oó]b[eé]rt[oó]')  AND (lower(asignatura) ~* '[aá]l[eé]x[ií]' OR       cod_asignatura ~* '[aá]l[eé]x[ií]' OR       text(c.id) ~* '[aá]l[eé]x[ií]' OR       lower(profesor) ~* '[aá]l[eé]x[ií]')  AND (lower(asignatura) ~* 'd[ií][aá]z' OR       cod_asignatura ~* 'd[ií][aá]z' OR       text(c.id) ~* 'd[ií][aá]z' OR       lower(profesor) ~* 'd[ií][aá]z')  AND (lower(asignatura) ~* 'q[uú][ií]nt[aá]n[ií]ll[aá]' OR       cod_asignatura ~* 'q[uú][ií]nt[aá]n[ií]ll[aá]' OR       text(c.id) ~* 'q[uú][ií]nt[aá]n[ií]ll[aá]' OR       lower(profesor) ~* 'q[uú][ií]nt[aá]n[ií]ll[aá]')  AND array[95,100,2,69,1,19,12] && (ids_carreras)  
               ORDER BY c.ano DESC, c.semestre DESC, cod_asignatura,c.seccion ) to stdout WITH CSV HEADER