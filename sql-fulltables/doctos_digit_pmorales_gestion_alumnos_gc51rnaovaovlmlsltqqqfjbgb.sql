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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'p[aá]r[eé]d[eé]s' OR  a.rut ~* 'p[aá]r[eé]d[eé]s' OR  lower(a.email) ~* 'p[aá]r[eé]d[eé]s' OR  text(a.id) ~* 'p[aá]r[eé]d[eé]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'd[eé]l' OR  a.rut ~* 'd[eé]l' OR  lower(a.email) ~* 'd[eé]l' OR  text(a.id) ~* 'd[eé]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[ií]n[oó]' OR  a.rut ~* 'p[ií]n[oó]' OR  lower(a.email) ~* 'p[ií]n[oó]' OR  text(a.id) ~* 'p[ií]n[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[eé]t[eé]r' OR  a.rut ~* 'p[eé]t[eé]r' OR  lower(a.email) ~* 'p[eé]t[eé]r' OR  text(a.id) ~* 'p[eé]t[eé]r' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]ndr[eé]s' OR  a.rut ~* '[aá]ndr[eé]s' OR  lower(a.email) ~* '[aá]ndr[eé]s' OR  text(a.id) ~* '[aá]ndr[eé]s' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER