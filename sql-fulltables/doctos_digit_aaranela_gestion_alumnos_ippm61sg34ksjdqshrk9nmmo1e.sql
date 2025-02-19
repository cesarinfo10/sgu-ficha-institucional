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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'v[ií]ct[oó]r[ií][aá]' OR  a.rut ~* 'v[ií]ct[oó]r[ií][aá]' OR  lower(a.email) ~* 'v[ií]ct[oó]r[ií][aá]' OR  text(a.id) ~* 'v[ií]ct[oó]r[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[aá]ld[eé]s' OR  a.rut ~* 'v[aá]ld[eé]s' OR  lower(a.email) ~* 'v[aá]ld[eé]s' OR  text(a.id) ~* 'v[aá]ld[eé]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[uú]b[ií][oó]' OR  a.rut ~* 'r[uú]b[ií][oó]' OR  lower(a.email) ~* 'r[uú]b[ií][oó]' OR  text(a.id) ~* 'r[uú]b[ií][oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER