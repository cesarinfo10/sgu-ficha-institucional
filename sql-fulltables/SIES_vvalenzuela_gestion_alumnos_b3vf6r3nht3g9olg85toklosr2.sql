COPY (SELECT 'R' AS tipo_docto_ident,
                       split_part(a.rut,'-',1) AS rut, split_part(a.rut,'-',2) AS dv,
                       translate(upper(split_part(trim(a.apellidos),' ',1)),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS ape_pat,
                       translate(upper(split_part(trim(a.apellidos),' ',2)),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS ape_mat,
                       translate(upper(a.nombres),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS nombre,
                       CASE a.genero WHEN 'f' THEN 'M' WHEN 'm' THEN 'H' END AS genero,
                       to_char(a.fec_nac,'DD/MM/YYYY') AS fec_nac,
                       p.cod_sies AS nacionalidad,
                       p.cod_sies AS pais_est_sec,1 as cod_sede,c.cod_sies_matunif AS cod_carrera_matunif,
                       c.modalidad,
                       CASE WHEN c.regimen='PRE' AND a.jornada='D' THEN 1
                            WHEN c.regimen='PRE' AND a.jornada='V' THEN 2
                            WHEN c.regimen IN ('POST-GD','POST-TD','DIP-D') AND a.jornada='V' THEN 4
                       END AS jornada,1 AS version,
                       CASE WHEN a.admision IN (1,10) THEN 1
							              WHEN a.admision IN (2,20) THEN 4
							              WHEN a.admision = 3 THEN 10
                       END AS forma_ingreso,a.cohorte,a.semestre_cohorte,
                       coalesce((SELECT min(ano) AS ano_ing_orig FROM cargas_academicas AS ca LEFT JOIN convalidaciones AS c ON c.id=ca.id_convalida WHERE ca.id_alumno=a.id),a.cohorte) AS ano_ing_origen,a.semestre_cohorte AS sem_ing_origen,
                       (SELECT count(ca.id) FROM cargas_academicas AS ca WHERE id_curso IN (SELECT id FROM cursos WHERE ano=2023-1) AND id_alumno=a.id AND NOT (solemne1=-1 AND solemne2=-1)) AS asig_insc_ano_ant,(SELECT count(ca.id) FROM cargas_academicas AS ca WHERE id_curso IN (SELECT id FROM cursos WHERE ano=2023-1) AND id_alumno=a.id AND id_estado=1) AS asig_aprob_ano_ant,
                       (SELECT (avg(ca.nota_final)*100)::int2 FROM cargas_academicas AS ca WHERE id_curso IN (SELECT id FROM cursos WHERE ano=2023-1 AND semestre=1) AND id_alumno=a.id AND NOT (solemne1=-1 AND solemne2=-1)) AS prom_ano_ant_1sem,(SELECT (avg(ca.nota_final)*100)::int2 FROM cargas_academicas AS ca WHERE id_curso IN (SELECT id FROM cursos WHERE ano=2023-1 AND semestre=2) AND id_alumno=a.id AND NOT (solemne1=-1 AND solemne2=-1)) AS prom_ano_ant_2sem,
                       (SELECT count(ca.id) FROM cargas_academicas AS ca WHERE id_curso NOT IN (SELECT id FROM cursos WHERE ano=2023) AND id_alumno=a.id AND NOT (solemne1=-1 AND solemne2=-1)) AS asig_hist_insc,(SELECT count(ca.id) FROM cargas_academicas AS ca WHERE id_curso NOT IN (SELECT id FROM cursos WHERE ano=2023) AND id_alumno=a.id AND id_estado=1) AS asig_hist_aprob,
                       CASE WHEN (SELECT max(nivel) 
                           FROM vista_alumnos_cursos AS vac 
                           LEFT JOIN detalle_mallas dm ON (dm.id_malla=a.malla_actual AND dm.id_prog_asig=vac.id_prog_asig) 
                           WHERE vac.ano=2023 AND vac.id_alumno=a.id) IS NULL THEN (SELECT max(nivel) 
                           FROM vista_alumnos_cursos AS vac 
                           LEFT JOIN detalle_mallas dm ON (dm.id_malla=a.malla_actual AND dm.id_prog_asig=vac.id_prog_asig) 
                           WHERE vac.id_estado=1 AND vac.id_alumno=a.id) ELSE (SELECT max(nivel) 
                           FROM vista_alumnos_cursos AS vac 
                           LEFT JOIN detalle_mallas dm ON (dm.id_malla=a.malla_actual AND dm.id_prog_asig=vac.id_prog_asig) 
                           WHERE vac.ano=2023 AND vac.id_alumno=a.id) END AS nivel_academico,
                       0 AS sit_socioeco_fon_sol,
                       ((2023-a.cohorte)+1)*2+(CASE WHEN 1<=a.semestre_cohorte THEN -1 ELSE 0 END)-(SELECT count(periodo) AS semestre_presente
                    FROM (SELECT id_alumno,ano||'-'||semestre as periodo 
                          FROM vista_alumnos_cursos 
                          WHERE id_alumno=a.id AND semestre>0 
                          GROUP BY id_alumno,periodo) AS foo 
                    GROUP BY id_alumno) AS semestres_susp,
                       1 AS vigencia,c.nombre AS nombre_carrera,a.jornada  
                FROM alumnos AS a
                LEFT JOIN pap		        ON pap.id=a.id_pap
                LEFT JOIN carreras   AS c   ON c.id=a.carrera_actual
                LEFT JOIN admision_tipo AS adm ON adm.id=a.admision
                LEFT JOIN pais       AS p   ON p.localizacion=a.nacionalidad
                LEFT JOIN mallas     AS vm  ON vm.id=a.malla_actual
                LEFT JOIN al_estados AS ae  ON ae.id=a.estado
                
                WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'l[uú][ií]s' OR  a.rut ~* 'l[uú][ií]s' OR  lower(a.email) ~* 'l[uú][ií]s' OR  text(a.id) ~* 'l[uú][ií]s' )  AND a.carrera_actual IN (108,96,25,109,37,70,135,112,17)  
                ORDER BY c.alias,a.jornada,a.apellidos,a.nombres) to stdout WITH CSV HEADER