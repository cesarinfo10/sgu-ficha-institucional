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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]r[oó]l[ií]n[aá]' OR  a.rut ~* 'c[aá]r[oó]l[ií]n[aá]' OR  lower(a.email) ~* 'c[aá]r[oó]l[ií]n[aá]' OR  text(a.id) ~* 'c[aá]r[oó]l[ií]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[ií]ct[oó]r[ií][aá]' OR  a.rut ~* 'v[ií]ct[oó]r[ií][aá]' OR  lower(a.email) ~* 'v[ií]ct[oó]r[ií][aá]' OR  text(a.id) ~* 'v[ií]ct[oó]r[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]ylw[ií]n' OR  a.rut ~* '[aá]ylw[ií]n' OR  lower(a.email) ~* '[aá]ylw[ií]n' OR  text(a.id) ~* '[aá]ylw[ií]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'l[aá]g[oó]s' OR  a.rut ~* 'l[aá]g[oó]s' OR  lower(a.email) ~* 'l[aá]g[oó]s' OR  text(a.id) ~* 'l[aá]g[oó]s' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER