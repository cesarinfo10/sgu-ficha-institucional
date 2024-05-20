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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'p[aá][ií]n[eé]n' OR  a.rut ~* 'p[aá][ií]n[eé]n' OR  lower(a.email) ~* 'p[aá][ií]n[eé]n' OR  text(a.id) ~* 'p[aá][ií]n[eé]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[eé]r[eé]z' OR  a.rut ~* 'p[eé]r[eé]z' OR  lower(a.email) ~* 'p[eé]r[eé]z' OR  text(a.id) ~* 'p[eé]r[eé]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]r[ií][aá]' OR  a.rut ~* 'm[aá]r[ií][aá]' OR  lower(a.email) ~* 'm[aá]r[ií][aá]' OR  text(a.id) ~* 'm[aá]r[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]ng[eé]l[aá]' OR  a.rut ~* '[aá]ng[eé]l[aá]' OR  lower(a.email) ~* '[aá]ng[eé]l[aá]' OR  text(a.id) ~* '[aá]ng[eé]l[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER