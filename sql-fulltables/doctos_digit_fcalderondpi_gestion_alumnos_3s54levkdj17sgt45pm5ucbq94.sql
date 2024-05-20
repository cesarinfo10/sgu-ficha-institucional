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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'fr[eé]ddy' OR  a.rut ~* 'fr[eé]ddy' OR  lower(a.email) ~* 'fr[eé]ddy' OR  text(a.id) ~* 'fr[eé]ddy' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]m[ií]lc[ií][eé]d' OR  a.rut ~* '[eé]m[ií]lc[ií][eé]d' OR  lower(a.email) ~* '[eé]m[ií]lc[ií][eé]d' OR  text(a.id) ~* '[eé]m[ií]lc[ií][eé]d' )  AND a.carrera_actual IN (59,57,33,54,56,31,58,60,55,30,32,29,76,79,82,81,83,78,80,27,28,38,115,77,125,114,147,148,161,160,163)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER