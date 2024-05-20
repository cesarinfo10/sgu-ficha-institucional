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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]c[eé]r[eé]s' OR  a.rut ~* 'c[aá]c[eé]r[eé]s' OR  lower(a.email) ~* 'c[aá]c[eé]r[eé]s' OR  text(a.id) ~* 'c[aá]c[eé]r[eé]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'g[aá]rc[ií][aá]' OR  a.rut ~* 'g[aá]rc[ií][aá]' OR  lower(a.email) ~* 'g[aá]rc[ií][aá]' OR  text(a.id) ~* 'g[aá]rc[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'n[ií]c[oó]l[aá]s' OR  a.rut ~* 'n[ií]c[oó]l[aá]s' OR  lower(a.email) ~* 'n[ií]c[oó]l[aá]s' OR  text(a.id) ~* 'n[ií]c[oó]l[aá]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'b[eé]rn[aá]rd[oó]' OR  a.rut ~* 'b[eé]rn[aá]rd[oó]' OR  lower(a.email) ~* 'b[eé]rn[aá]rd[oó]' OR  text(a.id) ~* 'b[eé]rn[aá]rd[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER