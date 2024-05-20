COPY (SELECT vu.id,vu.nombre,vu.nombre_usuario,vu.tipo,vu.escuela,gu.alias AS unidad,vu.email,
                        CASE WHEN jefe_unidad THEN '[Jefe]' ELSE '' END AS jefe_unidad
                 FROM vista_usuarios AS vu
                 LEFT JOIN usuarios AS u USING (id)
                 LEFT JOIN gestion.unidades AS gu ON gu.id=u.id_unidad
                 WHERE u.tipo<>3  AND (lower(u.nombre||' '||u.apellido) ~* 'mh[oó]r' OR  u.rut ~* 'mh[oó]r' OR  lower(u.email) ~* 'mh[oó]r' OR  lower(u.nombre_usuario) ~* 'mh[oó]r' OR  text(u.id) ~* 'mh[oó]r' ) 
                 ORDER BY vu.nombre_usuario) to stdout WITH CSV HEADER