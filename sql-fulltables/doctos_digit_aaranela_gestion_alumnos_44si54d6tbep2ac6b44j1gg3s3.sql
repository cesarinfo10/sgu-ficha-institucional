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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'v[ií]ll[aá]gr[aá]' OR  a.rut ~* 'v[ií]ll[aá]gr[aá]' OR  lower(a.email) ~* 'v[ií]ll[aá]gr[aá]' OR  text(a.id) ~* 'v[ií]ll[aá]gr[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[oó]j[aá]s' OR  a.rut ~* 'r[oó]j[aá]s' OR  lower(a.email) ~* 'r[oó]j[aá]s' OR  text(a.id) ~* 'r[oó]j[aá]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'l[uú]d[oó]v[ií]c[aá]' OR  a.rut ~* 'l[uú]d[oó]v[ií]c[aá]' OR  lower(a.email) ~* 'l[uú]d[oó]v[ií]c[aá]' OR  text(a.id) ~* 'l[uú]d[oó]v[ií]c[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'd[eé]' OR  a.rut ~* 'd[eé]' OR  lower(a.email) ~* 'd[eé]' OR  text(a.id) ~* 'd[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'l[aá]s' OR  a.rut ~* 'l[aá]s' OR  lower(a.email) ~* 'l[aá]s' OR  text(a.id) ~* 'l[aá]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[eé]rc[eé]d[eé]s.' OR  a.rut ~* 'm[eé]rc[eé]d[eé]s.' OR  lower(a.email) ~* 'm[eé]rc[eé]d[eé]s.' OR  text(a.id) ~* 'm[eé]rc[eé]d[eé]s.' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER