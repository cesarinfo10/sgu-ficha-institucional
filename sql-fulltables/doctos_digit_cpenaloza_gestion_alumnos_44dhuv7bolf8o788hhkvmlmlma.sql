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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[ií]ngr[ií]d' OR  a.rut ~* '[ií]ngr[ií]d' OR  lower(a.email) ~* '[ií]ngr[ií]d' OR  text(a.id) ~* '[ií]ngr[ií]d' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]st[oó]rg[aá]' OR  a.rut ~* '[aá]st[oó]rg[aá]' OR  lower(a.email) ~* '[aá]st[oó]rg[aá]' OR  text(a.id) ~* '[aá]st[oó]rg[aá]' )  AND a.carrera_actual IN (101,62,26,88,18,3,36,67)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER