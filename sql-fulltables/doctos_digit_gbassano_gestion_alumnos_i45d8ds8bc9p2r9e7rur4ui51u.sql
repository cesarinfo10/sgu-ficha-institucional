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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'gl[oó]r[ií][aá]' OR  a.rut ~* 'gl[oó]r[ií][aá]' OR  lower(a.email) ~* 'gl[oó]r[ií][aá]' OR  text(a.id) ~* 'gl[oó]r[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[uú][eé]bl[aá]' OR  a.rut ~* 'p[uú][eé]bl[aá]' OR  lower(a.email) ~* 'p[uú][eé]bl[aá]' OR  text(a.id) ~* 'p[uú][eé]bl[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'b[uú]st[aá]m[aá]nt[eé]' OR  a.rut ~* 'b[uú]st[aá]m[aá]nt[eé]' OR  lower(a.email) ~* 'b[uú]st[aá]m[aá]nt[eé]' OR  text(a.id) ~* 'b[uú]st[aá]m[aá]nt[eé]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER