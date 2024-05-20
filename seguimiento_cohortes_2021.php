<?php
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");
include("sinergia/func_sinergia.php");
$modulo_destino = "ver_alumno";

$ids_carreras = $_SESSION['ids_carreras'];

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) { $cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if ($reg_inicio=="") { $reg_inicio = 0; }

$texto_buscar        = $_REQUEST['texto_buscar'];
$buscar              = $_REQUEST['buscar'];
$id_carrera          = $_REQUEST['id_carrera'];
$jornada             = $_REQUEST['jornada'];
$semestre_cohorte    = $_REQUEST['semestre_cohorte'];
$mes_cohorte         = $_REQUEST['mes_cohorte'];
$cohorte             = $_REQUEST['cohorte'];
$estado              = $_REQUEST['estado'];
$moroso_financiero   = $_REQUEST['moroso_financiero'];
$admision            = $_REQUEST['admision'];
$regimen             = $_REQUEST['regimen'];
$aprob_ant           = $_REQUEST['aprob_ant'];
$id_riesgo_desercion = $_REQUEST['id_riesgo_desercion'];
$matriculado         = $_REQUEST['matriculado'];
$rend_c1             = $_REQUEST['rend_c1'];
$rend_s1             = $_REQUEST['rend_s1'];
$rend_s2             = $_REQUEST['rend_s2'];
//$sies_info         = $_REQUEST['sies_info'];
$ano_sies            = $_REQUEST['ano_sies'];

$ver_datos_contacto = $_REQUEST['ver_datos_contacto'];

if (empty($_REQUEST['matriculado'])) { $matriculado = "t"; }
if (empty($_REQUEST['cohorte'])) { $cohorte = $ANO; }
if (empty($_REQUEST['semestre_cohorte'])) { $semestre_cohorte = $SEMESTRE; }
if (empty($_REQUEST['mes_cohorte'])) { $mes_cohorte = 0; }
if (empty($_REQUEST['estado'])) { $estado = 1; }
if (empty($_REQUEST['moroso_financiero'])) { $moroso_financiero = -1; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }
if (empty($_REQUEST['id_riesgo_desercion'])) { $id_riesgo_desercion = "t"; }
if (!empty($ids_carreras) && empty($_REQUEST['regimen'])) { $regimen = "t"; }
if (empty($_REQUEST['aprob_ant'])) { $aprob_ant = 't'; }
if (empty($cond_base)) { $cond_base = "true"; }
if ($regimen == "POST-GD") { $matriculado = "a"; }

$condicion = "WHERE $cond_base  ";
$cond_ext = "WHERE true ";

if ($buscar == 'Buscar' && $texto_buscar <> "") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar = explode(" ",$texto_buscar_regexp);
	$condicion .= " AND ";
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= "(lower(a.nombres||' '||a.apellidos) ~* '$cadena_buscada' OR "
		            . " a.rut ~* '$cadena_buscada' OR "
		            . " text(a.id) ~* '$cadena_buscada' "
		            . ") AND ";
	}
	$condicion=substr($condicion,0,strlen($condicion)-4);
	$cohorte = $semestre_cohorte = $estado = $id_carrera = $admision = $matriculado = $regimen = $ano_sies = null;
} else {

	if ($cohorte > 0) {
		$condicion .= "AND (a.cohorte = '$cohorte') ";
	}

	if ($semestre_cohorte > 0) {
		$condicion .= "AND (a.semestre_cohorte = $semestre_cohorte) ";
	}

	if ($mes_cohorte > 0) {
		$condicion .= "AND (a.mes_cohorte = $mes_cohorte) ";
	}
	 
	if ($estado <> "-1") {
		$condicion .= "AND (a.estado = '$estado') ";
	}

	if ($moroso_financiero <> "-1") {
		$condicion .= "AND (a.moroso_financiero = '$moroso_financiero') ";
	}
	
	if ($id_carrera <> "") {
		$condicion .= "AND (a.carrera_actual = $id_carrera) ";
	}

	if ($jornada <> "") {
		$condicion .= "AND (a.jornada = '$jornada') ";
	}

	if ($admision <> "") {
		$condicion .= "AND (a.admision = '$admision') ";
	}

	if ($regimen <> "" && $regimen <> "t") {
		$condicion .= "AND (c.regimen = '$regimen') ";
	}
	
    $SQL_mat = "SELECT id_alumno FROM matriculas WHERE ano=$ANO AND semestre=$SEMESTRE";
	if ($matriculado == "t") {
		$condicion .= "AND (a.id IN ($SQL_mat)) ";
	} elseif ($matriculado == "f") {
		$condicion .= "AND (a.id NOT IN ($SQL_mat)) ";
	}
	
	switch ($rend_c1) {
		case "malo":
			$cond_ext .= " AND (c1_aprob::float/cant_cursos_insc::float < 0.33)";
			break;
		case "regular":
			$cond_ext .= " AND (c1_aprob::float/cant_cursos_insc::float BETWEEN 0.33 AND 0.66)";
			break;
		case "bueno":
			$cond_ext .= " AND (c1_aprob::float/cant_cursos_insc::float > 0.66)";			
	}

	switch ($rend_s1) {
		case "malo":
			$cond_ext .= " AND (s1_aprob::float/cant_cursos_insc::float < 0.33)";
			break;
		case "regular":
			$cond_ext .= " AND (s1_aprob::float/cant_cursos_insc::float BETWEEN 0.33 AND 0.66)";
			break;
		case "bueno":
			$cond_ext .= " AND (s1_aprob::float/cant_cursos_insc::float > 0.66)";			
	}
	
	switch ($rend_s2) {
		case "malo":
			$cond_ext .= " AND (s2_aprob::float/cant_cursos_insc::float < 0.33)";
			break;
		case "regular":
			$cond_ext .= " AND (s2_aprob::float/cant_cursos_insc::float BETWEEN 0.33 AND 0.66)";
			break;
		case "bueno":
			$cond_ext .= " AND (s2_aprob::float/cant_cursos_insc::float > 0.66)";			
	}
	
	//if ($id_riesgo_desercion <> "t") { $cond_ext .= "AND (riesgo_desercion = '$id_riesgo_desercion') "; }
	
	//if ($sies_info == "t") { $condicion .= " AND (sies.rut IS NOT NULL) "; }
	//if ($sies_info == "f") { $condicion .= " AND (sies.rut IS NULL) "; }
  
	if ($ano_sies > 0) { 
		$SQL_sies_ano = "SELECT rut FROM alumnos_sies WHERE ano=$ano_sies";
		if ($regimen <> "" && $regimen <> "t") { $SQL_sies_ano .= " AND regimen='$regimen' "; }    
		$condicion .= " AND a.rut IN ($SQL_sies_ano) "; 
	}

}

if (!empty($ids_carreras) && empty($id_carrera)) {
	$condicion .= " AND a.carrera_actual IN ($ids_carreras) ";
}

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_al_presente = "SELECT count(periodo) AS semestre_presente
                    FROM (SELECT id_alumno,ano||'-'||semestre as periodo 
                          FROM vista_alumnos_cursos 
                          WHERE id_alumno=a.id AND semestre>0 
                          GROUP BY id_alumno,periodo) AS foo 
                    GROUP BY id_alumno";

$SQL_tr_act = "SELECT count(id_alumno) FROM inscripciones_cursos WHERE id_alumno=a.id";

$SQL_tr_act = "SELECT count(id_alumno) FROM cargas_academicas WHERE id_alumno=a.id AND id_curso IN (SELECT id FROM cursos WHERE semestre=$semestre_cohorte AND ano=$cohorte) AND (id_estado IS NULL OR id_estado <> 6)";

if ($semestre_cohorte == 1) {
	$SQL_tr_2sem = "SELECT count(id_alumno) FROM cargas_academicas WHERE id_alumno=a.id AND id_curso IN (SELECT id FROM cursos WHERE semestre=2 AND ano=$cohorte) AND (id_estado IS NULL OR id_estado <> 6)";
}

$SQL_convalidaciones = "SELECT date_part('year',now())-conv.ano AS antiguedad 
                        FROM cargas_academicas AS ca 
                        LEFT JOIN vista_convalidaciones AS conv ON conv.id=ca.id_convalida
                        WHERE ca.id_alumno=a.id AND ca.convalidado";

$SQL_foto = "SELECT dd.id FROM doctos_digitalizados dd LEFT JOIN doctos_digital_tipos ddt ON dd.id_tipo=ddt.id WHERE rut=a.rut AND ddt.alias='fotos' AND NOT eliminado";

/* Calculo de diferencia en dos pruebas de nivelacion
$SQL_nivelacion = "SELECT p.rut_alumno,s.nota - p.nota as dif,s.nota AS nota_2da,p.nota AS nota_1ra
                   FROM sinergia.resp_pruebas_diag AS p 
                   LEFT JOIN sinergia.resp_pruebas_diag AS s USING (rut_alumno) 
                   WHERE s.oportunidad > p.oportunidad";

$SQL_alumnos=          CASE WHEN niv.dif > 0 THEN 'Aumenta'
                            WHEN niv.dif < 0 THEN 'Disminuye'
                            WHEN niv.dif = 0 THEN 'Igual'
                            WHEN niv.dif IS NULL THEN 'NSP'
                        END AS nivelacion,niv.nota_1ra,niv.nota_2da,
*/

$SQL_nivelacion = "SELECT rut_alumno,max(nota) AS nota FROM sinergia.resp_pruebas_diag WHERE ano=$cohorte AND semestre=$semestre_cohorte GROUP BY rut_alumno";

$color_nsp    = "#FFFF00";
$color_na     = "#BFBFBF";
$color_reprob = "#FF0000";
$color_aprob  = "#0000FF";

$SQL_c1        = "SELECT count(c1) FROM vista_seg_rendacad WHERE ano=$cohorte AND semestre=$semestre_cohorte AND id_alumno=a.id ";
$SQL_c1_nsp    = $SQL_c1 . "AND c1='NSP' GROUP BY id_alumno";
$SQL_c1_na     = $SQL_c1 . "AND c1='N/A' GROUP BY id_alumno";
$SQL_c1_reprob = $SQL_c1 . "AND c1='Reprob' GROUP BY id_alumno";
$SQL_c1_aprob  = $SQL_c1 . "AND c1='Aprob' GROUP BY id_alumno";

$SQL_s1        = "SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=$cohorte AND semestre=$semestre_cohorte AND id_alumno=a.id ";
$SQL_s1_nsp    = $SQL_s1 . "AND solemne1='NSP' GROUP BY id_alumno";
$SQL_s1_na     = $SQL_s1 . "AND solemne1='N/A' GROUP BY id_alumno";
$SQL_s1_reprob = $SQL_s1 . "AND solemne1='Reprob' GROUP BY id_alumno";
$SQL_s1_aprob  = $SQL_s1 . "AND solemne1='Aprob' GROUP BY id_alumno";

$SQL_s2        = "SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=$cohorte AND semestre=$semestre_cohorte AND id_alumno=a.id ";
$SQL_s2_nsp    = $SQL_s2 . "AND solemne2='NSP' GROUP BY id_alumno";
$SQL_s2_na     = $SQL_s2 . "AND solemne2='N/A' GROUP BY id_alumno";
$SQL_s2_reprob = $SQL_s2 . "AND solemne2='Reprob' GROUP BY id_alumno";
$SQL_s2_aprob  = $SQL_s2 . "AND solemne2='Aprob' GROUP BY id_alumno";

$sem_cohorte_2do = ($semestre_cohorte == 1) ? 2 : 1;

$SQL_tr_2dosem = "SELECT count(id_alumno) FROM cargas_academicas WHERE id_alumno=a.id AND id_curso IN (SELECT id FROM cursos WHERE semestre=$sem_cohorte_2do AND ano=$cohorte) AND (id_estado IS NULL OR id_estado <> 6)";

$SQL_2sem_c1        = "SELECT count(c1) FROM vista_seg_rendacad WHERE ano=$cohorte AND semestre=$sem_cohorte_2do AND id_alumno=a.id ";
$SQL_2sem_c1_nsp    = $SQL_2sem_c1 . "AND c1='NSP' GROUP BY id_alumno";
$SQL_2sem_c1_na     = $SQL_2sem_c1 . "AND c1='N/A' GROUP BY id_alumno";
$SQL_2sem_c1_reprob = $SQL_2sem_c1 . "AND c1='Reprob' GROUP BY id_alumno";
$SQL_2sem_c1_aprob  = $SQL_2sem_c1 . "AND c1='Aprob' GROUP BY id_alumno";

$SQL_2sem_s1        = "SELECT count(solemne1) FROM vista_seg_rendacad WHERE ano=$cohorte AND semestre=$sem_cohorte_2do AND id_alumno=a.id ";
$SQL_2sem_s1_nsp    = $SQL_2sem_s1 . "AND solemne1='NSP' GROUP BY id_alumno";
$SQL_2sem_s1_na     = $SQL_2sem_s1 . "AND solemne1='N/A' GROUP BY id_alumno";
$SQL_2sem_s1_reprob = $SQL_2sem_s1 . "AND solemne1='Reprob' GROUP BY id_alumno";
$SQL_2sem_s1_aprob  = $SQL_2sem_s1 . "AND solemne1='Aprob' GROUP BY id_alumno";

$SQL_2sem_s2        = "SELECT count(solemne2) FROM vista_seg_rendacad WHERE ano=$cohorte AND semestre=$sem_cohorte_2do AND id_alumno=a.id ";
$SQL_2sem_s2_nsp    = $SQL_2sem_s2 . "AND solemne2='NSP' GROUP BY id_alumno";
$SQL_2sem_s2_na     = $SQL_2sem_s2 . "AND solemne2='N/A' GROUP BY id_alumno";
$SQL_2sem_s2_reprob = $SQL_2sem_s2 . "AND solemne2='Reprob' GROUP BY id_alumno";
$SQL_2sem_s2_aprob  = $SQL_2sem_s2 . "AND solemne2='Aprob' GROUP BY id_alumno";

$SQL_prom_nf_s1    = "SELECT avg(nota_final)::numeric(3,1) FROM vista_seg_rendacad WHERE ano=$cohorte AND semestre=1 AND id_alumno=a.id ";
$SQL_prom_nf_s2    = "SELECT avg(nota_final)::numeric(3,1) FROM vista_seg_rendacad WHERE ano=$cohorte AND semestre=2 AND id_alumno=a.id ";
$SQL_prom_nf_anual = "SELECT avg(nota_final)::numeric(3,1) FROM vista_seg_rendacad WHERE ano=$cohorte AND id_alumno=a.id ";

$SQL_pruebas_psico = "SELECT count(id) FROM sinergia.respuestas WHERE rut_alumno=a.rut";

$SQL__als    = "SELECT a.id,trim(a.rut) AS rut,upper(a.apellidos) AS apellidos,initcap(a.nombres) AS nombres,
                       a.genero,a.fec_nac,c.alias||'-'||a.jornada AS carrera,c.regimen,
                       ($SQL_foto) AS id_foto,
                       a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.mes_cohorte,
                       a.tel_movil,a.telefono,a.email,a.nombre_usuario||'@alumni.umc.cl' AS email_gsuite,
                       ae.nombre AS estado,to_char(a.estado_fecha,'DD-MM-YYYY') AS estado_fecha,
                       ($SQL_tr_act) AS cant_cursos_insc,
                       ($SQL_tr_2dosem) AS cant_cursos_insc_2dosem,
                       CASE WHEN mat.id_alumno IS NOT NULL THEN 'Si' ELSE 'No' END AS matriculado,moroso_financiero,
                       coalesce(niv.nota::text,'NSP') AS nivelacion,
                       CASE WHEN niv.nota IS NULL            THEN 10
                            WHEN niv.nota BETWEEN 1 AND 3.99 THEN 10
                            WHEN niv.nota BETWEEN 4 AND 5    THEN 8
                            WHEN niv.nota BETWEEN 5 AND 6    THEN 4
                            WHEN niv.nota BETWEEN 6 AND 7    THEN 0
                       END riesgo_niv,
                       CASE WHEN moroso_financiero THEN 30 ELSE 0 END AS riesgo_morosidad,
                       CASE WHEN ($SQL_pruebas_psico) = 0 THEN 10 ELSE 0 END AS riesgo_pruebas_psico,
                       CASE WHEN round(($SQL_c1_aprob)::numeric*100/($SQL_c1)) BETWEEN 67 AND 100 THEN 0
                            WHEN round(($SQL_c1_aprob)::numeric*100/($SQL_c1)) BETWEEN 34 AND 66  THEN 5
                            WHEN round(($SQL_c1_aprob)::numeric*100/($SQL_c1)) BETWEEN 0  AND 33  THEN 10
                            WHEN ($SQL_c1_aprob) IS NULL                                          THEN 10
                       END AS riesgo_c1,
                       CASE WHEN ($SQL_c1_na) = ($SQL_c1) THEN 10 ELSE 0 END AS base_riesgo_c1,
                       CASE WHEN round(($SQL_s1_aprob)::numeric*100/($SQL_s1)) BETWEEN 67 AND 100 THEN 0
                            WHEN round(($SQL_s1_aprob)::numeric*100/($SQL_s1)) BETWEEN 34 AND 66  THEN 10
                            WHEN round(($SQL_s1_aprob)::numeric*100/($SQL_s1)) BETWEEN 0  AND 33  THEN 20
                            WHEN ($SQL_s1_aprob) IS NULL                                          THEN 20
                       END AS riesgo_s1,
                       CASE WHEN ($SQL_s1_na) = ($SQL_s1) THEN 20 ELSE 0 END AS base_riesgo_s1,
                       CASE WHEN round(($SQL_s2_aprob)::numeric*100/($SQL_s2)) BETWEEN 67 AND 100 THEN 0
                            WHEN round(($SQL_s2_aprob)::numeric*100/($SQL_s2)) BETWEEN 34 AND 66  THEN 10
                            WHEN round(($SQL_s2_aprob)::numeric*100/($SQL_s2)) BETWEEN 0  AND 33  THEN 20
                            WHEN ($SQL_s2_aprob) IS NULL                                          THEN 20
                       END AS riesgo_s2,
                       CASE WHEN ($SQL_s2_na) = ($SQL_s2) THEN 20 ELSE 0 END AS base_riesgo_s2,
                       CASE WHEN round(($SQL_2sem_c1_aprob)::numeric*100/($SQL_2sem_c1)) BETWEEN 67 AND 100 THEN 0
                            WHEN round(($SQL_2sem_c1_aprob)::numeric*100/($SQL_2sem_c1)) BETWEEN 34 AND 66  THEN 5
                            WHEN round(($SQL_2sem_c1_aprob)::numeric*100/($SQL_2sem_c1)) BETWEEN 0  AND 33  THEN 10
                            WHEN ($SQL_2sem_c1_aprob) IS NULL                                          THEN 10
                       END AS riesgo_c1_sem2,
                       CASE WHEN ($SQL_2sem_c1_na) = ($SQL_2sem_c1) THEN 10 ELSE 0 END AS base_riesgo_c1_sem2,
                       CASE WHEN round(($SQL_2sem_s1_aprob)::numeric*100/($SQL_2sem_s1)) BETWEEN 67 AND 100 THEN 0
                            WHEN round(($SQL_2sem_s1_aprob)::numeric*100/($SQL_2sem_s1)) BETWEEN 34 AND 66  THEN 10
                            WHEN round(($SQL_2sem_s1_aprob)::numeric*100/($SQL_2sem_s1)) BETWEEN 0  AND 33  THEN 20
                            WHEN ($SQL_2sem_s1_aprob) IS NULL                                          THEN 20
                       END AS riesgo_s1_sem2,
                       CASE WHEN ($SQL_2sem_s1_na) = ($SQL_2sem_s1) THEN 20 ELSE 0 END AS base_riesgo_s1_sem2,
                       CASE WHEN round(($SQL_2sem_s2_aprob)::numeric*100/($SQL_2sem_s2)) BETWEEN 67 AND 100 THEN 0
                            WHEN round(($SQL_2sem_s2_aprob)::numeric*100/($SQL_2sem_s2)) BETWEEN 34 AND 66  THEN 10
                            WHEN round(($SQL_2sem_s2_aprob)::numeric*100/($SQL_2sem_s2)) BETWEEN 0  AND 33  THEN 20
                            WHEN ($SQL_2sem_s2_aprob) IS NULL                                          THEN 20
                       END AS riesgo_s2_sem2,
                       CASE WHEN ($SQL_2sem_s2_na) = ($SQL_2sem_s2) THEN 20 ELSE 0 END AS base_riesgo_s2_sem2,
                       ($SQL_c1_nsp) AS c1_nsp,($SQL_c1_na) AS c1_na,($SQL_c1_reprob) AS c1_reprob,($SQL_c1_aprob) AS c1_aprob,
                       ($SQL_s1_nsp) AS s1_nsp,($SQL_s1_na) AS s1_na,($SQL_s1_reprob) AS s1_reprob,($SQL_s1_aprob) AS s1_aprob,
                       ($SQL_s2_nsp) AS s2_nsp,($SQL_s2_na) AS s2_na,($SQL_s2_reprob) AS s2_reprob,($SQL_s2_aprob) AS s2_aprob,
                       ($SQL_2sem_c1_nsp) AS c1_nsp_2sem,($SQL_2sem_c1_na) AS c1_na_2sem,($SQL_2sem_c1_reprob) AS c1_reprob_2sem,($SQL_2sem_c1_aprob) AS c1_aprob_2sem,
                       ($SQL_2sem_s1_nsp) AS s1_nsp_2sem,($SQL_2sem_s1_na) AS s1_na_2sem,($SQL_2sem_s1_reprob) AS s1_reprob_2sem,($SQL_2sem_s1_aprob) AS s1_aprob_2sem,
                       ($SQL_2sem_s2_nsp) AS s2_nsp_2sem,($SQL_2sem_s2_na) AS s2_na_2sem,($SQL_2sem_s2_reprob) AS s2_reprob_2sem,($SQL_2sem_s2_aprob) AS s2_aprob_2sem,
                       ($SQL_prom_nf_s1) AS prom_nf_s1,($SQL_prom_nf_s2) AS prom_nf_s2,($SQL_prom_nf_anual) AS prom_nf_anual
                FROM alumnos AS a
                LEFT JOIN pap ON pap.id=a.id_pap
                LEFT JOIN carreras   AS c ON c.id=a.carrera_actual
                LEFT JOIN al_estados AS ae ON ae.id=a.estado
                LEFT JOIN ($SQL_nivelacion) AS niv ON niv.rut_alumno=a.rut
                LEFT JOIN matriculas AS mat ON (mat.id_alumno=a.id AND mat.semestre=$SEMESTRE AND mat.ano=$ANO)
                $condicion
                ORDER BY a.apellidos,a.nombres ";
echo("\n\n<!-- $SQL__als -->");
$SQL_sum_riesgo  = "riesgo_niv+riesgo_morosidad+riesgo_pruebas_psico+riesgo_c1+riesgo_s1+riesgo_s2";
//$SQL_sum_riesgo  = "riesgo_niv+riesgo_morosidad+riesgo_pruebas_psico+riesgo_c1+riesgo_s1+riesgo_s2-base_riesgo_c1-base_riesgo_s1-base_riesgo_s2";
//$SQL_base_riesgo = "100-base_riesgo_c1-base_riesgo_s1-base_riesgo_s2";
$SQL_base_riesgo = "100";
$SQL_alumnos = "SELECT *,round(($SQL_sum_riesgo)*100::numeric/($SQL_base_riesgo)) AS tasa_riesgo FROM ($SQL__als) AS al $cond_ext";
$SQL_alumnos2 = "SELECT *,CASE WHEN tasa_riesgo BETWEEN 75 and 100 THEN 'Alto'
                              WHEN tasa_riesgo BETWEEN 50 and 74  THEN 'Medio Alto'
                              WHEN tasa_riesgo BETWEEN 25 and 50  THEN 'Medio'
                              WHEN tasa_riesgo BETWEEN 0  and 25  THEN 'Bajo' END AS riesgo_desercion
                FROM ($SQL_alumnos) AS foo ";
if ($id_riesgo_desercion <> "" && $id_riesgo_desercion <> "t") {
	$SQL_alumnos2 = "SELECT * FROM ($SQL_alumnos2) AS foo WHERE riesgo_desercion = '$id_riesgo_desercion'	";
}
$SQL_tabla_completa = "COPY ($SQL_alumnos2) to stdout WITH CSV HEADER";
$SQL_alumnos3 = $SQL_alumnos2 . " $limite_reg OFFSET $reg_inicio";
$alumnos = consulta_sql($SQL_alumnos3);


//echo("<!-- $SQL_alumnos -->");

$SQL_cursos_act          = "SELECT id FROM cursos WHERE ano=$ANO";
$SQL_cursos_ano_ant      = "SELECT id FROM cursos WHERE ano=$ANO-1";
$SQL_cursos_ano_ant_1sem = "SELECT id FROM cursos WHERE ano=$ANO-1 AND semestre=1";
$SQL_cursos_ano_ant_2sem = "SELECT id FROM cursos WHERE ano=$ANO-1 AND semestre=2";

$SQL_asig_ano_ant_insc  = "SELECT count(ca.id) FROM cargas_academicas AS ca WHERE id_curso IN ($SQL_cursos_ano_ant) AND id_alumno=a.id AND NOT (solemne1=-1 AND solemne2=-1)";
$SQL_asig_ano_ant_aprob = "SELECT count(ca.id) FROM cargas_academicas AS ca WHERE id_curso IN ($SQL_cursos_ano_ant) AND id_alumno=a.id AND id_estado=1";

$SQL_prom_ano_ant_1sem  = "SELECT (avg(ca.nota_final)*100)::int2 FROM cargas_academicas AS ca WHERE id_curso IN ($SQL_cursos_ano_ant_1sem) AND id_alumno=a.id AND NOT (solemne1=-1 AND solemne2=-1)";
$SQL_prom_ano_ant_2sem  = "SELECT (avg(ca.nota_final)*100)::int2 FROM cargas_academicas AS ca WHERE id_curso IN ($SQL_cursos_ano_ant_2sem) AND id_alumno=a.id AND NOT (solemne1=-1 AND solemne2=-1)";

$SQL_asig_hist_insc     = "SELECT count(ca.id) FROM cargas_academicas AS ca WHERE id_curso NOT IN ($SQL_cursos_act) AND id_alumno=a.id AND NOT (solemne1=-1 AND solemne2=-1)";
$SQL_asig_hist_aprob    = "SELECT count(ca.id) FROM cargas_academicas AS ca WHERE id_curso NOT IN ($SQL_cursos_act) AND id_alumno=a.id AND id_estado=1";

$SQL_nivel_acad         = "SELECT max(nivel) 
                           FROM vista_alumnos_cursos AS vac 
                           LEFT JOIN detalle_mallas dm ON (dm.id_malla=a.malla_actual AND dm.id_prog_asig=vac.id_prog_asig) 
                           WHERE vac.ano=$ANO AND vac.id_alumno=a.id";

$SQL_nivel_acad_alter   = "SELECT max(nivel) 
                           FROM vista_alumnos_cursos AS vac 
                           LEFT JOIN detalle_mallas dm ON (dm.id_malla=a.malla_actual AND dm.id_prog_asig=vac.id_prog_asig) 
                           WHERE vac.id_estado=1 AND vac.id_alumno=a.id";
                           
$SQL_ano_ing_orig       = "SELECT min(ano) AS ano_ing_orig FROM cargas_academicas AS ca LEFT JOIN convalidaciones AS c ON c.id=ca.id_convalida WHERE ca.id_alumno=a.id";

$enlace_nav = "$enlbase=$modulo"
            . "&mes_cohorte=$mes_cohorte"
            . "&semestre_cohorte=$semestre_cohorte"
            . "&cohorte=$cohorte"
            . "&estado=$estado"
            . "&moroso_financiero=$moroso_financiero"
            . "&admision=$admision"            
            . "&matriculado=$matriculado"
            . "&id_carrera=$id_carrera"
            . "&jornada=$jornada"
            . "&regimen=$regimen"
            . "&ver_datos_contacto=$ver_datos_contacto"
            . "&texto_buscar=$texto_buscar"
            . "&buscar=$buscar"
            . "&r_inicio";

if (count($alumnos) > 0) {
	$SQL_total_alumnos = "SELECT count(a.id) AS total_alumnos 
	                      FROM alumnos AS a 
	                      LEFT JOIN carreras AS c ON c.id=a.carrera_actual 
	                      LEFT JOIN matriculas AS mat ON (mat.id_alumno=a.id AND mat.semestre=$SEMESTRE AND mat.ano=$ANO)
	                      $condicion";
	$SQL_total_alumnos = "SELECT count(id) AS total_alumnos FROM ($SQL_alumnos2) AS foo $cond_ext";
	$total_alumnos = consulta_sql($SQL_total_alumnos);
	$tot_reg = $total_alumnos[0]['total_alumnos'];
	
	$HTML_paginador = "Páginas ".html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}

if (count($alumnos) == 1 && $texto_buscar <> "") {
	echo(js("window.location='$enlbase=ver_alumno&id_alumno={$alumnos[0]['id']}&rut={$alumnos[0]['rut']}';"));
}

$prueba = "AF5";
$ano_periodo = $cohorte;
$semestre_periodo = $semestre_cohorte;
$prueba_respuestas = sinergia_respuestas($prueba,$ano_periodo,$semestre_periodo,$condicion);
$prueba_af5        = sinergia_AF5($prueba_respuestas);

$prueba = "ACRA";
$prueba_respuestas = sinergia_respuestas($prueba,$ano_periodo,$semestre_periodo,$condicion);
$prueba_acra       = sinergia_ACRA($prueba_respuestas);
//var_dump($prueba_af5);

$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "" && $regimen <> "t") { $cond_carreras .= "AND regimen='$regimen' "; }

$SQL_carreras = "SELECT id,nombre||' ('||alias||')' AS nombre FROM carreras $cond_carreras ORDER BY nombre;";
$carreras = consulta_sql($SQL_carreras);

$carreras_act_adm = consulta_sql("SELECT id,nombre||' ('||alias||')' AS nombre FROM carreras $cond_carreras AND activa AND admision ORDER BY nombre");
$carreras_noact_noadm = consulta_sql("SELECT id,nombre||' ('||alias||')' AS nombre FROM carreras $cond_carreras AND NOT activa AND NOT admision ORDER BY nombre");

$SQL_al_estados = "SELECT id,nombre FROM al_estados WHERE nombre NOT IN ('Moroso') ORDER BY id;";
$al_estados = consulta_sql($SQL_al_estados);

$cohortes = $anos;

$SEMESTRES_COHORTES = array(array("id"=>1,"nombre"=>1),
                            array("id"=>2,"nombre"=>2));

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

$APROB_ANT = array(array("id" => 1, "nombre" => 'Mala (0%)'),
                   array("id" => 2, "nombre" => 'Regular (1% ~ 39%)'),
                   array("id" => 3, "nombre" => 'Buena (40% ~ 100%)'));

$RIESGO_DESERCION = array(array("id" => 'Alto',       "nombre" => 'Alto (rtd >= 75%)'),
                          array("id" => 'Medio Alto', "nombre" => 'Medio Alto (50% <= rtd < 75%)'),
                          array("id" => 'Medio',      "nombre" => 'Medio (25% <= rtd < 50%)'),
                          array("id" => 'Bajo',       "nombre" => 'Bajo (rtd < 25%)</sup>'));

$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);

$SIES_anos = consulta_sql("SELECT DISTINCT ON (ano) ano AS id,ano AS nombre FROM alumnos_sies ORDER BY ano DESC");

$REND = array(array('id'=>"malo",    'nombre'=>"Aprobación inferior al 33%"),
              array('id'=>"regular", 'nombre'=>"Aprobación entre el 33% y 66%"),
              array('id'=>"bueno",   'nombre'=>"Aprobación superior al 66%"));

?>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<div class="texto">
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Cohorte: <br>
<?php if ($regimen <> "PRE") { ?>          
          <select class="filtro" name="mes_cohorte" onChange="submitform();">
            <option value="0">-- mes --</option>
            <?php echo(select($meses_fn,$mes_cohorte)); ?>    
          </select>
          -
<?php } ?>
          <select class="filtro" name="semestre_cohorte" onChange="submitform();">
            <option value="0"></option>
            <?php echo(select($SEMESTRES_COHORTES,$semestre_cohorte)); ?>    
          </select>
          -
          <select class="filtro" name="cohorte" onChange="submitform();">
            <option value="0">Todas</option>
            <?php echo(select($cohortes,$cohorte)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Estado: <br>
          <select class="filtro" name="estado" onChange="submitform();">
            <option value="-1">Todos</option>
            <?php echo(select($al_estados,$estado)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Moroso: <br>
          <select class="filtro" name="moroso_financiero" onChange="submitform();">
            <option value="-1">Todos</option>
            <?php echo(select($sino,$moroso_financiero)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Admisión: <br>
          <select class="filtro" name="admision" onChange="submitform();">
            <option value="">Todos</option>
            <?php echo(select($ADMISION,$admision)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Matriculado: <br>
          <select class="filtro" name="matriculado" onChange="submitform();">
            <option value="a">Todos</option>
            <?php echo(select($sino,$matriculado)); ?>
          </select>
        </td>
<!--        <td class="celdaFiltro">
          Rendimiento C1: <br>
          <select class="filtro" name="rend_c1" onChange="submitform();">
            <option value="a">Todos</option>
            <?php echo(select($REND,$rend_c1)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Rendimiento S1: <br>
          <select class="filtro" name="rend_s1" onChange="submitform();">
            <option value="a">Todos</option>
            <?php echo(select($REND,$rend_s1)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Rendimiento S2: <br>
          <select class="filtro" name="rend_s2" onChange="submitform();">
            <option value="a">Todos</option>
            <?php echo(select($REND,$rend_s2)); ?>
          </select>
        </td> -->
        <td class="celdaFiltro">
          Riesgo de Deserción: <br>
          <select class="filtro" name="id_riesgo_desercion" onChange="submitform();">
            <option value="t">Todos</option>
            <?php echo(select($RIESGO_DESERCION,$id_riesgo_desercion)); ?>
          </select>
       </td>
      </tr>
    </table>
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>        
        <td class="celdaFiltro">
          Carrera/Programa:<br>
          <select class="filtro" name="id_carrera" onChange="submitform();">
            <option value="">Todas</option>
            <optgroup label="Activas y Admisión abierta">
              <?php echo(select($carreras_act_adm,$id_carrera)); ?>
            </optgroup>
            <optgroup label="Inactivas y Admisión cerrada">
              <?php echo(select($carreras_noact_noadm,$id_carrera)); ?>
            </optgroup>
          </select>
        </td>
        <td class="celdaFiltro">
          Jornada:<br>
          <select class="filtro" name="jornada" onChange="submitform();">
            <option value="">Ambas</option>
            <?php echo(select($JORNADAS,$jornada)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Régimen: <br>
          <select class="filtro" name="regimen" onChange="submitform();">
            <option value="t">Todos</option>
            <?php echo(select($REGIMENES,$regimen)); ?>
          </select>
        </td>
      </tr>
    </table>
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Buscar por ID, RUT o nombre:<br>
          <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="60" id="texto_buscar" class='boton'>
          <input type='submit' name='buscar' value='Buscar'>          
          <?php 
          	if ($buscar == "Buscar" && $texto_buscar <> "") {
          		echo(" <input type='submit' name='buscar' value='Vaciar'>");
          	};
          ?>          <script>document.getElementById("texto_buscar").focus();</script>
        </td>
        <td class="celdaFiltro">
          Ver Datos de Contacto:<br>
          <input type="checkbox" name="ver_datos_contacto" value="Si" class='boton' onClick="submitform();" <?php if($ver_datos_contacto=="Si") { echo("checked"); } ?>>
        </td>
        <td class="celdaFiltro">
          Año SIES: <br>
          <select class="filtro" name="ano_sies" onChange="submitform();">
            <option value="">--</option>
            <?php echo(select($SIES_anos,$ano_sies)); ?>
          </select>
        </td>
      </tr>
    </table>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="5">
      Mostrando <b><?php echo($tot_reg); ?></b> alumno(s) en total, en página(s) de
      <select class='filtro' name="cant_reg" onChange="submitform();">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas
    </td>
    <td class="texto" colspan="3">
      <small><b>
        <span style='background: #0000FF; color: #FFFFFF'>&nbsp;Aprueba&nbsp;</span>&nbsp;
        <span style='background: #FF0000; color: #FFFFFF'>&nbsp;Reprueba&nbsp;</span>&nbsp;
        <span style='background: #FFFF00; color: #000000'>&nbsp;NSP&nbsp;</span>&nbsp;
        <span style='background: #BFBFBF; color: #FFFFFF'>&nbsp;Sin registro&nbsp;</span>
      </b></small>
    </td>
    <td class="texto" colspan="8" align="right">
      <?php echo($HTML_paginador); ?>
      <?php echo($boton_tabla_completa); ?>
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' rowspan="2"></td>
    <td class='tituloTabla' rowspan="2">RUT</td>
    <td class='tituloTabla' rowspan="2">Nombre</td>
    <td class='tituloTabla' rowspan="2">Carrera</td>
    <td class='tituloTabla' rowspan="2">Estado</td>
    <td class='tituloTabla' rowspan="2">Mat?<br>C. Insc.</td>
    <td class='tituloTabla' rowspan="2">Ind.</td>
    <td class='tituloTabla' rowspan="2">Pruebas<br>Psicométricas</td>
<!--    <td class='tituloTabla' colspan="4">AF5</td> -->
<!--    <td class='tituloTabla' colspan="4">ACRA</td> -->
    <td class='tituloTabla' colspan="3">Rendimiento Semestre I</td>
    <td class='tituloTabla' colspan="3">Rendimiento Semestre II</td>
    <td class='tituloTabla' rowspan="2">Riesgo<br>Deserción</td>
  </tr>
  <tr class='filaTituloTabla'>
<!--    <td class='tituloTabla'>Acad/Lab</td> -->
<!--    <td class='tituloTabla'>Social</td> -->
<!--    <td class='tituloTabla'>Emoc.</td> -->
<!--    <td class='tituloTabla'>Familiar</td> -->
<!--    <td class='tituloTabla'>Adq.</td> -->
<!--    <td class='tituloTabla'>Cod.</td> -->
<!--    <td class='tituloTabla'>Rec.</td> -->
<!--    <td class='tituloTabla'>Proc.</td> -->
    <td class='tituloTabla'>C1</td>
    <td class='tituloTabla'>S1</td>
    <td class='tituloTabla'>S2</td>
    <td class='tituloTabla'>C1</td>
    <td class='tituloTabla'>S1</td>
    <td class='tituloTabla'>S2</td>
  </tr>

<?php
	$HTML_alumnos = "";
	if (count($alumnos) > 0) {
		for ($x=0;$x<count($alumnos);$x++) {
			extract($alumnos[$x]);
			
			$enl = "$enlbase=$modulo_destino&id_alumno=$id&rut=$rut";
			$enlace = "a class='enlitem' href='$enl'";
			
			if ($moroso_financiero == "t") { $estado .= "<br><span style='color: red; font-size: 8pt; font-weight: bold'>(M)</span>"; }
			
			if ($mes_cohorte <> "") { $mes_cohorte = "(".substr($meses_palabra[$mes_cohorte-1]['nombre'],0,3).")"; }
			
			$HTML_datos_contacto = "";
			if ($ver_datos_contacto == "Si") { 
				$HTML_datos_contacto = "    <td class='textoTabla'><small>$telefono</small></td>"
									 . "    <td class='textoTabla'><small>$tel_movil</small></td>"
									 . "    <td class='textoTabla'><small>$email</small></td>";
			}
            
            switch ($nivelacion) {
                case "Disminuye":
                case "Aumenta":
                case "Igual":
                    $nivelacion .= "<br><small>1ra opt.: $nota_1ra 2da opt.: $nota_2da</small>";
                    break;
                case "NSP":	
                    $nivelacion = "<span class='No'>$nivelacion</small>";
            }
            
            $s1 = $c1 = $s2 = "N/A";
            if ($cant_cursos_insc > 0) {
				$c1_nsp    = round(($c1_nsp/$cant_cursos_insc)*100,0);
				$c1_na     = round(($c1_na/$cant_cursos_insc)*100,0);
				$c1_reprob = round(($c1_reprob/$cant_cursos_insc)*100,0);
				$c1_aprob  = round(($c1_aprob/$cant_cursos_insc)*100,0);
				$c1        = barra_progreso($c1_nsp,$c1_na,$c1_reprob,$c1_aprob);
				
				$s1_nsp    = round(($s1_nsp/$cant_cursos_insc)*100,0);
				$s1_na     = round(($s1_na/$cant_cursos_insc)*100,0);
				$s1_reprob = round(($s1_reprob/$cant_cursos_insc)*100,0);
				$s1_aprob  = round(($s1_aprob/$cant_cursos_insc)*100,0);
				$s1        = barra_progreso($s1_nsp,$s1_na,$s1_reprob,$s1_aprob);
				
				$s2_nsp    = round(($s2_nsp/$cant_cursos_insc)*100,0);
				$s2_na     = round(($s2_na/$cant_cursos_insc)*100,0);
				$s2_reprob = round(($s2_reprob/$cant_cursos_insc)*100,0);
				$s2_aprob  = round(($s2_aprob/$cant_cursos_insc)*100,0);
				$s2        = barra_progreso($s2_nsp,$s2_na,$s2_reprob,$s2_aprob);
			}
            $s1_sem2 = $c1_sem2 = $s2_sem2 = "N/A";
            if ($cant_cursos_insc_2dosem > 0) {
				$c1_nsp_sem2    = round(($c1_nsp_2sem/$cant_cursos_insc_2dosem)*100,0);
				$c1_na_sem2     = round(($c1_na_2sem/$cant_cursos_insc_2dosem)*100,0);
				$c1_reprob_sem2 = round(($c1_reprob_2sem/$cant_cursos_insc_2dosem)*100,0);
				$c1_aprob_sem2  = round(($c1_aprob_2sem/$cant_cursos_insc_2dosem)*100,0);
				$c1_sem2        = barra_progreso($c1_nsp_2sem,$c1_na_2sem,$c1_reprob_2sem,$c1_aprob_2sem);
				
				$s1_nsp_sem2    = round(($s1_nsp_2sem/$cant_cursos_insc_2dosem)*100,0);
				$s1_na_sem2     = round(($s1_na_2sem/$cant_cursos_insc_2dosem)*100,0);
				$s1_reprob_sem2 = round(($s1_reprob_2sem/$cant_cursos_insc_2dosem)*100,0);
				$s1_aprob_sem2  = round(($s1_aprob_2sem/$cant_cursos_insc_2dosem)*100,0);
				$s1_sem2        = barra_progreso($s1_nsp_2sem,$s1_na_2sem,$s1_reprob_2sem,$s1_aprob_2sem);
				
				$s2_nsp_sem2    = round(($s2_nsp_2sem/$cant_cursos_insc)*100,0);
				$s2_na_sem2     = round(($s2_na_2sem/$cant_cursos_insc)*100,0);
				$s2_reprob_sem2 = round(($s2_reprob_2sem/$cant_cursos_insc)*100,0);
				$s2_aprob_sem2  = round(($s2_aprob_2sem/$cant_cursos_insc)*100,0);
				$s2_sem2        = barra_progreso($s2_nsp_2sem,$s2_na_2sem,$s2_reprob_2sem,$s2_aprob_2sem);
			}
			
			/*
			$aAF5 = $aACRA = array();
			$j = $i = null;
			$j = array_search($rut,array_column($prueba_af5,'rut_alumno'));
			$i = array_search($rut,array_column($prueba_acra,'rut_alumno'));
			*/
			
			$rinde_test = "No";
			for($j=0;$j<count($prueba_af5);$j++) { if ($prueba_af5[$j]['rut_alumno'] == $rut) { $rinde_test = "Si"; break; } }
			for($i=0;$i<count($prueba_acra);$i++) { if ($prueba_acra[$i]['rut_alumno'] == $rut) { $rinde_test = "Si"; break; } }
			
			switch ($riesgo_desercion) {
				case "Alto":
					$riesgo_desercion = "<div style='background: #FF0000; color: #FFFFFF; padding: 2px; border-radius: 3px 3px 3px 3px;'>$riesgo_desercion</div>";
					break;
				case "Medio Alto":
					$riesgo_desercion = "<div style='background: #FFA500; color: #FFFFFF; padding: 2px; border-radius: 3px 3px 3px 3px;'>$riesgo_desercion</div>";
					break;
				case "Medio":
					$riesgo_desercion = "<div style='background: #FFFF00; color: #4D4D4D; padding: 2px; border-radius: 3px 3px 3px 3px;'>$riesgo_desercion</div>";
					break;
				case "Bajo":
					$riesgo_desercion = "<div style='background: #008000; color: #FFFFFF; padding: 2px; border-radius: 3px 3px 3px 3px;'>$riesgo_desercion</div>";
					break;
			}
				
                        
			$HTML_alumnos .= "  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n"
			               . "    <td class='textoTabla' align='center'><img src='doctos_digitalizados_ver.php?id=$id_foto' width='25'></td>\n"
			               . "    <td class='textoTabla'>$rut<br><span style='color: #e5e5e5'>$id</span></td>\n"
			               . "    <td class='textoTabla'><a class='enlitem' href='$enl'>$apellidos<br>$nombres</a></td>\n"
			               . "    <td class='textoTabla' align='center'>$carrera<br><small>$cohorte $mes_cohorte</small></td>\n"
			               . "    <td class='textoTabla' align='center'>$estado<small><br><br>r: $riesgo_morosidad%</small></td>\n"
			               . "    <td class='textoTabla' align='center'>$matriculado<small><br><br><b>$cant_cursos_insc asig.</b></small></td>\n"
			               . "    <td class='textoTabla' align='center'>$nivelacion<small><br><br>r: $riesgo_niv%</small></td>\n"
			               . "    <td class='textoTabla' align='center'>$rinde_test<small><br><br>r: $riesgo_pruebas_psico%</small></td>\n"
			               //. "    <td class='textoTabla' align='center'><small>{$prueba_af5[$j]['nivel_Académico/Laboral']}</small></td>\n"
			               //. "    <td class='textoTabla' align='center'><small>{$prueba_af5[$j]['nivel_Social']}</small></td>\n"
			               //. "    <td class='textoTabla' align='center'><small>{$prueba_af5[$j]['nivel_Emocional']}</small></td>\n"
			               //. "    <td class='textoTabla' align='center'><small>{$prueba_af5[$j]['nivel_Familiar']}</small></td>\n"
			               //. "    <td class='textoTabla' align='center'><small>{$prueba_acra[$i]['nivel_Adquisición']}</small></td>\n"
			               //. "    <td class='textoTabla' align='center'><small>{$prueba_acra[$i]['nivel_Codificación']}</small></td>\n"
			               //. "    <td class='textoTabla' align='center'><small>{$prueba_acra[$i]['nivel_Recuperación']}</small></td>\n"
			               //. "    <td class='textoTabla' align='center'><small>{$prueba_acra[$i]['nivel_Procesamiento']}</small></td>\n"
                           . "    <td class='textoTabla' align='center' style='vertical-align: middle'>$c1<small><br>r: $riesgo_c1%</small></td>\n"
			               . "    <td class='textoTabla' align='center' style='vertical-align: middle'>$s1<small><br>r: $riesgo_s1%</small></td>\n"
			               . "    <td class='textoTabla' align='center' style='vertical-align: middle'>$s2<small><br>r: $riesgo_s2%</small></td>\n"
                           . "    <td class='textoTabla' align='center' style='vertical-align: middle'>$c1_sem2<small><br>r: $riesgo_c1_sem2%</small></td>\n"
			               . "    <td class='textoTabla' align='center' style='vertical-align: middle'>$s1_sem2<small><br>r: $riesgo_s1_sem2%</small></td>\n"
			               . "    <td class='textoTabla' align='center' style='vertical-align: middle'>$s2_sem2<small><br>r: $riesgo_s2_sem2%</small></td>\n"
			               . "    <td class='textoTabla' align='center' style='vertical-align: middle'><b>$riesgo_desercion<small><br>rtd: $tasa_riesgo%</small></b></td>\n"
			               . "  </tr>\n";
		}
	} else {
		$HTML_alumnos = "  <tr>"
		              . "    <td class='textoTabla' colspan='8'>"
		              . "      No hay registros para los criterios de búsqueda/selección"
		              . "    </td>\n"
		              . "  </tr>";
	}
	echo($HTML_alumnos);
?>
</table><br>
  </form>

<!-- Fin: <?php echo($modulo); ?> -->

<?php

function barra_progreso($porc_nsp,$porc_na,$porc_reprob,$porc_aprob) {
	global $color_nsp, $color_na, $color_reprob, $color_aprob;
	
	$td_nsp = $td_na = $td_reprob = $td_aprob = "";
	if ($porc_nsp > 0)    { $px_nsp    = round($porc_nsp/2,0);    $td_nsp    = "<td width='$px_nsp'    bgcolor='$color_nsp'><a title='NSP: $porc_nsp%'>&nbsp;</a></td>"; }
	if ($porc_na > 0)     { $px_na     = round($porc_na/2,0);     $td_na     = "<td width='$px_na'     bgcolor='$color_na'><a title='N/A: $porc_na%'>&nbsp;</a></td>"; }
	if ($porc_aprob > 0)  { $px_aprob  = round($porc_aprob/2,0);  $td_aprob  = "<td width='$px_aprob'  bgcolor='$color_aprob'><a title='Aprueba: $porc_aprob%'>&nbsp;</a></td>"; }
	if ($porc_reprob > 0) { $px_reprob = round($porc_reprob/2,0); $td_reprob = "<td width='$px_reprob' bgcolor='$color_reprob'><a title='Reprueba: $porc_reprob%'>&nbsp;</a></td>"; }
	
	$barra_progreso = "<table width='50' cellspacing='0'>"
				    . "  <tr>"
				    . "    $td_aprob"
				    . "    $td_reprob"
				    . "    $td_nsp"
				    . "    $td_na"
				    . "  </tr>"
				    . "</table>";
	return $barra_progreso;
}
?>
