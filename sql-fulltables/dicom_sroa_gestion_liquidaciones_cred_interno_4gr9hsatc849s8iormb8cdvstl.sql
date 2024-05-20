COPY (SELECT 825570 AS cod_aportante, replace(lpad(trim(a.rut),10,'0'),'-','') AS rut,
                         to_char((SELECT max(fecha_venc) FROM finanzas.cobros WHERE id_convenio_ci=c.id AND (NOT pagado OR abonado) AND fecha_venc<now()::date AND fecha_dicom IS NULL),'YYYYMMDD') AS fecha_venc,'01' AS num_doc,'01' as tipo_trans,
                         upper(a.nombres||''||a.apellidos) AS nombre_alumno,
                         '01' AS tipo_calle,upper(a.direccion) AS nombre_calle,'' AS num_calle,'' AS num_depto,'' AS ind_depto_local_oficina,'01' AS tipo_domicilio,
                         upper(com.nombre) AS comuna,upper(reg.nombre) AS ciudad,'' AS cod_postal,coalesce(a.tel_movil,a.telefono) AS telefono,
                         'PG' AS tipo_doc,'UF' AS tipo_moneda,round((SELECT sum(monto-coalesce(monto_abonado,0)) FROM finanzas.cobros WHERE id_convenio_ci=c.id AND (NOT pagado OR abonado) AND fecha_venc<now()::date AND fecha_dicom IS NULL)/uf.valor,2) AS monto_moroso_uf,(SELECT max(p.fecha::date) FROM finanzas.pagos_detalle pd LEFT JOIN finanzas.pagos p ON p.id=pd.id_pago LEFT JOIN finanzas.cobros cob ON cob.id=pd.id_cobro WHERE cob.id_convenio_ci=c.id) AS fecha_ult_pago
                  FROM finanzas.convenios_ci AS c
                  LEFT JOIN finanzas.vista_convenios_ci AS vc USING (id)
                  LEFT JOIN alumnos         AS a   ON a.id=c.id_alumno
                  LEFT JOIN comunas         AS com ON com.id=a.comuna
                  LEFT JOIN regiones        AS reg ON reg.id=a.region
                  LEFT JOIN carreras        AS car ON car.id=a.carrera_actual
                  LEFT JOIN vista_alumnos   AS va   ON va.id=c.id_alumno
                  LEFT JOIN finanzas.valor_uf AS uf ON uf.fecha=now()::date
                  WHERE true AND NOT c.nulo AND c.estado='Notariado' AND (car.regimen = 'PRE') AND vc.cant_cuotas_morosas >= 1  AND (SELECT sum(monto-coalesce(monto_abonado,0)) FROM finanzas.cobros WHERE id_convenio_ci=c.id AND (NOT pagado OR abonado) AND fecha_venc<now()::date AND fecha_dicom IS NULL) > 0
                  ORDER BY c.fecha DESC,nombre_alumno) to stdout WITH CSV HEADER FORCE QUOTE *