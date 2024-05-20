<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_contrato = $_REQUEST['id_contrato'];
$id_cobro    = $_REQUEST['id_cobro'];

if (!is_numeric($id_contrato) || !is_numeric($id_cobro)) {
	echo(js("location.href='$enlbase=gestion_contratos';"));
	exit;
}

// Glosas que son modificables los vencimientos de los cobros respectivos
$Ids_glosas = "1,2,20,21,22";

$SQL_cobro = "SELECT fecha_venc,fg.nombre AS glosa,monto,date_part('day',fecha_venc) AS dia_venc,
                     to_char(fecha_venc,'DD-tmMon-YYYY') AS fec_venc
              FROM finanzas.cobros      AS fc
              LEFT JOIN finanzas.glosas AS fg ON fg.id=fc.id_glosa
              WHERE fc.id=$id_cobro AND id_contrato=$id_contrato";
$cobro     = consulta_sql($SQL_cobro);
if (count($cobro) == 0) {
	echo(msje_js("El cobro no pertenece al contrato indicado. Intento de vulnerar el sistema. Esto se registrará e informará al Administrador"));
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$SQL_cobros = "SELECT min(fecha_venc) as primero,max(fecha_venc) as ultimo,
                      ceil((date_part('day',date(max(fecha_venc))+'1 month'::interval - now())/30)::numeric) AS max_meses,
                      to_char(date(min(fecha_venc)+'18 months'::interval),'DD-tmMon-YYYY') AS fec_venc_contrato,
                      date_part('day',date(min(fecha_venc))+'18 months'::interval - now())/30 AS tot_meses_posibles 
               FROM finanzas.cobros 
               WHERE id_contrato=$id_contrato and id_glosa IN ($Ids_glosas)";
//echo($SQL_cobros);
$cobros     = consulta_sql($SQL_cobros);
if ($cobros[0]['tot_meses_posibles'] == 0) {
	echo(msje_js("Este contrato está completamente vencido (fecha límite: {$cobros[0]['fec_venc_contrato']}).\\n"
	            ."No es posible alterar el calendario de pagos"));
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}
	
$SQL_cobros_repact = "SELECT id FROM finanzas.cobros WHERE id_contrato=$id_contrato AND id_glosa=20";
$cobros_repact     = consulta_sql($SQL_cobros_repact);
if (count($cobros_repact) >= 3) {
	echo(msje_js("Este contrato ya tiene 3 excepciones. No es posible alterar nuevamente el calendario de pagos"));
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$SQL_contrato = "SELECT c.*,coalesce(va.id,vp.id) AS id,coalesce(va.rut,vp.rut) AS rut,coalesce(va.nombre,vp.nombre) AS nombre,                        
                        to_char(c.fecha,'DD-MM-YYYY') AS fecha
                 FROM finanzas.contratos AS c
                 LEFT JOIN alumnos       AS al  ON al.id=c.id_alumno
                 LEFT JOIN vista_alumnos AS va  ON va.id=c.id_alumno
                 LEFT JOIN vista_pap     AS vp  ON vp.id=c.id_pap
                 LEFT JOIN pap                  ON pap.id=c.id_pap
                 WHERE c.id=$id_contrato";
$contrato     = consulta_sql($SQL_contrato);
if (count($contrato) == 0) {
	echo(js("location.href='$enlbase=gestion_contratos';"));
	exit;
}


$fec_venc_nueva = $_REQUEST['fec_venc_nueva'];
if ($_REQUEST['guardar'] == "Guardar" && $fec_venc_nueva <> "") {	
	$SQL_cobros_upd = "UPDATE finanzas.cobros
	                   SET fecha_venc = '$fec_venc_nueva'::date,
	                       id_glosa   = CASE id_glosa 
	                                        WHEN 2  THEN 20 
	                                        WHEN 21 THEN 22
	                                        WHEN 1  THEN 1
	                                    END
	                   WHERE id_contrato=$id_contrato AND id=$id_cobro";
	if (consulta_dml($SQL_cobros_upd) > 0) {
		echo(msje_js("Se ha realizado el cambio de vencimiento exitosamente"));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}
}

$max_meses = $cobros[0]['max_meses'];
if ($max_meses == 0) { $max_meses++; }
$dia_venc = $cobro[0]['dia_venc'];
$mes = date("n")+1;
if ($max_meses==1) { $mes--; }

$ano = date("Y");
$meses_prorroga = array();
for ($x=1;$x<=$max_meses;$x++) {
	if ($mes == 13) { $mes = 1; $ano++; }
	$dia_venc_aux = $dia_venc;
	if ($mes == 2 && $dia_venc == 30) { $dia_venc = 28; }
	$fec_venc_nueva = "$ano-$mes-$dia_venc";
	$meses_prorroga = array_merge($meses_prorroga,
	                              array(array('id'=>$fec_venc_nueva, 'nombre'=>$dia_venc."-".substr($meses_palabra[$mes-1]['nombre'],0,3)."-".$ano))
	                             );
	$mes++;
	$dia_venc = $dia_venc_aux;
}

$monto = number_format($cobro[0]['monto'],0,',','.');

?>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>
<br>
<form name="formulario" action="principal_sm.php" method="get">
<input type="hidden" name="modulo"      value="<?php echo($modulo); ?>">
<input type="hidden" name="id_contrato" value="<?php echo($id_contrato); ?>">
<input type="hidden" name="id_cobro"    value="<?php echo($id_cobro); ?>">

<input type="submit" name="guardar" value="Guardar"><br><br>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla">
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Generales del Contrato</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Número:</td>
    <td class='celdaValorAttr'><?php echo($id_contrato); ?></td>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['fecha']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tipo:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['tipo']); ?></td>
    <td class='celdaNombreAttr'>Periodo:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['semestre'].'-'.$contrato[0]['ano']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Alumno</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['rut']); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['id']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($contrato[0]['nombre']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes del Cobro</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha de Vencimiento:</td>
    <td class='celdaValorAttr'><?php echo($cobro[0]['fec_venc']); ?></td>
    <td class='celdaNombreAttr'>Monto:</td>
    <td class='celdaValorAttr'><?php echo($monto); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Glosa:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($cobro[0]['glosa']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nuevo Vencimiento:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name='fec_venc_nueva'>
		<option>-- Seleccione --</option>
        <?php echo(select($meses_prorroga,$fec_venc_nueva)); ?>
      </select><br>
    </td>
  </tr>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
