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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]rc[eé]l[aá]' OR  a.rut ~* 'm[aá]rc[eé]l[aá]' OR  lower(a.email) ~* 'm[aá]rc[eé]l[aá]' OR  text(a.id) ~* 'm[aá]rc[eé]l[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]l[eé]j[aá]ndr[aá]' OR  a.rut ~* '[aá]l[eé]j[aá]ndr[aá]' OR  lower(a.email) ~* '[aá]l[eé]j[aá]ndr[aá]' OR  text(a.id) ~* '[aá]l[eé]j[aá]ndr[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'f[eé]rn[aá]nd[eé]z' OR  a.rut ~* 'f[eé]rn[aá]nd[eé]z' OR  lower(a.email) ~* 'f[eé]rn[aá]nd[eé]z' OR  text(a.id) ~* 'f[eé]rn[aá]nd[eé]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[oó]rt[eé]s' OR  a.rut ~* 'c[oó]rt[eé]s' OR  lower(a.email) ~* 'c[oó]rt[eé]s' OR  text(a.id) ~* 'c[oó]rt[eé]s' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER