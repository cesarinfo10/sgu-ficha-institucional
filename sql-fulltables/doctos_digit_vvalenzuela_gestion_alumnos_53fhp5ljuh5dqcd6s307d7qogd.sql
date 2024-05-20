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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]rv[aá]j[aá]l' OR  a.rut ~* 'c[aá]rv[aá]j[aá]l' OR  lower(a.email) ~* 'c[aá]rv[aá]j[aá]l' OR  text(a.id) ~* 'c[aá]rv[aá]j[aá]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[eé]g[aá]' OR  a.rut ~* 'v[eé]g[aá]' OR  lower(a.email) ~* 'v[eé]g[aá]' OR  text(a.id) ~* 'v[eé]g[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]ng[eé]l[aá]' OR  a.rut ~* '[aá]ng[eé]l[aá]' OR  lower(a.email) ~* '[aá]ng[eé]l[aá]' OR  text(a.id) ~* '[aá]ng[eé]l[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[aá]l[eé]nt[ií]n[aá]' OR  a.rut ~* 'v[aá]l[eé]nt[ií]n[aá]' OR  lower(a.email) ~* 'v[aá]l[eé]nt[ií]n[aá]' OR  text(a.id) ~* 'v[aá]l[eé]nt[ií]n[aá]' )  AND a.carrera_actual IN (108,96,25,109,37,70,135,112,17,153)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER