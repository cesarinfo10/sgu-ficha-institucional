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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'k[aá]rl[aá]' OR  a.rut ~* 'k[aá]rl[aá]' OR  lower(a.email) ~* 'k[aá]rl[aá]' OR  text(a.id) ~* 'k[aá]rl[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'l[ií]ss[eé]tt[eé]' OR  a.rut ~* 'l[ií]ss[eé]tt[eé]' OR  lower(a.email) ~* 'l[ií]ss[eé]tt[eé]' OR  text(a.id) ~* 'l[ií]ss[eé]tt[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]b[eé]l[eé]' OR  a.rut ~* '[aá]b[eé]l[eé]' OR  lower(a.email) ~* '[aá]b[eé]l[eé]' OR  text(a.id) ~* '[aá]b[eé]l[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[aá]ld[eé]b[eé]n[ií]t[oó]' OR  a.rut ~* 'v[aá]ld[eé]b[eé]n[ií]t[oó]' OR  lower(a.email) ~* 'v[aá]ld[eé]b[eé]n[ií]t[oó]' OR  text(a.id) ~* 'v[aá]ld[eé]b[eé]n[ií]t[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER