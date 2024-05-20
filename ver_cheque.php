<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_cheque = $_REQUEST['id_cheque'];
if (!is_numeric($id_cheque)) {
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$SQL_cheque = "SELECT p.id AS id_pago,CASE WHEN p.nro_boleta IS NOT NULL THEN 'B' WHEN p.nro_factura IS NOT NULL THEN 'F' END AS tipo_docto,
                       coalesce(p.nro_boleta,p.nro_factura) AS nro_docto,to_char(p.fecha,'DD-MM-YYYY') AS fecha_docto,vpr.rut AS rut_alumno,
                       u.nombre_usuario AS cajero,to_char(ch.fecha_venc,'DD-MM-YYYY') AS fecha_venc,ch.monto,ch.nombre_emisor,ch.rut_emisor,ch.telefono_emisor,
                       if.nombre AS banco,ch.monto,ch.numero,ch.id AS id_cheque,
                       CASE WHEN ch.depositado THEN 'Si' ELSE 'No' END AS depositado,
                       CASE ch.protestado WHEN true THEN 'Si' WHEN false THEN 'No' ELSE 'N/D' END AS protestado,
                       CASE ch.aclarado   WHEN true THEN 'Si' WHEN false THEN 'No' ELSE 'N/D' END AS aclarado,
                       coalesce(to_char(ch.fecha_deposito,'DD-MM-YYYY'),'#N/D') AS fec_deposito,
                       coalesce(to_char(ch.fecha_protesto,'DD-MM-YYYY'),'#N/D') AS fec_protesto,
                       coalesce(to_char(ch.fecha_aclaracion,'DD-MM-YYYY'),'#N/D') AS fec_aclaracion,
                       coalesce(to_char(ch.fecha_pago,'DD-MM-YYYY'),'#N/D') AS fec_pago
                FROM finanzas.cheques AS ch
                LEFT JOIN finanzas.inst_financieras AS if ON if.codigo=ch.cod_inst_finan
                LEFT JOIN finanzas.pagos AS p             ON p.id=ch.id_pago
                LEFT JOIN vista_pagos_rut AS vpr          ON p.id=vpr.id
                LEFT JOIN vista_usuarios AS u             ON u.id=p.id_cajero
                WHERE ch.id=$id_cheque 
                ORDER BY ch.fecha_venc DESC ";
$cheque = consulta_sql($SQL_cheque);
if (count($cheque) == 0) {
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
} else {
	$nro_docto = "<a href='$enlbase_sm=ver_pago&id_pago={$cheque[0]['id_pago']}' class='enlaces'>{$cheque[0]['tipo_docto']}/".number_format($cheque[0]['nro_docto'],0,",",".")."</a>";	
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<table cellpadding="1" border="0" cellspacing="2" width="auto" style="margin-top: 5px">
  <tr>
    <td class="celdaFiltro">
      Acciones:<br>
      <a href='<?php echo("$enlbase_sm=editar_cheque&id_cheque=$id_cheque"); ?>' class="boton">Editar</a>
      <a href='<?php echo("$enlbase_sm=cheques_estado&id_cheque=$id_cheque&protestado=Si"); ?>' class="boton">Protestar</a>
      <a href='<?php echo("$enlbase_sm=cheques_aclarar&id_cheque=$id_cheque"); ?>' class="boton">Aclarar</a>
      <a href='<?php echo("$enlbase_sm=cheques_reemplazar&id_cheque=$id_cheque"); ?>' class="boton">Reemplazar</a>
      <a href='#' onClick="history.back();" class="boton">Volver</a>
    </td>
  </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes del Cheque</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Banco:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($cheque[0]['banco']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Número:</td>
    <td class='celdaValorAttr'><?php echo(sprintf("%'.09d\n",$cheque[0]['numero'])); ?></td>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><?php echo($cheque[0]['fecha_venc']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Monto:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo(number_format($cheque[0]['monto'],0,",",".")); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre Emisor:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($cheque[0]['nombre_emisor']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT Emisor:</td>
    <td class='celdaValorAttr'><?php echo($cheque[0]['rut_emisor']); ?></td>
    <td class='celdaNombreAttr'>Tél. Emisor:</td>
    <td class='celdaValorAttr'><?php echo($cheque[0]['telefono_emisor']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha de Depósito:</td>
    <td class='celdaValorAttr'><?php echo($cheque[0]['fec_deposito']); ?></td>
<?php if ($cheque[0]['protestado'] == "Si") { ?>
    <td class='celdaNombreAttr'>Fecha de Protesto:</td>
    <td class='celdaValorAttr'><?php echo($cheque[0]['fec_protesto']); ?></td>
<?php } elseif  ($cheque[0]['protestado'] == "No") { ?>
    <td class='celdaNombreAttr'>Fecha de Pago:</td>
    <td class='celdaValorAttr'><?php echo($cheque[0]['fec_pago']); ?></td>
<?php } ?>
  </tr>
<?php if ($cheque[0]['aclarado'] == "Si") { ?>	  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de Aclaración del Cheque</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><?php echo($cheque[0]['fec_aclaracion']); ?></td>
    <td class='celdaNombreAttr'>Operador:</td>
    <td class='celdaValorAttr'><?php echo($cheque[0]['usuario_aclaracion']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Comentarios:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($cheque[0]['comentarios_aclaracion']); ?></td>
  </tr>
  <tr>
<?php } ?>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Boleta/Factura</td></tr>
  <tr>
    <td class='celdaNombreAttr' style='color: #7F7F7F'>ID:</td>
    <td class='celdaValorAttr' style='color: #7F7F7F'><?php echo($cheque[0]['id_pago']); ?></td>
    <td class='celdaNombreAttr'>Nº:</td>
    <td class='celdaValorAttr'><?php echo($nro_docto.$nulo); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cajero:</td>
    <td class='celdaValorAttr'><?php echo($cheque[0]['cajero']); ?></td>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><?php echo($cheque[0]['fecha_docto']); ?></a></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($cheque[0]['rut_alumno']); ?></td>
  </tr>
</table>

<!-- Fin: <?php echo($modulo); ?> -->
