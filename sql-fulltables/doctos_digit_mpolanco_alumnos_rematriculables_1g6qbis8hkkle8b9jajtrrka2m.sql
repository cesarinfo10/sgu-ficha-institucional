COPY (SELECT a.rut,apellidos,nombres,ae.nombre as estado,c.alias||'-'||a.jornada as carrera,
                         ad.nombre AS admision,semestre_cohorte||'-'||cohorte as cohorte,a.nacionalidad,
                         ddt.nombre AS tipo_docto,dd.fecha
                  FROM doctos_digitalizados dd 
                  LEFT JOIN doctos_digital_tipos ddt on ddt.id=dd.id_tipo 
                  LEFT JOIN alumnos a                using(rut) 
                  LEFT JOIN admision_tipo ad         on ad.id=a.admision
                  LEFT JOIN al_estados ae            on ae.id=a.estado
                  LEFT JOIN mallas     AS m ON m.id=a.malla_actual
                  LEFT JOIN carreras c               on c.id=carrera_actual 
                  LEFT JOIN usuarios u               on u.id=dd.id_usuario
                  WHERE true  AND (a.semestre_cohorte = 1 ) AND (a.cohorte = '2022' ) AND (a.estado = '1') AND (a.moroso_financiero = 't') AND (a.id NOT IN (SELECT id_alumno FROM matriculas WHERE ano=2023 AND semestre=1))  AND ((CASE WHEN (SELECT count(id_curso) FROM cargas_academicas WHERE id_alumno=a.id AND id_curso IN (SELECT id FROM cursos WHERE semestre=2 AND ano=2022)) > 0 THEN ((SELECT count(id_curso) FROM cargas_academicas WHERE id_alumno=a.id AND id_estado=1 AND id_curso IN (SELECT id FROM cursos WHERE semestre=2 AND ano=2022))::real/(SELECT count(id_curso) FROM cargas_academicas WHERE id_alumno=a.id AND id_curso IN (SELECT id FROM cursos WHERE semestre=2 AND ano=2022))::real*100)::numeric(4,1) ELSE 0 END) BETWEEN 1 AND 100)  AND a.carrera_actual IN (95,100,19,2,69,1)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER