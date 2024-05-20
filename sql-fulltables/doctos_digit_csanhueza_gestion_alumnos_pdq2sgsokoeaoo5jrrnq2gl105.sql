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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'v[aá]l[eé]nt[ií]n' OR  a.rut ~* 'v[aá]l[eé]nt[ií]n' OR  lower(a.email) ~* 'v[aá]l[eé]nt[ií]n' OR  text(a.id) ~* 'v[aá]l[eé]nt[ií]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]st[eé]b[aá]n' OR  a.rut ~* '[eé]st[eé]b[aá]n' OR  lower(a.email) ~* '[eé]st[eé]b[aá]n' OR  text(a.id) ~* '[eé]st[eé]b[aá]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[eé]r[aá]' OR  a.rut ~* 'v[eé]r[aá]' OR  lower(a.email) ~* 'v[eé]r[aá]' OR  text(a.id) ~* 'v[eé]r[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'f[uú][eé]nt[eé]s' OR  a.rut ~* 'f[uú][eé]nt[eé]s' OR  lower(a.email) ~* 'f[uú][eé]nt[eé]s' OR  text(a.id) ~* 'f[uú][eé]nt[eé]s' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER