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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'v[ií]c[eé]nt[eé]' OR  a.rut ~* 'v[ií]c[eé]nt[eé]' OR  lower(a.email) ~* 'v[ií]c[eé]nt[eé]' OR  text(a.id) ~* 'v[ií]c[eé]nt[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]l[oó]ns[oó]' OR  a.rut ~* '[aá]l[oó]ns[oó]' OR  lower(a.email) ~* '[aá]l[oó]ns[oó]' OR  text(a.id) ~* '[aá]l[oó]ns[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[aá]m[ií]r[eé]z' OR  a.rut ~* 'r[aá]m[ií]r[eé]z' OR  lower(a.email) ~* 'r[aá]m[ií]r[eé]z' OR  text(a.id) ~* 'r[aá]m[ií]r[eé]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'l[ií]z[aá]m[aá]' OR  a.rut ~* 'l[ií]z[aá]m[aá]' OR  lower(a.email) ~* 'l[ií]z[aá]m[aá]' OR  text(a.id) ~* 'l[ií]z[aá]m[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER