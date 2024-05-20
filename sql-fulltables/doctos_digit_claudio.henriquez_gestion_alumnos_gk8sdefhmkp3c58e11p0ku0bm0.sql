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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'l[ií]c[eé]dh' OR  a.rut ~* 'l[ií]c[eé]dh' OR  lower(a.email) ~* 'l[ií]c[eé]dh' OR  text(a.id) ~* 'l[ií]c[eé]dh' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'ch[oó]q[uú][eé]' OR  a.rut ~* 'ch[oó]q[uú][eé]' OR  lower(a.email) ~* 'ch[oó]q[uú][eé]' OR  text(a.id) ~* 'ch[oó]q[uú][eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[oó]ll[oó]' OR  a.rut ~* 'm[oó]ll[oó]' OR  lower(a.email) ~* 'm[oó]ll[oó]' OR  text(a.id) ~* 'm[oó]ll[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER