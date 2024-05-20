<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

$ids_carreras = $_SESSION['ids_carreras'];
$id_usuario   = $_SESSION['id_usuario'];
 
include("validar_modulo.php");

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) { $cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if ($reg_inicio=="") { $reg_inicio = 0; }

$texto_buscar   = $_REQUEST['texto_buscar'];
$buscar         = $_REQUEST['buscar'];
$id_carrera     = $_REQUEST['id_carrera'];
$ano            = $_REQUEST['ano'];
$semestre       = $_REQUEST['semestre'];
$grado_acad     = $_REQUEST['grado_acad'];
$categorizacion = $_REQUEST['categorizacion'];
$regimen        = $_REQUEST['regimen'];
$funcion        = $_REQUEST['funcion'];
$estado_carpeta = $_REQUEST['estado_carpeta'];
$id_cargo_normalizado_sies = $_REQUEST['id_cargo_normalizado_sies'];

$ver_datos_contacto = $_REQUEST['ver_datos_contacto'];

if ($_REQUEST['ano'] == "")      { $ano = $ANO; }
if ($_REQUEST['semestre'] == "") { $semestre = $SEMESTRE; }
if (empty($_REQUEST['regimen'])) { $regimen = 't'; }
if ($_REQUEST['estado_carpeta'] == '') { $estado_carpeta='-1'; }

$condicion = $cond_cursos = "";
if (!empty($texto_buscar)) {
	$textos_buscar = explode(" ",sql_regexp($texto_buscar));
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= " AND (lower(u.nombre||' '||u.apellido) ~* '$cadena_buscada' "
		            . " OR u.rut ~* '$cadena_buscada') ";
	}
} else {

	if (is_numeric($id_carrera))               { $cond_cursos .= " AND vc.id_carrera=$id_carrera "; }
	if (is_numeric($ano) && $ano>0)            { $cond_cursos .= " AND vc.ano=$ano "; }
	if (is_numeric($semestre) && $semestre>-1) { $cond_cursos .= " AND vc.semestre=$semestre "; }
	if (!empty($ids_carreras))                 { $cond_cursos .= " AND vc.id_carrera IN ($ids_carreras) "; }
	if ($regimen <> "t")                       { $cond_cursos .= " AND car.regimen='$regimen' "; }
	$SQL_cursos_profes = "SELECT vc.id_profesor 
						  FROM vista_cursos  AS vc
						  LEFT JOIN carreras AS car ON car.id=vc.id_carrera 
						  WHERE true $cond_cursos";


	if (is_numeric($grado_acad))               { $condicion .= " AND u.grado_academico='$grado_acad' "; }
	if ($categorizacion == "null")             { $condicion .= " AND categorizacion IS NULL "; }
	elseif ($categorizacion <> "")             { $condicion .= " AND categorizacion='$categorizacion' "; }
	if ($funcion <> "")                        { $condicion .= " AND u.funcion='$funcion' "; }
	if ($id_cargo_normalizado_sies > 0)        { $condicion .= " AND u.id_cargo_normalizado_sies='$id_cargo_normalizado_sies' "; }
  if ($estado_carpeta <> '-1')               { $condicion .= " AND u.estado_carpeta_docto='$estado_carpeta' ";  }
	
	$condicion .= " AND u.id IN ($SQL_cursos_profes)";
}

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_profe_autoev = "SELECT 1 FROM encuestas.autoevaluacion_docente WHERE id_profesor=u.id";

$SQL_horas = "null";
if ($_SESSION['tipo'] == 0) { $SQL_horas = "horas_planta,horas_planta_docencia"; }

$SQL_profesores = "SELECT u.id AS id_profesor,initcap(u.apellido) AS apellidos,initcap(u.nombre) AS nombres,
                          initcap(u.apellido||' '||u.nombre) AS profesor,ga.nombre AS grado_acad,u.grado_acad_nombre,
                          u.rut,e.nombre AS escuela,u.funcion,dscn.nombre AS cargo_normalizado_sies,
                          CASE WHEN ($SQL_profe_autoev)=1 THEN 'Si' ELSE 'No' END AS auto_ev_completa,
                          u.direccion,com.nombre AS comuna,reg.nombre AS region,
                          u.categorizacion as categorizacion,u.nombre_usuario,u.email_personal,u.email_gsuite,
                          fpp.tipo_deposito,fif.nombre AS banco_deposito,fpp.tipo_cuenta_deposito,fpp.nro_cuenta_deposito,fpp.email,$SQL_horas
                   FROM usuarios         AS u
                   LEFT JOIN grado_acad  AS ga ON ga.id=u.grado_academico
                   LEFT JOIN escuelas    AS e  ON e.id=u.id_escuela
                   LEFT JOIN comunas     AS com ON com.id=u.comuna
                   LEFT JOIN regiones    AS reg ON reg.id=u.region
                   LEFT JOIN finanzas.profesores_pago AS fpp ON fpp.id_profesor=u.id
                   LEFT JOIN finanzas.inst_financieras AS fif ON fif.codigo=fpp.cod_banco_deposito
                   LEFT JOIN docentes_sies_cargos_normalizados AS dscn ON dscn.id=u.id_cargo_normalizado_sies
                   WHERE tipo=3 $condicion
                   ORDER BY u.apellido,u.nombre ";
$SQL_tabla_completa = "COPY ($SQL_profesores) to stdout WITH CSV HEADER";
$SQL_profesores .= "$limite_reg
                    OFFSET $reg_inicio;";
$profesores = consulta_sql($SQL_profesores);
//echo("<!-- $SQL_profesores --");

$SQL_profes_horas = "SELECT sum(horas_semanal) FROM cursos AS c1 LEFT JOIN prog_asig AS pa1 ON pa1.id=c1.id_prog_asig WHERE id_fusion IS NULL AND c1.ano=$ano AND semestre=1 AND id_profesor=u.id";
$SQL_profes_SIES = "SELECT 'R' AS \"TIPO_DOCUMENTO\",
                           split_part(u.rut,'-',1) AS \"NUM_DOCUMENTO\",
                           split_part(u.rut,'-',2) AS \"DV\",
                           translate(upper(split_part(trim(u.apellido),' ',1)),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS \"AP_PATERNO\",
                           translate(upper(split_part(trim(u.apellido),' ',2)),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS \"AP_MATERNO\",
                           translate(upper(trim(u.nombre)),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS \"NOMBRES\",
                           CASE u.sexo WHEN 'f' THEN 'M' WHEN 'm' THEN 'H' END AS \"SEXO\",
                           to_char(u.fec_nac,'DD-MM-YYYY') AS \"FECHA_NACIMIENTO\",
                           p.cod_sies AS \"NACIONALIDAD\",
						   CASE ga.nombre 
                                WHEN 'Doctor'   THEN 1
                                WHEN 'Magister' THEN 2
                                WHEN 'Profesional' THEN 3
                                WHEN 'Licenciado' THEN 4
                                WHEN 'Técnico Nivel Superior' THEN 5
                                WHEN 'Técnico Nivel Medio' THEN 6
                                WHEN 'Licencia Media' THEN 7
                                WHEN 'No tiene' THEN 8
                           END AS \"NIVEL_FORMACION_ACADEMICO\",
                           translate(upper(u.grado_acad_nombre),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS \"NOMBRE_TITULO_O_GRADO\",
                           translate(upper(u.grado_acad_universidad),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS \"NOMBRE_INSTITUCION_OBT_TITULO\",
                           p2.cod_sies AS \"PAIS_OBTENCION_TIT_O_GRADO\",
                           to_char(u.grado_acad_fecha,'DD-MM-YYYY') AS \"FECHA_OBT_TIT_O_GRADO\",
                           '' AS \"NIVEL_FORMACION_ESPECIALIDAD\",
                           '' AS \"NOMBRE_ESPECIALIDAD\",
                           '' AS \"NOMBRE_INST_OBT_ESPECIALIDAD\",
                           '' AS \"PAIS_OBTENCION_ESPECIALIDAD\",
                           '' AS \"FECHA_OBTENCION_ESPECIALIDAD\",
                           translate(upper(u.funcion::text),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS \"PRINCIPAL_CARGO_ACADEMICO\",
                           u.id_cargo_normalizado_sies AS \"CARGO_NORMALIZADO\",
                           'ESCUELA' AS \"NIVEL_SUPERIOR_ADSCRIPCION\",
                           '' AS \"NIVEL_SECUNDARIO_ADSCRIPCION\",
                           'SANTIAGO' AS \"COMUNA_MAYOR_FUNCION\",
                           (SELECT translate(upper(carrera),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') FROM vista_cursos WHERE ano=$ANO AND semestre=1 AND id_profesor=u.id ORDER BY carrera LIMIT 1) AS \"NOMBRE_PRINCIPAL_PROGRAMA\",
                           ($SQL_profes_horas) AS \"TOTAL_HORAS_PRINCIPAL_PROGRAMA\",
                           'SANTIAGO' AS \"COMUNA_PRINCIPAL_PROGRAMA\",
                           CASE WHEN u.horas_planta IS NOT NULL AND ($SQL_profes_horas) > 0 THEN u.horas_planta + ceil((($SQL_profes_horas)-coalesce(u.horas_planta_docencia,0))*1.25)
                                WHEN u.horas_planta IS NOT NULL THEN u.horas_planta 
                           END AS \"NUM_HORAS_PLANTA\",
                           CASE WHEN u.horas_plazo_fijo IS NOT NULL THEN u.horas_plazo_fijo END AS \"NUM_HORAS_CONTRATA\",
                           CASE WHEN coalesce(u.horas_planta,0)+coalesce(u.horas_plazo_fijo,0)>0 THEN 0
                                WHEN coalesce(u.horas_planta,0)+coalesce(u.horas_plazo_fijo,0)+coalesce(u.horas_honorarios,0)=0 THEN ceil(($SQL_profes_horas)*1.25)
                                WHEN u.horas_honorarios IS NOT NULL THEN u.horas_honorarios
                           END AS \"NUM_HORAS_HONORARIOS\",                           
                           '' AS \"HORAS_DOCENCIA_PLANTA\",
                           '' AS \"HORAS_DOCENCIA_CONTRATA\",
                           '' AS \"HORAS_DOCENCIA_HONORARIOS\",
                           1 AS \"VIGENCIA\",
                           ga.nombre AS grado_academico,u.categorizacion
                     FROM usuarios AS u
                     LEFT JOIN vista_profesores AS vu USING (id)
                     LEFT JOIN grado_acad       AS ga ON ga.id=u.grado_academico
                     LEFT JOIN pais             AS p  ON p.localizacion=u.nacionalidad
                     LEFT JOIN pais             AS p2 ON p2.localizacion=u.grado_acad_pais
                     WHERE u.id IN ($SQL_cursos_profes)";
$SQL_tabla_completa_SIES = "COPY ($SQL_profes_SIES) to stdout WITH CSV HEADER";

$enlace_nav = "$enlbase=$modulo"
			. "&id_carrera=$id_carrera"
			. "&texto_buscar=$texto_buscar"
			. "&ano=$ano"
			. "&semestre=$semestre"
			. "&categorizacion=$categorizacion"
			. "&grado_acad=$grado_acad"
			. "&funcion=$funcion"
			. "&id_cargo_normalizado_sies=$id_cargo_normalizado_sies"
			. "&ver_datos_contacto=$ver_datos_contacto"
			. "&buscar=$buscar"
			. "&r_inicio";

if (count($profesores) > 0) {
	$SQL_profes = "SELECT count(u.id) AS cant_profes
                   FROM usuarios         AS u
                   LEFT JOIN grado_acad  AS ga ON ga.id=u.grado_academico
                   LEFT JOIN escuelas    AS e  ON e.id=u.id_escuela
                   LEFT JOIN finanzas.profesores_pago AS fpp ON fpp.id_profesor=u.id
                   LEFT JOIN finanzas.inst_financieras AS fif ON fif.codigo=fpp.cod_banco_deposito
	               WHERE true $condicion AND u.id IN ($SQL_cursos_profes)";
	$tot_reg = consulta_sql($SQL_profes);
	$tot_reg = $tot_reg[0]['cant_profes']; 
	$HTML_paginador = "Páginas ".html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}


$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "")      { $cond_carreras .= "AND regimen='$regimen' "; }
$SQL_carreras = "SELECT id,nombre FROM carreras $cond_carreras ORDER BY nombre;";
$carreras = consulta_sql($SQL_carreras);

$funciones = consulta_sql("SELECT * FROM vista_usuarios_funciones");
$cargos_normalizados = consulta_sql("SELECT * FROM docentes_sies_cargos_normalizados ORDER BY id");
$grados_academicos = consulta_sql("SELECT id,nombre FROM grado_acad WHERE id>1 ORDER BY id");
$estados_carpetas  = consulta_sql("SELECT * FROM vista_estado_carpeta_docente");

$_SESSION['enlace_volver'] = "$enlbase=$modulo&id_carrera=$id_carrera&texto_buscar=$texto_buscar&buscar=$buscar&r_inicio=$reg_inicio";

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);

$id_sesion = "SIES_".$_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tabla_completa_SIES = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>T. Completa SIES</small></a>";
$nombre_arch_SIES = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch_SIES,$SQL_tabla_completa_SIES);
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
  <tr valign="top">
    <td class="celdaFiltro">
      Periodo: <br>
      <select name="semestre" onChange="submitform();" class='filtro'>
        <option value="-1">Todos</option>
        <?php echo(select($semestres,$semestre)); ?>
      </select> - 
      <select name="ano" onChange="submitform();" class='filtro'>
        <option value="0">Todos</option>
        <?php echo(select($anos,$ano)); ?>
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
      Carrera/Programa:<br>
      <select name="id_carrera" onChange="submitform();" class='filtro'>
        <option value="">Todas</option>
        <?php echo(select($carreras,$id_carrera)); ?>    
      </select>
    </td>
    <td class="celdaFiltro">
      Grado Académico:<br>
      <select name="grado_acad" onChange="submitform();" class='filtro'>
        <option value="">Todos</option>
        <?php echo(select($grados_academicos,$grado_acad)); ?>    
      </select>
    </td>
    <td class="celdaFiltro">
      Categorización:<br>
      <select name="categorizacion" onChange="submitform();" class='filtro'>
        <option value="">Todos</option>
        <option value="null">* Sin categorización *</option>
        <?php echo(select($CATEG_DOCENTE,$categorizacion)); ?>    
      </select>
    </td>
    <td class="celdaFiltro">
      Función:<br>
      <select name="funcion" onChange="submitform();" class='filtro'>
        <option value="">Todos</option>
        <?php echo(select($funciones,$funcion)); ?>    
      </select>
    </td> 
    <td class="celdaFiltro">
      Cargo Normalizado:<br>
      <select name="id_cargo_normalizado_sies" onChange="submitform();" class='filtro'>
        <option value="">Todos</option>
        <?php echo(select($cargos_normalizados,$id_cargo_normalizado_sies)); ?>    
      </select>
    </td>
    <td class="celdaFiltro">
      Estado Carpeta:<br>
      <select name="estado_carpeta" onChange="submitform();" class='filtro'>
        <option value="-1">Todos</option>
        <?php echo(select($estados_carpetas,$estado_carpeta)); ?>    
      </select>
    </td>
  </tr>
</table>
<table cellpadding="1" border="0" cellspacing="2" width="auto">
  <tr valign="top">
    <td class="celdaFiltro">
      Buscar por nombre o RUT del profesor:<br>
      <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="45" id="texto_buscar" class='boton'>
      <script>document.getElementById("texto_buscar").focus();</script>
      <input type='submit' name='buscar' value='Buscar'>          
      <?php if ($buscar == "Buscar" && $texto_buscar <> "") { echo("<input type='submit' name='buscar' value='Vaciar'>"); } ?>
    </td>
    <td class="celdaFiltro">
      Otras acciones:<br>
      <a href='<?php echo("$enlbase=crear_profesor&id_prog_curso=$id"); ?>' class='boton'>Agregar un Profesor(a)</a>
    </td>
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="10">
      Mostrando <b><?php echo($tot_reg); ?></b> profesor(es) en total, en página(s) de
      <select class='filtro' name="cant_reg" onChange="submitform();">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas
      <?php echo($HTML_paginador); ?>
      <?php echo($boton_tabla_completa); ?>
      <?php echo($boton_tabla_completa_SIES); ?>
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
<?php if ($_SESSION['tipo'] == 0 || $_SESSION['tipo'] == 5) { ?>
    <td class='tituloTabla'>RUT</td>
<?php } ?>
    <td class='tituloTabla'>Profesor</td>
    <td class='tituloTabla'>Grado<br>Académico</td>
    <td class='tituloTabla'>Categorización</td>
    <td class='tituloTabla' width="150">Escuela</td>
<!--    <td class='tituloTabla'>Función</td> -->
    <td class='tituloTabla' width="150">Cargo<br>Normalizado</td>
    <td class='tituloTabla'>AutoEv.?</td>
  </tr>
<?php
	if (count($profesores) > 0) {
		$_verde = "color: #009900;";
		$_rojo  = "color: #ff0000;";

		for ($x=0; $x<count($profesores); $x++) {
			extract($profesores[$x]);

			if ($auto_ev_completa == "Si") { 
				$est_auto_ev_completa = $_verde;
				$auto_ev_completa = " <a title='$fecha_ead'>Si</a>";
			} else {
				$enl="/sgu/encuestas/index.php?modulo=encuestas&arch_encuesta=autoevaluacion_docente&id_profesor=$id_profesor";
				//$auto_ev_completa .= " <a href='$enl' target='_blank' class='enlaces'>[Contestar]</a>";
				$est_auto_ev_completa = $_rojo;
			}
			
			$tr_style = empty($rut) ? "style='background: #F8FF00;'" : ""; 
			
			$HTML_rut_profe = "";
			if ($_SESSION['tipo'] == 0 || $_SESSION['tipo'] == 5) { $HTML_rut_profe = "    <td class='textoTabla' align='right'>$rut</td>"; }

			$HTML_datos_contacto = "";
			if ($ver_datos_contacto == "Si") { 
				$HTML_datos_contacto = "    <td class='textoTabla' width='300'><small>$direccion</small></td>"
									 . "    <td class='textoTabla'><small>$comuna</small></td>"
									 . "    <td class='textoTabla'><small>$region</small></td>"
									 . "    <td class='textoTabla'><small>$telefono</small></td>"
									 . "    <td class='textoTabla'><small>$tel_movil</small></td>"
									 . "    <td class='textoTabla'><small>$email_personal</small></td>";
			}
			
			$enl = "$enlbase=ver_profesor&id_profesor=$id_profesor&ano=$ano&semestre=$semestre&id_carrera=$id_carrera";
			$enlace = "<a class='enlitem' href='$enl'>";
			echo("  <tr class='filaTabla' $tr_style onClick=\"window.location='$enl';\">"
			    ."    <td class='textoTabla' align='right'>$id_profesor</td>"
			    .$HTML_rut_profe
			    ."    <td class='textoTabla'>$apellidos<br>$nombres</td>"
			    ."    <td class='textoTabla' width='150'>$grado_acad<br><small>$grado_acad_nombre</small></td>"
			    ."    <td class='textoTabla' align=''center'>$categorizacion</td>"
			    ."    <td class='textoTabla'><small>$escuela</small></td>"
//			    ."    <td class='textoTabla'>$funcion</td>"
			    ."    <td class='textoTabla'><small>$cargo_normalizado_sies</small></td>"
			    ."    <td class='textoTabla' style='$est_auto_ev_completa' align='center'>$auto_ev_completa</td>"
			    .$HTML_datos_contacto
			    ."  </tr>\n");			
		}
	} else {
		echo("<td class='textoTabla' colspan='5'>"
		    ."  No hay registros para los criterios de búsqueda/selección"
		    ."</td>\n");
	}
?>
</table>
</form>


<!-- Fin: <?php echo($modulo); ?> -->

<?php
/* Incluye horas adicionales de docencia en profes de planta
$SQL_profes_SIES = "SELECT split_part(u.rut,'-',1) AS rut, split_part(u.rut,'-',2) AS dv,
                           upper(split_part(u.apellido,' ',1))::char(30) AS ape_pat,upper(split_part(u.apellido,' ',2))::char(30) AS ape_mat,
                           upper(u.nombre)::char(60) AS nombre,
                           CASE u.sexo WHEN 'f' THEN 'M' WHEN 'm' THEN 'H' END AS genero,
                           to_char(u.fec_nac,'DD-MM-YYYY') AS fec_nac,
                           upper(p.nombre) AS nacionalidad,
                           $ANO-(SELECT min(ano) FROM cursos WHERE id_profesor=u.id)+1 AS antiguedad,
                           upper(u.funcion::text) AS principal_cargo,
                           'ESCUELA' AS nombre_facultad,                                   
                           upper(vu.escuela) AS principal_unidad_acad,13 AS region_principal_unidad_acad,
                           (SELECT upper(carrera) FROM vista_cursos WHERE ano=$ANO AND semestre=1 AND id_profesor=u.id ORDER BY carrera LIMIT 1) AS principal_carrera,                                   
                           ($SQL_profes_horas) AS horas_semanal_principal_carrera,
                           'SANTIAGO' AS ciudad_principal_carrera,'' AS segunda_carrera,'' AS horas_segunda_carrera,'' AS ciudad_segunda_carrera,
                           CASE ga.nombre 
                                WHEN 'Doctor'   THEN 1
                                WHEN 'Magister' THEN 2
                                WHEN 'Profesional' THEN 4
                                WHEN 'Licenciado' THEN 5
                                WHEN 'No tiene' THEN 8
                           END AS nivel_academico,
                           upper(u.grado_acad_nombre) AS grado_acad_nombre,
                           upper(u.grado_acad_universidad) AS grado_acad_universidad,
                           upper(p2.nombre) AS grado_acad_pais,
                           to_char(u.grado_acad_fecha,'DD-MM-YYYY') AS grado_acad_fecha,
                           CASE WHEN u.horas_plazo_fijo IS NOT NULL THEN u.horas_plazo_fijo-coalesce(u.horas_plazo_fijo_docencia,0)+($SQL_profes_horas) END AS horas_contrata,
                           CASE WHEN u.horas_planta IS NOT NULL THEN u.horas_planta-coalesce(u.horas_planta_docencia,0)+($SQL_profes_horas) END AS horas_planta,
                           CASE WHEN coalesce(u.horas_planta,0)+coalesce(u.horas_plazo_fijo,0)>0 THEN 0
                                WHEN coalesce(u.horas_planta,0)+coalesce(u.horas_plazo_fijo,0)+coalesce(u.horas_honorarios_docencia,0)=0 THEN ($SQL_profes_horas)
                                WHEN u.horas_honorarios IS NOT NULL THEN u.horas_honorarios-coalesce(u.horas_honorarios_docencia,0)+($SQL_profes_horas) 
                           END AS horas_honorarios,
                           ga.nombre AS grado_academico
                     FROM usuarios AS u
                     LEFT JOIN vista_profesores AS vu USING (id)
                     LEFT JOIN grado_acad       AS ga ON ga.id=u.grado_academico
                     LEFT JOIN pais             AS p  ON p.localizacion=u.nacionalidad
                     LEFT JOIN pais             AS p2 ON p2.localizacion=u.grado_acad_pais
                     WHERE u.id IN (SELECT id_profesor FROM cursos WHERE ano=$ano AND semestre=$semestre)";

$SQL_profes_SIES = "SELECT split_part(u.rut,'-',1) AS rut, split_part(u.rut,'-',2) AS dv,
                           upper(split_part(u.apellido,' ',1))::char(30) AS ape_pat,upper(split_part(u.apellido,' ',2))::char(30) AS ape_mat,
                           upper(u.nombre)::char(60) AS nombre,
                           CASE u.sexo WHEN 'f' THEN 'M' WHEN 'm' THEN 'H' END AS genero,
                           to_char(u.fec_nac,'DD-MM-YYYY') AS fec_nac,
                           upper(p.nombre) AS nacionalidad,
                           $ANO-(SELECT min(ano) FROM cursos WHERE id_profesor=u.id)+1 AS antiguedad,
                           upper(u.funcion::text) AS principal_cargo,
                           'ESCUELA' AS nombre_facultad,                                   
                           upper(vu.escuela) AS principal_unidad_acad,13 AS region_principal_unidad_acad,
                           (SELECT upper(carrera) FROM vista_cursos WHERE ano=$ANO AND semestre=1 AND id_profesor=u.id ORDER BY carrera LIMIT 1) AS principal_carrera,                                   
                           ($SQL_profes_horas) AS horas_semanal_principal_carrera,
                           'SANTIAGO' AS ciudad_principal_carrera,'' AS segunda_carrera,'' AS horas_segunda_carrera,'' AS ciudad_segunda_carrera,
                           CASE ga.nombre 
                                WHEN 'Doctor'   THEN 1
                                WHEN 'Magister' THEN 2
                                WHEN 'Profesional' THEN 4
                                WHEN 'Licenciado' THEN 5
                                WHEN 'No tiene' THEN 8
                           END AS nivel_academico,
                           upper(u.grado_acad_nombre) AS grado_acad_nombre,
                           upper(u.grado_acad_universidad) AS grado_acad_universidad,
                           upper(p2.nombre) AS grado_acad_pais,
                           to_char(u.grado_acad_fecha,'DD-MM-YYYY') AS grado_acad_fecha,
                           CASE WHEN u.horas_plazo_fijo IS NOT NULL THEN u.horas_plazo_fijo END AS horas_contrata,
                           CASE WHEN u.horas_planta IS NOT NULL AND ($SQL_profes_horas) > 0 THEN u.horas_planta + ceil((($SQL_profes_horas)-coalesce(u.horas_planta_docencia,0))*1.25)
                                WHEN u.horas_planta IS NOT NULL THEN u.horas_planta 
                           END AS horas_planta,
                           CASE WHEN coalesce(u.horas_planta,0)+coalesce(u.horas_plazo_fijo,0)>0 THEN 0
                                WHEN coalesce(u.horas_planta,0)+coalesce(u.horas_plazo_fijo,0)+coalesce(u.horas_honorarios,0)=0 THEN ceil(($SQL_profes_horas)*1.25)
                                WHEN u.horas_honorarios IS NOT NULL THEN u.horas_honorarios
                           END AS horas_honorarios,
                           ga.nombre AS grado_academico,u.categorizacion
                     FROM usuarios AS u
                     LEFT JOIN vista_profesores AS vu USING (id)
                     LEFT JOIN grado_acad       AS ga ON ga.id=u.grado_academico
                     LEFT JOIN pais             AS p  ON p.localizacion=u.nacionalidad
                     LEFT JOIN pais             AS p2 ON p2.localizacion=u.grado_acad_pais
                     WHERE u.id IN ($SQL_cursos_profes)";
*/
?>
