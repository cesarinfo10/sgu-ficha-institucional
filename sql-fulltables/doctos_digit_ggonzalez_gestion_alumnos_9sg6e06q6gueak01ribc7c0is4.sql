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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 's[aá]nt[oó]s' OR  a.rut ~* 's[aá]nt[oó]s' OR  lower(a.email) ~* 's[aá]nt[oó]s' OR  text(a.id) ~* 's[aá]nt[oó]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[aá]d[uú]ll[ií]' OR  a.rut ~* 'v[aá]d[uú]ll[ií]' OR  lower(a.email) ~* 'v[aá]d[uú]ll[ií]' OR  text(a.id) ~* 'v[aá]d[uú]ll[ií]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER