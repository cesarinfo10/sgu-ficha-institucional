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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'j[oó]c[eé]lyn' OR  a.rut ~* 'j[oó]c[eé]lyn' OR  lower(a.email) ~* 'j[oó]c[eé]lyn' OR  text(a.id) ~* 'j[oó]c[eé]lyn' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]sp[ií]n[oó]z[aá]' OR  a.rut ~* '[eé]sp[ií]n[oó]z[aá]' OR  lower(a.email) ~* '[eé]sp[ií]n[oó]z[aá]' OR  text(a.id) ~* '[eé]sp[ií]n[oó]z[aá]' )  AND a.carrera_actual IN (61,68,21)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER