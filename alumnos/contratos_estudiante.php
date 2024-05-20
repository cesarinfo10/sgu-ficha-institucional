<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");
//include("validar_modulo.php");

$id_alumno = $_SESSION['id'];
$rut       = $_REQUEST['rut'];
$token     = $_REQUEST['token'];

$alumno       = consulta_sql("SELECT trim(rut) AS rut,nombre_usuario||'@alumni.umc.cl' as email_gsuite FROM alumnos WHERE id=$id_alumno");
$rut          = $alumno[0]['rut'];
$email_gsuite = $alumno[0]['email_gsuite'];

if ($token == "") {
	$token_hoy = consulta_sql("SELECT 1 FROM finanzas.alumnos_token WHERE id_alumno=$id_alumno AND fecha::date=now()::date");
	if (count($token_hoy) == 1) {
		$msje = "Ya te hemos enviado un código hoy, que aún está vigente.\\n\\n"
		      . "Si no lo has recibido, es posible que el mensaje haya entrado en la carpeta de SPAM.\\n\\n"
		      . "En el caso que no logres encontrar el mensaje que contiene el código, "
		      . "por favor informa este problema a soporte@corp.umc.cl";
		echo(msje_js($msje));
	} else {
		enviar_token($id_alumno);
	}
} else {
	$valida_token = consulta_sql("SELECT 1 FROM finanzas.alumnos_token WHERE id_alumno=$id_alumno AND token='$token' AND date_part('hours',now()-fecha)<=24");
	$token_valido = false;
	if (count($valida_token) == 1) { $token_valido = true; }
}

$condicion = $cond_pagos = "WHERE true ";

$condicion .= "AND '$rut' IN (pap.rut,a.rut) AND c.estado IS NOT NULL";

$SQL_contratos = "SELECT c.id,to_char(c.fecha,'DD-MM-YYYY') AS fecha,c.tipo,c.estado,c.morosidad_manual,
                         CASE WHEN c.tipo='Semestral' THEN text(c.semestre)||'-'||text(c.ano) ELSE text(c.ano) END AS periodo,c.ano,
                         trim(coalesce(a.rut,pap.rut)) AS rut,coalesce(a.rut,pap.rut) AS rut_al,c.id_alumno,
                         upper(coalesce(a.apellidos,pap.apellidos)) AS al_apellidos,initcap(coalesce(a.nombres,pap.nombres)) AS al_nombres,av.rf_parentezco,
                         av.rf_rut,upper(av.rf_apellidos)||' '||initcap(av.rf_nombres) AS nombre_rf,av.rf_nombre_empresa,av.rf_telefono_empresa,
                         CASE c.estado 
                              WHEN 'E' THEN 'Emitido' 
                              WHEN 'F' THEN 'Firmado' 
                              WHEN 'R' THEN 'Retirado' 
                              WHEN 'S' THEN 'Suspendido' 
                              WHEN 'A' THEN 'Abandonado' 
                              ELSE 'Nulo'
                         END AS estado,c.monto_matricula,vc.monto_mat::int4,c.monto_matricula-vc.monto_mat::int4 AS beca_mat,c.monto_arancel,
                         CASE WHEN c.id_convenio IS NOT NULL THEN 'Procedencia' ELSE b.alias END AS nombre_beca,
                         CASE WHEN c.id_convenio IS NOT NULL        THEN round(c.monto_arancel*0.2,0)
                              WHEN c.porc_beca_arancel IS NOT NULL  THEN round(c.monto_arancel*(c.porc_beca_arancel/100),0)
                              ELSE c.monto_beca_arancel
                         END AS monto_beca_arancel_calc,
                         CASE WHEN c.monto_arancel > 0 THEN (monto_beca_arancel_calc::float/c.monto_arancel::float) ELSE 0 END AS porc_beca_arancel,
                         c.arancel_cred_interno,
                         trim(car.alias) AS carrera,c.jornada,
                         trim(c.financiamiento) AS financiamiento,
                         CASE WHEN c.id_alumno IS NOT NULL THEN 'A' 
                              WHEN c.id_pap IS NOT NULL THEN 'N'
                         END AS tipo_alumno, c.arancel_efectivo,c.arancel_cheque,
                         coalesce(c.arancel_cant_cheques,0) AS arancel_cant_cheques,
                         c.arancel_pagare_coleg,coalesce(c.arancel_cuotas_pagare_coleg,0) AS arancel_cuotas_pagare_coleg,
                         c.arancel_tarjeta_credito,coalesce(c.arancel_cant_tarj_credito,0) AS arancel_cant_tarj_credito,
                         CASE WHEN c.monto_condonacion>0 THEN '(C)' END AS condonacion,c.monto_condonacion,to_char(c.fecha_condonacion,'DD-MM-YYYY') AS fecha_condonacion,
                         vc.monto_pagado,vc.monto_saldot,vc.monto_moroso,vc.cuotas_morosas,vc.monto_repactado,vc.cuotas_repactadas,vc.mat_pagada,
                         u.nombre_usuario AS emisor,c.comentarios                         
                   FROM finanzas.contratos AS c
                   LEFT JOIN vista_contratos AS vc USING (id)
                   LEFT JOIN alumnos         AS a   ON a.id=c.id_alumno
                   LEFT JOIN pap                    ON pap.id=c.id_pap
				   LEFT JOIN avales          AS av  ON av.id=c.id_aval
                   LEFT JOIN carreras        AS car ON car.id=c.id_carrera      
                   LEFT JOIN becas           AS b   ON b.id=c.id_beca_arancel                             
                   LEFT JOIN usuarios        AS u   ON u.id=c.id_emisor                             
                   $condicion
                   ORDER BY c.fecha DESC,al_apellidos,al_nombres";
//echo("<!-- $SQL_contratos -->");
$contratos     = consulta_sql($SQL_contratos);

$HTML = "";
if (count($contratos) > 0 && $token_valido) {
	for ($x=0;$x<count($contratos);$x++) {
		extract($contratos[$x]);
		
		$tipo = trim($tipo);
		
		$al_apellidos = "<div>$al_apellidos</div>";
		$al_nombres   = "<div>$al_nombres</div>";
		
		if ($tipo == "Anual" || $tipo == "Semestral") {
			if ($tipo_alumno == "N") { $fmt_contrato = "al_nuevo"; } elseif ($tipo_alumno == "A") { $fmt_contrato = "al_antiguo"; }
		}
		if ($tipo == "Estival") { $fmt_contrato = "estival"; }
		if ($tipo == "Modular") {
			if ($tipo_alumno == "N") { $fmt_contrato = "al_nuevo_modular"; } elseif ($tipo_alumno == "A") { $fmt_contrato = "al_antiguo_modular"; }
		}
		
		//$enl = "contrato.php?id_contrato=$id&tipo=$fmt_contrato";
		//$enl = "$enlbase_sm=contratos_estudiante_ver&id_contrato=$id&token=$token#cobros";
		$enl = "$enlbase_sm=contratos_estudiante_ver&id_contrato=$id&token=$token#cobros";
		//$enlace = "a class='enlitem' href='$enl'";

		if ($rf_parentezco == "Ninguno") { $rf_parentezco = "El mismo Alumno"; $nombre_rf = ""; }
		$rf_parentezco = "($rf_parentezco)";
		
		$porc_beca_arancel    = ($monto_beca_arancel_calc / $monto_arancel) * 100;
		$porc_cred_interno    = ($arancel_cred_interno / $monto_arancel) * 100;
		$arancelEfectivo = $monto_arancel - $monto_beca_arancel_calc - $arancel_cred_interno;

		$cond = "";
		if ($monto_condonacion > 0) {
			$monto_condonacion *= -1;
			$arancel_cobrable   = $arancelEfectivo + $monto_condonacion;
			$monto_condonacion  = money_format("%7#7.0n",$monto_condonacion);
			$arancel_cobrable   = money_format("%7#7.0n",$arancel_cobrable);
			$cond               = "<div><small>Cond: $monto_condonacion</small></div>"
								. "<div><small>Arancel Cobrable:</small></div>"
								. "<div><b>$arancel_cobrable</b></div>";
		}

		$monto_arancel        = number_format($monto_arancel,0,',','.');
		$monto_matricula      = money_format("%(#7.0n",$monto_mat);
		$monto_beca_arancel   = number_format($monto_beca_arancel_calc,0,',','.');
		$porc_beca_arancel    = number_format($porc_beca_arancel,0,',','.');
		$arancel_cred_interno = number_format($arancel_cred_interno,0,',','.');
		$porc_cred_interno    = number_format($porc_cred_interno,0,',','.');
		$arancelEfectivo      = number_format($arancelEfectivo,0,',','.');
		
		$monto_pagado         = money_format("%(#7.0n",$monto_pagado);
		$monto_saldot         = money_format("%(#7.0n",$monto_saldot);
		
		if ($monto_moroso > 0) { 
			$monto_moroso   = "<span style='color: #ff0000'>".money_format("%(#7.0n",$monto_moroso)."</span>";
			$cuotas_morosas = "<span style='color: #ff0000'>($cuotas_morosas)</span>";
			$estado_financiero = "<span class='no'>MOROSO</span>";
		} else {
			$estado_financiero = "<span class='si'>Al día</span>";
		}
		
		if ($morosidad_manual == 't') { $morosidad_manual = "<span class='no'>M.M.</span>"; } else { $morosidad_manual = ""; }
		
		$arancel_efectivo        = money_format("%=*(!7#7.0n",$arancel_efectivo);
		$arancel_cheque          = money_format("%=*(!7#7.0n",$arancel_cheque);
		$arancel_pagare_coleg    = money_format("%=*(!7#7.0n",$arancel_pagare_coleg);
		$arancel_tarjeta_credito = money_format("%=*(!7#7.0n",$arancel_tarjeta_credito);
				
		list($forma_pago_nombre,$forma_pago_cuotas,$forma_pago_monto) = explode(",",str_replace(array("{","}"),"",$forma_pago));			
		$forma_pago_monto     = number_format($forma_pago_monto,0,',','.');
		if ($forma_pago_cuotas > 0) { $forma_pago_cuotas = "($forma_pago_cuotas)"; } else { $forma_pago_cuotas = ""; }
		
		$id = "<a href='$enl' id='sgu_fancybox' class='enlaces'>$id</a>";
		$monto_saldot .= "<div style='margin-top: 5px'><a href='$enl' id='sgu_fancybox' style='font-variant: small-caps' class='boton'>Ver detalle</a></div>";
		
		$aval = "";
		if ($mostrar_aval == "on") {
			$aval = "<div><small>  <b>Aval:</b> $nombre_rf $rf_parentezco</small></div>";
		}
		
		if ($mat_pagada > 0) { $color_mat = "si"; } else { $color_mat = "no"; }			
		$monto_matricula = "<span class='$color_mat'>$monto_matricula</span>";
		
		$background = "";
		if ($estado == "Nulo") {
			$background = "bgcolor='#FF8E8E'";
			$estado_financiero = $monto_moroso = $cuotas_morosas = $monto_saldot = "";
		}
		
		if ($emisor <> "") { $emisor = "($emisor)"; }
		
		$HTML .= "  <tr class='filaTabla' $background>\n"
			  . "    <td class='textoTabla' align='center'>"
			  . "      <div>$id</div>"
			  . "      <div>$estado</div>"
			  . "      <div><small>$condonacion</small></div>"
			  . "      <div><small>$estado_financiero $morosidad_manual</small></div>"
			  . "    </td>\n"
			  . "    <td class='textoTabla' align='center'>$periodo<br><small>$tipo</small></td>\n"
			  . "    <td class='textoTabla' align='center'>$carrera-$jornada</td>\n"
			  . "    <td class='textoTabla' align='right'><small>"
			  . "      <div>Matríc: $monto_matricula</div>"
			  . "      <div align='left'>Arancel $financiamiento:</div>"
			  . "      <div><b>$$monto_arancel</b></div>"
			  . "    </small></td>\n"
			  . "    <td class='textoTabla' align='right'><small>"
			  . "      <div style='background: rgba(229,229,229,0.75)'>Beca: $$monto_beca_arancel</div>"
			  . "      <div style='background: rgba(229,229,229,0.75)'><small>$nombre_beca ($porc_beca_arancel%)</small></div>"
			  . "      <div>C. I.: $$arancel_cred_interno</div>"
			  . "      <div><small>($porc_cred_interno%)</small></div>"
			  . "    </small></td>\n"
			  . "    <td class='textoTabla' align='right' style='vertical-align: middle'>"
			  . "      <div>$$arancelEfectivo</div>"
			  . "      $cond"
			  . "    </td>\n"
			  . "    <td class='textoTabla'><small style='font-family: ubuntu mono,mono'>"
			  . "      <div>&nbsp;EF: $arancel_efectivo</div>" 
			  . "      <div>&nbsp;CH: $arancel_cheque ($arancel_cant_cheques)</div>" 
			  . "      <div>Pag: $arancel_pagare_coleg ($arancel_cuotas_pagare_coleg)</div>"
			  . "      <div>&nbsp;TC: $arancel_tarjeta_credito ($arancel_cant_tarj_credito)</div>"			                 
			  . "    </small></td>\n"
			  . "    <td class='textoTabla' align='right'><div>$monto_pagado</div></td>\n"
			  . "    <td class='textoTabla' align='right'><div>$monto_saldot</div></td>\n"
			  . "    <td class='textoTabla' align='right'><div>$monto_moroso</div><div>$cuotas_morosas</div></td>\n"
			  . "    <td class='textoTabla'><div>$fecha</div></td>\n"
			  . "  </tr>\n";
	}
} else {
	$HTML = "  <tr>"
		  . "    <td class='textoTabla' colspan='11'><br><br>"
		  . "      <center>*** No hay contratos registrados ***</center><br><br>"
		  . "    </td>\n"
		  . "  </tr>";
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  Contratos de Servicios Educacionales
</div>
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="token"  value="<?php echo($token); ?>">
<input type="hidden" name="rut"    value="<?php echo($rut); ?>">

<?php if ($token == "") { ?>

<div class="texto" style="margin-top: 5px">
  <blockquote>
    Para revisar tu información financiera, deberás ingresar el código que hemos enviado a tu email institucional <?php echo($email_gsuite); ?>:<br>
    <input type="number" name="token" class="boton">
    <input type="submit" name="enviar" value="Entrar"><br><br>
    NOTA: Si no tienes acceso a tu cuenta <?php echo($email_gsuite); ?>, por favor informa este problema a soporte@corp.umc.cl
  </blockquote>
</div>

<?php } elseif ($token_valido) { ?>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr class='filaTituloTabla'>
    <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Nº<br>Estado</td>
    <td class='tituloTabla'>Periodo<br>y Tipo</td>    
    <td class='tituloTabla'>Carrera</td>
    <td class='tituloTabla'>Valores<br>Nominales</td>
    <td class='tituloTabla'>Beneficios</td>
    <td class='tituloTabla'>Arancel<br>Efectivo</td>
    <td class='tituloTabla'>Forma<br>de Pago</td>
    <td class='tituloTabla'>Monto<br>Pagado</td>
    <td class='tituloTabla'>Saldo<br>Total</td>
    <td class='tituloTabla'>Monto<br>Moroso</td>
    <td class='tituloTabla'>Fecha</td>
  </tr>
  <?php echo($HTML); ?>
</table>

<?php } ?>

</form>
<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 1200,
		'height'			: 600,
		'afterClose'		: function () {  },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 700,
		'height'			: 600,
		'maxHeight'			: 600,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});
</script>

<!-- Fin: <?php echo($modulo); ?> -->


<?php 

function enviar_token($id_alumno) {
	
	consulta_dml("INSERT INTO finanzas.alumnos_token (id_alumno) VALUES ($id_alumno)");
	$token = consulta_sql("SELECT token FROM finanzas.alumnos_token WHERE id_alumno=$id_alumno AND fecha::date=now()::date");
	$token = $token[0]['token'];

	$SQL_servidor_email = "CASE servidor_nombre_usuario WHEN  'al.umcervantes.cl' THEN 'alumni.umc.cl' WHEN  'postgrado.umcervantes.cl' THEN 'postgrado.umc.cl' END";
	
	$alumno = consulta_sql("SELECT nombre_usuario||'@'||($SQL_servidor_email) AS email_gsuite FROM alumnos WHERE id=$id_alumno");
	$alumno2 = consulta_sql("SELECT nombre_usuario||'@'||servidor_nombre_usuario AS email FROM alumnos WHERE id=$id_alumno");
	if (count($alumno) == 1) {
		$email_gsuite = $alumno[0]['email_gsuite'];

		$CR = "\r\n";

		$cabeceras = "From: UMC - SGU <no-responder@umcervantes.cl>" . $CR
		           . "Content-Type: text/html;charset=utf-8" . $CR;
		$asunto    = "Revisar información financiera";
		$msje      = "Para revisar tu información financiera debes ingresar el siguiente código: $token";
		mail($email_gsuite,$asunto,$msje,$cabeceras);
	}
	if (count($alumno2) == 1) {
		$email = $alumno2[0]['email'];

		$CR = "\r\n";

		$cabeceras = "From: UMC - SGU <no-responder@umcervantes.cl>" . $CR
		           . "Content-Type: text/html;charset=utf-8" . $CR;
		$asunto    = "Revisar información financiera";
		$msje      = "Para revisar tu información financiera debes ingresar el siguiente código: $token";
		mail($email,$asunto,$msje,$cabeceras);
	}
}

?>


<?php

/*

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) {	$cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if (empty($reg_inicio)) { $reg_inicio = 0; }

$texto_buscar        = $_REQUEST['texto_buscar'];
$buscar              = $_REQUEST['buscar'];
$buscar_fecha        = $_REQUEST['buscar_fecha'];
$semestre            = $_REQUEST['semestre'];
$ano                 = $_REQUEST['ano'];
$estado              = $_REQUEST['estado'];
$tipo                = $_REQUEST['tipo'];
$tipo_alumno         = $_REQUEST['tipo_alumno'];
$id_carrera          = $_REQUEST['id_carrera'];
$jornada             = $_REQUEST['jornada'];
$semestre_cohorte    = $_REQUEST['semestre_cohorte'];
$cohorte             = $_REQUEST['cohorte'];
$beca                = $_REQUEST['beca'];
$condonado           = $_REQUEST['condonado'];
$repactado           = $_REQUEST['repactado'];
$regimen             = $_REQUEST['regimen'];
$mat_pagada          = $_REQUEST['mat_pagada'];
$cant_cuotas_morosas = $_REQUEST['cant_cuotas_morosas'];
$forma_pago          = $_REQUEST['forma_pago'];
$emisor              = $_REQUEST['emisor'];
$fec_ini_pago        = $_REQUEST['fec_ini_pago'];
$fec_fin_pago        = $_REQUEST['fec_fin_pago'];

$mostrar_aval     = $_REQUEST['mostrar_aval'];

if (empty($ano))      { $ano = $ANO; }
if (empty($semestre)) { $semestre = null; }
if ($estado == "")    { $estado = '1'; }
if (empty($_REQUEST['cohorte'])) { $cohorte = 0; }
if (empty($_REQUEST['semestre_cohorte'])) { $semestre_cohorte = 0; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }
if ($_REQUEST['cant_cuotas_morosas'] == "") { $cant_cuotas_morosas = -2; }

if ($buscar == "Buscar" && $texto_buscar <> "") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar = explode(" ",$texto_buscar_regexp);
	$condicion = "WHERE ";
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= "(lower(pap.nombres||' '||pap.apellidos) ~* '$cadena_buscada' OR "
		           .  " pap.rut ~* '$cadena_buscada' OR "
		           .  "lower(a.nombres||' '||a.apellidos) ~* '$cadena_buscada' OR "
		           .  " a.rut ~* '$cadena_buscada' OR "
		           .  "lower(av.rf_nombres||' '||av.rf_apellidos) ~* '$cadena_buscada' OR "
		           .  " av.rf_rut ~* '$cadena_buscada' OR "
		           .  " text(c.id) ~* '$cadena_buscada' "
		           .  ") AND ";
	}
	$condicion=substr($condicion,0,strlen($condicion)-4);
	$ano = $estado = $tipo = $id_carrera = $jornada = null;	
} else {

	if ($id_carrera <> "") { $condicion .= "AND c.id_carrera=$id_carrera "; }

	if ($jornada <> "") { $condicion .= "AND c.jornada='$jornada' "; }
	
	if ($cohorte > 0) {
		$condicion .= "AND (a.cohorte = '$cohorte') ";
	}

	if ($semestre_cohorte > 0) {
		$condicion .= "AND (a.semestre_cohorte = $semestre_cohorte) ";
	}

	if ($ano > 0) { $condicion .= "AND c.ano=$ano "; }
	
	if ($tipo == "Anual" || $tipo == "Modular") { $semestre = null; }
	
	if (!is_null($semestre)) { $condicion .= "AND c.semestre=$semestre "; }

	if ($estado <> "") {
		if ($estado == "N")  { $condicion .= "AND c.estado IS NULL "; } 
		elseif ($estado == "1") { $condicion .= "AND c.estado IS NOT NULL "; }
		elseif ($estado == "D") { $condicion .= "AND c.estado IN ('S','R','A') "; }
		elseif ($estado != "0") { $condicion .= "AND c.estado='$estado' "; }
	}
	
	if ($tipo <> "" && $tipo <> "0") { $condicion .= "AND c.tipo='$tipo' "; }
	
	if ($tipo_alumno == "N") { $condicion .= "AND c.id_pap IS NOT NULL "; }
	if ($tipo_alumno == "A") { $condicion .= "AND c.id_alumno IS NOT NULL "; }
	
	if ($beca == "100")  { $condicion .= "AND c.id_convenio IS NOT NULL "; }
	elseif ($beca <> "") { $condicion .= "AND c.id_beca_arancel = $beca "; }
	
	if ($condonado == "t") { $condicion .= "AND c.monto_condonacion IS NOT NULL "; }
	if ($condonado == "f") { $condicion .= "AND c.monto_condonacion IS NULL "; }
	
	if ($repactado == "t") { $condicion .= "AND vc.monto_repactado IS NOT NULL "; }
	
	if ($regimen <> "" && $regimen <> "t") { $condicion .= "AND (car.regimen = '$regimen') "; }
	
	if ($mat_pagada == "t") { $condicion .= "AND (mat_pagada > 0 ) "; } 
	elseif ($mat_pagada == "f") { $condicion .= "AND (mat_pagada = 0 AND monto_mat>0 AND (coalesce(0,c.porc_beca_mat)<100 OR c.monto_matricula<>coalesce(0,c.monto_beca_mat))) "; } 
	
	if ($cant_cuotas_morosas >= 0) { $condicion .= "AND vc.cuotas_morosas = $cant_cuotas_morosas "; }
	if ($cant_cuotas_morosas == -1) { $condicion .= "AND vc.cuotas_morosas >= 1 "; }

	
	if ($emisor > 0) { $condicion .= "AND c.id_emisor=$emisor "; }
	
	if ($forma_pago <> "") { $condicion .= "AND $forma_pago>0 "; }
	
	$SQL_pagos_aranceles = "SELECT 0";	
	if (!empty($fec_ini_pago) && !empty($fec_fin_pago)) { 
		$cond_pagos .= " AND p.fecha::date BETWEEN '$fec_ini_pago'::date AND '$fec_fin_pago'::date ";
		$SQL_pagos_aranceles = "(SELECT sum(monto_pagado) 
                                 FROM finanzas.cobros AS c 
                                 LEFT JOIN finanzas.pagos_detalle AS pd ON pd.id_cobro=c.id
                                 LEFT JOIN finanzas.pagos         AS p  ON p.id=pd.id_pago 
                                 $cond_pagos AND id_glosa>1 AND id_contrato=vc.id
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


$SQL_ult_pago = "SELECT max(p.fecha::date) FROM finanzas.pagos_detalle pd LEFT JOIN finanzas.pagos p ON p.id=pd.id_pago LEFT JOIN finanzas.cobros cob ON cob.id=pd.id_cobro WHERE cob.id_contrato=c.id";
$SQL_monto_saldot2   = "SELECT sum(monto) FROM finanzas.cobros WHERE id_contrato=c.id AND id_glosa>1 AND NOT pagado AND NOT abonado";

$SQL_contratos = "SELECT c.id,to_char(c.fecha,'DD-MM-YYYY') AS fecha,c.tipo,c.estado,to_char(c.estado_fecha,'DD-MM-YYYY') AS estado_fecha,
                         c.morosidad_manual,
                         CASE WHEN c.tipo='Semestral' THEN text(c.semestre)||'-'||text(c.ano) ELSE text(c.ano) END AS periodo,c.ano,
                         trim(coalesce(a.rut,pap.rut)) AS rut,coalesce(a.rut,pap.rut) AS rut_al,c.id_alumno,
                         upper(coalesce(a.apellidos,pap.apellidos)) AS al_apellidos,initcap(coalesce(a.nombres,pap.nombres)) AS al_nombres,av.rf_parentezco,
                         av.rf_rut,upper(av.rf_apellidos)||' '||initcap(av.rf_nombres) AS nombre_rf,av.rf_nombre_empresa,av.rf_telefono_empresa,
                         CASE c.estado 
                              WHEN 'E' THEN 'Emitido' 
                              WHEN 'F' THEN 'Firmado' 
                              WHEN 'R' THEN 'Retirado' 
                              WHEN 'S' THEN 'Suspendido' 
                              WHEN 'A' THEN 'Abandonado' 
                              ELSE 'Nulo'
                         END AS estado,c.monto_matricula,vc.monto_mat::int4,c.monto_matricula-vc.monto_mat::int4 AS beca_mat,c.monto_arancel,
                         CASE WHEN c.id_convenio IS NOT NULL THEN 'Procedencia' ELSE b.alias END AS nombre_beca,
                         CASE WHEN c.id_convenio IS NOT NULL        THEN round(c.monto_arancel*0.2,0)
                              WHEN c.porc_beca_arancel IS NOT NULL  THEN round(c.monto_arancel*(c.porc_beca_arancel/100),0)
                              ELSE c.monto_beca_arancel
                         END AS monto_beca_arancel_calc,
                         CASE WHEN c.monto_arancel > 0 THEN round(vc.monto_beca_arancel_calc*(1-(coalesce(c.monto_condonacion,0)::float/c.monto_arancel::float))) ELSE 0 END AS arancel_beca_contable,
                         CASE WHEN c.monto_arancel > 0 THEN (monto_beca_arancel_calc::float/c.monto_arancel::float) ELSE 0 END AS porc_beca_arancel,
                         c.arancel_cred_interno,
                         CASE WHEN c.monto_arancel > 0 THEN round(c.arancel_cred_interno*(1-(coalesce(c.monto_condonacion,0)::float/c.monto_arancel::float))) ELSE 0 END AS arancel_cred_int_contable,
                         trim(car.alias) AS carrera,c.jornada,
                         trim(c.financiamiento) AS financiamiento,
                         CASE WHEN c.id_alumno IS NOT NULL THEN 'A' 
                              WHEN c.id_pap IS NOT NULL THEN 'N'
                         END AS tipo_alumno, c.arancel_efectivo,c.arancel_cheque,
                         coalesce(c.arancel_cant_cheques,0) AS arancel_cant_cheques,
                         c.arancel_pagare_coleg,coalesce(c.arancel_cuotas_pagare_coleg,0) AS arancel_cuotas_pagare_coleg,
                         c.arancel_tarjeta_credito,coalesce(c.arancel_cant_tarj_credito,0) AS arancel_cant_tarj_credito,
                         CASE WHEN c.monto_condonacion>0 THEN '(C)' END AS condonacion,c.monto_condonacion,to_char(c.fecha_condonacion,'DD-MM-YYYY') AS fecha_condonacion,
                         vc.monto_pagado,vc.monto_saldot,($SQL_monto_saldot2) AS monto_saldot_sin_abonos,vc.monto_moroso,($SQL_pagos_aranceles) AS pagos_rango_fechas,
                         vc.cuotas_morosas,($SQL_ult_pago) AS fecha_ult_pago,vc.monto_repactado,vc.cuotas_repactadas,vc.mat_pagada,
                         u.nombre_usuario AS emisor,c.comentarios,pc.id AS nro_pagare_coleg,
                         CASE WHEN c.id_alumno IS NOT NULL 
                              THEN substr(split_part(a.apellidos,' ',1),1,20)||' '||substr(split_part(a.apellidos,' ',2),1,20)||' '||substr(a.nombres,1,20) 
                              ELSE substr(split_part(pap.apellidos,' ',1),1,20)||' '||substr(split_part(pap.apellidos,' ',2),1,20)||' '||substr(pap.nombres,1,20) 
                         END AS nombre_al_dicom,coalesce(a.cohorte,pap.cohorte) AS cohorte,
                         coalesce(a.direccion,pap.direccion) AS direccion,coalesce(va.comuna,vpap.comuna) as comuna,coalesce(va.region,vpap.region) AS region,
                         coalesce(a.telefono,pap.telefono) AS telefono,coalesce(a.tel_movil,pap.tel_movil) AS tel_movil,coalesce(a.email,pap.email) as email,
                         coalesce(a.nombre_usuario,(SELECT nombre_usuario FROM alumnos WHERE id_pap=c.id_pap LIMIT 1))||'@alumni.umc.cl' AS email_gsuite,coalesce(pap.genero,a.genero) AS genero,
                         coalesce(a.carr_ies_pro,pap.carr_ies_pro) AS profesion,coalesce(va.ies,vpap.ies) as ies_anterior,coalesce(pap.fec_nac,a.fec_nac) as fec_nac
                   FROM finanzas.contratos AS c
                   LEFT JOIN vista_contratos AS vc USING (id)
                   LEFT JOIN alumnos         AS a   ON a.id=c.id_alumno
                   LEFT JOIN pap                    ON pap.id=c.id_pap
                   LEFT JOIN vista_avales    AS vav ON vav.id=c.id_aval
                   LEFT JOIN avales          AS av  ON av.id=c.id_aval
                   LEFT JOIN carreras        AS car ON car.id=c.id_carrera      
                   LEFT JOIN becas           AS b   ON b.id=c.id_beca_arancel                             
                   LEFT JOIN usuarios        AS u   ON u.id=c.id_emisor                             
                   LEFT JOIN finanzas.pagares_colegiatura AS pc ON pc.id_contrato=c.id
                   LEFT JOIN vista_alumnos                AS va   ON va.id=c.id_alumno
                   LEFT JOIN vista_pap                    AS vpap ON vpap.id=c.id_pap
                   $condicion
                   ORDER BY c.fecha DESC,al_apellidos,al_nombres ";
$SQL_tabla_completa = "COPY ($SQL_contratos) to stdout WITH CSV HEADER";

$SQL_contr_ctbles = "SELECT to_char(c.fecha,'DD-MM-YYYY') as fecha_emision,
                            (SELECT to_char(max(fecha_venc),'DD-MM-YYYY') FROM finanzas.cobros where id_contrato=c.id) as fecha_venc,
                            '' as cta_ctble,
                            coalesce(c.arancel_efectivo,0)+coalesce(c.arancel_pagare_coleg,0)+coalesce(c.arancel_cheque,0)+coalesce(c.arancel_tarjeta_credito,0) as monto,
                            coalesce(va.rut,vp.rut) as rut,coalesce(va.nombre,vp.nombre) as razon_social,
                            c.id as nro_contrato,pc.id as nro_pagare 
                     FROM finanzas.contratos AS c 
                     LEFT JOIN vista_contratos AS vc USING (id)
                     LEFT JOIN vista_alumnos AS va ON va.id=c.id_alumno 
                     LEFT JOIN vista_pap     AS vp ON vp.id=c.id_pap 
                     LEFT JOIN finanzas.pagares_colegiatura AS pc ON pc.id_contrato=c.id
                     LEFT JOIN carreras        AS car ON car.id=c.id_carrera
                     $condicion
                     ORDER BY c.fecha DESC ";
$SQL_tc_cont_ctbles = "COPY ($SQL_contr_ctbles) to stdout WITH CSV HEADER";
//var_dump($contratos);
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
 
$cohortes = $anos;

$SEMESTRES_COHORTES = array(array("id"=>1,"nombre"=>1),
                            array("id"=>2,"nombre"=>2));

$SEMESTRES = array(array("id"=>0,"nombre"=>0),
                   array("id"=>1,"nombre"=>1),
                   array("id"=>2,"nombre"=>2));

$_SESSION['enlace_volver'] = "$enlace_nav=$reg_inicio";

$becas = consulta_sql("SELECT id,nombre from becas ORDER BY nombre");
$beca_proc = array(array("id"=>100,"nombre"=>"Procedencia"));
$becas = array_merge($becas,$beca_proc);
$emisores = consulta_sql("SELECT id,nombre FROM vista_usuarios WHERE id IN (SELECT id_emisor FROM finanzas.contratos WHERE ano=$ano)");

$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();

$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);

$boton_tc_contr_ctbles = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=contr_ctbles_$id_sesion');\" class='boton'><small>TC Contr. Ctbles.</small></a>";
$nombre_arch = "sql-fulltables/contr_ctbles_$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tc_cont_ctbles);

$FORMAS_PAGO = array(array('id'=>"c.arancel_efectivo",       'nombre'=>"Efectivo"),
                     array('id'=>"c.arancel_cheque",         'nombre'=>"Cheque(s)"),
                     array('id'=>"c.arancel_pagare_coleg",   'nombre'=>"Pagaré Colegiatura"),
                     array('id'=>"c.arancel_tarjeta_credito",'nombre'=>"Tarjeta de Crédito"));

$REGIMENES = consulta_sql("SELECT * FROM regimenes");
 
 
*/

?>

<!--

  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="5">
      Mostrando <b><?php echo($tot_reg); ?></b> contrato(s) en total, en página(s) de
      <select class='filtro' name="cant_reg" onChange="submitform();">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas
    </td>
    <td class="texto" align="right" colspan="6">
      <?php echo($HTML_paginador); ?>
      <?php echo($boton_tc_contr_ctbles); ?>
      <?php echo($boton_tabla_completa); ?>
    </td>
  </tr>
  
-->
