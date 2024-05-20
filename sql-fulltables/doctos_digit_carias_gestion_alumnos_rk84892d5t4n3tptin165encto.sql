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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'c[uú][eé]v[aá]s' OR  a.rut ~* 'c[uú][eé]v[aá]s' OR  lower(a.email) ~* 'c[uú][eé]v[aá]s' OR  text(a.id) ~* 'c[uú][eé]v[aá]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'q[uú][ií]ñ[oó]n[eé]s' OR  a.rut ~* 'q[uú][ií]ñ[oó]n[eé]s' OR  lower(a.email) ~* 'q[uú][ií]ñ[oó]n[eé]s' OR  text(a.id) ~* 'q[uú][ií]ñ[oó]n[eé]s' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER