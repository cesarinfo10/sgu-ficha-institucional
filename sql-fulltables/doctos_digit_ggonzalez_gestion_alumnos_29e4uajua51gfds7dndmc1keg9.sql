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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]ng[eé]l[eé]s' OR  a.rut ~* '[aá]ng[eé]l[eé]s' OR  lower(a.email) ~* '[aá]ng[eé]l[eé]s' OR  text(a.id) ~* '[aá]ng[eé]l[eé]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[ií]d[aá]l' OR  a.rut ~* 'v[ií]d[aá]l' OR  lower(a.email) ~* 'v[ií]d[aá]l' OR  text(a.id) ~* 'v[ií]d[aá]l' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER