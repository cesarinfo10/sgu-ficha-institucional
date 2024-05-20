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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]t[ií][aá]s' OR  a.rut ~* 'm[aá]t[ií][aá]s' OR  lower(a.email) ~* 'm[aá]t[ií][aá]s' OR  text(a.id) ~* 'm[aá]t[ií][aá]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'h[eé]rn[aá]n' OR  a.rut ~* 'h[eé]rn[aá]n' OR  lower(a.email) ~* 'h[eé]rn[aá]n' OR  text(a.id) ~* 'h[eé]rn[aá]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'ch[aá]v[aá]rr[ií][aá]' OR  a.rut ~* 'ch[aá]v[aá]rr[ií][aá]' OR  lower(a.email) ~* 'ch[aá]v[aá]rr[ií][aá]' OR  text(a.id) ~* 'ch[aá]v[aá]rr[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'f[eé]rn[aá]nd[eé]z' OR  a.rut ~* 'f[eé]rn[aá]nd[eé]z' OR  lower(a.email) ~* 'f[eé]rn[aá]nd[eé]z' OR  text(a.id) ~* 'f[eé]rn[aá]nd[eé]z' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER