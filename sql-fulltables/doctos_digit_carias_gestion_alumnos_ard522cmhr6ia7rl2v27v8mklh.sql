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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[ií]nf[aá]nt[eé]' OR  a.rut ~* '[ií]nf[aá]nt[eé]' OR  lower(a.email) ~* '[ií]nf[aá]nt[eé]' OR  text(a.id) ~* '[ií]nf[aá]nt[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[eé]d[ií]n[aá]' OR  a.rut ~* 'm[eé]d[ií]n[aá]' OR  lower(a.email) ~* 'm[eé]d[ií]n[aá]' OR  text(a.id) ~* 'm[eé]d[ií]n[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER