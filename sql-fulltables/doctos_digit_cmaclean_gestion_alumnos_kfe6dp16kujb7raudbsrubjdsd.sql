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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'g[ií]s[eé]l[eé]' OR  a.rut ~* 'g[ií]s[eé]l[eé]' OR  lower(a.email) ~* 'g[ií]s[eé]l[eé]' OR  text(a.id) ~* 'g[ií]s[eé]l[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'n[aá]t[aá]l[ií]' OR  a.rut ~* 'n[aá]t[aá]l[ií]' OR  lower(a.email) ~* 'n[aá]t[aá]l[ií]' OR  text(a.id) ~* 'n[aá]t[aá]l[ií]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[aá][eé]z' OR  a.rut ~* 's[aá][eé]z' OR  lower(a.email) ~* 's[aá][eé]z' OR  text(a.id) ~* 's[aá][eé]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[aá]nh[uú][eé]z[aá]' OR  a.rut ~* 's[aá]nh[uú][eé]z[aá]' OR  lower(a.email) ~* 's[aá]nh[uú][eé]z[aá]' OR  text(a.id) ~* 's[aá]nh[uú][eé]z[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER