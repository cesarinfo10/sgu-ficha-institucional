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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'j[oó]s[eé]' OR  a.rut ~* 'j[oó]s[eé]' OR  lower(a.email) ~* 'j[oó]s[eé]' OR  text(a.id) ~* 'j[oó]s[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[ií]g[uú][eé]l' OR  a.rut ~* 'm[ií]g[uú][eé]l' OR  lower(a.email) ~* 'm[ií]g[uú][eé]l' OR  text(a.id) ~* 'm[ií]g[uú][eé]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[aá]r[aá]d[aá]' OR  a.rut ~* 'p[aá]r[aá]d[aá]' OR  lower(a.email) ~* 'p[aá]r[aá]d[aá]' OR  text(a.id) ~* 'p[aá]r[aá]d[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER