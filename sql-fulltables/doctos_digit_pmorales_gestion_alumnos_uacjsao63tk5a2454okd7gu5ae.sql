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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]n[aá]' OR  a.rut ~* '[aá]n[aá]' OR  lower(a.email) ~* '[aá]n[aá]' OR  text(a.id) ~* '[aá]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'd[eé]l' OR  a.rut ~* 'd[eé]l' OR  lower(a.email) ~* 'd[eé]l' OR  text(a.id) ~* 'd[eé]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[oó]s[aá]r[ií][oó]' OR  a.rut ~* 'r[oó]s[aá]r[ií][oó]' OR  lower(a.email) ~* 'r[oó]s[aá]r[ií][oó]' OR  text(a.id) ~* 'r[oó]s[aá]r[ií][oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'j[aá]r[aá]' OR  a.rut ~* 'j[aá]r[aá]' OR  lower(a.email) ~* 'j[aá]r[aá]' OR  text(a.id) ~* 'j[aá]r[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]rr[ií][aá]g[aá]d[aá]' OR  a.rut ~* '[aá]rr[ií][aá]g[aá]d[aá]' OR  lower(a.email) ~* '[aá]rr[ií][aá]g[aá]d[aá]' OR  text(a.id) ~* '[aá]rr[ií][aá]g[aá]d[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER