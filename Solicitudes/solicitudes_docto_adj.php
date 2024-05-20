<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");

if (isset($_SESSION['tipo'])) { 
	include("validar_modulo.php"); 
} else { 
	$datos_modulo = modulos($modulo); 
	$nombre_modulo = $datos_modulo['nombre'];
}

$id_solic   = $_REQUEST['id_solic'];

if ($_REQUEST["subir"] == "Guardar") {
	$max_tam = 3*1024*1024; // 3MB

	$id_alumno         = $_REQUEST['id_alumno'];
	$tipo_solic        = $_REQUEST['tipo_solic'];
	$tipo_docto_solic  = $_REQUEST['tipo_docto_solic'];

	$archivo_nombre     = $_FILES['archivo']['name'];
	$archivo_tmp_nombre = $_FILES['archivo']['tmp_name'];
	$archivo_tipo_mime  = $_FILES['archivo']['type'];
	$archivo_longitud   = $_FILES['archivo']['size'];

	//$archivo_ext        = substr($archivo_nombre,strpos($archivo_nombre,'.')+1);

	$archivo_ext        = pathinfo($archivo_nombre,PATHINFO_EXTENSION);

	$max_tam_mb = $max_tam/(1024*1024);

	if ($archivo_ext <> "pdf" || $archivo_longitud > $max_tam) {
		echo(msje_js("ATENCIÓN: El archivo que está intentando subir no está en formato PDF o bien sobrepasa los $max_tam_mb MB.\\n"
		            ."Lo sentimos, pero no están permitidos otros formatos por motivos de "
		            ."compatibilidad. Así mismo $max_tam_mb MB es más que suficiente para almacenar un "
		            ."documento de varias decenas de páginas.\\n"
		            ."Puede transformar a formato PDF usando cualquier aplicación que lo "
		            ."permita, como por ejemplo LibreOffice. Si su documento contiene imágenes, "
		            ."considere exportar a PDF activando la compresión."));
	} else {
		$archivo_data = pg_escape_bytea(file_get_contents($archivo_tmp_nombre));

		$SQL_ins = "INSERT INTO gestion.solic_doctos_adj (id_solicitud,tipo,archivo) "
		         . "VALUES ($id_solic,'$tipo_docto_solic','{$archivo_data}')";

		if (consulta_dml($SQL_ins) > 0) {
			echo(msje_js("Se ha subido el documento $tipo_docto_solic exitosamente."));
			echo(js("location='$enlbase_sm=solicitudes_ver&id_solic=$id_solic&id_alumno=$id_alumno&tipo=$tipo_solic';"));
			exit;
		}
	}

}

$SQL_solic = "SELECT st.nombre AS nombre_tipo_solic,s.estado,st.tipo_docto_oblig,st.alias AS tipo_solic,
                     to_char(s.estado_fecha,'DD-tmMon-YYYY HH24:MI') AS estado_fecha,
                     to_char(s.fecha,'DD-tmMon-YYYY HH24:MI') AS fecha_solic,
					 s.email,s.telefono,s.tel_movil,
                     va.rut,va.id AS id_alumno,va.nombre,va.carrera||'-'||a.jornada AS carrera,
					 a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.id_pap
              FROM gestion.solicitudes AS s 
			  LEFT JOIN gestion.solic_tipos AS st ON st.id=s.id_tipo
			  LEFT JOIN vista_alumnos       AS va ON va.id = s.id_alumno 
			  LEFT JOIN alumnos             AS a  ON a.id = s.id_alumno 
			  WHERE s.id=$id_solic";
$solic = consulta_sql($SQL_solic);
if (count($solic) == 0) {
	echo(msje_js("ERROR: No es posible acceder a esta solicitud."));
	echo(js("parent.jQuery.fancybox.close()"));
	exit;
}

?>

<div class="tituloModulo">
  Adjuntar Documento a Solicitud
</div>
<form name='formulario' action='<?php echo($_SERVER['SCRIPT_NAME']); ?>' method="post" id="form1" enctype="multipart/form-data">
<input type='hidden' name='modulo'     value='<?php echo($modulo); ?>'>
<input type='hidden' name='id_solic'   value='<?php echo($id_solic); ?>'>
<input type='hidden' name='id_alumno'  value='<?php echo($solic[0]['id_alumno']); ?>'>
<input type='hidden' name='tipo_solic' value='<?php echo($solic[0]['tipo_solic']); ?>'>
<div style='margin-top: 5px'>
  <input type='submit' name='subir' value='Guardar'>
  <input type="button" name="cancelar" value="Cerrar" onclick="history.back();">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr'>Tipo:</td>
    <td class='celdaValorAttr'><?php echo($solic[0]['nombre_tipo_solic']); ?></td>
    <td class='celdaNombreAttr'>Fecha creación:</td>
    <td class='celdaValorAttr'><?php echo($solic[0]['fecha_solic']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Estado:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo("{$solic[0]['estado']} <i>desde el {$solic[0]['estado_fecha']}</i>"); ?></td>
  </tr>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center;">Adjuntar Documento</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Tipo Documento:</td>
    <td class='celdaValorAttr' colspan="3"><input type='text' class='boton' name="tipo_docto_solic" value="<?php echo($solic[0]['tipo_docto_oblig']); ?>" readonly></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Archivo:</td>
    <td class='celdaValorAttr' colspan="3">
	  <input type="file" class='boton' name="archivo" accept=".pdf" required><br>
	  Tamaño máximo: 3 MB
	</td>
  </tr>
</table>
</form>