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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[ií]gn[aá]c[ií][oó]' OR  a.rut ~* '[ií]gn[aá]c[ií][oó]' OR  lower(a.email) ~* '[ií]gn[aá]c[ií][oó]' OR  text(a.id) ~* '[ií]gn[aá]c[ií][oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[ií]sr[aá][eé]l' OR  a.rut ~* '[ií]sr[aá][eé]l' OR  lower(a.email) ~* '[ií]sr[aá][eé]l' OR  text(a.id) ~* '[ií]sr[aá][eé]l' )  AND a.carrera_actual IN (108,96,25,109,37,70,135,112,17)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER