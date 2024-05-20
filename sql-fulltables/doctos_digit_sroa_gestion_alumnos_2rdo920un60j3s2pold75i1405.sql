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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]lf[oó]ns[oó]' OR  a.rut ~* '[aá]lf[oó]ns[oó]' OR  lower(a.email) ~* '[aá]lf[oó]ns[oó]' OR  text(a.id) ~* '[aá]lf[oó]ns[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'g[uú][eé]rr[eé]r[oó]' OR  a.rut ~* 'g[uú][eé]rr[eé]r[oó]' OR  lower(a.email) ~* 'g[uú][eé]rr[eé]r[oó]' OR  text(a.id) ~* 'g[uú][eé]rr[eé]r[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[oó]n[aá]rd[eé]s' OR  a.rut ~* 'm[oó]n[aá]rd[eé]s' OR  lower(a.email) ~* 'm[oó]n[aá]rd[eé]s' OR  text(a.id) ~* 'm[oó]n[aá]rd[eé]s' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER