COPY (SELECT 4 AS TIPO_REGISTRO,
                       'R' AS TIPO_DOCUMENTO,
                       split_part(a.rut,'-',1) AS NUM_DOCUMENTO,
                       split_part(a.rut,'-',2) AS dv,
                       translate(upper(split_part(trim(a.apellidos),' ',1)),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS AP_PATERNO,
                       translate(upper(split_part(trim(a.apellidos),' ',2)),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS AP_MATERNO,
                       translate(upper(a.nombres),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS NOMBRES,
                       CASE a.genero WHEN 'f' THEN 'M' WHEN 'm' THEN 'H' END AS sexo,
                       to_char(a.fec_nac,'DD-MM-YYYY') AS FECHA_NACIMIENTO,
                       p.cod_sies AS NACIONALIDAD,
                       CASE a.jornada WHEN 'D' THEN c.cod_sies_diurno WHEN 'V' THEN c.cod_sies_vespertino END AS COD_SIES_OBT_TIT,
                       CASE a.jornada WHEN 'D' THEN c.cod_sies_diurno WHEN 'V' THEN c.cod_sies_vespertino END AS COD_SIES_TERMINAL,
                       translate(upper(c.nombre_titulo),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS nombre_titulo,
                       translate(upper(c.nombre_grado),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS nombre_grado,
                       to_char(a.fecha_egreso,'DD-MM-YYYY') AS FECHA_OBT_EGR_LIC_NO_TER,
                       CASE WHEN a.admision NOT IN (2,20) THEN ((ano_egreso-cohorte)+1)*2+(CASE WHEN semestre_egreso<=semestre_cohorte THEN -1 ELSE 0 END)-(SELECT count(periodo) AS semestre_presente
                    FROM (SELECT id_alumno,ano||'-'||semestre as periodo 
                          FROM vista_alumnos_cursos 
                          WHERE id_alumno=a.id AND semestre>0 
                          GROUP BY id_alumno,periodo) AS foo 
                    GROUP BY id_alumno) ELSE 0 END AS N_SEMESTRES_SUSPENSION,
                       a.cohorte AS ANIO_INGRESO_CARRERA_ACTUAL,
                       a.semestre_cohorte AS SEM_INGRESO_CARRERA_ACTUAL,
                       coalesce((SELECT min(ano) AS ano_ing_orig FROM cargas_academicas AS ca LEFT JOIN convalidaciones AS c ON c.id=ca.id_convalida WHERE ca.id_alumno=a.id),a.cohorte) AS ANIO_INGRESO_CARRERA_ORIGEN,
                       a.semestre_cohorte AS SEM_INGRESO_CARRERA_ORIGEN,
                       a.ano_egreso AS ANIO_EGRESO,
                       a.semestre_egreso,
                       1 AS estado
                FROM alumnos AS a
                LEFT JOIN carreras AS c ON c.id=a.carrera_actual
                LEFT JOIN pais     AS p ON p.localizacion=a.nacionalidad
                LEFT JOIN al_estados AS ae ON ae.id=a.estado
                LEFT JOIN al_estados AS aet ON aet.id=a.estado_tramite
                LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND semestre=2 AND ano=2022)
                WHERE ae.nombre='Egresado'   AND (a.fecha_egreso between '2022-12-31'::date AND '2022-12-31'::date) AND (c.regimen = 'PRE') 
                ORDER BY c.alias,a.jornada,a.apellidos,a.nombres) to stdout WITH CSV HEADER