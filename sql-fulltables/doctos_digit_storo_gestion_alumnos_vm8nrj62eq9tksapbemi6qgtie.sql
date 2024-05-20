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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'g[ií][aá]n' OR  a.rut ~* 'g[ií][aá]n' OR  lower(a.email) ~* 'g[ií][aá]n' OR  text(a.id) ~* 'g[ií][aá]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[oó][oó]l' OR  a.rut ~* 'p[oó][oó]l' OR  lower(a.email) ~* 'p[oó][oó]l' OR  text(a.id) ~* 'p[oó][oó]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'g[aá]rc[ií][aá]' OR  a.rut ~* 'g[aá]rc[ií][aá]' OR  lower(a.email) ~* 'g[aá]rc[ií][aá]' OR  text(a.id) ~* 'g[aá]rc[ií][aá]' )  AND a.carrera_actual IN (95,100,19,2,69,1)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER