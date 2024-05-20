<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_tarea = $_REQUEST['id_tarea'];

if (!is_numeric($id_tarea)) {
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

if ($_REQUEST['guardar'] == "Guardar") {
	if (!empty($_FILES['evidencia'])) {
		
		//$max_tam = 50*1024*1024; // 50MB

		$max_tam = in_bytes(ini_get("upload_max_filesize"));

		$extenciones = array("pdf","odp","odt","ods","xls","xlsx","doc","docx","ppt","pptx","csv","zip");

		$mimes = array("application/pdf",
		               "application/vnd.oasis.opendocument.presentation",
		               "application/vnd.oasis.opendocument.spreadsheet",
		               "application/vnd.oasis.opendocument.text",
		               "application/vnd.ms-excel",
		               "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
		               "application/msword",
		               "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
		               "application/vnd.ms-powerpoint",
		               "application/vnd.openxmlformats-officedocument.presentationml.presentation",
		               "text/csv",
					   "application/zip");

		$arch_evidencia_nombre     = $_FILES['evidencia']['name'];
		$arch_evidencia_tmp_nombre = $_FILES['evidencia']['tmp_name'];
		$arch_evidencia_tipo_mime  = $_FILES['evidencia']['type'];
		$arch_evidencia_longitud   = $_FILES['evidencia']['size'];
		//$arch_evidencia_ext        = substr($arch_evidencia_nombre,-3);
		$arch_evidencia_ext        = substr($arch_evidencia_nombre,strpos($arch_evidencia_nombre,'.')+1);
		//var_dump(substr($arch_cv_nombre,-3));
		if (!in_array($arch_evidencia_tipo_mime,$mimes) && !in_array($arch_evidencia_ext,$extenciones) || $arch_evidencia_longitud > $max_tam) { 
			echo(msje_js("ATENCIÓN: El archivo que está intentando subir no parece estar en alguno de los formatos permitidos "
						."o bien el tamaño sobrepasa los 10MB.\\n"
						."Lo sentimos, pero no están permitidos otros formatos por motivos de "
						."compatibilidad. Así mismo 6MB es más que suficiente para almacenar un "
						."documento de varias decenas de páginas."
						."Puede transformar a formato PDF usando cualquier aplicación que lo "
						."permita, como por ejemplo LibreOffice. Si su documento contiene imágenes, "
						."considere exportar a PDF activando la compresión."));		
		} else {
			$arch_evidencia_data = pg_escape_bytea(file_get_contents($arch_evidencia_tmp_nombre));

			$SQLupd = "UPDATE gestion.poas 
			           SET evidencia='{$arch_evidencia_data}',
			               evidencia_mime='$arch_evidencia_tipo_mime',
			               evidencia_ext='$arch_evidencia_ext',
			               evidencia_filename='$arch_evidencia_nombre',
			               fecha_fin_real=now(),
			               responsable={$_SESSION['id_usuario']},
			               estado='Terminada' 
			           WHERE id=$id_tarea";
			if (consulta_dml($SQLupd) == 1) {
				email_terminar_tarea($id_tarea);
				echo(msje_js("Se ha subido la evidencia de término de esta tarea exitósamente"));
				echo(js("parent.jQuery.fancybox.close();"));
				exit;
			}
		}
	}	
}

$SQL_tarea = "SELECT tipo_act,gu.nombre AS unidad,actividad,prioridad,
                     to_char(fecha_prog_termino,'DD-tmMon-YYYY') AS fecha_prog_termino,fecha_prog_termino_hist,poas.fecha_prog_termino AS fecha_prog_ter,
                     to_char(fecha_fin_real,'DD-tmMon-YYYY') AS fecha_fin_real,
                     coalesce(p.nombre,'** Ninguno **') AS proyecto,estado,poas.comentarios 
              FROM gestion.poas 
              LEFT JOIN gestion.unidades AS gu ON gu.id=poas.id_unidad
              LEFT JOIN gestion.proyectos AS p ON p.id=poas.id_proyecto
              WHERE poas.id=$id_tarea";
//echo($SQL_tarea);
$tarea = consulta_sql($SQL_tarea);

$fec_prog_ter_hist = "";
if ($tarea[0]['fecha_prog_termino_hist'] <> "") {
	$fecha_prog_termino_hist = explode(",",str_replace(array("{","}"),"",$tarea[0]['fecha_prog_termino_hist']));
	$fec_prog_ter_hist = "<hr><small><b>Fechas anteriores:</b><div align='right'>";
	for($x=0;$x<count($fecha_prog_termino_hist);$x++) {
		$fec_prog_ter_hist .= strftime("%d-%b-%Y",strtotime($fecha_prog_termino_hist[$x]))."<br>";
	}
	$fec_prog_ter_hist .= "</div></small>";
}



$admin_poa = consulta_sql("SELECT poas_admin FROM usuarios WHERE id={$_SESSION['id_usuario']}");
$admin_poa = ($admin_poa[0]['poas_admin'] == "t") ? true : false;

$cond_unidades = "";
if ($_SESSION['id_unidad'] <> "" && !$admin_poa) { $cond_unidades = "WHERE id = {$_SESSION['id_unidad']}"; $_REQUEST['id_unidad'] = $_SESSION['id_unidad']; }
$UNIDADES = consulta_sql("SELECT id,nombre||' ('||alias||')' AS nombre FROM gestion.unidades $cond_unidades ORDER BY nombre");

$ESTADOS = consulta_sql("SELECT id,nombre FROM vista_poa_estados");

$TIPOS_TAREA = consulta_sql("SELECT id,nombre FROM vista_poa_tipo_act");

$PROYECTOS = consulta_sql("SELECT id,nombre FROM gestion.proyectos ORDER BY nombre");

?>
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>	
<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME'])?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_tarea" value="<?php echo($_REQUEST['id_tarea']); ?>">
<input type="hidden" name="id_usuario_reg" value="<?php echo($_REQUEST['id_usuario_reg']); ?>">
<div style="margin-top: 5px">
  <input type="submit" name="guardar" value="Guardar">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <tr><td class="celdaNombreAttr" colspan="4" style="text-align: center">Antecedentes de la Tarea</td></tr>
  <tr>
    <td class="celdaNombreAttr"><u>Tipo de Tarea:</u></td>
    <td class="celdaValorAttr"><?php echo($tarea[0]['tipo_act']); ?></td>
    <td class="celdaNombreAttr"><u>Prioridad:</u></td>
    <td class="celdaValorAttr"><span class="<?php echo($tarea[0]['prioridad']); ?>"><?php echo($tarea[0]['prioridad']); ?></span></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr"><u>Unidad:</u></td>
    <td class="celdaValorAttr" colspan="3"><?php echo($tarea[0]['unidad']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr"><u>Actividad:</u></td>
    <td class="celdaValorAttr" colspan="3"><?php echo(nl2br($tarea[0]['actividad'])); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Fecha de Término:</td>
    <td class="celdaValorAttr"><?php echo($tarea[0]['fecha_prog_termino'].$fec_prog_ter_hist); ?></td>
    <td class="celdaNombreAttr">Término Efectivo:</td>
    <td class="celdaValorAttr"><?php echo($tarea[0]['fecha_fin_real']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Proyecto:</td>
    <td class="celdaValorAttr" colspan='3'><?php echo($tarea[0]['proyecto']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr"><u>Estado:</u></td>
    <td class="celdaValorAttr" colspan='3'><span class="<?php echo($tarea[0]['estado']); ?>"><?php echo($tarea[0]['estado']); ?></span></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Evidencia:</td>
    <td class="celdaValorAttr" colspan="3">
      <input type="file" name="evidencia" class="boton" accept=".pdf,.odp,.odt,.ods,.xls,.xlsx,.doc,.docx,.ppt,.pptx,.csv,.zip" required>
<!--      <input type="file" name="evidencia2" class="boton" accept=".pdf"> -->
<!--      <input type="file" name="evidencia3" class="boton" accept=".pdf"> -->
      <br>
      <small>
        Se aceptan documentos PDF, OpenDocument (LibreOffice: Writer .odt, Calc .ods e Impress .odp), CSV (valores separados por comas) y ZIP (archivo comprimido)
		El tamaño máximo de archivo es de <?php echo(ini_get("upload_max_filesize")); ?>
      </small>
    </td>
  </tr>
</table>
</form>
<?php

function email_terminar_tarea($id_tarea) {
	
	$SQL_tarea = "SELECT poas.id,tipo_act,actividad,u.nombre AS unidad,id_unidad,fecha_prog_termino 
	              FROM gestion.poas 
	              LEFT JOIN gestion.unidades AS u ON u.id=poas.id_unidad 
	              WHERE poas.id=$id_tarea";
	$tarea = consulta_sql($SQL_tarea);
	
	$fecha_prog_termino = strftime("%A %e-%b-%Y",strtotime($tarea[0]['fecha_prog_termino']));
	$unidad             = $tarea[0]['unidad'];
	$actividad          = $tarea[0]['actividad'];
	
	$usuarios = consulta_sql("SELECT email FROM usuarios WHERE id_unidad={$tarea[0]['id_unidad']}");
	
	$CR = "\r\n";
			
	$cabeceras = "From: SGU" . $CR
	           . "Content-Type: text/plain;charset=utf-8" . $CR;
	           
	$asunto = "POA: Tarea Terminada";
	
	$cuerpo = "Estimad@ Responsable de $unidad" . $CR.$CR
	        . "Se ha recibido la evidencia y se ha marcado como Terminada la siguiente tarea de su POA:" . $CR.$CR
	        . $actividad . $CR
	        . "Fecha de Término: $fecha_prog_termino" . $CR.$CR
	        . "Gracias!" . $CR
	        . "Atte.," . $CR.$CR
	        . "Dirección de Aseguramiento de la Calidad";
	        
	for ($x=0;$x<count($usuarios);$x++) { mail($usuarios[$x]['email'],$asunto,$cuerpo,$cabeceras); }

	switch($tarea[0]['tipo_act']) {
		case "PIA":
		case "PM":
		case "Acreditación":
		
			$asunto = "POA: Tarea Terminada {$tarea[0]['tipo_act']}";
	
			$cuerpo = "Estimad@ " . $CR.$CR
					. "Se ha recibido la evidencia y se ha marcado como Terminada la siguiente tarea del POA:" . $CR.$CR
					. $actividad . $CR
					. "Fecha de Término: $fecha_prog_termino" . $CR.$CR
					. "https://sgu.umc.cl/sgu/ver_evidencia_poa.php?id_tarea=".$tarea[0]['id']
					. "Gracias!" . $CR
					. "Atte.," . $CR.$CR
					. "Dirección de Aseguramiento de la Calidad";
			mail('hkruse@umcervantes.cl',$asunto,$cuerpo,$cabeceras);
	}
}


function in_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last) {
        // El modificador 'G' está disponble desde PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

?>
