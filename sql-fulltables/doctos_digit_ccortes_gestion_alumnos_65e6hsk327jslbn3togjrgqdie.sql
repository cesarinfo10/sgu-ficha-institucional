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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[ií]ntr[oó]d[uú]cc[ií][oó]n' OR  a.rut ~* '[ií]ntr[oó]d[uú]cc[ií][oó]n' OR  lower(a.email) ~* '[ií]ntr[oó]d[uú]cc[ií][oó]n' OR  text(a.id) ~* '[ií]ntr[oó]d[uú]cc[ií][oó]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]' OR  a.rut ~* '[aá]' OR  lower(a.email) ~* '[aá]' OR  text(a.id) ~* '[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'l[aá]' OR  a.rut ~* 'l[aá]' OR  lower(a.email) ~* 'l[aá]' OR  text(a.id) ~* 'l[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[ií]d[aá]' OR  a.rut ~* 'v[ií]d[aá]' OR  lower(a.email) ~* 'v[ií]d[aá]' OR  text(a.id) ~* 'v[ií]d[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]c[aá]d[eé]m[ií]c[aá]' OR  a.rut ~* '[aá]c[aá]d[eé]m[ií]c[aá]' OR  lower(a.email) ~* '[aá]c[aá]d[eé]m[ií]c[aá]' OR  text(a.id) ~* '[aá]c[aá]d[eé]m[ií]c[aá]' )  AND a.carrera_actual IN (95,100,2,69,1,19)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER