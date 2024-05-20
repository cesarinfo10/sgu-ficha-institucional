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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 's[aá]nt[aá]nd[eé]r' OR  a.rut ~* 's[aá]nt[aá]nd[eé]r' OR  lower(a.email) ~* 's[aá]nt[aá]nd[eé]r' OR  text(a.id) ~* 's[aá]nt[aá]nd[eé]r' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'r[ií]q[uú][eé]lm[eé]' OR  a.rut ~* 'r[ií]q[uú][eé]lm[eé]' OR  lower(a.email) ~* 'r[ií]q[uú][eé]lm[eé]' OR  text(a.id) ~* 'r[ií]q[uú][eé]lm[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[eé]l[ií]z[aá]b[eé]th' OR  a.rut ~* '[eé]l[ií]z[aá]b[eé]th' OR  lower(a.email) ~* '[eé]l[ií]z[aá]b[eé]th' OR  text(a.id) ~* '[eé]l[ií]z[aá]b[eé]th' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'd[eé]l' OR  a.rut ~* 'd[eé]l' OR  lower(a.email) ~* 'd[eé]l' OR  text(a.id) ~* 'd[eé]l' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'c[aá]rm[eé]n' OR  a.rut ~* 'c[aá]rm[eé]n' OR  lower(a.email) ~* 'c[aá]rm[eé]n' OR  text(a.id) ~* 'c[aá]rm[eé]n' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER