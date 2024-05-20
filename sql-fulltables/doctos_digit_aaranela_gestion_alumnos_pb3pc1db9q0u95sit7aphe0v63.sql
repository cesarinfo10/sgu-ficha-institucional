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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'p[ií]n[oó]ch[eé]t' OR  a.rut ~* 'p[ií]n[oó]ch[eé]t' OR  lower(a.email) ~* 'p[ií]n[oó]ch[eé]t' OR  text(a.id) ~* 'p[ií]n[oó]ch[eé]t' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]v[ií][eé]r[eé]s' OR  a.rut ~* 'c[aá]v[ií][eé]r[eé]s' OR  lower(a.email) ~* 'c[aá]v[ií][eé]r[eé]s' OR  text(a.id) ~* 'c[aá]v[ií][eé]r[eé]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[aá]f[aá][eé]l' OR  a.rut ~* 'r[aá]f[aá][eé]l' OR  lower(a.email) ~* 'r[aá]f[aá][eé]l' OR  text(a.id) ~* 'r[aá]f[aá][eé]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[aá]tr[ií]c[ií][oó]' OR  a.rut ~* 'p[aá]tr[ií]c[ií][oó]' OR  lower(a.email) ~* 'p[aá]tr[ií]c[ií][oó]' OR  text(a.id) ~* 'p[aá]tr[ií]c[ií][oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER