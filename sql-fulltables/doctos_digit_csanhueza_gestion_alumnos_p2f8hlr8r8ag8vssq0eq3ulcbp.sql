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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'r[aá]f[aá][eé]l' OR  a.rut ~* 'r[aá]f[aá][eé]l' OR  lower(a.email) ~* 'r[aá]f[aá][eé]l' OR  text(a.id) ~* 'r[aá]f[aá][eé]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'j[eé]s[uú]s' OR  a.rut ~* 'j[eé]s[uú]s' OR  lower(a.email) ~* 'j[eé]s[uú]s' OR  text(a.id) ~* 'j[eé]s[uú]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá][eé]rt[eé]n' OR  a.rut ~* 'm[aá][eé]rt[eé]n' OR  lower(a.email) ~* 'm[aá][eé]rt[eé]n' OR  text(a.id) ~* 'm[aá][eé]rt[eé]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'j[aá]c[aá]s' OR  a.rut ~* 'j[aá]c[aá]s' OR  lower(a.email) ~* 'j[aá]c[aá]s' OR  text(a.id) ~* 'j[aá]c[aá]s' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER