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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'p[aá][oó]l[aá]' OR  a.rut ~* 'p[aá][oó]l[aá]' OR  lower(a.email) ~* 'p[aá][oó]l[aá]' OR  text(a.id) ~* 'p[aá][oó]l[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'f[eé]rr[aá]r[ií]' OR  a.rut ~* 'f[eé]rr[aá]r[ií]' OR  lower(a.email) ~* 'f[eé]rr[aá]r[ií]' OR  text(a.id) ~* 'f[eé]rr[aá]r[ií]' )  AND a.carrera_actual IN (59,57,27,28,33,54,56,31,58,60,55,30,32,29,76,79,82,81,83,78,80,38,115,114,77,125,147,148)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER