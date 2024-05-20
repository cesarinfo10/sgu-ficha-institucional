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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'y[aá]m[ií]l[eé]t' OR  a.rut ~* 'y[aá]m[ií]l[eé]t' OR  lower(a.email) ~* 'y[aá]m[ií]l[eé]t' OR  text(a.id) ~* 'y[aá]m[ií]l[eé]t' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[uú]ll[oó][aá]' OR  a.rut ~* '[uú]ll[oó][aá]' OR  lower(a.email) ~* '[uú]ll[oó][aá]' OR  text(a.id) ~* '[uú]ll[oó][aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER