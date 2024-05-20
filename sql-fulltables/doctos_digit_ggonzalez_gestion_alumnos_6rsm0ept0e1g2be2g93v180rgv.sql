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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'l[uú][ií]s' OR  a.rut ~* 'l[uú][ií]s' OR  lower(a.email) ~* 'l[uú][ií]s' OR  text(a.id) ~* 'l[uú][ií]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* '' OR  a.rut ~* '' OR  lower(a.email) ~* '' OR  text(a.id) ~* '' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'g[aá]rr[ií]d[oó]' OR  a.rut ~* 'g[aá]rr[ií]d[oó]' OR  lower(a.email) ~* 'g[aá]rr[ií]d[oó]' OR  text(a.id) ~* 'g[aá]rr[ií]d[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[aá]v[eé]z' OR  a.rut ~* 'p[aá]v[eé]z' OR  lower(a.email) ~* 'p[aá]v[eé]z' OR  text(a.id) ~* 'p[aá]v[eé]z' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER