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
                  WHERE true  AND (a.cohorte = '2023' ) AND (a.estado = '1') AND (a.admision IN ('2')) AND (c.regimen = 'PRE')  AND (CASE WHEN (SELECT count(id) FROM cargas_academicas WHERE id_alumno=a.id AND convalidado AND id_pa IN (SELECT id_prog_asig FROM detalle_mallas WHERE id_malla=a.malla_actual)) > 0 THEN round((SELECT count(id) FROM cargas_academicas WHERE id_alumno=a.id AND convalidado AND id_pa IN (SELECT id_prog_asig FROM detalle_mallas WHERE id_malla=a.malla_actual))::numeric*100/(coalesce(m.cant_asig_oblig,0)+coalesce(m.cant_asig_elect,0)+coalesce(m.cant_asig_efp,0)),0) ELSE 0 END) BETWEEN 1 AND 60AND (a.id IN (SELECT id_alumno FROM matriculas WHERE ano=2023 AND semestre=1))  AND NOT dd.eliminado) TO stdout WITH CSV HEADER