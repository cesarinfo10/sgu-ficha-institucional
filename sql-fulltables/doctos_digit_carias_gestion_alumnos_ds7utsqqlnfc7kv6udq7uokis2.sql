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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'g[uú][ií]ll[eé]rm[oó]' OR  a.rut ~* 'g[uú][ií]ll[eé]rm[oó]' OR  lower(a.email) ~* 'g[uú][ií]ll[eé]rm[oó]' OR  text(a.id) ~* 'g[uú][ií]ll[eé]rm[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'b[aá]rr[eé]r[aá]' OR  a.rut ~* 'b[aá]rr[eé]r[aá]' OR  lower(a.email) ~* 'b[aá]rr[eé]r[aá]' OR  text(a.id) ~* 'b[aá]rr[eé]r[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER