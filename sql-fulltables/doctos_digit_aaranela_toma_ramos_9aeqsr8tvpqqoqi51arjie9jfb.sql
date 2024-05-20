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
                  WHERE (SELECT count(id_alumno) FROM inscripciones_cursos WHERE id_alumno=a.id)=0  AND (a.semestre_cohorte = 2 ) AND (a.cohorte = '2022' ) AND (a.estado = '1') AND (a.admision IN ('20')) AND (c.regimen = 'PRE')  AND (SELECT count(id) FROM cargas_academicas WHERE id_alumno=a.id AND convalidado AND id_pa IN (SELECT id_prog_asig FROM detalle_mallas WHERE id_malla=a.malla_actual))=0  AND NOT dd.eliminado) TO stdout WITH CSV HEADER