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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'b[aá]rb[aá]r[aá]' OR  a.rut ~* 'b[aá]rb[aá]r[aá]' OR  lower(a.email) ~* 'b[aá]rb[aá]r[aá]' OR  text(a.id) ~* 'b[aá]rb[aá]r[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'd[aá]n[ií][eé]l[aá]' OR  a.rut ~* 'd[aá]n[ií][eé]l[aá]' OR  lower(a.email) ~* 'd[aá]n[ií][eé]l[aá]' OR  text(a.id) ~* 'd[aá]n[ií][eé]l[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'h[eé]rr[eé]r[aá]' OR  a.rut ~* 'h[eé]rr[eé]r[aá]' OR  lower(a.email) ~* 'h[eé]rr[eé]r[aá]' OR  text(a.id) ~* 'h[eé]rr[eé]r[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'g[aá]rc[ií][aá]' OR  a.rut ~* 'g[aá]rc[ií][aá]' OR  lower(a.email) ~* 'g[aá]rc[ií][aá]' OR  text(a.id) ~* 'g[aá]rc[ií][aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER