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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[oó]rt[eé]g[aá]' OR  a.rut ~* '[oó]rt[eé]g[aá]' OR  lower(a.email) ~* '[oó]rt[eé]g[aá]' OR  text(a.id) ~* '[oó]rt[eé]g[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[ií]nt[oó]' OR  a.rut ~* 'p[ií]nt[oó]' OR  lower(a.email) ~* 'p[ií]nt[oó]' OR  text(a.id) ~* 'p[ií]nt[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'fr[aá]nc[ií]sc[oó]' OR  a.rut ~* 'fr[aá]nc[ií]sc[oó]' OR  lower(a.email) ~* 'fr[aá]nc[ií]sc[oó]' OR  text(a.id) ~* 'fr[aá]nc[ií]sc[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]l[eé]j[aá]ndr[oó]' OR  a.rut ~* '[aá]l[eé]j[aá]ndr[oó]' OR  lower(a.email) ~* '[aá]l[eé]j[aá]ndr[oó]' OR  text(a.id) ~* '[aá]l[eé]j[aá]ndr[oó]' )  AND a.carrera_actual IN (108,96,25,109,37,70,135,112,17)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER