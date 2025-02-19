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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'v[aá]n' OR  a.rut ~* 'v[aá]n' OR  lower(a.email) ~* 'v[aá]n' OR  text(a.id) ~* 'v[aá]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[aá][uú]l' OR  a.rut ~* 'r[aá][uú]l' OR  lower(a.email) ~* 'r[aá][uú]l' OR  text(a.id) ~* 'r[aá][uú]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]rr[ií]ll[oó]' OR  a.rut ~* 'c[aá]rr[ií]ll[oó]' OR  lower(a.email) ~* 'c[aá]rr[ií]ll[oó]' OR  text(a.id) ~* 'c[aá]rr[ií]ll[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[oó]rd[oó]b[aá]' OR  a.rut ~* 'c[oó]rd[oó]b[aá]' OR  lower(a.email) ~* 'c[oó]rd[oó]b[aá]' OR  text(a.id) ~* 'c[oó]rd[oó]b[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER