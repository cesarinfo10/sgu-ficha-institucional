<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

$ids_carreras = $_SESSION['ids_carreras'];
$id_usuario   = $_SESSION['id_usuario'];
 
include("validar_modulo.php");

$texto_buscar = $_REQUEST['texto_buscar'];
$buscar       = $_REQUEST['buscar'];
$id_carrera   = $_REQUEST['id_carrera'];
$regimen      = $_REQUEST['regimen'];
$periodo      = $_REQUEST['periodo'];

if ($_REQUEST['periodo'] == "")  { $periodo = $SEMESTRE."-".$ANO; }
list($semestre,$ano) = explode("-",$periodo);

if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }

$condicion = "";
if (!empty($texto_buscar)) {
	$textos_buscar = explode(" ",sql_regexp($texto_buscar));
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= " AND (lower(profesor) ~* '$cadena_buscada')";
	}
}
if (is_numeric($id_carrera)) { $condicion .= " AND id_carrera=$id_carrera "; }
if (!empty($ids_carreras))   { $condicion .= " AND id_carrera IN ($ids_carreras) "; }

$SQL_ids_carreras_pregrado = "SELECT id FROM carreras WHERE regimen='$regimen'";

$SQL_profesores = "SELECT vc.id_profesor,initcap(u.apellido||' '||u.nombre) AS profesor
                   FROM vista_cursos AS vc
                   LEFT JOIN usuarios   AS u  ON u.id=vc.id_profesor
                   WHERE id_carrera IN ($SQL_ids_carreras_pregrado) AND ano=$ano AND semestre=$semestre 
                     AND id_profesor IS NOT NULL $condicion
                   GROUP BY vc.id_profesor,u.apellido,u.nombre
                   ORDER BY u.apellido,u.nombre";
$profesores = consulta_sql($SQL_profesores);
if (count($profesores) > 0) {
	$tot_reg = consulta_sql("SELECT count(id_profesor) AS cant_profes FROM (SELECT DISTINCT ON (id_profesor) id_profesor FROM vista_cursos WHERE id_carrera IN ($SQL_ids_carreras_pregrado) AND ano=$ano AND semestre=$semestre AND id_profesor IS NOT NULL $condicion) AS profes;");
	$tot_reg = $tot_reg[0]['cant_profes']; 

	$SQL_autoev_doc = "SELECT ead.*
	                   FROM encuestas.autoevaluacion_docente_historica AS ead
	                   LEFT JOIN vista_cursos AS c ON (c.ano=ead.ano AND c.semestre=ead.semestre AND c.id_profesor=ead.id_profesor)
	                   WHERE ead.ano=$ano AND ead.semestre=$semestre $condicion
	                   ORDER BY ead.id_profesor";
	if (($ano==2012 && $semestre==2) || $ano>2012) {
		$stat_autoev_doc = enc_autoev_conteo_ranking2(consulta_sql($SQL_autoev_doc));
	} else {
		$stat_autoev_doc = enc_autoev_conteo_ranking(consulta_sql($SQL_autoev_doc));
	}

	$SQL_ev_doc = "SELECT eed.*
	               FROM encuestas.evaluacion_docente_historica AS eed
	               LEFT JOIN vista_cursos AS vc ON (vc.ano=eed.ano AND vc.semestre=eed.semestre AND vc.id_profesor=eed.id_profesor)
	               WHERE eed.ano=$ano AND eed.semestre=$semestre $condicion
	               ORDER BY eed.id_profesor";
	$stat_ev_doc = enc_evdoc_conteo_ranking(consulta_sql($SQL_ev_doc));
	
	$SQL_ev_est = "SELECT c.id_profesor,ee.*
	               FROM encuestas.estudiantil_historica AS ee
	               LEFT JOIN vista_cursos AS c ON c.id=ee.id_curso
	               WHERE ee.ano=$ano AND ee.semestre=$semestre AND id_profesor IS NOT NULL 
	               ORDER BY id_profesor,id_curso";
	$stat_ev_est = enc_est_conteo_ranking(consulta_sql($SQL_ev_est));
}

for ($x=0;$x<count($profesores);$x++) {
	$profesores[$x]['p_ev_est'] = $profesores[$x]['p_ev_est_casos'] = 0;
	for ($y=0;$y<count($stat_ev_est);$y++) {
		if ($profesores[$x]['id_profesor'] == $stat_ev_est[$y]['id_profesor']) {
			$profesores[$x]['p_ev_est'] = $stat_ev_est[$y]['total'];
			$profesores[$x]['p_ev_est_casos'] = $stat_ev_est[$y]['casos'];
		}
	}
}

for ($x=0;$x<count($profesores);$x++) {
	$profesores[$x]['p_autoev_doc'] = 0;
	for ($y=0;$y<count($stat_autoev_doc);$y++) {
		if ($profesores[$x]['id_profesor'] == $stat_autoev_doc[$y]['id_profesor']) {
			$profesores[$x]['p_autoev_doc'] = $stat_autoev_doc[$y]['total'];
		}		
	}
}

for ($x=0;$x<count($profesores);$x++) {
	$profesores[$x]['p_ev_doc'] = 0;
	for ($y=0;$y<count($stat_ev_doc);$y++) {
		if ($profesores[$x]['id_profesor'] == $stat_ev_doc[$y]['id_profesor']) {
			$profesores[$x]['p_ev_doc'] = $stat_ev_doc[$y]['total'];
		}		
	}
}

$tot_autoev_doc = (count($stat_autoev_doc)/$tot_reg)*100;
$tot_ev_doc     = (count($stat_ev_doc)/$tot_reg)*100;

//if ($ids_carreras <> "") { $condicion_carreras = "WHERE id IN ($ids_carreras)"; }
//$SQL_carreras = "SELECT id,nombre FROM carreras $condicion_carreras ORDER BY nombre;";
//$carreras = consulta_sql($SQL_carreras);

$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "")      { $cond_carreras .= "AND regimen='$regimen' "; }

$SQL_carreras = "SELECT id,nombre FROM carreras $cond_carreras ORDER BY nombre;";
$carreras = consulta_sql($SQL_carreras);

$_SESSION['enlace_volver'] = "$enlbase=$modulo&id_carrera=$id_carrera&texto_buscar=$texto_buscar&buscar=$buscar&reg_inicio=$reg_inicio";

$PERIODOS = array();
$z=0;
for ($x=$ANO;$x>=2007;$x--) {	
	for ($y=2;$y>=1;$y--) {
		$PERIODOS = array_merge($PERIODOS,array($z=>array("id"=>"$y-$x","nombre"=>"$y-$x")));
	}
}
$REGIMENES = consulta_sql("SELECT * FROM regimenes");

$SQL_evest = "SELECT * FROM encuestas.vista_estudiantil_hist WHERE ano=$ano AND semestre=$semestre";
$SQL_tc_evest = "COPY ($SQL_evest) to stdout WITH CSV HEADER";
$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tc_est = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>📥 TC Est</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tc_evest);

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>

<div class="texto">
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <table cellpadding="2" border="0" cellspacing="0" width="auto" style='margin-top: 5px'>
      <tr valign="top">
        <td class="celdaFiltro">
          Periodo:<br>
          <select class="filtro" name="periodo" onChange="submitform();">
				<?php
					echo(select($PERIODOS,$periodo));
				?>
          </select>        </td>
        <td class="celdaFiltro">
          Carrera/Programa:<br>
          <select class="filtro" name="id_carrera" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($carreras,$id_carrera)); ?>
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
    <table cellpadding="2" border="0" cellspacing="0" width="auto" style='margin-top: 5px'>
      <tr valign="top">
        <td class="celdaFiltro" width="auto">
          Buscar por nombre del profesor:<br>
          <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="50" class='boton'>
          <input type='submit' name='buscar' value='Buscar'>          
          <?php 
          	if ($buscar == "Buscar" && $texto_buscar <> "") {
          		echo("<input type='submit' name='buscar' value='Vaciar'>");          		
          	};
          ?>
        </td>
      </tr>
    </table>
  </form>
  Mostrando <b><?php echo($tot_reg); ?></b> profesor(es) en total  
</div>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Lugar</td>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>Profesor</td>
    <td class='tituloTabla' valign='bottom'>Auto Ev.<br>30%</td>
    <td class='tituloTabla' valign='bottom'>Ev. Doc. Dir.<br>35%</td>
    <td class='tituloTabla' colspan="2" valign='bottom'><?php echo($boton_tc_est); ?><br>Ev. Doc. Est.<br>35%</td>
    <td class='tituloTabla' valign='bottom'>Puntaje<br>Final</td>
  </tr>
<?php
	if (count($profesores) > 0) {
		$_verde = "color: #009900;";
		$_rojo  = "color: #ff0000;";
		
		for ($x=0; $x<count($profesores); $x++) {
			$profesores[$x]['total_ev'] = ($profesores[$x]['p_autoev_doc']*0.30)
			                            + ($profesores[$x]['p_ev_doc']*0.35)
			                            + ($profesores[$x]['p_ev_est']*0.35);
		}

		//burjuja para ordenar por total_ev
		for ($i=0; $i<count($profesores)-1; $i++) {
			for ($j=$i+1; $j<count($profesores); $j++) {
				if ($profesores[$i]['total_ev'] < $profesores[$j]['total_ev']) {
					$aux            = $profesores[$i];
					$profesores[$i] = $profesores[$j];
					$profesores[$j] = $aux;
				}
			}
		}

		$repr = 0;
		for ($x=0; $x<count($profesores); $x++) {
			extract($profesores[$x]);

			
			if ($profesores[$x]['total_ev'] < 60) {
				$estilo = $_rojo;
				$repr++;
			}
			
			$p_autoev_doc = number_format($p_autoev_doc,1);
			$p_ev_doc     = number_format($p_ev_doc,1);
			$p_ev_est     = number_format($p_ev_est,1);
			$total_ev     = number_format($total_ev,1);
			$lugar        = $x + 1;
			
			$enl      = "$enlbase=resultados_ev_docente_estudiantil&id_profesor=$id_profesor&ano=$ano&semestre=$semestre";
			$p_ev_est = "<a href='$enl' style='$estilo'>$p_ev_est%</a>";
			
			$enl = "$enlbase=ver_profesor&id_profesor=$id_profesor&ano=$ano&semestre=$semestre&id_carrera=$id_carrera";
			$enlace = "<a class='enlitem' href='$enl'>";
			echo("  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n"
			    ."    <td class='textoTabla' align='right' style='$estilo'>".$lugar."º</td>"
			    ."    <td class='textoTabla' style='$estilo'>$id_profesor</td>"
			    ."    <td class='textoTabla' style='$estilo'>$profesor</td>"
			    ."    <td class='textoTabla' align='right' style='$estilo'>$p_autoev_doc%</td>"
			    ."    <td class='textoTabla' align='right' style='$estilo'>$p_ev_doc%</td>"
			    ."    <td class='textoTabla' align='right' style='$estilo'>$p_ev_est</td>"
			    ."    <td class='textoTabla' align='right' style='$estilo'>$p_ev_est_casos</td>"
			    ."    <td class='textoTabla' align='right' style='$estilo'>$total_ev%</td>"
			    ."  </tr>");			
		}
	$stat_autoev = number_format($tot_autoev_doc,1);
	$stat_ev     = number_format($tot_ev_doc,1);

	} else {
		echo("<td class='textoTabla' colspan='5'>"
		    ."  No hay registros para los criterios de búsqueda/selección"
		    ."</td>\n");
	}
?>
</table><br>
<div class="texto">
  Mostrando <b><?php echo($tot_reg); ?></b> profesor(es) en total.<br>
  <!-- de los cuales <b><?php echo($repr); ?></b> están bajo el 60% exigido.<br> -->
  Un <b><?php echo($stat_autoev); ?>%</b> realizó su autoevaluación y 
  un <b><?php echo($stat_ev); ?>%</b> fue evaluado por su dirección de escuela(s)<br>
  <br>
  <b>Auto Ev.:</b> Porcentaje ponderado de la Autoevaluación del Docente<br>
  <b>Ev. Doc. Dir:</b> Porcentaje ponderado de la Evaluación Docente del Director<br>
  <b>Ev. Doc. Est:</b> Porcentaje ponderado de la Evaluación Docente de los estudiantes. Entre parentesis, casos (alumnos) que contestan
</div><br>
<!-- Fin: <?php echo($modulo); ?> -->

