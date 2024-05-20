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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]r[ií][aá]' OR  a.rut ~* 'm[aá]r[ií][aá]' OR  lower(a.email) ~* 'm[aá]r[ií][aá]' OR  text(a.id) ~* 'm[aá]r[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'fr[aá]nc[ií]sc[aá]' OR  a.rut ~* 'fr[aá]nc[ií]sc[aá]' OR  lower(a.email) ~* 'fr[aá]nc[ií]sc[aá]' OR  text(a.id) ~* 'fr[aá]nc[ií]sc[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[oó]ll[ií]p[aá]l' OR  a.rut ~* 'c[oó]ll[ií]p[aá]l' OR  lower(a.email) ~* 'c[oó]ll[ií]p[aá]l' OR  text(a.id) ~* 'c[oó]ll[ií]p[aá]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'h[uú][aá]nq[uú][ií]' OR  a.rut ~* 'h[uú][aá]nq[uú][ií]' OR  lower(a.email) ~* 'h[uú][aá]nq[uú][ií]' OR  text(a.id) ~* 'h[uú][aá]nq[uú][ií]' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER