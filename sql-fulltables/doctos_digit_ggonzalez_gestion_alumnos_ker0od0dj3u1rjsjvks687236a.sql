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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'p[aá]bl[aá]' OR  a.rut ~* 'p[aá]bl[aá]' OR  lower(a.email) ~* 'p[aá]bl[aá]' OR  text(a.id) ~* 'p[aá]bl[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[oó]fr[eé]' OR  a.rut ~* 'c[oó]fr[eé]' OR  lower(a.email) ~* 'c[oó]fr[eé]' OR  text(a.id) ~* 'c[oó]fr[eé]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER