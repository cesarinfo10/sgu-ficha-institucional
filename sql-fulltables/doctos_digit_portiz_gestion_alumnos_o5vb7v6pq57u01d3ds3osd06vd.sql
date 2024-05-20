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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'j[uú]l[ií]ss[aá]' OR  a.rut ~* 'j[uú]l[ií]ss[aá]' OR  lower(a.email) ~* 'j[uú]l[ií]ss[aá]' OR  text(a.id) ~* 'j[uú]l[ií]ss[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'k[aá]t[ií]n[aá]' OR  a.rut ~* 'k[aá]t[ií]n[aá]' OR  lower(a.email) ~* 'k[aá]t[ií]n[aá]' OR  text(a.id) ~* 'k[aá]t[ií]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'f[ií]ch[eé]r' OR  a.rut ~* 'f[ií]ch[eé]r' OR  lower(a.email) ~* 'f[ií]ch[eé]r' OR  text(a.id) ~* 'f[ií]ch[eé]r' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[oó]rr[aá]d[ií]n[ií]' OR  a.rut ~* 'c[oó]rr[aá]d[ií]n[ií]' OR  lower(a.email) ~* 'c[oó]rr[aá]d[ií]n[ií]' OR  text(a.id) ~* 'c[oó]rr[aá]d[ií]n[ií]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER