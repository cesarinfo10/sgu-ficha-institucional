<?php


if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");
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

$estados = array(array('estado'=>"estado IS NOT NULL",     'titulo'=>"Morosidad General"           ,'enl'=>"1"),
                 array('estado'=>"estado='E'",             'titulo'=>"Morosidad Alumnos Regulares" ,'enl'=>"E"),
                 array('estado'=>"estado IN ('S','R','A')",'titulo'=>"Morosidad Alumnos Desertados",'enl'=>"D"));
$HTML = array();
for ($x=0;$x<count($estados);$x++) {
	$condicion = "WHERE {$estados[$x]['estado']} " . $cond;
	$morosos = "";
	morosidad_sql($morosos);

	$HTML[$x] = genera_html_morosidad_cuotas($morosos,$estados[$x]['titulo'],$estados[$x]['enl']);
}

$cohortes = $anos;

$SEMESTRES = array(array("id"=>0,"nombre"=>0),
                   array("id"=>1,"nombre"=>1),
                   array("id"=>2,"nombre"=>2));

$_SESSION['enlace_volver'] = "$enlace_nav=$reg_inicio";

$cond_carreras = "";
if ($regimen <> "") { $cond_carreras = "WHERE regimen='$regimen'"; }
$carreras = consulta_sql("SELECT id,nombre FROM carreras $cond_carreras ORDER BY nombre;");

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div><br>
<div class="texto">
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          <div align='left'>Periodo matrícula:</div>
          <select name="semestre" onChange="submitform();" class='filtro'>
            <option value=""></option>
            <?php echo(select($SEMESTRES,$semestre)); ?>    
          </select>
          - 
          <select name="ano" onChange="submitform();" class='filtro'>
            <option value="0"></option>
            <?php echo(select($anos_contratos,$ano)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Tipo:</div>
          <select name="tipo" onChange="submitform();" class='filtro'>
            <option value="0">Todos</option>
            <?php echo(select($tipos_contratos,$tipo)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Alumnos:</div>
          <select name="tipo_alumno" onChange="submitform();" class='filtro'>
            <option value="">Todos</option>
            <?php echo(select($tipos_alumnos,$tipo_alumno)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Carrera:</div>
          <select name="id_carrera" onChange="submitform();" class='filtro'>
            <option value="">Todas</option>
            <?php echo(select($carreras,$id_carrera)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Jornada:</div>
          <select name="jornada" onChange="submitform();" class='filtro'>
            <option value="">Ambas</option>
            <?php echo(select($JORNADAS,$jornada)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Régimen:</div>
          <select name="regimen" onChange="submitform();" class='filtro'>
            <option value="t">Todos</option>
            <?php echo(select($REGIMENES,$regimen)); ?>
          </select>
        </td>
      </tr>
    </table>
</div>
</form>
<table>
  <tr>
<?php
	for ($x=0;$x<count($HTML);$x++) {
		echo("<td valign='top'>{$HTML[$x]}</td>");
	}
?>
  </tr>
</table>
<br>
<!-- Fin: <?php echo($modulo); ?> -->
<?php

function genera_html_morosidad_cuotas($morosos,$titulo,$enl_estado) {
	global $enlbase,$semestre,$ano,$tipo,$tipo_alumno,$id_carrera,$jornada,$regimen;
	$total_morosidad = 0;
	$HTML2 = "<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla'>\n"
		   . "<tr class='filaTituloTabla'><td class='tituloTabla' colspan='4'>$titulo</td></tr>\n"
		   . "<tr class='filaTituloTabla'>\n"
		   . "  <td class='tituloTabla'>Cantidad<br>Cuotas</td>\n"
		   . "  <td class='tituloTabla'>Cantidad<br>Contratos</td>\n"
		   . "  <td class='tituloTabla'>Monto</td>\n"
		   . "  <td class='tituloTabla'>Acumulado</td>\n"
		   . "</tr>\n";
	
	for ($x=0;$x<count($morosos);$x++) {
		$enl = "$enlbase=gestion_contratos&semestre=$semestre&ano=$ano&tipo=$tipo&tipo_alumno=$tipo_alumno&id_carrera=$id_carrera&jornada=$jornada&regimen=$regimen&estado=$enl_estado&cant_cuotas_morosas={$morosos[$x]['cuotas_morosas']}";
		$total_morosidad += $morosos[$x]['monto_morosidad_cuota'];
		$total_contratos += $morosos[$x]['cant_contratos'];
		$monto_morosidad_cuota = money_format("%(10#10.0n",$morosos[$x]['monto_morosidad_cuota']);
		$HTML2 .= "<tr class='filaTabla' onClick=\"window.location='$enl';\">\n"
			   .  "  <td class='textoTabla' align='center'>{$morosos[$x]['cuotas_morosas']}</td>\n"
			   .  "  <td class='textoTabla' align='right'>{$morosos[$x]['cant_contratos']}</td>\n"
			   .  "  <td class='textoTabla' align='right'>$monto_morosidad_cuota</td>\n"
			   .  "  <td class='textoTabla' align='right'>".money_format("%(10#10.0n",$total_morosidad)."</td>\n"
			   .  "</tr>\n";
	}
	
	$total_morosidad = money_format("%(10#10.0n",$total_morosidad);
	
	$HTML2 .= "<tr>\n"
	       .  "  <td class='textoTabla' align='right'><b>Total:</b></td>\n"
	       .  "  <td class='textoTabla' align='right'><b>$total_contratos</b></td>\n"
	       .  "  <td class='textoTabla' align='right'><b>$total_morosidad</b></td>\n"
	       .  "</tr>\n";
	
	$HTML2 .= "</table>";
	return $HTML2;
}

function morosidad_sql(&$morosos) {
	global $condicion,$IDS_GLOSAS,$jornada;
	
	$SQL_morosos = "SELECT cuotas_morosas,count(c.id) AS cant_contratos,sum(monto_moroso) AS monto_morosidad_cuota
	                FROM vista_contratos AS c 
	                LEFT JOIN carreras   AS car ON car.id=c.id_carrera 
	                $condicion AND cuotas_morosas>0
	                GROUP BY cuotas_morosas
	                ORDER BY cuotas_morosas";

	$morosos     = consulta_sql($SQL_morosos);
}
?>
