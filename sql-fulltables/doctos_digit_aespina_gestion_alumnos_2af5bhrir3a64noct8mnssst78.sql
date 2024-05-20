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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'l[eé][aá]ndr[aá]' OR  a.rut ~* 'l[eé][aá]ndr[aá]' OR  lower(a.email) ~* 'l[eé][aá]ndr[aá]' OR  text(a.id) ~* 'l[eé][aá]ndr[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[ií]n[eé]s' OR  a.rut ~* '[ií]n[eé]s' OR  lower(a.email) ~* '[ií]n[eé]s' OR  text(a.id) ~* '[ií]n[eé]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]lm[aá]rc[eé]g[uú][ií]' OR  a.rut ~* '[aá]lm[aá]rc[eé]g[uú][ií]' OR  lower(a.email) ~* '[aá]lm[aá]rc[eé]g[uú][ií]' OR  text(a.id) ~* '[aá]lm[aá]rc[eé]g[uú][ií]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[oó]r[oó]n[aá]d[oó]' OR  a.rut ~* 'c[oó]r[oó]n[aá]d[oó]' OR  lower(a.email) ~* 'c[oó]r[oó]n[aá]d[oó]' OR  text(a.id) ~* 'c[oó]r[oó]n[aá]d[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER