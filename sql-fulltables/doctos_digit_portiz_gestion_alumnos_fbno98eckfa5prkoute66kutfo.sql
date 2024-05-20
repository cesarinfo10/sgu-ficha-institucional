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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'j[oó]s[eé]' OR  a.rut ~* 'j[oó]s[eé]' OR  lower(a.email) ~* 'j[oó]s[eé]' OR  text(a.id) ~* 'j[oó]s[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[ií]gn[aá]c[ií][oó]' OR  a.rut ~* '[ií]gn[aá]c[ií][oó]' OR  lower(a.email) ~* '[ií]gn[aá]c[ií][oó]' OR  text(a.id) ~* '[ií]gn[aá]c[ií][oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]sp[ií]n[oó]s[aá]' OR  a.rut ~* '[eé]sp[ií]n[oó]s[aá]' OR  lower(a.email) ~* '[eé]sp[ií]n[oó]s[aá]' OR  text(a.id) ~* '[eé]sp[ií]n[oó]s[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]g[uú][ií]l[aá]r' OR  a.rut ~* '[aá]g[uú][ií]l[aá]r' OR  lower(a.email) ~* '[aá]g[uú][ií]l[aá]r' OR  text(a.id) ~* '[aá]g[uú][ií]l[aá]r' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER