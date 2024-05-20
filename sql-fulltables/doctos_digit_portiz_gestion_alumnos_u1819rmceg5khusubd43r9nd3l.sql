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
                  WHERE true   AND (lower(a.nombres||' '||a.apellidos) ~* 'br[uú]c[eé]' OR  a.rut ~* 'br[uú]c[eé]' OR  lower(a.email) ~* 'br[uú]c[eé]' OR  text(a.id) ~* 'br[uú]c[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'j[eé][aá]n' OR  a.rut ~* 'j[eé][aá]n' OR  lower(a.email) ~* 'j[eé][aá]n' OR  text(a.id) ~* 'j[eé][aá]n' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'p[ií][eé]rr[eé]' OR  a.rut ~* 'p[ií][eé]rr[eé]' OR  lower(a.email) ~* 'p[ií][eé]rr[eé]' OR  text(a.id) ~* 'p[ií][eé]rr[eé]' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'm[aá]rt[ií]n[eé]z' OR  a.rut ~* 'm[aá]rt[ií]n[eé]z' OR  lower(a.email) ~* 'm[aá]rt[ií]n[eé]z' OR  text(a.id) ~* 'm[aá]rt[ií]n[eé]z' ) AND (lower(a.nombres||' '||a.apellidos) ~* 'str[eé][ií]t' OR  a.rut ~* 'str[eé][ií]t' OR  lower(a.email) ~* 'str[eé][ií]t' OR  text(a.id) ~* 'str[eé][ií]t' )  AND NOT dd.eliminado) TO stdout WITH CSV HEADER