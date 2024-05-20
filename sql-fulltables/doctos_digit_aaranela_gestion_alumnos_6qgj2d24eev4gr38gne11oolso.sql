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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'v[eé]l[ií]z' OR  a.rut ~* 'v[eé]l[ií]z' OR  lower(a.email) ~* 'v[eé]l[ií]z' OR  text(a.id) ~* 'v[eé]l[ií]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'g[aá][eé]t[eé]' OR  a.rut ~* 'g[aá][eé]t[eé]' OR  lower(a.email) ~* 'g[aá][eé]t[eé]' OR  text(a.id) ~* 'g[aá][eé]t[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]l[eé]j[aá]ndr[oó]' OR  a.rut ~* '[aá]l[eé]j[aá]ndr[oó]' OR  lower(a.email) ~* '[aá]l[eé]j[aá]ndr[oó]' OR  text(a.id) ~* '[aá]l[eé]j[aá]ndr[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]d[uú][aá]rd[oó]' OR  a.rut ~* '[eé]d[uú][aá]rd[oó]' OR  lower(a.email) ~* '[eé]d[uú][aá]rd[oó]' OR  text(a.id) ~* '[eé]d[uú][aá]rd[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER