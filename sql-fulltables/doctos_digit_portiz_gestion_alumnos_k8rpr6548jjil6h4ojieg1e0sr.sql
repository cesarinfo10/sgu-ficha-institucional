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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 't[eé]r[eé]s[aá]' OR  a.rut ~* 't[eé]r[eé]s[aá]' OR  lower(a.email) ~* 't[eé]r[eé]s[aá]' OR  text(a.id) ~* 't[eé]r[eé]s[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'd[eé]' OR  a.rut ~* 'd[eé]' OR  lower(a.email) ~* 'd[eé]' OR  text(a.id) ~* 'd[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'j[eé]s[uú]s' OR  a.rut ~* 'j[eé]s[uú]s' OR  lower(a.email) ~* 'j[eé]s[uú]s' OR  text(a.id) ~* 'j[eé]s[uú]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]r[aá]y[aá]' OR  a.rut ~* '[aá]r[aá]y[aá]' OR  lower(a.email) ~* '[aá]r[aá]y[aá]' OR  text(a.id) ~* '[aá]r[aá]y[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[ií]v[eé]r[aá]' OR  a.rut ~* 'r[ií]v[eé]r[aá]' OR  lower(a.email) ~* 'r[ií]v[eé]r[aá]' OR  text(a.id) ~* 'r[ií]v[eé]r[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER