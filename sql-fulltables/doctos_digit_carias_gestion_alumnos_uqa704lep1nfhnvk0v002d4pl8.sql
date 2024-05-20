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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[oó]rn[eé]ll[aá]' OR  a.rut ~* '[oó]rn[eé]ll[aá]' OR  lower(a.email) ~* '[oó]rn[eé]ll[aá]' OR  text(a.id) ~* '[oó]rn[eé]ll[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[oó]t[eé][ií]z[aá]' OR  a.rut ~* '[oó]t[eé][ií]z[aá]' OR  lower(a.email) ~* '[oó]t[eé][ií]z[aá]' OR  text(a.id) ~* '[oó]t[eé][ií]z[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER