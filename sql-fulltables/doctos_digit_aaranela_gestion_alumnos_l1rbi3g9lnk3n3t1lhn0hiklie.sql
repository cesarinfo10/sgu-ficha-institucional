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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 's[ií]lv[aá]' OR  a.rut ~* 's[ií]lv[aá]' OR  lower(a.email) ~* 's[ií]lv[aá]' OR  text(a.id) ~* 's[ií]lv[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'g[uú]zm[aá]n' OR  a.rut ~* 'g[uú]zm[aá]n' OR  lower(a.email) ~* 'g[uú]zm[aá]n' OR  text(a.id) ~* 'g[uú]zm[aá]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* '' OR  a.rut ~* '' OR  lower(a.email) ~* '' OR  text(a.id) ~* '' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'g[eé]n[eé]s[ií]s' OR  a.rut ~* 'g[eé]n[eé]s[ií]s' OR  lower(a.email) ~* 'g[eé]n[eé]s[ií]s' OR  text(a.id) ~* 'g[eé]n[eé]s[ií]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[aá]l[eé]nt[ií]n[aá]' OR  a.rut ~* 'v[aá]l[eé]nt[ií]n[aá]' OR  lower(a.email) ~* 'v[aá]l[eé]nt[ií]n[aá]' OR  text(a.id) ~* 'v[aá]l[eé]nt[ií]n[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER