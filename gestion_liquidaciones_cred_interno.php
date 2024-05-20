<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

setlocale(LC_MONETARY,"es_CL.UTF8");
include("validar_modulo.php");

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) {	$cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if (empty($reg_inicio)) { $reg_inicio = 0; }

$texto_buscar        = $_REQUEST['texto_buscar'];
$buscar              = $_REQUEST['buscar'];
$buscar_fecha        = $_REQUEST['buscar_fecha'];
$ano                 = $_REQUEST['ano'];
$estado              = $_REQUEST['estado'];
$estado_legal        = $_REQUEST['estado_legal'];
$id_carrera          = $_REQUEST['id_carrera'];
$jornada             = $_REQUEST['jornada'];
$semestre_cohorte    = $_REQUEST['semestre_cohorte'];
$cohorte             = $_REQUEST['cohorte'];
$condonado           = $_REQUEST['condonado'];
$regimen             = $_REQUEST['regimen'];
$cant_cuotas_morosas = $_REQUEST['cant_cuotas_morosas'];
$forma_pago          = $_REQUEST['forma_pago'];
$emisor              = $_REQUEST['emisor'];
$fec_ini_pago        = $_REQUEST['fec_ini_pago'];
$fec_fin_pago        = $_REQUEST['fec_fin_pago'];

//if (empty($ano))      { $ano = $ANO; }
if ($estado == "")    { $estado = '-1'; }
if (empty($_REQUEST['cohorte'])) { $cohorte = 0; }
if (empty($_REQUEST['semestre_cohorte'])) { $semestre_cohorte = 0; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }
if ($_REQUEST['cant_cuotas_morosas'] == "") { $cant_cuotas_morosas = -2; }

$SQL_pagos_aranceles = "SELECT 0";	

$condicion = $cond_pagos = "WHERE true ";

if ($buscar == 'Buscar' && $texto_buscar <> "") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar = explode(" ",$texto_buscar_regexp);
	$condicion = "WHERE ";
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= "(lower(va.nombre) ~* '$cadena_buscada' OR "
		           .  " a.rut ~* '$cadena_buscada' OR "
		           .  " text(c.id) ~* '$cadena_buscada' "
		           .  ") AND ";
	}
	$condicion=substr($condicion,0,-4);
	$ano = $estado = $estado_legal = $regimen = $id_carrera = $jornada = null;	
} else {

	if ($id_carrera <> "") { $condicion .= "AND c.id_carrera=$id_carrera "; }

	if ($jornada <> "") { $condicion .= "AND c.jornada='$jornada' "; }
	
	if ($cohorte > 0) {
		$condicion .= "AND (a.cohorte = '$cohorte') ";
	}

	if ($semestre_cohorte > 0) {
		$condicion .= "AND (a.semestre_cohorte = $semestre_cohorte) ";
	}

	if ($ano > 0) { $condicion .= "AND date_part('year',c.fecha) = $ano "; }
	
	if ($tipo == "Anual" || $tipo == "Modular") { $semestre = null; }
	
	if (!is_null($semestre)) { $condicion .= "AND c.semestre=$semestre "; }

	if ($estado <> "") {
		if ($estado == "-1")  { $condicion .= "AND NOT c.nulo "; } 
		elseif ($estado == "-2") { $condicion .= "AND c.nulo "; }
		elseif ($estado == "1") { $condicion .= "AND c.estado IS NOT NULL "; }
		elseif ($estado == "D") { $condicion .= "AND c.estado IN ('S','R','A') "; }
		elseif ($estado != "0") { $condicion .= "AND NOT c.nulo AND c.estado='$estado' "; }
	}
	
	if ($tipo <> "" && $tipo <> "0") { $condicion .= "AND c.tipo='$tipo' "; }
	
	if ($tipo_alumno == "N") { $condicion .= "AND c.id_pap IS NOT NULL "; }
	if ($tipo_alumno == "A") { $condicion .= "AND c.id_alumno IS NOT NULL "; }
	
	if ($beca == "100")  { $condicion .= "AND c.id_convenio IS NOT NULL "; }
	elseif ($beca <> "") { $condicion .= "AND c.id_beca_arancel = $beca "; }
	
	if ($condonado == "t") { $condicion .= "AND c.monto_condonacion IS NOT NULL "; }
	
	if ($regimen <> "" && $regimen <> "t") { $condicion .= "AND (car.regimen = '$regimen') "; }
	
	if ($mat_pagada == "t") { $condicion .= "AND (mat_pagada > 0 ) "; } 
	elseif ($mat_pagada == "f") { $condicion .= "AND (mat_pagada = 0 AND monto_mat>0 AND (coalesce(0,c.porc_beca_mat)<100 OR c.monto_matricula<>coalesce(0,c.monto_beca_mat))) "; } 
	
	if ($cant_cuotas_morosas >= 0) { $condicion .= "AND vc.cant_cuotas_morosas = $cant_cuotas_morosas "; }
	if ($cant_cuotas_morosas == -1) { $condicion .= "AND vc.cant_cuotas_morosas >= 1 "; }
	
	if ($emisor > 0) { $condicion .= "AND c.id_emisor=$emisor "; }
	
	if ($forma_pago <> "") { $condicion .= "AND $forma_pago>0 "; }
		
	if (!empty($fec_ini_pago) && !empty($fec_fin_pago)) { 
		$cond_pagos .= " AND p.fecha::date BETWEEN '$fec_ini_pago'::date AND '$fec_fin_pago'::date ";
		$SQL_pagos_aranceles = "(SELECT sum(monto_pagado) 
                                 FROM finanzas.cobros AS c 
                                 LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_cobro=c.id
                                 LEFT JOIN finanzas.pagos         AS p  ON p.id=pd.id_pago 
                                 $cond_pagos AND id_convenio_ci=vc.id
                                )";
	}

}

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_monto_pagado   = "SELECT sum(coalesce(monto_abonado,monto)) FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa>1 AND (pagado OR abonado)";
$SQL_mat_pagada     = "SELECT count(id) FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa=1 AND pagado";
$SQL_monto_saldot   = "SELECT sum(coalesce(monto-monto_abonado,monto)) FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa>1 AND (NOT pagado OR abonado)";
$SQL_monto_moroso   = "SELECT sum(coalesce(monto-monto_abonado,monto)) FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa>1 AND fecha_venc < now() AND (NOT pagado OR abonado)";
$SQL_cuotas_morosas = "SELECT count(id) FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa>1 AND fecha_venc < now() AND (NOT pagado OR abonado)";
$SQL_saldot_lp = "SELECT sum(coalesce(monto_abonado,monto)) FROM finanzas.cobros WHERE id_convenio_ci=c.id AND date_part('year',fecha_venc) > date_part('year',now()) AND (NOT pagado OR abonado)";

$SQL_convenios_ci = "SELECT c.id,to_char(c.fecha,'DD-tmMon-YYYY') AS fecha,c.estado,
                            date_part('year',c.fecha) AS periodo,trim(a.rut) AS rut,c.id_alumno,
                            upper(a.apellidos) AS al_apellidos,initcap(a.nombres) AS al_nombres,c.monto_liqci,
                            c.monto_liqci::float/uf.valor::float AS monto_liqci_uf,
                            c.descuento_inicial,c.monto_adicional,c.monto_adicional::float/uf.valor::float AS monto_adicional_uf,
                            c.descuento_inicial::float/uf.valor::float AS descuento_inicial_uf,
                            trim(car.alias) AS carrera,a.jornada,
                            c.liqci_efectivo,c.liqci_cheque,coalesce(c.liqci_cant_cheques,0) AS liqci_cant_cheques,
                            c.liqci_pagare,coalesce(c.liqci_cuotas_pagare,0) AS liqci_cuotas_pagare,
                            c.liqci_tarj_credito,coalesce(c.liqci_cant_tarj_credito,0) AS liqci_cant_tarj_credito,
                            CASE WHEN c.monto_condonacion>0 THEN '(C)' END AS condonacion,c.monto_condonacion,
                            to_char(c.fecha_condonacion,'DD-MM-YYYY') AS fecha_condonacion,
                            vc.total_pagado AS monto_pagado,vc.saldo_total AS monto_saldot,vc.monto_moroso,vc.cant_cuotas_morosas AS cuotas_morosas,
                            ($SQL_saldot_lp) AS monto_saldot_lp,
                            ($SQL_pagos_aranceles) AS pagos_rango_fechas,
                            u.nombre_usuario AS emisor,c.comentarios,
                            a.direccion,va.comuna,va.region,a.telefono,a.tel_movil,a.email,a.genero,a.fec_nac,
                            a.cohorte,a.mes_cohorte,uf.valor AS valor_uf,c.nulo
                     FROM finanzas.convenios_ci AS c
                     LEFT JOIN finanzas.vista_convenios_ci AS vc USING (id)
                     LEFT JOIN alumnos         AS a   ON a.id=c.id_alumno
                     LEFT JOIN carreras        AS car ON car.id=a.carrera_actual
                     LEFT JOIN usuarios        AS u   ON u.id=c.id_emisor                             
                     LEFT JOIN vista_alumnos   AS va   ON va.id=c.id_alumno
                     LEFT JOIN finanzas.valor_uf AS uf ON uf.fecha=c.fecha
                     $condicion
                     ORDER BY c.fecha DESC,al_apellidos,al_nombres ";
echo("<!-- $SQL_convenios_ci -->");
$SQL_tabla_completa = "COPY ($SQL_convenios_ci) to stdout WITH CSV HEADER";
$SQL_convenios_ci .= "$limite_reg OFFSET $reg_inicio;";
$convenios_ci     = consulta_sql($SQL_convenios_ci);

$SQL_fec_moroso = "SELECT max(fecha_venc) FROM finanzas.cobros WHERE id_convenio_ci=c.id AND (NOT pagado OR abonado) AND fecha_venc<now()::date AND fecha_dicom IS NULL";
$SQL_monto_moroso = "SELECT sum(monto-coalesce(monto_abonado,0)) FROM finanzas.cobros WHERE id_convenio_ci=c.id AND (NOT pagado OR abonado) AND fecha_venc<now()::date AND fecha_dicom IS NULL";

$SQL_ult_pago = "SELECT max(p.fecha::date) FROM finanzas.pagos_detalle pd LEFT JOIN finanzas.pagos p ON p.id=pd.id_pago LEFT JOIN finanzas.cobros cob ON cob.id=pd.id_cobro WHERE cob.id_convenio_ci=c.id";

$SQL_cci_dicom = "SELECT 825570 AS cod_aportante, replace(lpad(trim(a.rut),10,'0'),'-','') AS rut,
                         to_char(($SQL_fec_moroso),'YYYYMMDD') AS fecha_venc,'01' AS num_doc,'01' as tipo_trans,
                         upper(a.nombres||''||a.apellidos) AS nombre_alumno,
                         '01' AS tipo_calle,upper(a.direccion) AS nombre_calle,'' AS num_calle,'' AS num_depto,'' AS ind_depto_local_oficina,'01' AS tipo_domicilio,
                         upper(com.nombre) AS comuna,upper(reg.nombre) AS ciudad,'' AS cod_postal,coalesce(a.tel_movil,a.telefono) AS telefono,
                         'PG' AS tipo_doc,'UF' AS tipo_moneda,round(($SQL_monto_moroso)/uf.valor,2) AS monto_moroso_uf,($SQL_ult_pago) AS fecha_ult_pago
                  FROM finanzas.convenios_ci AS c
                  LEFT JOIN finanzas.vista_convenios_ci AS vc USING (id)
                  LEFT JOIN alumnos         AS a   ON a.id=c.id_alumno
                  LEFT JOIN comunas         AS com ON com.id=a.comuna
                  LEFT JOIN regiones        AS reg ON reg.id=a.region
                  LEFT JOIN carreras        AS car ON car.id=a.carrera_actual
                  LEFT JOIN vista_alumnos   AS va   ON va.id=c.id_alumno
                  LEFT JOIN finanzas.valor_uf AS uf ON uf.fecha=now()::date
                  $condicion AND ($SQL_monto_moroso) > 0
                  ORDER BY c.fecha DESC,nombre_alumno";
$SQL_cci_dicom = "COPY ($SQL_cci_dicom) to stdout WITH CSV HEADER FORCE QUOTE *";

$enlace_nav = "$enlbase=$modulo"
            . "&ano=$ano"
            . "&semestre=$semestre"
            . "&estado=$estado"
            . "&tipo=$tipo"
            . "&tipo_alumno=$tipo_alumno"
            . "&beca=$beca"
            . "&condonado=$condonado"
            . "&regimen=$regimen"
            . "&cant_cuotas_morosas=$cant_cuotas_morosas"
            . "&mat_pagada=$mat_pagada"
            . "&id_carrera=$id_carrera"
            . "&jornada=$jornada"
            . "&texto_buscar=$texto_buscar"
            . "&buscar=$buscar"
            . "&cant_reg=$cant_reg"
            . "&r_inicio";

if (count($convenios_ci) > 0) {
	$SQL_convenios_ci = "SELECT c.id
	                     FROM finanzas.convenios_ci AS c
	                     LEFT JOIN finanzas.vista_convenios_ci AS vc USING (id)
	                     LEFT JOIN alumnos         AS a   ON a.id=c.id_alumno
	                     LEFT JOIN carreras        AS car ON car.id=a.carrera_actual
	                     LEFT JOIN usuarios        AS u   ON u.id=c.id_emisor
	                     LEFT JOIN vista_alumnos   AS va   ON va.id=c.id_alumno
	                     $condicion";
	$SQL_max_cuotas_morosas = "SELECT floor(date_part('day',now()-min(fecha_venc))/30) AS max_cuotas_morosas
	                           FROM finanzas.cobros
	                           WHERE NOT pagado AND id_convenio_ci IN ($SQL_convenios_ci)";
	$max_cuotas_morosas     = consulta_sql($SQL_max_cuotas_morosas);
	$CUOTAS_MOROSAS = array();
	for ($x=0;$x<=$max_cuotas_morosas[0]['max_cuotas_morosas'];$x++) {
		$CUOTAS_MOROSAS = array_merge($CUOTAS_MOROSAS,array(array('id'=>$x,'nombre'=>"$x")));
	}
	$CUOTAS_MOROSAS = array_merge(array(array('id'=>-1,'nombre'=>"1 o más")),$CUOTAS_MOROSAS);

	$SQL_total_convenios_ci =  "SELECT count(c.id) AS total_convenios_ci
	                            FROM finanzas.convenios_ci AS c
	                            LEFT JOIN finanzas.vista_convenios_ci AS vc USING (id)
	                            LEFT JOIN alumnos         AS a   ON a.id=c.id_alumno
	                            LEFT JOIN carreras        AS car ON car.id=a.carrera_actual
	                            LEFT JOIN usuarios        AS u   ON u.id=c.id_emisor
	                            LEFT JOIN vista_alumnos   AS va   ON va.id=c.id_alumno
	                            $condicion";
	$total_convenios_ci = consulta_sql($SQL_total_convenios_ci);
	$tot_reg            = $total_convenios_ci[0]['total_convenios_ci'];
	
	$HTML_paginador = "Páginas ".html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}

$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "")      { $cond_carreras .= "AND regimen='$regimen' "; }
$carreras = consulta_sql("SELECT id,nombre FROM carreras $cond_carreras ORDER BY nombre;");

$anos_convenios_ci = array(); $x = 0;
for ($ano_ci=date("Y");$ano_ci>=2013;$ano_ci--) { $anos_convenios_ci[$x] = array("id" => $ano_ci,"nombre" => $ano_ci); $x++; }

$cohortes = $anos;

$SEMESTRES_COHORTES = array(array("id"=>1,"nombre"=>1),
                            array("id"=>2,"nombre"=>2));

$SEMESTRES = array(array("id"=>0,"nombre"=>0),
                   array("id"=>1,"nombre"=>1),
                   array("id"=>2,"nombre"=>2));

$_SESSION['enlace_volver'] = "$enlace_nav=$reg_inicio";

$emisores = consulta_sql("SELECT id,nombre FROM vista_usuarios WHERE id IN (SELECT id_emisor FROM finanzas.convenios_ci WHERE date_part('year',fecha)=$ano)");

$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();

$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);

$boton_tabla_completa_dicom = "";
if ($cant_cuotas_morosas == -1) {
	$boton_tabla_completa_dicom = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=dicom_$id_sesion');\" class='boton'><small>Tabla Completa DICOM</small></a>";
	$nombre_arch_dicom = "sql-fulltables/dicom_$id_sesion.sql";
	file_put_contents($nombre_arch_dicom,$SQL_cci_dicom);
}

$FORMAS_PAGO = array(array('id'=>"c.arancel_efectivo",       'nombre'=>"Efectivo"),
                     array('id'=>"c.arancel_cheque",         'nombre'=>"Cheque(s)"),
                     array('id'=>"c.arancel_pagare_coleg",   'nombre'=>"Pagaré LCI"),
                     array('id'=>"c.arancel_tarjeta_credito",'nombre'=>"Tarjeta de Crédito"));

$REGIMENES = consulta_sql("SELECT * FROM regimenes");


$ESTADOS = array(array('id'=>"al_curso",  'nombre'=>"En curso"),
                 array('id'=>"pre-egreso",'nombre'=>"Último Año"),
                 array('id'=>"egresados", 'nombre'=>"Egresados"));                 
                 

$ESTADOS_CCI = array(array('id'=>"-1",'nombre'=>"Todas"),
                      array('id'=>"-2",'nombre'=>"Nulos"));
$estados_cci = consulta_sql("SELECT * FROM vista_cci_estados");
$estados_cci = array_merge($ESTADOS_CCI,$estados_cci);
//$estados_cci = array_merge($ESTADOS,$estados_cci);

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<div class="texto" style='margin-top: 5px'>
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          <div align='left'>Año emisión:</div>
          <select class="filtro" name="ano" onChange="submitform();">
            <option value="0">Todos</option>
            <?php echo(select($anos_convenios_ci,$ano)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Estado:</div>
          <select class="filtro" name="estado" onChange="submitform();" style="max-width: 100px">
            <optgroup label="Alumnos sin Liquidar">
              <?php echo(select($ESTADOS,$estado)); ?>
            </optgroup>
            <optgroup label="Liquidaciones Emitidas">
              <?php echo(select($estados_cci,$estado)); ?>    
            </optgroup>
          </select>
        </td>
<!--        <td class="celdaFiltro">
          <div align='left'>Condonado:</div>
          <select class="filtro" name="condonado" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($sino,$condonado)); ?>    
          </select>
        </td> -->
        <td class="celdaFiltro">
          <div align='left'>Cuotas Morosas:</div>
          <select class="filtro" name="cant_cuotas_morosas" onChange="submitform();">
            <option value="-2">Todos</option>
            <?php echo(select($CUOTAS_MOROSAS,$cant_cuotas_morosas)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Forma de Pago:</div>
          <select class="filtro" name="forma_pago" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($FORMAS_PAGO,$forma_pago)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Recepción de Pagos:</div>
          <div style='font-weight: normal'>
            Entre el: <input type="date" name="fec_ini_pago" value="<?php echo($fec_ini_pago); ?>" class="boton">
            y el: <input type="date" name="fec_fin_pago" value="<?php echo($fec_fin_pago); ?>" class="boton">
            <input type="submit" name="buscar" value="Buscar">
          </div>
        </td>
      </tr>
    </table>
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          <div align='left'>Carrera/Programa:</div>
          <select class="filtro" name="id_carrera" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($carreras,$id_carrera)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Jornada:</div>
          <select class="filtro" name="jornada" onChange="submitform();">
            <option value="">Ambas</option>
            <?php echo(select($JORNADAS,$jornada)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Cohorte:</div>
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
          <div align='left'>Régimen:</div>
          <select class="filtro" name="regimen" onChange="submitform();">
            <option value="t">Todos</option>
            <?php echo(select($REGIMENES,$regimen)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Emisor:</div>
          <select class="filtro" name="emisor" onChange="submitform();">
            <option value="t">Todos</option>
            <?php echo(select($emisores,$emisor)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
		  Reportes:<br>
		  <a href='<?php echo("$enlbase_sm=gestion_cobranza_creditos_internos&ano=$ano&estado=$estado&id_carrera=$id_carrera&jornada=$jornada&regimen=$regimen"); ?>' class="boton" id='sgu_fancybox'>Gestión de Cobranza LCI</a>
		  <a href='<?php echo("$enlbase_sm=flujos_generales_ingresos_resumen&id_tipo=Créditos+Internos"); ?>' class="boton" id='sgu_fancybox'>Flujos de Ingresos</a>
        </td>
      </tr>
    </table>
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          <div align='left'>Buscar por Nº Convenio, RUT o nombre del alumno:</div>
          <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="40" id="texto_buscar" class="boton">
          <script>document.getElementById("texto_buscar").focus();</script>
          <input type='submit' name='buscar' value='Buscar'>          
          <?php 
          	if ($buscar == "Buscar" && $texto_buscar <> "") {
          		echo("<input type='submit' name='buscar' value='Vaciar'>");          		
          	};
          ?>
        </td>
        <td class="celdaFiltro">
		  Acciones:<br>
		  <a href='<?php echo("$enlbase_sm=crear_liquidacion_ci"); ?>' class="boton" id='sgu_fancybox_medium'>Crear Liquidación</a>
		  <a href='<?php echo("$enlbase_sm=convenios_ci_excepcion"); ?>' class="boton" id='sgu_fancybox_small'>Autorizar excepción</a>
        </td>
<!--        <td class="celdaFiltro">
		  Cambiar Estado Legal:<br>
		  <a href='<?php echo("$enlbase_sm=convenios_ci_cambiar_estado_legal&estado_legal=Firmado"); ?>' class="boton" id='sgu_fancybox_medium'>Firmado</a>
		  <a href='<?php echo("$enlbase_sm=convenios_ci_cambiar_estado_legal&estado_legal=En+Notaria"); ?>' class="boton" id='sgu_fancybox_medium'>En Notaría</a>
		  <a href='<?php echo("$enlbase_sm=convenios_ci_cambiar_estado_legal&estado_legal=Notariado"); ?>' class="boton" id='sgu_fancybox_medium'>Notariado</a>
        </td> -->
      </tr>
    </table>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="5">
      Mostrando <b><?php echo($tot_reg); ?></b> convenios(s) en total, en página(s) de
      <select class='filtro' name="cant_reg" onChange="submitform();">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas
    </td>
    <td class="texto" align="right" colspan="6">
      <?php echo($HTML_paginador); ?>
      <?php echo($boton_tabla_completa); ?>
      <?php echo($boton_tabla_completa_dicom); ?>
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Nº<br>Estado</td>
    <td class='tituloTabla'>Año<br>Emisión</td>    
    <td class='tituloTabla'>Alumno</td>
    <td class='tituloTabla'>Carrera</td>
    <td class='tituloTabla'>Valores<br>Nominales</td>
    <td class='tituloTabla'>Desc.</td>
    <td class='tituloTabla'>Monto<br>a Pagar</td>
    <td class='tituloTabla'>Forma de<br>Pago (UF)</td>
    <td class='tituloTabla'>Monto Pagado<br>[Saldo Total]</td>
    <td class='tituloTabla'>Monto<br>Moroso</td> 
    <td class='tituloTabla'>Fecha y<br>Emisor</td>
  </tr>
<?php
	$HTML = "";
	//var_dump($SQL_convenios_ci);
	if (count($convenios_ci) > 0) {
		for ($x=0;$x<count($convenios_ci);$x++) {
			extract($convenios_ci[$x]);
			
			$tipo = trim($tipo);
			
			$al_apellidos = "<div>$al_apellidos</div>";
			$al_nombres   = "<div>$al_nombres</div>";
						
			//$enl = "contrato.php?id_contrato=$id&tipo=$fmt_contrato";
			$enl = "$enlbase=ver_convenio_ci&id_convenio_ci=$id#cobros";
			//$enlace = "a class='enlitem' href='$enl'";
			
			$porc_beca_arancel    = ($monto_beca_arancel / $monto_arancel) * 100;
			$porc_cred_interno    = ($arancel_cred_interno / $monto_arancel) * 100;
			$arancelEfectivo = $monto_arancel - $monto_beca_arancel - $arancel_cred_interno;

			$cond = "";
			if ($monto_condonacion > 0) {
				$monto_condonacion *= -1;
				$arancel_cobrable   = $arancelEfectivo + $monto_condonacion;
				$monto_condonacion  = money_format("%7#7.0n",$monto_condonacion);
				$arancel_cobrable   = money_format("%7#7.0n",$arancel_cobrable);
				$cond               = "<div><small>Cond: $monto_condonacion</small></div>";
			}
			
			$estado = "<br><div class='".str_replace(" ","",$estado)."'><b style='font-variant: small-caps'>$estado</b></div>";
			if ($nulo == "t") { $estado = "<b style='color: #FF0000'>N U L O</b>$estado"; }
			
			$monto_final_uf = $monto_liqci_uf + $monto_adicional_uf - $descuento_inicial_uf;
			$monto_final = round($monto_final_uf * $valor_uf,0);

			$monto_liqci          = number_format($monto_liqci,0,',','.');
			$monto_liqci_uf       = number_format($monto_liqci_uf,2,',','.');
			$monto_adicional_uf   = number_format($monto_adicional_uf,2,',','.');
			//$monto_matricula      = money_format("%(#7.0n",$monto_mat);
			$descuento_inicial_uf = number_format($descuento_inicial_uf,2,',','.');
			$monto_final_uf       = number_format($monto_final_uf,2,',','.');
			
			$monto_pagado         = money_format("%(#7.0n",$monto_pagado);
			$monto_saldot         = money_format("%(#7.0n",$monto_saldot);
			
			if ($monto_moroso > 0) { 
				$monto_moroso   = "<span style='color: #ff0000'>".money_format("%(#7.0n",$monto_moroso)."</span>";
				$cuotas_morosas = "<span style='color: #ff0000'>($cuotas_morosas)</span>";
				$estado_financiero = "<span class='no'>MOROSO</span>";
			} else {
				$estado_financiero = "<span class='si'>Al día</span>";
			}
			
			$monto_final        = money_format("%=*(!7#7.0n",$monto_final);
			
			$liqci_efectivo     = money_format("%=*(!4#4.2n",$liqci_efectivo/$valor_uf);
			$liqci_cheque       = money_format("%=*(!4#4.2n",$liqci_cheque/$valor_uf);
			$liqci_pagare       = money_format("%=*(!4#4.2n",$liqci_pagare/$valor_uf);
			$liqci_tarj_credito = money_format("%=*(!4#4.2n",$liqci_tarj_credito/$valor_uf);
			$liqci_tarj_debito  = money_format("%=*(!4#4.2n",$liqci_tarj_debito/$valor_uf);
					
			list($forma_pago_nombre,$forma_pago_cuotas,$forma_pago_monto) = explode(",",str_replace(array("{","}"),"",$forma_pago));			
			$forma_pago_monto     = number_format($forma_pago_monto,0,',','.');
			if ($forma_pago_cuotas > 0) { $forma_pago_cuotas = "($forma_pago_cuotas)"; } else { $forma_pago_cuotas = ""; }
			
			$id = "<a href='$enl' class='enlaces'>$id</a>";
			
			$aval = "";
			if ($mostrar_aval == "on") {
				$aval = "<div><small>  <b>Aval:</b> $nombre_rf $rf_parentezco</small></div>";
			}
			
			if ($mat_pagada > 0) { $color_mat = "si"; } else { $color_mat = "no"; }			
			$monto_matricula = "<span class='$color_mat'>$monto_matricula</span>";
			
			$background = "";
			if ($nulo == "t") {
				$background = "bgcolor='#FF8E8E'";
				$estado_financiero = $monto_moroso = $cuotas_morosas = $monto_saldot = "";
			}
			
			if ($emisor <> "") { $emisor = "($emisor)"; }
			
			if (!empty($comentarios)) {
				$comentarios = str_replace("###","blockquote",wordwrap(nl2br($comentarios),90)); 
				$id = "<div title='header=[Observaciones] fade=[on] body=[$comentarios]' style='background: #BFE4BF; border-radius: 25px; padding: 0px 2px 0px 2px'>$id</div>";
			}
			
			$HTML .= "  <tr class='filaTabla' $background onClick=\"window.location='$enl';\">\n"
			      . "    <td class='textoTabla' align='center'>"
			      . "      <div>$id</div>"
			      . "      <div>$estado</div>"
			      . "      <div><small>$condonacion</small></div>"
			      . "      <!-- <div><small>$estado_financiero</small></div> -->"
			      . "    </td>\n"
			      . "    <td class='textoTabla' align='center'>$periodo<br><small>$tipo</small></td>\n"
			      . "    <td class='textoTabla'><div>$rut</div> $al_apellidos $al_nombres $aval</td>\n"
			      . "    <td class='textoTabla'>$carrera-$jornada</td>\n"
			      . "    <td class='textoTabla' align='right'><small>"
			      . "      <div align='left'>Convenio:</div> UF $monto_liqci_uf"
			      . "      <div align='left'>Adicional:</div> UF $monto_adicional_uf"
			      . "    </small></td>\n"
			      . "    <td class='textoTabla' align='right'>"
			      . "      <div style='background: rgba(229,229,229,0.75)'>UF $descuento_inicial_uf</div>"
			      . "    </td>\n"
			      . "    <td class='textoTabla' align='right'>"
			      . "      <div>UF $monto_final_uf<br><small>Equiv. apróx.:<br><a title='A la fecha del convenio, en pesos'><big>≈</big>$$monto_final</a></small></div>"
			      . "      $cond"
			      . "    </td>\n"
			      . "    <td class='textoTabla'><small style='font-family: ubuntu mono,mono'>"
			      . "      <div>&nbsp;EF: $liqci_efectivo</div>" 
			      . "      <div>&nbsp;CH: $liqci_cheque ($arancel_cant_cheques)</div>" 
			      . "      <div>Pag: $liqci_pagare ($liqci_cuotas_pagare)</div>"
			      . "      <div>&nbsp;TC: $liqci_tarj_credito ($liqci_cant_tarj_credito)</div>"			                 
			      . "      <div>&nbsp;TD: $liqci_tarj_debito</div>"			                 
			      . "    </small></td>\n"
			      . "    <td class='textoTabla' align='right'><div>$monto_pagado&nbsp;</div><div>[$monto_saldot]</div></td>\n"
			      . "    <td class='textoTabla' align='right'><div>$monto_moroso</div><div>$cuotas_morosas</div></td>\n"
			      . "    <td class='textoTabla'><div>$fecha</div><div align='center'>$emisor</div></td>\n"
			      . "  </tr>\n";
		}
	} else {
		$HTML = "  <tr>"
		      . "    <td class='textoTabla' colspan='11'><br><br>"
		      . "      <center>*** No hay registros para los criterios de búsqueda/selección ***</center><br><br>"
		      . "    </td>\n"
		      . "  </tr>";
	}
	echo($HTML);
?>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 1000,
		'height'			: 400,
		'afterClose'		: function () { location.reload(false); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 500,
		'height'			: 350,
		'maxHeight'			: 350,
		'afterClose'		: function () { location.reload(false); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_medium").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 800,
		'height'			: 700,
		'maxHeight'			: 700,
		'afterClose'		: function () { location.reload(false); },
		'type'				: 'iframe'
	});
});
</script>
<!-- Fin: <?php echo($modulo); ?> -->
