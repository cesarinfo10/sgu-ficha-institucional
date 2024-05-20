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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]r[ií]lyn' OR  a.rut ~* 'm[aá]r[ií]lyn' OR  lower(a.email) ~* 'm[aá]r[ií]lyn' OR  text(a.id) ~* 'm[aá]r[ií]lyn' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'gr[aá]c[eé]' OR  a.rut ~* 'gr[aá]c[eé]' OR  lower(a.email) ~* 'gr[aá]c[eé]' OR  text(a.id) ~* 'gr[aá]c[eé]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER