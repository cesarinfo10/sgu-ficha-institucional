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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]shl[eé]y' OR  a.rut ~* '[aá]shl[eé]y' OR  lower(a.email) ~* '[aá]shl[eé]y' OR  text(a.id) ~* '[aá]shl[eé]y' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[oó]nst[aá]nz[aá]' OR  a.rut ~* 'c[oó]nst[aá]nz[aá]' OR  lower(a.email) ~* 'c[oó]nst[aá]nz[aá]' OR  text(a.id) ~* 'c[oó]nst[aá]nz[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[uú][ií]z' OR  a.rut ~* 'r[uú][ií]z' OR  lower(a.email) ~* 'r[uú][ií]z' OR  text(a.id) ~* 'r[uú][ií]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]rd[eé]n[aá]s' OR  a.rut ~* 'c[aá]rd[eé]n[aá]s' OR  lower(a.email) ~* 'c[aá]rd[eé]n[aá]s' OR  text(a.id) ~* 'c[aá]rd[eé]n[aá]s' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER