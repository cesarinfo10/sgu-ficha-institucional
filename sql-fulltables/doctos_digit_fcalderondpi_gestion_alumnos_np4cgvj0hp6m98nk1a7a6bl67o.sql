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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'k[aá]th[eé]r[ií]n[eé]' OR  a.rut ~* 'k[aá]th[eé]r[ií]n[eé]' OR  lower(a.email) ~* 'k[aá]th[eé]r[ií]n[eé]' OR  text(a.id) ~* 'k[aá]th[eé]r[ií]n[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'g[ií][oó]v[aá]nn[aá]' OR  a.rut ~* 'g[ií][oó]v[aá]nn[aá]' OR  lower(a.email) ~* 'g[ií][oó]v[aá]nn[aá]' OR  text(a.id) ~* 'g[ií][oó]v[aá]nn[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 't[oó]ff[oó]l[ií]' OR  a.rut ~* 't[oó]ff[oó]l[ií]' OR  lower(a.email) ~* 't[oó]ff[oó]l[ií]' OR  text(a.id) ~* 't[oó]ff[oó]l[ií]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]sp[eé]' OR  a.rut ~* '[aá]sp[eé]' OR  lower(a.email) ~* '[aá]sp[eé]' OR  text(a.id) ~* '[aá]sp[eé]' )  AND a.carrera_actual IN (59,57,27,28,33,54,56,31,58,60,55,30,32,29,76,79,82,81,83,78,80,38,115,114,77,125)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER