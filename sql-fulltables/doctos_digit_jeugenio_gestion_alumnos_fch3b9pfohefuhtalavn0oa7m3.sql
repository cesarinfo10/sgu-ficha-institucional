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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'j[aá]v[ií]' OR  a.rut ~* 'j[aá]v[ií]' OR  lower(a.email) ~* 'j[aá]v[ií]' OR  text(a.id) ~* 'j[aá]v[ií]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'tr[eé]sk[oó]w' OR  a.rut ~* 'tr[eé]sk[oó]w' OR  lower(a.email) ~* 'tr[eé]sk[oó]w' OR  text(a.id) ~* 'tr[eé]sk[oó]w' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER