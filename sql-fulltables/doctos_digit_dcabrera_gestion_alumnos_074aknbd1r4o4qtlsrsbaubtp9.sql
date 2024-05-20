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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'n[ií]c[oó]l[eé]' OR  a.rut ~* 'n[ií]c[oó]l[eé]' OR  lower(a.email) ~* 'n[ií]c[oó]l[eé]' OR  text(a.id) ~* 'n[ií]c[oó]l[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]l[aá]rc[oó]n' OR  a.rut ~* '[aá]l[aá]rc[oó]n' OR  lower(a.email) ~* '[aá]l[aá]rc[oó]n' OR  text(a.id) ~* '[aá]l[aá]rc[oó]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[ií]d[aá]l' OR  a.rut ~* 'v[ií]d[aá]l' OR  lower(a.email) ~* 'v[ií]d[aá]l' OR  text(a.id) ~* 'v[ií]d[aá]l' )  AND a.carrera_actual IN (59,57,27,28,33,54,56,31,58,60,55,30,32,29,76,79,82,81,83,78,80,38,115,114,77,125)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER