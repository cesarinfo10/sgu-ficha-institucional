<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo_uid_no_cero.php");

$folio       = $_REQUEST['folio'];

$SQL_certificado = "SELECT folio,trim(a.rut) AS rut,va.nombre AS alumno,cert.nombre AS docto,to_char(ac.fecha,'DD-tmMon-YYYY') AS fecha,vac.cod,
                           CASE WHEN length(ac.archivo)>0 THEN 'Si' ELSE 'No' END AS docto_firmado
                    FROM alumnos_certificados AS ac
                    LEFT JOIN vista_alumnos_certificados_codbarras AS vac USING (folio)
                    LEFT JOIN certificados    AS cert ON cert.id=ac.id_certificado
                    LEFT JOIN alumnos         AS a    ON a.id=ac.id_alumno
                    LEFT JOIN vista_alumnos   AS va   ON va.id=ac.id_alumno
                    WHERE folio=$folio";
$certificado = consulta_sql($SQL_certificado);
if (count($certificado) > 0) {
	extract($certificado[0]);
	if ($docto_firmado == "Si") { echo(msje_js("Este certificado ya posee un documento digitalizado. Puede subir otro, pero reemplazará el actual.")); }
} else {
	echo(msje_js("ERROR: Folio inexistente. No se puede continuar."));
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

if ($_REQUEST['guardar'] == "Guardar") {
		
	$arch_nombre     = $_FILES['arch']['name'];
	$arch_tmp_nombre = $_FILES['arch']['tmp_name'];
	$arch_tipo_mime  = $_FILES['arch']['type'];
	$arch_longitud   = $_FILES['arch']['size'];

	if ($arch_tipo_mime <> "application/pdf" || $arch_longitud > 2*1048576) {
		echo(msje_js("ATENCIÓN: El archivo que está intentando subir no está en formato PDF"
		            ."o bien el tamaño sobrepasa 2MB.\\n"
		            ."Lo sentimos, pero no están permitidos otros formatos por motivos de "
		            ."compatibilidad. Así mismo 2MB es más que suficiente para almacenar un "
		            ."documento de varias decenas de páginas."));
	} else {
		$arch_data   = pg_escape_bytea(file_get_contents($arch_tmp_nombre));
		$id_usuario  = $_SESSION['id_usuario'];
		$SQL_docto = "UPDATE alumnos_certificados SET archivo='{$arch_data}' WHERE folio=$folio";
		if (consulta_dml($SQL_docto) > 0) {
			echo(msje_js("Se ha recibido y guardado satisfactoriamente el documento."));
			echo(js("parent.jQuery.fancybox.close();"));
			exit;
		}
	}
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name='formulario' action='principal_sm.php' method='post' enctype="multipart/form-data">
<input type='hidden' name='modulo' value='<?php echo($modulo); ?>'>
<input type='hidden' name='folio' value='<?php echo($folio); ?>'>

<div  style='margin-top: 5px'>
  <input type="submit" name="guardar" value="Guardar">
  <input type="button" name="volver" value="Volver" onClick="history.back();">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Certificado</td></tr>
  <tr>
    <td class='celdaNombreAttr'><u>Folio:</u></td>
    <td class='celdaValorAttr'><?php echo($folio); ?></td>
    <td class='celdaNombreAttr'><u>Codigo de Barras:</u></td>
    <td class='celdaValorAttr'><?php echo($cod); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Nombre:</u></td>
    <td class='celdaValorAttr'><?php echo($docto); ?></td>
    <td class='celdaNombreAttr'><u>Fecha de Emisión:</u></td>
    <td class='celdaValorAttr'><?php echo($fecha); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Alumno(a)</td></tr>
  <tr>
    <td class='celdaNombreAttr'><u>RUT:</u></td>
    <td class='celdaValorAttr'><?php echo($rut); ?></td>
    <td class='celdaNombreAttr'><u>Nombre:</u></td>
    <td class='celdaValorAttr'><?php echo($alumno); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Archivo:</u></td>
    <td class='celdaValorAttr' colspan="3">
      <input type='file' name='arch' class='boton'><br>
      ATENCIÓN: Sólo se aceptan archivos en formato PDF y con una longitud de hasta 1 MB.                  
    </td>
  </tr>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
