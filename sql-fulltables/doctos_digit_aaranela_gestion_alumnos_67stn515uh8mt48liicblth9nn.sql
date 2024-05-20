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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'j[uú][aá]n' OR  a.rut ~* 'j[uú][aá]n' OR  lower(a.email) ~* 'j[uú][aá]n' OR  text(a.id) ~* 'j[uú][aá]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]rl[oó]s' OR  a.rut ~* 'c[aá]rl[oó]s' OR  lower(a.email) ~* 'c[aá]rl[oó]s' OR  text(a.id) ~* 'c[aá]rl[oó]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[aá][aá]v[eé]dr[aá]' OR  a.rut ~* 's[aá][aá]v[eé]dr[aá]' OR  lower(a.email) ~* 's[aá][aá]v[eé]dr[aá]' OR  text(a.id) ~* 's[aá][aá]v[eé]dr[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER