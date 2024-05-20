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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]rg[aá]r[ií]t[aá]' OR  a.rut ~* 'm[aá]rg[aá]r[ií]t[aá]' OR  lower(a.email) ~* 'm[aá]rg[aá]r[ií]t[aá]' OR  text(a.id) ~* 'm[aá]rg[aá]r[ií]t[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'j[oó]fr[eé]' OR  a.rut ~* 'j[oó]fr[eé]' OR  lower(a.email) ~* 'j[oó]fr[eé]' OR  text(a.id) ~* 'j[oó]fr[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[oó]y[aá]rz[uú]n' OR  a.rut ~* '[oó]y[aá]rz[uú]n' OR  lower(a.email) ~* '[oó]y[aá]rz[uú]n' OR  text(a.id) ~* '[oó]y[aá]rz[uú]n' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER