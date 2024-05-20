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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'b[aá]n[ií]' OR  a.rut ~* 'b[aá]n[ií]' OR  lower(a.email) ~* 'b[aá]n[ií]' OR  text(a.id) ~* 'b[aá]n[ií]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'd[aá]n[ií]x[aá]' OR  a.rut ~* 'd[aá]n[ií]x[aá]' OR  lower(a.email) ~* 'd[aá]n[ií]x[aá]' OR  text(a.id) ~* 'd[aá]n[ií]x[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[oó]t[oó]' OR  a.rut ~* 's[oó]t[oó]' OR  lower(a.email) ~* 's[oó]t[oó]' OR  text(a.id) ~* 's[oó]t[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'g[eé]yss[eé]l' OR  a.rut ~* 'g[eé]yss[eé]l' OR  lower(a.email) ~* 'g[eé]yss[eé]l' OR  text(a.id) ~* 'g[eé]yss[eé]l' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER