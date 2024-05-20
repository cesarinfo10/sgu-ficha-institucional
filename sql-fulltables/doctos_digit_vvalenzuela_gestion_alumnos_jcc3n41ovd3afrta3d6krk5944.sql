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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'm[oó]r[aá]l[eé]s' OR  a.rut ~* 'm[oó]r[aá]l[eé]s' OR  lower(a.email) ~* 'm[oó]r[aá]l[eé]s' OR  text(a.id) ~* 'm[oó]r[aá]l[eé]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[uú]b[ií]l[aá]r' OR  a.rut ~* 'r[uú]b[ií]l[aá]r' OR  lower(a.email) ~* 'r[uú]b[ií]l[aá]r' OR  text(a.id) ~* 'r[uú]b[ií]l[aá]r' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[ií]ct[oó]r[ií][aá]' OR  a.rut ~* 'v[ií]ct[oó]r[ií][aá]' OR  lower(a.email) ~* 'v[ií]ct[oó]r[ií][aá]' OR  text(a.id) ~* 'v[ií]ct[oó]r[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[oó]l[aá]ng[eé]' OR  a.rut ~* 's[oó]l[aá]ng[eé]' OR  lower(a.email) ~* 's[oó]l[aá]ng[eé]' OR  text(a.id) ~* 's[oó]l[aá]ng[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]l[ií]z[aá]b[eé]th' OR  a.rut ~* '[eé]l[ií]z[aá]b[eé]th' OR  lower(a.email) ~* '[eé]l[ií]z[aá]b[eé]th' OR  text(a.id) ~* '[eé]l[ií]z[aá]b[eé]th' )  AND a.carrera_actual IN (108,96,25,109,37,70,112,17)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER