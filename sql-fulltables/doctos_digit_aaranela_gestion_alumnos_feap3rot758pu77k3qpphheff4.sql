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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]rt[ií]ns' OR  a.rut ~* 'm[aá]rt[ií]ns' OR  lower(a.email) ~* 'm[aá]rt[ií]ns' OR  text(a.id) ~* 'm[aá]rt[ií]ns' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'd[eé]' OR  a.rut ~* 'd[eé]' OR  lower(a.email) ~* 'd[eé]' OR  text(a.id) ~* 'd[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[oó]r[aá][eé]s' OR  a.rut ~* 'm[oó]r[aá][eé]s' OR  lower(a.email) ~* 'm[oó]r[aá][eé]s' OR  text(a.id) ~* 'm[oó]r[aá][eé]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'l[uú]c[eé]l[ií][aá]' OR  a.rut ~* 'l[uú]c[eé]l[ií][aá]' OR  lower(a.email) ~* 'l[uú]c[eé]l[ií][aá]' OR  text(a.id) ~* 'l[uú]c[eé]l[ií][aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER