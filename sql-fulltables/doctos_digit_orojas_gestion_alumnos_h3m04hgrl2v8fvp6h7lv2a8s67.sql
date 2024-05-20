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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 's[aá][eé]z' OR  a.rut ~* 's[aá][eé]z' OR  lower(a.email) ~* 's[aá][eé]z' OR  text(a.id) ~* 's[aá][eé]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[uú]sch[eé]l' OR  a.rut ~* 'p[uú]sch[eé]l' OR  lower(a.email) ~* 'p[uú]sch[eé]l' OR  text(a.id) ~* 'p[uú]sch[eé]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]l[eé]x' OR  a.rut ~* '[aá]l[eé]x' OR  lower(a.email) ~* '[aá]l[eé]x' OR  text(a.id) ~* '[aá]l[eé]x' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]n[uú][eé]l' OR  a.rut ~* 'm[aá]n[uú][eé]l' OR  lower(a.email) ~* 'm[aá]n[uú][eé]l' OR  text(a.id) ~* 'm[aá]n[uú][eé]l' )  AND a.carrera_actual IN (59,57,27,28,33,54,56,31,58,60,55,30,32,29,76,79,82,81,83,78,80,38,115,114,77,125)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER