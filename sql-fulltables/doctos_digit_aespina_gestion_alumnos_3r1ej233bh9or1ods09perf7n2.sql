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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'j[oó]rg[eé]' OR  a.rut ~* 'j[oó]rg[eé]' OR  lower(a.email) ~* 'j[oó]rg[eé]' OR  text(a.id) ~* 'j[oó]rg[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]l[eé]j[aá]ndr[oó]' OR  a.rut ~* '[aá]l[eé]j[aá]ndr[oó]' OR  lower(a.email) ~* '[aá]l[eé]j[aá]ndr[oó]' OR  text(a.id) ~* '[aá]l[eé]j[aá]ndr[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'g[aá]ll[eé]g[oó]s' OR  a.rut ~* 'g[aá]ll[eé]g[oó]s' OR  lower(a.email) ~* 'g[aá]ll[eé]g[oó]s' OR  text(a.id) ~* 'g[aá]ll[eé]g[oó]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'g[uú][aá]j[aá]rd[oó]' OR  a.rut ~* 'g[uú][aá]j[aá]rd[oó]' OR  lower(a.email) ~* 'g[uú][aá]j[aá]rd[oó]' OR  text(a.id) ~* 'g[uú][aá]j[aá]rd[oó]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER