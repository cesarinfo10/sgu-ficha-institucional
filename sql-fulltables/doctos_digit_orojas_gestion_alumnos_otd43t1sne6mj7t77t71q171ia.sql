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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'l[oó]p[eé]z' OR  a.rut ~* 'l[oó]p[eé]z' OR  lower(a.email) ~* 'l[oó]p[eé]z' OR  text(a.id) ~* 'l[oó]p[eé]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[eé]nd[oó]z[aá]' OR  a.rut ~* 'm[eé]nd[oó]z[aá]' OR  lower(a.email) ~* 'm[eé]nd[oó]z[aá]' OR  text(a.id) ~* 'm[eé]nd[oó]z[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'g[eé]r[aá]rd[oó]' OR  a.rut ~* 'g[eé]r[aá]rd[oó]' OR  lower(a.email) ~* 'g[eé]r[aá]rd[oó]' OR  text(a.id) ~* 'g[eé]r[aá]rd[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]st[eé]b[aá]n' OR  a.rut ~* '[eé]st[eé]b[aá]n' OR  lower(a.email) ~* '[eé]st[eé]b[aá]n' OR  text(a.id) ~* '[eé]st[eé]b[aá]n' )  AND a.carrera_actual IN (59,57,27,28,33,54,56,31,58,60,55,30,32,29,76,79,82,81,83,78,80,38,115,114,77,125,147,148)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER