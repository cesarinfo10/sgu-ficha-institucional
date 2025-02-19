COPY (SELECT u.id AS id_profesor,initcap(u.apellido) AS apellidos,initcap(u.nombre) AS nombres,
                          initcap(u.apellido||' '||u.nombre) AS profesor,ga.nombre AS grado_acad,u.grado_acad_nombre,
                          u.rut,e.nombre AS escuela,u.funcion,dscn.nombre AS cargo_normalizado_sies,
                          CASE WHEN (SELECT 1 FROM encuestas.autoevaluacion_docente WHERE id_profesor=u.id)=1 THEN 'Si' ELSE 'No' END AS auto_ev_completa,
                          u.direccion,com.nombre AS comuna,reg.nombre AS region,
                          u.categorizacion as categorizacion,u.nombre_usuario,u.email_personal,u.email_gsuite,
                          fpp.tipo_deposito,fif.nombre AS banco_deposito,fpp.tipo_cuenta_deposito,fpp.nro_cuenta_deposito,fpp.email,null
                   FROM usuarios         AS u
                   LEFT JOIN grado_acad  AS ga ON ga.id=u.grado_academico
                   LEFT JOIN escuelas    AS e  ON e.id=u.id_escuela
                   LEFT JOIN comunas     AS com ON com.id=u.comuna
                   LEFT JOIN regiones    AS reg ON reg.id=u.region
                   LEFT JOIN finanzas.profesores_pago AS fpp ON fpp.id_profesor=u.id
                   LEFT JOIN finanzas.inst_financieras AS fif ON fif.codigo=fpp.cod_banco_deposito
                   LEFT JOIN docentes_sies_cargos_normalizados AS dscn ON dscn.id=u.id_cargo_normalizado_sies
                   WHERE tipo=3  AND (lower(u.nombre||' '||u.apellido) ~* 'd[oó]r[ií]s'  OR u.rut ~* 'd[oó]r[ií]s')  AND (lower(u.nombre||' '||u.apellido) ~* 'j[oó]s[eé]f[ií]n[aá]'  OR u.rut ~* 'j[oó]s[eé]f[ií]n[aá]')  AND (lower(u.nombre||' '||u.apellido) ~* 's[oó]l[ií]s'  OR u.rut ~* 's[oó]l[ií]s')  AND (lower(u.nombre||' '||u.apellido) ~* 'm[eé]j[ií][aá]s'  OR u.rut ~* 'm[eé]j[ií][aá]s') 
                   ORDER BY u.apellido,u.nombre ) to stdout WITH CSV HEADER