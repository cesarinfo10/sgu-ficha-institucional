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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[ií]v[oó]nn[eé]' OR  a.rut ~* '[ií]v[oó]nn[eé]' OR  lower(a.email) ~* '[ií]v[oó]nn[eé]' OR  text(a.id) ~* '[ií]v[oó]nn[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'n[aá]t[aá]l[ií][aá]' OR  a.rut ~* 'n[aá]t[aá]l[ií][aá]' OR  lower(a.email) ~* 'n[aá]t[aá]l[ií][aá]' OR  text(a.id) ~* 'n[aá]t[aá]l[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[aá]gr[eé]d[oó]' OR  a.rut ~* 's[aá]gr[eé]d[oó]' OR  lower(a.email) ~* 's[aá]gr[eé]d[oó]' OR  text(a.id) ~* 's[aá]gr[eé]d[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[uú]b[ií]l[aá]r' OR  a.rut ~* 'r[uú]b[ií]l[aá]r' OR  lower(a.email) ~* 'r[uú]b[ií]l[aá]r' OR  text(a.id) ~* 'r[uú]b[ií]l[aá]r' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER