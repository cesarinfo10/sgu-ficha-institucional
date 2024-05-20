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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]r[oó]l[ií]n[aá]' OR  a.rut ~* 'c[aá]r[oó]l[ií]n[aá]' OR  lower(a.email) ~* 'c[aá]r[oó]l[ií]n[aá]' OR  text(a.id) ~* 'c[aá]r[oó]l[ií]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'l[ií]ss[eé]tt[eé]' OR  a.rut ~* 'l[ií]ss[eé]tt[eé]' OR  lower(a.email) ~* 'l[ií]ss[eé]tt[eé]' OR  text(a.id) ~* 'l[ií]ss[eé]tt[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'g[oó]nz[aá]l[eé]z' OR  a.rut ~* 'g[oó]nz[aá]l[eé]z' OR  lower(a.email) ~* 'g[oó]nz[aá]l[eé]z' OR  text(a.id) ~* 'g[oó]nz[aá]l[eé]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'g[oó]m[eé]z' OR  a.rut ~* 'g[oó]m[eé]z' OR  lower(a.email) ~* 'g[oó]m[eé]z' OR  text(a.id) ~* 'g[oó]m[eé]z' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER