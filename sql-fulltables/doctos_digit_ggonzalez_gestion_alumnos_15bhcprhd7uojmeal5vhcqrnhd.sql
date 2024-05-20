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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]n[uú][eé]l' OR  a.rut ~* 'm[aá]n[uú][eé]l' OR  lower(a.email) ~* 'm[aá]n[uú][eé]l' OR  text(a.id) ~* 'm[aá]n[uú][eé]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]str[oó]' OR  a.rut ~* 'c[aá]str[oó]' OR  lower(a.email) ~* 'c[aá]str[oó]' OR  text(a.id) ~* 'c[aá]str[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'q' OR  a.rut ~* 'q' OR  lower(a.email) ~* 'q' OR  text(a.id) ~* 'q' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER