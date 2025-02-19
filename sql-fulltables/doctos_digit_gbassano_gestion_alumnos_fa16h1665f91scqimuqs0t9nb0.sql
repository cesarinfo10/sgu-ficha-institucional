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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'l[uú][ií]s' OR  a.rut ~* 'l[uú][ií]s' OR  lower(a.email) ~* 'l[uú][ií]s' OR  text(a.id) ~* 'l[uú][ií]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[aá]tr[ií]c[ií][oó]' OR  a.rut ~* 'p[aá]tr[ií]c[ií][oó]' OR  lower(a.email) ~* 'p[aá]tr[ií]c[ií][oó]' OR  text(a.id) ~* 'p[aá]tr[ií]c[ií][oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'b[eé]rm[uú]d[eé]z' OR  a.rut ~* 'b[eé]rm[uú]d[eé]z' OR  lower(a.email) ~* 'b[eé]rm[uú]d[eé]z' OR  text(a.id) ~* 'b[eé]rm[uú]d[eé]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'l[eé][oó]n' OR  a.rut ~* 'l[eé][oó]n' OR  lower(a.email) ~* 'l[eé][oó]n' OR  text(a.id) ~* 'l[eé][oó]n' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER