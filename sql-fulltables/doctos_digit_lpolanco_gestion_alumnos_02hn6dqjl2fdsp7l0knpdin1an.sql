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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'd[aá]n[ií][eé]l[aá]' OR  a.rut ~* 'd[aá]n[ií][eé]l[aá]' OR  lower(a.email) ~* 'd[aá]n[ií][eé]l[aá]' OR  text(a.id) ~* 'd[aá]n[ií][eé]l[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]nt[oó]n[eé]ll[aá]' OR  a.rut ~* '[aá]nt[oó]n[eé]ll[aá]' OR  lower(a.email) ~* '[aá]nt[oó]n[eé]ll[aá]' OR  text(a.id) ~* '[aá]nt[oó]n[eé]ll[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[eé]p[uú]lv[eé]d[aá]' OR  a.rut ~* 's[eé]p[uú]lv[eé]d[aá]' OR  lower(a.email) ~* 's[eé]p[uú]lv[eé]d[aá]' OR  text(a.id) ~* 's[eé]p[uú]lv[eé]d[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[ií]ll[aá]gr[aá]n' OR  a.rut ~* 'v[ií]ll[aá]gr[aá]n' OR  lower(a.email) ~* 'v[ií]ll[aá]gr[aá]n' OR  text(a.id) ~* 'v[ií]ll[aá]gr[aá]n' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER