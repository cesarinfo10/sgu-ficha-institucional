<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo_uid_no_cero.php");

$id_alumno          = $_REQUEST['id_alumno'];
$val_datos_contacto = $_REQUEST['val_datos_contacto'];

if ($val_datos_contacto <> "t") {
	echo(msje_js("ATENCIÖN: Primero debe verificar los datos de contacto del alumno antes de emitir una certificación"));
	echo(js("window.location='$enlbase_sm=editar_alumno_datos_contacto&id_alumno=$id_alumno&mod_ant=$modulo';"));
}

if ($_REQUEST['val_tit'] == "si") { echo(js("parent.jQuery.fancybox.close();")); }

if ($_REQUEST["fecha"] == "") { $_REQUEST["fecha"] = date("Y-m-d"); }
//if ($_REQUEST["ano_academico"] == "") { $_REQUEST["ano_academico"] = date("Y"); }
//if ($_REQUEST["ano_academico"] == "") { $_REQUEST["ano_academico"] = $ANO; }

//$SQL_matriculado = "SELECT 1 FROM matriculas WHERE ano IN ($ANO,$ANO_MATRICULA) AND semestre IN ($SEMESTRE,$SEMESTRE_MATRICULA) AND id_alumno=$id_alumno LIMIT 1";
$SQL_matriculado = "SELECT 1 FROM matriculas WHERE ano IN ($ANO_MATRICULA) AND semestre IN ($SEMESTRE_MATRICULA) AND id_alumno=$id_alumno LIMIT 1";
$SQL_mat_actual = "SELECT 1 FROM matriculas WHERE ano IN ($ANO) AND semestre IN ($SEMESTRE) AND id_alumno=$id_alumno LIMIT 1";
$SQL_mat_sgte_ano = "SELECT 1 FROM matriculas WHERE ano IN ($ANO_MATRICULA) AND semestre IN ($SEMESTRE_MATRICULA) AND id_alumno=$id_alumno LIMIT 1";

$SQL_alumno = "SELECT va.id,va.nombre,va.rut,trim(va.carrera) AS alias_carrera,va.estado,
                      CASE a.jornada WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada,a.carrera_actual,
                      c.nombre AS carrera,a.cohorte,c.regimen,a.cohorte_reinc,
                      to_char(a.fecha_egreso,'DD-MM-YYYY') AS fecha_egreso,
                      to_char(a.examen_grado_titulo_fecha,'DD-MM-YYYY') AS examen_grado_titulo_fecha,
                      to_char(a.fecha_titulacion,'DD-MM-YYYY') AS fecha_titulacion,
                      a.examen_grado_titulo_calif,c.nombre_titulo,c.nombre_grado,
                      aefp.examen_anual_1 AS al_examen_anual_1,aefp.examen_anual_2 AS al_examen_anual_2,
                      a.malla_actual AS al_malla_actual,a.estado AS id_estado,
                      CASE WHEN ($SQL_matriculado)=1 THEN 't' ELSE 'f' END AS matriculado,
                      CASE WHEN ($SQL_mat_actual)=1 THEN 't' ELSE 'f' END AS mat_actual,
                      CASE WHEN ($SQL_mat_sgte_ano)=1 THEN 't' ELSE 'f' END AS mat_sgte_ano
               FROM vista_alumnos AS va
               LEFT JOIN alumnos AS a USING (id)
               LEFT JOIN carreras AS c ON c.id=a.carrera_actual
               LEFT JOIN alumnos_examen_final_postgrado aefp ON aefp.id_alumno=a.id
               WHERE va.id=$id_alumno;";
$alumno     = consulta_sql($SQL_alumno);

extract($alumno[0]);

$tit = false;
switch ($estado) {
	case "Vigente":
		break;
	case "Egresado" || "Titulado" || "Licenciado" || "Graduado" || "Post-Titulado":
		$tit = true;
		break;
	default:
		echo(msje_js("ERROR: Este alumno NO tiene estado de «Vigente», «Egresado», «Titulado», «Licenciado», «Graduado» ni «Post-Titulado»."."\\n\\n"."No puede continuar"));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
}

$ANOS_ACADEMICOS[] = array("id" => $ANO, "nombre" => $ANO); 

$PERIODOS_ACADEMICOS = array();

if ($mat_actual == "t") { 
	$ANOS_ACADEMICOS[] = array("id" => $ANO, "nombre" => $ANO); 
	$PERIODOS_ACADEMICOS[] = array("id" => "$SEMESTRE-$ANO", "nombre" => "$SEMESTRE-$ANO"); 
	if ($_REQUEST["ano_academico"] == "") { $_REQUEST["ano_academico"] = $ANO; }
	if ($_REQUEST["periodo_academico"] == "") { $_REQUEST["periodo_academico"] = "$SEMESTRE-$ANO"; }
}
//if ($mat_sgte_ano == "t" && $ANO <> $ANO_MATRICULA) { 
if ($mat_sgte_ano == "t") { 
	$ANOS_ACADEMICOS[] = array("id" => $ANO_MATRICULA, "nombre" => $ANO_MATRICULA);
	$PERIODOS_ACADEMICOS[] = array("id" => "$SEMESTRE_MATRICULA-$ANO_MATRICULA", "nombre" => "$SEMESTRE_MATRICULA-$ANO_MATRICULA"); 
	if ($_REQUEST["ano_academico"] == "") { $_REQUEST["ano_academico"] = $ANO_MATRICULA; }
	if ($_REQUEST["periodo_academico"] == "") { $_REQUEST["periodo_academico"] = "$SEMESTRE_MATRICULA-$ANO_MATRICULA"; }
}

if (($estado == "Egresado" || $estado == "Licenciado") && $fecha_egreso == "") { 
	echo(msje_js("ERROR: No se puede continuar debido a que el estudiante se encuentra Egresado/Licenciado/Titulado "
	            ."y no se encuentra registrada la Fecha de Egreso.\\n\\n"
	            ."No se puede continuar")); 
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}  

if ($_REQUEST['val_tit'] <> "no" && ($estado == "Egresado" || $estado == "Licenciado") && strtotime($fecha_egreso)+strtotime("3 years") <= strtotime("now")) { 
	echo(msje_js("ATENCIÓN: No se puede emitir un Certificado de Procurar a este alumno, debido a que tiene más de 3 años de egresado.\\n\\n"
	            ."No obstante, se puede continuar e intentar emitir otro tipo de certificado")); 
}  

$problemas = false;
$msje = "No está ingresada cierta información importante, que puede afectar la emisión de un certificado. En particular:";
if ($fecha_egreso     == "")          { $msje .= "\\n\\n - Fecha de Egreso";     $problemas = true; }
if ($fecha_titulacion == "")          { $msje .= "\\n\\n - Fecha de Titulación"; $problemas = true; }
if ($examen_grado_titulo_fecha == "") { $msje .= "\\n\\n - Fecha de Examen de Grado/Título"; $problemas = true; }
if ($examen_grado_titulo_calif == "") { $msje .= "\\n\\n - Calificación de Examen de Grado/Título"; $problemas = true; }
if ($nombre_grado              == "") { $msje .= "\\n\\n - Nombre del Grado Académico que otorga la carrera"; $problemas = true; }
if ($nombre_titulo             == "") { $msje .= "\\n\\n - Nombre del Título Profesional que otorga la carrera"; $problemas = true; }
if ($problemas && $_REQUEST['val_tit'] <> "no" && $tit) {
	$msje .= "\\n\\n"
	      .  "¿Desea continuar de todas formas?";
	$url_si = "$enlbase_sm=alumno_emitir_certif&id_alumno=$id_alumno&val_tit=no&val_datos_contacto=$val_datos_contacto";
	$url_no = "$enlbase_sm=alumno_emitir_certif&id_alumno=$id_alumno&val_tit=si&val_datos_contacto=$val_datos_contacto";
	echo(confirma_js($msje,$url_si,$url_no));
}

$cond_certif = "WHERE activo AND (id_carrera=$carrera_actual OR id_carrera IS NULL) and (id_estado_req IS NULL OR id_estado_req=$id_estado) ";

if ($regimen == "PRE" && ($estado == "Egresado" || $estado == "Licenciado") && strtotime($fecha_egreso)+strtotime("3 years")<=strtotime("now")) { $cond_certif .= " AND egresado OR NOT matriculado"; } 
if ($regimen == "POST-GD" && ($cohorte+3 <= $ANO && $cohorte_reinc+3 <= $ANO)) { $cond_certif .= " AND NOT matriculado"; }
if ($regimen == "PRE" && ($matriculado == "f" && $mat_actual == "f" && $mat_sgte_ano == "f")) { $cond_certif .= " AND NOT matriculado"; }
$guardar_enabled = "disabled";
//echo("SELECT id,nombre FROM certificados $cond_certif ORDER BY nombre");
$CERTIFICADOS = consulta_sql("SELECT id,nombre FROM certificados $cond_certif ORDER BY nombre");
if ($_REQUEST['id_certificado'] > 0) {
	$cert = consulta_sql("SELECT nombre_archivo,to_char(fecha_vigencia,'YYYYMMDD') AS fecha_vigencia,firma1,firma2 FROM certificados WHERE id={$_REQUEST['id_certificado']}");
	extract($cert[0]);
	
	$SQL_total_horas_carrera = "SELECT SUM(horas_semanal*nro_semanas_semestrales) AS total 
							    FROM detalle_mallas AS dm 
							    LEFT JOIN prog_asig AS pa ON pa.id=dm.id_prog_asig 
							    WHERE dm.id_malla=a.malla_actual
							    GROUP BY dm.id_malla";
	$SQL_certificado = "SELECT trim(a.rut) AS rut_alumno,
	                           coalesce(a.cohorte_reinc,a.cohorte) AS cohorte,
	                           coalesce(a.mes_cohorte_reinc,a.mes_cohorte) AS mes_cohorte,a.carrera_actual,
	                           coalesce(a.semestre_cohorte_reinc,a.semestre_cohorte) AS semestre_cohorte,
							   CASE a.genero WHEN 'm' THEN 'don' WHEN 'f' THEN 'doña' END AS vocativo_alumno,
							   upper(a.nombres||' '||a.apellidos) AS nombre_alumno,c.nombre AS carrera_alumno,
							   CASE a.jornada WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada_alumno,
							   c.alias AS carrera_alias_alumno,ae.nombre AS estado_alumno,
							   a.fecha_egreso::date AS fecha_egreso_alumno,
							   a.examen_grado_titulo_fecha::date AS examen_grado_titulo_fecha_alumno,
							   a.salida_int_fecha::date AS salida_int_fecha_alumno,
							   a.salida_int_nroreg_libro||'/'||date_part('year',salida_int_fecha) AS salida_int_nroreg_libro_alumno,
							   to_char(a.salida_int_calif,'9D9') AS salida_int_calif_alumno,
							   m.tns_nombre,
							   m.ano AS ano_malla,
							   a.fecha_inicio_programa::date AS fecha_inicio_programa,
							   a.fecha_titulacion::date AS fecha_titulacion_alumno,
							   a.fecha_graduacion::date AS fecha_graduacion_alumno,
                               to_char(a.nota_titulacion,'9D9') AS nota_titulacion_alumno,
                               to_char(a.nota_graduacion,'9D9') AS nota_graduacion_alumno,
      						   a.nro_registro_libro_tit||'/'||date_part('year',fecha_titulacion) AS nro_registro_libro_tit_alumno,
      						   a.nro_registro_libro_grado||'/'||date_part('year',fecha_graduacion) AS nro_registro_libro_grado_alumno,
							   to_char(a.examen_grado_titulo_calif,'9D9') AS examen_grado_titulo_calif_alumno,
							   c.nombre_titulo AS nombre_titulo_alumno,c.nombre_grado AS nombre_grado_alumno,
							   m.niveles AS duracion_carrera_alumno,($SQL_total_horas_carrera) AS horas_totales_carrera_alumno,
							   a.rpnp AS rpnp_alumno
						FROM alumnos       AS a						
						LEFT JOIN carreras AS c ON c.id=a.carrera_actual
						LEFT JOIN mallas   AS m ON m.id=a.malla_actual
						LEFT JOIN al_estados AS ae ON ae.id=a.estado
						WHERE a.id=$id_alumno";
	$certificado = consulta_sql($SQL_certificado);
	extract($certificado[0]);
	
	$SQL_curso_dip = "SELECT to_char(nota_final,'9D9') AS nota_final FROM cargas_academicas WHERE id_curso IN (SELECT id FROM vista_cursos WHERE id_carrera=$carrera_actual) AND id_alumno=$id_alumno";
	$curso_dip     = consulta_sql($SQL_curso_dip);
	if (count($curso_dip) == 0) {
		$SQL_curso_dip = "SELECT to_char(avg(nota_final),'9D9') AS nota_final FROM cargas_academicas WHERE id_alumno=$id_alumno";
		$curso_dip     = consulta_sql($SQL_curso_dip);    
	}
	$nota_final_dip           = $curso_dip[0]['nota_final'];
	$nota_final_dip_palabras  = calificacion_palabras($curso_dip[0]['nota_final']);
	$nota_final_dip_apelativo = apelativo_aprobacion($curso_dip[0]['nota_final']);
	
	
	$fecha_cert                       = strftime("%e de %B de %Y",strtotime($_REQUEST['fecha']));
	$fecha_egreso_alumno              = strftime("%e de %B de %Y",strtotime($fecha_egreso_alumno));
	$fecha_titulacion_alumno          = strftime("%e de %B de %Y",strtotime($fecha_titulacion_alumno));
	$fecha_graduacion_alumno          = strftime("%e de %B de %Y",strtotime($fecha_graduacion_alumno));
	$fecha_inicio_programa            = strftime("%e de %B de %Y",strtotime($fecha_inicio_programa));
	$examen_grado_titulo_fecha_alumno = strftime("%e de %B de %Y",strtotime($examen_grado_titulo_fecha_alumno));
	$salida_int_fecha_alumno          = strftime("%e de %B de %Y",strtotime($salida_int_fecha_alumno));
	$mes_cohorte = $meses_palabra[$mes_cohorte-1]['nombre'];
	$ano_academico = $_REQUEST['ano_academico'];
	list($semestre_academico,$ano_academico) = explode("-",$_REQUEST['periodo_academico']);
	$_REQUEST['semestre_academico'] = $semestre_academico;
	$semestre_academico = $semestres_academicos[$semestre_academico]['nombre'] . " (" . $semestres[$semestre_academico]['nombre'] . ")";
	$examen_grado_titulo_calif_palabras = calificacion_palabras($examen_grado_titulo_calif_alumno);
	$salida_int_calif_palabras = calificacion_palabras($salida_int_calif_alumno);
	$salida_int_calif_apelativo = apelativo_aprobacion($salida_int_calif_alumno);
	$nota_titulacion_palabras = calificacion_palabras($nota_titulacion_alumno);
	$nota_titulacion_apelativo = apelativo_aprobacion($nota_titulacion_alumno);
	$nota_graduacion_palabras = calificacion_palabras($nota_graduacion_alumno);
	$nota_graduacion_apelativo = apelativo_aprobacion($nota_graduacion_alumno);

	list($rut,$dv) = explode("-",$rut_alumno);
	$rut = number_format(intval($rut),0,",",".");
	$rut_alumno = "$rut-$dv";

	$otros = "alumno_emitir_certif_otros_$nombre_archivo.php";

	$nombre_archivo = $nombre_archivo."_".$fecha_vigencia;
	include("fmt/$nombre_archivo.php");
	$texto_docto = nl2br($texto_docto);
	if ($texto_adic == "") { $texto_adic = "Se extiende el presente certificado a petición del(a) interesado(a) para los fines que estime convenientes."; }
	$guardar_enabled = "";
}

if ($_REQUEST['guardar'] == "Emitir") {
	$texto_adicional = $_REQUEST['texto_adicional'];
	eval("\$texto_adicional = \"$texto_adicional\";");
	$_REQUEST['texto_adicional'] = $texto_adicional;
	$_REQUEST['otros'] = implode(",",$_REQUEST['otros']);

	$aCampos = array("id_alumno","id_certificado","fecha","ano_academico","semestre_academico","texto_adicional","otros","firma1","firma2","id_emisor");
	$SQLinsert_certif = "INSERT INTO alumnos_certificados " . arr2sqlinsert($_REQUEST,$aCampos);
	if (consulta_dml($SQLinsert_certif) == 1) {
	
		$certif = consulta_sql("SELECT currval('alumnos_certificados_folio_seq') AS folio");
		$folio  = $certif[0]['folio'];

		echo(msje_js("Se ha emitido exitósamente el certificado"));
		certificado_email($folio);
		echo(js("window.open('certificado.php?folio=$folio')"));
		echo(js("parent.jQuery.fancybox.close()"));
		exit;
	} else {
		echo(msje_js("ERROR: No se ha podido emitir el certificado"."\\n\\n"."Por favor revise los formatos de los datos ingresados."));
	}
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal_sm.php" method="post">
<input type="hidden" name="modulo"             value="<?php echo($modulo); ?>">
<input type="hidden" name="id_alumno"          value="<?php echo($id_alumno); ?>">
<input type="hidden" name="id_emisor"          value="<?php echo($_SESSION['id_usuario']); ?>">
<input type="hidden" name="val_tit"            value="<?php echo($_REQUEST['val_tit']); ?>">
<input type="hidden" name="val_datos_contacto" value="<?php echo($_REQUEST['val_datos_contacto']); ?>">
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Emitir" <?php echo($guardar_enabled); ?>>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class="celdaNombreAttr" style="text-align: center" colspan="4">Antecedentes del Alumno</td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">ID:</td>
    <td class="celdaValorAttr"><?php echo($id); ?></td>
    <td class="celdaNombreAttr">RUT:</td>
    <td class="celdaValorAttr"><?php echo($rut_alumno); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nombre:</td>
    <td class="celdaValorAttr" colspan="3"><?php echo($nombre); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Carrera:</td>
    <td class="celdaValorAttr"><?php echo($carrera); ?></td>
    <td class="celdaNombreAttr"><u>Jornada:</u></td>
    <td class="celdaValorAttr"><?php echo($jornada); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr" style="text-align: center" colspan="4">Antecedentes del Certificado</td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Documento:</td>
    <td class="celdaValorAttr" colspan='3'>
      <select name="id_certificado" class='filtro' style="max-width: none" onChange='submitform()'>
        <option value="">-- Ninguna --</option>
        <?php echo(select($CERTIFICADOS,$_REQUEST['id_certificado'])); ?>
      </select>    
    </td>
  </tr>
<?php if ($_REQUEST['id_certificado'] > 0) { ?>  
  <tr>
    <td class="celdaNombreAttr">Fecha:</td>
    <td class="celdaValorAttr">
      <input type='date' name='fecha' value='<?php echo($_REQUEST['fecha']); ?>' min='<?php echo($_REQUEST['fecha']); ?>' max='<?php echo($_REQUEST['fecha']); ?>' class='boton' onChange='submitform()'><br>
    </td>
    <td class="celdaNombreAttr">Año Académico:<br>Periodo Académico:</td>
    <td class="celdaValorAttr">
      <select name='ano_academico' onChange='submitform()' class='filtro'><?php echo(select($ANOS_ACADEMICOS,$_REQUEST['ano_academico'])); ?></select><br>
      <select name='periodo_academico' onChange='submitform()' class='filtro'><?php echo(select($PERIODOS_ACADEMICOS,$_REQUEST['periodo_academico'])); ?></select>
      <!-- <input type='text' size='4' name='ano_academico' value='<?php echo($_REQUEST['ano_academico']); ?>' class='boton' onChange='submitform()'><br>
      <sup>Formato: AAAA</sup> -->
    </td>
  </tr>
  <tr>
    <td class="celdaValorAttr" colspan='4'>
      <div class="celdaNombreAttr" style='text-align: left'>Texto Documento</div>
      <div style='width: auto; height: 250px; overflow: auto; padding: 5px'><?php echo($texto_docto); ?></div>
    </td>
  </tr>
  <tr>
    <td class="celdaValorAttr" colspan='4'>
      <div class="celdaNombreAttr" style='text-align: left'>Texto Adicional</div>
      <textarea name='texto_adicional' class='general' style='width: 100%; height: 50px'><?php echo($texto_adic); ?></textarea>
    </td>
  </tr>
  <tr>
    <td class="celdaValorAttr" colspan='4'>
      <div class="celdaNombreAttr" style='text-align: left'>Otros</div>
	  <?php include($otros); ?>
    </td>
  </tr>
  <tr>
    <td class="celdaValorAttr" colspan='2' width='50%'>
      <div class="celdaNombreAttr" style='text-align: left'>Firmante 1°</div>
      <textarea name='firma1' class='general' style='height: 50px; text-align: center'><?php echo($firma1); ?></textarea>
    </td>
    <td class="celdaValorAttr" colspan='2' width='50%'>
      <div class="celdaNombreAttr" style='text-align: left'>Firmante 2°</div>
      <textarea name='firma2' class='general' style='height: 50px; text-align: center'><?php echo($firma2); ?></textarea>
    </td>
  </tr>
<?php } ?>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

