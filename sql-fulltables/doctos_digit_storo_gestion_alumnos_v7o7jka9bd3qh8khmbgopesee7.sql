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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]rn[eé]st[oó]' OR  a.rut ~* '[eé]rn[eé]st[oó]' OR  lower(a.email) ~* '[eé]rn[eé]st[oó]' OR  text(a.id) ~* '[eé]rn[eé]st[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[oó]y[aá]' OR  a.rut ~* 'm[oó]y[aá]' OR  lower(a.email) ~* 'm[oó]y[aá]' OR  text(a.id) ~* 'm[oó]y[aá]' )  AND a.carrera_actual IN (95,100,2,69,1,19)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER