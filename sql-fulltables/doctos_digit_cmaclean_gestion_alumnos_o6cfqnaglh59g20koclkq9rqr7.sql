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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá][uú]r[ií]c[ií][oó]' OR  a.rut ~* 'm[aá][uú]r[ií]c[ií][oó]' OR  lower(a.email) ~* 'm[aá][uú]r[ií]c[ií][oó]' OR  text(a.id) ~* 'm[aá][uú]r[ií]c[ií][oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]l[eé]j[aá]ndr[oó]' OR  a.rut ~* '[aá]l[eé]j[aá]ndr[oó]' OR  lower(a.email) ~* '[aá]l[eé]j[aá]ndr[oó]' OR  text(a.id) ~* '[aá]l[eé]j[aá]ndr[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[uú]ñ[oó]z' OR  a.rut ~* 'm[uú]ñ[oó]z' OR  lower(a.email) ~* 'm[uú]ñ[oó]z' OR  text(a.id) ~* 'm[uú]ñ[oó]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'g[oó]nz[aá]l[eé]' OR  a.rut ~* 'g[oó]nz[aá]l[eé]' OR  lower(a.email) ~* 'g[oó]nz[aá]l[eé]' OR  text(a.id) ~* 'g[oó]nz[aá]l[eé]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER