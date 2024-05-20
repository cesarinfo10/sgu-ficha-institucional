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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'm[eé]n[aá]' OR  a.rut ~* 'm[eé]n[aá]' OR  lower(a.email) ~* 'm[eé]n[aá]' OR  text(a.id) ~* 'm[eé]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[uú]ñ[oó]z' OR  a.rut ~* 'm[uú]ñ[oó]z' OR  lower(a.email) ~* 'm[uú]ñ[oó]z' OR  text(a.id) ~* 'm[uú]ñ[oó]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* '' OR  a.rut ~* '' OR  lower(a.email) ~* '' OR  text(a.id) ~* '' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]r[oó]l[ií]n[aá]' OR  a.rut ~* 'c[aá]r[oó]l[ií]n[aá]' OR  lower(a.email) ~* 'c[aá]r[oó]l[ií]n[aá]' OR  text(a.id) ~* 'c[aá]r[oó]l[ií]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]ndr[eé][aá]' OR  a.rut ~* '[aá]ndr[eé][aá]' OR  lower(a.email) ~* '[aá]ndr[eé][aá]' OR  text(a.id) ~* '[aá]ndr[eé][aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER