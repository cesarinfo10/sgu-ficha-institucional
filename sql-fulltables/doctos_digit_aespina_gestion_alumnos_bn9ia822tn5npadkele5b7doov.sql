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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]n[aá]' OR  a.rut ~* '[aá]n[aá]' OR  lower(a.email) ~* '[aá]n[aá]' OR  text(a.id) ~* '[aá]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'd[oó]l[oó]r[eé]s' OR  a.rut ~* 'd[oó]l[oó]r[eé]s' OR  lower(a.email) ~* 'd[oó]l[oó]r[eé]s' OR  text(a.id) ~* 'd[oó]l[oó]r[eé]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'l[oó]p[eé]z' OR  a.rut ~* 'l[oó]p[eé]z' OR  lower(a.email) ~* 'l[oó]p[eé]z' OR  text(a.id) ~* 'l[oó]p[eé]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]lm[uú]n[aá]' OR  a.rut ~* '[aá]lm[uú]n[aá]' OR  lower(a.email) ~* '[aá]lm[uú]n[aá]' OR  text(a.id) ~* '[aá]lm[uú]n[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER