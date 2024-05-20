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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'g[uú][aá]rd[aá]' OR  a.rut ~* 'g[uú][aá]rd[aá]' OR  lower(a.email) ~* 'g[uú][aá]rd[aá]' OR  text(a.id) ~* 'g[uú][aá]rd[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'h[eé]rr[eé]r[aá]' OR  a.rut ~* 'h[eé]rr[eé]r[aá]' OR  lower(a.email) ~* 'h[eé]rr[eé]r[aá]' OR  text(a.id) ~* 'h[eé]rr[eé]r[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'k[aá]th[eé]r[ií]n[eé]' OR  a.rut ~* 'k[aá]th[eé]r[ií]n[eé]' OR  lower(a.email) ~* 'k[aá]th[eé]r[ií]n[eé]' OR  text(a.id) ~* 'k[aá]th[eé]r[ií]n[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]lb[aá]ny' OR  a.rut ~* '[aá]lb[aá]ny' OR  lower(a.email) ~* '[aá]lb[aá]ny' OR  text(a.id) ~* '[aá]lb[aá]ny' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER