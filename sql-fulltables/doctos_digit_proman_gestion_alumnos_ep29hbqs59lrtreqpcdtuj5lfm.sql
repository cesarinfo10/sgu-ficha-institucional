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
                  WHERE true  AND (a.id IN (SELECT id_alumno FROM matriculas WHERE ano=2022 AND semestre=2))  AND a.carrera_actual IN (52,99,92,49,111,89,93,107,86,87,72,84,85,44,40,46,64,39,45,42,47,53,43,51,71,73,74,41,63,65,66,90,75,113,124,121,122,123,129,130,134,133,132,127,131)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER