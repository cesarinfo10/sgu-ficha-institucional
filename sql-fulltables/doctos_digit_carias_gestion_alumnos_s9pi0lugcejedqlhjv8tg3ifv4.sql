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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'p[oó]bl[eé]t[eé]' OR  a.rut ~* 'p[oó]bl[eé]t[eé]' OR  lower(a.email) ~* 'p[oó]bl[eé]t[eé]' OR  text(a.id) ~* 'p[oó]bl[eé]t[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]ng[uú]l[oó]' OR  a.rut ~* '[aá]ng[uú]l[oó]' OR  lower(a.email) ~* '[aá]ng[uú]l[oó]' OR  text(a.id) ~* '[aá]ng[uú]l[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'cr[ií]st[ií][aá]n' OR  a.rut ~* 'cr[ií]st[ií][aá]n' OR  lower(a.email) ~* 'cr[ií]st[ií][aá]n' OR  text(a.id) ~* 'cr[ií]st[ií][aá]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]rc[eé]l[oó]' OR  a.rut ~* 'm[aá]rc[eé]l[oó]' OR  lower(a.email) ~* 'm[aá]rc[eé]l[oó]' OR  text(a.id) ~* 'm[aá]rc[eé]l[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER