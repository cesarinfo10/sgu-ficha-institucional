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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'r[ií]q[uú][eé]lm[eé]' OR  a.rut ~* 'r[ií]q[uú][eé]lm[eé]' OR  lower(a.email) ~* 'r[ií]q[uú][eé]lm[eé]' OR  text(a.id) ~* 'r[ií]q[uú][eé]lm[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]ld[oó]n[aá]d[oó]' OR  a.rut ~* 'm[aá]ld[oó]n[aá]d[oó]' OR  lower(a.email) ~* 'm[aá]ld[oó]n[aá]d[oó]' OR  text(a.id) ~* 'm[aá]ld[oó]n[aá]d[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'n[ií]c[oó]l[eé]' OR  a.rut ~* 'n[ií]c[oó]l[eé]' OR  lower(a.email) ~* 'n[ií]c[oó]l[eé]' OR  text(a.id) ~* 'n[ií]c[oó]l[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]ndr[eé][aá]' OR  a.rut ~* '[aá]ndr[eé][aá]' OR  lower(a.email) ~* '[aá]ndr[eé][aá]' OR  text(a.id) ~* '[aá]ndr[eé][aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER