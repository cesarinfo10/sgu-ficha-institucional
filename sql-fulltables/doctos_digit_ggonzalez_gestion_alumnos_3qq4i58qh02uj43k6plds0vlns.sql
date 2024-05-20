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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'j[oó]s[eé]f[aá]' OR  a.rut ~* 'j[oó]s[eé]f[aá]' OR  lower(a.email) ~* 'j[oó]s[eé]f[aá]' OR  text(a.id) ~* 'j[oó]s[eé]f[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'h[eé]rr[eé]r[aá]' OR  a.rut ~* 'h[eé]rr[eé]r[aá]' OR  lower(a.email) ~* 'h[eé]rr[eé]r[aá]' OR  text(a.id) ~* 'h[eé]rr[eé]r[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'n[aá]v[aá]rr[oó]' OR  a.rut ~* 'n[aá]v[aá]rr[oó]' OR  lower(a.email) ~* 'n[aá]v[aá]rr[oó]' OR  text(a.id) ~* 'n[aá]v[aá]rr[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER