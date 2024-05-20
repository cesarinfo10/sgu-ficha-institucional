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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'cl[aá][uú]d[ií][oó]' OR  a.rut ~* 'cl[aá][uú]d[ií][oó]' OR  lower(a.email) ~* 'cl[aá][uú]d[ií][oó]' OR  text(a.id) ~* 'cl[aá][uú]d[ií][oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[oó]t[aá]r[oó]l[aá]' OR  a.rut ~* '[oó]t[aá]r[oó]l[aá]' OR  lower(a.email) ~* '[oó]t[aá]r[oó]l[aá]' OR  text(a.id) ~* '[oó]t[aá]r[oó]l[aá]' )  AND a.carrera_actual IN (95,100,2,69,1,19)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER