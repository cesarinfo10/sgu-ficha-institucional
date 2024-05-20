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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'd[aá]n[ií][eé]l' OR  a.rut ~* 'd[aá]n[ií][eé]l' OR  lower(a.email) ~* 'd[aá]n[ií][eé]l' OR  text(a.id) ~* 'd[aá]n[ií][eé]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá][uú]r[ií]c[ií][oó]' OR  a.rut ~* 'm[aá][uú]r[ií]c[ií][oó]' OR  lower(a.email) ~* 'm[aá][uú]r[ií]c[ií][oó]' OR  text(a.id) ~* 'm[aá][uú]r[ií]c[ií][oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[oó]rt[ií]z' OR  a.rut ~* '[oó]rt[ií]z' OR  lower(a.email) ~* '[oó]rt[ií]z' OR  text(a.id) ~* '[oó]rt[ií]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[eé]nd[oó]z[aá]' OR  a.rut ~* 'm[eé]nd[oó]z[aá]' OR  lower(a.email) ~* 'm[eé]nd[oó]z[aá]' OR  text(a.id) ~* 'm[eé]nd[oó]z[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER