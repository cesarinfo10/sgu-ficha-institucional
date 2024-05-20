COPY (SELECT pp.id,pp.rut,pp.apellidos,initcap(pp.nombres) AS nombres,(SELECT char_comma_sum(alias) FROM carreras WHERE id::text = ANY(string_to_array(pp.carreras,','))) AS carreras,pp.genero,
                          horarios,to_char(fecha,'DD-tmMon-YYYY HH24:MI') AS fecha,ga.nombre AS grado_acad,pp.email,p.nombre AS pais
                   FROM portalweb.profes_post AS pp
                   LEFT JOIN grado_acad       AS ga ON ga.id=pp.est_grado_acad
                   LEFT JOIN pais             AS p ON p.localizacion=pp.nacionalidad
                   WHERE true  AND (lower(pp.nombres) ~* 'j[uú][aá]n') OR (lower(pp.apellidos) ~* 'j[uú][aá]n') OR (lower(pp.rut) ~* 'j[uú][aá]n') AND (lower(pp.nombres) ~* 'p[aá]bl[oó]') OR (lower(pp.apellidos) ~* 'p[aá]bl[oó]') OR (lower(pp.rut) ~* 'p[aá]bl[oó]') AND (lower(pp.nombres) ~* '[aá]g[uú][ií]rr[eé]') OR (lower(pp.apellidos) ~* '[aá]g[uú][ií]rr[eé]') OR (lower(pp.rut) ~* '[aá]g[uú][ií]rr[eé]') AND (lower(pp.nombres) ~* '') OR (lower(pp.apellidos) ~* '') OR (lower(pp.rut) ~* '')
                   ORDER BY pp.fecha DESC,pp.apellidos,pp.nombres ) to stdout WITH CSV HEADER