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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'wl[aá]d[ií]m[ií]r' OR  a.rut ~* 'wl[aá]d[ií]m[ií]r' OR  lower(a.email) ~* 'wl[aá]d[ií]m[ií]r' OR  text(a.id) ~* 'wl[aá]d[ií]m[ií]r' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[oó]nc[eé]' OR  a.rut ~* 'p[oó]nc[eé]' OR  lower(a.email) ~* 'p[oó]nc[eé]' OR  text(a.id) ~* 'p[oó]nc[eé]' )  AND a.carrera_actual IN (61,68,21)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER