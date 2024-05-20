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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]r[ií][eé]l' OR  a.rut ~* '[aá]r[ií][eé]l' OR  lower(a.email) ~* '[aá]r[ií][eé]l' OR  text(a.id) ~* '[aá]r[ií][eé]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[ií]c[eé]nt[eé]' OR  a.rut ~* 'v[ií]c[eé]nt[eé]' OR  lower(a.email) ~* 'v[ií]c[eé]nt[eé]' OR  text(a.id) ~* 'v[ií]c[eé]nt[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[uú]n[ií]z[aá]g[aá]' OR  a.rut ~* 'm[uú]n[ií]z[aá]g[aá]' OR  lower(a.email) ~* 'm[uú]n[ií]z[aá]g[aá]' OR  text(a.id) ~* 'm[uú]n[ií]z[aá]g[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[eé]z[aá]' OR  a.rut ~* 'm[eé]z[aá]' OR  lower(a.email) ~* 'm[eé]z[aá]' OR  text(a.id) ~* 'm[eé]z[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER