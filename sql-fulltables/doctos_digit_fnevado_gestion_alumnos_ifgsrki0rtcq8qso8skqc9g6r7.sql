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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'j[aá]q[uú][eé]' OR  a.rut ~* 'j[aá]q[uú][eé]' OR  lower(a.email) ~* 'j[aá]q[uú][eé]' OR  text(a.id) ~* 'j[aá]q[uú][eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'tr[oó]nc[oó]s[oó]' OR  a.rut ~* 'tr[oó]nc[oó]s[oó]' OR  lower(a.email) ~* 'tr[oó]nc[oó]s[oó]' OR  text(a.id) ~* 'tr[oó]nc[oó]s[oó]' )  AND a.carrera_actual IN (94,97,104,110,98,105,102,91,15,103,126,4)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER