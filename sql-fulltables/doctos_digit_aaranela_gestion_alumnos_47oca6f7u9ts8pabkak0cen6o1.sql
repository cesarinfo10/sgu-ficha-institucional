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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]c[eé]r[eé]s' OR  a.rut ~* 'c[aá]c[eé]r[eé]s' OR  lower(a.email) ~* 'c[aá]c[eé]r[eé]s' OR  text(a.id) ~* 'c[aá]c[eé]r[eé]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[eé]r[eé]z' OR  a.rut ~* 'p[eé]r[eé]z' OR  lower(a.email) ~* 'p[eé]r[eé]z' OR  text(a.id) ~* 'p[eé]r[eé]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[eé]n[eé]' OR  a.rut ~* 'r[eé]n[eé]' OR  lower(a.email) ~* 'r[eé]n[eé]' OR  text(a.id) ~* 'r[eé]n[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]n[ií]b[aá]l' OR  a.rut ~* '[aá]n[ií]b[aá]l' OR  lower(a.email) ~* '[aá]n[ií]b[aá]l' OR  text(a.id) ~* '[aá]n[ií]b[aá]l' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER