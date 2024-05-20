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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'p[aá]lm[aá]' OR  a.rut ~* 'p[aá]lm[aá]' OR  lower(a.email) ~* 'p[aá]lm[aá]' OR  text(a.id) ~* 'p[aá]lm[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]rn[eé]z' OR  a.rut ~* '[aá]rn[eé]z' OR  lower(a.email) ~* '[aá]rn[eé]z' OR  text(a.id) ~* '[aá]rn[eé]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'sh[ií]rl[eé]y' OR  a.rut ~* 'sh[ií]rl[eé]y' OR  lower(a.email) ~* 'sh[ií]rl[eé]y' OR  text(a.id) ~* 'sh[ií]rl[eé]y' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'k[aá]th[eé]r[ií]n[eé]' OR  a.rut ~* 'k[aá]th[eé]r[ií]n[eé]' OR  lower(a.email) ~* 'k[aá]th[eé]r[ií]n[eé]' OR  text(a.id) ~* 'k[aá]th[eé]r[ií]n[eé]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER