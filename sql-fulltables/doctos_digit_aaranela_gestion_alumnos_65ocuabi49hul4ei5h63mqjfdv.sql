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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'r[ií]c[aá]rd[oó]' OR  a.rut ~* 'r[ií]c[aá]rd[oó]' OR  lower(a.email) ~* 'r[ií]c[aá]rd[oó]' OR  text(a.id) ~* 'r[ií]c[aá]rd[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[uú]ñ[oó]z' OR  a.rut ~* 'm[uú]ñ[oó]z' OR  lower(a.email) ~* 'm[uú]ñ[oó]z' OR  text(a.id) ~* 'm[uú]ñ[oó]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'b[uú]st[aá]m[aá]nt[eé]' OR  a.rut ~* 'b[uú]st[aá]m[aá]nt[eé]' OR  lower(a.email) ~* 'b[uú]st[aá]m[aá]nt[eé]' OR  text(a.id) ~* 'b[uú]st[aá]m[aá]nt[eé]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER