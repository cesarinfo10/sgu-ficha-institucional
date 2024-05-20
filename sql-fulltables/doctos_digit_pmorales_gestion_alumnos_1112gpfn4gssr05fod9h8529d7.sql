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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'r[uú]th' OR  a.rut ~* 'r[uú]th' OR  lower(a.email) ~* 'r[uú]th' OR  text(a.id) ~* 'r[uú]th' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]l[ií][aá]n[aá]' OR  a.rut ~* '[eé]l[ií][aá]n[aá]' OR  lower(a.email) ~* '[eé]l[ií][aá]n[aá]' OR  text(a.id) ~* '[eé]l[ií][aá]n[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[oó]j[eé]d[aá]' OR  a.rut ~* '[oó]j[eé]d[aá]' OR  lower(a.email) ~* '[oó]j[eé]d[aá]' OR  text(a.id) ~* '[oó]j[eé]d[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]nc[ií]ll[aá]' OR  a.rut ~* 'm[aá]nc[ií]ll[aá]' OR  lower(a.email) ~* 'm[aá]nc[ií]ll[aá]' OR  text(a.id) ~* 'm[aá]nc[ií]ll[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER