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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'b[aá]rb[aá]r[aá]' OR  a.rut ~* 'b[aá]rb[aá]r[aá]' OR  lower(a.email) ~* 'b[aá]rb[aá]r[aá]' OR  text(a.id) ~* 'b[aá]rb[aá]r[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]l[eé]j[aá]ndr[aá]' OR  a.rut ~* '[aá]l[eé]j[aá]ndr[aá]' OR  lower(a.email) ~* '[aá]l[eé]j[aá]ndr[aá]' OR  text(a.id) ~* '[aá]l[eé]j[aá]ndr[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'j[uú][aá]r[eé]z' OR  a.rut ~* 'j[uú][aá]r[eé]z' OR  lower(a.email) ~* 'j[uú][aá]r[eé]z' OR  text(a.id) ~* 'j[uú][aá]r[eé]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[eé]l[eé]z' OR  a.rut ~* 'v[eé]l[eé]z' OR  lower(a.email) ~* 'v[eé]l[eé]z' OR  text(a.id) ~* 'v[eé]l[eé]z' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER