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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]rl[aá]' OR  a.rut ~* 'c[aá]rl[aá]' OR  lower(a.email) ~* 'c[aá]rl[aá]' OR  text(a.id) ~* 'c[aá]rl[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[aá][oó]l[aá]' OR  a.rut ~* 'p[aá][oó]l[aá]' OR  lower(a.email) ~* 'p[aá][oó]l[aá]' OR  text(a.id) ~* 'p[aá][oó]l[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'l[oó]r[eé]t[oó]' OR  a.rut ~* 'l[oó]r[eé]t[oó]' OR  lower(a.email) ~* 'l[oó]r[eé]t[oó]' OR  text(a.id) ~* 'l[oó]r[eé]t[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[oó]rr[oó]' OR  a.rut ~* 'p[oó]rr[oó]' OR  lower(a.email) ~* 'p[oó]rr[oó]' OR  text(a.id) ~* 'p[oó]rr[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'n[oó]r[aá]mb[uú][eé]n[aá]' OR  a.rut ~* 'n[oó]r[aá]mb[uú][eé]n[aá]' OR  lower(a.email) ~* 'n[oó]r[aá]mb[uú][eé]n[aá]' OR  text(a.id) ~* 'n[oó]r[aá]mb[uú][eé]n[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER