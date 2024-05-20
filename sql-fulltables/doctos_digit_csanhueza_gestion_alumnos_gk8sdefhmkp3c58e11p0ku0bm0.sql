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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá][uú]r[ií]c[ií][oó]' OR  a.rut ~* 'm[aá][uú]r[ií]c[ií][oó]' OR  lower(a.email) ~* 'm[aá][uú]r[ií]c[ií][oó]' OR  text(a.id) ~* 'm[aá][uú]r[ií]c[ií][oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]st[eé]b[aá]n' OR  a.rut ~* '[eé]st[eé]b[aá]n' OR  lower(a.email) ~* '[eé]st[eé]b[aá]n' OR  text(a.id) ~* '[eé]st[eé]b[aá]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[aá]l[eé]nz[uú][eé]l[aá]' OR  a.rut ~* 'v[aá]l[eé]nz[uú][eé]l[aá]' OR  lower(a.email) ~* 'v[aá]l[eé]nz[uú][eé]l[aá]' OR  text(a.id) ~* 'v[aá]l[eé]nz[uú][eé]l[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]r[aá]y[aá]' OR  a.rut ~* '[aá]r[aá]y[aá]' OR  lower(a.email) ~* '[aá]r[aá]y[aá]' OR  text(a.id) ~* '[aá]r[aá]y[aá]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER