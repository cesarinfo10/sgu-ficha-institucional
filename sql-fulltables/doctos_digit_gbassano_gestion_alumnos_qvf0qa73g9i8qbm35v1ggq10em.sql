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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'j[uú][aá]n' OR  a.rut ~* 'j[uú][aá]n' OR  lower(a.email) ~* 'j[uú][aá]n' OR  text(a.id) ~* 'j[uú][aá]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* '' OR  a.rut ~* '' OR  lower(a.email) ~* '' OR  text(a.id) ~* '' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[oó]r[eé]n[oó]' OR  a.rut ~* 'm[oó]r[eé]n[oó]' OR  lower(a.email) ~* 'm[oó]r[eé]n[oó]' OR  text(a.id) ~* 'm[oó]r[eé]n[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[uú][eé]nt[eé]s' OR  a.rut ~* 'p[uú][eé]nt[eé]s' OR  lower(a.email) ~* 'p[uú][eé]nt[eé]s' OR  text(a.id) ~* 'p[uú][eé]nt[eé]s' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER