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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'h[eé]rvyn' OR  a.rut ~* 'h[eé]rvyn' OR  lower(a.email) ~* 'h[eé]rvyn' OR  text(a.id) ~* 'h[eé]rvyn' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'j[oó]n[aá]th[aá]n' OR  a.rut ~* 'j[oó]n[aá]th[aá]n' OR  lower(a.email) ~* 'j[oó]n[aá]th[aá]n' OR  text(a.id) ~* 'j[oó]n[aá]th[aá]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]g[uú]d[eé]l[oó]' OR  a.rut ~* '[aá]g[uú]d[eé]l[oó]' OR  lower(a.email) ~* '[aá]g[uú]d[eé]l[oó]' OR  text(a.id) ~* '[aá]g[uú]d[eé]l[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[aá]l[eé]nc[ií][aá]' OR  a.rut ~* 'v[aá]l[eé]nc[ií][aá]' OR  lower(a.email) ~* 'v[aá]l[eé]nc[ií][aá]' OR  text(a.id) ~* 'v[aá]l[eé]nc[ií][aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER