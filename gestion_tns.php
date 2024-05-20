<?php
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");
$modulo_destino = "ver_alumno";

$ids_carreras = $_SESSION['ids_carreras'];

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) { $cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if ($reg_inicio=="") { $reg_inicio = 0; }

$texto_buscar      = $_REQUEST['texto_buscar'];
$buscar            = $_REQUEST['buscar'];
$id_carrera        = $_REQUEST['id_carrera'];
$id_malla          = $_REQUEST['id_malla'];
$jornada           = $_REQUEST['jornada'];
$semestre_cohorte  = $_REQUEST['semestre_cohorte'];
$mes_cohorte       = $_REQUEST['mes_cohorte'];
$cohorte           = $_REQUEST['cohorte'];
$estado            = $_REQUEST['estado'];
$ano_titulacion    = $_REQUEST['ano_titulacion'];
$mes_titulacion    = $_REQUEST['mes_titulacion'];
$fec_ini_tit       = $_REQUEST['fec_ini_tit'];
$fec_fin_tit       = $_REQUEST['fec_fin_tit'];
$moroso_financiero = $_REQUEST['moroso_financiero'];
$admision          = $_REQUEST['admision'];
$regimen           = $_REQUEST['regimen'];
$aprob_ant         = $_REQUEST['aprob_ant'];
$matriculado       = $_REQUEST['matriculado'];
$orden             = $_REQUEST['orden'];

if (empty($_REQUEST['cohorte'])) { $cohorte = 0; }
if (empty($_REQUEST['semestre_cohorte'])) { $semestre_cohorte = 0; }
if (empty($_REQUEST['mes_cohorte'])) { $mes_cohorte = 0; }
if (empty($_REQUEST['estado'])) { $estado = -1; }
if (empty($_REQUEST['ano_titulacion'])) { $ano_titulacion = $ANO; $mes_titulacion = -1; }
if (empty($_REQUEST['fec_ini_tit'])) { $fec_ini_tit = date("Y")."-01-01"; }
if (empty($_REQUEST['fec_fin_tit'])) { $fec_fin_tit = date("Y-m-d"); }
if (empty($_REQUEST['moroso_financiero'])) { $moroso_financiero = -1; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }
if (empty($_REQUEST['orden'])) { $orden = "a.salida_int_fecha DESC"; }
if (empty($cond_base)) { $cond_base = "a.salida_int_fecha IS NOT NULL AND a.salida_int_nroreg_libro IS NOT NULL AND salida_int_calif IS NOT NULL"; }

$sem_ant = $ano_ant = 0;
if ($SEMESTRE == 2)     { $sem_ant = 1; $ano_ant = $ANO; }
elseif ($SEMESTRE <= 1) { $sem_ant = 2; $ano_ant = $ANO - 1; }

$condicion = "WHERE $cond_base  ";

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
	$cohorte = $semestre_cohorte = $estado = $id_carrera = $admision = $matriculado = $regimen = null;
} else {

	if ($cohorte > 0) {
		$condicion .= "AND (cohorte = '$cohorte') ";
	}

	if ($semestre_cohorte > 0) {
		$condicion .= "AND (semestre_cohorte = $semestre_cohorte) ";
	}

	if ($mes_cohorte > 0) {
		$condicion .= "AND (mes_cohorte = $mes_cohorte) ";
	}
	 
	if ($estado <> "-1") {
		$condicion .= "AND (estado = '$estado') ";
	}
	 
	if ($ano_titulacion > 0) {
		$condicion .= "AND (date_part('year',a.salida_int_fecha) = $ano_titulacion) ";
		if ($mes_titulacion <> "-1") { $condicion .= "AND (date_part('month',a.salida_int_fecha) = $mes_titulacion) "; }
	} elseif ($ano_titulacion == "-2") {
		if ($fec_ini_tit <> "" && $fec_fin_tit <> "") {
			$condicion .= " AND (a.salida_int_fecha between '$fec_ini_tit'::date AND '$fec_fin_tit'::date) ";
		}
	}

	if ($moroso_financiero <> "-1") {
		$condicion .= "AND (moroso_financiero = '$moroso_financiero') ";
	}
	
	if ($id_carrera <> "") {
		$condicion .= "AND (a.carrera_actual = '$id_carrera') ";
	}
	
	if ($id_malla <> "") {
		$condicion .= "AND (a.malla_actual = '$id_malla') ";
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

	if ($matriculado == "t") {
		$condicion .= "AND (m.id_alumno IS NOT NULL) ";
	} elseif ($matriculado == "f") {
		$condicion .= "AND (m.id_alumno IS NULL) ";
	}
	
	switch ($aprob_ant) {
		case 1:
			$condicion .= " AND (($SQL_tasa_aprob_ant) = 0) ";
			break;
		case 2:
			$condicion .= " AND (($SQL_tasa_aprob_ant) BETWEEN 1 AND 39.9) ";
			break;
		case 3:
			$condicion .= " AND (($SQL_tasa_aprob_ant) BETWEEN 40 AND 100) ";
			break;
	}
}

if (!empty($ids_carreras) && empty($id_carrera)) {
	$condicion .= " AND carrera_actual IN ($ids_carreras) ";
}

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_al_presente = "SELECT count(periodo) AS semestre_presente
                    FROM (SELECT id_alumno,ano||'-'||semestre as periodo 
                          FROM vista_alumnos_cursos 
                          WHERE id_alumno=a.id AND semestre>0 
                          GROUP BY id_alumno,periodo) AS foo 
                    GROUP BY id_alumno";

$SQL_alumnos = "SELECT a.id,trim(a.rut) AS rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre,c.alias||'-'||a.jornada AS carrera,
                       a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.mes_cohorte,ta.nombre AS admision,
                       CASE WHEN estado_tramite IS NOT NULL THEN ae.nombre||'/'||aet.nombre ELSE ae.nombre END AS estado,
                       CASE WHEN m.id_alumno IS NOT NULL THEN 'Si' ELSE 'No' END AS matriculado,moroso_financiero,
                       semestre_egreso||'-'||ano_egreso AS periodo_egreso,
                       (ano_egreso-cohorte+1)*2+CASE WHEN semestre_egreso <= semestre_cohorte THEN -1 ELSE 0 END AS duracion_egreso,
                       to_char(a.fecha_titulacion,'DD-tmMon-YYYY') AS fecha_titulacion,
                       to_char(a.salida_int_fecha,'DD-tmMon-YYYY') AS salida_int_fecha,
                       CASE WHEN a.admision=1 THEN ((ano_egreso-cohorte)+1)*2+(CASE WHEN semestre_egreso <= semestre_cohorte THEN -1 ELSE 0 END)-($SQL_al_presente) ELSE 0 END AS semestres_susp 
                FROM alumnos AS a
                LEFT JOIN carreras AS c ON c.id=a.carrera_actual
                LEFT JOIN al_estados AS ae ON ae.id=a.estado
                LEFT JOIN al_estados AS aet ON aet.id=a.estado_tramite
                LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND semestre=$SEMESTRE AND ano=$ANO)
                LEFT JOIN tipos_admision AS ta ON ta.id=a.admision
                $condicion
                ORDER BY $orden
                $limite_reg
                OFFSET $reg_inicio;";
$alumnos = consulta_sql($SQL_alumnos);

$SQL_ano_ing_orig = "SELECT min(ano) AS ano_ing_orig FROM cargas_academicas AS ca LEFT JOIN convalidaciones AS c ON c.id=ca.id_convalida WHERE ca.id_alumno=a.id";

$SQL_al_SIES = "SELECT 2 AS TIPO_REGISTRO,
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
                       translate(upper(ma.tns_nombre),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS nombre_titulo,
                       '' AS nombre_grado,
                       to_char(a.salida_int_fecha,'DD-MM-YYYY') AS FECHA_OBT_TIT_GRA,
                       CASE WHEN a.admision NOT IN (2,20) THEN ((ano_egreso-cohorte)+1)*2+(CASE WHEN semestre_egreso<=semestre_cohorte THEN -1 ELSE 0 END)-($SQL_al_presente) ELSE 0 END AS N_SEMESTRES_SUSPENSION,
                       a.cohorte AS ANIO_INGRESO_CARRERA_ACTUAL,
                       a.semestre_cohorte AS SEM_INGRESO_CARRERA_ACTUAL,
                       coalesce(($SQL_ano_ing_orig),a.cohorte) AS ANIO_INGRESO_CARRERA_ORIGEN,
                       a.semestre_cohorte AS SEM_INGRESO_CARRERA_ORIGEN,
                       a.ano_egreso AS ANIO_EGRESO,
                       a.semestre_egreso,
                       1 AS estado,
                       a.email,
                       a.nombre_usuario||'@'||dominio_gsuite as email_gsuite
                FROM alumnos AS a
                LEFT JOIN carreras AS c ON c.id=a.carrera_actual
                LEFT JOIN regimenes_ AS r ON r.id=c.regimen
                LEFT JOIN pais     AS p ON p.localizacion=a.nacionalidad
                LEFT JOIN al_estados AS ae ON ae.id=a.estado
                LEFT JOIN mallas     AS ma ON ma.id=a.malla_actual
                LEFT JOIN al_estados AS aet ON aet.id=a.estado_tramite
                LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND m.semestre=$SEMESTRE AND m.ano=$ANO)
                $condicion
                ORDER BY c.alias,a.jornada,a.apellidos,a.nombres"; 
$SQL_tabla_completa_SIES = "COPY ($SQL_al_SIES) to stdout WITH CSV HEADER";

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
            . "&texto_buscar=$texto_buscar"
            . "&buscar=$buscar"
            . "&r_inicio";

if (count($alumnos) > 0) {
	$SQL_total_alumnos =  "SELECT count(a.id) AS total_alumnos 
	                       FROM alumnos AS a 
	                       LEFT JOIN carreras AS c ON c.id=a.carrera_actual 
	                       LEFT JOIN al_estados AS ae ON ae.id=a.estado
	                       LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND semestre=$SEMESTRE AND ano=$ANO)
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
if ($regimen <> "")      { $cond_carreras .= "AND regimen='$regimen' "; }

$SQL_carreras = "SELECT id,nombre FROM carreras $cond_carreras ORDER BY nombre;";
$carreras = consulta_sql($SQL_carreras);

if ($id_carrera>0) { $mallas = consulta_sql("SELECT id,ano AS nombre FROM mallas WHERE id_carrera=$id_carrera ORDER BY nombre"); }

$SQL_al_estados = "SELECT id,nombre FROM al_estados AS ae WHERE nombre NOT IN ('Moroso') ORDER BY id;";
$al_estados = consulta_sql($SQL_al_estados);

$cohortes = $anos;

$ORDENES = array(array('id' => "a.apellidos,a.nombres"           ,'nombre' => "Nombre (asc.)"),
                 array('id' => "a.apellidos DESC,a.nombres DESC" ,'nombre' => "Nombre (desc.)"),
                 array('id' => "a.salida_int_fecha"              ,'nombre' => "Fec. Titulación (asc.)"),
                 array('id' => "a.salida_int_fecha DESC"         ,'nombre' => "Fec. Titulación (desc.)"));

$SEMESTRES_COHORTES = array(array("id"=>1,"nombre"=>1),
                            array("id"=>2,"nombre"=>2));

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

$APROB_ANT = array(array("id" => 1, "nombre" => 'Mala (0%)'),
                   array("id" => 2, "nombre" => 'Regular (1% ~ 39%)'),
                   array("id" => 3, "nombre" => 'Buena (40% ~ 100%)'));

$SQL_anos_titulados = "SELECT DISTINCT ON (id) id,nombre
                       FROM (SELECT date_part('year',salida_int_fecha) AS id,date_part('year',salida_int_fecha) AS nombre 
                             FROM alumnos AS a
                             LEFT JOIN al_estados AS ae ON ae.id=a.estado
                             WHERE $cond_base) AS foo
                       ORDER BY id DESC";
$anos_titulados = consulta_sql($SQL_anos_titulados);
$anos_titulados = array_merge(array(array('id'=>-2,'nombre'=>"Otro")),$anos_titulados);

$id_sesion = "SIES_".$_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tabla_completa_SIES = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa SIES</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa_SIES);

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
          Año Titulación: <br>
          <select class="filtro" name="ano_titulacion" onChange="submitform();">
            <option value="-1">Todos</option>
            <?php echo(select($anos_titulados,$ano_titulacion)); ?>
          </select>
          <?php if ($ano_titulacion > 0) { ?>
          <select class="filtro" name="mes_titulacion" onChange="submitform();">
            <option value="-1">- Mes --</option>
            <?php echo(select($meses_palabra,$mes_titulacion)); ?>
          </select>
          <?php } ?>
          <?php if ($ano_titulacion == -2) { ?>
          <input type="date" placeholder="Fec. ini" name="fec_ini_tit" value="<?php echo($fec_ini_tit); ?>" size="10" class="boton" style='font-size: 9pt'>
          <input type="date" placeholder="Fec. fin" name="fec_fin_tit" value="<?php echo($fec_fin_tit); ?>" size="10" class="boton" style='font-size: 9pt'>
          <script>document.getElementById("fec_ini_tit").focus();</script>
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
          Carrera/Programa:<br>
          <select class="filtro" name="id_carrera" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($carreras,$id_carrera)); ?>
          </select>
        </td>
<?php if ($id_carrera>0) { ?>
        <td class="celdaFiltro">
          Malla:<br>
          <select class="filtro" name="id_malla" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($mallas,$id_malla)); ?>
          </select>
        </td>
<?php } ?>
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
          Orden: <br>
          <select class="filtro" name="orden" onChange="submitform();">
            <?php echo(select($ORDENES,$orden)); ?>
          </select>
        </td>
      </tr>
    </table>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="3">
      Mostrando <b><?php echo($tot_reg); ?></b> alumno(s) en total, en página(s) de
      <select class='filtro' name="cant_reg" onChange="submitform();">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas
    </td>
    <td class="texto" align="right" colspan="20">
      <?php echo($HTML_paginador); ?>
      <?php echo($boton_tabla_completa_SIES); ?>
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' rowspan="2">ID</td>
    <td class='tituloTabla' rowspan="2">RUT</td>
    <td class='tituloTabla' rowspan="2">Nombre</td>
    <td class='tituloTabla' rowspan="2">Carrera</td>
    <td class='tituloTabla' rowspan="2">Estado</td>
    <td class='tituloTabla' rowspan="2">Admisión</td>
    <td class='tituloTabla' rowspan="2">Cohorte</td>
    <td class='tituloTabla' rowspan="2">F. Salida<br>Intermedia</td>
    <td class='tituloTabla' rowspan="2">Sem.<br>Susp.</td>
    <td class='tituloTabla' colspan="2">Egreso</td>
    <td class='tituloTabla' colspan="3">Titulación</td>
  </tr>
  <tr>
    <td class='tituloTabla'>Periodo</td>
    <td class='tituloTabla'>Duración</td> 
    <td class='tituloTabla'>Fecha</td> 
    <td class='tituloTabla'>Duración</td> 
    <td class='tituloTabla'>Oportuna</td> 
  </tr>
<?php
	$HTML_alumnos = "";
	if (count($alumnos) > 0) {
		for ($x=0;$x<count($alumnos);$x++) {
			extract($alumnos[$x]);
			
			$enl = "$enlbase_sm=editar_alumno_egreso&id_alumno=$id&rut=$rut";
			
			if ($moroso_financiero == "t") { $estado .= " <sup>(M)</sup>"; }
			
			if ($mes_cohorte <> "") { $mes_cohorte = "(".substr($meses_palabra[$mes_cohorte-1]['nombre'],0,3).")"; }
			
			if (!empty($duracion)) { $duracion .= " semestres"; }
			
			$enl_id = "$enlbase_sm=ver_alumno&id_alumno=$id&rut=$rut";
			$id = "<a href='$enl_id' id='sgu_fancybox' class='enlaces' title='Ver ficha de estudiante'>$id</a>";
			
			$nombre = "<a class='enlaces' href='$enl' title='Ver/Editar ficha de Titulación' id='sgu_fancybox_small'>$nombre</a>";
			
			$HTML_alumnos .= "  <tr class='filaTabla'>\n"
			               . "    <td class='textoTabla'>$id</td>\n"
			               . "    <td class='textoTabla'>$rut</td>\n"
			               . "    <td class='textoTabla'>$nombre</td>\n"
			               . "    <td class='textoTabla'>$carrera</td>\n"
			               . "    <td class='textoTabla'>$estado</td>\n"
			               . "    <td class='textoTabla'>$admision</td>\n"
			               . "    <td class='textoTabla'>$cohorte $mes_cohorte</td>\n"
			               . "    <td class='textoTabla'>$salida_int_fecha</td>\n"
			               . "    <td class='textoTabla'>$semestres_susp</td>\n"
			               . "    <td class='textoTabla'>$periodo_egreso</td>\n"
			               . "    <td class='textoTabla' align='right'>$duracion_egreso</td>\n"
			               . "    <td class='textoTabla' align='right'>$fecha_titulacion</td>\n"
			               . "    <td class='textoTabla' align='right'>$duracion_tit</td>\n"
			               . "    <td class='textoTabla' align='right'>$oportuna</td>\n"
			               . "  </tr>\n";
		}
	} else {
		$HTML_alumnos = "  <tr>"
		              . "    <td class='textoTabla' colspan='11'>"
		              . "      <br> ** No hay registros para los criterios de búsqueda/selección ** <br><br>"
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
		'autoScale'			: false,
		'autoDimensions'	: true,
		'titleShow'         : false,
		'titlePosition'     : 'inside',
		'transitionIn'		: 'elastic',
		'transitionOut'		: 'elastic',
		'width'				: 1000,
		'height'			: 550,
		'maxHeight'			: 600,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: true,
		'titleShow'         : false,
		'titlePosition'     : 'inside',
		'transitionIn'		: 'elastic',
		'transitionOut'		: 'elastic',
		'width'				: 600,
		'height'			: 780,
		'maxHeight'			: 780,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});
</script>
