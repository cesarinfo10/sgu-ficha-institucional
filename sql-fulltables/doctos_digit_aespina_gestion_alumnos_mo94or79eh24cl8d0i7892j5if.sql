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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]m[ií]l[aá]' OR  a.rut ~* 'c[aá]m[ií]l[aá]' OR  lower(a.email) ~* 'c[aá]m[ií]l[aá]' OR  text(a.id) ~* 'c[aá]m[ií]l[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'f[eé]rn[aá]nd[aá]' OR  a.rut ~* 'f[eé]rn[aá]nd[aá]' OR  lower(a.email) ~* 'f[eé]rn[aá]nd[aá]' OR  text(a.id) ~* 'f[eé]rn[aá]nd[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'gr[ií]ll[eé]' OR  a.rut ~* 'gr[ií]ll[eé]' OR  lower(a.email) ~* 'gr[ií]ll[eé]' OR  text(a.id) ~* 'gr[ií]ll[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'l[eé]yt[oó]n' OR  a.rut ~* 'l[eé]yt[oó]n' OR  lower(a.email) ~* 'l[eé]yt[oó]n' OR  text(a.id) ~* 'l[eé]yt[oó]n' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER