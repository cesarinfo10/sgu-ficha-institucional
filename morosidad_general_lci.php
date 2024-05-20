<?php


if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$ano              = $_REQUEST['ano'];
$jornada          = $_REQUEST['jornada'];
$regimen          = $_REQUEST['regimen'];

if (empty($ano))     { $ano = $ANO; }
if (empty($jornada)) { $jornada = 'Agreg'; }
if (empty($regimen)) { $regimen = 'PRE'; }


$cond = " ";

if ($ano > 0) { $cond .= "AND date_part('year',c.fecha)=$ano "; }

if ($regimen <> "" && $regimen <> "t") { $cond .= "AND (car.regimen = '$regimen') "; }

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$IDS_GLOSAS = "300,301,302,303";

$estados = array(array('estado'=>"c.estado IS NOT NULL",        'titulo'=>"Morosidad General"),
                 array('estado'=>"c.estado='Sin Firma'",        'titulo'=>"Morosidad Sin Firma"),
                 array('estado'=>"c.estado='Notariado'",        'titulo'=>"Morosidad Notariado"),
                 array('estado'=>"c.estado='En Protesto'",      'titulo'=>"Morosidad en Protesto"),
                 array('estado'=>"c.estado='Protestado'",       'titulo'=>"Morosidad Protestado"),
                 array('estado'=>"c.estado='Cob. Prejudicial'", 'titulo'=>"Morosidad en Cobranza Prejudicial"),
                 array('estado'=>"c.estado='Cob. Judicial'",    'titulo'=>"Morosidad en Cobranza Judicial"));

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

$cci_anos = consulta_sql("SELECT min(date_part('year',fecha)) AS ano_min,max(date_part('year',fecha)) AS ano_max FROM finanzas.convenios_ci");
$ano_min = $cci_anos[0]['ano_min'];
$ano_max = $cci_anos[0]['ano_max'];
$ANOS = array();
for ($x_ano=$ano_max; $x_ano>=$ano_min; $x_ano--) { $ANOS[] = array("id" => $x_ano,"nombre" => $x_ano); }

$_SESSION['enlace_volver'] = "$enlace_nav=$reg_inicio";

$JORNADAS = array(array('id' => "Agreg",'nombre'=>"Agregadas"),
                  array('id' => "Desag",'nombre'=>"Desagradas"));
                                        
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
          Año emisión:<br>
          <select name="ano" onChange="submitform();" class="filtro">
            <option value="t">Todos</option>
            <?php echo(select($ANOS,$ano)); ?>    
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
          <a href="<?php echo("$enlbase=morosidad_general"); ?>" class="boton">Pagerés Colegiatura</a>
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
		   . "  <td class='tituloTabla'>Convenios</td>\n"
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
	$SQL_convenios_ci_pactados = "SELECT car.nombre AS carrera,a.jornada,sum(monto) AS monto,c.id
							      FROM finanzas.convenios_ci AS c
							      LEFT JOIN finanzas.cobros  AS cob ON c.id=cob.id_convenio_ci
							      LEFT JOIN alumnos          AS a ON a.id=c.id_alumno
							      LEFT JOIN carreras         AS car ON car.id=a.carrera_actual
							      $condicion AND id_glosa IN ($IDS_GLOSAS) AND fecha_venc<now()::date AND NOT c.nulo
							      GROUP BY c.id,car.nombre,a.jornada";

	$SQL_convenios_ci_morosos = "SELECT car.nombre AS carrera,a.jornada,sum(monto-coalesce(monto_abonado,0)) AS morosidad_monto,c.id
	                             FROM finanzas.convenios_ci AS c
	                             LEFT JOIN finanzas.cobros  AS cob ON c.id=cob.id_convenio_ci
							     LEFT JOIN alumnos          AS a ON a.id=c.id_alumno
							     LEFT JOIN carreras         AS car ON car.id=a.carrera_actual
							     $condicion AND id_glosa IN ($IDS_GLOSAS) AND NOT pagado AND fecha_venc<now()::date  AND NOT c.nulo
							     GROUP BY c.id,car.nombre,a.jornada";

	if ($jornada == "Agreg") {
		$SQL_pactados = "SELECT carrera,sum(monto) as monto
						 FROM ($SQL_convenios_ci_pactados) AS c
						 GROUP BY carrera
						 ORDER BY carrera;";

		$SQL_morosidad = "SELECT carrera,sum(morosidad_monto) as morosidad_monto,count(id) AS cant_alumnos
						  FROM ($SQL_convenios_ci_morosos) AS c
						  GROUP BY carrera
						  ORDER BY carrera;";
	} else {
		$SQL_pactados = "SELECT carrera,jornada,sum(monto) as monto
						  FROM ($SQL_convenios_ci_pactados) AS c
						  GROUP BY carrera,jornada
						  ORDER BY carrera,jornada;";
						  
		$SQL_morosidad = "SELECT carrera,jornada,sum(morosidad_monto) as morosidad_monto,count(id) AS cant_alumnos
						  FROM ($SQL_convenios_ci_morosos) AS c
						  GROUP BY carrera,jornada
						  ORDER BY carrera,jornada;";
	}
	//echo($SQL_morosidad."<br><br>");

	$pactados      = consulta_sql($SQL_pactados);
	$morosos       = consulta_sql($SQL_morosidad);
}
?>
