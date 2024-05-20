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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'd[oó]m[ií]ng[oó]' OR  a.rut ~* 'd[oó]m[ií]ng[oó]' OR  lower(a.email) ~* 'd[oó]m[ií]ng[oó]' OR  text(a.id) ~* 'd[oó]m[ií]ng[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 't[oó]rr[eé]s' OR  a.rut ~* 't[oó]rr[eé]s' OR  lower(a.email) ~* 't[oó]rr[eé]s' OR  text(a.id) ~* 't[oó]rr[eé]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* '' OR  a.rut ~* '' OR  lower(a.email) ~* '' OR  text(a.id) ~* '' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'q[uú][eé]z[aá]d[aá]' OR  a.rut ~* 'q[uú][eé]z[aá]d[aá]' OR  lower(a.email) ~* 'q[uú][eé]z[aá]d[aá]' OR  text(a.id) ~* 'q[uú][eé]z[aá]d[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER