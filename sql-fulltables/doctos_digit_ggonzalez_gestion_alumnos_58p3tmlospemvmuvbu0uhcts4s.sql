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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'f[eé]l[ií]p[eé]' OR  a.rut ~* 'f[eé]l[ií]p[eé]' OR  lower(a.email) ~* 'f[eé]l[ií]p[eé]' OR  text(a.id) ~* 'f[eé]l[ií]p[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]rn[aá]ld[oó]' OR  a.rut ~* '[aá]rn[aá]ld[oó]' OR  lower(a.email) ~* '[aá]rn[aá]ld[oó]' OR  text(a.id) ~* '[aá]rn[aá]ld[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]v[aá]l[oó]s' OR  a.rut ~* '[aá]v[aá]l[oó]s' OR  lower(a.email) ~* '[aá]v[aá]l[oó]s' OR  text(a.id) ~* '[aá]v[aá]l[oó]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'g[uú][ií]ñ[eé]z' OR  a.rut ~* 'g[uú][ií]ñ[eé]z' OR  lower(a.email) ~* 'g[uú][ií]ñ[eé]z' OR  text(a.id) ~* 'g[uú][ií]ñ[eé]z' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER