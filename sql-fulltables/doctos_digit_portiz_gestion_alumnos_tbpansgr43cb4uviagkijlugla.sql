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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'y[uú]l[ií][eé]th' OR  a.rut ~* 'y[uú]l[ií][eé]th' OR  lower(a.email) ~* 'y[uú]l[ií][eé]th' OR  text(a.id) ~* 'y[uú]l[ií][eé]th' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[eé]rc[eé]d[eé]s' OR  a.rut ~* 'm[eé]rc[eé]d[eé]s' OR  lower(a.email) ~* 'm[eé]rc[eé]d[eé]s' OR  text(a.id) ~* 'm[eé]rc[eé]d[eé]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[oó]r[aá]l[eé]s' OR  a.rut ~* 'm[oó]r[aá]l[eé]s' OR  lower(a.email) ~* 'm[oó]r[aá]l[eé]s' OR  text(a.id) ~* 'm[oó]r[aá]l[eé]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'tr[uú]j[ií]ll[oó]' OR  a.rut ~* 'tr[uú]j[ií]ll[oó]' OR  lower(a.email) ~* 'tr[uú]j[ií]ll[oó]' OR  text(a.id) ~* 'tr[uú]j[ií]ll[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER