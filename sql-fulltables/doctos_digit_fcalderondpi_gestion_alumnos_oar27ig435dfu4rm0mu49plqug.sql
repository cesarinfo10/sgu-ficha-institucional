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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'l[uú][ií]s' OR  a.rut ~* 'l[uú][ií]s' OR  lower(a.email) ~* 'l[uú][ií]s' OR  text(a.id) ~* 'l[uú][ií]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]t[ií][aá]s' OR  a.rut ~* 'm[aá]t[ií][aá]s' OR  lower(a.email) ~* 'm[aá]t[ií][aá]s' OR  text(a.id) ~* 'm[aá]t[ií][aá]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[oó]r[eé]tt[ií]' OR  a.rut ~* 'm[oó]r[eé]tt[ií]' OR  lower(a.email) ~* 'm[oó]r[eé]tt[ií]' OR  text(a.id) ~* 'm[oó]r[eé]tt[ií]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 's[eé]rv[ií]ll[aá]' OR  a.rut ~* 's[eé]rv[ií]ll[aá]' OR  lower(a.email) ~* 's[eé]rv[ií]ll[aá]' OR  text(a.id) ~* 's[eé]rv[ií]ll[aá]' )  AND a.carrera_actual IN (59,57,33,54,56,31,58,60,55,30,32,29,76,79,82,81,83,78,80,27,28,38,115,77,125,114,147,148,161,160,163)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER