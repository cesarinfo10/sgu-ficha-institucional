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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'm[ií]g[uú][eé]l' OR  a.rut ~* 'm[ií]g[uú][eé]l' OR  lower(a.email) ~* 'm[ií]g[uú][eé]l' OR  text(a.id) ~* 'm[ií]g[uú][eé]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]ng[eé]l' OR  a.rut ~* '[aá]ng[eé]l' OR  lower(a.email) ~* '[aá]ng[eé]l' OR  text(a.id) ~* '[aá]ng[eé]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'n[uú]ñ[eé]z' OR  a.rut ~* 'n[uú]ñ[eé]z' OR  lower(a.email) ~* 'n[uú]ñ[eé]z' OR  text(a.id) ~* 'n[uú]ñ[eé]z' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER