COPY (SELECT * FROM (SELECT DISTINCT ON (coalesce(p.nro_boleta,p.nro_boleta_e,p.nro_factura)) p.id,coalesce(p.nro_boleta,p.nro_boleta_e,p.nro_factura) AS nro_docto,
                       CASE WHEN p.nro_boleta IS NOT NULL THEN 'B' 
                            WHEN p.nro_boleta_e IS NOT NULL THEN 'BE' 
                            WHEN p.nro_factura IS NOT NULL THEN 'F' 
                       END AS tipo_doc,p.fecha AS fecha_bol,
                       to_char(p.fecha,'DD-MM-YYYY') AS fecha,u.nombre_usuario AS cajero,cod_operacion,
                       coalesce(efectivo,0)+coalesce(deposito,0)+coalesce(cheque,0)+coalesce(cheque_afecha,0)+coalesce(transferencia,0)+coalesce(tarj_credito,0)+coalesce(tarj_debito,0) AS monto_boleta,
                       CASE WHEN id_arqueo IS NULL THEN 'No' ELSE 'Si' END AS rendida,cob.id_contrato,cob.id_convenio_ci,
                       CASE 
                         WHEN cob.id_contrato IS NOT NULL    THEN coalesce(a.rut,pap.rut)||' '||coalesce(a.apellidos||' '||a.nombres,pap.apellidos||' '||pap.nombres) 
                         WHEN cob.id_convenio_ci IS NOT NULL THEN a3.rut||' '||a3.apellidos||' '||a3.nombres 
                         WHEN cob.id_alumno IS NOT NULL      THEN a2.rut||' '||a2.apellidos||' '||a2.nombres
                         WHEN p.nulo THEN '****** NULO ******'
                       END AS alumno,
                       CASE 
                         WHEN cob.id_contrato IS NOT NULL    THEN coalesce(a.rut,pap.rut)
                         WHEN cob.id_convenio_ci IS NOT NULL THEN a3.rut
                         WHEN cob.id_alumno IS NOT NULL      THEN a2.rut
                         WHEN p.nulo THEN '****** NULO ******'
                       END AS rut_alumno,p.fecha_reg,coalesce(car.nombre,car2.nombre,car3.nombre) AS carrera_alumno,
                       efectivo,deposito,cheque,cheque_afecha,transferencia,tarj_credito,tarj_debito,p.bol_e_respuesta_api,p.bol_e_cod_erp
                FROM finanzas.pagos AS p
                LEFT JOIN vista_usuarios AS u          ON u.id=id_cajero
                LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_pago=p.id 
                LEFT JOIN finanzas.cobros AS cob       ON cob.id=id_cobro 
                LEFT JOIN finanzas.contratos AS c      ON c.id=cob.id_contrato 
                LEFT JOIN finanzas.convenios_ci AS cci ON cci.id=cob.id_convenio_ci 
                LEFT JOIN alumnos AS a                 ON a.id=c.id_alumno
                LEFT JOIN alumnos AS a2                ON a2.id=cob.id_alumno
                LEFT JOIN alumnos AS a3                ON a3.id=cci.id_alumno
                LEFT JOIN pap                          ON pap.id=c.id_pap
                LEFT JOIN carreras AS car              ON car.id=c.id_carrera
                LEFT JOIN carreras AS car2             ON car2.id=a2.carrera_actual
                LEFT JOIN carreras AS car3             ON car3.id=a3.carrera_actual
                WHERE (nro_boleta IS NOT NULL OR nro_boleta_e IS NOT NULL) AND (lower(pap.nombres||' '||pap.apellidos) ~* '20446269' OR  pap.rut ~* '20446269' OR lower(a.nombres||' '||a.apellidos) ~* '20446269' OR lower(a3.nombres||' '||a3.apellidos) ~* '20446269' OR  a.rut ~* '20446269' OR a2.rut ~* '20446269' OR a3.rut ~* '20446269' )  
                ORDER BY coalesce(p.nro_boleta,p.nro_boleta_e,p.nro_factura) DESC ) AS foo ORDER BY fecha_bol DESC,nro_docto DESC ) to stdout WITH CSV HEADER