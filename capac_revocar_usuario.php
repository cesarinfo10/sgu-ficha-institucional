<?php
/*
function existenRegistros($ano, 
//$id_asiscapac_origen, 
$id_capacitacion, 
$id_usuario_seleccionado) {

try {
$ss = "
select count(*) as cuenta from asiscapac_usuario_capacitaciones
where
ano = $ano 
and id_asiscapac_capacitaciones = $id_capacitacion
and id_usuario = $id_usuario_seleccionado
"; 



$sqlCuenta     = consulta_sql($ss);

//        echo("<br>".$ss);


extract($sqlCuenta[0]);
} catch (Exception $e) {
$cuenta = 0;
}

return $cuenta;

}

*/
function sacaEstadoCapacitacion($id_campo_usuario_capacitaciones) {

	$ss = "
	  select id_asiscapac_estado from asiscapac_usuario_capacitaciones
	  where id = $id_campo_usuario_capacitaciones
	";
	$sql     = consulta_sql($ss);

	//echo("<br>".$ss);


	extract($sql[0]);
	return $id_asiscapac_estado;
}


if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$ano            = $_REQUEST['ano'];
$id_origen      = $_REQUEST['id_origen']; 
$id_campo_usuario_capacitaciones  = $_REQUEST['id_campo_usuario_capacitaciones'];
$id_estado_check  = $_REQUEST['id_estado_check'];
$id_usuario_confirmar  = $_REQUEST['id_usuario_confirmar'];
$confirmar  = $_REQUEST['confirmar'];
$observacion  = $_REQUEST['observacion'];
$observacionrevocar  = $_REQUEST['observacionrevocar'];
$param_observacion  = $_REQUEST['param_observacion'];
$param_observacionrevocar  = $_REQUEST['param_observacionrevocar'];
$reabrir  = $_REQUEST['reabrir'];
$cerrar  = $_REQUEST['cerrar'];
$suspender  = $_REQUEST['suspender'];




if ($reabrir <> "") { 
	$SQL = "
	update asiscapac_usuario_capacitaciones
	set 
	id_asiscapac_estado = 1
	where id = $id_campo_usuario_capacitaciones
  
  ;";
//echo($SQL);
	if (consulta_dml($SQL) > 0) {
	  echo(msje_js("Registro Actualizado exitosamente"));
	  echo(js("parent.jQuery.fancybox.close();"));
	} else {
			echo(msje_js("Error* : al momento de grabar."));          
	}                  
  
  }
  if ($cerrar <> "") { //CERRAR ACTIVIDAD
	$SQL = "
	update asiscapac_usuario_capacitaciones
	set 
	id_asiscapac_estado = 3
	where id = $id_campo_usuario_capacitaciones
  
  ;";
//echo($SQL);
	if (consulta_dml($SQL) > 0) {
	  echo(msje_js("Registro Actualizado exitosamente"));
	  echo(js("parent.jQuery.fancybox.close();"));
	} else {
			echo(msje_js("Error* : al momento de grabar."));          
	}                  
  
  }
  
  if ($suspender <> "") {
		  $SQL_correo = "select email as email_usuario, 
			nombre_usuario as nombre_usuario_operador, 
			nombre as nombre_operador, 
			apellido as apellido_operador  
			from usuarios where id = $id_usuario_confirmar
		  and email is not null";
  
  
			$envio_correo = consulta_sql($SQL_correo);
			$envioMensaje = false;
			for ($y=0;$y<count($envio_correo);$y++) {
					extract($envio_correo[$y]);
					//AQUI DEBE ENVIAR CORREO
					$sql_act = "select descripcion act_descripcion, 
						  to_char(fecha_inicio,'DD \"de\" tmMonth \"de\" YYYY a las HH24:MI') act_fecha_inicio, 
						  to_char(fecha_termino,'DD \"de\" tmMonth \"de\" YYYY a las HH24:MI') act_fecha_termino, 
							link_capacitaciones link_capacitaciones 
							from asiscapac_usuario_capacitaciones 
							where id = $id_campo_usuario_capacitaciones";
					$my_act = consulta_sql($sql_act);
					extract($my_act[0]);
  //echo("<br>se envia correo a : $nombre_operador $apellido_operador");
  
					$asunto = "SGU: Suspensión de convocatoria de capacitación para $act_fecha_inicio : $act_descripcion";
					$cuerpo = "Sr(a) $nombre_operador $apellido_operador, \n\n";
					$cuerpo .= "Informamos que la convocatoria de Capacitación, relacionada con $act_descripcion', la que fue citada para el $act_fecha_inicio horas, queda Suspendida.\n\n";
					$cuerpo .= "Agradecemos la consideración de esta información.\n\n";
					$cuerpo .= "Saludos cordiales.\n\n";
					$cuerpo .= "Unidad de Recursos Humanos\nUniversidad Miguel de Cervantes";
					$cabeceras = "From: SGU" . "\r\n"
								. "Content-Type: text/plain;charset=utf-8" . "\r\n";
  
					//                mail($email_usuario,$asunto,$cuerpo,$cabeceras);
					//if ($y == 0) {
					  //mail("rmazuela@corp.umc.cl",$asunto,$cuerpo,$cabeceras);
					  mail("dcarreno@corp.umc.cl",$asunto,$cuerpo,$cabeceras);
					  $envioMensaje = true;
					//}
  
			}
  
  
  
  
  
			$SQL = "
			update asiscapac_usuario_capacitaciones
			set 
			id_asiscapac_estado = 4
			where id = $id_campo_usuario_capacitaciones
  
		  ;";
		  //echo($SQL);
			if (consulta_dml($SQL) > 0) {
			  if ($envioMensaje) {
				echo(msje_js("Se ha se han enviado correctamente los correos con la suspensión de la actividad."));
			  } else {
				echo(msje_js("Registro actualizado correctamente, Sin embargo no se han enviado correos."));
			  }
			  echo(js("parent.jQuery.fancybox.close();"));  
			} else {
					echo(msje_js("Error** : al momento de grabar."));          
			}                  
  
  }
  







$estado_actividad = "";
$strActividad = "";
if ($id_campo_usuario_capacitaciones<>"") {
  $estado_actividad = sacaEstadoCapacitacion($id_campo_usuario_capacitaciones);
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

$ss = "
select 
a.observacion as db_observacion, 
a.observacion_revocar as db_observacionrevocar ,
(SELECT CASE
                 WHEN ( (SELECT Count(*)
                         FROM   asiscapac_usuario_capacitaciones
                         WHERE  confirmado = 't'
                                AND fecha_aceptacion IS NOT NULL
                                AND id = a.id) > 0 ) THEN 'SI'
                 ELSE ( CASE
                          WHEN ( (SELECT Count(*)
                                  FROM   asiscapac_usuario_capacitaciones
                                  WHERE  confirmado = 'f'
                                         AND fecha_revocar IS NOT NULL
                                         AND id = a.id) > 0 ) THEN
                          'NO'
                          ELSE 'NADA'
                        END )
               END) AS db_confirmado
from asiscapac_usuario_capacitaciones a
where
a.ano = $ano 
and a.id = $id_campo_usuario_capacitaciones
and a.id_usuario = $id_usuario_confirmar
"; 
//echo($ss);
$sqlobs     = consulta_sql($ss);
extract($sqlobs[0]);

//echo("$ss <br>");
//echo("db_observacion= $db_observacion <br>");



/*
if ($confirmar == "SI") {
	$campo_obs = $observacion;
} else {
	$campo_obs = $observacionrevocar;
}
*/




if ($confirmar == "SI") {
	$textoBoton = "Confirmar";
}
if ($confirmar =="NO") {
	$textoBoton = "Revocar";
}

$gg = $_REQUEST['guardar'];
if ($confirmar == "SI") {
	$campo_obs = $observacion;
} else {
	if ($confirmar == "NO") {
		$campo_obs = $observacionrevocar;
	} else {
		$campo_obs = "";
	}
}

//echo("ano = $ano <br>");
//echo("id_origen = $id_origen <br>");
//echo("id_campo_capacitaciones = $id_campo_capacitaciones <br>");
//echo("id_estado_check = $id_estado_check <br>");
//echo("id_usuario_confirmar = $id_usuario_confirmar <br>");
//echo("confirmar = $confirmar <br>");
//echo("guardar = $gg <br>");
//echo("textoBoton = $textoBoton <br>");

if (($gg == "Confirmar") || ($gg == "Revocar")){
	//if ($gg == $textoBoton) 
	{ 
//					echo("UNO");
					if ($confirmar <> "") {
//						echo("DOS");
						if ($id_usuario_confirmar <> "") {
//							echo("TRES");
								if ($gg == "Confirmar") {
									$confirmar = "SI";
									
								}
								if ($gg == "Revocar") {
									$confirmar = "NO";
								}
//								echo("gg = $gg, confirmar=$confirmar<br>");
				/*
						$cuenta = existenRegistros($ano, 
										//$id_asiscapac_origen, 
										$id_campo_usuario_capacitaciones, 
										$id_usuario_confirmar);
										*/
										
										
										
				//						  echo("CUATRO");

						//echo("<br>cuenta = $cuenta");
						//if ($confirmar == "SI") {
							//$bConfirmar = 't';
							//$sCampoObs = "observacion";
							//$sObs = $observacion;
						//} else {
							//$bConfirmar = 'f';
							//$sCampoObs = "observacion_revocar";
							//$sObs = $observacionrevocar;
						//}
						if ($confirmar == "SI") {
							//if ($cuenta==0) {
				/*						
											//$fecha = date("Y-m-d");
											$SQL = "
											insert into asiscapac_usuario_capacitaciones
											(ano, 
											id_asiscapac_capacitaciones, 
											id_usuario, 
											convocado,
											confirmado,
											observacion,
											fecha_aceptacion
											) 
											(select 
											$ano, 
											$id_campo_usuario_capacitaciones, 
											id,
											't',
											't',
											'$observacion',
											now()
											from usuarios where id = $id_usuario_confirmar
											)
											;";
															echo("<br>$SQL");
											if (consulta_dml($SQL) > 0) {
												echo(msje_js("*Registro exitoso"));
												echo(js("parent.jQuery.fancybox.close();"));
									
											} else {
											}                  
											*/
									//} else {
											$SQL = "
											update asiscapac_usuario_capacitaciones
											set 
											confirmado = 't',
											observacion = '$observacion',
											fecha_aceptacion = now()
											where 
											id = $id_campo_usuario_capacitaciones
											;"; 
											consulta_dml($SQL);      
											//echo("<br>$SQL");
											echo(msje_js("Registro exitoso"));
											echo(js("parent.jQuery.fancybox.close();"));
									exit;

								}
							
						
						if ($confirmar == "NO") { //REVOCAR
				//			if ($cuenta==0) 
				//			{
				/*			
								//$fecha = date("Y-m-d");
								$SQL = "
								insert into asiscapac_usuario_capacitaciones
								(ano, 
								id_asiscapac_capacitaciones, 
								id_usuario, 
								convocado,
								confirmado,
								observacion_revocar,
								fecha_revocar
								) 
								(select 
								$ano, 
								$id_campo_usuario_capacitaciones, 
								id,
								't',
								'f',
								'$observacionrevocar',
								now()
								from usuarios where id = $id_usuario_confirmar
								)
								;";
												echo("<br>$SQL");
								if (consulta_dml($SQL) > 0) {
									echo(msje_js("*Registro exitoso"));
									echo(js("parent.jQuery.fancybox.close();"));
						
								} else {
								}                  
								*/
				//			} else {
									$SQL = "
									update asiscapac_usuario_capacitaciones
									set 
									confirmado = 'f',
									--observacion = null,
									observacion_revocar = '$observacion',
									fecha_revocar = now()
									where 
									id = $id_campo_usuario_capacitaciones
									;"; 
//									echo("<br>$SQL");
									consulta_dml($SQL);      
									
									
									echo(msje_js("Registro exitoso"));
									echo(js("parent.jQuery.fancybox.close();"));			
								exit;

							}
						}
					} //confirmar
	}
	  



//	$aCampos = array("id_usuario_reg","tipo_act","prioridad","estado","id_unidad","actividad","id_proyecto","fecha_prog_termino","comentarios");
/*	
	if (!empty($_REQUEST['comentarios'])) { 
		$fecha_comentario = strftime("%A %e-%b-%Y a las %R");
		$_REQUEST['comentarios'] = "El $fecha_comentario, {$_SESSION['usuario']} escribió:\n\n"
		                         . $_REQUEST['comentarios']."\n"
		                         . "<hr>";
	}
	if (strtotime($_REQUEST['fecha_prog_termino']) < time()) { $_REQUEST['estado'] = "Pendiente"; }
	
	$SQLinsert = "INSERT INTO gestion.poas " . arr2sqlinsert($_REQUEST,$aCampos);
	if (consulta_dml($SQLinsert) == 1) {
		email_nueva_tarea($_REQUEST['id_unidad'],$_REQUEST['actividad'],$_REQUEST['fecha_prog_termino']);
		echo(msje_js("Se ha creado una nueva tarea con los datos ingresados exitósamente"));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}
	*/
//		  }
		  
//	}
	
}
if ($param_observacion != "") {
	$observacion = $param_observacion;
}
if ($param_observacionrevocar != "") {
	$observacion = $param_observacionrevocar;
}

?>
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>	
<!--<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME'])?>" method="post"> -->
<form name="formulario" action="principal_sm.php" method="get">
	

<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="ano" id="ano" value="<?php echo($ano);?>">
<input type="hidden" name="id_origen" id="id_origen" value="<?php echo($id_origen);?>">
<input type="hidden" name="id_campo_usuario_capacitaciones" id="id_campo_usuario_capacitaciones" value="<?php echo($id_campo_usuario_capacitaciones);?>">
<input type="hidden" name="id_estado_check" id="id_estado_check" value="<?php echo($id_estado_check);?>">
<input type="hidden" name="id_usuario_confirmar" id="id_usuario_confirmar" value="<?php echo($id_usuario_confirmar);?>">
<input type="hidden" name="confirmar" id="confirmar" value="<?php echo($confirmar);?>">

<?php

?>

<div style="margin-top: 5px">
  <!--<input type="submit" name="guardar" value="<?php echo($textoBoton); ?>"> -->
  <?php 
	$mostrar = 1;
  	if ($strActividad == "CERRADA") {          
		$mostrar = 0;
  	}
    if ($strActividad == "SUSPENDIDA") {          
		$mostrar = 0;
	}

	?>



  <?php if ($mostrar == 1) {          ?>
	<input type="submit" name="guardar" value="Confirmar">
	<input type="submit" name="guardar" value="Revocar">
  <?php }  ?>

  <?php 
        //    echo("<br>Estado : $strActividad");
		if ($strActividad == "CERRADA") {          ?>
			<input type='submit' name='reabrir' value='Re-abrir' onClick="return confirm('Está seguro de abrir esta capacitación?');">
			
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<?php }  ?>
		<?php 	
            if ($strActividad != "CERRADA") {          ?>
              <?php if ($strActividad != "SUSPENDIDA") {          ?>
              <!--<input type='submit' name='grabar' value='grabar' style='font-size: 9pt'>-->
              <?php } ?>

              <?php if ($strActividad!="SUSPENDIDA") { ?>
                      <input type='submit' name='suspender' value='Suspender' onClick="return confirm('Está seguro de suspender esta capacitación (Se enviará correo a los participantes)?');">
              <?php } ?>

              <?php if ($strActividad != "SUSPENDIDA") {          ?>
                <input type='submit' name='cerrar' value='Cerrar Capacitación' onClick="return confirm('Está seguro de cerrar capacitación?');">          
                <?php } ?>

              <?php if ($strActividad == "PROGRAMADA") {?>
                <!--<input type='submit' name='eliminar' value='Eliminar' style='font-size: 9pt' onClick="return confirm('Está seguro de eliminar actividad?');">          -->
              <?php }?>
			  
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <?php }  ?>

</div>

<?php 
	$textoColocar = "";

	$mostrar = 1;
  	if ($strActividad == "CERRADA") {          
		$mostrar = 0;
  	}
    if ($strActividad == "SUSPENDIDA") {          
		$mostrar = 0;
	}
	if ($mostrar == 1) {
		if ($db_confirmado=="SI") { 
			$textoColocar = "Esta capacitación se encuentra Confirmada actualmente.";
		}
		if ($db_confirmado=="NO") { 
			$textoColocar = "Esta capacitación se encuentra Revocada actualmente.";
		}	
	}
	if ($mostrar == 0) {
		$textoColocar = "Esta capacitación se encuentra $strActividad.";
	}



	if ($textoColocar <> "") {

	?>
			<table cellspacing="1" cellpadding="2" class="tabla">
			<tr>
				<td class="texto">
				<?php echo($textoColocar); ?> 
				</td>
			</tr>
			</table>
<?php }?>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style="margin-top: 5px">
  <!--<tr><td class="celdaNombreAttr" colspan="4" style="text-align: center">Ingrese Observación</td></tr>-->
<!--
  <tr>
	<td class="celdaValorAttr" colspan="3">
	  <select class='filtro' name="id_proyecto" style="max-width: none">
		<option value="">Ninguno</option>
		<optgroup label="Mis Proyectos">
		  <?php echo(select($MIS_PROYECTOS,$_REQUEST[0]['id_proyecto'])); ?>
		</optgroup>
		<optgroup label="Proyectos de otros">
          <?php echo(select($OTROS_PROYECTOS,$_REQUEST[0]['id_proyecto'])); ?>
		</optgroup>
	  </select>
	</td>
  </tr>
-->
  <tr>
    <td class="celdaNombreAttr">Observación</td>
    <td class="celdaValorAttr" colspan="3">
      <textarea name="observacion" cols="40" rows="20" class="general"><?php echo($observacion); ?></textarea>
	</td>
  </tr>


</table>
</form>

<?php

function email_nueva_tarea($id_unidad,$actividad,$fecha_prog_termino) {
	
	$fecha_prog_termino = strftime("%A %e-%b-%Y",strtotime($fecha_prog_termino));
	
	$unidad = consulta_sql("SELECT nombre FROM gestion.unidades WHERE id=$id_unidad");
	$unidad = $unidad[0]['nombre'];
	
	$usuarios = consulta_sql("SELECT email FROM usuarios WHERE id_unidad=$id_unidad");
	
	$CR = "\r\n";
			
	$cabeceras = "From: SGU" . $CR
	           . "Content-Type: text/plain;charset=utf-8" . $CR;
	           
	$asunto = "POA: Nueva Tarea";
	
	$cuerpo = "Estimad@ Responsable de $unidad" . $CR.$CR
	        . "Se agregó una nueva tarea a su POA:" . $CR.$CR
	        . $actividad . $CR
	        . "Fecha de Término: $fecha_prog_termino" . $CR.$CR
	        . "Para completar esta tarea, debe usar el módulo POA en el "
	        . "SGU y usar el botón «Terminar». El SGU le pedirá que "
	        . "escoja y suba el archivo de evidencia respectivo." . $CR.$CR
	        . "Luego, la unidad de Aseguramiento de la Calidad, "
	        . "evaluará el contenido del archivo y si corresponde otorgará "
	        . "el OK a la tarea." . $CR.$CR
	        . "Gracias!" . $CR
	        . "Atte.," . $CR.$CR
	        . "Dirección de Aseguramiento de la Calidad";
	        
	for ($x=0;$x<count($usuarios);$x++) { mail($usuarios[$x]['email'],$asunto,$cuerpo,$cabeceras); }
}

?>
