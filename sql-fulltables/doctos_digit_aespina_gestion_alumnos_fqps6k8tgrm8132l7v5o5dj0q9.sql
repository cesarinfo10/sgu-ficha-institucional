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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'l[eé][oó]p[oó]ld[oó]' OR  a.rut ~* 'l[eé][oó]p[oó]ld[oó]' OR  lower(a.email) ~* 'l[eé][oó]p[oó]ld[oó]' OR  text(a.id) ~* 'l[eé][oó]p[oó]ld[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'q[uú][eé]z[aá]d[aá]' OR  a.rut ~* 'q[uú][eé]z[aá]d[aá]' OR  lower(a.email) ~* 'q[uú][eé]z[aá]d[aá]' OR  text(a.id) ~* 'q[uú][eé]z[aá]d[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[uú]z' OR  a.rut ~* 'r[uú]z' OR  lower(a.email) ~* 'r[uú]z' OR  text(a.id) ~* 'r[uú]z' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER