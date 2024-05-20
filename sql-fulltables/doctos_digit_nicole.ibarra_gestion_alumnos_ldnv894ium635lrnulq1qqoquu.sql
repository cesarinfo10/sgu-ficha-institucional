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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]st[eé]f[aá]n[ií][aá]' OR  a.rut ~* '[eé]st[eé]f[aá]n[ií][aá]' OR  lower(a.email) ~* '[eé]st[eé]f[aá]n[ií][aá]' OR  text(a.id) ~* '[eé]st[eé]f[aá]n[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]ndr[eé][aá]' OR  a.rut ~* '[aá]ndr[eé][aá]' OR  lower(a.email) ~* '[aá]ndr[eé][aá]' OR  text(a.id) ~* '[aá]ndr[eé][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[aá]nt[aá]nd[eé]r' OR  a.rut ~* 's[aá]nt[aá]nd[eé]r' OR  lower(a.email) ~* 's[aá]nt[aá]nd[eé]r' OR  text(a.id) ~* 's[aá]nt[aá]nd[eé]r' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[oó]nc[eé]' OR  a.rut ~* 'p[oó]nc[eé]' OR  lower(a.email) ~* 'p[oó]nc[eé]' OR  text(a.id) ~* 'p[oó]nc[eé]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER