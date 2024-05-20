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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'j[aá]n[ií]c[eé]' OR  a.rut ~* 'j[aá]n[ií]c[eé]' OR  lower(a.email) ~* 'j[aá]n[ií]c[eé]' OR  text(a.id) ~* 'j[aá]n[ií]c[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'k[ií][aá]b[eé]th' OR  a.rut ~* 'k[ií][aá]b[eé]th' OR  lower(a.email) ~* 'k[ií][aá]b[eé]th' OR  text(a.id) ~* 'k[ií][aá]b[eé]th' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'j[uú]sc[aá]m[aá]yt[aá]' OR  a.rut ~* 'j[uú]sc[aá]m[aá]yt[aá]' OR  lower(a.email) ~* 'j[uú]sc[aá]m[aá]yt[aá]' OR  text(a.id) ~* 'j[uú]sc[aá]m[aá]yt[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá][ií]c[eé]d[oó]' OR  a.rut ~* 'c[aá][ií]c[eé]d[oó]' OR  lower(a.email) ~* 'c[aá][ií]c[eé]d[oó]' OR  text(a.id) ~* 'c[aá][ií]c[eé]d[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER