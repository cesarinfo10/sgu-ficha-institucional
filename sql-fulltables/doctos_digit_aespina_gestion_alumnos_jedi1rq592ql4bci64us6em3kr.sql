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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[oó]p[aá]z[oó]' OR  a.rut ~* '[oó]p[aá]z[oó]' OR  lower(a.email) ~* '[oó]p[aá]z[oó]' OR  text(a.id) ~* '[oó]p[aá]z[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[uú]rq[uú][ií]z[aá]' OR  a.rut ~* '[uú]rq[uú][ií]z[aá]' OR  lower(a.email) ~* '[uú]rq[uú][ií]z[aá]' OR  text(a.id) ~* '[uú]rq[uú][ií]z[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'd[aá]l[ií]l[aá]' OR  a.rut ~* 'd[aá]l[ií]l[aá]' OR  lower(a.email) ~* 'd[aá]l[ií]l[aá]' OR  text(a.id) ~* 'd[aá]l[ií]l[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[eé]n[eé]l[oó]p[eé]' OR  a.rut ~* 'p[eé]n[eé]l[oó]p[eé]' OR  lower(a.email) ~* 'p[eé]n[eé]l[oó]p[eé]' OR  text(a.id) ~* 'p[eé]n[eé]l[oó]p[eé]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER