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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'b[aá]h[aá]m[oó]nd[eé]s' OR  a.rut ~* 'b[aá]h[aá]m[oó]nd[eé]s' OR  lower(a.email) ~* 'b[aá]h[aá]m[oó]nd[eé]s' OR  text(a.id) ~* 'b[aá]h[aá]m[oó]nd[eé]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[oó]t[oó]' OR  a.rut ~* 's[oó]t[oó]' OR  lower(a.email) ~* 's[oó]t[oó]' OR  text(a.id) ~* 's[oó]t[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'cr[ií]st[ií][aá]n' OR  a.rut ~* 'cr[ií]st[ií][aá]n' OR  lower(a.email) ~* 'cr[ií]st[ií][aá]n' OR  text(a.id) ~* 'cr[ií]st[ií][aá]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]lb[eé]rt[oó]' OR  a.rut ~* '[aá]lb[eé]rt[oó]' OR  lower(a.email) ~* '[aá]lb[eé]rt[oó]' OR  text(a.id) ~* '[aá]lb[eé]rt[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER