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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'v[eé]r[oó]n[ií]c[aá]' OR  a.rut ~* 'v[eé]r[oó]n[ií]c[aá]' OR  lower(a.email) ~* 'v[eé]r[oó]n[ií]c[aá]' OR  text(a.id) ~* 'v[eé]r[oó]n[ií]c[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'pr[aá]d[oó]' OR  a.rut ~* 'pr[aá]d[oó]' OR  lower(a.email) ~* 'pr[aá]d[oó]' OR  text(a.id) ~* 'pr[aá]d[oó]' )  AND a.carrera_actual IN (95,100,19,2,69,1)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER