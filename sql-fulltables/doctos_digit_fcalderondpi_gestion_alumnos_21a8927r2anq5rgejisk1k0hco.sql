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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'fr[aá]nc[oó]' OR  a.rut ~* 'fr[aá]nc[oó]' OR  lower(a.email) ~* 'fr[aá]nc[oó]' OR  text(a.id) ~* 'fr[aá]nc[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[oó]y[aá]rz[oó]' OR  a.rut ~* '[oó]y[aá]rz[oó]' OR  lower(a.email) ~* '[oó]y[aá]rz[oó]' OR  text(a.id) ~* '[oó]y[aá]rz[oó]' )  AND a.carrera_actual IN (59,57,33,54,56,31,58,60,55,30,32,29,76,79,82,81,83,78,80,27,28,38,115,114,77,125,147,148,161,160,163)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER