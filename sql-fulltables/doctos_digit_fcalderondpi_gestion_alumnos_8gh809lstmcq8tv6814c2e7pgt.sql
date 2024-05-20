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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]dr[ií][eé]l' OR  a.rut ~* '[aá]dr[ií][eé]l' OR  lower(a.email) ~* '[aá]dr[ií][eé]l' OR  text(a.id) ~* '[aá]dr[ií][eé]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]m[aá]n[ií]' OR  a.rut ~* 'm[aá]m[aá]n[ií]' OR  lower(a.email) ~* 'm[aá]m[aá]n[ií]' OR  text(a.id) ~* 'm[aá]m[aá]n[ií]' )  AND a.carrera_actual IN (59,57,27,28,33,54,56,31,58,60,55,30,32,29,76,79,82,81,83,78,80,38,115,114,77,125)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER