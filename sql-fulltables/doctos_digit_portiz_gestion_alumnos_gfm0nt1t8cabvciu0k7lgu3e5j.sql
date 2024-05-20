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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'chr[ií]st[ií][aá]n' OR  a.rut ~* 'chr[ií]st[ií][aá]n' OR  lower(a.email) ~* 'chr[ií]st[ií][aá]n' OR  text(a.id) ~* 'chr[ií]st[ií][aá]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* '[aá]ndr[eé]s' OR  a.rut ~* '[aá]ndr[eé]s' OR  lower(a.email) ~* '[aá]ndr[eé]s' OR  text(a.id) ~* '[aá]ndr[eé]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'b[uú]rg[oó]s' OR  a.rut ~* 'b[uú]rg[oó]s' OR  lower(a.email) ~* 'b[uú]rg[oó]s' OR  text(a.id) ~* 'b[uú]rg[oó]s' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'h[uú][eé]nch[uú]m[aá]n' OR  a.rut ~* 'h[uú][eé]nch[uú]m[aá]n' OR  lower(a.email) ~* 'h[uú][eé]nch[uú]m[aá]n' OR  text(a.id) ~* 'h[uú][eé]nch[uú]m[aá]n' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER