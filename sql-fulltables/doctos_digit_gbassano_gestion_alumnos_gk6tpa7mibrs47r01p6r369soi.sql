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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'v[aá]l[eé]nt[ií]n[aá]' OR  a.rut ~* 'v[aá]l[eé]nt[ií]n[aá]' OR  lower(a.email) ~* 'v[aá]l[eé]nt[ií]n[aá]' OR  text(a.id) ~* 'v[aá]l[eé]nt[ií]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[oó]t[oó]' OR  a.rut ~* 's[oó]t[oó]' OR  lower(a.email) ~* 's[oó]t[oó]' OR  text(a.id) ~* 's[oó]t[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[ií]t[uú]rr[ií][aá]g[aá]' OR  a.rut ~* '[ií]t[uú]rr[ií][aá]g[aá]' OR  lower(a.email) ~* '[ií]t[uú]rr[ií][aá]g[aá]' OR  text(a.id) ~* '[ií]t[uú]rr[ií][aá]g[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER