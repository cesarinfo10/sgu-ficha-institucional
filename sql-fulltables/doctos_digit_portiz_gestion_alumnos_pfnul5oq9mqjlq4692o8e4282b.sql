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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'l[oó]rn[aá]' OR  a.rut ~* 'l[oó]rn[aá]' OR  lower(a.email) ~* 'l[oó]rn[aá]' OR  text(a.id) ~* 'l[oó]rn[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]l[ií]z[aá]b[eé]th' OR  a.rut ~* '[eé]l[ií]z[aá]b[eé]th' OR  lower(a.email) ~* '[eé]l[ií]z[aá]b[eé]th' OR  text(a.id) ~* '[eé]l[ií]z[aá]b[eé]th' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]l[aá]m[oó]s' OR  a.rut ~* '[aá]l[aá]m[oó]s' OR  lower(a.email) ~* '[aá]l[aá]m[oó]s' OR  text(a.id) ~* '[aá]l[aá]m[oó]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[oó]dr[ií]g[uú][eé]z' OR  a.rut ~* 'r[oó]dr[ií]g[uú][eé]z' OR  lower(a.email) ~* 'r[oó]dr[ií]g[uú][eé]z' OR  text(a.id) ~* 'r[oó]dr[ií]g[uú][eé]z' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER