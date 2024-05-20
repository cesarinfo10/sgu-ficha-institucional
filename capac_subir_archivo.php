<?php





/*
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_asiscapac_actividades = $_REQUEST['id_asiscapac_actividades'];


if ($_REQUEST['guardar'] == "Guardar") {
		
	$arch_nombre     = $_FILES['arch']['name'];
	$arch_tmp_nombre = $_FILES['arch']['tmp_name'];
	$arch_tipo_mime  = $_FILES['arch']['type'];
	$arch_longitud   = $_FILES['arch']['size']; 

	if (($arch_tipo_mime <> "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" && $arch_tipo_mime <> "image/jpeg") || $arch_longitud > 1048576) {
		echo(msje_js("tipo = $arch_tipo_mime ATENCIÓN: El archivo que está intentando subir no está en formato XLSX "
		            ."o bien el tamaño sobrepasa 1MB.\\n"
		            ."Lo sentimos, pero no están permitidos otros formatos por motivos de "
		            ."compatibilidad. Así mismo 1MB es suficiente para almacenar un "
		            ."documento de varias decenas de páginas."
		            ."Puede transformar a formato PDF usando cualquier aplicación que lo "
		            ."permita, como por ejemplo OpenOffice/LibreOffice"));
	} else {
		//$id_tipo    = $_REQUEST['id_tipo'];
		$arch_data  = pg_escape_bytea(file_get_contents($arch_tmp_nombre));
		$id_usuario = $_SESSION['id_usuario'];
		$comp_docto = consulta_sql("SELECT 1 FROM asiscapac_doctos_digitalizados WHERE id_tipo=$id_tipo AND id_asiscapac_actividades='$id_asiscapac_actividades' AND NOT eliminado");
		if (count($comp_docto) == 0) {
			$SQLINS_docto = "INSERT INTO asiscapac_doctos_digitalizados (
							id_asiscapac_actividades,
							id_tipo,
							nombre_archivo,
							mime,
							id_usuario,archivo) 
			                      VALUES ($id_asiscapac_actividades,
								  0, --$id_tipo,
								  '$arch_nombre',
								  '$arch_tipo_mime',
								  $id_usuario,
								  '{$arch_data}');";
echo("<br>$SQLINS_docto");
			if (consulta_dml($SQLINS_docto) > 0) {
				echo(msje_js("Se ha recibido y guardado satisfactoriamente el documento ***SACAR ESTE COMENTARIO***"));
				//echo(js("window.location='$enlbase_sm=asiscapac_doctos_digitalizados&rut=$rut';"));
				exit;
			}
		} else {
			$tipo_docto  = consulta_sql("SELECT nombre FROM doctos_digital_tipos WHERE id=$id_tipo");
			$nombre_tipo = $tipo_docto[0]['nombre'];
			echo(msje_js("ERROR: Ya existe un documento de tipo $nombre_tipo registrado para esta actividad. ***SACAR COMENTARIO***"));
			//echo(js("window.location='$enlbase_sm=asiscapac_doctos_digitalizados&rut=$rut';"));
			exit;
		}
	}
}

//$TIPOS_DOCTOS = consulta_sql("SELECT id,nombre||' ('||mime||')' AS nombre FROM doctos_digital_tipos ORDER BY nombre");
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name='formulario' action='principal_sm.php' method='post' enctype="multipart/form-data">
<input type='hidden' name='modulo' value='<?php echo($modulo); ?>'>
<!--<input type='hidden' name='rut' value='<?php echo($rut); ?>'> -->
<input type='text' name='id_asiscapac_actividades' value='<?php echo($id_asiscapac_actividades); ?>'>


  <input type="submit" name="guardar" value="Guardar">
  <input type="button" name="volver" value="Volver" onClick="history.back();">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="6" style="text-align: center; ">Actividad</td></tr>
  <!--
  <tr>
    <td class='celdaNombreAttr'><u>RUT:</u></td>
    <td class='celdaValorAttr'><?php echo($rut); ?></td>
    <td class='celdaNombreAttr'><u>Nombre:</u></td>
    <td class='celdaValorAttr'><?php echo($nombre); ?></td>
  </tr>
-->
  <!--
  <tr>

    <td class='celdaNombreAttr'><u>Contenido</u></td>
    <td class='celdaValorAttr' colspan='3'>
      <select class="filtro" name="id_tipo" style='max-width: none'>
        <option value="">-- Seleccione --</option>
        <?php echo(select($TIPOS_DOCTOS,$tipo)); ?>
      </select>
    </td>
  </tr>
-->
  <tr>
    <td class='celdaNombreAttr'><u>Archivo:</u></td>
    <td class='celdaValorAttr' colspan="3">
      <input type='file' name='arch'><br>
        ATENCIÓN: Sólo se aceptan archivos en formato XLS y con una longitud de hasta 1 MB.<br>
                  Las imagenes serán redimencionadas a 900x600 pixeles, si la resolución está excedida.
    </td>
  </tr>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
*/







?>



<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");


/*
function existenRegistros($ano, 
                      //$id_asiscapac_origen, 
                      $id_actividad, 
                      $id_usuario) {
  
  try {
        $ss = "
          select count(*) as cuenta from asiscapac_actividades_obligatorias_funcionarios
          where
          ano = $ano 
          and id_asiscapac_actividades = $id_actividad
          and id_usuario = $id_usuario
		  --and convocado = 't'
        "; 


    
        $sqlCuenta     = consulta_sql($ss);

        //echo("<br>".$ss);


        extract($sqlCuenta[0]);
  } catch (Exception $e) {
    $cuenta = 0;
  }

      return $cuenta;
  
}
*/

/*
function cuentaRegistroConvocado($ano, 
							$id_asiscapac_actividades, 
							$id_usuario) {
  
  try {
		$ss = "select count(*) as cuenta
		from asiscapac_actividades_obligatorias_funcionarios
		where 
			ano = $ano 
			and id_asiscapac_actividades=$id_asiscapac_actividades 
			and id_usuario = $id_usuario
			and convocado = 't'
		";
 
        $sqlCuenta     = consulta_sql($ss);

        //echo("<br>cuentaRegistroCOnvocado => ".$ss);


        extract($sqlCuenta[0]);
  } catch (Exception $e) {
    $cuenta = 0;
  }

  return $cuenta;
  
}
*/
/*
function cuentaEmailUsuario($email) {
  
  try {
		$SQL_usuarios = "select count(*) as cuenta from usuarios where upper(trim(email)) = upper(trim('$email')) and activo = 't'";

        $sqlCuenta     = consulta_sql($ss);

        //echo("<br>cuentaRegistroCOnvocado => ".$ss);


        extract($sqlCuenta[0]);
  } catch (Exception $e) {
    $cuenta = 0;
  }

  return $cuenta;
  
}
*/
/*
function marcarActividadObligatoria($ano, $id_asiscapac_actividades,$id_usuario,$id_check, $obs) {
	$SQL = "update asiscapac_actividades_obligatorias_funcionarios
	set 
	id_asiscapac_actividades_funcionarios_check = $id_check,
	observacion = $obs
	where 
	ano = $ano 
	and id_asiscapac_actividades=$id_asiscapac_actividades 
	and id_usuario = $id_usuario
	";
	//echo("<br>*3* $SQL");
	if (consulta_dml($SQL) > 0) {
		$hizoAlgo = true;
	}
	
	$SQL = "
	update asiscapac_zoom
	set log_estado = 1
	where id = $id_asiscapac_zoom;
	;";
	if (consulta_dml($SQL) > 0) {
			
	}

}
*/
/*
function obtieneMinutosEnActividades($id_asiscapac_actividades) {
	$duracion = 0;
	try {
		$ss = "select duracion  from asiscapac_actividades
		where 
		id = $id_asiscapac_actividades 
		";

        $sql     = consulta_sql($ss);

        //echo("<br>cuentaEmailExisteEnZoom => ".$ss);

        extract($sql[0])
		;
  } catch (Exception $e) {
    $duracion = 0;
  }

  return $duracion;

}
*/
/*
function detect_encoding($file){
    return mb_detect_encoding(file_get_contents($file), mb_list_encodings());
}
*/
/*
function sacaEstadoActividad($id_asiscapac_actividades) {

	$ss = "
	  select id_asiscapac_estado from asiscapac_actividades
	  where id = $id_asiscapac_actividades
	";
	$sql     = consulta_sql($ss);

	//echo("<br>".$ss);


	extract($sql[0]);
	return $id_asiscapac_estado;
}
*/
/*
function sacaPorcAprobacion($id_asiscapac_actividades) {

	$ss = "
	  select COALESCE(porc_aprobacion,0) porc_aprobacion from asiscapac_actividades
	  where id = $id_asiscapac_actividades
	";
	$sql     = consulta_sql($ss);
  
	//echo("<br>".$ss);
  
  
	extract($sql[0]);
	return $porc_aprobacion;
  }
*/



//$fecha_docto = $_REQUEST['fecha_docto'];
//$id_asiscapac_actividades = $_REQUEST['id_asiscapac_actividades'];

$id_campo_capacitaciones = $_REQUEST['id_campo_capacitaciones'];

$ano = $_REQUEST['ano'];
$opcion_origen = $_REQUEST['opcion_origen'];
//$id_origen = $_REQUEST['id_origen'];
$id_estado_check = $_REQUEST['id_estado_check'];
$id_campo_actividades = $_REQUEST['id_campo_actividades'];

$id_usuario = $_SESSION['id_usuario'];
$id_usuario_parametro = $_REQUEST['id_usuario_parametro'];
$nombre_usuario_parametro = $_REQUEST['nombre_usuario_parametro'];

if ($id_usuario_parametro <> "") {
	$id_usuario = $id_usuario_parametro;
  }
  


$estado_actividad = "";
$strActividad = "";
/*
if ($id_campo_actividades<>"") {
  $estado_actividad = sacaEstadoActividad($id_campo_actividades);
  if ($estado_actividad == 1) {
    $strActividad = "PROGRAMADA";
  }
  if ($estado_actividad == 2) {
    $strActividad = "EJECUTADA";
  }
  if ($estado_actividad == 3) {
    $strActividad = "CERRADA";
  }
  if ($estado_actividad == 4) {
    $strActividad = "SUSPENDIDA";
  }

}
*/

if ($_REQUEST['subir'] == "Subir y Validar") {
//	$tipo_docto  = $_REQUEST['tipo_docto'];
//	$descripcion = $_REQUEST['descripcion'];
//	$id_aprobacion = sacaPorcAprobacion($id_asiscapac_actividades);	


	$archivo_pdf_nomarch     = $_FILES['archivo_pdf']['name'];
	$archivo_pdf_nomarch_tmp = $_FILES['archivo_pdf']['tmp_name'];
	$archivo_pdf_mime        = $_FILES['archivo_pdf']['type'];
	$archivo_pdf_size        = $_FILES['archivo_pdf']['size'];
	$arch_data  = pg_escape_bytea(file_get_contents($archivo_pdf_nomarch_tmp));

//	echo("<br>archivo_pdf_nomarch = $archivo_pdf_nomarch");
//	echo("<br>archivo_pdf_nomarch_tmp = $archivo_pdf_nomarch_tmp");
//	echo("<br>archivo_pdf_mime = $archivo_pdf_mime");
//	echo("<br>archivo_pdf_size = $archivo_pdf_size");

	if ($archivo_pdf_mime == "application/pdf") {

		{
			//echo("<br>estoy dentro del proceso");
			if ($opcion_origen == "1") { //mismo usuario  : id_asiscapac_usuario_capacitaciones
				$campo_origen_1 = $id_campo_capacitaciones;
				$campo_origen_2 = "null";
			}
			if ($opcion_origen == "2") { //asignado a usuario
				$campo_origen_1 = "null";
				$campo_origen_2 = $id_campo_capacitaciones;
			}

			$SQLINS_docto = "INSERT INTO capac_doctos_digitalizados (
				id_asiscapac_usuario_capacitaciones,
				id_asiscapac_capacitaciones,
				nombre_archivo,
				mime,
				id_usuario,
				eliminado,
				archivo) 
					  VALUES (
					  $campo_origen_1,
					  $campo_origen_2, 
					  '$archivo_pdf_nomarch',
					  '$archivo_pdf_mime',
					  $id_usuario,
					  'f',
					  '{$arch_data}');";
				//echo("<br>$SQLINS_docto");
				if (consulta_dml($SQLINS_docto) > 0) {
///CHAPSITO
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

//if ($id_origen==1) {
	//YO HE CREADO EVIDENCIA
	//asiscapac_usuario_capacitacions
	$SQL_correo = "
	select email as email_usuario, 
	concat(nombre, ' ', apellido) as nombre_completo_usuario
	from usuarios where id = (select id_usuario from asiscapac_usuario_capacitaciones where id = $id_campo_capacitaciones)	

	";

//}
//echo($SQL_correo);
$envio_correo = consulta_sql($SQL_correo);
extract($envio_correo[0]);
$envioMensaje = false;


$sql_act = "select descripcion act_descripcion, 
to_char(fecha_inicio,'DD \"de\" tmMonth \"de\" YYYY') act_fecha_inicio, 
to_char(fecha_termino,'DD \"de\" tmMonth \"de\" YYYY') act_fecha_termino
--  link_capacitaciones act_link 
  from asiscapac_usuario_capacitaciones 
  where id = $id_campo_capacitaciones";
$my_act = consulta_sql($sql_act);
extract($my_act[0]);


$asunto = "SGU: Capacitación : $act_descripcion,  Nueva Evidencia";

		//AQUI DEBE ENVIAR CORREO
$prox_ano = $ano; //($ANO);

$cuerpo_dani = "Para su información, $nombre_completo_usuario \n\n";
$cuerpo_dani .= "ha subido evidencia de Capacitación , relacionada con '$act_descripcion'\n";
$cuerpo_dani .= "la cual estará comprendida entre $act_fecha_inicio y $act_fecha_termino.\n\n";
//$cuerpo_dani .= "Recuerde ingresar a la inscripción con su correo institucional (@corp.umc.cl) y no compartir el link. Presione el siguiente enlace para unirse $act_link \n\n";
//$cuerpo_dani .= "Agradecemos desde ya su participación. Esta capacitación es parte integral de la Evaluación del Desempeño $prox_ano.\n\n";
$cuerpo_dani .= "Saludos cordiales.\n\nUnidad de Recursos Humanos\nUniversidad Miguel de Cervantes";

//if ($act_link!= "") { //OBLIGATORIA ONLINE
$cuerpo = "Sr(a) $nombre_completo_usuario, \n\n";
$cuerpo .= "Informamos que se ud ha subido una evidencia de Capacitación , relacionada con '$act_descripcion'\n";
$cuerpo .= "la cual estará comprendida entre $act_fecha_inicio y $act_fecha_termino.\n\n";
//$cuerpo .= "Recuerde ingresar a la inscripción con su correo institucional (@corp.umc.cl) y no compartir el link. Presione el siguiente enlace para unirse $act_link \n\n";
//$cuerpo .= "Agradecemos desde ya su participación. Esta capacitación es parte integral de la Evaluación del Desempeño $prox_ano.\n\n";
$cuerpo .= "Saludos cordiales.\n\nUnidad de Recursos Humanos\nUniversidad Miguel de Cervantes";


//echo("<br>$cuerpo_dani");

//} 
/*
else {
//OBLIGATORIA PRESENCIAL
$sala = sacaSala($id_campo_capacitaciones);
$cuerpo = "Sr(a) $nombre_operador $apellido_operador, \n\n";
$cuerpo .= "Informamos que se ha creado una nueva convocatoria de capacitación, relacionada con '$act_descripcion' ";
$cuerpo .= "la cual estará comprendida entree $act_fecha_inicio y $act_fecha_termino.\n\n";
//$cuerpo .= "Esta será de carácter presencial en la Universidad Miguel de Cervantes, <<<Salón auditorio Bernado Leighton, piso 7>>>.\n\n";
$cuerpo .= "Esta será de carácter presencial en la Universidad Miguel de Cervantes, $sala.\n\n";
$cuerpo .= "Agradecemos desde ya su participación. Esta capacitación es parte integral de la Evaluación del Desempeño $prox_ano.\n\n";
$cuerpo .= "Saludos cordiales.\n\nUnidad de Recursos Humanos\nUniversidad Miguel de Cervantes";
}
*/

		$cabeceras = "From: SGU" . "\r\n"
					. "Content-Type: text/plain;charset=utf-8" . "\r\n";

		  mail("dcarreno@corp.umc.cl",$asunto,$cuerpo_dani,$cabeceras);


		  //mail($email_usuario,$asunto,$cuerpo,$cabeceras);
		  mail("dcarreno@corp.umc.cl",$asunto,$cuerpo,$cabeceras);
		  $envioMensaje = true;

//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

///SIN CHAPDITO




					echo(msje_js("Se ha recibido, enviado correo y guardado satisfactoriamente el documento"));
					echo(js("window.location='$enlbase=capac_usuario_buscar&id_usuario_parametro=$id_usuario_parametro&nombre_usuario_parametro=$nombre_usuario_parametro';"));
					exit;
				}

			/*
			$sql_update = "
			update asiscapac_actividades_obligatorias_funcionarios
			set 
			observacion = null, 
			id_asiscapac_actividades_funcionarios_check = 0 
			where ano=$ano 
			and id_asiscapac_actividades = $id_asiscapac_actividades
			and convocado = 't';
				";

		//echo($sql_update);

			if (consulta_dml($sql_update) == 1) {
				//echo(msje_js("Curso eliminado."));
				//echo(js("location='$mod_ant';"));
			} else {
				//echo(msje_js("Curso no se encuentra completamente vacío para eliminar."));
			}      
*/
			
			
			
				
		}
		
	} else {
		echo(msje_js("ERROR: El archivo que ha subido no está en formato PDF."));
	}
}


/*
$SQL_zoom = "
select
--id ,
b.glosa estado,
a.nombre_funcionario      ,
a.email                   ,
a.hora_unirse             ,
a.hora_salir              ,
a.duracion_minutos        ,
a.invitado                ,
a.consentimiento_grabacion,
a.en_sala_espera          ,
a.log_estado              ,
a.log_comentario          ,
b.glosa glosa_resultado
from asiscapac_zoom a, asiscapac_zoom_log_estado b
where a.id_asiscapac_actividades = $id_asiscapac_actividades
and b.id = a.log_estado
";
//					$SQL_validacion = "SELECT * FROM ($SQL_validacion) AS foo WHERE obs IS NOT NULL";
					echo($SQL_validacion);
$myZoom = consulta_sql($SQL_zoom);

	$HTML = "";
	for ($x=0;$x<count($myZoom);$x++) {
		//$rut = "<a target='_blank' href='$enlbase=gestion_contratos&texto_buscar={$aPagos_problemas[$x]['rut']}&buscar=Buscar' class='enlaces'>{$aPagos_problemas[$x]['rut']}</a>";
		extract($myZoom[$x]);
		//$monto = number_format($aPagos_problemas[$x]['monto'],0,',','.');
			  $HTML =  "<tr class='filaTabla'>\n"
			  .  "  <td class='textoTabla' align='left'>$nombre_funcionario</td>\n"
			  .  "  <td class='textoTabla' align='left'>$email</td>\n"
			  .  "  <td class='textoTabla' align='center'>$hora_unirse</td>\n"
			  .  "  <td class='textoTabla' align='center'>$hora_salir</td>\n"
			  .  "  <td class='textoTabla' align='right'>$duracion_minutos</td>\n"
			  .  "  <td class='textoTabla' align='center'>$invitado</td>\n"
			  .  "  <td class='textoTabla' align='center'>$consentimiento_grabacion</td>\n"
			  .  "  <td class='textoTabla' align='center'>$en_sala_espera</td>\n"
			  .  "</tr>\n";	


			$HTML_zoom .= $HTML;
		
	}

*/



?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>
<?php if ($id_usuario_parametro <> "") {?>
  <div class="tituloModulo">
    <?php echo("Simulado para : $id_usuario_parametro - $nombre_usuario_parametro<br>"); ?>
  </div>
<?php }?>
<form name="formulario" method="post" enctype="multipart/form-data">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_asiscapac_actividades" value="<?php echo($id_asiscapac_actividades); ?>">
<input type="hidden" name="ano" value="<?php echo($ano); ?>">
<!--<input type="hidden" name="id_origen" value="<?php echo($id_origen); ?>"> -->
<input type="hidden" name="id_estado_check" value="<?php echo($id_estado_check); ?>">
<!--<input type="hidden" name="id_campo_actividades" value="<?php echo($id_campo_actividades); ?>"> -->
<input type="hidden" name="id_usuario" value="<?php echo($id_usuario); ?>">
<input type="hidden" name="id_usuario_parametro" value="<?php echo($id_usuario_parametro); ?>">
<input type="hidden" name="nombre_usuario_parametro" value="<?php echo($nombre_usuario_parametro); ?>">



<?php if ($id_campo_capacitaciones!="") 
{ 
	?>

<div style='margin-top: 5px'>
  
  			<input type="submit" name="subir" value="Subir y Validar" onClick="return confirm('Está seguro de continuar?');" > <!--onBlur="this.disabled=true;">-->

  <!--<input type="button" name="volver" value="Volver" onClick="window.location.href='<?php echo($enlbase); ?>=capac_usuario_buscar&ano=<?php echo($ano); ?>&id_origen=<?php echo($id_origen); ?>&id_estado_check=<?php echo($id_estado_check); ?>&id_campo_actividades=<?php echo($id_campo_actividades); ?>'"> -->
  <input type="button" name="volver" value="Volver" onClick="window.location.href='<?php echo($enlbase); ?>=capac_usuario_buscar&ano=<?php echo($ano); ?>&id_origen=<?php echo($id_origen); ?>&id_estado_check=<?php echo($id_estado_check); ?>&id_campo_actividades=<?php echo($id_campo_actividades); ?>&id_usuario_parametro=<?php echo($id_usuario_parametro); ?>&nombre_usuario_parametro=<?php echo($nombre_usuario_parametro); ?>'">

</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
<!--
  <tr>
    <td class='celdaNombreAttr'><u>Descripción:</u></td>
    <td class='celdaValorAttr' colspan='3'><input type="text" name="descripcion" size="40" value="<?php echo($_REQUEST['descripcion']); ?>" class="boton" required></td>
  </tr>
-->
<!--
  <tr>
    <td class='celdaNombreAttr'>Tipo de Documento:</td>
    <td class='celdaValorAttr'>
      <select name="tipo_docto" class="filtro" required>
        <option value="">-- Seleccione --</option>
        <?php echo(select($TIPOS_DOCTOS,$_REQUEST['tipo_docto'])); ?>
      </select>
    </td>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><input type='date' name='fecha_docto' value='<?php echo($_REQUEST['fecha_docto']); ?>' class="boton" required></td>
  </tr>
-->
			<tr>
				<td class='celdaNombreAttr'><u>Planilla:</u></td>
				<td class='celdaValorAttr' colspan='3'>
				<input type="file" accept=".pdf" name="archivo_pdf" size="50" required><br><br>
				NOTA: El archivo debe estar en formato PDF.<br><br>
				<br>
				</td>
			</tr>
</table>
<!--
<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
  <tr class='filaTituloTabla'><td class='tituloTabla' colspan="8">Subidas anteriores (<?php echo(count($pagos_planillas)); ?>)</td></tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>Descripción</td>
    <td class='tituloTabla'>Fecha<br>Op.</td>
    <td class='tituloTabla'>Fecha y Tipo<br>Docto</td>
    <td class='tituloTabla'>Cantidad</td>
    <td class='tituloTabla'>Monto<br>Total</td>
    <td class='tituloTabla'>Estado</td>
  </tr>
  <?php //echo($HTML_pagos_planilla); ?>
</table>
-->
<?php } 

//elseif($id_pp > 0) 
{ ?>

<!--
<input type="hidden" name="id_pp" value="<?php echo($id_pp); ?>">

<div style='margin-top: 5px'>
  <input type="submit" name="subir" value="Registrar Planilla y Generar Boletas" onClick="return confirm('Está seguro de continuar?');" onBlur="this.disabled=true;"> 
  <input type="button" name="Cancelar" value="Cancelar" onClick="">
</div>
-->






<!--
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: center'>Subida de Planilla de Pagos</td></tr>
  <tr>
    <td class='celdaNombreAttr'><u>Descripción:</u></td>
    <td class='celdaValorAttr' colspan='3'><?php echo($descripcion); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tipo de Documento:</td>
    <td class='celdaValorAttr'><?php echo($aTipos_doctos[$tipo_docto]); ?></td>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><?php echo($fecha_docto); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Archivo:</u></td>
    <td class='celdaValorAttr' colspan='3'><?php echo($planilla_nomarch); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cantidad de pagos:</td>
    <td class='celdaValorAttr'><?php echo($pp_det_tot[0]['cant_pagos']); ?></td>
    <td class='celdaNombreAttr'>Monto Total:</td>
    <td class='celdaValorAttr'><?php echo(number_format($pp_det_tot[0]['monto_total'],0,',','.')); ?></td>
  </tr>
</table>

-->






<?php 	//if (count($aPagos_problemas) > 0) { 
/*	
	
	?>

	
<table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla' style='margin-top: 5px'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="10">
	  Registros cargados (<?php echo(count($myZoom)); ?>) <br>
	  <!--<small>Debe establecer la forma de registrar, usando la columna «Solución»</small> -->
    </td>
  </tr>
  <tr class='filaTituloTabla'>
  <!--<td class='tituloTabla'>Estado</td> -->
    <td class='tituloTabla'>Funcionario</td>
    <td class='tituloTabla'>email</td>
    <td class='tituloTabla'>Fecha Hora Unirse</td>
    <td class='tituloTabla'>Fecha Hora Salir</td>
    <td class='tituloTabla'>Duración (Minutos)</td>
    <td class='tituloTabla'>Invitado</td>
	<td class='tituloTabla'>Consentimiento Grabación</td>
	<td class='tituloTabla'>En Sala Espera</td>
	<!--<td class='tituloTabla'>Resultado Proceso</td>-->
  </tr>
  <?php //echo($HTML_pagos_problemas); ?>
  <!--
  <tr>
    <td class='celdaNombreAttr' colspan='10' align='right'>
	  <input type="submit" name="subir" value="Registrar Planilla y Generar Boletas" onClick="return confirm('Está seguro de continuar?');" onBlur="this.disabled=true;">
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="10">
	  Pagos sin Observaciones (<?php echo($cant_pagos_sin_prob); ?>)<br>
    </td>
  </tr>
-->
  <?php 
  echo($HTML_zoom); 
  ?>
</table>

<?php 	//}  

*/
?>

<?php } ?>

</form>

<?php


?>

