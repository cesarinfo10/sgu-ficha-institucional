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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'p[aá][oó]l[aá]' OR  a.rut ~* 'p[aá][oó]l[aá]' OR  lower(a.email) ~* 'p[aá][oó]l[aá]' OR  text(a.id) ~* 'p[aá][oó]l[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'x[ií]m[eé]n[aá]' OR  a.rut ~* 'x[ií]m[eé]n[aá]' OR  lower(a.email) ~* 'x[ií]m[eé]n[aá]' OR  text(a.id) ~* 'x[ií]m[eé]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[ií]lv[aá]' OR  a.rut ~* 's[ií]lv[aá]' OR  lower(a.email) ~* 's[ií]lv[aá]' OR  text(a.id) ~* 's[ií]lv[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá][uú]r[eé][ií]r[aá]' OR  a.rut ~* 'm[aá][uú]r[eé][ií]r[aá]' OR  lower(a.email) ~* 'm[aá][uú]r[eé][ií]r[aá]' OR  text(a.id) ~* 'm[aá][uú]r[eé][ií]r[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER