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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[uú]ll[oó][aá]' OR  a.rut ~* '[uú]ll[oó][aá]' OR  lower(a.email) ~* '[uú]ll[oó][aá]' OR  text(a.id) ~* '[uú]ll[oó][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[ií]st[eé]rn[aá]s' OR  a.rut ~* 'c[ií]st[eé]rn[aá]s' OR  lower(a.email) ~* 'c[ií]st[eé]rn[aá]s' OR  text(a.id) ~* 'c[ií]st[eé]rn[aá]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* '' OR  a.rut ~* '' OR  lower(a.email) ~* '' OR  text(a.id) ~* '' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]dr[ií][aá]n' OR  a.rut ~* '[aá]dr[ií][aá]n' OR  lower(a.email) ~* '[aá]dr[ií][aá]n' OR  text(a.id) ~* '[aá]dr[ií][aá]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* '' OR  a.rut ~* '' OR  lower(a.email) ~* '' OR  text(a.id) ~* '' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]st[eé]b[aá]n' OR  a.rut ~* '[eé]st[eé]b[aá]n' OR  lower(a.email) ~* '[eé]st[eé]b[aá]n' OR  text(a.id) ~* '[eé]st[eé]b[aá]n' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER