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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'j[oó]c[eé]lyn' OR  a.rut ~* 'j[oó]c[eé]lyn' OR  lower(a.email) ~* 'j[oó]c[eé]lyn' OR  text(a.id) ~* 'j[oó]c[eé]lyn' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[aá]tr[ií]c[ií][aá]' OR  a.rut ~* 'p[aá]tr[ií]c[ií][aá]' OR  lower(a.email) ~* 'p[aá]tr[ií]c[ií][aá]' OR  text(a.id) ~* 'p[aá]tr[ií]c[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'g[uú][eé]rr[aá]' OR  a.rut ~* 'g[uú][eé]rr[aá]' OR  lower(a.email) ~* 'g[uú][eé]rr[aá]' OR  text(a.id) ~* 'g[uú][eé]rr[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[oó]rt[eé]s' OR  a.rut ~* 'c[oó]rt[eé]s' OR  lower(a.email) ~* 'c[oó]rt[eé]s' OR  text(a.id) ~* 'c[oó]rt[eé]s' )  AND a.carrera_actual IN (59,57,27,28,33,54,56,31,58,60,55,30,32,29,76,79,82,81,83,78,80,38,115,114,77,125)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER