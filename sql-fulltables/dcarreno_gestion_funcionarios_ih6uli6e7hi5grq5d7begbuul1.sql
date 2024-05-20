COPY (SELECT vu.id,vu.nombre,vu.nombre_usuario,vu.tipo,vu.escuela,gu.alias AS unidad,vu.email,
                        CASE WHEN jefe_unidad THEN '[Jefe]' ELSE '' END AS jefe_unidad
                 FROM vista_usuarios AS vu
                 LEFT JOIN usuarios AS u USING (id)
                 LEFT JOIN gestion.unidades AS gu ON gu.id=u.id_unidad
                 WHERE u.tipo<>3  AND (lower(u.nombre||' '||u.apellido) ~* '[oó]sc[aá]r' OR  u.rut ~* '[oó]sc[aá]r' OR  lower(u.email) ~* '[oó]sc[aá]r' OR  lower(u.nombre_usuario) ~* '[oó]sc[aá]r' OR  text(u.id) ~* '[oó]sc[aá]r' ) AND (lower(u.nombre||' '||u.apellido) ~* 't[oó]rr[eé]s' OR  u.rut ~* 't[oó]rr[eé]s' OR  lower(u.email) ~* 't[oó]rr[eé]s' OR  lower(u.nombre_usuario) ~* 't[oó]rr[eé]s' OR  text(u.id) ~* 't[oó]rr[eé]s' ) 
                 ORDER BY vu.nombre_usuario) to stdout WITH CSV HEADER