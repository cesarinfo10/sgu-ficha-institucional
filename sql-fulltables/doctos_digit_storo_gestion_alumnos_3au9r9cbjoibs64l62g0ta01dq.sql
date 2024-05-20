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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'd[aá]n[ií][eé]l' OR  a.rut ~* 'd[aá]n[ií][eé]l' OR  lower(a.email) ~* 'd[aá]n[ií][eé]l' OR  text(a.id) ~* 'd[aá]n[ií][eé]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]r[eé]n[aá]s' OR  a.rut ~* '[aá]r[eé]n[aá]s' OR  lower(a.email) ~* '[aá]r[eé]n[aá]s' OR  text(a.id) ~* '[aá]r[eé]n[aá]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[oó]y[aá]' OR  a.rut ~* 'm[oó]y[aá]' OR  lower(a.email) ~* 'm[oó]y[aá]' OR  text(a.id) ~* 'm[oó]y[aá]' )  AND a.carrera_actual IN (95,100,2,69,1,19)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER