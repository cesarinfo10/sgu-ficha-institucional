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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'c[eé]s[aá]r' OR  a.rut ~* 'c[eé]s[aá]r' OR  lower(a.email) ~* 'c[eé]s[aá]r' OR  text(a.id) ~* 'c[eé]s[aá]r' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]l[eé]j[aá]ndr[oó]' OR  a.rut ~* '[aá]l[eé]j[aá]ndr[oó]' OR  lower(a.email) ~* '[aá]l[eé]j[aá]ndr[oó]' OR  text(a.id) ~* '[aá]l[eé]j[aá]ndr[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[ií]b[aá]ñ[eé]z' OR  a.rut ~* '[ií]b[aá]ñ[eé]z' OR  lower(a.email) ~* '[ií]b[aá]ñ[eé]z' OR  text(a.id) ~* '[ií]b[aá]ñ[eé]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[eé][ií]n[oó]s[oó]' OR  a.rut ~* 'r[eé][ií]n[oó]s[oó]' OR  lower(a.email) ~* 'r[eé][ií]n[oó]s[oó]' OR  text(a.id) ~* 'r[eé][ií]n[oó]s[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER