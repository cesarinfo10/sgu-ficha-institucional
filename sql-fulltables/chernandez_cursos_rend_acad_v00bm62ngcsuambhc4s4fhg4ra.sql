COPY (SELECT c.id,cod_asignatura||'-'||c.seccion AS cod_asignatura,asignatura,c.semestre||'-'||c.ano AS periodo,
                      upper(u.apellido) as apellidos,initcap(u.nombre) AS nombres,vc.carrera,
                      coalesce(sesion1,'')||' '||coalesce(sesion2,'')||' '||coalesce(sesion3,'') as horario,
                      cantidad_alumnos(c.id) AS cant_alumnos,cant_alumnos_asist(c.id) AS al_asist,c.cerrado,c.tipo_clase AS modalidad,
                      (SELECT CASE WHEN count(id_alumno)>0 THEN ((count(solemne1)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion))) AS s1, (SELECT CASE WHEN count(id_alumno)>0 THEN ((count(nota_catedra)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion))) AS nc, (SELECT CASE WHEN count(id_alumno)>0 THEN ((count(solemne2)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion))) AS s2, (SELECT CASE WHEN count(id_alumno)>0 THEN ((count(id_estado)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) and id_estado NOT IN (6,10)) AS nf, (SELECT CASE WHEN count(id)=count(materia) AND count(id)>0 THEN 'SI' ELSE 'NO' END AS cal FROM calendarizaciones WHERE id_curso=c.id) AS cal,
                      (SELECT coalesce(avg(solemne1)::numeric(2,1)::text,'---') FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND solemne1>=1) AS prom_s1, (SELECT avg(solemne2)::numeric(2,1) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND solemne2>=1) AS prom_s2, (SELECT avg(nota_catedra)::numeric(2,1) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND nota_catedra>=1) AS prom_nc, (SELECT avg(nota_final)::numeric(2,1) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND (solemne1>=1 OR solemne2>=1)) AS prom_nf,(SELECT stddev(nota_final)::numeric(5,4) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND (solemne1>=1 OR solemne2>=1)) AS desvest_nf,
                      (SELECT count(id_alumno) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND solemne1>=4) AS aprob_s1, (SELECT count(id_alumno) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND solemne2>=4) AS aprob_s2, (SELECT count(id_alumno) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND nota_catedra>=4) AS aprob_nc, (SELECT count(id_alumno) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND nota_final>=4) AS aprob_nf,
                      (SELECT count(id_alumno) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND solemne1 between 1 and 3.9) AS reprob_s1, (SELECT count(id_alumno) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND solemne2 between 1 and 3.9) AS reprob_s2, (SELECT count(id_alumno) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND nota_catedra between 1 and 3.9) AS reprob_nc, (SELECT count(id_alumno) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND nota_final between 1 and 3.9) AS reprob_nf,
                      (SELECT count(id_alumno) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND solemne1=-1 AND solemne2=-1 AND nota_catedra<=1 AND id_estado=2) as cant_nsp,
                      (SELECT char_comma_sum(vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura) AS asig_fusionadas FROM vista_gestion_cursos AS vc LEFT JOIN cursos AS ca USING (id) WHERE ca.id_fusion = c.id) AS asig_fusionadas
               FROM vista_gestion_cursos AS vc
               LEFT JOIN cursos AS c USING (id)
               LEFT JOIN usuarios AS u ON u.id=vc.id_profesor
               LEFT JOIN carreras AS car ON car.id=vc.id_carrera
               WHERE true  AND c.ano=2023  AND c.semestre=1  AND ('PRE' = ANY (regimenes))  AND array[94,97,104,110,98,105,102,91,15,103,126,4,157] && (ids_carreras)  AND c.id_fusion IS NULL
               ORDER BY c.ano DESC, c.semestre DESC, cod_asignatura ) to stdout WITH CSV HEADER