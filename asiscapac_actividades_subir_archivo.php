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
function cuentaEmailExisteEnZoom($ano,$id_asiscapac_actividades, $email) {
	try {
		$ss = "select count(*) as cuenta from asiscapac_zoom 
		where ano = $ano 
		and id_asiscapac_actividades = $id_asiscapac_actividades 
		and upper(trim(email)) = upper(trim('$email'))
		";

        $sqlCuenta     = consulta_sql($ss);

        //echo("<br>cuentaEmailExisteEnZoom => ".$ss);


        extract($sqlCuenta[0])
		;
  } catch (Exception $e) {
    $cuenta = 0;
  }

  return $cuenta;

}
function minutosEnZoom($ano,$id_asiscapac_actividades, $email) {
	$duracion_minutos = 0;
	try {
		$ss = "select sum(duracion_minutos) duracion_minutos from asiscapac_zoom 
		where ano = $ano 
		and id_asiscapac_actividades = $id_asiscapac_actividades 
		and upper(trim(email)) = upper(trim('$email'))
		";

        $sql     = consulta_sql($ss);

        //echo("<br>cuentaEmailExisteEnZoom => ".$ss);


        extract($sql[0])
		;
  } catch (Exception $e) {
    $duracion_minutos = 0;
  }

  return $duracion_minutos;

}

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
function detect_encoding($file){
    return mb_detect_encoding(file_get_contents($file), mb_list_encodings());
}

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
  function buscaPrimeraLinea($planilla) {
	//$planilla = file($archivo_csv_nomarch_tmp);
	//$planilla[0] .= str_replace("\n","",$planilla[0])."\n";
	//$planilla = mb_convert_encoding($planilla, 'UTF-8'); 
	$lineaBuscar1 = "Nombre (nombre original),E-mail del usuario,Hora para unirse,Hora para salir,Duraci"; //"Nombre (nombre original),E-mail del usuario,Hora para unirse,Hora para salir,Duración (minutos)";
	$lineaBuscar2 = "Nombre (nombre original);E-mail del usuario;Hora para unirse;Hora para salir;Duraci"; //"Nombre (nombre original);E-mail del usuario;Hora para unirse;Hora para salir;Duración (minutos)";
	
	$filaSalida = -1;
	$separador = "";
	//echo("<br>va a buscar por $lineaBuscar1");
	for ($x=0;$x<count($planilla);$x++) { 
		$linea = $planilla[$x];
		//echo("<br>linea = $linea");
		$pos = strpos(strtoupper($linea), strtoupper($lineaBuscar1));
		//echo("<br>----( * )pos=$pos, $x");
		if (is_numeric($pos)) {
		//if ($linea == $lineaBuscar1) {
			//echo("<br>--------( * ) ha encontrado en linea = $x");
			$filaSalida = $x;
			$separador = ",";
			break;
		}
	}
	if ($filaSalida == -1) {
		//echo("<br>va a buscar por $lineaBuscar2");
		for ($x=0;$x<count($planilla);$x++) { 
			$linea = $planilla[$x];

			//echo("<br>linea = $linea");
			$pos = strpos(strtoupper($linea), strtoupper($lineaBuscar2));
			//echo("<br>----( * * )pos=$pos, $x");
			if (is_numeric($pos)) {
			//if ($linea == $lineaBuscar2) {
				//echo("<br>--------( * * )ha encontrado en linea2 = $x");
				$filaSalida = $x;
				$separador = ";";
				break;
			}
		}	
	}
	return $filaSalida;
  }
  function buscaSeparador($planilla) {
	//$planilla = file($archivo_csv_nomarch_tmp);
	//$planilla[0] .= str_replace("\n","",$planilla[0])."\n";
	//$planilla = mb_convert_encoding($planilla, 'UTF-8'); 
//	$lineaBuscar1 = "Nombre (nombre original),E-mail del usuario,Hora para unirse,Hora para salir,Duración (minutos)";
//	$lineaBuscar2 = "Nombre (nombre original);E-mail del usuario;Hora para unirse;Hora para salir;Duración (minutos)";
	$lineaBuscar1 = "Nombre (nombre original),E-mail del usuario,Hora para unirse,Hora para salir,Duraci"; //"Nombre (nombre original),E-mail del usuario,Hora para unirse,Hora para salir,Duración (minutos)";
	$lineaBuscar2 = "Nombre (nombre original);E-mail del usuario;Hora para unirse;Hora para salir;Duraci"; //"Nombre (nombre original);E-mail del usuario;Hora para unirse;Hora para salir;Duración (minutos)";

	$filaSalida = -1;
	$separador = "";
	//echo("<br>va a buscar por $lineaBuscar1");
	for ($x=0;$x<count($planilla);$x++) { 
		$linea = $planilla[$x];
		//echo("<br>linea = $linea");
		$pos = strpos(strtoupper($linea), strtoupper($lineaBuscar1));
		//echo("<br>pos=$pos, $x");
		if (is_numeric($pos)) {
		//if ($linea == $lineaBuscar1) {
			//echo("<br>ha encontrado en linea = $x");
			$filaSalida = $x;
			$separador = ",";
			break;
		}
	}
	if ($filaSalida == -1) {
		//echo("<br>va a buscar por $lineaBuscar2");
		for ($x=0;$x<count($planilla);$x++) { 
			$linea = $planilla[$x];

			//echo("<br>linea = $linea");
			$pos = strpos(strtoupper($linea), strtoupper($lineaBuscar2));
			//echo("<br>pos=$pos");
			if (is_numeric($pos)) {
			//if ($linea == $lineaBuscar2) {
				//echo("<br>ha encontrado en linea2 = $x");
				$filaSalida = $x;
				$separador = ";";
				break;
			}
		}	
	}
	return $separador;
  }

  function buscaMaximoColumnas($planilla, $filaBuscar, $separador) {
	$maxColumnas = 0;
	//echo("<br>Estoy en buscaMaximoColumnas, filaBuscar = $filaBuscar, separador = $separador");
	for ($x=0;$x<count($planilla);$x++) { 
		$linea = $planilla[$x];
		if ($x == $filaBuscar) {
			//echo("<br>ya estoy en la posición...");
//			echo("<br>$x === linea = $linea");
//			$pos = strpos($linea, $lineaBuscar1);
			//echo("<br>pos=$pos, $linea en $lineaBuscar2");
//			if (is_numeric($pos)) {
			//if ($linea == $lineaBuscar1) {
		//		echo("<br>ha encontrado en linea = $x");
				//$linea .= str_replace("\n","",$linea)."\n";
				$maxColumnas = explode($separador,trim($linea));
				$maxColumnas = count($maxColumnas);
				break;
//			}
	
		}
	}
	return $maxColumnas;
  }



//$fecha_docto = $_REQUEST['fecha_docto'];
$id_asiscapac_actividades = $_REQUEST['id_asiscapac_actividades'];
$ano = $_REQUEST['ano'];
$id_origen = $_REQUEST['id_origen'];
$id_estado_check = $_REQUEST['id_estado_check'];
$id_campo_actividades = $_REQUEST['id_campo_actividades'];


$estado_actividad = "";
$strActividad = "";
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


if ($_REQUEST['subir'] == "Subir y Validar") {
//	$tipo_docto  = $_REQUEST['tipo_docto'];
//	$descripcion = $_REQUEST['descripcion'];
	$id_aprobacion = sacaPorcAprobacion($id_asiscapac_actividades);	


	$archivo_csv_nomarch     = $_FILES['archivo_csv']['name'];
	$archivo_csv_nomarch_tmp = $_FILES['archivo_csv']['tmp_name'];
	$archivo_csv_mime        = $_FILES['archivo_csv']['type'];
	$archivo_csv_size        = $_FILES['archivo_csv']['size'];
	
//echo("<br>archivo_csv_nomarch =$archivo_csv_nomarch ");
//echo("<br>archivo_csv_nomarch_tmp=$archivo_csv_nomarch_tmp");
//echo("<br>archivo_csv_mime=	$archivo_csv_mime");
//echo("<br>archivo_csv_size=$archivo_csv_size");



	if ($archivo_csv_mime == "text/csv") {
		$planilla = file($archivo_csv_nomarch_tmp);

		//BUSCAMOS POR EL ERROR
		$primeraLinea = buscaPrimeraLinea($planilla);
		$separador = buscaSeparador($planilla);
		$maxColumnas = buscaMaximoColumnas($planilla, $primeraLinea, $separador);
		//echo("<br>primeraLinea = $primeraLinea");
		//echo("<br>separador = '$separador'");
		//echo("<br>maxColumnas = $maxColumnas");
		//$resultCheck = check1($planilla, $separador);
		//echo("<br>check1 resultado = $resultCheck");
		$primeraLinea++; //próxima linea
/*		
echo(msje_js(detect_encoding($planilla)));
if (detect_encoding($planilla) == 'UFT-8') {
	echo(msje_js("ES UTF-8"));
} else {
	echo(msje_js("NO ES UTF-8"));
}
if(!mb_check_encoding($planilla, 'iso-8859-1')) {
//if ( !(detect_encoding($planilla) == 'UFT-8') ) {	
	
	$planilla = mb_convert_encoding($planilla, 'iso-8859-1');
	echo(msje_js("APLICA CONVERSION"));
} else {
	echo(msje_js("NO APLICA CONVERSION"));

}

//$planilla = mb_convert_encoding($planilla, 'iso-8859-1');
//$planilla =  mb_convert_encoding($planilla, "ISO-8859-1", "UFT-8");
*/

$planilla = mb_convert_encoding($planilla, 'UTF-8'); 


/*
if(!mb_check_encoding($planilla, 'UTF-8')
    OR !($planilla === mb_convert_encoding(mb_convert_encoding($planilla, 'UTF-32', 'UTF-8' ), 'UTF-8', 'UTF-32'))) {

    $planilla = mb_convert_encoding($planilla, 'UTF-8'); 
	echo(msje_js("APLICA 1"));
} else {
	$planilla = mb_convert_encoding($planilla, 'iso-8859-1');
	echo(msje_js("APLICA 2"));
}*/


//$planilla = iconv("UTF-8","ISO-8859-1//TRANSLIT",$planilla);





//ESTE ES EL ORIGINAL
//$planilla = mb_convert_encoding($planilla, 'iso-8859-1');

		//echo("<br>COLUMNAS = $count($archivo_csv_encabezado)");



		//$archivo_csv_encabezado = explode(",",trim($planilla[0]));


		//$archivo_csv_encabezado = explode($separador,trim($planilla[0]));
		//echo("<br>COLUMNAS = $count($archivo_csv_encabezado)");
		//if (count($archivo_csv_encabezado) == 8) {
		$pSeguir = false;
		if ($maxColumnas == 7) {
			$pSeguir = true;
		}
		if (!$pSeguir) {
			if ($maxColumnas == 8) {
				$pSeguir = true;
			}
		}
		if ($pSeguir)	{
			//echo("<br>estoy dentro del proceso");
			$sql_delete = "
			delete from asiscapac_zoom where ano=$ano and id_asiscapac_actividades = $id_asiscapac_actividades;
				";
			if (consulta_dml($sql_delete) == 1) {
				//echo(msje_js("Curso eliminado."));
				//echo(js("location='$mod_ant';"));
			} else {
				//echo(msje_js("Curso no se encuentra completamente vacío para eliminar."));
			}      
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

			
			
			$problemas = false;
			
			if (!$problemas) {				

//				$SQL_ins_pago_planilla = "INSERT INTO finanzas.pagos_planillas (tipo_docto,fecha_docto,descripcion,id_usuario) "
//									   . " VALUES ('$tipo_docto','$fecha_docto','$descripcion',{$_SESSION['id_usuario']})";

//				consulta_dml($SQL_ins_pago_planilla);
				
//				$id_pp = consulta_sql("SELECT max(id) AS id FROM finanzas.pagos_planillas");
//				$id_pp = $id_pp[0]['id'];
	
				//$planilla[0] .= str_replace("\n","",$planilla[0]).",id_pp\n";

//NO SE QUE ES				
				$planilla[0] .= str_replace("\n","",$planilla[0])."\n";
//FIN NO SE QUE ES				
				$huboError = false;
				$linea = 0;
				$ss_error = "No se cargó linea : ";
				for ($x=0;$x<count($planilla);$x++) { 
					if ($x >= $primeraLinea) {
									//$planilla[$x] = str_replace("\n","",$planilla[$x]).",$id_pp\n"; 
									$planilla[$x] = str_replace("\n","",$planilla[$x])."\n"; 
									//echo("<br>$x.-$planilla[$x]");
									//$linea = explode(",",trim($planilla[$x]));
									$linea = explode($separador,trim($planilla[$x]));
									if ($maxColumnas == 8) {
										$nombreFuncionario 	= $linea[0];
										$email 				= $linea[1];
										$horaUnirse 		= $linea[2];
										$horaSalir 			= $linea[3];
										$duracionMinutos	= $linea[4];
										$invitado 			= $linea[5];
										$consentimientoGrabacion = $linea[6];
										$enSalaEspera 		= $linea[7];	
									}
									if ($maxColumnas == 7) {
										$nombreFuncionario 	= $linea[0];
										$email 				= $linea[1];
										$horaUnirse 		= $linea[2];
										$horaSalir 			= $linea[3];
										$duracionMinutos	= $linea[4];
										$invitado 			= $linea[5];
										$consentimientoGrabacion = " "; //$linea[6];
										$enSalaEspera 		= $linea[6];	

									}

									//echo("<br>$nombreFuncionario - $email - $horaUnirse - $horaSalir - $duracionMinutos - $invitado - $consentimientoGrabacion - $enSalaEspera");

									$sql_insert = "
									insert into asiscapac_zoom(
										ano, 
										id_asiscapac_actividades, 
										nombre_funcionario, 
										email,
										hora_unirse, 
										hora_salir, 
										duracion_minutos, 
										invitado,
										consentimiento_grabacion,
										en_sala_espera,
										log_estado,
										log_comentario
									) values (
										$ano, 
										$id_asiscapac_actividades,
										'$nombreFuncionario',
										trim('$email') 			,
										'$horaUnirse' 	,
										'$horaSalir' 		,
										$duracionMinutos,	
										'$invitado' 		,	
										'$consentimientoGrabacion' ,
										'$enSalaEspera',
										-1,
										null 			
									)	";
									if (consulta_dml($sql_insert) > 0) {						
									} else {
										$huboError = true;
										$ss_error = $ss_error." ".$x.",";
									}

									//echo("<br>$sql_insert");

					}
				}




				//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
				//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
				//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
				//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------


				if (!$huboError) {
						//UNIVERSO DE CONVOCADOS
						$SQL_convocados = "
							select 
							id_usuario,
							(select trim(email) from usuarios where id = id_usuario) email
							from asiscapac_actividades_obligatorias_funcionarios 
							where convocado = 't'
							and ano = $ano
							and id_asiscapac_actividades = $id_asiscapac_actividades
						";
//						echo($SQL_convocados);
						$myConvocados = consulta_sql($SQL_convocados);
						
						for ($u=0;$u<count($myConvocados);$u++) {
							extract($myConvocados[$u]);
							//echo("<br> convocado = $email");
							if ($email != "") {
								$emailExisteEnZoom = cuentaEmailExisteEnZoom($ano,$id_asiscapac_actividades, $email);
								//echo("<br> convocado = $email, existe En archivo zoom ? = $emailExisteEnZoom");
								
								if ($emailExisteEnZoom>0) {
									$max_def_minutos_actividad = obtieneMinutosEnActividades($id_asiscapac_actividades);
									$minutos_en_zoom = minutosEnZoom($ano,$id_asiscapac_actividades, $email);
									
									$porcentaje_minutos = ($minutos_en_zoom * 100) / ($max_def_minutos_actividad);

//echo("<br>$email : max_def_minutos_actividad = $max_def_minutos_actividad, minutos_en_zoom = $minutos_en_zoom, %=$porcentaje_minutos");
									if ($porcentaje_minutos>=$id_aprobacion) { //70
										$id_check = "2"; //PRESENTE
										$obs = "null";
									} else {
										$id_check = "5"; //INASISTENTE
										$obs = "'No cumple mínimo minutos exigido'"; //"'Estuvo en reunión menos del tiempo permitido.'";	
									}
									
									marcarActividadObligatoria($ano, $id_asiscapac_actividades,$id_usuario,$id_check, $obs);		
	
								} else {
									$id_check = "5"; //INASISTENTE
									$obs = "'No presente en bitácora de plataforma zoom'"; //"'No existe email = $email,  en archivo Zoom'";
									marcarActividadObligatoria($ano, $id_asiscapac_actividades,$id_usuario,$id_check, $obs);		
	
								}	
							} else {
								$id_check = "7"; //sin correo
								$obs = "'No tiene correo.'";
								marcarActividadObligatoria($ano, $id_asiscapac_actividades,$id_usuario,$id_check, $obs);		
							}
						}
						
						

						//MISMO CORREO MUCHOS USUARIOS
						$ss = "select count(*), email from 	asiscapac_zoom
						where ano = $ano
						and id_asiscapac_actividades = $id_asiscapac_actividades
						group by email
						having count(*) = 1";
						$mismosCorreos = consulta_sql($ss);
						for ($u=0;$u<count($mismosCorreos);$u++) {
							extract($mismosCorreos[$u]);
							$existeUsuarioMail = cuentaEmailUsuario($email);

							$SQL_usuarios = "select id id_usuario from usuarios where upper(email) = upper('$email') and activo = 't'";
							$myUsuarios = consulta_sql($SQL_usuarios);
							if (count($myUsuarios)>0) {
								$hizoAlgo = false;
								for ($y=0;$y<count($myUsuarios);$y++) { //DEBIESE SER SOLO UNO
									extract($myUsuarios[$y]);
									$cuentaConvocado = cuentaRegistroConvocado($ano, 
																			$id_asiscapac_actividades, 
																			$id_usuario);
									if ($cuentaConvocado>0) { //SOLO CONVOCADOS
										$id_check = "mismo correo muchos usuarios"; //sin correo
										$obs = "'Mismo correo = $email, utilizado en varios usuarios'";
										marcarActividadObligatoria($ano, $id_asiscapac_actividades,$id_usuario,$id_check,$obs);					
									}
								}
							}





						}


//						}
		
						//sql_insert = "insert into asiscapac_actividades_obligatorias_funcionarios(ano, id_asiscapac_actividades, id_usuario, id_asiscapac_actividades_funcionarios_check) values (2022, 25,1337,0);";
						//if (consulta_dml($sql_insert) > 0) {						
						//} else {
						//	$huboError = true;
						//	$ss_error = $ss_error." ".$x.",";
						//}

						echo(msje_js("Se ha recibido y guardado satisfactoriamente el documento "));
						//echo(js("window.location='$enlbase_sm=asiscapac_doctos_digitalizados&rut=$rut';"));
						//exit;
				} else {
					echo(msje_js("Error en línea $ss_error"));
					//echo(js("window.location='$enlbase_sm=asiscapac_doctos_digitalizados&rut=$rut';"));
					//exit;

				}

//				array_shift($planilla);
				//var_dump($planilla);
			} else {
				echo(msje_js("ERROR: El archivo que ha subido en su primera línea no contiene los nombres de columna exigidos."));
			}
				
		} else {
			echo(msje_js("ERROR: El archivo que ha subido no contiene 8 columnas esperadas.\\n\\n"
						."Verifique que el delimitador o separador de columnas sea una coma.\\n\\n"
						."Habitualmente MS-Excel usa punto y coma, por lo que deberá configurar esto al exportar el archivo."));
		}
		
	} else {
		echo(msje_js("ERROR: El archivo que ha subido no está en formato CSV."));
	}
}



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
		/*
		$HTML =  "<tr class='filaTabla'>\n"
			  .  "  <td class='textoTabla' align='left'>$estado</td>\n"
			  .  "  <td class='textoTabla' align='left'>$nombre_funcionario</td>\n"
			  .  "  <td class='textoTabla' align='left'>$email</td>\n"
			  .  "  <td class='textoTabla' align='center'>$hora_unirse</td>\n"
			  .  "  <td class='textoTabla' align='center'>$hora_salir</td>\n"
			  .  "  <td class='textoTabla' align='right'>$duracion_minutos</td>\n"
			  .  "  <td class='textoTabla' align='center'>$invitado</td>\n"
			  .  "  <td class='textoTabla' align='center'>$consentimiento_grabacion</td>\n"
			  .  "  <td class='textoTabla' align='center'>$en_sala_espera</td>\n"
			  .  "</tr>\n";	
*/

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





/*
if ($_REQUEST['subir'] == "") {

	$SQL_pp_det1 = "SELECT count(id_pp) FROM finanzas.pagos_planillas_detalle WHERE id_pp=pp.id";
	$SQL_pp_det2 = "SELECT sum(monto) FROM finanzas.pagos_planillas_detalle WHERE id_pp=pp.id";

	$SQL_pagos_planillas = "SELECT pp.*,
								   to_char(pp.fecha,'DD-tmMon-YYYY HH24:MI') AS fecha,
								   to_char(pp.fecha_docto,'DD-tmMon-YYYY') AS fecha_docto,
								   CASE tipo_docto 
									    WHEN 'nro_boleta'   THEN 'Boleta'
									    WHEN 'nro_boleta_e' THEN 'Bol-E'
								   END AS tipo_docto,
								   u.nombre_usuario,
								   ($SQL_pp_det1) AS cant_pagos,
								   ($SQL_pp_det2) AS monto_total
							FROM finanzas.pagos_planillas AS pp 
							LEFT JOIN usuarios AS u ON u.id=pp.id_usuario
							ORDER BY pp.fecha DESC";
	$pagos_planillas = consulta_sql($SQL_pagos_planillas);
	for ($x=0;$x<count($pagos_planillas);$x++) {
		extract($pagos_planillas[$x]);
		$monto_total = number_format($monto_total,0,",",".");
		$HTML_pagos_planilla .= "<tr class='filaTabla'>\n"
							.  "  <td class='textoTabla' align='right'>$id</td>\n"
							.  "  <td class='textoTabla'>$descripcion</td>\n"
							.  "  <td class='textoTabla' align='center'>$fecha<br>($nombre_usuario)</td>\n"
							.  "  <td class='textoTabla' align='center'>$fecha_docto<br>$tipo_docto</td>\n" 
							.  "  <td class='textoTabla' align='center'>$cant_pagos</td>\n"
							.  "  <td class='textoTabla' align='center'>$monto_total</td>\n"
							.  "  <td class='textoTabla' align='center'>$estado</td>\n"
							.  "</tr>\n";
	}
	
}
*/
//$TIPOS_DOCTOS = array(array('id' => "nro_boleta_e", 'nombre' => "Bol-E"),
//                      array('id' => "nro_boleta",   'nombre' => "Boleta"));

//$aTipos_doctos = array_column($TIPOS_DOCTOS,'nombre','id'); 
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>

<form name="formulario" method="post" enctype="multipart/form-data">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_asiscapac_actividades" value="<?php echo($id_asiscapac_actividades); ?>">
<input type="hidden" name="ano" value="<?php echo($ano); ?>">
<input type="hidden" name="id_origen" value="<?php echo($id_origen); ?>">
<input type="hidden" name="id_estado_check" value="<?php echo($id_estado_check); ?>">
<input type="hidden" name="id_campo_actividades" value="<?php echo($id_campo_actividades); ?>">


<?php if ($id_asiscapac_actividades!="") 
{ 
	?>

<div style='margin-top: 5px'>
  <?php if ($strActividad != "CERRADA") { ?>
  
  			<input type="submit" name="subir" value="Subir y Validar" onClick="return confirm('Está seguro de continuar?');" > <!--onBlur="this.disabled=true;">-->

  <?php } ?>
  <input type="button" name="volver" value="Volver" onClick="window.location.href='<?php echo($enlbase); ?>=asiscapac_actividades_buscar&ano=<?php echo($ano); ?>&id_origen=<?php echo($id_origen); ?>&id_estado_check=<?php echo($id_estado_check); ?>&id_campo_actividades=<?php echo($id_campo_actividades); ?>'">
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
  <?php if ($strActividad != "CERRADA") { ?>
			<tr>
				<td class='celdaNombreAttr'><u>Planilla:</u></td>
				<td class='celdaValorAttr' colspan='3'>
				<input type="file" accept=".csv" name="archivo_csv" size="50" required><br><br>
				NOTA: El archivo debe estar en formato CSV (delimitado por ;) y campos sin formato.<br><br>
				La primera línea de la planilla debe tener los nombres de campos o columnas siguientes:<br>
				<ul>
					<li><b>NOMBRE FUNCIONARIO</b> Funcionario quién estuvo en la reunión.</li>
					<li><b>E-MAIL</b> Correo corporativo funcionario @corp.umc.cl.</li>
					<li><b>HORA PARA UNIRSE</b> Fecha Hora reunión</li>
					<li><b>HORA PARA SALIR</b> Fecha Hora reunión</li>
					<li><b>DURACIÓN MINUTOS</b> Cantidad minutos reunión.</li>
					<li><b>INVITADO</b> Sí/No</li>
					<li><b>CONSENTIMIENTO GRABACIÓN</b> Sí/nulo</li>
					<li><b>EN SALA DE ESPERA</b> Sí / No</li>
				</ul>
				</td>
			</tr>
  <?php } ?>
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






<?php 	//if (count($aPagos_problemas) > 0) { ?>

	
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

<?php 	//}  ?>

<?php } ?>

</form>

<?php


?>

