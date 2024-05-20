<?php


if ($ANO_MATRICULA > $ANO && date("Y") > $ANO) {
	$ANO_MATRICULA = 2024;
	$SEMESTRE_MATRICULA = 1;
	$ANO = $ANO_MATRICULA;
	$SEMESTRE = $SEMESTRE_MATRICULA;
}



$ids_carreras = $_SESSION['ids_carreras'];

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) { $cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if ($reg_inicio=="") { $reg_inicio = 0; }

$texto_buscar       = trim($_REQUEST['texto_buscar']);
$buscar             = $_REQUEST['buscar'];
$id_carrera         = $_REQUEST['id_carrera'];
$jornada            = $_REQUEST['jornada'];
$semestre_cohorte   = $_REQUEST['semestre_cohorte'];
$mes_cohorte        = $_REQUEST['mes_cohorte'];
$cohorte            = $_REQUEST['cohorte'];
$estado             = $_REQUEST['estado'];
$id_benef_fiscal    = $_REQUEST['id_benef_fiscal'];
$moroso_financiero  = $_REQUEST['moroso_financiero'];
$admision           = $_REQUEST['admision'];
$regimen            = $_REQUEST['regimen'];
$aprob_ant          = $_REQUEST['aprob_ant'];
$tasa_conv          = $_REQUEST['tasa_conv'];
$matriculado        = $_REQUEST['matriculado'];
$nacionalidad       = $_REQUEST['nacionalidad'];
$pruebas_psico      = $_REQUEST['pruebas_psico'];
//$sies_info         = $_REQUEST['sies_info'];
$ano_sies           = $_REQUEST['ano_sies'];
$fec_ini_asist      = $_REQUEST['fec_ini_asist'];
$fec_fin_asist      = $_REQUEST['fec_fin_asist'];
$mostrar_asistencia = $_REQUEST['mostrar_asistencia'];
$mostrar_torniquete = $_REQUEST['mostrar_torniquete'];
$excep_finan        = $_REQUEST['excep_finan'];
$remat_atencion     = $_REQUEST['remat_atencion'];
$post_becaumc       = $_REQUEST['post_becaumc'];
$incluye_reinc      = $_REQUEST['incluye_reinc'];
$ano_fuas           = $_REQUEST['ano_fuas'];
$ano_renov_cae      = $_REQUEST['ano_renov_cae'];
$ano_presel_pub     = $_REQUEST['ano_presel_pub'];
$ano_presel_mineduc = $_REQUEST['ano_presel_mineduc'];
$fec_ini_estado     = $_REQUEST['fec_ini_estado'];
$fec_fin_estado     = $_REQUEST['fec_fin_estado'];

$ver_datos_contacto = $_REQUEST['ver_datos_contacto'];

$fec_ini_estado_min = "$ANO-01-01";
$fec_fin_estado_max = date("Y-m-d");

if (empty($_REQUEST['matriculado'])) { $matriculado = "t"; }
if (empty($_REQUEST['cohorte'])) { $cohorte = 0; }
if (empty($_REQUEST['semestre_cohorte'])) { $semestre_cohorte = 0; }
if (empty($_REQUEST['mes_cohorte'])) { $mes_cohorte = 0; }
if (empty($_REQUEST['estado'])) { $estado = -1; }
if (empty($_REQUEST['moroso_financiero'])) { $moroso_financiero = -1; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }
if (!empty($ids_carreras) && empty($_REQUEST['regimen'])) { $regimen = "t"; }
if (empty($_REQUEST['aprob_ant'])) { $aprob_ant = 't'; }
if (empty($cond_base)) { $cond_base = "true"; }
if ($regimen == "POST-GD") { $matriculado = "a"; }
//$ano_sies = $ANO - 1;
if ($_REQUEST['fec_ini_asist'] == "") { $fec_ini_asist = ($SEMESTRE == 1) ? date("Y-m-d",$Fec_Ini_Sem1) : date("Y-m-d",$Fec_Ini_Sem2); }
if ($_REQUEST['fec_fin_asist'] == "") { $fec_fin_asist = ($SEMESTRE == 1) ? date("Y-m-d",$Fec_Fin_Sem1) : date("Y-m-d",$Fec_Fin_Sem2); }

$cohorte_1erano = $ANO_MATRICULA -1;
$cohorte_2doano = $ANO_MATRICULA -2;

//$ano_sies = $ANO;
$sem_ant = $ano_ant = 0;
if ($SEMESTRE == 2)     { $sem_ant = 1; $ano_ant = $ANO; }
elseif ($SEMESTRE <= 1) { $sem_ant = 2; $ano_ant = $ANO - 1; }

$SQL_cursos_modulares = "SELECT count(id_curso) FROM cargas_academicas AS ca LEFT JOIN cursos AS c ON c.id=ca.id_curso WHERE id_alumno=a.id AND c.seccion=9 AND ca.id_estado <> 6";

$SQL_cursos_ant     = "SELECT id FROM cursos WHERE semestre=$sem_ant AND ano=$ano_ant";
$SQL_cursos_aprob   = "SELECT count(id_curso) FROM cargas_academicas WHERE id_alumno=a.id AND id_estado=1 AND id_curso IN ($SQL_cursos_ant)";
$SQL_cursos_insc    = "SELECT count(id_curso) FROM cargas_academicas WHERE id_alumno=a.id AND id_curso IN ($SQL_cursos_ant)";
$SQL_tasa_aprob_ant = "CASE WHEN ($SQL_cursos_insc) > 0 THEN (($SQL_cursos_aprob)::real/($SQL_cursos_insc)::real*100)::numeric(4,1) ELSE 0 END";

$SQL_cursos_act       = "SELECT id FROM cursos WHERE ano=$ANO AND semestre=$SEMESTRE";
$SQL_cursos_aprob_act = "SELECT count(id_curso) FROM cargas_academicas WHERE id_alumno=a.id AND id_estado=1 AND id_curso IN ($SQL_cursos_act)";
$SQL_cursos_insc_act  = "SELECT count(id_curso) FROM cargas_academicas WHERE id_alumno=a.id AND id_curso IN ($SQL_cursos_act)";
$SQL_tasa_aprob_act   = "CASE WHEN ($SQL_cursos_insc_act) > 0 THEN (($SQL_cursos_aprob_act)::real/($SQL_cursos_insc_act)::real*100)::numeric(4,1) ELSE 0 END";

$SQL_cursos_anoant     = "SELECT id FROM cursos WHERE ano=$ANO-1";
$SQL_prom_anoant       = "SELECT id_alumno,round(avg(nota_final),1) as nota_final,count(id) AS cant_asig FROM cargas_academicas WHERE id_curso IN ($SQL_cursos_anoant) AND id_estado IN (1,2) GROUP BY id_alumno";
$SQL_prom_aprob_anoant = "SELECT id_alumno,round(avg(nota_final),1) as nota_final,count(id) AS cant_asig FROM cargas_academicas WHERE id_curso IN ($SQL_cursos_anoant) AND id_estado=1 GROUP BY id_alumno";

$SQL_cursos_anoact       = "SELECT id FROM cursos WHERE ano=$ANO";
$SQL_cursos_aprob_anoact = "SELECT count(id_alumno) AS cant_aprob FROM cargas_academicas WHERE id_curso IN ($SQL_cursos_anoact) AND id_estado=1 AND id_alumno=a.id";
$SQL_cursos_insc_anoact  = "SELECT count(id_alumno) AS cant_insc FROM cargas_academicas WHERE id_curso IN ($SQL_cursos_anoact) AND id_alumno=a.id";


$SQL_conv = "SELECT count(id) FROM cargas_academicas WHERE id_alumno=a.id AND convalidado AND id_pa IN (SELECT id_prog_asig FROM detalle_mallas WHERE id_malla=a.malla_actual)";

$SQL_pruebas_psico = "SELECT count(id) FROM sinergia.respuestas WHERE ano=$ANO AND semestre=$SEMESTRE AND rut_alumno=a.rut";

$SQL_solic_excep_finan = "SELECT estado 
                          FROM gestion.solicitudes AS sol 
                          LEFT JOIN gestion.solic_tipos AS gst ON gst.id=sol.id_tipo 
                          WHERE id_alumno=a.id AND gst.alias='solic_excep_finan' AND sol.fecha::date>='2021-10-01'
                          ORDER BY sol.fecha DESC
                          LIMIT 1";

$SQL_remat_atencion = "SELECT CASE WHEN fecha_compromiso IS NOT NULL   THEN 'Comprometido(a)'
                                   WHEN id_motivo_no_remat IS NOT NULL THEN 'Desertor(a)'
                                   WHEN obtiene_respuesta='f'          THEN 'Sin respuesta'
                              END AS atencion_remat
                       FROM gestion.atenciones_remat AS gar
                       WHERE gar.id_alumno=a.id AND ((fecha_compromiso IS NOT NULL AND fecha_compromiso>=now()::date) OR id_motivo_no_remat IS NOT NULL OR NOT obtiene_respuesta)
                       ORDER BY gar.fecha DESC
                       LIMIT 1";
                       
$SQL_post_becaumc = "SELECT estado
                     FROM dae.fuas
                     WHERE id_alumno=a.id and ano=$ANO_MATRICULA
                     ORDER BY fecha_creacion DESC
                     LIMIT 1";

$condicion = "WHERE $cond_base  ";

if ($buscar == 'Buscar' && $texto_buscar <> "") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar = explode(" ",$texto_buscar_regexp);
	$condicion .= " AND ";
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= "(lower(a.nombres||' '||a.apellidos) ~* '$cadena_buscada' OR "
		            . " a.rut ~* '$cadena_buscada' OR "
		            . " lower(a.email) ~* '$cadena_buscada' OR "
		            . " text(a.id) ~* '$cadena_buscada' "
		            . ") AND ";
	}
	$condicion=substr($condicion,0,strlen($condicion)-4);
	$cohorte = $semestre_cohorte = $mes_cohorte = $estado = $id_carrera = $admision = $matriculado = $regimen = $ano_sies = null;
} else {

  if ($cohorte > 0 && $semestre_cohorte > 0 && $mes_cohorte > 0) { 
		$condicion .= "AND ((a.cohorte = $cohorte AND a.semestre_cohorte = $semestre_cohorte AND a.mes_cohorte = $mes_cohorte) ";
    if ($incluye_reinc == "si") {
      $condicion .= "     OR (a.cohorte_reinc = $cohorte AND a.semestre_cohorte_reinc = $semestre_cohorte AND a.mes_cohorte_reinc = $mes_cohorte)) ";
    } else {
      $condicion .= ") ";
    }
  } elseif ($semestre_cohorte > 0) {
		$condicion .= "AND (a.semestre_cohorte = $semestre_cohorte ";
    if ($incluye_reinc == "si") {
      $condicion .= "OR a.semestre_cohorte_reinc = '$semestre_cohorte') ";
    } else {
      $condicion .= ") ";
    }
	}

	if ($cohorte > 0) {
		$condicion .= "AND (a.cohorte = '$cohorte' ";
    if ($incluye_reinc == "si") {
      $condicion .= "OR a.cohorte_reinc = '$cohorte') ";
    } else {
      $condicion .= ") ";
    }
  }

/*
	if ($semestre_cohorte > 0) {
		$condicion .= "AND (a.semestre_cohorte = $semestre_cohorte OR a.semestre_cohorte_reinc = '$semestre_cohorte') ";
	}

	if ($mes_cohorte > 0) {
		$condicion .= "AND (a.mes_cohorte = $mes_cohorte OR a.mes_cohorte_reinc = '$mes_cohorte') ";
	}
	*/

	if ($estado <> "-1") { 
    $condicion .= "AND (a.estado = '$estado') ";
    if ($estado > "1" && $fec_ini_estado <> "" && $fec_fin_estado <> "") {
			$condicion .= " AND (a.estado_fecha between '$fec_ini_estado'::date AND '$fec_fin_estado'::date) ";

		}
  }

	if ($moroso_financiero <> "-1") { $condicion .= "AND (a.moroso_financiero = '$moroso_financiero') "; }
	
	if ($id_carrera <> "") { $condicion .= "AND (a.carrera_actual = $id_carrera) "; }

	if ($jornada <> "") { $condicion .= "AND (a.jornada = '$jornada') "; }

	if ($admision <> "") { $condicion .= "AND (a.admision IN ('".str_replace(",","','",$admision)."')) ";	}

	if ($regimen <> "" && $regimen <> "t") { $condicion .= "AND (c.regimen = '$regimen') "; }

  if ($nacionalidad == "-2") { $condicion .= "AND a.nacionalidad <> 'CL'"; }
  elseif ($nacionalidad <> "") { $condicion .= "AND a.nacionalidad = '$nacionalidad'"; }

	if ($pruebas_psico == "t") { $condicion .= "AND (($SQL_pruebas_psico) = 2) "; }
	if ($pruebas_psico == "f") { $condicion .= "AND (($SQL_pruebas_psico) < 2) "; }

  if ($id_benef_fiscal == "t") { $condicion .= " AND benef_fiscal IS NOT NULL "; }
  if ($id_benef_fiscal == "f") { $condicion .= " AND benef_fiscal IS NULL "; }

  if ($tasa_conv <> "") {
    switch ($tasa_conv) {
      case "0":
        $condicion .= " AND ($SQL_conv)=0 ";
        break;
      case "1-60":
        $condicion .= " AND (CASE WHEN ($SQL_conv) > 0 THEN round(($SQL_conv)::numeric*100/(coalesce(m.cant_asig_oblig,0)+coalesce(m.cant_asig_elect,0)+coalesce(m.cant_asig_efp,0)),0) ELSE 0 END) BETWEEN 1 AND 60";
        break;
      case "61":
        $condicion .= " AND (CASE WHEN ($SQL_conv) > 0 THEN round(($SQL_conv)::numeric*100/(coalesce(m.cant_asig_oblig,0)+coalesce(m.cant_asig_elect,0)+coalesce(m.cant_asig_efp,0)),0) ELSE 0 END) > 60";
        break;
    }
  }

  if ($ano_fuas > 0) { 
    $SQL_left_join_fuasm = "LEFT JOIN dae.fuas_mineduc AS fuasm ON (fuasm.ano=$ano_fuas AND fuasm.rut=split_part(trim(a.rut),'-',1)::int8)"; 
    $condicion .= " AND fuasm.rut IS NOT NULL ";
  }  
  
  if ($ano_renov_cae > 0) { 
    $SQL_left_join_renov_cae = "LEFT JOIN dae.renovantes_cae AS renov_cae ON (renov_cae.ano=$ano_renov_cae AND renov_cae.rut=split_part(trim(a.rut),'-',1)::int8)"; 
    $condicion .= " AND renov_cae.rut IS NOT NULL ";
  }

  if ($ano_presel_pub > 0) { 
    $SQL_left_join_presel_pub = "LEFT JOIN dae.cae_preseleccionados_publicacion AS presel_pub ON (presel_pub.ano=$ano_presel_pub AND presel_pub.rut=split_part(trim(a.rut),'-',1)::int8)"; 
    $condicion .= " AND presel_pub.rut IS NOT NULL ";
  }
  
  if ($ano_presel_mineduc > 0) { 
    $SQL_left_join_presel_mineduc = "LEFT JOIN dae.vista_presel_mineduc AS presel_mineduc ON (presel_mineduc.ano=$ano_presel_mineduc AND presel_mineduc.rut=split_part(trim(a.rut),'-',1)::int8)"; 
    $condicion .= " AND presel_mineduc.rut IS NOT NULL AND presel_mineduc.cant_becas > 0 ";
  }

/*	
	if ($matriculado <> "a") {
		$SQL_left_join_matriculas = "LEFT JOIN matriculas AS mat ON (mat.id_alumno=a.id ";
		switch ($matriculado) {
			case "t":
				$condicion .= "AND mat.id_alumno IS NOT NULL"; 
				$SQL_left_join_matriculas .= " AND mat.semestre=$SEMESTRE AND mat.ano=$ANO)";
				break;
			case "t1":
				$condicion .= "AND mat.id_alumno IS NOT NULL"; 
				$SQL_left_join_matriculas .= " AND mat.ano=$ANO)";
				break;
			case "f":
				$condicion .= "AND mat.id_alumno IS NULL"; 
				$SQL_left_join_matriculas .= " AND mat.semestre=$SEMESTRE AND mat.ano=$ANO)";
		}
	}
*/

	if ($matriculado <> "a") {
		$SQL_mat = "SELECT id_alumno FROM matriculas WHERE ";
		switch ($matriculado) {
			case "t":
				$SQL_mat .= "ano=$ANO AND semestre=$SEMESTRE";
				$condicion .= "AND (a.id IN ($SQL_mat)) ";
				break;
			case "t1":
				$SQL_mat .= "ano=$ANO";
				$condicion .= "AND (a.id IN ($SQL_mat)) ";
				break;
			case "f":
				$SQL_mat .= "ano=$ANO AND semestre=$SEMESTRE";
				$condicion .= "AND (a.id NOT IN ($SQL_mat)) ";
		}
	}


	//if ($sies_info == "t") { $condicion .= " AND (sies.rut IS NOT NULL) "; }
	//if ($sies_info == "f") { $condicion .= " AND (sies.rut IS NULL) "; }
  
	if ($ano_sies > 0) {
		$SQL_left_join_alumnos_sies = "LEFT JOIN alumnos_sies AS al_sies ON (al_sies.rut=a.rut AND al_sies.regimen='$regimen' AND al_sies.ano=$ano_sies)"; 
		$condicion .= " AND al_sies.rut IS NOT NULL ";

/*
    $SQL_sies_ano = "SELECT rut FROM alumnos_sies WHERE ano=$ano_sies";
    if ($regimen <> "" && $regimen <> "t") { $SQL_sies_ano .= " AND regimen='$regimen' "; }    
    $condicion .= " AND a.rut IN ($SQL_sies_ano) "; 
*/
	}

	switch ($aprob_ant) {
		case 1:
			$condicion .= " AND (($SQL_tasa_aprob_ant) = 0) ";
			break;
		case 10:
			$condicion .= " AND (($SQL_tasa_aprob_ant) = 0 AND ($SQL_cursos_insc) > 0) ";
			break;
		case 11:
			$condicion .= " AND (($SQL_tasa_aprob_ant) = 0 AND ($SQL_cursos_insc) = 0) ";
			break;
		case 2:
			$condicion .= " AND (($SQL_tasa_aprob_ant) BETWEEN 1 AND 39.9) ";
			break;
		case 3:
			$condicion .= " AND (($SQL_tasa_aprob_ant) BETWEEN 40 AND 100) ";
			break;
		case 30:
			$condicion .= " AND (($SQL_tasa_aprob_ant) BETWEEN 1 AND 100) ";
			break;
	}

  if ($excep_finan <> "") {
    switch ($excep_finan) {
      case "con":
        $condicion .= " AND (($SQL_solic_excep_finan) IS NOT NULL) ";
        break;
      case "sin":
        $condicion .= " AND (($SQL_solic_excep_finan) IS NULL) ";
        break;
      default:
        $condicion .= " AND (($SQL_solic_excep_finan) = '$excep_finan') ";
        break;
    }
  }

  if ($remat_atencion <> "") {
    switch ($remat_atencion) {
      case "con":
        $condicion .= " AND (($SQL_remat_atencion) IS NOT NULL) ";
        break;
      case "sin":
        $condicion .= " AND (($SQL_remat_atencion) IS NULL) ";
        break;
      default:
        $condicion .= " AND (($SQL_remat_atencion) = '$remat_atencion') ";
        break;
    }
  }

  if ($post_becaumc <> "") {
    switch ($post_becaumc) {
      case "con":
        $condicion .= " AND (($SQL_post_becaumc) IS NOT NULL) ";
        break;
      case "sin":
        $condicion .= " AND (($SQL_post_becaumc) IS NULL) ";
        break;
      default:
        $condicion .= " AND (($SQL_post_becaumc) = '$post_becaumc') ";
        break;
    }
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

$SQL_tr_act = "SELECT 1 FROM inscripciones_cursos WHERE id_alumno=a.id LIMIT 1";

$SQL_convalidaciones = "SELECT date_part('year',now())-conv.ano AS antiguedad 
                        FROM cargas_academicas AS ca 
                        LEFT JOIN vista_convalidaciones AS conv ON conv.id=ca.id_convalida
                        WHERE ca.id_alumno=a.id AND ca.convalidado";

$SQL_mat = "SELECT 1 FROM matriculas WHERE id_alumno=a.id AND ano=$ANO AND semestre=$SEMESTRE LIMIT 1";
if ($matriculado == "t1") { $SQL_mat = "SELECT 1 FROM matriculas WHERE id_alumno=a.id AND ano=$ANO LIMIT 1"; }

/*
$SQL_benef_fiscal = "SELECT DISTINCT ON (rut,be.nombre) be.nombre AS benef_fiscal 
                     FROM finanzas.contratos AS c 
                     LEFT JOIN vista_contratos_rut vcr USING (id)
                     LEFT JOIN finanzas.becas_externas be ON be.id=c.id_beca_externa 
                     WHERE ano=$ANO AND vcr.rut=a.rut AND id_beca_externa IS NOT NULL LIMIT 1";
*/

$SQL_left_join_benef_fiscal = "SELECT DISTINCT ON (rut,be.nombre) rut,be.nombre AS benef_fiscal 
                               FROM finanzas.contratos AS c 
                               LEFT JOIN vista_contratos_rut vcr USING (id)
                               LEFT JOIN finanzas.becas_externas be ON be.id=c.id_beca_externa 
                               WHERE ano=$ANO AND id_beca_externa IS NOT NULL";
$SQL_left_join_benef_fiscal = "LEFT JOIN ($SQL_left_join_benef_fiscal) AS be ON be.rut=a.rut";

$SQL_alumnos_asist = "";
if ($mostrar_asistencia == "si") {
	$SQL_asist_total    = "SELECT count(id_alumno) FROM vista_alumnos_asistencia WHERE id_alumno=a.id  AND fecha BETWEEN '$fec_ini_asist'::date AND '$fec_fin_asist'::date";
	$SQL_asist_presente = "$SQL_asist_total AND presente";
	$SQL_asist_ausente  = "$SQL_asist_total AND NOT presente";
	$SQL_alumnos_asist  = ",($SQL_asist_total) AS asist_total,($SQL_asist_presente) AS asist_presente";
	$SQL_alumnos_asist2 = ",CASE WHEN ($SQL_asist_total)>0 THEN round(100*($SQL_asist_presente)::real/($SQL_asist_total)) ELSE 0 END AS tasa_presente";
}

$SQL_torniquete = $SQL_al_torniquete = $SQL_left_join_torniquete = "";
if ($mostrar_torniquete == "si") {
	$SQL_al_torniquete = "SELECT rut FROM vista_torniquete_rut WHERE fecha_reg::date BETWEEN '$fec_ini_asist'::date AND '$fec_fin_asist'::date GROUP BY rut,fecha_reg::date";
	$SQL_al_torniquete = "SELECT rut,count(rut) AS dias FROM ($SQL_al_torniquete) AS foo GROUP BY rut";
	$SQL_left_join_torniquete = "LEFT JOIN ($SQL_al_torniquete) AS vtr ON vtr.rut=split_part(a.rut,'-',1)";
	$SQL_torniquete = ",coalesce(vtr.dias,0) AS cant_dias_accede_edif";
}

$SQL_alumnos = "SELECT a.id,trim(a.rut) AS rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre,a.genero,a.fec_nac,c.alias||'-'||a.jornada AS carrera,c.regimen,
                       a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.mes_cohorte,
                       a.semestre_cohorte_reinc||'-'||a.cohorte_reinc AS cohorte_reinc,a.mes_cohorte_reinc,
                       ae.nombre AS estado,to_char(a.estado_fecha,'DD-MM-YYYY') AS estado_fecha,
                       CASE WHEN ($SQL_conv) > 0 THEN round(($SQL_conv)::numeric*100/(coalesce(m.cant_asig_oblig,0)+coalesce(m.cant_asig_elect,0)+coalesce(m.cant_asig_efp,0)),0) ELSE 0 END AS porc_conv,
                       ($SQL_solic_excep_finan) AS excep_finan,
                       ($SQL_remat_atencion) AS remat_atencion,
                       ($SQL_post_becaumc) AS post_becaumc,
                       be.benef_fiscal,
                       CASE WHEN ($SQL_tr_act) > 0 THEN 'Si' ELSE 'No' END AS tr_act,
                       CASE WHEN ($SQL_mat)=1 THEN 'Si' ELSE 'No' END AS matriculado,moroso_financiero,
                       CASE WHEN ($SQL_cursos_insc)>0 THEN (($SQL_cursos_aprob)::real/($SQL_cursos_insc)::real*100)::numeric(4,1) ELSE 0 END AS tasa_aprobacion_ant $SQL_alumnos_asist $SQL_torniquete
                FROM alumnos AS a
                LEFT JOIN pap ON pap.id=a.id_pap
                LEFT JOIN carreras   AS c ON c.id=a.carrera_actual
                LEFT JOIN al_estados AS ae ON ae.id=a.estado
                LEFT JOIN mallas     AS m ON m.id=a.malla_actual
                $SQL_left_join_fuasm
                $SQL_left_join_renov_cae
                $SQL_left_join_presel_pub
                $SQL_left_join_presel_mineduc
                $SQL_left_join_alumnos_sies
                $SQL_left_join_matriculas
                $SQL_left_join_benef_fiscal
                $SQL_left_join_torniquete
                $condicion
                ORDER BY a.apellidos,a.nombres
                $limite_reg OFFSET $reg_inicio";
//var_dump($SQL_alumnos);
$alumnos = consulta_sql($SQL_alumnos);
echo("<!-- $SQL_alumnos -->");
/*
$SQL_prom_notas = "SELECT avg(nf::numeric(3,1))::numeric(3,1) 
                   FROM vista_alumnos_cursos AS vac
                   LEFT JOIN cursos AS c ON c.id=vac.id_curso
                   LEFT JOIN detalle_mallas AS dm ON dm.id_prog_asig=c.id_prog_asig
                   WHERE vac.id_alumno=a.id AND vac.id_estado=1 AND dm.id_malla=a.malla_actual";
($SQL_prom_notas) AS prom_notas,
*/

$SQL_alsies = "SELECT 1 FROM alumnos_sies WHERE ano=$ANO_MATRICULA AND regimen='$regimen' AND rut=a.rut";
$SQL_fecha_ult_mat = "SELECT to_char(max(fecha),'DD/MM/YYYY') FROM matriculas WHERE id_alumno=a.id";
$SQL_fecha_ult_email = "SELECT max(email_fecha::date) FROM alumnos_datos_contacto WHERE id_alumno=a.id";
$SQL_asist = "SELECT id_alumno,sum(presente) as presente,sum(tot_asist) as tot_asist 
              FROM (SELECT id_alumno,(select count(id_ca) from ca_asistencia where id_ca=ca.id and presente) as presente,
                                     (select count(id_ca) from ca_asistencia where id_ca=ca.id) as tot_asist 
                    FROM cargas_academicas ca 
                    WHERE id_estado IS NULL AND id_curso IN ($SQL_cursos_act)) as foo 
              GROUP BY id_alumno";

$SQL_monto_moroso = "SELECT sum(monto_moroso) AS monto_moroso FROM vista_contratos AS vc LEFT JOIN vista_contratos_rut AS vcr ON vcr.id=vc.id WHERE vcr.rut=a.rut";
$SQL_monto_saldot = "SELECT sum(monto_saldot) AS monto_saldot FROM vista_contratos AS vc LEFT JOIN vista_contratos_rut AS vcr ON vcr.id=vc.id WHERE vcr.rut=a.rut";

$SQL_arancel_real = "SELECT monto_arancel*CASE trim(tipo) WHEN 'Semestral' THEN 2 ELSE 1 END AS arancel_real FROM finanzas.contratos WHERE ano=$ANO_MATRICULA AND (id_alumno=a.id OR id_pap=a.id_pap) AND estado IN ('E','S','R','A') LIMIT 1";

$SQL_nivel_estudios = "SELECT ceil(max(nivel)::float/2) AS nivel
                       FROM vista_alumnos_cursos AS vac 
                       LEFT JOIN detalle_mallas dm ON (dm.id_malla=a.malla_actual AND dm.id_prog_asig=vac.id_prog_asig) 
                       WHERE vac.id_alumno=a.id";

$SQL_alumnos = "SELECT a.id,trim(a.rut) AS rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre,a.genero,a.fec_nac,c.alias||'-'||a.jornada AS carrera,c.regimen,
                       a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.mes_cohorte,a.email,($SQL_fecha_ult_email) AS email_fecha,
                       a.semestre_cohorte_reinc||'-'||a.cohorte_reinc AS cohorte_reinc,a.mes_cohorte_reinc,
                       a.nombre_usuario||'@'||dominio as email_institucional,a.nombre_usuario||'@'||dominio_gsuite as email_gsuite,
                       a.tel_movil,a.telefono,adm.nombre as admision,
                       ($SQL_solic_excep_finan) AS excep_finan,
                       ($SQL_remat_atencion) AS remat_atencion,
                       ($SQL_post_becaumc) AS post_becaumc,
                       be.benef_fiscal,
                       coalesce(a.rbd_colegio,pap.rbd_colegio) AS rbd_colegio,
                       coalesce(a.promedio_col,pap.promedio_col) AS nem,
                       coalesce(a.puntaje_psu,pap.puntaje_psu) AS puntaje_psu,
                       ae.nombre AS estado,to_char(a.estado_fecha,'DD-MM-YYYY') AS estado_fecha,u.nombre_usuario AS estado_operador,
                       paa.nota_final AS prom_ano_ant,
                       CASE WHEN paa.cant_asig > 0 THEN round(paaa.cant_asig*100/paa.cant_asig,0) ELSE 0 END AS avance_acad_ano_ant,
                       monto_adeudado AS deuda_total,
                       CASE WHEN moroso_financiero THEN 'Si' ELSE 'No' END AS moroso_financiero,
                       CASE WHEN ($SQL_pruebas_psico)=2 THEN 'Si' ELSE 'No' END AS prubeas_psico,
                       CASE WHEN ($SQL_mat)=1  THEN 'Si' ELSE 'No' END AS matriculado,($SQL_fecha_ult_mat) AS fecha_ult_mat,
                       CASE WHEN ($SQL_tr_act) > 0 THEN 'Si' ELSE 'No' END AS tr_act,
                       CASE WHEN ($SQL_conv) > 0 THEN round(($SQL_conv)::numeric*100/(coalesce(m.cant_asig_oblig,0)+coalesce(m.cant_asig_elect,0)+coalesce(m.cant_asig_efp,0)),0) ELSE 0 END AS porc_conv,
                       CASE WHEN ($SQL_cursos_insc)>0 THEN (($SQL_cursos_aprob)::real/($SQL_cursos_insc)::real*100)::numeric(4,1) ELSE 0 END AS tasa_aprobacion_ant,
                       CASE WHEN ($SQL_cursos_insc_act)>0 THEN (($SQL_cursos_aprob_act)::real/($SQL_cursos_insc_act)::real*100)::numeric(4,1) ELSE 0 END AS tasa_aprobacion_act,
                       CASE WHEN ($SQL_cursos_insc_anoact)>0 THEN (($SQL_cursos_aprob_anoact)::real/($SQL_cursos_insc_anoact)::real*100)::numeric(4,1) ELSE 0 END AS porcentaje_avance,
                       ($SQL_cursos_modulares) AS total_cursos_modulares,
                       a.salida_int_fecha,a.salida_int_nroreg_libro,salida_int_calif,a.fecha_graduacion,a.nota_graduacion,
                       fecha_titulacion,a.fecha_egreso,anotaciones,
                       split_part(a.rut,'-',1) AS rut, split_part(a.rut,'-',2) AS dv,
                       translate(upper(split_part(trim(a.apellidos),' ',1)),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS ape_pat,
                       translate(upper(split_part(trim(a.apellidos),' ',2)),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS ape_mat,
                       translate(upper(a.nombres),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS nombre,
                       ($SQL_arancel_real) AS arancel_real,
                       translate(upper(a.direccion),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS direccion,
                       co.cod_ingresa_cae AS cod_comuna,co.cod_ciudad_ingresa_cae AS cod_ciudad,a.region AS cod_region,
                       coalesce(($SQL_nivel_estudios),1) AS nivel_estudios, 
                       a.nombres,a.apellidos,co.nombre AS comuna,p.nacionalidad,m.ano AS malla,a.nombre_usuario,
                       ies.nombre_original AS ies_proced,a.carr_ies_pro AS ies_carrera_proced,col.rbd,col.nombre AS colegio,
                       a.examen_grado_titulo_fecha,a.examen_grado_titulo_oportunidades,a.examen_grado_titulo_calif,a.nota_titulacion,a.nro_registro_libro_tit,
                       CASE WHEN ($SQL_alsies)=1 THEN 'Si' ELSE 'No' END AS al_sies_$ANO_MATRICULA  $SQL_alumnos_asist2 $SQL_torniquete
                FROM alumnos AS a
                LEFT JOIN pap ON pap.id=a.id_pap
                LEFT JOIN carreras   AS c ON c.id=a.carrera_actual
                LEFT JOIN regimenes_ AS r ON r.id=c.regimen
                LEFT JOIN admision_tipo AS adm ON adm.id=a.admision
                LEFT JOIN mallas     AS m ON m.id=a.malla_actual
                LEFT JOIN colegios   AS col ON col.rbd=a.rbd_colegio
                LEFT JOIN inst_edsup AS ies ON ies.id=a.id_inst_edsup_proced
                LEFT JOIN comunas    AS co ON co.id=a.comuna
                LEFT JOIN pais       AS p ON p.localizacion=a.nacionalidad
                LEFT JOIN al_estados AS ae ON ae.id=a.estado
                LEFT JOIN usuarios   AS u ON u.id=a.estado_id_usuario
                LEFT JOIN ($SQL_prom_anoant)       AS paa ON paa.id_alumno=a.id
                LEFT JOIN ($SQL_prom_aprob_anoant) AS paaa ON paaa.id_alumno=a.id
                LEFT JOIN vista_contratos_rut_carrera_monto_adeudado AS vcrcma ON (vcrcma.rut=a.rut AND vcrcma.id_carrera=a.carrera_actual)
                $SQL_left_join_fuasm
                $SQL_left_join_renov_cae
                $SQL_left_join_presel_pub
                $SQL_left_join_presel_mineduc
                $SQL_left_join_alumnos_sies
                $SQL_left_join_matriculas
                $SQL_left_join_benef_fiscal
                $SQL_left_join_torniquete
                $condicion
                ORDER BY a.apellidos,a.nombres ";
$SQL_tabla_completa = "COPY ($SQL_alumnos) to stdout WITH CSV HEADER";

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

$SQL_al_SIES = "SELECT 'R' AS tipo_docto_ident,
                       split_part(a.rut,'-',1) AS rut, split_part(a.rut,'-',2) AS dv,
                       translate(upper(split_part(trim(a.apellidos),' ',1)),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS ape_pat,
                       translate(upper(split_part(trim(a.apellidos),' ',2)),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS ape_mat,
                       translate(upper(a.nombres),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS nombre,
                       CASE a.genero WHEN 'f' THEN 'M' WHEN 'm' THEN 'H' END AS genero,
                       to_char(a.fec_nac,'DD/MM/YYYY') AS fec_nac,
                       p.cod_sies AS nacionalidad,
                       p.cod_sies AS pais_est_sec,1 as cod_sede,c.cod_sies_matunif AS cod_carrera_matunif,
                       c.modalidad,
                       CASE WHEN c.regimen='PRE' AND a.jornada='D' THEN 1
                            WHEN c.regimen='PRE' AND a.jornada='V' THEN 2
                            WHEN c.regimen IN ('POST-GD','POST-TD','DIP-D') AND a.jornada='V' THEN 4
                       END AS jornada,1 AS version,
                       CASE WHEN a.admision IN (1,10) THEN 1
							              WHEN a.admision IN (2,20) THEN 4
							              WHEN a.admision = 3 THEN 10
                       END AS forma_ingreso,a.cohorte,a.semestre_cohorte,
                       coalesce(($SQL_ano_ing_orig),a.cohorte) AS ano_ing_origen,a.semestre_cohorte AS sem_ing_origen,
                       ($SQL_asig_ano_ant_insc) AS asig_insc_ano_ant,($SQL_asig_ano_ant_aprob) AS asig_aprob_ano_ant,
                       ($SQL_prom_ano_ant_1sem) AS prom_ano_ant_1sem,($SQL_prom_ano_ant_2sem) AS prom_ano_ant_2sem,
                       ($SQL_asig_hist_insc) AS asig_hist_insc,($SQL_asig_hist_aprob) AS asig_hist_aprob,
                       CASE WHEN ($SQL_nivel_acad) IS NULL THEN ($SQL_nivel_acad_alter) ELSE ($SQL_nivel_acad) END AS nivel_academico,
                       0 AS sit_socioeco_fon_sol,
                       (($ANO-a.cohorte)+1)*2+(CASE WHEN $SEMESTRE<=a.semestre_cohorte THEN -1 ELSE 0 END)-($SQL_al_presente) AS semestres_susp,
                       CASE WHEN a.cohorte=$ANO_MATRICULA THEN ($SQL_fecha_ult_mat) ELSE null END AS fecha_ult_mat,
                       0 AS reincoporacion,
                       1 AS vigencia,c.nombre AS nombre_carrera,a.jornada  $SQL_alumnos_asist2
                FROM alumnos AS a
                LEFT JOIN pap		        ON pap.id=a.id_pap
                LEFT JOIN carreras   AS c   ON c.id=a.carrera_actual
                LEFT JOIN admision_tipo AS adm ON adm.id=a.admision
                LEFT JOIN pais       AS p   ON p.localizacion=a.nacionalidad
                LEFT JOIN mallas     AS vm  ON vm.id=a.malla_actual
                LEFT JOIN al_estados AS ae  ON ae.id=a.estado
                $SQL_left_join_matriculas
                $condicion 
                ORDER BY c.alias,a.jornada,a.apellidos,a.nombres"; 
$SQL_tabla_completa_SIES = "COPY ($SQL_al_SIES) to stdout WITH CSV HEADER";

$SQL_tc_doctos = "SELECT a.rut,apellidos,nombres,ae.nombre as estado,c.alias||'-'||a.jornada as carrera,
                         ad.nombre AS admision,semestre_cohorte||'-'||cohorte as cohorte,a.nacionalidad,
                         ddt.nombre AS tipo_docto,dd.fecha
                  FROM doctos_digitalizados dd 
                  LEFT JOIN doctos_digital_tipos ddt on ddt.id=dd.id_tipo 
                  LEFT JOIN alumnos a                using(rut) 
                  LEFT JOIN admision_tipo ad         on ad.id=a.admision
                  LEFT JOIN al_estados ae            on ae.id=a.estado
                  LEFT JOIN mallas     AS m ON m.id=a.malla_actual
                  LEFT JOIN carreras c               on c.id=carrera_actual 
                  LEFT JOIN usuarios u               on u.id=dd.id_usuario
                  $condicion AND NOT dd.eliminado";
$SQL_tc_doctos = "COPY ($SQL_tc_doctos) TO stdout WITH CSV HEADER";

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
	$SQL_total_alumnos =  "SELECT count(a.id) AS total_alumnos 
	                       FROM alumnos AS a 
	                       LEFT JOIN carreras AS c ON c.id=a.carrera_actual
                         LEFT JOIN pap ON pap.id=a.id_pap
                         LEFT JOIN al_estados AS ae ON ae.id=a.estado
                         LEFT JOIN mallas     AS m ON m.id=a.malla_actual
                         $SQL_left_join_fuasm
                         $SQL_left_join_renov_cae
                         $SQL_left_join_presel_pub
                         $SQL_left_join_presel_mineduc
                         $SQL_left_join_alumnos_sies
                         $SQL_left_join_matriculas
                         $SQL_left_join_benef_fiscal
                         $SQL_left_join_torniquete
	                       $condicion";
	$total_alumnos = consulta_sql($SQL_total_alumnos);
	$tot_reg = $total_alumnos[0]['total_alumnos'];
	
	$HTML_paginador = "Páginas ".html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}

if (count($alumnos) == 1 && $texto_buscar <> "") {
	echo(js("window.location='$enlbase=ver_alumno&id_alumno={$alumnos[0]['id']}&rut={$alumnos[0]['rut']}';"));
}

$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "" && $regimen <> "t") { $cond_carreras .= "AND regimen='$regimen' "; }

$SQL_carreras = "SELECT id,nombre||' ('||alias||')' AS nombre FROM carreras $cond_carreras AND activa ORDER BY nombre;";
$carreras = consulta_sql($SQL_carreras);

$SQL_carreras_novig = "SELECT id,nombre||' ('||alias||')' AS nombre FROM carreras $cond_carreras AND NOT activa ORDER BY nombre;";
$carreras_novig = consulta_sql($SQL_carreras_novig);


$SQL_al_estados = "SELECT id,nombre,agrupador as grupo FROM al_estados WHERE nombre NOT IN ('Moroso') ORDER BY agrupador,id;";
$al_estados = consulta_sql($SQL_al_estados);

$cohortes = $anos;

$cohortes = consulta_sql("SELECT DISTINCT ON (cohorte) cohorte AS id,cohorte AS nombre,CASE WHEN cohorte=$ANO THEN 'Nuevos' WHEN cohorte>$ANO THEN 'Futuros' ELSE 'Antiguos' END AS grupo FROM alumnos ORDER BY cohorte DESC");

$SEMESTRES_COHORTES = array(array("id"=>1,"nombre"=>1),
                            array("id"=>2,"nombre"=>2));

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

$APROB_ANT = array(array("id" => 1,  "nombre" => 'Mala (0%)'),
                   array("id" => 10, "nombre" => 'Reprobación Masiva'),
                   array("id" => 11, "nombre" => 'Sin Toma de Ramos'),
                   array("id" => 2,  "nombre" => 'Regular (1% ~ 39%)'),
                   array("id" => 3,  "nombre" => 'Buena (40% ~ 100%)'),
                   array("id" => 30, "nombre" => 'Regular y Buena'));

$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);

$id_sesion = "SIES_".$_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tabla_completa_SIES = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>TC SIES</small></a>";
$nombre_arch_SIES = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch_SIES,$SQL_tabla_completa_SIES);

$id_sesion = "doctos_digit_".$_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tc_doctos = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>TC doctos</small></a>";
$nombre_arch_doctos = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch_doctos,$SQL_tc_doctos);

$SIES_anos = consulta_sql("SELECT DISTINCT ON (ano) ano AS id,ano AS nombre FROM alumnos_sies ORDER BY ano DESC");

$MATRICULADO = array(array('id'=>"t", 'nombre'=>"Sí (sólo $SEMESTRE-$ANO)"),
                     array('id'=>"t1",'nombre'=>"Sí (Año $ANO)"),
                     array('id'=>"f", 'nombre'=>"No"));

$ADMISION = array_merge(array(array('id' => "1,3", 'nombre' => "1er año (Regular + Especial)")),$ADMISION) ;  

$EXCEP_FINAN = array(array('id'=>"con", 'nombre'=>"* sólo CON E. F."),
                     array('id'=>"sin", 'nombre'=>"* sólo SIN E. F."));
$solic_estados = consulta_sql("SELECT id,nombre FROM vista_solic_estados");
$EXCEP_FINAN = array_merge($EXCEP_FINAN,$solic_estados);

$REMAT_ATENCION = array(array('id'=>"con",             'nombre'=>"* sólo CON atenciones"),
                        array('id'=>"sin",             'nombre'=>"* sólo SIN atenciones"),
                        array('id'=>"Comprometido(a)", 'nombre'=>"Comprometido(a)s"),
                        array('id'=>"Sin respuesta",   'nombre'=>"Sin respuesta"),
                        array('id'=>"Desertor(a)",     'nombre'=>"Desertore(a)s"));

$CONVALIDACIONES = array(array("id" => '0',   "nombre" => '0%'),
                         array("id" => '1-60',"nombre" => 'entre 1% y 60%'),
                         array("id" => '61',  "nombre" => 'más de 60%'));

$POST_BECAUMC = array(array('id'=>"con", 'nombre'=>"* sólo CON Post."),
                      array('id'=>"sin", 'nombre'=>"* sólo SIN Post."));
$fuas_estados = consulta_sql("SELECT id,nombre FROM vista_fuas_estados");
$POST_BECAUMC = array_merge($POST_BECAUMC,$fuas_estados);

$ANOS_FUASM = consulta_sql("SELECT DISTINCT ON (ano) ano AS id,ano AS nombre FROM dae.fuas_mineduc");

$ANOS_RENOV_CAE = consulta_sql("SELECT DISTINCT ON (ano) ano AS id,ano AS nombre FROM dae.renovantes_cae");

$ANOS_PRESEL_PUB = consulta_sql("SELECT DISTINCT ON (ano) ano AS id,ano AS nombre FROM dae.cae_preseleccionados_publicacion");

$ANOS_PRESEL_MINEDUC = consulta_sql("SELECT DISTINCT ON (ano) ano AS id,ano AS nombre FROM dae.preseleccionados_beneficios_mineduc");

$NACIONALIDADES = consulta_sql("SELECT localizacion AS id,nacionalidad AS nombre FROM pais ORDER BY nacionalidad");
$NACIONALIDADES = array_merge(array(array('id' => "-2",'nombre' => "* Sólo extranjeros *")),$NACIONALIDADES);
?>

<div class="texto">
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Cohorte: <br>
<?php if ($regimen <> "PRE") { ?>          
          <select class="filtro" name="mes_cohorte" onChange="if (this.value > 6) { formulario.semestre_cohorte.value=2; } else { formulario.semestre_cohorte.value=1; } submitform();">
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
            <?php echo(select_group($cohortes,$cohorte)); ?>    
          </select>
<?php if ($cohorte > 0) { ?>
          <input type='checkbox' name='incluye_reinc' value='si' id='incluye_reinc' onClick='submitform();' <?php if ($incluye_reinc == 'si') { echo('checked'); } ?>>
          <label for='incluye_reinc'>Reincorporados</label>
<?php } ?>
        </td>
        <td class="celdaFiltro">
          Estado: <br>
          <select class="filtro" name="estado" onChange="submitform();">
            <option value="-1">Todos</option>
            <?php echo(select_group($al_estados,$estado)); ?>
          </select>
          <?php if ($estado > 1) { ?>
          <input type="date" max="<?php echo($fec_fin_estado_max); ?>" name="fec_ini_estado" onClick="this.value='<?php echo($ANO); ?>-01-01';" value="<?php echo($fec_ini_estado); ?>" size="10" class="boton" style='font-size: 9pt'>
          <input type="date" max="<?php echo($fec_fin_estado_max); ?>" name="fec_fin_estado" onClick='this.value=this.max;' value="<?php echo($fec_fin_estado); ?>" size="10" class="boton" style='font-size: 9pt'>
          <script>document.getElementById("fec_ini_estado").focus();</script>
          <input type='submit' name='buscar' value='Buscar' style='font-size: 9pt'>
          <?php } ?>
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
            <?php echo(select($MATRICULADO,$matriculado)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          P. Psico: <br>
          <select class="filtro" name="pruebas_psico" onChange="submitform();">
            <option value="a">Todos</option>
            <?php echo(select($sino,$pruebas_psico)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Tasa Aprob. Ant.: <br>
          <select class="filtro" name="aprob_ant" onChange="submitform();" style="max-width: 100px">
            <option value="t">Todos</option>
            <?php echo(select($APROB_ANT,$aprob_ant)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Tasa Conv: <br>
          <select class="filtro" name="tasa_conv" onChange="submitform();">
            <option value="t">Todos</option>
            <?php echo(select($CONVALIDACIONES,$tasa_conv)); ?>
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
            <optgroup label='Vigentes'>
            <?php echo(select($carreras,$id_carrera)); ?>
            </optgroup>
            <optgroup label='No vigentes'>
            <?php echo(select($carreras_novig,$id_carrera)); ?>
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
        <td class="celdaFiltro">
          Nacionalidad: <br>
          <select class="filtro" name="nacionalidad" onChange="submitform();">
            <option value="">Todos</option>
            <?php echo(select($NACIONALIDADES,$nacionalidad)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          <div title='Beneficiario Fiscal (aplicado en contrato)'>B. F.:</div>
          <select class="filtro" name="id_benef_fiscal" onChange="submitform();">
            <option value="a">--</option>
            <?php echo(select($sino,$id_benef_fiscal)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          SIES: <br>
          <select class="filtro" name="ano_sies" onChange="submitform();">
            <option value="">--</option>
            <?php echo(select($SIES_anos,$ano_sies)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          FUAS: <br>
          <select class="filtro" name="ano_fuas" onChange="submitform();">
            <option value="">--</option>
            <?php echo(select($ANOS_FUASM,$ano_fuas)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          <div title='Renovantes CAE'>Renov. CAE: </div>
          <select class="filtro" name="ano_renov_cae" onChange="submitform();">
            <option value="">--</option>
            <?php echo(select($ANOS_RENOV_CAE,$ano_renov_cae)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          <div title='Preseleccionados para CAE'>Presel. CAE.:</div>
          <select class="filtro" name="ano_presel_pub" onChange="submitform();">
            <option value="">--</option>
            <?php echo(select($ANOS_PRESEL_PUB,$ano_presel_pub)); ?>
          </select>
        </td>        
        <td class="celdaFiltro">
          <div title='Preseleccionados Becas MINEDUC'>Presel. ME.: </div>
          <select class="filtro" name="ano_presel_mineduc" onChange="submitform();">
            <option value="">--</option>
            <?php echo(select($ANOS_PRESEL_MINEDUC,$ano_presel_mineduc)); ?>
          </select>
        </td>
<?php if ($modulo == "alumnos_rematriculables") { ?>
        <td class="celdaFiltro">
          Excep. Finan.:<br>
          <select class="filtro" name="excep_finan" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($EXCEP_FINAN,$excep_finan)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Atenciones:<br>
          <select class="filtro" name="remat_atencion" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($REMAT_ATENCION,$remat_atencion)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Post. Beca UMC:<br>
          <select class="filtro" name="post_becaumc" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($POST_BECAUMC,$post_becaumc)); ?>
          </select>
        </td>
<?php 	} ?>
      </tr>
    </table>
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Buscar por ID, RUT o nombre:<br>
          <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="45" id="texto_buscar" class='boton'>
          <input type='submit' name='buscar' value='Buscar'>          
          <?php 
          	if ($buscar == "Buscar" && $texto_buscar <> "") {
          		echo(" <input type='submit' name='buscar' value='Vaciar'>");
          	};
          ?>          <script>document.getElementById("texto_buscar").focus();</script>
        </td>
        <!-- <td class="celdaFiltro">
          Ver Datos de Contacto:<br>
          <input type="checkbox" name="ver_datos_contacto" value="Si" class='boton' onClick="submitform();" <?php if($ver_datos_contacto=="Si") { echo("checked"); } ?>>
        </td> -->
        <!-- <td class="celdaFiltro">
          SIES <?php echo($ano_sies); ?>: <br>
          <select class="filtro" name="sies_info" onChange="submitform();">
            <option value="a">Todos</option>
            <?php echo(select($sino,$sies_info)); ?>
          </select>          
        </td> -->
<?php if ($modulo == "alumnos_rematriculables") { ?>
        <td class="celdaFiltro">
          Revisar:<br>
          <a href="<?php echo("$enlbase=$modulo&cohorte=$cohorte_1erano&ano_sies=$cohorte_1erano&admision=1,3&estado=1&excep_finan=sin&remat_atencion=sin&post_becaumc=sin"); ?>" class='botoncito'>Cohorte <?php echo($cohorte_1erano); ?> SIES</a>
          <a href="<?php echo("$enlbase=$modulo&cohorte=$cohorte_2doano&ano_sies=$cohorte_2doano&admision=1,3&estado=1&excep_finan=sin&remat_atencion=sin&post_becaumc=sin"); ?>" class='botoncito'>Cohorte <?php echo($cohorte_2doano); ?> SIES</a>
          <a href="<?php echo("$enlbase=resumen_remat_retencion&"); ?>" class='botoncito'>Resumen MAT<?php echo($ANO_MATRICULA); ?></a>
        </td>
<?php } ?>
<?php if ($modulo == "gestion_alumnos") { ?>
	    <td class="celdaFiltro">
	      Ver asistencia según:<br>
        <span style='font-weight: normal'>
	      <input type="checkbox" name="mostrar_asistencia" id="mostrar_asistencia" value="si" id="mostrar_asistencia" onClick="submitform();" <?php echo($mostrar_asistencia == "si" ? "checked" : ""); ?>>
        <label for="mostrar_asistencia">Libro de Clases</label>
        &nbsp;
        <input type="checkbox" name="mostrar_torniquete" id="mostrar_torniquete" value="si" id="mostrar_torniquete" onClick="submitform();" <?php echo($mostrar_torniquete == "si" ? "checked" : ""); ?>>
        <label for="mostrar_torniquete">Acceso Edificio</label>
        </span>
	    </td>
<?php 	if ($mostrar_asistencia == "si") { ?>
        <td class="celdaFiltro">      
          Periodo Asist. Libro de Clases:<br>
          <input type="date" name="fec_ini_asist" value="<?php echo($fec_ini_asist); ?>" class="boton" style='font-size: 8pt' onBlur="formulario.fec_fin_asist.value=this.value;"> al
          <input type="date" <br>"fec_fin_asist" value="<?php echo($fec_fin_asist); ?>" class="boton" style='font-size: 8pt'>
          <input type='submit' name='buscar' value='Buscar' class="botoncito">          
        </td>
<?php 	} ?>
<?php 	if ($mostrar_torniquete == "si") { ?>
        <td class="celdaFiltro">      
          Periodo Acceso Edificio:<br>
          <input type="date" name="fec_ini_asist" value="<?php echo($fec_ini_asist); ?>" class="boton" style='font-size: 8pt' onBlur="formulario.fec_fin_asist.value=this.value;"> al
          <input type="date" name="fec_fin_asist" value="<?php echo($fec_fin_asist); ?>" class="boton" style='font-size: 8pt'>
          <input type='submit' name='buscar' value='Buscar' class="botoncito">          
        </td>
<?php 	} ?>
<?php } ?>
      </tr>
    </table>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="3">
      Mostrando <b><?php echo($tot_reg); ?></b> estudiante(s) en total, en página(s) de
      <select class='filtro' name="cant_reg" onChange="submitform();">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas
    </td>
    <td class="texto" align="right" colspan="20">
      <?php echo($HTML_paginador); ?>
      <?php echo($boton_tabla_completa); ?>
      <?php echo($boton_tabla_completa_SIES); ?>
      <?php echo($boton_tc_doctos); ?>
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>RUT</td>
    <td class='tituloTabla'>Nombre</td>
    <td class='tituloTabla'>Carrera</td>
    <td class='tituloTabla'>Cohorte</td>
    <td class='tituloTabla'>Estado</td>
    <td class='tituloTabla'>Aprob Ant.</td>
<?php if ($modulo=="alumnos_rematriculables") { ?>
    <td class='tituloTabla'><small>Excep.<br>Finan.</small></td>
    <td class='tituloTabla'><small>Atención<br>Remat.</small></td>
    <td class='tituloTabla'><small>Post.<br>Beca UMC.</small></td>
<?php } ?>        
    <td class='tituloTabla'>Mat?</td>
    <!-- <td class='tituloTabla'>SIES?</td> -->
    <td class='tituloTabla'>T.R.?</td>
    <td class='tituloTabla'>Conv.</td>
<?php if ($modulo=="gestion_alumnos" && $mostrar_asistencia == "si") { ?>
    <td class='tituloTabla'>Asist.</td>
<?php } ?>   
<?php if ($modulo=="gestion_alumnos" && $mostrar_torniquete == "si") { ?>
    <td class='tituloTabla'><small>Días de acceso<br>al Edificio</small></td>
<?php } ?>        
<?php if ($ver_datos_contacto == "Si") { ?>
    <td class='tituloTabla'>Teléfono</td>
    <td class='tituloTabla'>Tel. Movil</td>
    <td class='tituloTabla'>e-Mail</td>
<?php } ?>
  </tr>
<?php
	$HTML_alumnos = "";
	if (count($alumnos) > 0) {
		for ($x=0;$x<count($alumnos);$x++) {
			extract($alumnos[$x]);
			
			$enl = "$enlbase=$modulo_destino&id_alumno=$id&rut=$rut";
			$enlace = "a class='enlitem' href='$enl'";
			
			if ($moroso_financiero == "t") { $estado .= " <sup>(M)</sup>"; }
			
			if ($mes_cohorte <> "") { $mes_cohorte = "(".substr($meses_palabra[$mes_cohorte-1]['nombre'],0,3).")"; }
			
			$HTML_datos_contacto = "";
			if ($ver_datos_contacto == "Si") { 
				$HTML_datos_contacto = "    <td class='textoTabla'><small>$telefono</small></td>"
									 . "    <td class='textoTabla'><small>$tel_movil</small></td>"
									 . "    <td class='textoTabla'><small>$email</small></td>";
			}
			
			if ($modulo == "gestion_alumnos" && $mostrar_asistencia == "si") {
				$asistencia = "";
				if ($asist_total > 0) {
					$tasa_asist_presente = round($asist_presente*100/$asist_total,0);
					$asistencia = "    <td class='textoTabla' align='center'>$tasa_asist_presente%</td>\n";
				} else {
					$asistencia = "    <td class='textoTabla' align='center'> - </td>\n";
				}
			}

			if ($modulo == "gestion_alumnos" && $mostrar_torniquete == "si") {
				$torniquete = "    <td class='textoTabla' align='center'> $cant_dias_accede_edif </td>\n";
			}

      		$remat = "";
      		if ($modulo == "alumnos_rematriculables") {

        		if ($remat_atencion == "") { 
          			$remat_atencion = "<a href='$enlbase_sm=remat_atenciones_agregar&id_alumno=$id' id='sgu_fancybox' class='botoncito'> + agregar</a>";
        		} else {
          			$estilo_remat = "";        
          			if ($remat_atencion == "Desertor(a)") { $estilo_remat = "background: red; border-radius: 2px; color: white; padding: 2px 4px";  }
          			if ($remat_atencion == "Comprometido(a)") { $estilo_remat = "background: orange; border-radius: 2px; color: white; padding: 2px 4px";  }
          			$remat_atencion = "<a href='$enlbase_sm=remat_atenciones&id_alumno=$id' id='sgu_fancybox'><span style='$estilo_remat'><b>$remat_atencion</b></span></a>";
        		}

				$remat = "    <td class='textoTabla' align='center'><small>$excep_finan</small></td>\n"
					. "    <td class='textoTabla' align='center'><small>$remat_atencion</small></td>\n"
					. "    <td class='textoTabla' align='center'><small>$post_becaumc</small></td>\n";
			}

			if ($cohorte_reinc <> "") { 
				if ($mes_cohorte_reinc <> "") { $mes_cohorte_reinc = "(".substr($meses_palabra[$mes_cohorte_reinc-1]['nombre'],0,3).")"; }
				$cohorte_reinc = "<small><br>Reinc: $cohorte_reinc $mes_cohorte_reinc</small>";
			}

			if ($benef_fiscal <> "") { $benef_fiscal = "<sup><a href='#' title='$benef_fiscal' class='enlaces'>BF</a></sup>" ;}
			
			$HTML_alumnos .= "  <tr class='filaTabla' >\n"
			               . "    <td class='textoTabla'>$id</td>\n"
			               . "    <td class='textoTabla'>$rut</td>\n"
			               . "    <td class='textoTabla'><a class='enlaces' href='$enl'>$nombre</a></td>\n"
			               . "    <td class='textoTabla'>$carrera</td>\n"
			               . "    <td class='textoTabla'>$cohorte $mes_cohorte $cohorte_reinc</td>\n"
			               . "    <td class='textoTabla'><span title='$estado_fecha por $estado_operador'>$estado</span> $benef_fiscal</td>\n"
			               . "    <td class='textoTabla' align='right'>$tasa_aprobacion_ant%</td>\n"
                           . $remat
			               . "    <td class='textoTabla' align='center'>$matriculado</td>\n"
			               //. "    <td class='textoTabla' align='center'>$info_sies</td>\n"
			               . "    <td class='textoTabla' align='center'>$tr_act</td>\n"
			               . "    <td class='textoTabla' align='center'>$porc_conv%</td>\n"
			               . $asistencia
						   . $torniquete
			               . $HTML_datos_contacto
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
<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'		: false,
		'autoDimensions': false,
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic',
		'width'			: 800,
		'maxHeight'		: 9999,
		'afterClose'	: function () { location.reload(true); },
		'type'			: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_big").fancybox({
		'autoScale'		: false,
		'autoDimensions': false,
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic',
		'width'			: 1000,
		'maxHeight'		: 9999,
		'afterClose'	: function () { location.reload(true); },
		'type'			: 'iframe'
	});
});
</script>
