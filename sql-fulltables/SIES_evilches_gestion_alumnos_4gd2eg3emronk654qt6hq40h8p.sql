COPY (SELECT 'R' AS tipo_docto_ident,
                       split_part(a.rut,'-',1) AS rut, split_part(a.rut,'-',2) AS dv,
                       translate(upper(split_part(trim(a.apellidos),' ',1)),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS ape_pat,
                       translate(upper(split_part(trim(a.apellidos),' ',2)),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS ape_mat,
                       translate(upper(a.nombres),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS nombre,
                       CASE a.genero WHEN 'f' THEN 'M' WHEN 'm' THEN 'H' END AS genero,
                       to_char(a.fec_nac,'DD/MM/YYYY') AS fec_nac,
                       p.cod_sies AS nacionalidad,
                       p.cod_sies AS pais_est_sec,1 as cod_sede,c.cod_sies_matunif AS cod_carrera_matunif,
                       CASE WHEN c.regimen='PRE' AND a.jornada='D' THEN 1
                            WHEN c.regimen='PRE' AND a.jornada='V' THEN 2
                            WHEN c.regimen IN ('POST-GD','POST-TD','DIP-D') AND a.jornada='V' THEN 4
                       END AS jornada,1 AS version,c.modalidad,
                       CASE WHEN a.admision IN (1,10) THEN 1
							WHEN a.admision IN (2,20) THEN 4
							WHEN a.admision = 3 THEN 10
                       END AS forma_ingreso,a.cohorte,a.semestre_cohorte,(coalesce(a.promedio_col,pap.promedio_col,0)*100)::int2 AS nem,
                       coalesce((SELECT min(ano) AS ano_ing_orig FROM cargas_academicas AS ca LEFT JOIN convalidaciones AS c ON c.id=ca.id_convalida WHERE ca.id_alumno=a.id),a.cohorte) AS ano_ing_origen,a.semestre_cohorte AS sem_ing_origen,
                       (SELECT count(ca.id) FROM cargas_academicas AS ca WHERE id_curso IN (SELECT id FROM cursos WHERE ano=2022-1) AND id_alumno=a.id AND NOT (solemne1=-1 AND solemne2=-1)) AS asig_insc_ano_ant,(SELECT count(ca.id) FROM cargas_academicas AS ca WHERE id_curso IN (SELECT id FROM cursos WHERE ano=2022-1) AND id_alumno=a.id AND id_estado=1) AS asig_aprob_ano_ant,
                       (SELECT (avg(ca.nota_final)*100)::int2 FROM cargas_academicas AS ca WHERE id_curso IN (SELECT id FROM cursos WHERE ano=2022-1 AND semestre=1) AND id_alumno=a.id AND NOT (solemne1=-1 AND solemne2=-1)) AS prom_ano_ant_1sem,(SELECT (avg(ca.nota_final)*100)::int2 FROM cargas_academicas AS ca WHERE id_curso IN (SELECT id FROM cursos WHERE ano=2022-1 AND semestre=2) AND id_alumno=a.id AND NOT (solemne1=-1 AND solemne2=-1)) AS prom_ano_ant_2sem,
                       (SELECT count(ca.id) FROM cargas_academicas AS ca WHERE id_curso NOT IN (SELECT id FROM cursos WHERE ano=2022) AND id_alumno=a.id AND NOT (solemne1=-1 AND solemne2=-1)) AS asig_hist_insc,(SELECT count(ca.id) FROM cargas_academicas AS ca WHERE id_curso NOT IN (SELECT id FROM cursos WHERE ano=2022) AND id_alumno=a.id AND id_estado=1) AS asig_hist_aprob,
                       CASE WHEN (SELECT max(nivel) 
                           FROM vista_alumnos_cursos AS vac 
                           LEFT JOIN detalle_mallas dm ON (dm.id_malla=a.malla_actual AND dm.id_prog_asig=vac.id_prog_asig) 
                           WHERE vac.ano=2022 AND vac.id_alumno=a.id) IS NULL THEN (SELECT max(nivel) 
                           FROM vista_alumnos_cursos AS vac 
                           LEFT JOIN detalle_mallas dm ON (dm.id_malla=a.malla_actual AND dm.id_prog_asig=vac.id_prog_asig) 
                           WHERE vac.id_estado=1 AND vac.id_alumno=a.id) ELSE (SELECT max(nivel) 
                           FROM vista_alumnos_cursos AS vac 
                           LEFT JOIN detalle_mallas dm ON (dm.id_malla=a.malla_actual AND dm.id_prog_asig=vac.id_prog_asig) 
                           WHERE vac.ano=2022 AND vac.id_alumno=a.id) END AS nivel_academico,
                       0 AS ano_egreso,0 as sem_egreso,0 AS sit_socioeco_fon_sol,0 AS opta_beca_art,0 AS extranjeros_bjgme,
                       ((2022-a.cohorte)+1)*2+(CASE WHEN 2<=a.semestre_cohorte THEN -1 ELSE 0 END)-(SELECT count(periodo) AS semestre_presente
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
                
                WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '10358880-4|10456663-4|10719873-3|10965984-3|12279063-0|12685143-k|12909483-4|13028882-0|13277146-4|13298426-3|13491711-3|13910427-7|14164264-2|14601509-3|15057156-1|15111325-7|15312099-4|15454059-8|15470168-0|15541942-3|15609445-5|15812440-8|15900232-2|16192283-8|16616781-7|16986826-3|17002864-3|17052780-1|17078990-3|17122345-8|17389818-5|17763666-5|17834208-8|18072287-4|18275906-6|18667460-k|18724138-3|18732442-4|19026997-3|19344929-8|19784950-9|19955307-0|20034951-2|20057511-3|20142214-0|24281143-7|25382293-7|25434041-3|25473556-6|25978450-6|3824365-9|6978614-6|7348629-7|7626724-3|7935457-0|8596378-3|9910862-2' OR  a.rut ~* '10358880-4|10456663-4|10719873-3|10965984-3|12279063-0|12685143-k|12909483-4|13028882-0|13277146-4|13298426-3|13491711-3|13910427-7|14164264-2|14601509-3|15057156-1|15111325-7|15312099-4|15454059-8|15470168-0|15541942-3|15609445-5|15812440-8|15900232-2|16192283-8|16616781-7|16986826-3|17002864-3|17052780-1|17078990-3|17122345-8|17389818-5|17763666-5|17834208-8|18072287-4|18275906-6|18667460-k|18724138-3|18732442-4|19026997-3|19344929-8|19784950-9|19955307-0|20034951-2|20057511-3|20142214-0|24281143-7|25382293-7|25434041-3|25473556-6|25978450-6|3824365-9|6978614-6|7348629-7|7626724-3|7935457-0|8596378-3|9910862-2' OR  lower(a.email) ~* '10358880-4|10456663-4|10719873-3|10965984-3|12279063-0|12685143-k|12909483-4|13028882-0|13277146-4|13298426-3|13491711-3|13910427-7|14164264-2|14601509-3|15057156-1|15111325-7|15312099-4|15454059-8|15470168-0|15541942-3|15609445-5|15812440-8|15900232-2|16192283-8|16616781-7|16986826-3|17002864-3|17052780-1|17078990-3|17122345-8|17389818-5|17763666-5|17834208-8|18072287-4|18275906-6|18667460-k|18724138-3|18732442-4|19026997-3|19344929-8|19784950-9|19955307-0|20034951-2|20057511-3|20142214-0|24281143-7|25382293-7|25434041-3|25473556-6|25978450-6|3824365-9|6978614-6|7348629-7|7626724-3|7935457-0|8596378-3|9910862-2' OR  text(a.id) ~* '10358880-4|10456663-4|10719873-3|10965984-3|12279063-0|12685143-k|12909483-4|13028882-0|13277146-4|13298426-3|13491711-3|13910427-7|14164264-2|14601509-3|15057156-1|15111325-7|15312099-4|15454059-8|15470168-0|15541942-3|15609445-5|15812440-8|15900232-2|16192283-8|16616781-7|16986826-3|17002864-3|17052780-1|17078990-3|17122345-8|17389818-5|17763666-5|17834208-8|18072287-4|18275906-6|18667460-k|18724138-3|18732442-4|19026997-3|19344929-8|19784950-9|19955307-0|20034951-2|20057511-3|20142214-0|24281143-7|25382293-7|25434041-3|25473556-6|25978450-6|3824365-9|6978614-6|7348629-7|7626724-3|7935457-0|8596378-3|9910862-2' )  
                ORDER BY c.alias,a.jornada,a.apellidos,a.nombres) to stdout WITH CSV HEADER