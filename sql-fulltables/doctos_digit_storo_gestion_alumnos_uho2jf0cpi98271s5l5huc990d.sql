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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[ií]v[aá]n' OR  a.rut ~* '[ií]v[aá]n' OR  lower(a.email) ~* '[ií]v[aá]n' OR  text(a.id) ~* '[ií]v[aá]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'ch[aá]c[oó]n' OR  a.rut ~* 'ch[aá]c[oó]n' OR  lower(a.email) ~* 'ch[aá]c[oó]n' OR  text(a.id) ~* 'ch[aá]c[oó]n' )  AND a.carrera_actual IN (95,100,19,2,69,1)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER