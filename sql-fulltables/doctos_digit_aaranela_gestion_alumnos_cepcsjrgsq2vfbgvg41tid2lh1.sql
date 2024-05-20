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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[oó]ss[aá]nd[oó]n' OR  a.rut ~* '[oó]ss[aá]nd[oó]n' OR  lower(a.email) ~* '[oó]ss[aá]nd[oó]n' OR  text(a.id) ~* '[oó]ss[aá]nd[oó]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]sp[ií]n[oó]z[aá]' OR  a.rut ~* '[eé]sp[ií]n[oó]z[aá]' OR  lower(a.email) ~* '[eé]sp[ií]n[oó]z[aá]' OR  text(a.id) ~* '[eé]sp[ií]n[oó]z[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'h[uú]g[oó]' OR  a.rut ~* 'h[uú]g[oó]' OR  lower(a.email) ~* 'h[uú]g[oó]' OR  text(a.id) ~* 'h[uú]g[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]nr[ií]q[uú][eé]' OR  a.rut ~* '[eé]nr[ií]q[uú][eé]' OR  lower(a.email) ~* '[eé]nr[ií]q[uú][eé]' OR  text(a.id) ~* '[eé]nr[ií]q[uú][eé]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER