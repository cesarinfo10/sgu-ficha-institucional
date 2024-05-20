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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'h[eé]ct[oó]r' OR  a.rut ~* 'h[eé]ct[oó]r' OR  lower(a.email) ~* 'h[eé]ct[oó]r' OR  text(a.id) ~* 'h[eé]ct[oó]r' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'g[aá]m[ií]n' OR  a.rut ~* 'g[aá]m[ií]n' OR  lower(a.email) ~* 'g[aá]m[ií]n' OR  text(a.id) ~* 'g[aá]m[ií]n' )  AND a.carrera_actual IN (95,100,2,69,1,19)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER