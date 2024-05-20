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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'd[ií][eé]g[oó]' OR  a.rut ~* 'd[ií][eé]g[oó]' OR  lower(a.email) ~* 'd[ií][eé]g[oó]' OR  text(a.id) ~* 'd[ií][eé]g[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[oó]dr[ií]g[oó]' OR  a.rut ~* 'r[oó]dr[ií]g[oó]' OR  lower(a.email) ~* 'r[oó]dr[ií]g[oó]' OR  text(a.id) ~* 'r[oó]dr[ií]g[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]st[eé]b[aá]n' OR  a.rut ~* '[eé]st[eé]b[aá]n' OR  lower(a.email) ~* '[eé]st[eé]b[aá]n' OR  text(a.id) ~* '[eé]st[eé]b[aá]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'b[eé]rr[oó][eé]t[aá]' OR  a.rut ~* 'b[eé]rr[oó][eé]t[aá]' OR  lower(a.email) ~* 'b[eé]rr[oó][eé]t[aá]' OR  text(a.id) ~* 'b[eé]rr[oó][eé]t[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[ií]ll[aá]gr[aá]n' OR  a.rut ~* 'v[ií]ll[aá]gr[aá]n' OR  lower(a.email) ~* 'v[ií]ll[aá]gr[aá]n' OR  text(a.id) ~* 'v[ií]ll[aá]gr[aá]n' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER