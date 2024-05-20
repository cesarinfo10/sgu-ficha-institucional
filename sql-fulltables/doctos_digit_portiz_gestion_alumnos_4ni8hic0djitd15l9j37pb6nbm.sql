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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'chr[ií]st[oó]ph[eé]r' OR  a.rut ~* 'chr[ií]st[oó]ph[eé]r' OR  lower(a.email) ~* 'chr[ií]st[oó]ph[eé]r' OR  text(a.id) ~* 'chr[ií]st[oó]ph[eé]r' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[eé]r[eé][ií]r[aá]' OR  a.rut ~* 'p[eé]r[eé][ií]r[aá]' OR  lower(a.email) ~* 'p[eé]r[eé][ií]r[aá]' OR  text(a.id) ~* 'p[eé]r[eé][ií]r[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER