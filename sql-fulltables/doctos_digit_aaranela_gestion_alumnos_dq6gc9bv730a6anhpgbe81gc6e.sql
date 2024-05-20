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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'ch[aá]v[eé]z' OR  a.rut ~* 'ch[aá]v[eé]z' OR  lower(a.email) ~* 'ch[aá]v[eé]z' OR  text(a.id) ~* 'ch[aá]v[eé]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[eé]r[eé]y' OR  a.rut ~* 's[eé]r[eé]y' OR  lower(a.email) ~* 's[eé]r[eé]y' OR  text(a.id) ~* 's[eé]r[eé]y' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[oó]ss[aá]n[aá]' OR  a.rut ~* 'r[oó]ss[aá]n[aá]' OR  lower(a.email) ~* 'r[oó]ss[aá]n[aá]' OR  text(a.id) ~* 'r[oó]ss[aá]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]rc[eé]l[aá]' OR  a.rut ~* 'm[aá]rc[eé]l[aá]' OR  lower(a.email) ~* 'm[aá]rc[eé]l[aá]' OR  text(a.id) ~* 'm[aá]rc[eé]l[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[ií][aá]' OR  a.rut ~* 'p[ií][aá]' OR  lower(a.email) ~* 'p[ií][aá]' OR  text(a.id) ~* 'p[ií][aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER