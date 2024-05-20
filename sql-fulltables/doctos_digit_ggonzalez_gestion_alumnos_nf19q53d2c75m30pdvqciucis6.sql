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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]d[ií]th' OR  a.rut ~* '[eé]d[ií]th' OR  lower(a.email) ~* '[eé]d[ií]th' OR  text(a.id) ~* '[eé]d[ií]th' ) AND (lower(a.nombres||' '||a.apellidos) ~* 't[oó]b[aá]r' OR  a.rut ~* 't[oó]b[aá]r' OR  lower(a.email) ~* 't[oó]b[aá]r' OR  text(a.id) ~* 't[oó]b[aá]r' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER