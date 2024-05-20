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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[oó]l[ií]v[eé]r' OR  a.rut ~* '[oó]l[ií]v[eé]r' OR  lower(a.email) ~* '[oó]l[ií]v[eé]r' OR  text(a.id) ~* '[oó]l[ií]v[eé]r' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[oó]ll[aá]n[oó]' OR  a.rut ~* 'r[oó]ll[aá]n[oó]' OR  lower(a.email) ~* 'r[oó]ll[aá]n[oó]' OR  text(a.id) ~* 'r[oó]ll[aá]n[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER