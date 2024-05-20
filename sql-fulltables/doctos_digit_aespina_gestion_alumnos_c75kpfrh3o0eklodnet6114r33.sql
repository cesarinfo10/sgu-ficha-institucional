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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]ndr[eé]' OR  a.rut ~* '[aá]ndr[eé]' OR  lower(a.email) ~* '[aá]ndr[eé]' OR  text(a.id) ~* '[aá]ndr[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[eé]b[aá]st[ií][aá]n' OR  a.rut ~* 's[eé]b[aá]st[ií][aá]n' OR  lower(a.email) ~* 's[eé]b[aá]st[ií][aá]n' OR  text(a.id) ~* 's[eé]b[aá]st[ií][aá]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'd[eé]' OR  a.rut ~* 'd[eé]' OR  lower(a.email) ~* 'd[eé]' OR  text(a.id) ~* 'd[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'j[eé]s[uú]s' OR  a.rut ~* 'j[eé]s[uú]s' OR  lower(a.email) ~* 'j[eé]s[uú]s' OR  text(a.id) ~* 'j[eé]s[uú]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[uú][eé]v[aá]s' OR  a.rut ~* 'c[uú][eé]v[aá]s' OR  lower(a.email) ~* 'c[uú][eé]v[aá]s' OR  text(a.id) ~* 'c[uú][eé]v[aá]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[oó]m[aá]n' OR  a.rut ~* 'r[oó]m[aá]n' OR  lower(a.email) ~* 'r[oó]m[aá]n' OR  text(a.id) ~* 'r[oó]m[aá]n' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER