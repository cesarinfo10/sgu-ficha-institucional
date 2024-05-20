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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'p[aá][ií]ll[aá]n' OR  a.rut ~* 'p[aá][ií]ll[aá]n' OR  lower(a.email) ~* 'p[aá][ií]ll[aá]n' OR  text(a.id) ~* 'p[aá][ií]ll[aá]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]lt[aá]m[ií]r[aá]n[oó]' OR  a.rut ~* '[aá]lt[aá]m[ií]r[aá]n[oó]' OR  lower(a.email) ~* '[aá]lt[aá]m[ií]r[aá]n[oó]' OR  text(a.id) ~* '[aá]lt[aá]m[ií]r[aá]n[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'cynt[ií][aá]' OR  a.rut ~* 'cynt[ií][aá]' OR  lower(a.email) ~* 'cynt[ií][aá]' OR  text(a.id) ~* 'cynt[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[aá]n[eé]ss[aá]' OR  a.rut ~* 'v[aá]n[eé]ss[aá]' OR  lower(a.email) ~* 'v[aá]n[eé]ss[aá]' OR  text(a.id) ~* 'v[aá]n[eé]ss[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER