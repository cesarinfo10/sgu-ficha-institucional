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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[oó]v[aá]ll[eé]' OR  a.rut ~* '[oó]v[aá]ll[eé]' OR  lower(a.email) ~* '[oó]v[aá]ll[eé]' OR  text(a.id) ~* '[oó]v[aá]ll[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '' OR  a.rut ~* '' OR  lower(a.email) ~* '' OR  text(a.id) ~* '' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[eé]n[eé]g[aá]s' OR  a.rut ~* 'v[eé]n[eé]g[aá]s' OR  lower(a.email) ~* 'v[eé]n[eé]g[aá]s' OR  text(a.id) ~* 'v[eé]n[eé]g[aá]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'd[oó]m[ií]ng[oó]' OR  a.rut ~* 'd[oó]m[ií]ng[oó]' OR  lower(a.email) ~* 'd[oó]m[ií]ng[oó]' OR  text(a.id) ~* 'd[oó]m[ií]ng[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]nt[oó]n[ií][oó]' OR  a.rut ~* '[aá]nt[oó]n[ií][oó]' OR  lower(a.email) ~* '[aá]nt[oó]n[ií][oó]' OR  text(a.id) ~* '[aá]nt[oó]n[ií][oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER