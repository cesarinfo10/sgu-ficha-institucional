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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'p[aá]m[eé]l[aá]' OR  a.rut ~* 'p[aá]m[eé]l[aá]' OR  lower(a.email) ~* 'p[aá]m[eé]l[aá]' OR  text(a.id) ~* 'p[aá]m[eé]l[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'pr[uú]d[eé]n[aá]' OR  a.rut ~* 'pr[uú]d[eé]n[aá]' OR  lower(a.email) ~* 'pr[uú]d[eé]n[aá]' OR  text(a.id) ~* 'pr[uú]d[eé]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[oó]r[eé]ll[aá]n[aá]' OR  a.rut ~* '[oó]r[eé]ll[aá]n[aá]' OR  lower(a.email) ~* '[oó]r[eé]ll[aá]n[aá]' OR  text(a.id) ~* '[oó]r[eé]ll[aá]n[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER