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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]c[aá]r[eé]n[aá]' OR  a.rut ~* 'm[aá]c[aá]r[eé]n[aá]' OR  lower(a.email) ~* 'm[aá]c[aá]r[eé]n[aá]' OR  text(a.id) ~* 'm[aá]c[aá]r[eé]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]st[ií]ll[oó]' OR  a.rut ~* 'c[aá]st[ií]ll[oó]' OR  lower(a.email) ~* 'c[aá]st[ií]ll[oó]' OR  text(a.id) ~* 'c[aá]st[ií]ll[oó]' )  AND a.carrera_actual IN (108,96,25,109,37,70,135,112,17,153)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER