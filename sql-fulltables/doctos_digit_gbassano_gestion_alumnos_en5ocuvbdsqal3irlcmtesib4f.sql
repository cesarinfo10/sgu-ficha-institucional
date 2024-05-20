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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'j[oó]rg[eé]' OR  a.rut ~* 'j[oó]rg[eé]' OR  lower(a.email) ~* 'j[oó]rg[eé]' OR  text(a.id) ~* 'j[oó]rg[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'g[aá]ll[eé]g[oó]' OR  a.rut ~* 'g[aá]ll[eé]g[oó]' OR  lower(a.email) ~* 'g[aá]ll[eé]g[oó]' OR  text(a.id) ~* 'g[aá]ll[eé]g[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'fr[aá]d[eé]m[aá]nn' OR  a.rut ~* 'fr[aá]d[eé]m[aá]nn' OR  lower(a.email) ~* 'fr[aá]d[eé]m[aá]nn' OR  text(a.id) ~* 'fr[aá]d[eé]m[aá]nn' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER