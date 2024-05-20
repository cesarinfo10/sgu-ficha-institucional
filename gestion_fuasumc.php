<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");
include("validar_modulo.php");
$ids_carreras = $_SESSION['ids_carreras'];

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) { $cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if ($reg_inicio=="") { $reg_inicio = 0; }

$texto_buscar      = $_REQUEST['texto_buscar'];
$buscar            = $_REQUEST['buscar'];
$ano_academico     = $_REQUEST['ano_academico'];
$id_carrera        = $_REQUEST['id_carrera'];
$jornada           = $_REQUEST['jornada'];
$semestre_cohorte  = $_REQUEST['semestre_cohorte'];
$mes_cohorte       = $_REQUEST['mes_cohorte'];
$cohorte           = $_REQUEST['cohorte'];
$estado            = $_REQUEST['estado'];
$moroso_financiero = $_REQUEST['moroso_financiero'];
$admision          = $_REQUEST['admision'];
$regimen           = $_REQUEST['regimen'];
$matriculado       = $_REQUEST['matriculado'];

//if (empty($_REQUEST['matriculado'])) { $matriculado = "t"; }
if (empty($_REQUEST['ano_academico'])) { $ano_academico = $ANO_MATRICULA; }
if (empty($_REQUEST['cohorte'])) { $cohorte = 0; }
if (empty($_REQUEST['semestre_cohorte'])) { $semestre_cohorte = 0; }
if (empty($_REQUEST['mes_cohorte'])) { $mes_cohorte = 0; }
if (empty($_REQUEST['estado'])) { $estado = -1; }
if (empty($_REQUEST['moroso_financiero'])) { $moroso_financiero = -1; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }
if (empty($cond_base)) { $cond_base = "true"; }

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

	if ($ano_academico > 0) { $condicion .= "AND (fuas.ano = $ano_academico) "; }

	if ($cohorte > 0) { $condicion .= "AND (a.cohorte = '$cohorte') "; }

	if ($semestre_cohorte > 0) { $condicion .= "AND (a.semestre_cohorte = $semestre_cohorte) "; }

	if ($mes_cohorte > 0) { $condicion .= "AND (a.mes_cohorte = $mes_cohorte) "; }
	 
	if ($estado <> "-1") { $condicion .= "AND (fuas.estado = '$estado') "; }

	if ($moroso_financiero <> "-1") { $condicion .= "AND (a.moroso_financiero = '$moroso_financiero') "; }
	
	if ($id_carrera <> "") { $condicion .= "AND (a.carrera_actual = '$id_carrera') "; }

	if ($jornada <> "") { $condicion .= "AND (a.jornada = '$jornada') "; }

	if ($admision <> "") { $condicion .= "AND (a.admision = '$admision') "; }

	if ($regimen <> "" && $regimen <> "t") { $condicion .= "AND (c.regimen = '$regimen') "; }

	if ($matriculado == "t") { $condicion .= "AND (m.id_alumno IS NOT NULL) "; } 
	elseif ($matriculado == "f") { $condicion .= "AND (m.id_alumno IS NULL) "; }
	
}

if (!empty($ids_carreras) && empty($id_carrera)) {
	$condicion .= " AND carrera_actual IN ($ids_carreras) ";
}

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_ing_gf = "SELECT sum(ing_liq_mensual_prom) FROM dae.fuas_grupo_familiar WHERE id_fuas=fuas.id";
$SQL_cant_gf = "SELECT count(id) FROM dae.fuas_grupo_familiar WHERE id_fuas=fuas.id";

$SQL_fuas = "SELECT fuas.id,fuas.ano,a.id AS id_alumno,a.rut,a.nombres,apellidos,c.alias||'-'||a.jornada AS carrera,
	                semestre_cohorte||'-'||cohorte AS cohorte,a.mes_cohorte,fuas.estado,
	                to_char(greatest(fecha_creacion,fecha_presentacion,fecha_validacion,fecha_rechazo),'DD-tmMon-YYYY') as fecha_estado,
	                fuas.email,fuas.telefono,fuas.tel_movil,ne.nombre AS nivel_educ,fuas.estado_civil,
	                CASE WHEN fuas.enfermo_cronico THEN 'Si' ELSE 'No' END AS enfermo_cronico,fuas.nombre_enfermedad,
                    fuas.pertenece_pueblo_orig,CASE WHEN fuas.acred_pert_pueblo_orig THEN 'Si' ELSE 'No' END AS acred_pert_pueblo_orig,
                    fuas.cat_ocupacional,act.nombre AS cat_ocupacional_nombre,
                    CASE WHEN fuas.jefe_hogar THEN 'Si' ELSE 'No' END AS jefe_hogar,fuas.ing_liq_mensual_prom,
                    fuas.domicilio_grupo_fam,com.nombre AS comuna_grupo_fam,reg.nombre AS region_grupo_fam,tenencia_dom_grupo_fam,
                    round((coalesce(($SQL_ing_gf),0) + fuas.ing_liq_mensual_prom)/(($SQL_cant_gf) + 1),0) AS ingreso_percapita,
                    puntaje_socioeconomico,puntaje_notas,puntaje_sit_financiera,puntaje_comp_cervantino,
                    beca_otorgada
             FROM dae.fuas
             LEFT JOIN alumnos            AS a   ON a.id=fuas.id_alumno
             LEFT JOIN carreras           AS c   ON c.id=a.carrera_actual
             LEFT JOIN comunas            AS com ON com.id=fuas.comuna_grupo_fam
             LEFT JOIN regiones           AS reg ON reg.id=fuas.region_grupo_fam
             LEFT JOIN dae.nivel_estudios AS ne  ON ne.id=nivel_educ
             LEFT JOIN dae.actividades    AS act ON act.id=fuas.cat_ocupacional
             $condicion
             ORDER BY fuas.fecha_creacion DESC ";
//echo($SQL_fuas);
$SQL_tabla_completa = "COPY ($SQL_fuas) to stdout WITH CSV HEADER";
$SQL_fuas .= "$limite_reg OFFSET $reg_inicio";
$fuas = consulta_sql($SQL_fuas);

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

if (count($fuas) > 0) {
	$SQL_total_fuas =  "SELECT count(a.id) AS total_fuas 
                        FROM dae.fuas
                        LEFT JOIN alumnos            AS a   ON a.id=fuas.id_alumno
                        LEFT JOIN carreras           AS c   ON c.id=a.carrera_actual
                        LEFT JOIN comunas            AS com ON com.id=fuas.comuna_grupo_fam
                        LEFT JOIN regiones           AS reg ON reg.id=fuas.region_grupo_fam
                        LEFT JOIN dae.nivel_estudios AS ne  ON ne.id=nivel_educ
                        LEFT JOIN dae.actividades    AS act ON act.id=fuas.cat_ocupacional
                        $condicion";
	$total_fuas = consulta_sql($SQL_total_fuas);
	$tot_reg = $total_fuas[0]['total_fuas'];
	
	$HTML_paginador = "Páginas ".html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}

$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "")      { $cond_carreras .= "AND regimen='$regimen' "; }

$SQL_carreras = "SELECT id,nombre||' ('||alias||')' AS nombre FROM carreras $cond_carreras ORDER BY nombre;";
$carreras = consulta_sql($SQL_carreras);

$SQL_al_estados = "SELECT id,nombre FROM al_estados WHERE nombre NOT IN ('Moroso') ORDER BY id;";
$al_estados = consulta_sql($SQL_al_estados);

$ESTADOS = consulta_sql("SELECT * FROM vista_fuas_estados");

$cohortes = $anos;

$SEMESTRES_COHORTES = array(array("id"=>1,"nombre"=>1),
                            array("id"=>2,"nombre"=>2));

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

$ANOS_ACAD = array();
for($ano=date("Y")+1;$ano>=2018;$ano--) { $ANOS_ACAD = array_merge($ANOS_ACAD,array(array('id'=>$ano,'nombre'=>$ano))); }

$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);

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
          Año Acad.: <br>
          <select class="filtro" name="ano_academico" onChange="submitform();">
            <?php echo(select($ANOS_ACAD,$ano_academico)); ?>    
          </select>
        </td>
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
            <?php echo(select($ESTADOS,$estado)); ?>
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
          <?php echo("<a href='$enlbase_sm=fuasumc_crear' id='sgu_fancybox_medium' class='boton'>Nueva Postulación</a>"); ?>
        </td>
        <td class="celdaFiltro">
          Tablas de Puntaje:<br>
          <small>
          <?php echo("<a href='$enlbase_sm=fuasumc_puntaje_notas' id='sgu_fancybox_small' class='boton'><small>Notas</small></a>"); ?>
          <?php echo("<a href='$enlbase_sm=fuasumc_puntaje_socioeconomico' id='sgu_fancybox_small' class='boton'><small>Socio-Económico</small></a>"); ?>
          <?php echo("<a href='$enlbase_sm=fuasumc_puntaje_sit_financiera' id='sgu_fancybox_small' class='boton'><small>Situación Financiera</small></a>"); ?>
          <?php echo("<a href='$enlbase_sm=fuasumc_puntaje_becaumc' id='sgu_fancybox_small' class='boton'><small>Beca UMC (acumulativo)</small></a>"); ?>
          </small>
        </td>
      </tr>
    </table>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="7">
      Mostrando <b><?php echo($tot_reg); ?></b> postulaciones en total, en página(s) de
      <select class='filtro' name="cant_reg" onChange="submitform();">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas
    </td>
    <td class="texto" align="right" colspan="7">
      <?php echo($HTML_paginador); ?>
      <?php echo($boton_tabla_completa); ?>
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' rowspan='2'>N°<br>[Año]</td>
    <td class='tituloTabla' rowspan='2'><small>RUT<br>Nombre</small></td>
    <td class='tituloTabla' rowspan='2'><small>Carrera<br>Cohorte</small></td>
    <td class='tituloTabla' rowspan='2'>Estado</td>
    <td class='tituloTabla' rowspan='2'><small>Enf. Crónico<br>Enfermedad</small></td>
    <td class='tituloTabla' rowspan='2'><small>Ingreso<br>Percápita</small></td>
    <td class='tituloTabla' colspan="4"><small>Puntaje Obtenido</small></td>
    <td class='tituloTabla' rowspan='2'><small>Beca<br>asignada</small></td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'><small><small>Notas</small></small></td>
    <td class='tituloTabla'><small><small>Socio<br>Económico</small></small></td>
    <td class='tituloTabla'><small><small>Situación<br>Financiera</small></small></td>
<!--    <td class='tituloTabla'><small><small>Compromiso<br>Cervantino</small></small></td> -->
    <td class='tituloTabla'><small><small>TOTAL</small></small></td>
  </tr>
<?php
	$HTML_fuas = "";
	if (count($fuas) > 0) {
		for ($x=0;$x<count($fuas);$x++) {
			extract($fuas[$x]);
			
			$enl = "$enlbase=fuasumc_ver&id_fuas=$id&id_alumno=$id_alumno";
			$enlace = "a class='enlitem' href='$enl'";
			
			if ($moroso_financiero == "t") { $estado .= " <sup>(M)</sup>"; }
			
			if ($mes_cohorte <> "") { $mes_cohorte = "(".substr($meses_palabra[$mes_cohorte-1]['nombre'],0,3).")"; }
			
			if ($beca_otorgada <> "") { $beca_otorgada .= "%"; }
			$ingreso_percapita = number_format($ingreso_percapita,0,',','.');
			$puntaje_total = $puntaje_socioeconomico + $puntaje_notas + $puntaje_sit_financiera;
			
			$HTML_fuas .= "  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n"
			           . "    <td class='textoTabla' align='center'>$id<br>[$ano]</td>\n"
			           . "    <td class='textoTabla'>$rut<br><small>$apellidos<br>$nombres</small></td>\n"
			           . "    <td class='textoTabla' align='center'>$carrera<br>$cohorte $mes_cohorte</td>\n"
			           . "    <td class='textoTabla' align='center'>$estado<br><small>$fecha_estado</small></td>\n"
			           . "    <td class='textoTabla'><a title='$nombre_enfermedad'>$enfermo_cronico</a></td>\n"
			           . "    <td class='textoTabla' align='right'>$$ingreso_percapita</td>\n"
			           . "    <td class='textoTabla' align='center'>$puntaje_notas</td>\n"
			           . "    <td class='textoTabla' align='center'>$puntaje_socioeconomico</td>\n"
			           . "    <td class='textoTabla' align='center'>$puntaje_sit_financiera</td>\n"
			           //. "    <td class='textoTabla' align='center'>$puntaje_comp_cervantino</td>\n"
			           . "    <td class='textoTabla' align='center'>$puntaje_total</td>\n"
			           . "    <td class='textoTabla' align='center'>$beca_otorgada</td>\n"
			            . "  </tr>\n";
		}
	} else {
		$HTML_fuas = "  <tr>"
		              . "    <td class='textoTabla' colspan='12'>"
		              . "      No hay registros para los criterios de búsqueda/selección"
		              . "    </td>\n"
		              . "  </tr>";
	}
	echo($HTML_fuas);
?>
</table><br>
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
	$("#sgu_fancybox_medium").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 900,
		'height'			: 750,
		'maxHeight'			: 750,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 850,
		'height'			: 400,
		'maxHeight'			: 400,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});
</script>

<!-- Fin: <?php echo($modulo); ?> -->

