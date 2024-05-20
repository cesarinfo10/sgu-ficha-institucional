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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'w[ií]lm[aá]' OR  a.rut ~* 'w[ií]lm[aá]' OR  lower(a.email) ~* 'w[ií]lm[aá]' OR  text(a.id) ~* 'w[ií]lm[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[eé]m[ií]r' OR  a.rut ~* 's[eé]m[ií]r' OR  lower(a.email) ~* 's[eé]m[ií]r' OR  text(a.id) ~* 's[eé]m[ií]r' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[oó]dr[ií]g[uú][eé]z' OR  a.rut ~* 'r[oó]dr[ií]g[uú][eé]z' OR  lower(a.email) ~* 'r[oó]dr[ií]g[uú][eé]z' OR  text(a.id) ~* 'r[oó]dr[ií]g[uú][eé]z' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER