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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá][uú]r[ií]c[ií][oó]' OR  a.rut ~* 'm[aá][uú]r[ií]c[ií][oó]' OR  lower(a.email) ~* 'm[aá][uú]r[ií]c[ií][oó]' OR  text(a.id) ~* 'm[aá][uú]r[ií]c[ií][oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[aá]sq[uú][eé]z' OR  a.rut ~* 'v[aá]sq[uú][eé]z' OR  lower(a.email) ~* 'v[aá]sq[uú][eé]z' OR  text(a.id) ~* 'v[aá]sq[uú][eé]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]lf[aá]r[oó]' OR  a.rut ~* '[aá]lf[aá]r[oó]' OR  lower(a.email) ~* '[aá]lf[aá]r[oó]' OR  text(a.id) ~* '[aá]lf[aá]r[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER