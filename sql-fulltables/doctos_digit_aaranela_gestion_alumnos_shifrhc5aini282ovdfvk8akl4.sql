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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'l[oó]p[eé]z' OR  a.rut ~* 'l[oó]p[eé]z' OR  lower(a.email) ~* 'l[oó]p[eé]z' OR  text(a.id) ~* 'l[oó]p[eé]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[oó]rd[eé]r[oó]' OR  a.rut ~* 'c[oó]rd[eé]r[oó]' OR  lower(a.email) ~* 'c[oó]rd[eé]r[oó]' OR  text(a.id) ~* 'c[oó]rd[eé]r[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]rc[eé]l[aá]' OR  a.rut ~* 'm[aá]rc[eé]l[aá]' OR  lower(a.email) ~* 'm[aá]rc[eé]l[aá]' OR  text(a.id) ~* 'm[aá]rc[eé]l[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[aá]tr[ií]c[ií][aá]' OR  a.rut ~* 'p[aá]tr[ií]c[ií][aá]' OR  lower(a.email) ~* 'p[aá]tr[ií]c[ií][aá]' OR  text(a.id) ~* 'p[aá]tr[ií]c[ií][aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER