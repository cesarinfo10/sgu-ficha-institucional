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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]r[ií][aá]n[aá]' OR  a.rut ~* 'm[aá]r[ií][aá]n[aá]' OR  lower(a.email) ~* 'm[aá]r[ií][aá]n[aá]' OR  text(a.id) ~* 'm[aá]r[ií][aá]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'f[eé]rn[aá]nd[aá]' OR  a.rut ~* 'f[eé]rn[aá]nd[aá]' OR  lower(a.email) ~* 'f[eé]rn[aá]nd[aá]' OR  text(a.id) ~* 'f[eé]rn[aá]nd[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[eé]b[aá]ld' OR  a.rut ~* 's[eé]b[aá]ld' OR  lower(a.email) ~* 's[eé]b[aá]ld' OR  text(a.id) ~* 's[eé]b[aá]ld' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'tr[ií]nc[aá]d[oó]' OR  a.rut ~* 'tr[ií]nc[aá]d[oó]' OR  lower(a.email) ~* 'tr[ií]nc[aá]d[oó]' OR  text(a.id) ~* 'tr[ií]nc[aá]d[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER