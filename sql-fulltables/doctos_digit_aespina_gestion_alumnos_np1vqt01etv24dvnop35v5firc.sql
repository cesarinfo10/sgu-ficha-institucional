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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]x[ií]m[oó]' OR  a.rut ~* 'm[aá]x[ií]m[oó]' OR  lower(a.email) ~* 'm[aá]x[ií]m[oó]' OR  text(a.id) ~* 'm[aá]x[ií]m[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'w[aá]lt[eé]r' OR  a.rut ~* 'w[aá]lt[eé]r' OR  lower(a.email) ~* 'w[aá]lt[eé]r' OR  text(a.id) ~* 'w[aá]lt[eé]r' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[eé]y[eé]s' OR  a.rut ~* 'r[eé]y[eé]s' OR  lower(a.email) ~* 'r[eé]y[eé]s' OR  text(a.id) ~* 'r[eé]y[eé]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'b[aá]rr[ií][eé]nt[oó]s' OR  a.rut ~* 'b[aá]rr[ií][eé]nt[oó]s' OR  lower(a.email) ~* 'b[aá]rr[ií][eé]nt[oó]s' OR  text(a.id) ~* 'b[aá]rr[ií][eé]nt[oó]s' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER