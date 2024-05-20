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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'd[aá]n[ií][eé]ll[aá]' OR  a.rut ~* 'd[aá]n[ií][eé]ll[aá]' OR  lower(a.email) ~* 'd[aá]n[ií][eé]ll[aá]' OR  text(a.id) ~* 'd[aá]n[ií][eé]ll[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]l[eé]x[aá]ndr[aá]' OR  a.rut ~* '[aá]l[eé]x[aá]ndr[aá]' OR  lower(a.email) ~* '[aá]l[eé]x[aá]ndr[aá]' OR  text(a.id) ~* '[aá]l[eé]x[aá]ndr[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[ií]n[oó]' OR  a.rut ~* 'p[ií]n[oó]' OR  lower(a.email) ~* 'p[ií]n[oó]' OR  text(a.id) ~* 'p[ií]n[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]sc[oó]b[aá]r' OR  a.rut ~* '[eé]sc[oó]b[aá]r' OR  lower(a.email) ~* '[eé]sc[oó]b[aá]r' OR  text(a.id) ~* '[eé]sc[oó]b[aá]r' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER