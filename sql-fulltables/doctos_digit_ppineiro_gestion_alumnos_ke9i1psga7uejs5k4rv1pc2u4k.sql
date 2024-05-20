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
                  WHERE true  AND ((a.cohorte = 2018 AND a.semestre_cohorte = 2 AND a.mes_cohorte = 8)      OR (a.cohorte_reinc = 2018 AND a.semestre_cohorte_reinc = 2 AND a.mes_cohorte_reinc = 8)) AND (a.cohorte = '2018' OR a.cohorte_reinc = '2018') AND (a.estado = '53') AND (c.regimen = 'POST-TD')  AND NOT dd.eliminado) TO stdout WITH CSV HEADER