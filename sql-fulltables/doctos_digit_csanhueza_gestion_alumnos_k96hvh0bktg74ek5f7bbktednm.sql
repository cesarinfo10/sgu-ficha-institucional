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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]r[ií][aá]' OR  a.rut ~* 'm[aá]r[ií][aá]' OR  lower(a.email) ~* 'm[aá]r[ií][aá]' OR  text(a.id) ~* 'm[aá]r[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'b[eé]rn[aá]rd[ií]t[aá]' OR  a.rut ~* 'b[eé]rn[aá]rd[ií]t[aá]' OR  lower(a.email) ~* 'b[eé]rn[aá]rd[ií]t[aá]' OR  text(a.id) ~* 'b[eé]rn[aá]rd[ií]t[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'cr[oó][oó]k[eé]r' OR  a.rut ~* 'cr[oó][oó]k[eé]r' OR  lower(a.email) ~* 'cr[oó][oó]k[eé]r' OR  text(a.id) ~* 'cr[oó][oó]k[eé]r' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'd[oó]n[oó]s[oó]' OR  a.rut ~* 'd[oó]n[oó]s[oó]' OR  lower(a.email) ~* 'd[oó]n[oó]s[oó]' OR  text(a.id) ~* 'd[oó]n[oó]s[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER