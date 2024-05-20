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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'm[ií]ll[eé]nk[oó]' OR  a.rut ~* 'm[ií]ll[eé]nk[oó]' OR  lower(a.email) ~* 'm[ií]ll[eé]nk[oó]' OR  text(a.id) ~* 'm[ií]ll[eé]nk[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'n[aá]d[ií]n[ií]c' OR  a.rut ~* 'n[aá]d[ií]n[ií]c' OR  lower(a.email) ~* 'n[aá]d[ií]n[ií]c' OR  text(a.id) ~* 'n[aá]d[ií]n[ií]c' )  AND a.carrera_actual IN (95,100,2,69,1,19)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER