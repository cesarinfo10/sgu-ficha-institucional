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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'h[eé]nr[ií]q[uú][eé]z' OR  a.rut ~* 'h[eé]nr[ií]q[uú][eé]z' OR  lower(a.email) ~* 'h[eé]nr[ií]q[uú][eé]z' OR  text(a.id) ~* 'h[eé]nr[ií]q[uú][eé]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]rr[aá]sc[oó]' OR  a.rut ~* 'c[aá]rr[aá]sc[oó]' OR  lower(a.email) ~* 'c[aá]rr[aá]sc[oó]' OR  text(a.id) ~* 'c[aá]rr[aá]sc[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[eé]dr[oó]' OR  a.rut ~* 'p[eé]dr[oó]' OR  lower(a.email) ~* 'p[eé]dr[oó]' OR  text(a.id) ~* 'p[eé]dr[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[aá][uú]l' OR  a.rut ~* 'r[aá][uú]l' OR  lower(a.email) ~* 'r[aá][uú]l' OR  text(a.id) ~* 'r[aá][uú]l' )  AND a.carrera_actual IN (106,120)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER