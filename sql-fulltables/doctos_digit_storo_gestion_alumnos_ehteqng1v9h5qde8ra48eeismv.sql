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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]lv[aá]r[oó]' OR  a.rut ~* '[aá]lv[aá]r[oó]' OR  lower(a.email) ~* '[aá]lv[aá]r[oó]' OR  text(a.id) ~* '[aá]lv[aá]r[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[oó]rt[eé]g[aá]' OR  a.rut ~* '[oó]rt[eé]g[aá]' OR  lower(a.email) ~* '[oó]rt[eé]g[aá]' OR  text(a.id) ~* '[oó]rt[eé]g[aá]' )  AND a.carrera_actual IN (95,100,2,69,1,19)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER