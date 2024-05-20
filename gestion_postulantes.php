<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) { $cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if ($reg_inicio == "") { $reg_inicio = 0; }

$texto_buscar      = $_REQUEST['texto_buscar'];
$buscar            = $_REQUEST['buscar'];
$id_carrera        = $_REQUEST['id_carrera'];
$jornada           = $_REQUEST['jornada'];
$semestre_cohorte  = $_REQUEST['semestre_cohorte'];
$cohorte           = $_REQUEST['cohorte'];
$est_regular       = $_REQUEST['est_regular'];
$id_desertor       = $_REQUEST['id_desertor'];
$id_emisor         = $_REQUEST['id_emisor'];
$estado            = $_REQUEST['estado'];
$admision          = $_REQUEST['admision'];
$regimen           = $_REQUEST['regimen'];
$id_inst_edsup     = $_REQUEST['id_inst_edsup'];
$matriculado       = $_REQUEST['matriculado'];

if (empty($_REQUEST['cohorte'])) { $cohorte = $ANO_MATRICULA; }
if (empty($_REQUEST['semestre_cohorte'])) { $semestre_cohorte = $SEMESTRE_MATRICULA; }
if (empty($_REQUEST['estado'])) { $estado = -1; }
if (empty($_REQUEST['id_desertor'])) { $id_desertor = -1; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }
if (empty($cond_base)) { $cond_base = "true"; }

$condicion = "WHERE true ";

$SQL_contrato = "SELECT 1 FROM finanzas.contratos WHERE id_pap=pap.id AND ano=$ANO_MATRICULA AND estado IS NOT NULL LIMIT 1";

$SQL_ies_pap = "SELECT DISTINCT ON (id_inst_edsup) id_inst_edsup FROM convalidaciones WHERE id_pap=vp.id";

if ($buscar == 'Buscar' && $texto_buscar <> "") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar = explode(" ",$texto_buscar_regexp);
	$condicion = "WHERE ";
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= "(lower(pap.nombres||' '||pap.apellidos) ~* '$cadena_buscada' OR "
		            . " pap.rut ~* '$cadena_buscada' OR "
		            . " text(pap.id) ~* '$cadena_buscada' "
		            . ") AND ";
	}
	$condicion=substr($condicion,0,strlen($condicion)-4);
	$cohorte = $semestre_cohorte = $estado = $id_carrera = $admision = $matriculado = $regimen = null;
} else {

	if ($id_carrera <> "") {
		$condicion .= "AND ($id_carrera IN (pap.carrera1_post,pap.carrera2_post,pap.carrera3_post)) ";
	}
	
	if ($jornada <> "") {
		$condicion .= "AND ('$jornada' IN (pap.jornada1_post,pap.jornada2_post,pap.jornada3_post)) ";
	}
	
	if ($cohorte > 0) { $condicion .= " AND (pap.cohorte='$cohorte') "; }
	if ($semestre_cohorte > 0) { $condicion .= " AND (pap.semestre_cohorte='$semestre_cohorte') "; }
	
	if ($estado <> "-1") { $condicion .= " AND (pap.estado_carpeta_doctos='$estado') "; }

  if ($id_desertor == "t") { $condicion .= " AND vcp.estado IN ('R','S','A') "; }
  elseif ($id_desertor == "f") { $condicion .= " AND vcp.estado = 'E' "; }

  if ($id_emisor > 0) { $condicion .= " AND vcp.id_emisor=$id_emisor "; }
	
	if ($regimen <> "" && $regimen <> "t") {
		$condicion .= "AND ('$regimen' IN (c1.regimen,c2.regimen,c3.regimen,c4.regimen,c5.regimen,c6.regimen)) ";
	}
	
	if ($admision <> "") {
		$condicion .= "AND (pap.admision = '$admision') ";
	}

  if ($est_regular == "t") { $condicion .= " AND (a.id IS NOT NULL)"; }
  elseif ($est_regular == "f") { $condicion .= " AND (a.id IS NULL)"; }

  if ($id_inst_edsup > 0) { $condicion .= " AND $id_inst_edsup IN ($SQL_ies_pap) "; }

if ($matriculado == "t") { $condicion .= "AND (vca.rut IS NOT NULL) AND ('$regimen' IN (SELECT regimen FROM carreras WHERE id=(SELECT id_carrera FROM finanzas.contratos WHERE estado IS NOT NULL AND ano=$ANO_MATRICULA AND id_pap=pap.id LIMIT 1)))"; }
	elseif ($matriculado == "f") { $condicion .= "AND (vca.rut IS NULL) "; }
	
}

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_foto = "SELECT dd.id FROM doctos_digitalizados dd LEFT JOIN doctos_digital_tipos ddt ON dd.id_tipo=ddt.id WHERE rut=vp.rut AND ddt.alias='fotos' AND NOT eliminado LIMIT 1";

$SQL_evid_cert_titulo_grado = "SELECT dd.id FROM doctos_digitalizados dd LEFT JOIN doctos_digital_tipos ddt ON dd.id_tipo=ddt.id WHERE rut=vp.rut AND ddt.alias IN ('evid_cert_titulo_grado','cert_tit') AND NOT eliminado LIMIT 1";

$SQL_postulantes = "SELECT trim(pap.rut) AS rut,vp.id,vp.nombre,to_char(vp.fecha_post,'DD-MM-YYYY') AS fecha_post,
                           trim(vp.carrera1)||'-'||pap.jornada1_post AS carrera1,
                           trim(vp.carrera2)||'-'||pap.jornada2_post AS carrera2,
                           trim(vp.carrera3)||'-'||pap.jornada3_post AS carrera3,
                           trim(c4.alias)||'-'||pap.jornada4_post AS carrera4,
                           trim(c5.alias)||'-'||pap.jornada5_post AS carrera5,
                           trim(c6.alias)||'-'||pap.jornada6_post AS carrera6,
                           pap.estado_carpeta_doctos,pap.semestre_cohorte||'-'||pap.cohorte AS cohorte_post,pap.telefono,pap.tel_movil,pap.email,
                           CASE WHEN vp.cert_nacimiento THEN 'Si' ELSE 'No' END AS cert_nacimiento,
                           CASE WHEN vp.copia_ced_iden  THEN 'Si' ELSE 'No' END AS copia_ced_iden,
                           CASE WHEN vp.conc_notas_em   THEN 'Si' ELSE 'No' END AS conc_notas_em,
                           CASE WHEN pap.conc_notas_em_comp_solic THEN 'Si' ELSE 'No' END AS conc_notas_em_comp_solic,
                           CASE WHEN vp.licencia_em     THEN 'Si' ELSE 'No' END AS licencia_em,
                           CASE WHEN pap.licencia_em_comp_solic   THEN 'Si' ELSE 'No' END AS licencia_em_comp_solic,
                           CASE pap.fotografias WHEN true THEN 'Si' ELSE 'No' END AS fotografias,
                           CASE WHEN vca.rut IS NOT NULL THEN 'Si' ELSE 'No' END AS matriculado,pap.comentarios,pap.promedio_col,
                           CASE WHEN ($SQL_evid_cert_titulo_grado) IS NOT NULL THEN 'Si' ELSE 'No' END AS evid_cert_titulo_grado,
                           CASE WHEN a.id IS NOT NULL THEN 'Si' ELSE 'No' END AS estudiante_regular,
                           CASE WHEN vcp.estado IN ('S','R','A') THEN 'Si' ELSE 'No' END AS desertor, 
                           CASE vcp.estado 
                             WHEN 'E' THEN 'Emitido'
                             WHEN 'S' THEN 'Suspensión'
                             WHEN 'R' THEN 'Retiro'
                             WHEN 'A' THEN 'Abandono'
                           END AS estado_contrato,to_char(vcp.estado_fecha,'DD-tmMon-YYYY HH24:MI') AS estado_contrato_fecha,
                           ae.nombre AS estado_estudiante,pap.regimen,vcp.nombre_usuario AS ejecutivo,
                           pap.referencia,r.nombre AS nombre_referencia,pap.referencia_comentarios,($SQL_foto) AS id_foto,
                           vp.admision,pap.rbd_colegio,col.dependencia AS dependencia_colegio,pap.admision_subtipo,vp.nacionalidad,vp.comuna,obd.nombre AS origen_bd
                    FROM vista_pap AS vp
                    LEFT JOIN pap USING (id)
                    LEFT JOIN vista_pap_estados vpe USING (id)
                    LEFT JOIN carreras c1 ON c1.id=pap.carrera1_post
                    LEFT JOIN carreras c2 ON c2.id=pap.carrera2_post
                    LEFT JOIN carreras c3 ON c3.id=pap.carrera3_post
                    LEFT JOIN carreras c4 ON c4.id=pap.carrera4_post
                    LEFT JOIN carreras c5 ON c5.id=pap.carrera5_post
                    LEFT JOIN carreras c6 ON c6.id=pap.carrera6_post
                    LEFT JOIN vista_contratos_anos AS vca ON (vca.rut=pap.rut AND vca.ano=$ANO_MATRICULA)
                    LEFT JOIN vista_contratos_pap AS vcp ON (pap.id= vcp.id_pap AND vcp.ano=$ANO_MATRICULA)
                    LEFT JOIN admision.referencias AS r ON r.id=pap.referencia
                    LEFT JOIN vista_colegios AS col ON col.rbd=pap.rbd_colegio
                    LEFT JOIN alumnos AS a ON (a.id_pap=pap.id AND a.semestre_cohorte=pap.semestre_cohorte AND a.cohorte=pap.cohorte)
                    LEFT JOIN al_estados AS ae ON ae.id=a.estado
                    LEFT JOIN admision.origenes_bd AS obd ON obd.id=pap.id_origen_bd
                    $condicion
                    ORDER BY pap.fecha_post DESC ";
$SQL_tabla_completa = "COPY ($SQL_postulantes) to stdout WITH CSV HEADER";
$SQL_postulantes .= "$limite_reg OFFSET $reg_inicio";
$postulantes      = consulta_sql($SQL_postulantes);

$enlace_nav = "$enlbase=$modulo"
            . "&mes_cohorte=$mes_cohorte"
            . "&semestre_cohorte=$semestre_cohorte"
            . "&cohorte=$cohorte"
            . "&estado=$estado"
            . "&admision=$admision"            
            . "&matriculado=$matriculado"
            . "&id_carrera=$id_carrera"
            . "&jornada=$jornada"
            . "&regimen=$regimen"
            . "&texto_buscar=$texto_buscar"
            . "&buscar=$buscar"
            . "&cant_reg=$cant_reg"
            . "&r_inicio";

if (count($postulantes) > 0) {
	$SQL_total_pap =  "SELECT count(pap.id) AS total_pap 
                     FROM vista_pap AS vp
                     LEFT JOIN pap USING (id)
                     LEFT JOIN vista_pap_estados vpe USING (id)
                     LEFT JOIN carreras c1 ON c1.id=pap.carrera1_post
                     LEFT JOIN carreras c2 ON c2.id=pap.carrera2_post
                     LEFT JOIN carreras c3 ON c3.id=pap.carrera3_post
                     LEFT JOIN carreras c4 ON c4.id=pap.carrera4_post
                     LEFT JOIN carreras c5 ON c5.id=pap.carrera5_post
                     LEFT JOIN carreras c6 ON c6.id=pap.carrera6_post
                     LEFT JOIN vista_contratos_anos AS vca ON (vca.rut=pap.rut AND vca.ano=$ANO_MATRICULA)
                     LEFT JOIN vista_contratos_pap AS vcp ON (pap.id= vcp.id_pap AND vcp.ano=$ANO_MATRICULA) 
                     LEFT JOIN admision.referencias AS r ON r.id=pap.referencia
                     LEFT JOIN vista_colegios AS col ON col.rbd=pap.rbd_colegio
                     LEFT JOIN alumnos AS a ON (a.id_pap=pap.id AND a.semestre_cohorte=pap.semestre_cohorte AND a.cohorte=pap.cohorte)
                    LEFT JOIN al_estados AS ae ON ae.id=a.estado
                    LEFT JOIN admision.origenes_bd AS obd ON obd.id=pap.id_origen_bd
	                   $condicion;";
	$total_pap = consulta_sql($SQL_total_pap);
	$tot_reg = $total_pap[0]['total_pap'];
	
	$HTML_paginador = html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}

$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "")      { $cond_carreras .= "AND regimen='$regimen' "; }

$SQL_carreras = "SELECT id,nombre FROM carreras $cond_carreras ORDER BY nombre;";
$carreras = consulta_sql($SQL_carreras);

$cohortes = $anos;

$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();

$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);

$SEMESTRES_COHORTES = array(array("id"=>1,"nombre"=>1),
                            array("id"=>2,"nombre"=>2));

/*
$ESTADOS = array(array('id'=>"Completo",          'nombre'=>"Completo"),
                 array('id'=>"Incompleto s/C.N.", 'nombre'=>"Incompleto s/C.N."),
                 array('id'=>"Condicional c/LIC", 'nombre'=>"Condicional c/LIC"),
                 array('id'=>"Condicional s/LIC", 'nombre'=>"Condicional s/LIC"),
                 array('id'=>"Provisorio",        'nombre'=>"Provisorio"));
*/

$ESTADOS_CARPETA_DOCTOS = consulta_sql("SELECT id,nombre FROM vista_estado_carpeta_doctos");

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

$EMISORES = consulta_sql("SELECT DISTINCT ON (id_emisor,nombre_usuario) id_emisor AS id,nombre_usuario AS nombre FROM vista_contratos_pap WHERE ano=$ANO ORDER BY nombre_usuario");

$IES = consulta_sql("SELECT ies.id,coalesce(ies.alias,'')||' - '||ies.nombre as nombre,iet.nombre AS grupo FROM inst_edsup AS ies LEFT JOIN inst_edsup_tipo AS iet ON iet.id=ies.id_tipo ORDER BY iet.nombre,ies.nombre");

$_SESSION['enlace_volver'] = "$enlbase=$modulo&fecha_ini=$fecha_ini&fecha_fin=$fecha_fin&buscar_fecha=$buscar_fecha&id_carrera=$id_carrera&texto_buscar=$texto_buscar&buscar=$buscar&reg_inicio";

// para actualizar en masa
//for ($x=0;$x<count($postulantes);$x++) { $rut=$postulantes[$x]['rut']; verif_estado_carpeta_doctos($rut); }
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<div class="texto">
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
      <tr>
        <td class="celdaFiltro">
          Periodo de Post.: <br>
<?php if ($regimen <> "PRE") { ?>          
          <select class="filtro" name="mes_cohorte" onChange="submitform();">
            <option value="0">-- mes --</option>
            <?php echo(select($meses_fn,$mes_cohorte)); ?>    
          </select>
          -
<?php } ?>
          <select class="filtro" name="semestre_cohorte" onChange="submitform();">
            <option value="-1"></option>
            <?php echo(select($SEMESTRES_COHORTES,$semestre_cohorte)); ?>    
          </select>
          -
          <select class="filtro" name="cohorte" onChange="submitform();">
            <option value="-1">Todas</option>
            <?php echo(select($cohortes,$cohorte)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Carpeta Doctos: <br>
          <select class="filtro" name="estado" onChange="submitform();">
            <option value="-1">--</option>
            <?php echo(select($ESTADOS_CARPETA_DOCTOS,$estado)); ?>
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
        <td class="celdaFiltro">
          Desertor: <br>
          <select class="filtro" name="id_desertor" onChange="submitform();">
            <option value="-1">Todos</option>
            <?php echo(select($sino,$id_desertor)); ?>
          </select>
        </td> 
        <td class="celdaFiltro">
          Est. Regular: <br>
          <select class="filtro" name="est_regular" onChange="submitform();">
            <option value="a">Todos</option>
            <?php echo(select($sino,$est_regular)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Ejecutivo:<br>
          <select class="filtro" name="id_emisor" onChange="submitform();">
            <option value="">--</option>
            <?php echo(select_group($EMISORES,$id_emisor)); ?>
          </select>
        </td>
      </tr>
    </table>
    <table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
      <tr>
        <td class="celdaFiltro">
          Carrera/Programa:<br>
          <select class="filtro" name="id_carrera" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($carreras,$id_carrera)); ?>
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
          IES procedencia:<br>
          <select class="filtro" name="id_inst_edsup" onChange="submitform();">
            <option value="">--</option>
            <?php echo(select_group($IES,$id_inst_edsup)); ?>
          </select>
        </td>
      </tr>
    </table>
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Buscar por ID, RUT o nombre:<br>
          <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="40" id="texto_buscar" class='boton'>
          <input type='submit' name='buscar' value='Buscar'>          
          <?php 
          	if ($buscar == "Buscar" && $texto_buscar <> "") {
          		echo(" <input type='submit' name='buscar' value='Vaciar'>");
          	}
          ?>          <script>document.getElementById("texto_buscar").focus();</script>
        </td>
        <td class="celdaFiltro">
          Acciones:<br>
          <a href="<?php echo("$enlbase=$modulo&matriculado=t&id_desertor=f&est_regular=f"); ?>" class='botoncito'>ver Pendientes</a>
          <a href="<?php echo("$enlbase=$modulo&matriculado=t&id_desertor=f&est_regular=f&estado=Completa"); ?>" class='botoncito'>ver Pendientes carpeta Completa</a>        
        </td>
      </tr>
    </table>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="3">
      Mostrando <b><?php echo($tot_reg); ?></b> postulante(s) en total, en página(s) de
      <select class='filtro' name="cant_reg" onChange="submitform();">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas
    </td>
    <td class="texto" align="right" colspan="8">
      <?php echo($HTML_paginador); ?>
      <?php echo($boton_tabla_completa); ?>
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>RUT</td>
    <td class='tituloTabla'>Nombre</td>
    <td class='tituloTabla'>Carrera(s)</td>
    <td class='tituloTabla'>Periodo</td>
    <td class='tituloTabla'>Matric.<br><sup>(<?php echo("$SEMESTRE_MATRICULA-$ANO_MATRICULA"); ?>)</sup></td>
    <td class='tituloTabla'>Desertor</td> 
    <td class='tituloTabla'>Est.<br>Regular</td> 
    <td class='tituloTabla'>Carpeta<br>Doctos.</td> 
    <td class='tituloTabla'>Ejecutivo</td> 
    <td class='tituloTabla'>Fecha de<br>Postulación</td>
  </tr>
<?php
	$HTML_pap = "";
	if (count($postulantes) > 0) {
		for ($x=0;$x<count($postulantes);$x++) {
			extract($postulantes[$x]);
			
			$enl = "$enlbase=ver_postulante&id_pap=$id";
			$enlace = "a class='enlitem' href='$enl'";
			
			$carreras = implode(",",array_filter(array($carrera1, $carrera2, $carrera3, $carrera4, $carrera5, $carrera6)));
			
			$HTML_doctos = "Licencia EM (Original): <b>$licencia_em</b><br>
			                Comprobante de Solicitud de Licencia EM: <b>$licencia_em_comp_solic</b><br>
			                Concentración de Notas EM (Orig.): <b>$conc_notas_em</b><br>
			                Comprobante Solicitud Conc. Notas EM: <b>$conc_notas_em_comp_solic</b><br>
			                Copia Cédula de Identidad: <b>$copia_ced_iden</b><br>
			                Cert. Nacimiento: <b>$cert_nacimiento</b><br>
			                2 Fotografías: <b>$fotografias</b>";
			                
			if (!empty($comentarios)) {
				$comentarios = str_replace("###","blockquote",wordwrap(nl2br($comentarios),90)); 
				$id = "<div title='header=[Observaciones] fade=[on] body=[$comentarios]' style='background: #BFE4BF; border-radius: 25px; padding: 0px 2px 0px 2px'>$id</div>";
			}

			$estado = "<div title='header=[Detalle documentación] fade=[on] body=[$HTML_doctos]' class='".implode(str_split(" ",$estado,1))."'>$estado</div>";

			$estado_carpeta_doctos = "<div class='$estado_carpeta_doctos'>$estado_carpeta_doctos</div>";
      $estado_carpeta_doctos = "<a href='$enlbase_sm=doctos_digitalizados&rut=$rut' class='enlaces' title='Ver documentación' id='sgu_fancybox'>$estado_carpeta_doctos</a>";

      $rut = "<a href='$enl' class='enlaces'>$rut</a>";
			
      $desertor = "<div title='$estado_contrato desde el $estado_contrato_fecha'>$desertor</div>";

			$HTML_pap .= "  <tr class='filaTabla'>\n"
			          . "    <td class='textoTabla'>$id<!--<br><img src='doctos_digitalizados_ver.php?id=$id_foto' width='30'>--></td>\n"
			          . "    <td class='textoTabla' align='right'>$rut</td>\n"
			          . "    <td class='textoTabla'><a class='enlitem' href='$enl'>$nombre</a></td>\n"
			          . "    <td class='textoTabla'>$carreras</td>\n"
			          . "    <td class='textoTabla'>$cohorte_post</td>\n"
			          . "    <td class='textoTabla' align='center'>$matriculado</td>\n"
			          . "    <td class='textoTabla' align='center'>$desertor</td>\n"
			          . "    <td class='textoTabla' align='center'>$estudiante_regular</td>\n"
			          . "    <td class='textoTabla' align='center'>$estado_carpeta_doctos</td>\n"
			          . "    <td class='textoTabla'>$ejecutivo</td>\n"
			          . "    <td class='textoTabla'>$fecha_post</td>\n"
			          . "  </tr>\n";
		}
	} else {
		$HTML_pap = "  <tr>"
		          . "    <td class='textoTabla' colspan='7'>"
		          . "      No hay registros para los criterios de búsqueda/selección"
		          . "    </td>\n"
		          . "  </tr>";
	}
	echo($HTML_pap);
?>
</form>
</table>

<!-- Fin: <?php echo($modulo); ?> -->
<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'		: false,
		'autoDimensions': false,
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic',
		'width'			: 700,
		'maxHeight'		: 550,
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
		'maxHeight'		: 700,
		'afterClose'	: function () { location.reload(true); },
		'type'			: 'iframe'
	});
});
</script>
