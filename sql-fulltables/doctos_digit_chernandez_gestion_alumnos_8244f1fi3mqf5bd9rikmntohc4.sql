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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]r[ií][aá]' OR  a.rut ~* 'm[aá]r[ií][aá]' OR  lower(a.email) ~* 'm[aá]r[ií][aá]' OR  text(a.id) ~* 'm[aá]r[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[eé]l[eé]st[eé]' OR  a.rut ~* 'c[eé]l[eé]st[eé]' OR  lower(a.email) ~* 'c[eé]l[eé]st[eé]' OR  text(a.id) ~* 'c[eé]l[eé]st[eé]' )  AND a.carrera_actual IN (94,97,104,110,98,105,102,91,15,103,126,4,157)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER