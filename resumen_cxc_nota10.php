<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");
include("validar_modulo.php");


$ano                 = $_REQUEST['ano'];
$estado              = $_REQUEST['estado'];
$regimen             = implode(",",$_REQUEST['regimen']);
$id_escuela          = $_REQUEST['id_escuela'];
$id_carrera          = $_REQUEST['id_carrera'];
$divisor_valores     = $_REQUEST['divisor_valores'];

if (empty($ano))      { $ano = $ANO; }
if ($estado == "")    { $estado = '1'; }
if (empty($_REQUEST['regimen'])) { $regimen = "PRE,PRE-D,POST-T,POST-TD,POST-G,POST-GD"; }
if ($divisor_valores == "") { $divisor_valores = 1000; }

$condicion = "WHERE true
                AND (NOT cob.pagado OR abonado)
				AND c.ano=$ano";

if ($estado <> "") {
	if ($estado == "N")  { $condicion .= " AND c.estado IS NULL "; } 
	elseif ($estado == "1") { $condicion .= " AND c.estado IS NOT NULL "; }
	elseif ($estado == "D") { $condicion .= " AND c.estado IN ('S','R','A') "; }
	elseif ($estado != "0") { $condicion .= " AND c.estado='$estado' "; }
}

$regimen_ = implode("','",explode(",",$regimen));

if (empty($fec_ini_emision) && empty($fec_fin_emision)) { 
	$SQL_contratos_per = "SELECT min(vc.fecha::date) AS fec_ini_emision,max(vc.fecha::date) AS fec_fin_emision 
	                      FROM finanzas.contratos AS vc
                          LEFT JOIN carreras   AS car ON car.id=vc.id_carrera
                          $condicion";
	$contratos_per = consulta_sql($SQL_contratos_per);
	if (count($contratos_per) > 0) {
		extract($contratos_per[0]);
		$fec_ini_pago = $fec_ini_emision;
		$fec_fin_pago = $fec_fin_emision;
	}
}

$REGIMENES = consulta_sql("SELECT id AS id_regimen,nombre AS nombre_regimen FROM regimenes");
$HTML_regimenes = "";
$aRegimen = explode(",",$regimen);
for ($x=0;$x<count($REGIMENES);$x++) {
	$checked = "";
	extract($REGIMENES[$x]);
	if (in_array($id_regimen,$aRegimen)) { $checked = "checked='checked'"; }
	$HTML_regimenes .= "<input style='vertical-align: bottom;' type='checkbox' name='regimen[]' value='$id_regimen' id='$id_regimen' onChange='submitform();' $checked> "
	                .  "<label for='$id_regimen'>$nombre_regimen</label><br>";
}

$DIVISORES = array(array('id'=>1      ,'nombre'=>"en pesos"),
                   array('id'=>1000   ,'nombre'=>"en miles de pesos"),
                   array('id'=>1000000,'nombre'=>"en millones de pesos"));

$ESCUELAS = consulta_sql("SELECT id,nombre FROM escuelas WHERE activa ORDER BY nombre");

$cond_carreras = "";
if ($id_escuela > 0) { $cond_carreras .= " AND c.id_escuela=$id_escuela "; }
if ($regimen <> "") { $cond_carreras .= " AND c.regimen IN ('$regimen_') "; }

$SQL_carreras = "SELECT c.id,c.nombre,r.nombre AS grupo 
                 FROM carreras AS c
				 LEFT JOIN regimenes_ AS r ON r.id=c.regimen
				 WHERE c.activa $cond_carreras 
				 ORDER BY r.orden,c.nombre";
$CARRERAS = consulta_sql($SQL_carreras);

$id_carrera = implode(",",array_column($CARRERAS,"id"));

$condicion .= " AND id_carrera IN ($id_carrera) ";

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<table cellpadding="1" border="0" cellspacing="2" width="auto" style='margin-top: 5px'>
  <tr>
    <td class="celdaFiltro" rowspan="4">
      Régimen:<br>
      <div style='vertical-align: top'><?php echo($HTML_regimenes); ?></div>
    </td>
  </tr>
  <tr>
    <td class="celdaFiltro">
      Año:<br>
      <select class="filtro" name="ano" onChange="submitform();">
        <?php echo(select($anos_contratos,$ano)); ?>
      </select>
    </td>
    <td class="celdaFiltro">
      Estado:<br>
      <select class="filtro" name="estado" onChange="submitform();">
        <option value="0">Todos</option>
        <?php echo(select($estados_contratos,$estado)); ?>    
      </select>
    </td>
    <td class="celdaFiltro">
      Mostrar valores:<br>
      <select name="divisor_valores" onChange="submitform();" class="filtro">
        <?php echo(select($DIVISORES,$divisor_valores)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaFiltro" colspan='3'>
      Escuela:<br>
      <select class="filtro" name="id_escuela" onChange="submitform();">
        <option value="0">-- Todas --</option>
        <?php echo(select($ESCUELAS,$id_escuela)); ?>    
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaFiltro" colspan='3'>
      Carrera/Programa:<br>
      <select class="filtro" name="id_carrera" onChange="submitform();">
        <option value="0">-- Todas --</option>
        <?php echo(select_group($CARRERAS,$id_carrera)); ?>    
      </select>
    </td>
  </tr>
</table>
</form>

<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Valores al <?php echo($fecha_informe); ?></td>
    <td class='tituloTabla' colspan="2"><?php echo($ano); ?></td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='text-align: left'>10.1</td>
    <td class='tituloTabla'>Corriente</td>
    <td class='tituloTabla'>No corriente</td>
  </tr>

  <tr>
    <td class='textoTabla'>Deudores por aranceles</td>
    <td class='textoTabla' style='text-align: right'><?php echo(number_format(deudores_aranceles("corriente"),0,',','.')); ?></td>		
    <td class='textoTabla' style='text-align: right'><?php echo(number_format(deudores_aranceles("nocorriente"),0,',','.')); ?></td>		
  </tr>
  <tr>
    <td class='textoTabla'>Deterioro incobrabilidad aranceles</td>
    <td class='textoTabla' style='text-align: right'><?php echo(number_format(deterioro_aranceles("corriente"),0,',','.')); ?></td>		
    <td class='textoTabla' style='text-align: right'><?php echo(number_format(deterioro_aranceles("nocorriente"),0,',','.')); ?></td>				
  </tr>
  <tr class='filaTituloTabla'>
    <td class='celdaNombreAttr' style='text-align: left'>Total deudores por aranceles</td>
    <td class='celdaNombreAttr'><?php echo(number_format(deudores_aranceles("corriente")+deterioro_aranceles("corriente"),0,',','.')); ?></td>		
    <td class='celdaNombreAttr'><?php echo(number_format(deudores_aranceles("nocorriente")+deterioro_aranceles("nocorriente"),0,',','.')); ?></td>				
  </tr>

  <tr>
    <td class='textoTabla'>Deudores por matricula</td>
    <td class='textoTabla' style='text-align: right'><?php echo(number_format(deudores_matriculas("corriente"),0,',','.')); ?></td>		
    <td class='textoTabla' style='text-align: right'><?php echo(number_format(deudores_matriculas("nocorriente"),0,',','.')); ?></td>				
  </tr>
  <tr>
    <td class='textoTabla' style='text-align: left'>Deterioro Incobrabilidad Matricula</td>
    <td class='textoTabla' style='text-align: right'><?php echo(number_format(deterioro_matriculas("corriente"),0,',','.')); ?></td>		
    <td class='textoTabla' style='text-align: right'><?php echo(number_format(deterioro_matriculas("nocorriente"),0,',','.')); ?></td>			
  </tr>
  <tr class='filaTituloTabla'>
    <td class='celdaNombreAttr' style='text-align: left'>Total deudores por Matricula</td>
    <td class='celdaNombreAttr'><?php echo(number_format(deudores_matriculas("corriente")+deterioro_matriculas("corriente"),0,',','.')); ?></td>		
    <td class='celdaNombreAttr'><?php echo(number_format(deudores_matriculas("nocorriente")+deterioro_matriculas("nocorriente"),0,',','.')); ?></td>				
  </tr>

  <tr>
    <td class='textoTabla'>Deudores por becas estatales</td>
    <td class='textoTabla' style='text-align: right'></td>		
    <td class='textoTabla' style='text-align: right'></td>				
  </tr>
  <tr class='filaTituloTabla'>
    <td class='celdaNombreAttr' style='text-align: left'>Total deudores por becas estatales</td>
    <td class='celdaNombreAttr'></td>		
    <td class='celdaNombreAttr'></td>				
  </tr>

  <tr>
    <td class='textoTabla'>Deudores por gratuidad</td>
    <td class='textoTabla' style='text-align: right'></td>		
    <td class='textoTabla' style='text-align: right'></td>				
  </tr>
  <tr class='filaTituloTabla'>
    <td class='celdaNombreAttr' style='text-align: left'>Total deudores por gratuidad</td>
    <td class='celdaNombreAttr'></td>		
    <td class='celdaNombreAttr'></td>				
  </tr>

  <tr>
    <td class='textoTabla'>Deudores por otros aportes estatales</td>
    <td class='textoTabla' style='text-align:  right'></td>		
    <td class='textoTabla' style='text-align:  right'></td>				
  </tr>
  <tr class='filaTituloTabla'>
    <td class='celdaNombreAttr' style='text-align: left'>Total deudores por otros aportes estatales</td>
    <td class='celdaNombreAttr'></td>		
    <td class='celdaNombreAttr'></td>				
  </tr>

  <tr>
    <td class='textoTabla'>Deudores proyectos estatales</td>
    <td class='textoTabla' style='text-align: right'></td>		
    <td class='textoTabla' style='text-align: right'></td>				
  </tr>
  <tr  class='filaTituloTabla'>
    <td class='celdaNombreAttr' style='text-align: left'>Total deudores proyectos estatales</td>
    <td class='celdaNombreAttr'></td>		
    <td class='celdaNombreAttr'></td>				
  </tr>

  <tr>
    <td class='textoTabla'>Documentos por cobrar</td>
    <td class='textoTabla' style='text-align: right'></td>		
    <td class='textoTabla' style='text-align: right'></td>				
  </tr>
  <tr>
    <td class='textoTabla'>Deterioro incobrabilidad documentos por cobrar</td>
    <td class='textoTabla' style='text-align: right'></td>		
    <td class='textoTabla' style='text-align: right'></td>				
  </tr>
  <tr  class='filaTituloTabla'>
    <td class='celdaNombreAttr' style='text-align: left'>Total documentos por cobrar</td>
    <td class='celdaNombreAttr'></td>		
    <td class='celdaNombreAttr'></td>				
  </tr>

  <tr>
    <td class='textoTabla'>Otros deudores</td>
    <td class='textoTabla' style='text-align:  right'></td>		
    <td class='textoTabla' style='text-align:  right'></td>				
  </tr>
  <tr>
    <td class='textoTabla'>Deterioro incobrabilidad otros deudores</td>
    <td class='textoTabla' style='text-align:  right'></td>		
    <td class='textoTabla' style='text-align:  right'></td>				
  </tr>
  <tr class='filaTituloTabla'>
    <td class='celdaNombreAttr' style='text-align: left'>Total Otros deudores</td>
    <td class='celdaNombreAttr''></td>		
    <td class='celdaNombreAttr'></td>				
  </tr>

  <tr class='filaTituloTabla'>
    <td class='celdaNombreAttr' style='text-align: left'>Total deudores comerciales y otras cuentas por cobrar Neto</td>
    <td class='celdaNombreAttr'></td>		
    <td class='celdaNombreAttr'></td>				
  </tr>
</table>

<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='text-align: left' colspan='2'>10.2</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='text-align: left'>Vencimiento de deudores comerciales y otras cuentas por cobrar</td>
    <td class='tituloTabla'><?php echo($ano); ?></td>
  </tr>

  <tr>
    <td class='textoTabla'>No vencidas corrientes</td>
    <td class='textoTabla' style='text-align: right'></td>
  </tr>
  <tr>
    <td class='textoTabla'>Deterioro incobrabilidad no vencidas corrientes</td>
    <td class='textoTabla' style='text-align: right'></td>
  </tr>
  <tr>
    <td class='textoTabla'>No vencidas no corrientes</td>
    <td class='textoTabla' style='text-align: right'></td>
  </tr>
  <tr>
    <td class='textoTabla'>Deterioro incobrabilidad no vencidas no corrientes</td>
    <td class='textoTabla' style='text-align: right'></td>
  </tr>
  
  <tr class='filaTituloTabla'>
    <td class='celdaNombreAttr' style='text-align: left'>Subtotal No vencidas corrientes</td>
    <td class='celdaNombreAttr'></td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='celdaNombreAttr' style='text-align: left'>Subtotal deterioro incobrabilidad no vencidas no corrientes</td>
    <td class='celdaNombreAttr'></td>
  </tr>
  
  <tr>
    <td class='textoTabla'>Vencidos menor 90 días</td>
    <td class='textoTabla' style='text-align:  right'></td>
  </tr>
  <tr>
    <td class='textoTabla'>Deterioro incobrabilidad vencido menor 90 días</td>
    <td class='textoTabla' style='text-align:  right'></td>
  </tr>
  <tr>
    <td class='textoTabla'>Vencidos entre 91 y 360 días</td>
    <td class='textoTabla' style='text-align:  right'></td>
  </tr>
  <tr>
    <td class='textoTabla'>Deterioro incobrabilidad vencidos entre 91 y 360 días</td>
    <td class='textoTabla' style='text-align:  right'></td>
  </tr>
  <tr>
    <td class='textoTabla'>Vencidos más de 360 días</td>
    <td class='textoTabla' style='text-align:  right'></td>
  </tr>
  <tr>
    <td class='textoTabla'>Deterioro incobrabilidad vencidos más de 360 días</td>
    <td class='textoTabla' style='text-align:  right'></td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='celdaNombreAttr' style='text-align: left'>Subtotal vencidos</td>
    <td class='celdaNombreAttr'></td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='celdaNombreAttr' style='text-align: left'>Subtotal deterioro incobrabilidad vencidos</td>
    <td class='celdaNombreAttr'></td>
  </tr>
  <tr  class='filaTituloTabla'>
    <td class='celdaNombreAttr' style='text-align: left'>Total deudores comerciales y otras cuentas por cobrar Neto</td>
    <td class='celdaNombreAttr'></td>
  </tr>
</table>

<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='text-align: left' colspan='2'>10.3</td>
  </tr>

  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='text-align: left'>Documentos Protestados, en cobranza judicial  o con recuperabilidad incierta</td>
    <td class='tituloTabla'><?php echo($ano); ?></td>
  </tr>

  <tr>
    <td class='textoTabla'>Documentos Protestados</td>
    <td class='textoTabla' style='text-align:  right'></td>
  </tr>
  <tr>
    <td class='textoTabla'>Documentos en cobranza judicial</td>
    <td class='textoTabla' style='text-align:  right'></td>
  </tr>
  <tr>
    <td class='textoTabla'>Documentos con recuperabilidad incierta</td>
    <td class='textoTabla' style='text-align:  right'></td>
  </tr>
  <tr>
    <td class='textoTabla'>Deterioro incobrabilidad documentos protestados, en cobranza judicial o con recuperabilidad incierta</td>
    <td class='textoTabla' style='text-align:  right'></td>
  </tr>
</table>

<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='text-align: left' colspan='2'>10.4</td>
  </tr>
  
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='text-align: left'>Provision de deudores incobrables, corrientes</td>
    <td class='tituloTabla'><?php echo($ano); ?></td>
  </tr>

  <tr>
    <td class='textoTabla'>Saldo inicial</td>
    <td class='textoTabla' style='text-align:  right'></td>
  </tr>
  <tr>
    <td class='textoTabla'>Ajustes</td>
    <td class='textoTabla' style='text-align:  right'></td>
  </tr>
  <tr>
    <td class='textoTabla'>(aumento) disminucion de provision</td>
    <td class='textoTabla' style='text-align:  right'></td>
  </tr>
  <tr>
    <td class='textoTabla' >Castigos</td>
    <td class='textoTabla' style='text-align:  right'></td>
  </tr>
  <tr>
    <td class='textoTabla' >Saldo Final</td>
    <td class='textoTabla' style='text-align:  right'></td>
  </tr>

  <tr><td class='textoTabla' colspan="2">&nbsp;</td></tr>

  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='text-align: left'>Provision de deudores incobrables, No  corrientes</td>
    <td class='tituloTabla'><?php echo($ano); ?></td>
  </tr>

  <tr>
    <td class='textoTabla' >Saldo inicial</td>
    <td class='textoTabla' style='text-align:  right'></td>
  </tr>
  <tr>
    <td class='textoTabla' >Ajustes</td>
    <td class='textoTabla' style='text-align:  right'></td>
  </tr>
  <tr>
    <td class='textoTabla' >(aumento) disminucion de provision</td>
    <td class='textoTabla' style='text-align:  right'></td>
  </tr>
  <tr>
    <td class='textoTabla' >Castigos</td>
    <td class='textoTabla' style='text-align:  right'></td>
  </tr>
  <tr>
    <td class='textoTabla' >Saldo Final</td>
    <td class='textoTabla' style='text-align:  right'></td>
  </tr>
</table>

<?php

function deudores_aranceles($tipo) {
	global $regimen,$ano,$estado,$id_carrera,$condicion,$divisor_valores;

	$cond = "";
	if ($tipo === "corriente") { $cond = " AND fecha_venc <= now()+'1 year'::interval "; }
	if ($tipo === "nocorriente") { $cond = " AND fecha_venc > now()+'1 year'::interval "; }

  	$id_glosa = "2,20";

  	$SQL = "SELECT sum(cob.monto-coalesce(cob.monto_abonado,0)-coalesce(cob.castigo_monto,0)) AS monto
	        FROM finanzas.cobros AS cob
			LEFT JOIN finanzas.contratos AS c ON c.id=cob.id_contrato
			$condicion
			$cond
			  AND cob.id_glosa IN ($id_glosa)";
    $monto = consulta_sql($SQL);
	//echo($SQL);
  	return round($monto[0]['monto']/$divisor_valores,0);
}

function deterioro_aranceles($tipo) {
	global $regimen,$ano,$estado,$id_carrera,$condicion,$divisor_valores;

	$cond = "";
	if ($tipo === "corriente") { $cond = " AND fecha_venc <= now()+'1 year'::interval "; }
	if ($tipo === "nocorriente") { $cond = " AND fecha_venc > now()+'1 year'::interval "; }

  	$id_glosa = "2,20";

  	$SQL = "SELECT sum(coalesce(cob.prov_incob_monto,0)) AS monto
	        FROM finanzas.cobros AS cob
			LEFT JOIN finanzas.contratos AS c ON c.id=cob.id_contrato
			$condicion
			$cond
			  AND cob.id_glosa IN ($id_glosa)";
    $monto = consulta_sql($SQL);
	//echo($SQL);
  	return round(-1*$monto[0]['monto']/$divisor_valores,0);
}

function deudores_matriculas($tipo) {
	global $regimen,$ano,$estado,$id_carrera,$condicion,$divisor_valores;

	$cond = "";
	if ($tipo === "corriente") { $cond = " AND fecha_venc <= now()+'1 year'::interval "; }
	if ($tipo === "nocorriente") { $cond = " AND fecha_venc > now()+'1 year'::interval "; }

  	$id_glosa = "1,10001";

  	$SQL = "SELECT sum(cob.monto-coalesce(cob.monto_abonado,0)-coalesce(cob.castigo_monto,0)) AS monto
	        FROM finanzas.cobros AS cob
			LEFT JOIN finanzas.contratos AS c ON c.id=cob.id_contrato
			$condicion
			$cond
			  AND cob.id_glosa IN ($id_glosa)";
    $monto = consulta_sql($SQL);
	//echo($SQL);
  	return round($monto[0]['monto']/$divisor_valores,0);
}

function deterioro_matriculas($tipo) {
	global $regimen,$ano,$estado,$id_carrera,$condicion,$divisor_valores;

	$cond = "";
	if ($tipo === "corriente") { $cond = " AND fecha_venc <= now()+'1 year'::interval "; }
	if ($tipo === "nocorriente") { $cond = " AND fecha_venc > now()+'1 year'::interval "; }

	$id_glosa = "1,10001";

  	$SQL = "SELECT sum(coalesce(cob.prov_incob_monto,0)) AS monto
	        FROM finanzas.cobros AS cob
			LEFT JOIN finanzas.contratos AS c ON c.id=cob.id_contrato
			$condicion
			$cond
			  AND cob.id_glosa IN ($id_glosa)";
    $monto = consulta_sql($SQL);
	//echo($SQL);
  	return round(-1*$monto[0]['monto']/$divisor_valores,0);
}

?>