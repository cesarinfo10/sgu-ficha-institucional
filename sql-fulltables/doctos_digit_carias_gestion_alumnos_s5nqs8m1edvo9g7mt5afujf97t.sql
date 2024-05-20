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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'v[ií]ll[aá]rr[oó][eé]l' OR  a.rut ~* 'v[ií]ll[aá]rr[oó][eé]l' OR  lower(a.email) ~* 'v[ií]ll[aá]rr[oó][eé]l' OR  text(a.id) ~* 'v[ií]ll[aá]rr[oó][eé]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[oó]y[aá]rc[eé]' OR  a.rut ~* '[oó]y[aá]rc[eé]' OR  lower(a.email) ~* '[oó]y[aá]rc[eé]' OR  text(a.id) ~* '[oó]y[aá]rc[eé]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER