COPY (SELECT pp.id,pp.rut,pp.apellidos,initcap(pp.nombres) AS nombres,(SELECT char_comma_sum(alias) FROM carreras WHERE id::text = ANY(string_to_array(pp.carreras,','))) AS carreras,pp.genero,
                          horarios,to_char(fecha,'DD-tmMon-YYYY HH24:MI') AS fecha,ga.nombre AS grado_acad,pp.email,p.nombre AS pais
                   FROM portalweb.profes_post AS pp
                   LEFT JOIN grado_acad       AS ga ON ga.id=pp.est_grado_acad
                   LEFT JOIN pais             AS p ON p.localizacion=pp.nacionalidad
                   WHERE true  AND (lower(pp.nombres) ~* 'c[aá]m[ií]l[aá]') OR (lower(pp.apellidos) ~* 'c[aá]m[ií]l[aá]') OR (lower(pp.rut) ~* 'c[aá]m[ií]l[aá]') AND (lower(pp.nombres) ~* 'p[eé]ñ[aá]l[oó]z[aá]') OR (lower(pp.apellidos) ~* 'p[eé]ñ[aá]l[oó]z[aá]') OR (lower(pp.rut) ~* 'p[eé]ñ[aá]l[oó]z[aá]') AND (lower(pp.nombres) ~* 'c[aá]c[eé]r[eé]s') OR (lower(pp.apellidos) ~* 'c[aá]c[eé]r[eé]s') OR (lower(pp.rut) ~* 'c[aá]c[eé]r[eé]s')
                   ORDER BY pp.fecha DESC,pp.apellidos,pp.nombres ) to stdout WITH CSV HEADER