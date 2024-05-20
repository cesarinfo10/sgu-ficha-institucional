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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'l[oó]rc[aá]' OR  a.rut ~* 'l[oó]rc[aá]' OR  lower(a.email) ~* 'l[oó]rc[aá]' OR  text(a.id) ~* 'l[oó]rc[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[eé]r[ií]n[oó]' OR  a.rut ~* 'm[eé]r[ií]n[oó]' OR  lower(a.email) ~* 'm[eé]r[ií]n[oó]' OR  text(a.id) ~* 'm[eé]r[ií]n[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[aá][uú]l' OR  a.rut ~* 'r[aá][uú]l' OR  lower(a.email) ~* 'r[aá][uú]l' OR  text(a.id) ~* 'r[aá][uú]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]d[uú][aá]rd[oó]' OR  a.rut ~* '[eé]d[uú][aá]rd[oó]' OR  lower(a.email) ~* '[eé]d[uú][aá]rd[oó]' OR  text(a.id) ~* '[eé]d[uú][aá]rd[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER