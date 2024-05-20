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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'v[aá]sq[uú][eé]z' OR  a.rut ~* 'v[aá]sq[uú][eé]z' OR  lower(a.email) ~* 'v[aá]sq[uú][eé]z' OR  text(a.id) ~* 'v[aá]sq[uú][eé]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[uú][aá]z[oó]' OR  a.rut ~* 's[uú][aá]z[oó]' OR  lower(a.email) ~* 's[uú][aá]z[oó]' OR  text(a.id) ~* 's[uú][aá]z[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'd[aá]v[ií]d' OR  a.rut ~* 'd[aá]v[ií]d' OR  lower(a.email) ~* 'd[aá]v[ií]d' OR  text(a.id) ~* 'd[aá]v[ií]d' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'h[uú]mb[eé]rt[oó]' OR  a.rut ~* 'h[uú]mb[eé]rt[oó]' OR  lower(a.email) ~* 'h[uú]mb[eé]rt[oó]' OR  text(a.id) ~* 'h[uú]mb[eé]rt[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER