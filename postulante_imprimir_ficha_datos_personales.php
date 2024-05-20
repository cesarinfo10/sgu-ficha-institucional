<html>
  <head>
      <meta content="text/html; charset=UTF-8" http-equiv="content-type">
      <link href="sgu.css" rel="stylesheet" type="text/css">
  </head>
</head>
<body>
  <div>
    <h3>Ficha de Antecedentes del Postulante</h3>
  </div>
<?php

include("funciones.php");
$id_pap = $_REQUEST['id_pap'];
$rut    = $_REQUEST['rut'];
echo("<table cellspacing='0' cellpadding='0' border='0'><tr><td>");
include("postulante_ficha_datos_personales.php");
echo("</td>");
$id_foto = "";
$SQL_foto = "SELECT dd.id FROM doctos_digitalizados dd LEFT JOIN doctos_digital_tipos ddt ON dd.id_tipo=ddt.id WHERE rut='$rut' AND ddt.alias='fotos' AND NOT eliminado";
$foto = consulta_sql($SQL_foto);
if (count($foto) > 0) { 
	$id_foto = $foto[0]['id'];
	echo("<td valign='top'><img align='right' src='doctos_digitalizados_ver.php?id=$id_foto' width='150'></td>");
}
echo("</tr></table>");
include("postulante_ficha_datos_escolares_instedsup.php");
$resp_finan = consulta_sql("SELECT * FROM vista_avales WHERE id=(SELECT id_aval FROM pap WHERE id=$id_pap)");
if (count($resp_finan) == 1) {
	extract($resp_finan[0]);
?>
  <table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">
      Antecedentes del Responsable Financiero<br><sup>(Apoderado, Sostenedor o Aval)</sup> 
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($rf_rut); ?></td>
    <td class='celdaNombreAttr'>Parentezco:</td>
    <td class='celdaValorAttr'><?php echo($rf_parentezco); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($rf_nombre); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Estado Civil:</td>
    <td class='celdaValorAttr'><?php echo($rf_estado_civil); ?></td>
    <td class='celdaNombreAttr'>Profesión:</td>
    <td class='celdaValorAttr'><?php echo($rf_profesion); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nacionalidad:</td>
    <td class='celdaValorAttr'><?php echo($rf_nac); ?></td>
    <td class='celdaNombreAttr'>N° Pasaporte:</td>
    <td class='celdaValorAttr'><?php echo($rf_pasaporte); ?></td>    
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Dirección:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($rf_direccion); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Comuna:</td>
    <td class='celdaValorAttr'><?php echo($rf_com); ?></td>    
    <td class='celdaNombreAttr'>Región:</td>
    <td class='celdaValorAttr'><?php echo($rf_reg); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tel. Fijo:</td>
    <td class='celdaValorAttr'><?php echo($rf_telefono); ?></td>
    <td class='celdaNombreAttr'>Tel. Móvil:</td>
    <td class='celdaValorAttr'><?php echo($rf_tel_movil); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>e-mail:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($rf_email); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">
      <b>Antecedentes Laborales del Responsable Financiero</b>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre Empresa:</td>
    <td class='celdaValorAttr'><?php echo($rf_nombre_empresa); ?></td>
    <td class='celdaNombreAttr'>Cargo:</td>
    <td class='celdaValorAttr'><?php echo($rf_cargo_empresa); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Antigüedad:</td>
    <td class='celdaValorAttr'><?php echo($rf_antiguedad_empresa); ?></td>
    <td class='celdaNombreAttr' nowrap>Sueldo Líquido:</td>
    <td class='celdaValorAttr'>$<?php echo(number_format($rf_sueldo_liquido,0,',','.')); ?></td>
  </tr>  
  <tr>
    <td class='celdaNombreAttr'>Dirección:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($rf_direccion_empresa); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Comuna:</td>
    <td class='celdaValorAttr'><?php echo($rf_com_empresa); ?></td>
    <td class='celdaNombreAttr'>Región:</td>
    <td class='celdaValorAttr'><?php echo($rf_reg_empresa); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Teléfono:</td>
    <td class='celdaValorAttr'><?php echo($rf_telefono_empresa); ?></td>
    <td class='celdaNombreAttr'>e-Mail:</td>
    <td class='celdaValorAttr'><?php echo($rf_email_empresa); ?></td>
  </tr>
  </table>
<?php
}
?>
  <script language="javascript1.2">
    window.print();
	setTimeout(window.close, 1000);
  </script>
</body>
</html>
