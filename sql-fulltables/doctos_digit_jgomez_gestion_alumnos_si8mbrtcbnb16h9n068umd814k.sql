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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]s[eé]nc[ií][oó]' OR  a.rut ~* '[aá]s[eé]nc[ií][oó]' OR  lower(a.email) ~* '[aá]s[eé]nc[ií][oó]' OR  text(a.id) ~* '[aá]s[eé]nc[ií][oó]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[oó]ns[aá]lv[eé]' OR  a.rut ~* 'm[oó]ns[aá]lv[eé]' OR  lower(a.email) ~* 'm[oó]ns[aá]lv[eé]' OR  text(a.id) ~* 'm[oó]ns[aá]lv[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'v[aá]l[eé]r[ií][aá]' OR  a.rut ~* 'v[aá]l[eé]r[ií][aá]' OR  lower(a.email) ~* 'v[aá]l[eé]r[ií][aá]' OR  text(a.id) ~* 'v[aá]l[eé]r[ií][aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'f[eé]rn[aá]nd[aá]' OR  a.rut ~* 'f[eé]rn[aá]nd[aá]' OR  lower(a.email) ~* 'f[eé]rn[aá]nd[aá]' OR  text(a.id) ~* 'f[eé]rn[aá]nd[aá]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'd[eé]' OR  a.rut ~* 'd[eé]' OR  lower(a.email) ~* 'd[eé]' OR  text(a.id) ~* 'd[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'l[oó][uú]rd[eé]s' OR  a.rut ~* 'l[oó][uú]rd[eé]s' OR  lower(a.email) ~* 'l[oó][uú]rd[eé]s' OR  text(a.id) ~* 'l[oó][uú]rd[eé]s' )  AND a.carrera_actual IN (108,96,25,109,37,70,112,17)  AND NOT dd.eliminado) TO stdout WITH CSV HEADER