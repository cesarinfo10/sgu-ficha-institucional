<?php


if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$semestre         = $_REQUEST['semestre'];
$ano              = $_REQUEST['ano'];
$tipo             = $_REQUEST['tipo'];
$tipo_alumno      = $_REQUEST['tipo_alumno'];
$id_carrera       = $_REQUEST['id_carrera'];
$jornada          = $_REQUEST['jornada'];
$regimen          = $_REQUEST['regimen'];

if (empty($ano))      { $ano = $ANO; }
if (empty($semestre)) { $semestre = null; }
if (empty($jornada))  { $_REQUEST['jornada'] = $jornada = "Agreg"; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }


$cond = " ";

if ($id_carrera <> "") { $cond .= "AND c.id_carrera=$id_carrera "; }

if ($jornada == "D" || $jornada == "V") { $cond .= "AND c.jornada='$jornada' "; }

if ($ano > 0) { $cond .= "AND c.ano=$ano "; }

if ($tipo == "Anual" || $tipo == "Modular") { $semestre = null; }

if (!is_null($semestre)) { $cond .= "AND c.semestre=$semestre "; }

if ($tipo <> "" && $tipo <> "0") { $cond .= "AND c.tipo='$tipo' "; }

if ($tipo_alumno == "N") { $cond .= "AND c.id_pap IS NOT NULL "; }
if ($tipo_alumno == "A") { $cond .= "AND c.id_alumno IS NOT NULL "; }

if ($regimen <> "" && $regimen <> "t") { $cond .= "AND (car.regimen = '$regimen') "; }

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$IDS_GLOSAS = "2,3,20,21,22,31,10003";
//$IDS_GLOSAS = "2,20";

$estados = array(array('estado'=>"estado IS NOT NULL",     'titulo'=>"Morosidad General"),
                 array('estado'=>"estado IN ('E','Z')",             'titulo'=>"Morosidad Alumnos Regulares"),
                 array('estado'=>"estado IN ('S','R','A')",'titulo'=>"Morosidad Alumnos Desertados (Suspendidos, Retirados y Abandonados)"));
$HTML = array();
for ($x=0;$x<count($estados);$x++) {
	$condicion = "WHERE {$estados[$x]['estado']} " . $cond;
	$pactados = $morosos = "";
	morosidad_sql($pactados,$morosos);

	$flujo = array();
	genera_morosidad_carreras($pactados, $morosos, $flujo);
	$HTML[$x] = genera_html_morosidad_carreras($flujo,$estados[$x]['titulo']);
}

$cohortes = $anos;

$SEMESTRES = array(array("id"=>0,"nombre"=>0),
                   array("id"=>1,"nombre"=>1),
                   array("id"=>2,"nombre"=>2));

$_SESSION['enlace_volver'] = "$enlace_nav=$reg_inicio";

$JORNADAS = array_merge($JORNADAS,array(array('id' => "Agreg",'nombre'=>"Agregadas"),
                                        array('id' => "Desag",'nombre'=>"Desagradas")));
                                        
$REGIMENES = consulta_sql("SELECT * FROM regimenes");

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<div class="texto" style='margin-top: 5px;'>
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Periodo matrícula:<br>
          <select name="semestre" onChange="submitform();" class="filtro">
            <option value=""></option>
            <?php echo(select($SEMESTRES,$semestre)); ?>    
          </select>
          - 
          <select name="ano" onChange="submitform();" class="filtro">
            <option value="0"></option>
            <?php echo(select($anos_contratos,$ano)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Tipo:<br>
          <select name="tipo" onChange="submitform();" class="filtro">
            <option value="0">Todos</option>
            <?php echo(select($tipos_contratos,$tipo)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Alumnos:<br>
          <select name="tipo_alumno" onChange="submitform();" class="filtro">
            <option value="">Todos</option>
            <?php echo(select($tipos_alumnos,$tipo_alumno)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Jornada:<br>
          <select name="jornada" onChange="submitform();" class="filtro">
            <?php echo(select($JORNADAS,$jornada)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Régimen:<br>
          <select name="regimen" onChange="submitform();" class="filtro">
            <option value="t">Todos</option>
            <?php echo(select($REGIMENES,$regimen)); ?>
          </select>          
        </td>
        <td class="celdaFiltro">
          Otros:<br>
          <a href="<?php echo("$enlbase=morosidad_general_lci"); ?>" class="boton">Créditos Solidarios</a>
        </td>
      </tr>
    </table>  
</div>
</form>
<?php
	for ($x=0;$x<count($HTML);$x++) {
		echo($HTML[$x]."<br>");
	}
?>
<br>
<!-- Fin: <?php echo($modulo); ?> -->
<?php

function genera_morosidad_carreras($pactados,$morosos,&$flujo) {
	$y = 0;
	for ($x=0;$x<count($pactados);$x++) {
		$flujo[$x]['carrera'] = $pactados[$x]['carrera'];
		$flujo[$x]['jornada'] = $pactados[$x]['jornada'];
		$flujo[$x]['pactado'] = $pactados[$x]['monto'];
		$flujo[$x]['cant_alumnos'] = 0;
		if ($flujo[$x]['carrera'] == $morosos[$y]['carrera'] && $flujo[$x]['jornada'] == $morosos[$y]['jornada']) {
			$flujo[$x]['morosidad_monto'] = $morosos[$y]['morosidad_monto'];
			$flujo[$x]['cant_alumnos']    = $morosos[$y]['cant_alumnos'];
			$y++;
		}
		$flujo[$x]['morosidad_rel']   = $flujo[$x]['morosidad_monto'] / $flujo[$x]['pactado'];
	}
}

function genera_html_morosidad_carreras($flujo,$titulo) {
	$total_morosidad = $total_pactado = 0;
	$HTML2 = "<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla'>\n"
		   . "<tr class='filaTituloTabla'><td class='tituloTabla' colspan='6'>$titulo</td></tr>\n"
		   . "<tr class='filaTituloTabla'>\n"
		   . "  <td class='tituloTabla' rowspan='2'>Carrera</td>\n"
		   . "  <td class='tituloTabla' rowspan='2'>Jornada</td>\n"
		   . "  <td class='tituloTabla' colspan='4'>Morosidad Acumulada</td>\n"
		   . "</tr>\n"
		   . "<tr class='filaTituloTabla'>\n"
		   . "  <td class='tituloTabla'>Contratos</td>\n"
		   . "  <td class='tituloTabla'>Monto</td>\n"
		   . "  <td class='tituloTabla'>Porc.</td>\n"
		   . "  <td class='tituloTabla'>Imp. Rel.</td>\n"
		   . "</tr>\n";
	
	
	for ($x=0;$x<count($flujo);$x++) {
		$total_morosidad += $flujo[$x]['morosidad_monto'];
		$total_alumnos   += $flujo[$x]['cant_alumnos'];
		$total_pactado   += $flujo[$x]['pactado'];
	}
	
	for ($x=0;$x<count($flujo);$x++) {
		$jornada = "";
		if ($flujo[$x]['jornada'] == "D") { $jornada = "Diurna"; } else { $jornada = "Vespertina"; }
		if ($_REQUEST['jornada'] == "Agreg") { $jornada = "Ambas"; }
		$HTML2 .= "<tr>\n"
			   .  "  <td class='textoTabla'>{$flujo[$x]['carrera']}</td>\n"
			   .  "  <td class='textoTabla'>$jornada</td>\n"
			   .  "  <td class='textoTabla' align='right'>{$flujo[$x]['cant_alumnos']}</td>\n"
			   .  "  <td class='textoTabla' align='right'>".number_format($flujo[$x]['morosidad_monto'],0,',','.')."</td>\n"
			   .  "  <td class='textoTabla' align='right'>".number_format($flujo[$x]['morosidad_rel']*100,2,',','.')."%</td>\n"
			   .  "  <td class='textoTabla' align='right'>".number_format(($flujo[$x]['morosidad_monto']/$total_morosidad)*100,2,',','.')."%</td>\n"
			   .  "</tr>\n";
	}
	$HTML2 .= "<tr>\n"
	       .  "  <td class='textoTabla' colspan='2' align='right'><b>Total:</b></td>\n"
	       .  "  <td class='textoTabla' align='right'><b>$total_alumnos</b></td>\n"
	       .  "  <td class='textoTabla' align='right'><b>".number_format($total_morosidad,0,',','.')."</b></td>\n"
	       .  "  <td class='textoTabla' align='right'><b>".number_format(($total_morosidad/$total_pactado)*100,2,',','.')."%</b></td>\n"
	       .  "</tr>\n";
	
	$HTML2 .= "</table>";
	return $HTML2;
}

function morosidad_sql(&$pactados,&$morosos) {
	global $condicion,$IDS_GLOSAS,$jornada;
	$SQL_contratos_pactados = "SELECT car.nombre AS carrera,c.jornada,sum(monto) AS monto,c.id
							   FROM finanzas.contratos   AS c
							   LEFT JOIN finanzas.cobros AS cob ON c.id=cob.id_contrato
							   LEFT JOIN carreras        AS car ON car.id=c.id_carrera
							   $condicion AND id_glosa IN ($IDS_GLOSAS) AND fecha_venc<now()::date
							   GROUP BY c.id,car.nombre,c.jornada";

	$SQL_contratos_morosos = "SELECT car.nombre AS carrera,c.jornada,sum(monto-coalesce(monto_abonado,0)) AS morosidad_monto,c.id
							  FROM finanzas.contratos   AS c
							  LEFT JOIN finanzas.cobros AS cob ON c.id=cob.id_contrato
							  LEFT JOIN carreras        AS car ON car.id=c.id_carrera
							  $condicion AND id_glosa IN ($IDS_GLOSAS) AND NOT pagado AND fecha_venc<now()::date
							  GROUP BY c.id,car.nombre,c.jornada";

	if ($jornada == "Agreg") {
		$SQL_pactados = "SELECT carrera,sum(monto) as monto
						 FROM ($SQL_contratos_pactados) AS c
						 GROUP BY carrera
						 ORDER BY carrera;";

		$SQL_morosidad = "SELECT carrera,sum(morosidad_monto) as morosidad_monto,count(id) AS cant_alumnos
						  FROM ($SQL_contratos_morosos) AS c
						  GROUP BY carrera
						  ORDER BY carrera;";
	} else {
		$SQL_pactados = "SELECT carrera,jornada,sum(monto) as monto
						  FROM ($SQL_contratos_pactados) AS c
						  GROUP BY carrera,jornada
						  ORDER BY carrera,jornada;";
						  
		$SQL_morosidad = "SELECT carrera,jornada,sum(morosidad_monto) as morosidad_monto,count(id) AS cant_alumnos
						  FROM ($SQL_contratos_morosos) AS c
						  GROUP BY carrera,jornada
						  ORDER BY carrera,jornada;";
	}
	//echo($SQL_morosidad."<br><br>");

	$pactados      = consulta_sql($SQL_pactados);
	$morosos       = consulta_sql($SQL_morosidad);
}
?>
