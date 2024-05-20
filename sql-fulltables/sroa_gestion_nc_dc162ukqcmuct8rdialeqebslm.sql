COPY (SELECT * FROM (SELECT DISTINCT ON (nc.nro_docto) nc.nro_docto,coalesce(p.nro_boleta,p.nro_boleta_e,p.nro_factura) AS nro_docto_pago,nc.id_pago,nc.observacion,
                              CASE WHEN p.nro_boleta IS NOT NULL THEN 'B' 
                                   WHEN p.nro_boleta_e IS NOT NULL THEN 'BE' 
                                   WHEN p.nro_factura IS NOT NULL THEN 'F' 
                              END AS tipo_docto_pago,id_contrato,id_convenio_ci,nc.monto,
                              to_char(nc.fecha,'DD-MM-YYYY') AS fecha,to_char(nc.fecha_reg,'DD-MM-YYYY HH24:MI') AS fecha_reg,u.nombre_usuario AS cajero,
                              CASE WHEN cob.id_contrato IS NOT NULL    THEN coalesce(a.rut,pap.rut)||' '||coalesce(a.apellidos||' '||a.nombres,pap.apellidos||' '||pap.nombres) 
                                   WHEN cob.id_convenio_ci IS NOT NULL THEN a3.rut||' '||a3.apellidos||' '||a3.nombres 
                                   WHEN cob.id_alumno IS NOT NULL      THEN a2.rut||' '||a2.apellidos||' '||a2.nombres
                              END AS alumno,
                              CASE WHEN cob.id_contrato IS NOT NULL    THEN coalesce(a.rut,pap.rut)
                                   WHEN cob.id_convenio_ci IS NOT NULL THEN a3.rut
                                   WHEN cob.id_alumno IS NOT NULL      THEN a2.rut                               
                              END AS rut_alumno,
							  to_char(p.fecha,'DD-MM-YYYY') AS fecha_pago,u2.nombre_usuario AS cajero_pago,u.nombre AS nombre_cajero,
							  coalesce(efectivo,0)+coalesce(deposito,0)+coalesce(cheque,0)+coalesce(cheque_afecha,0)+coalesce(transferencia,0)+coalesce(tarj_credito,0)+coalesce(tarj_debito,0) AS monto_pago
           FROM finanzas.notas_credito AS nc
		   LEFT JOIN vista_usuarios AS u                   ON u.id=id_cajero
           LEFT JOIN finanzas.notas_credito_detalle AS ncd ON ncd.nro_nc_docto=nc.nro_docto
		   LEFT JOIN finanzas.pagos AS p                   ON p.id=nc.id_pago
		   LEFT JOIN vista_usuarios AS u2                  ON u2.id=p.id_cajero
		   LEFT JOIN finanzas.cobros AS cob                ON cob.id=ncd.id_cobro
		   LEFT JOIN finanzas.contratos AS c               ON c.id=cob.id_contrato 
           LEFT JOIN finanzas.convenios_ci AS cci          ON cci.id=cob.id_convenio_ci 
           LEFT JOIN alumnos AS a                          ON a.id=c.id_alumno
           LEFT JOIN alumnos AS a2                         ON a2.id=cob.id_alumno
           LEFT JOIN alumnos AS a3                         ON a3.id=cci.id_alumno
           LEFT JOIN pap                                   ON pap.id=c.id_pap
           LEFT JOIN carreras AS car                       ON car.id=c.id_carrera
           LEFT JOIN carreras AS car2                      ON car2.id=a2.carrera_actual
           LEFT JOIN carreras AS car3                      ON car3.id=a3.carrera_actual
		   WHERE (p.nro_boleta IS NOT NULL OR p.nro_boleta_e IS NOT NULL OR p.nro_factura IS NOT NULL)  AND p. = 288  
           ORDER BY nc.nro_docto DESC ) AS foo ORDER BY nro_docto DESC ) to stdout WITH CSV HEADER