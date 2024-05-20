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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'v[aá]ld[eé]s' OR  a.rut ~* 'v[aá]ld[eé]s' OR  lower(a.email) ~* 'v[aá]ld[eé]s' OR  text(a.id) ~* 'v[aá]ld[eé]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'g[aá]ll[aá]rd[oó]' OR  a.rut ~* 'g[aá]ll[aá]rd[oó]' OR  lower(a.email) ~* 'g[aá]ll[aá]rd[oó]' OR  text(a.id) ~* 'g[aá]ll[aá]rd[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[ií]lv[ií][aá]' OR  a.rut ~* 's[ií]lv[ií][aá]' OR  lower(a.email) ~* 's[ií]lv[ií][aá]' OR  text(a.id) ~* 's[ií]lv[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]l[ií]s[aá]' OR  a.rut ~* '[eé]l[ií]s[aá]' OR  lower(a.email) ~* '[eé]l[ií]s[aá]' OR  text(a.id) ~* '[eé]l[ií]s[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER