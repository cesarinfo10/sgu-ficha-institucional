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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 's[oó]t[oó]' OR  a.rut ~* 's[oó]t[oó]' OR  lower(a.email) ~* 's[oó]t[oó]' OR  text(a.id) ~* 's[oó]t[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]nd[ií][aá]' OR  a.rut ~* 'c[aá]nd[ií][aá]' OR  lower(a.email) ~* 'c[aá]nd[ií][aá]' OR  text(a.id) ~* 'c[aá]nd[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'd[ií][eé]g[oó]' OR  a.rut ~* 'd[ií][eé]g[oó]' OR  lower(a.email) ~* 'd[ií][eé]g[oó]' OR  text(a.id) ~* 'd[ií][eé]g[oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[ií]sr[aá][eé]l' OR  a.rut ~* '[ií]sr[aá][eé]l' OR  lower(a.email) ~* '[ií]sr[aá][eé]l' OR  text(a.id) ~* '[ií]sr[aá][eé]l' )  AND a.carrera_actual IN (108,96,25,109,37,70,112,17)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER