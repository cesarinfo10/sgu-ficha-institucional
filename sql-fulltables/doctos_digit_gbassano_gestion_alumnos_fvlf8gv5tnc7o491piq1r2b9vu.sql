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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]cr[eé]n[aá]' OR  a.rut ~* 'm[aá]cr[eé]n[aá]' OR  lower(a.email) ~* 'm[aá]cr[eé]n[aá]' OR  text(a.id) ~* 'm[aá]cr[eé]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[oó]y[aá]rz[uú]n' OR  a.rut ~* '[oó]y[aá]rz[uú]n' OR  lower(a.email) ~* '[oó]y[aá]rz[uú]n' OR  text(a.id) ~* '[oó]y[aá]rz[uú]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* '' OR  a.rut ~* '' OR  lower(a.email) ~* '' OR  text(a.id) ~* '' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]s[aá]n[oó]v[aá]' OR  a.rut ~* 'c[aá]s[aá]n[oó]v[aá]' OR  lower(a.email) ~* 'c[aá]s[aá]n[oó]v[aá]' OR  text(a.id) ~* 'c[aá]s[aá]n[oó]v[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER