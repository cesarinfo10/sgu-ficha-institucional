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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'fl[oó]r[eé]s' OR  a.rut ~* 'fl[oó]r[eé]s' OR  lower(a.email) ~* 'fl[oó]r[eé]s' OR  text(a.id) ~* 'fl[oó]r[eé]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'f[uú][eé]nt[eé]s' OR  a.rut ~* 'f[uú][eé]nt[eé]s' OR  lower(a.email) ~* 'f[uú][eé]nt[eé]s' OR  text(a.id) ~* 'f[uú][eé]nt[eé]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[ií]c[aá]rd[oó]' OR  a.rut ~* 'r[ií]c[aá]rd[oó]' OR  lower(a.email) ~* 'r[ií]c[aá]rd[oó]' OR  text(a.id) ~* 'r[ií]c[aá]rd[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]lb[eé]rt[oó]' OR  a.rut ~* '[aá]lb[eé]rt[oó]' OR  lower(a.email) ~* '[aá]lb[eé]rt[oó]' OR  text(a.id) ~* '[aá]lb[eé]rt[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER